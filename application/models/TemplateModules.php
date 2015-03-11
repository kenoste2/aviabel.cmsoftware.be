<?php

require_once 'application/models/Base.php';

class Application_Model_TemplateModules extends Application_Model_Base
{
    public $MAIL_MODULE = 10;

    public $SMS_MODULE = 11;

    public function getModules()
    {
        return $this->db->get_results("select * from SYSTEM\$TEMPLATES_MODULES where ACTIEF='1'");
    }

    public function getModulesForTemplate($templateId) {
        $escTemplateId = $this->db->escape($templateId);
        $modulesStr =  $this->db->get_var("select TEMPLATE_MODULES from SYSTEM\$TEMPLATES where TEMPLATE_ID = $escTemplateId");

        //NOTE: ignore empty or illegal values
        $moduleIds = explode(',', $modulesStr);
        $escModuleIds = array();
        foreach($moduleIds as $moduleId) {
            if(preg_match("/^\\d+$/", $moduleId)) {
                $escModuleIds []= $this->db->escape($moduleId);
            }
        }

        if(count($escModuleIds) > 1) {
            $escModulesStr = implode(',', $escModuleIds);
            $modules = $this->db->get_results("select ID from SYSTEM\$TEMPLATES_MODULES where ACTIEF = 1 AND ID IN ({$escModulesStr})");
            $moduleIds = array();
            if(count($modules) > 0) {
                foreach($modules as $module) {
                    $moduleIds []= $module->ID;
                }
            }
            return $moduleIds;
        }
        return array();
    }
}

?>
