<?php

use CRM_CiviRules_ExtensionUtil as E;

return [
  [
    'name' => 'CiviRules',
    'entity' => 'Navigation',
    'cleanup' => 'always',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'label' => E::ts('CiviRules'),
        'name' => 'CiviRules',
        'url' => NULL,
        'permission' => ['administer CiviCRM, administer CiviRules'],
        'permission_operator' => 'OR',
        'parent_id.name' => 'Administer',
        'is_active' => TRUE,
        'has_separator' => 0,
        'weight' => 90,
      ],
      'match' => ['name'],
    ],
  ],
];
