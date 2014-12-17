<?php

require_once 'application/models/Base.php';

class Application_Model_Language extends Application_Model_Base {

    public function getLanguages()
    {
        //select DESCRIPTION from SUPPORT\$LANGUAGES where LANGUAGE_ID='$row->LANGUAGE_CODE_ID'
        $results = $this->db->get_results("select LANGUAGE_ID, DESCRIPTION from SUPPORT\$LANGUAGES",ARRAY_N);
        return $results;
    }

}

?>
