<?php

require_once 'application/models/Base.php';

class Application_Model_FilesTypes extends Application_Model_Base
{
    public function getFileTypeByAndClient($code = false, $clientId)
    {
        if (empty($code)) {
            return $this->db->get_var("SELECT TYPE_ID FROM CLIENTS\$FILE_TYPES WHERE CLIENT_ID = '$clientId'");
        } else {
            return $this->db->get_var("SELECT TYPE_ID FROM CLIENTS\$FILE_TYPES WHERE CODE = '$code' AND CLIENT_ID = '$clientId'");
        }

    }



    public function createFileType ($clientId, $code, $description)
    {
        $data = array(
            'CLIENT_ID' => $clientId,
            'CODE' => $code,
            'DESCRIPTION' => $description,
            'VISIBLE' => 'Y',
            'MODIFIED' => date("Y-m-d"),
            'MODIFIEDBY' => $this->online_user
        );
        $typeId = $this->addData('CLIENTS$FILE_TYPES', $data, 'TYPE_ID');
        return $typeId;
    }
}

?>
