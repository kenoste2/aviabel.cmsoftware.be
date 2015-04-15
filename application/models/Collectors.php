<?php

require_once 'application/models/Base.php';

class Application_Model_Collectors extends Application_Model_Base 
{


    public function getCollectors()
    {
        return $this->db->get_results("select * from SYSTEM\$COLLECTORS where ACTIF='Y'  order by SYSTEM_DEFAULT DESC, NAME");
    }

    public function getCollector($collector_id)
    {
        return $this->db->get_row("SELECT C.*, Z.CODE AS ZIP_CODE, Z.CITY, Z.COUNTRY_ID
            FROM SYSTEM\$COLLECTORS C
            LEFT JOIN SUPPORT\$ZIP_CODES Z ON Z.ZIP_CODE_ID = C.ZIP_CODE_ID
            WHERE C.COLLECTOR_ID = " . $collector_id);
    }

    public function getCollectorByFileId($fileId) {
        $escFileId = $this->db->escape($fileId);
        $sql = "SELECT C.*, Z.CODE AS ZIP_CODE, Z.CITY, Z.COUNTRY_ID,
                    (SELECT FIRST 1 CODE FROM SUPPORT\$LANGUAGES
                      WHERE LANGUAGE_ID = C.LANGUAGE_ID) AS LANGUAGE_CODE
                FROM SYSTEM\$COLLECTORS C
                LEFT JOIN SUPPORT\$ZIP_CODES Z ON Z.ZIP_CODE_ID = C.ZIP_CODE_ID
                WHERE C.COLLECTOR_ID IN
                    (SELECT F.COLLECTOR_ID FROM FILES\$FILES F
                    WHERE F.FILE_ID = {$escFileId})";
        return $this->db->get_row($sql);
    }

    public function add($data)
    {
        $data['ACCOUNT_ID'] = '1';
        $data['COMMISSION'] = '0';
        $data['FORFAIT'] = '0';

        $data = $this->setZipcodeId($data);

        return $this->addData("SYSTEM\$COLLECTORS", $data);
    }

    public function save($data, $where)
    {
        $data = $this->setZipcodeId($data);

        return $this->saveData("SYSTEM\$COLLECTORS", $data, $where);
    }

    public function delete($id)
    {
        return $this->db->query("update SYSTEM\$COLLECTORS set ACTIF='N' where COLLECTOR_ID='$id'");
    }
    
    public function getCollectorsForSelect()
    {
        return $this->db->get_results("select COLLECTOR_ID,NAME FROM SYSTEM\$COLLECTORS where ACTIF='Y' AND COALESCE(EXTERN, 0) = 0 order by NAME", ARRAY_N);
    }

    public function getExternalCollectorsForSelect()
    {
        return $this->db->get_results("select COLLECTOR_ID,NAME FROM SYSTEM\$COLLECTORS where ACTIF='Y' AND EXTERN = 1 order by NAME", ARRAY_N);
    }

    public function checkIsDeletable($id)
    {
        $results1 = $this->db->get_results("select count(*) from SUPPORT\$ZIP_CODES WHERE COLLECTOR_ID = '$id'");
        $results2 = $this->db->get_results("select count(*) from SYSTEM\$USERS WHERE COLLECTOR_ID = '$id'");

        if ($results1[0]->COUNT > 0 || $results2[0]->COUNT > 0) {
            return false;
        } else {
            return true;
        }
    }

    public function getBaseCollectorId()
    {
        return $this->db->get_var("SELECT COLLECTOR_ID FROM SYSTEM\$COLLECTORS WHERE SYSTEM_DEFAULT = 1");
    }


    protected function setZipcodeId(array $data)
    {
        $zipCodesModel = new Application_Model_ZipCodes();
        $zipCodeId = $zipCodesModel->CheckOrCreate($data);

        $data['ZIP_CODE_ID'] = $zipCodeId;
        unset($data['ZIP_CODE']);
        unset($data['CITY']);
        unset($data['COUNTRY_ID']);

        return $data;
    }

}

?>
