<?php
/**
 * Copyright (C) 2021  Jaap Jansma (jaap.jansma@civicoop.org)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Civi\ConfigItems\Entity\CiviRules;

use Civi\ConfigItems\ConfigurationForm;
use Civi\ConfigItems\Tab;
use CRM_Civirules_ExtensionUtil as E;

class ImportForm implements ConfigurationForm, Tab {

  /**
   * @var \Civi\ConfigItems\Entity\SimpleEntity\Importer
   */
  protected $entityImporter;

  public function __construct(Importer $entityImporter) {
    $this->entityImporter = $entityImporter;
  }

  /**
   * @return string
   */
  public function getTitle() {
    return $this->entityImporter->getEntityDefinition()->getTitlePlural();
  }

  /**
   * @return string
   */
  public function getHelpText() {
    return $this->entityImporter->getHelpText();
  }

  /**
   * @param \CRM_Core_Form $form
   * @param array $configuration
   * @param array $config_item_set
   */
  public function buildConfigurationForm(\CRM_Core_Form $form, $configuration, $config_item_set) {
    $entityData = $this->entityImporter->loadEntityImportData($config_item_set);
    $groups = $this->entityImporter->getGroups();
    $form->assign('groups', $groups);
    $form->assign('entityData', $entityData);
    $form->assign('entityTitle', $this->getTitle());
    $form->assign('helpText', $this->getHelpText());
    $defaults = [];
    $nameAttribute = $this->entityImporter->getEntityDefinition()->getNameAttribute();
    $titleAttribute = $this->entityImporter->getEntityDefinition()->getTitleAttribute();
    $elements = [];
    foreach($groups as $group => $groupTitle) {
      if (!isset($entityData[$group])) {
        $entityData[$group] = [];
      }
      foreach ($entityData[$group] as $name => $data) {
        $name = \CRM_Utils_String::munge($data[$nameAttribute]);
        $radioButtons = $this->entityImporter->getOptions($group, $data);
        if ($radioButtons && is_array($radioButtons) && count($radioButtons)) {
          $form->addRadio($name, $data[$titleAttribute], $radioButtons, ['allowClear' => TRUE], NULL, TRUE);
          if (isset($configuration[$group][$data[$nameAttribute]])) {
            $defaults[$name] = $configuration[$group][$data[$nameAttribute]];
          } else {
            $defaults[$name] = $this->entityImporter->getDefaultOption($group, $data);
          }
          $elements[$group][] = $name;
        }
      }
    }
    $form->assign('elements', $elements);
    $form->setDefaults($defaults);
  }


  /**
   * Returns the name of the template for the configuration form.
   *
   * @return string
   */
  public function getConfigurationTemplateFileName() {
    return "CRM/ConfigItems/Entity/CiviRules/ImportForm.tpl";
  }

  /**
   * Process the submitted values and create a configuration array
   *
   * @param $submittedValues
   * @param array $config_item_set
   * @return array
   */
  public function processConfiguration($submittedValues, $config_item_set) {
    $config = [];
    $entityData = $this->entityImporter->loadEntityImportData($config_item_set);
    $nameAttribute = $this->entityImporter->getEntityDefinition()->getNameAttribute();
    foreach ($this->entityImporter->getGroups() as $group => $groupTitle) {
      $config[$group] = [];
      if (isset($entityData[$group])) {
        foreach ($entityData[$group] as $data) {
          $name = \CRM_Utils_String::munge($data[$nameAttribute]);
          $config[$group][$data[$nameAttribute]] = $submittedValues[$name];
        }
      }
    }
    return $config;
  }

  /**
   * This function is called to add tabs to the tabset.
   * Returns the $tabset
   *
   * @param $tabset
   * @param $configuration
   * @param $config_item_set
   * @param bool $reset
   * @return array
   */
  public function getTabs($tabset, $configuration, $config_item_set, $reset=FALSE) {
    $entityName = $this->entityImporter->getEntityDefinition()->getName();
    $url = \CRM_Utils_System::url('civicrm/admin/civiconfig/import/entity', ['reset' => 1, 'id' => $config_item_set['id'], 'entity' => $entityName]);
    $tabset[$entityName] = [
      'title' => $this->entityImporter->getEntityDefinition()->getTitlePlural(),
      'active' => 1,
      'valid' => 1,
      'link' => $url,
      'current' => false,
    ];
    return $tabset;
  }


}
