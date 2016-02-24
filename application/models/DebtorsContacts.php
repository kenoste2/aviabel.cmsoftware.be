<?php

require_once 'application/models/Base.php';

class Application_Model_DebtorsContacts extends Application_Model_Base {

    public function getDebtorContacts($debtorId)
    {
        $results = $this->db->get_results("select A.CONTACT_ID,A.NAME,A.ADDRESS,A.EMAIL,A.FUNCTION_DESCRIPTION,A.TEL,A.FAX,B.DESCRIPTION AS LANGUAGE 
            from DEBTORS\$CONTACTS A
            JOIN SUPPORT\$LANGUAGES B ON A.LANGUAGE_CODE_ID = B.LANGUAGE_ID 
            where A.DEBTOR_ID='{$debtorId}' AND A.VISIBLE='Y'");
        return $results;
    }

    public function getContact($contact_id)
    {
        return $this->db->get_row("SELECT * FROM DEBTORS\$CONTACTS WHERE CONTACT_ID = " . $contact_id);
    }

    public function add($data)
    {
        if(!$this->checkContactExists($data['NAME'], $data['ADDRESS'], $data['EMAIL'], $data['TEL'], $data['FAX'],
            $data['LANGUAGE_CODE_ID'], $data['DEBTOR_ID'], $data['FUNCTION_DESCRIPTION'])) {
            $data['MODIFIEDBY'] = $this->online_user;
            $this->addData('DEBTORS$CONTACTS', $data);
            return true;
        }
        return false;
    }

    public function save($data, $where)
    {
        $data['MODIFIEDBY'] = $this->online_user;
        $this->saveData('DEBTORS$CONTACTS', $data, $where);
    }

    public function checkContactExists($name, $address, $email, $tel, $fax, $language_code_id, $debtor_id, $function_description)
    {
        $results = $this->db->get_results("select count(*) from DEBTORS\$CONTACTS where NAME='" . $name . "' and ADDRESS = '" . $address . "' and  EMAIL='" . $email ."' and TEL='" . $tel . "' and FAX='" . $fax . "' and LANGUAGE_CODE_ID='" . $language_code_id . "' and DEBTOR_ID='".$debtor_id."' and FUNCTION_DESCRIPTION='" . $function_description . "'");

        if ($results[0]->COUNT) {
            return true;
        } else {
            return false;
        }
    }

    public function delete($contact_id)
    {
        $this->db->query("UPDATE DEBTORS\$CONTACTS SET VISIBLE='N',MODIFIED=CURRENT_DATE,MODIFIEDBY='{$this->online_user}' where CONTACT_ID='$contact_id'");
    }
}

?>
