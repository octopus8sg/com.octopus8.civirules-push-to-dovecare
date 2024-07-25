<?php

class CRM_CivirulesConditions_Contact_PostalCodeExists extends CRM_Civirules_Condition {
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

    /**
     * Returns a redirect url to extra data input from the user after adding a condition
     * @param int $ruleConditionId
     * @return bool|string
     */
    public function getExtraDataInputUrl($ruleConditionId) {
        return FALSE;
    }

    /**
     * Method is mandatory and checks if the condition is met
     * @param CRM_Civirules_TriggerData_TriggerData $triggerData
     * @return bool
     */
    public function isConditionValid(CRM_Civirules_TriggerData_TriggerData $triggerData) {
        $contactId = $triggerData->getContactId();
        $postalCode = $this->getPostalCode($contactId);

        if ($postalCode && $this->isPostalCodeInDatabase($postalCode)) {
            Civi::log()->info("Postal code {$postalCode} found in database for contactId: {$contactId}");
            return TRUE;
        }

        Civi::log()->error("Postal code not found in database for contactId: {$contactId}");
        return FALSE;
    }

    /**
     * Fetches the postal code of the contact.
     * @param int $contactId
     * @return string|null
     */
    private function getPostalCode($contactId) {
        $url = $this->apiUrl . '/Address/get';
        $params = [
            'select' => ['postal_code'],
            'where' => [['contact_id', '=', $contactId], ['contact_id.contact_sub_type', '=', 'senior']],
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

    /**
     * Checks if the given postal code exists in the database.
     * @param string $postalCode
     * @return bool
     */
    private function isPostalCodeInDatabase($postalCode) {
        $dao = new CRM_PostalcodeRange_DAO_AacPostal();
        $dao->postal_code = $postalCode;
        return $dao->find(TRUE);
    }

    /**
     * Get the label for this condition
     * @return string
     */
    public function getLabel() {
        return ts('Check Postal Code in Database');
    }

    /**
     * Get the description for this condition
     * @return string
     */
    public function getDescription() {
        return ts('Checks if the contact postal code is present in the database.');
    }

    /**
     * This function validates whether this condition works with the selected trigger.
     * @param CRM_Civirules_Trigger $trigger
     * @param CRM_Civirules_BAO_Rule $rule
     * @return bool
     */
    public function doesWorkWithTrigger(CRM_Civirules_Trigger $trigger, CRM_Civirules_BAO_Rule $rule) {
        return $trigger->doesProvideEntity('Contact');
    }
}
?>
