<?php

require_once 'application/models/Base.php';

class Application_Model_Templates extends Application_Model_Base
{
    public function getTemplates()
    {
        return $this->db->get_results("select * from SYSTEM\$TEMPLATES where VISIBLE='Y' order by CODE");
    }

    public function getTemplate($template_id)
    {
        return $this->db->get_row("SELECT * FROM SYSTEM\$TEMPLATES WHERE TEMPLATE_ID = " . $template_id);
    }

    public function add($data)
    {
        $data['SQL_FOR_MERGE'] = "SELECT * FROM FILES\$FILES_ALL_INFO";

        if (empty($data['ACTION_ID'])) {
            $data['ACTION_ID'] = 0;
        }

        if (array_key_exists('TEMPLATE_MODULES', $data) && !empty($data['TEMPLATE_MODULES'])) {
            $data['TEMPLATE_MODULES'] = implode(',', $data['TEMPLATE_MODULES']);
        }

        return $this->addData("SYSTEM\$TEMPLATES", $data);
    }

    public function save($data, $where)
    {
        if (empty($data['ACTION_ID'])) {
            $data['ACTION_ID'] = 0;
        }

        if (array_key_exists('TEMPLATE_MODULES', $data) && !empty($data['TEMPLATE_MODULES'])) {
            $data['TEMPLATE_MODULES'] = implode(',', $data['TEMPLATE_MODULES']);
        }

        return $this->saveData("SYSTEM\$TEMPLATES", $data, $where);
    }

    public function delete($id)
    {
        return $this->db->query("update SYSTEM\$TEMPLATES set VISIBLE='N' where TEMPLATE_ID='$id'");
    }

    public function getTemplatesForSelect()
    {
        return $this->db->get_results("select TEMPLATE_ID, DESCRIPTION from SYSTEM\$TEMPLATES where VISIBLE='Y' order by CODE", ARRAY_N);
    }

    public function getTemplateDescription($templateId)
    {
        return $this->db->get_var("select DESCRIPTION from SYSTEM\$TEMPLATES where TEMPLATE_ID = {$templateId}");
    }


    public function excludeDispute($templateId)
    {
        global $config;

        if ($config->disputeTemplates) {

            $array = explode(",",$config->disputeTemplates);

            if (in_array($templateId, $array)) {
                return false;
            } else {
                return true;
            }
        }

        return true;
    }

    public function getTemplateContent($templateId, $lang)
    {
        $data['CONTENT'] = $this->db->get_var("SELECT TEXT_{$lang} FROM SYSTEM\$TEMPLATES
                   WHERE TEMPLATE_ID = {$templateId} ");

        $data['CONTENT'] = str_replace("€","EURO",$data['CONTENT']);
        $data['CONTENT'] = str_replace("&'128;","EURO",$data['CONTENT']);
        return $data;
    }

    public function getTemplateModules($templateId)
    {
        $templateModules = $this->db->get_var("select TEMPLATE_MODULES from SYSTEM\$TEMPLATES where TEMPLATE_ID={$templateId}");
        $templateModules = explode(",", $templateModules);
        $data = array();
        if ($templateModules) {
            foreach ($templateModules as $module) {
                 switch ($module) {
                    case '5':
                        $type = "PaymentForm";
                        break;
                    case '6':
                        $type = "Invoices";
                        break;
                    default :
                        $type = "";

                }
                if (!empty($type)) {
                    $data[] = $type;
                }
            }
        }
        return $data;
    }
    public function checkIsDeletable($id)
    {
        return true;
    }

    public function getByCode($code)
    {
        return $this->db->get_row("select TEMPLATE_ID, CODE from SYSTEM\$TEMPLATES where CODE = '{$code}'", ARRAY_A);
    }



}

?>
