<?php

class CRM_CivirulesActions_Activity_PostalCodeAssign extends CRM_Civirules_Action {
    private $apiUrl;
    private $apiKey;

    public function __construct() {
        // Load configuration
        $config = include('config.php');

        // API configuration
        $apiConfig = $config['api'];
        $this->apiUrl = $apiConfig['url'];
        $this->apiKey = $apiConfig['key'];
    }

    public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
        //$contactId = $triggerData->getContactId();
        $contactId = 8;
        $postalCode = $this->getPostalCode($contactId);

        if ($postalCode && $this->isPostalCodeInDatabase($postalCode)) {
            Civi::log()->info("Postal code {$postalCode} found in database");
            return true;
        }
        Civi::log()->error("Postal code not found for contactId: {$contactId}");
        return false;
    }

    private function getPostalCode($contactId) {
        $url = $this->apiUrl . '/Address/get';
        $params = [
            'select' => ['postal_code'],
            'where' => [['contact_id', '=', $contactId], ['contact_id.contact_sub_type', '=', 'senior']],
            'orderBy' => ['id' => 'DESC'],
            'limit' => 1,
        ];
        $request = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => [
                    'Content-Type: application/x-www-form-urlencoded',
                    'X-Civi-Auth: Bearer ' . $this->apiKey,
                ],
                'content' => http_build_query(['params' => json_encode($params)]),
            ]
        ]);

        $response = file_get_contents($url, FALSE, $request);
        if ($response === FALSE) {
            Civi::log()->error("Failed to fetch postal code data from API");
            return null;
        }

        $data = json_decode($response, TRUE);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Civi::log()->error("JSON decode error: " . json_last_error_msg());
            return null;
        }

        if (!empty($data['values'])) {
            return $data['values'][0]['postal_code'];
        }
        return null;
    }

    private function isPostalCodeInDatabase($postalCode) {
        $dao = new CRM_PostalcodeRange_DAO_AacPostal();
        $dao->postal_code = $postalCode;
        if ($dao->find(TRUE)) {
            return true;
        }
        return false;
    }

    public function getExtraDataInputUrl($ruleActionId) {
        return FALSE;
    }

    public function getLabel() {
        return ts('Check Postal Code in Database');
    }

    public function getDescription() {
        return ts('Checks if the contact postal code is present in the database.');
    }
}
