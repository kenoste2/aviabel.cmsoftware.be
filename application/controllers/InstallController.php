<?php

require_once 'application/controllers/BaseController.php';

class InstallController extends BaseController
{

    public function indexAction()
    {
        $this->checkAccessAndRedirect(array('install/recentupdates'));

        die("done");
    }


    public function demoupdateAction()
    {
        $sql =  "UPDATE FILES\$REFERENCES SET START_DATE = START_DATE+10, END_DATE = END_DATE+10, INVOICE_DATE = INVOICE_DATE+10";
        print $sql;
        $this->db->query($sql);
        $sql =  "UPDATE FILES\$FILE_ACTIONS SET ACTION_DATE = ACTION_DATE+10";
        print "<br>".$sql;
        $this->db->query($sql);
        $sql =  "UPDATE FILES\$FILES SET LAST_ACTION_DATE = LAST_ACTION_DATE+10";
        print "<br>".$sql;
        $this->db->query($sql);
        $sql =  "UPDATE FILES\$PAYMENTS SET PAYMENT_DATE = PAYMENT_DATE+10";
        print "<br>".$sql;
        $this->db->query($sql);
        $sql =  "UPDATE FILES\$PAYMENTS SET CREATION_DATE = CREATION_DATE+10";
        print "<br>".$sql;
        $this->db->query($sql);

    }








    public function setTekstenIdAction()
    {
        $nextId = $this->db->get_var("SELECT MAX(TEKSTEN_ID) + 1 AS ID  FROM TEKSTEN");
        $sql = "SET GENERATOR TEKSTEN_NEXT_ID TO {$nextId}";
        $this->db->query($sql);

        die ("teksten id set to {$nextId}");

    }


    public function deleteAllFilesAction()
    {

        $pass = $this->getParam('pass');
        if ($pass == date("YmdH")) {
            $this->db->query("DELETE FROM TODOS");
            $this->db->query("DELETE FROM FILES\$PAYMENTS");
            $this->db->query("DELETE FROM ACCOUNTS\$JOURNAL");
            $this->db->query("DELETE FROM ACCOUNTS\$TRANSACTIONS");
            $this->db->query("DELETE FROM ACCOUNTS\$JOURNAL");
            $this->db->query("DELETE FROM FILES\$DEBTORS_HISTORY");
            $this->db->query("DELETE FROM FILES\$DEBTORS");
            $this->db->query("DELETE FROM FILES\$DEBTORS_LINKS");
            $this->db->query("DELETE FROM FILES\$FILE_COSTS");
            $this->db->query("DELETE FROM FILES\$FILE_LINKS");
            $this->db->query("DELETE FROM FILES\$REFERENCES");
            $this->db->query("DELETE FROM FILES\$REMARKS");
            $this->db->query("DELETE FROM FILES\$FILE_ACTIONS");
            $this->db->query("DELETE FROM VISITS");
            $this->db->query("DELETE FROM TODOS");
            $this->db->query("DELETE FROM FILE_DOCUMENTS");
            $this->db->query("DELETE FROM FILES\$FILES");
            die("All files deleted");
        } else {
            die("No pass provided");
        }
    }

    public function deleteEmptyZipCodesAction()
    {
        $Obj = new Application_Model_ZipCodes();
        $results = $this->db->get_results("SELECT ZIP_CODE_ID FROM SUPPORT\$ZIP_CODES WHERE CODE = ''");
        if (!empty($results)) {
            foreach ($results as $row) {
                $Obj->delete($row->ZIP_CODE_ID);
            }
        }
        die("All empty zip codes deleted");
    }


    public function recentupdatesAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();


        $content = file_get_contents("updates/recentupdates.sql");
        $queries = explode("#", $content);

        if (!empty($queries)) {
            foreach ($queries as $sql) {
                $this->db->query($sql);
            }
        }
        die("<br>System is up to date");

    }

    public function updateAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();


        $content = file_get_contents("updates/recentupdates.sql");
        $queries = explode("#", $content);

        if (!empty($queries)) {
            foreach ($queries as $sql) {
                $this->db->query($sql);
            }
        }
        die("<br>System is up to date");
    }

    public function installDisputeModuleAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $content = file_get_contents("updates/20141119_module_import_disputes.sql");
        $queries = explode(";", $content);

        if (!empty($queries)) {
            foreach ($queries as $sql) {
                $this->db->query($sql);
            }
        }
        die("<br>System is up to date");
    }

    public function installReportsUpdateAction() {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $sql = "ALTER TABLE REPORTS\$SALDO ADD CLIENT_ID DOM_RECORD_ID";
        $this->db->query($sql);

        $sql = "ALTER TABLE REPORTS\$SALDO
              ADD CONSTRAINT FK_SALDO_CLIENT
              FOREIGN KEY (CLIENT_ID) REFERENCES CLIENTS\$CLIENTS";
        $this->db->query($sql);

        $sql = "ALTER TABLE REPORTS\$DSO ADD CLIENT_ID DOM_RECORD_ID";
        $this->db->query($sql);

        $sql = "ALTER TABLE REPORTS\$DSO
              ADD CONSTRAINT FK_DSO_CLIENT
              FOREIGN KEY (CLIENT_ID) REFERENCES CLIENTS\$CLIENTS";
        $this->db->query($sql);

        $sql = "INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('not_due_c', 'all', 'Niet vervallen', 'Non-échu', 'Not due', 0)";
        $this->db->query($sql);
        die("<br>System updated");
    }

    public function phpinfoAction()
    {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $pass = $this->getParam('pass');
        if ($pass == date("YmdH")) {
            phpinfo();
        } else die("enter pass");


        die();
    }

    public function installSearchGraphUpdateAction() {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $sql = "INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('past_due_c', 'all', 'Vervallen', 'Échu', 'Due', 0)";
        $this->db->query($sql);
        die("<br>System updated");
    }

    public function installFetchMailsAction() {

        $content = file_get_contents("updates/20150121_mailTab.sql");
        $queries = explode(";", $content);

        if (!empty($queries)) {
            foreach ($queries as $sql) {
                $this->db->query($sql);
            }
        }
        die("<br>System is up to date");
    }
}

