<?php
/**
 * Class for CiviRules generic HasTag condition
 *
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

class CRM_CivirulesConditions_Generic_HasTag {
  protected $entityTable = "civicrm_contact";

  /**
   * @param $entityTable
   */
  public function setEntityTable($entityTable) {
    $this->entityTable = $entityTable;
  }

  /**
   * Method to get tags with API4
   * @param $entityId
   * @return array
   */
  public function getApi4TagsWithEntityId($entityId) {
    $tags = [];
    try {
      $tags = \Civi\Api4\EntityTag::get()
        ->setCheckPermissions(FALSE)
        ->addSelect('tag_id')
        ->addWhere('entity_table', '=', $this->entityTable)
        ->addWhere('entity_id', '=', $entityId)
        ->execute()->column('tag_id');
    }
    catch (API_Exception $ex) {
    }
    return $tags;
  }

  /**
   * Method to get tags with API3
   *
   * @param $entityId
   * @return array
   */
  public function getApi3TagsWithEntityId($entityId) {
    $tags = [];
    try {
      $tags = civicrm_api3('Tag', 'get', [
        'options' => ['limit' => 0],
        'entity_table' => $this->entityTable,
        'entity_id' => $entityId,
        'return' => 'id',
      ])['values'];
    }
    catch (CiviCRM_API3_Exception $ex) {
    }
    return $tags;
  }

  /**
   * @param int $entityId
   * @param array $tagIds
   * @return bool
   */
  public function entityHasNotTag(int $entityId, array $tagIds): bool {
    $isValid = TRUE;
    if (CRM_Civirules_Utils::isApi4Active()) {
      $tags = $this->getApi4TagsWithEntityId($entityId);
    }
    else {
      $tags = $this->getApi3TagsWithEntityId($entityId);
    }
    foreach ($tagIds as $tagId) {
      if (in_array($tagId, $tags)) {
        $isValid = FALSE;
      }
    }
    return $isValid;
  }

  /**
   * @param int $entityId
   * @param array $tagIds
   * @return bool
   */
  public function entityHasAllTags(int $entityId, array $tagIds):bool {
    $isValid = 0;
    if (CRM_Civirules_Utils::isApi4Active()) {
      $tags = $this->getApi4TagsWithEntityId($entityId);
    }
    else {
      $tags = $this->getApi3TagsWithEntityId($entityId);
    }
    foreach($tagIds as $tagId) {
      if (in_array($tagId, $tags)) {
        $isValid++;
      }
    }
    if (count($tagIds) == $isValid && count($tagIds) > 0) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * @param int $entityId
   * @param array $tagIds
   * @return bool
   */
  public function entityHasOneOfTags(int $entityId, array $tagIds): bool {
    $isValid = FALSE;
    if (CRM_Civirules_Utils::isApi4Active()) {
      $tags = $this->getApi4TagsWithEntityId($entityId);
    }
    else {
      $tags = $this->getApi3TagsWithEntityId($entityId);
    }
    foreach($tagIds as $tagId) {
      if (in_array($tagId, $tags)) {
        $isValid = TRUE;
        break;
      }
    }
    return $isValid;
  }

  /**
   * Method to get operators
   *
   * @return array
   */
  public function getOperatorOptions(): array {
    return [
      'in one of' => ts('In one of selected'),
      'in all of' => ts('In all selected'),
      'not in' => ts('Not in selected'),
    ];
  }

  /**
   * Method to get the tags for the entity
   *
   * @return array
   */
  public function getEntityTags() {
    if (CRM_Civirules_Utils::isApi4Active()) {
      return $this->getApi4Tags();
    }
    else {
      return $this->getApi3Tags();
    }
  }

  /**
   * Method to get all contact tags with API4
   */
  private function getApi4Tags() {
    $tags = [];
    try {
      $apiTags = \Civi\Api4\Tag::get()
        ->addSelect('name')
        ->addWhere('used_for', 'LIKE', '%' . $this->entityTable . '%')
        ->execute();
      foreach ($apiTags as $apiTag) {
        if (!isset($tags[$apiTag['id']])) {
          $tags[$apiTag['id']] = $apiTag['name'];
        }
      }
    }
    catch (API_Exception $ex) {
    }
    return $tags;
  }

  /**
   * Method to get all contact tags with API3
   */
  private function getApi3Tags() {
    $tags = [];
    try {
      $apiTags = civicrm_api3('Tag', 'get', [
        'return' => ["name"],
        'used_for' => ['LIKE' => "%" . $this->entityTable ."%"],
        'options' => ['limit' => 0],
      ]);
      foreach ($apiTags['values'] as $apiTagId => $apiTag) {
        if (!isset($tags[$apiTagId])) {
          $tags[$apiTagId] = $apiTag['name'];
        }
      }
    }
    catch (CiviCRM_API3_Exception $ex) {
    }
    return $tags;
  }

}
