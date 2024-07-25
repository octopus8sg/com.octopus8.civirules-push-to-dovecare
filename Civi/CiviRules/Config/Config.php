<?php
/**
 * Copyright (C) 2023  Jaap Jansma (jaap.jansma@civicoop.org)
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

namespace Civi\CiviRules\Config;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class Config extends Container {

  public function getCustomGroupById(int $customGroupId):? array {
    $customGroups = $this->getParameter('custom_groups');
    if (isset($customGroups[$customGroupId]) && is_array($customGroups[$customGroupId])) {
      return $customGroups[$customGroupId];
    }

    $customGroup = civicrm_api3('CustomGroup', 'get', [
      'id' => $customGroupId,
      'sequential' => 1,
    ]);
    if ($customGroup['count'] > 0) {
      return $customGroup['values'][0];
    }

    return null;
  }

  /**
   * Build the container with the custom field and custom groups.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder
   */
  public static function buildConfigContainer(ContainerBuilder $containerBuilder) {
    $customGroups = array();
    $customFields = array();
    try {
      $customGroupApi = civicrm_api3('CustomGroup', 'get', [
        'options' => ['limit' => 0],
      ]);
      foreach($customGroupApi['values'] as $customGroup) {
        $customGroups[$customGroup['id']] = $customGroup;
      }
    } catch (CRM_Core_Exception $e) {
    }

    $customGroups = $containerBuilder->getParameterBag()->escapeValue($customGroups);
    $containerBuilder->setParameter('custom_groups', $customGroups);
  }

}
