<?php
/**
 * Class following Singleton pattern for specific extension configuration
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @license AGPL-3.0
 */
class CRM_Civirules_Config {
  /*
   * singleton pattern
   */
  private static $_singleton = NULL;
  /*
   * properties to hold the valid entities and actions for civirule trigger
   */
  protected $validTriggerObjectNames = NULL;
  protected $validTriggerOperations = NULL;

  /**
   * Constructor
   */
  function __construct() {
    $this->setTriggerProperties();
  }

  /**
   * Function to return singleton object
   *
   * @return object $_singleton
   */
  public static function &singleton() {
    if (self::$_singleton === NULL) {
      self::$_singleton = new CRM_Civirules_Config();
    }
    return self::$_singleton;
  }

  /**
   * Function to get the valid trigger entities
   *
   * @return array
   */
  public function getValidTriggerObjectNames()
  {
    return $this->validTriggerObjectNames;
  }

  /**
   * Function to get the valid trigger actions
   *
   * @return array
   */
  public function getValidTriggerOperations()
  {
    return $this->validTriggerOperations;
  }

  protected function setTriggerProperties() {
    $this->validTriggerOperations = [
      'create',
      'edit',
      'delete',
      'restore',
      'trash',
      'update'
    ];

    // Load all entities from CiviCRM core.
    $this->validTriggerObjectNames = array_keys(CRM_Core_DAO_AllCoreTables::daoToClass());
    $this->validTriggerObjectNames[] = 'Individual';
    $this->validTriggerObjectNames[] = 'Household';
    $this->validTriggerObjectNames[] = 'Organization';
  }
}
