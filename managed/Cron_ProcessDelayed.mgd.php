<?php

return [
  0 =>
    [
      'name' => 'Cron:CiviRuleAction.Process',
      'entity' => 'Job',
      'params' =>
        [
          'version' => 3,
          'name' => 'Process delayed civirule actions',
          'description' => '',
          'run_frequency' => 'Always',
          'api_entity' => 'CiviRulesAction',
          'api_action' => 'Process',
          'parameters' => '',
          'is_active' => '1',
        ],
    ],
];