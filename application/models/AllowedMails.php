<?php

require_once 'application/models/Base.php';

class Application_Model_AllowedMails extends Application_Model_Base
{
    public function getSettingsAllowedMails()
    {
        return $this->db->get_results("select * from ALLOWEDMAILS order by NAME,EMAIL");
    }

    public function add($data)
    {
        $data['CREATION_USER'] = $this->online_user;
        $data['CREATION_DATE'] = date("Y-m-d");

        return $this->addData("ALLOWEDMAILS", $data);
    }

    public function delete($email)
    {
        return $this->db->query("DELETE FROM ALLOWEDMAILS WHERE EMAIL='{$email}'");
    }

    public function emailExists($email) {

        $exists = $this->db->get_var("SELECT COUNT(*) FROM ALLOWEDMAILS WHERE EMAIL = '{$email}'");

        if (!empty($exists)) {
            return true;
        } else {
            return false;
        }

    }

    public function getFileAllowedMails($fileId) {

        $fileObj = new Application_Model_File();
        $debtorEmail = $fileObj->getFileField($fileId, "DEBTOR_E_MAIL");
        $clientId = $fileObj->getFileField($fileId, "CLIENT_ID");
        $clientObj = new Application_Model_Clients();
        $clientEmail = $clientObj->getClientField($clientId, "E_MAIL");

        $list =  array($debtorEmail => $debtorEmail. " (".$this->functions->T("debtor_c").")" ,$clientEmail => $clientEmail . " (".$this->functions->T("client_c").")");

        $usersObj = new Application_Model_Users();

        $users = $usersObj->getUsers();
        foreach ($users as $user) {
            if (!empty($user->E_MAIL) && $user->RIGHTS !=5) {
                $list[$user->EMAIL] = $user->EMAIL;
            }
        }

        $allowedMails = $this->getSettingsAllowedMails();
        if (!empty($allowedMails)) {
            foreach ($allowedMails as $mail) {
                $list[] = $mail->EMAIL;
            }
        }

        return $list;

    }
}

?>
