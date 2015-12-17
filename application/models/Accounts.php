<?php

require_once 'application/models/Base.php';

class Application_Model_Accounts extends Application_Model_Base
{
    public function getSettingAccounts()
    {
        return $this->db->get_results("select * from ACCOUNTS\$ACCOUNTS where VISIBLE='Y' order by CODE");
    }

    public function getSetting($account_id)
    {
        return $this->db->get_row("SELECT * FROM ACCOUNTS\$ACCOUNTS WHERE ACCOUNT_ID = " . $account_id);
    }

    public function getAccountIdByCode($code)
    {
        return $this->db->get_var("select ACCOUNT_ID from ACCOUNTS\$ACCOUNTS where CODE='" . trim($code) . "'");
    }

    public function add($data)
    {
        $data['CODA_IMPORT'] = "0";
        $data['INVOICEABLE'] = "1";
        $data['COMMISSION'] = "1";

        if (!$this->checkAccountCodeExists($data['CODE'])) {
            return $this->addData("ACCOUNTS\$ACCOUNTS", $data);
        }

        return false;
    }

    public function save($data, $id)
    {
        if (!$this->checkAccountCodeExists($data['CODE'], $id)) {
            return $this->saveData("ACCOUNTS\$ACCOUNTS", $data, 'ACCOUNT_ID = ' . $id);
        }

        return false;
    }

    public function delete($id)
    {
        return $this->db->query("update ACCOUNTS\$ACCOUNTS set VISIBLE='0' where ACCOUNT_ID='$id'");
    }

    public function getAccountsForSelect()
    {
        return $this->db->get_results("select ACCOUNT_ID, DESCRIPTION from ACCOUNTS\$ACCOUNTS where VISIBLE='Y' order by CODE", ARRAY_N);
    }

    public function checkAccountCodeExists($code, $id = '')
    {
        $and = '';
        if (!empty($id)) {
            $and .= ' and ACCOUNT_ID != ' . $id;
        }

        $results = $this->db->get_results("select count(*) from ACCOUNTS\$ACCOUNTS where CODE='" . $code . "'" . $and);

        if ($results[0]->COUNT > 0) {
            return true;
        } else {
            return false;
        }
    }


    public function getInternalAccountId()
    {
        return $this->db->get_var("SELECT ACCOUNT_ID FROM ACCOUNTS\$ACCOUNTS WHERE IN_HOUSE = 1 AND VISIBLE='Y' ORDER BY ACCOUNT_ID");
    }


    public function getRecieveAccount($valuta = false) {

        $account = false;


        if (!empty($valuta))  {
            $account = $this->db->get_row("SELECT FIRST 1 *  FROM ACCOUNTS\$ACCOUNTS WHERE IN_HOUSE = 1 AND VISIBLE='Y' AND VALUTA='{$valuta}' ORDER BY ACCOUNT_ID");
        }

        if (empty($account)) {
            $account = $this->db->get_row("SELECT FIRST 1 *  FROM ACCOUNTS\$ACCOUNTS WHERE IN_HOUSE = 1 AND VISIBLE='Y' ORDER BY ACCOUNT_ID");
        }
        return $account;
    }


    public function checkIsDeletable($id)
    {
        if ($id > 3) {
            return true;
        } else {
            return false;
        }
    }
}

?>
