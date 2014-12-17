<?php

require_once 'application/models/Base.php';

class Application_Model_Countries extends Application_Model_Base {

    public function getCountries()
    {
        $results = $this->db->get_results("select COUNTRY_ID,DESCRIPTION from SUPPORT\$COUNTRIES  order by DESCRIPTION", ARRAY_N);
        return $results;
    }

    public function getAllCountries()
    {
        return $this->db->get_results("select * from SUPPORT\$COUNTRIES");
    }

    public function getSetting($country_id)
    {
        return $this->db->get_row("SELECT * FROM SUPPORT\$COUNTRIES WHERE COUNTRY_ID = " . $country_id);
    }

    public function getCountryByCode($code)
    {
        return $this->db->get_var("SELECT COUNTRY_ID FROM SUPPORT\$COUNTRIES WHERE CODE='" . $code . "'");
    }

    public function getCountryCodeById($id)
    {
        return $this->db->get_var("SELECT CODE FROM SUPPORT\$COUNTRIES WHERE COUNTRY_ID='" . $id . "'");
    }


    public function add($data)
    {
        $data['TELEPHONE_CODE'] = "000";
        if(empty($data['DELTA'])) {
            $data['DELTA'] = 0;
        }

        if(array_key_exists('DELTA', $data) && !empty($data['DELTA'])) {
            $data['DELTA']  = $this->functions->dbBedrag($data['DELTA']);
        }

        return $this->addData("SUPPORT\$COUNTRIES", $data, 'COUNTRY_ID');
    }

    public function save($data, $where)
    {
        if(empty($data['DELTA'])) {
            $data['DELTA'] = 0;
        }

        if(array_key_exists('DELTA', $data) && !empty($data['DELTA'])) {
            $data['DELTA']  = $this->functions->dbBedrag($data['DELTA']);
        }

        return $this->saveData("SUPPORT\$COUNTRIES", $data, $where);
    }

    public function delete($id)
    {
        return $this->db->query("delete from SUPPORT\$COUNTRIES where COUNTRY_ID='$id'");
    }

    public function getCountriesForSelect()
    {
        return $this->db->get_results("select COUNTRY_ID, (CODE || ' - ' || DESCRIPTION) AS DESCRIPTION from SUPPORT\$COUNTRIES order by DESCRIPTION", ARRAY_N);
    }

    public function checkIsDeletable($id)
    {
        $results = $this->db->get_results("select count(*) from SUPPORT\$ZIP_CODES WHERE COUNTRY_ID = '$id'");

        if ($results[0]->COUNT > 0) {
            return false;
        } else {
            return true;
        }
    }



}

?>
