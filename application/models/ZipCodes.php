<?php

require_once 'application/models/Base.php';

class Application_Model_ZipCodes extends Application_Model_Base {

    public function getZipcodes()
    {
        return $this->db->get_results("select Z.ZIP_CODE_ID, Z.POPULATION_PLACE_ID,Z.COUNTRY_ID,Z.CODE,Z.CITY,Z.CITY_DUTCH,Z.CITY_FRENCH,Z.CITY_ENGLISH,
            C.CODE AS COUNTRY_CODE, C.DESCRIPTION AS COUNTRY_DESCRIPTION, C2.NAME AS COLLECTOR_NAME
            from SUPPORT\$ZIP_CODES Z
            LEFT JOIN SUPPORT\$COUNTRIES C ON C.COUNTRY_ID = Z.COUNTRY_ID
            LEFT JOIN SYSTEM\$COLLECTORS C2 ON C2.COLLECTOR_ID = Z.COLLECTOR_ID
            order by CODE");
    }

    public function getSetting($zipcode_id)
    {
        return $this->db->get_row("SELECT * FROM SUPPORT\$ZIP_CODES WHERE ZIP_CODE_ID = " . $zipcode_id);
    }

    public function getZipcodeByCodeAndDutchName($code, $name)
    {
        return $this->db->get_var("select ZIP_CODE_ID from SUPPORT\$ZIP_CODES where CODE='" . $code . "' AND CITY_DUTCH = '" . $name . "'");
    }

    public function getZipcodeByDutchNameAndCountry($name, $countryId)
    {
        return $this->db->get_var("select ZIP_CODE_ID from SUPPORT\$ZIP_CODES where CITY_DUTCH = '" . $name . "' AND COUNTRY_ID = {$countryId}");
    }

    public function create($data) {
        $data = $this->setDefaults($data);

        $sql = "INSERT INTO SUPPORT\$ZIP_CODES (COUNTRY_ID,CODE,CITY,CREATION_USER,CREATION_DATE,CITY_FRENCH,CITY_DUTCH,CITY_ENGLISH, POPULATION_PLACE_ID, COLLECTOR_ID)
                        VALUES
                        ({$data['COUNTRY_ID']},'{$data['ZIP_CODE']}','{$data['CITY']}','{$this->online_user}',CURRENT_DATE,'{$data['CITY_FRENCH']}','{$data['CITY_DUTCH']}','{$data['CITY_ENGLISH']}', '{$data['POPULATION_PLACE_ID']}', '{$data['COLLECTOR_ID']}')
                        RETURNING ZIP_CODE_ID";
        $id = $this->db->get_var($sql);
        return $id;
    }

    public function CheckOrCreate($data) {
        $sql = "select ZIP_CODE_ID from SUPPORT\$ZIP_CODES where COUNTRY_ID = {$data['COUNTRY_ID']} AND CODE='{$data['ZIP_CODE']}' AND CITY = '{$data['CITY']}'";
        $id = $this->db->get_var($sql);
        if (empty($id)) {
            $id = $this->create($data);
        }
        return $id;
    }

    public function save($data, $where)
    {
        $data = $this->setDefaults($data);
        unset($data['ZIP_CODE']);
        unset($data['NEW_COUNTRY_CODE']);
        unset($data['NEW_COUNTRY_DESCRIPTION']);
        return $this->saveData("SUPPORT\$ZIP_CODES", $data, $where);
    }

    public function delete($id)
    {
        return $this->db->query("delete from SUPPORT\$ZIP_CODES where ZIP_CODE_ID='$id'");
    }

    public function getZipcodesForSelect()
    {
        return $this->db->get_results("select ZIP_CODE_ID, CITY from SUPPORT\$ZIP_CODES", ARRAY_N);
    }

    public function checkIsDeletable($id)
    {
        $results1 = $this->db->get_var("select count(*) from FILES\$DEBTORS WHERE ZIP_CODE_ID = '$id'");
        $results2 = $this->db->get_var("select count(*) from CLIENTS\$CLIENTS WHERE ZIP_CODE_ID = '$id'");
        $results3 = $this->db->get_var("select count(*) from SYSTEM\$COLLECTORS WHERE ZIP_CODE_ID = '$id'");
        $results4 = $this->db->get_var("select count(*) from SYSTEM\$USERS WHERE ZIP_CODE_ID = '$id'");

        if ($results1 > 0 || $results2 > 0 || $results3 > 0 || $results4 > 0) {
            return false;
        } else {
            return true;
        }
    }

    protected function setDefaults(array $data)
    {
        if (empty($data['POPULATION_PLACE_ID'])) {
            $data['POPULATION_PLACE_ID'] = 0;
        }

        if (empty($data['COLLECTOR_ID'])) {
            $data['COLLECTOR_ID'] = 1;
        }

        if (empty($data['CITY'])) {
            $data['CITY'] = $data['CITY_DUTCH'];
        }

        if (empty($data['CITY_DUTCH'])) {
            $data['CITY_DUTCH'] = $data['CITY'];
        }

        if (empty($data['CITY_FRENCH'])) {
            $data['CITY_FRENCH'] = $data['CITY'];
        }

        if (empty($data['CITY_ENGLISH'])) {
            $data['CITY_ENGLISH'] = $data['CITY'];
        }

        return $data;
    }

}

?>
