<?php

require_once 'application/models/Base.php';

class Application_Model_ClientsContacts extends Application_Model_Base {

    public function getClientContacts($clientId)
    {
        $results = $this->db->get_results("select A.CONTACT_ID,A.NAME,A.EMAIL,A.FUNCTION_DESCRIPTION,A.TEL,A.FAX,B.DESCRIPTION AS LANGUAGE 
            from CLIENTS\$CONTACTS A
            JOIN SUPPORT\$LANGUAGES B ON A.LANGUAGE_CODE_ID = B.LANGUAGE_ID 
            where A.CLIENT_ID='{$clientId}' AND A.VISIBLE='Y'");
        return $results;
    }

    public function getContact($contact_id)
    {
        return $this->db->get_row("SELECT * FROM CLIENTS\$CONTACTS WHERE CONTACT_ID = " . $contact_id);
    }

    public function add($data)
    {
        if(!$this->checkContactExists($data['NAME'], $data['EMAIL'], $data['TEL'], $data['FAX'],
            $data['LANGUAGE_CODE_ID'], $data['CLIENT_ID'], $data['FUNCTION_DESCRIPTION'])) {

            $next_contact_id = $this->db->get_var("select MAX(CONTACT_ID)+1 next_id from CLIENTS\$CONTACTS");
            if ($next_contact_id == "") {
                $next_contact_id = 1;
            }

            $data['CONTACT_ID'] = $next_contact_id;
            $data['MODIFIEDBY'] = $this->online_user;
            $this->addData('CLIENTS$CONTACTS', $data);
            return true;
        }
        return false;
    }

    public function save($data, $where)
    {
        $data['MODIFIEDBY'] = $this->online_user;
        $this->saveData('CLIENTS$CONTACTS', $data, $where);
    }

    public function checkContactExists($name, $email, $tel, $fax, $language_code_id, $clientId, $function_description)
    {
        $results = $this->db->get_results("select count(*) from CLIENTS\$CONTACTS where NAME='" . $name . "' and  EMAIL='" . $email ."' and TEL='" . $tel . "' and FAX='" . $fax . "' and LANGUAGE_CODE_ID='" . $language_code_id . "' and CLIENT_ID='".$clientId."' and FUNCTION_DESCRIPTION='" . $function_description . "'");

        if ($results[0]->COUNT) {
            return true;
        } else {
            return false;
        }
    }

    public function delete($contact_id)
    {
        $this->db->query("UPDATE CLIENTS\$CONTACTS SET VISIBLE='N',MODIFIED=CURRENT_DATE,MODIFIEDBY='{$this->online_user}' where CONTACT_ID='$contact_id'");
    }
}

?>
