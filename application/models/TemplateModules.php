<?php

require_once 'application/models/Base.php';

class Application_Model_TemplateModules extends Application_Model_Base
{
    public function getModules()
    {
        return $this->db->get_results("select * from SYSTEM\$TEMPLATES_MODULES where ACTIEF='1'");
    }
}

?>
