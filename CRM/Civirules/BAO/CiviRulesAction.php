<?php
/**
 * BAO Action for CiviRule Action
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */
class CRM_Civirules_BAO_CiviRulesAction extends CRM_Civirules_DAO_CiviRulesAction {

  /**
   * Function to get values
   *
   * @return array $result found rows with data
   * @access public
   * @static
   */
  public static function getValues($params) {
    $result = array();
    $action = new CRM_Civirules_BAO_Action();
    if (!empty($params)) {
      $fields = self::fields();
      foreach ($params as $key => $value) {
        if (isset($fields[$key])) {
          $action->$key = $value;
        }
      }
    }
    $action->find();
    while ($action->fetch()) {
      $row = array();
      self::storeValues($action, $row);
      $result[$row['id']] = $row;
    }
    return $result;
  }

  /**
   * Function to delete an action with id
   *
   * @param int $actionId
   * @throws Exception when actionId is empty
   * @access public
   * @static
   */
  public static function deleteWithId($actionId) {
    if (empty($actionId)) {
      throw new Exception('action id can not be empty when attempting to delete a civirule action');
    }

    if (!CRM_Core_DAO::checkTableExists(self::getTableName())) {
      return;
    }

    //delete rows from rule_action to prevent a foreign key constraint error
    CRM_Core_DAO::executeQuery("DELETE FROM `civirule_rule_action` where `action_id` = %1", array(1 => array($actionId, 'Integer')));

    $action = new CRM_Civirules_BAO_Action();
    $action->id = $actionId;
    $action->delete();
    return;
  }

  /**
   * Function to retrieve the label of an action with actionId
   *
   * @param int $actionId
   * @return string $action->label
   * @access public
   * @static
   */
  public static function getActionLabelWithId($actionId) {
    if (empty($actionId)) {
      return '';
    }
    $action = new CRM_Civirules_BAO_Action();
    $action->id = $actionId;
    $action->find(true);
    return $action->label;
  }

  /**
   * Get the action class for this condition
   *
   * @param $actionId
   * @param bool $abort if true this function will throw an exception if class could not be instantiated
   * @return CRM_Civirules_Action
   * @throws Exception if abort is set to true and class does not exist or is not valid
   */
  public static function getActionObjectById($actionId, $abort=true) {
    $action = new CRM_Civirules_BAO_Action();
    $action->id = $actionId;
    if (!$action->find(true)) {
      if ($abort) {
        throw new Exception('CiviRule could not find action');
      }
      return false;
    }

    $className = $action->class_name;
    if (!class_exists($className)) {
      if ($abort) {

        throw new Exception('CiviRule action class "' . $className . '" does not exist');
      }
      return false;
    }

    $object = new $className();
    if (!$object instanceof CRM_Civirules_Action) {
      if ($abort) {
        throw new Exception('CiviRule action class "' . $className . '" is not a subclass of CRM_Civirules_Action');
      }
      return false;
    }

    $actionData = array();
    CRM_Core_DAO::storeValues($action, $actionData);
    $object->setActionData($actionData);

    return $object;
  }

}
