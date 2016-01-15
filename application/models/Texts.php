<?php

require_once 'application/models/Base.php';

class Application_Model_Texts extends Application_Model_Base
{
    public function getTexts($search)
    {
        $extra_query = "";
        if (!empty($search)) {
            $extra_query = "AND (NAV containing '$search' OR CODE containing '$search' OR NL containing '$search' or FR containing '$search' OR EN containing '$search') ";
        }

        return $this->db->get_results("select * from TEKSTEN WHERE SETTINGS =  1 $extra_query order by NAV,CODE,NL");
    }

    public function getText($text_id)
    {
        return $this->db->get_row("select * from TEKSTEN WHERE TEKSTEN_ID = " . $text_id);
    }

    public function add($data)
    {
        $data['SETTINGS'] = '1';
        $data['TEKSTEN_ID'] = $this->getNextId();
        return $this->addData("TEKSTEN", $data);
    }

    public function save($data, $where)
    {
        $data['SETTINGS'] = '1';
       return $this->saveData("TEKSTEN", $data, $where);
    }

    public function delete($id)
    {
        return $this->db->query("delete from TEKSTEN WHERE TEKSTEN_ID=$id");
    }

    public function getTextsForSelect()
    {
        return $this->db->get_results("select TEKSTEN_ID, DESCRIPTION from TEKSTEN WHERE SETTINGS=1 order by NAV,CODE,NL", ARRAY_N);
    }

    public function getNextId() {

        $textenId = $this->db->get_var("SELECT MAX(TEKSTEN_ID) FROM TEKSTEN");
        if (empty($textenId)) {
            $textenId = 0;
        }
        $textenId++;

        return $textenId;
    }

    public function checkIsDeletable($id)
    {
        return true;
    }
}

?>
