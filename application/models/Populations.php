<?php

require_once 'application/models/Base.php';

class Application_Model_Populations extends Application_Model_Base
{
    public function getSettingPopulation()
    {
        return $this->db->get_results("select * from SUPPORT\$POPULATION_PLACES order by ZIP_CODE");
    }

    public function getSetting($population_id)
    {
        return $this->db->get_row("SELECT * FROM SUPPORT\$POPULATION_PLACES WHERE POPULATION_PLACE_ID = " . $population_id);
    }

    public function add($data)
    {
        if (empty($data['AMOUNT'])) {
            $data['AMOUNT'] = 0;
        }

        if(array_key_exists('AMOUNT', $data) && !empty($data['AMOUNT'])) {
            $data['AMOUNT']  = $this->functions->dbBedrag($data['AMOUNT']);
        }

        $data['PAYEMENT_TYPE_ID'] = 3;
        $data['AMOUNT2'] = $data['AMOUNT'];
        $data['ACCOUNT_NO2'] = $data['ACCOUNT_NO'];

        return $this->addData("SUPPORT\$POPULATION_PLACES", $data);
    }

    public function save($data, $where)
    {
        if(array_key_exists('AMOUNT', $data) && !empty($data['AMOUNT'])) {
            $data['AMOUNT']  = $this->functions->dbBedrag($data['AMOUNT']);
        }

        return $this->saveData("SUPPORT\$POPULATION_PLACES", $data, $where);
    }

    public function delete($id)
    {
        return $this->db->query("delete from SUPPORT\$POPULATION_PLACES where POPULATION_PLACE_ID='$id'");
    }

    public function getPopulationsForSelect()
    {
        return $this->db->get_results("select POPULATION_PLACE_ID, (ZIP_CODE || ' - ' || NAME||' ('||AMOUNT||')') AS DESCRIPTION from SUPPORT\$POPULATION_PLACES order by ZIP_CODE", ARRAY_N);
    }

    public function checkIsDeletable($id)
    {
        $results = $this->db->get_results("select count(*) from SUPPORT\$ZIP_CODES WHERE POPULATION_PLACE_ID = '$id'");

        if ($results[0]->COUNT > 0) {
            return false;
        } else {
            return true;
        }
    }
}

?>
