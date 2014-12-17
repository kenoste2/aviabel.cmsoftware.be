<?php

require_once 'application/models/Base.php';

class Application_Model_Languages extends Application_Model_Base {

    public function getLanguages()
    {
        $results = $this->db->get_results("select LANGUAGE_ID, DESCRIPTION from SUPPORT\$LANGUAGES",ARRAY_N);
        return $results;
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
