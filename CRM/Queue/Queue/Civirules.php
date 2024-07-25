<?php

class CRM_Queue_Queue_Civirules extends CRM_Queue_Queue_Sql {

  /**
   * Determine number of items remaining in the queue
   *
   * @return int
   */
  public function numberOfItems() {
    return CRM_Core_DAO::singleValueQuery("
      SELECT count(*)
      FROM civicrm_queue_item
      WHERE queue_name = %1
      and (release_time is null OR release_time <= NOW())
    ", [
      1 => [$this->getName(), 'String'],
    ]);
  }

  /**
   * Get the next item
   *
   * @param int $lease_time seconds
   *
   * @return object with key 'data' that matches the inputted data
   */
  public function claimItem($lease_time = 3600) {
    $sql = "
      SELECT id, queue_name, submit_time, release_time, data
      FROM civicrm_queue_item
      WHERE queue_name = %1
      and (release_time is null OR release_time <= NOW())
      ORDER BY weight ASC, release_time ASC, id ASC
      LIMIT 1
    ";
    return $this->getItemFromQueue($sql, $lease_time);
  }

  /**
   * Get the next item, even if there's an active lease
   *
   * @param int $lease_time seconds
   *
   * @return object with key 'data' that matches the inputted data
   */
  public function stealItem($lease_time = 3600) {
    $sql = "
      SELECT id, queue_name, submit_time, release_time, data
      FROM civicrm_queue_item
      WHERE queue_name = %1
      ORDER BY weight ASC, release_time ASC, id ASC
      LIMIT 1
    ";
    return $this->getItemFromQueue($sql, $lease_time);
  }

  /**
   * @param string $sql
   * @param int $leaseTime
   *
   * @return \CRM_Core_DAO|\DB_Error|object|void
   * @throws \Civi\Core\Exception\DBQueryException
   */
  private function getItemFromQueue(string $sql, int $leaseTime = 3600) {
    $params = [
      1 => [$this->getName(), 'String'],
    ];
    $dao = CRM_Core_DAO::executeQuery($sql, $params, TRUE, 'CRM_Queue_DAO_QueueItem');
    if ($dao->fetch()) {
      $nowEpoch = CRM_Utils_Time::time();
      CRM_Core_DAO::executeQuery("UPDATE civicrm_queue_item SET release_time = %1 WHERE id = %2", [
        '1' => [date('YmdHis', $nowEpoch + $leaseTime), 'String'],
        '2' => [$dao->id, 'Integer'],
      ]);
      $dao->data = unserialize($dao->data);
      return $dao;
    }
  }

}
