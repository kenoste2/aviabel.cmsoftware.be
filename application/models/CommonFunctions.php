<?php

class Application_Model_CommonFunctions
{

    public function date_dbformat($variable)
    {
        if (strlen($variable) >= 6) {


            if (stripos($variable, "-")) {
                list($day, $month, $year) = explode("-", $variable);
                if (strlen($year) <= 2) {
                    $year = $year * 1 + 2000;
                }
            } else {
                list($day, $month, $year) = explode("/", $variable);
                if (strlen($year) <= 2) {
                    $year = $year * 1 + 2000;
                }
            }

            if ($day <= 31 and $month <= 12 and $year >= 1900) {
                return "$year-$month-$day";
            } else
                return "";
        } else
            return "";
    }

    public function dateformat($variable)
    {
        if (strlen($variable) > 8) {

            if (stripos($variable, "-"))
                list($year, $month, $day) = explode("-", $variable);
            else
                list($year, $month, $day) = explode("/", $variable);
            if ($day <= 31 and $day >= 1 and $month <= 12 and $month >= 1 and $year >= 1900) {
                return "$day/$month/$year";
            } else
                return "";
        } else
            return "";
    }

    public function db2array($data, $empty = true)
    {

        if ($empty === true) {
            $resultArray = array("" => "-");
        } else {
            $resultArray = array();
        }
        if (!empty($data)) {
            foreach ($data as $row) {
                $resultArray[$row[0]] = $row[1];
            }
        }

        return $resultArray;
    }

    public function T($code,$otherLang = false, $decodeUtf8 = false)
    {
        global $db;
        global $lang;

        if (!empty($otherLang)) {
            $lang = $otherLang;
        }

        if ($lang == "DE") {
            $lang = "GE";
        }

        $tekst = $db->get_var("SELECT {$lang} FROM TEKSTEN WHERE CODE = '{$code}'");


        if (!empty($decodeUtf8)) {
            $tekst = utf8_decode($tekst);
        }

        if (empty($tekst) && $db->get_var("SELECT COUNT(*) FROM TEKSTEN WHERE CODE = '{$code}'") == 0 ){
            $tekst = $code;
        }
        return $tekst;
    }

    public function dbBedrag($bedrag, $format = false)
    {

        switch ($format) {
            case 'US':
                return $this->_dbBedragUs($bedrag);
                break;

            case 'EU':
            default :
                return $this->_dbBedragEu($bedrag);
                break;
        }

    }

    protected  function _dbBedragEu($bedrag)
    {
        $bedrag = str_replace(".", "", $bedrag);
        $bedrag = str_replace(",", ".", $bedrag);
        $bedrag = str_replace("€", "", $bedrag);
        $bedrag = str_replace(" ", "", $bedrag);
        if ($bedrag == "") {
            $bedrag = 0;
        }
        return $bedrag;
    }

    protected function _dbBedragUs($bedrag)
    {
        $bedrag = str_replace(",", "", $bedrag);
        $bedrag = str_replace(" ", "", $bedrag);
        if ($bedrag == "") {
            $bedrag = 0;
        }
        return $bedrag;
    }



    public function amount($bedrag)
    {
        $bedrag = number_format($bedrag, 2, ',', '.');
        return $bedrag;
    }

    public function getSetting($code)
    {
        global $db;
        return $db->get_var("select WAARDE from CMS_INSTELLINGEN where CODE='{$code}'");
    }

    public function getUserSetting($code,$otherLang = false)
    {
        global $db;
        global $lang;

        if (!empty($otherLang)) {
            $lang = $otherLang;
        }

        if ($lang == "DE") {
            $lang = "GE";
        }


        $tekst = $db->get_var("SELECT {$lang} FROM TEKSTEN WHERE CODE = '{$code}' AND SETTINGS = 1");
        return $tekst;
    }


    function saveData($tableName, $data, $where = false, $returnField = false, $escapeSql = false)
    {
        global $db;
        $dataSql = "";
        $fields = array_keys($data);
        if (empty($where)) {
            $fieldSql = implode(",", $fields);
            $dataSql .= "#|" . implode("#|,#|", $data) . "#|";
            $dataSql = str_replace("'", "`", $dataSql);
            $dataSql = str_replace("#|", "'", $dataSql);
            $sql = "INSERT INTO {$tableName} ({$fieldSql}) VALUES ({$dataSql})";
            if (!empty($returnField)) {
                $sql .= " RETURNING {$returnField}";
            }
        } else {
            $fieldSql = "";
            foreach ($fields as $field) {
                if ($data[$field] !== null) {
                    $fieldSql .= $field . "='" . str_replace("'", "`", $data[$field]) . "',";
                } else {
                    $fieldSql .= $field . "= null ,";
                }
            }
            $fieldSql = substr($fieldSql, 0, -1);
            $sql = "UPDATE {$tableName} SET {$fieldSql} WHERE {$where}";
        }

        return $db->get_var($sql);
    }

    function date_diff($date1, $date2)
    {
        $diff = round((strtotime("$date2 23:59:59") - strtotime("$date1 00:00:00")) / 86400);
        return $diff;
    }

    function validatePassword($password1, $password2)
    {
        if ($password1 != $password2) {
            return "NOTSAME";
        }

        if (strlen($password1) <= 5) {
            return "MIN5";
        }

        if (empty($password2)) {
            return "2ND";
        }

        return true;
    }


    function langToCode($lang)
    {
        switch ($lang) {
            case 'FRENCH':
                $code = 'FR';
                break;
            case 'ENGLISH':
                $code = 'EN';
                break;
            case 'GERMAN':
                $code = 'DE';
                break;
            default:
                $code = 'NL';
                break;
        }
        return $code;
    }

}

?>
