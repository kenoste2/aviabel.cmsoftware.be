<?php

require_once 'application/models/Base.php';

class Application_Model_Languages extends Application_Model_Base {

    public function getLanguages()
    {
        $results = $this->db->get_results("select LANGUAGE_ID, DESCRIPTION from SUPPORT\$LANGUAGES",ARRAY_N);
        return $results;
    }

    public function getLanguageById($languageId) {
        $escLanguageId = $this->db->escape($languageId);
        return $this->db->get_row("select LANGUAGE_ID, DESCRIPTION from SUPPORT\$LANGUAGES WHERE LANGUAGE_ID = {$escLanguageId}");
    }

    public function getShortLanguageStringById($languageId)
    {
        $language = $this->getLanguageById($languageId);
        $mapping = array('DUTCH' => 'NL', 'FRENCH' => 'FR', 'ENGLISH' => 'EN', 'GERMAN' => 'DE');
        if(in_array($language->DESCRIPTION, $mapping)) {
            return $mapping[$language->DESCRIPTION];
        }
        return 'NL';
    }

    public function getIdByCode($code)
    {
        switch($code) {
            case "NL":
            case "NED":
            case "DUTCH":
                $matchCode = "DUTCH";
                break;
            case "FR":
            case "FRA":
            case "FRENCH":
                $matchCode = "FRENCH";
                break;
            case "EN":
            case "ENG":
            case "ENGLISH":
                $matchCode = "ENGLISH";
                break;
            case "DE":
            case "DU":
            case "GER":
            case "GERMAN":
                $matchCode = "ENGLISH";
                break;

            default:
                $matchCode = "DUTCH";
                break;
        }

        $id = $this->db->get_var("SELECT LANGUAGE_ID FROM SUPPORT\$LANGUAGES WHERE CODE='{$matchCode}'");

        return $id;

    }

}

?>
