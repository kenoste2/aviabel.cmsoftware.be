<?php

require_once 'application/controllers/BaseController.php';

class InstallController extends BaseController
{

    public function indexAction()
    {
        $this->checkAccessAndRedirect(array('install/recentupdates'));

        die("done");
    }



    public function paymentdelayAction(){

        $debtorsObj = new Application_Model_Debtors();
        $paymentDelayHistoryObj = new Application_Model_PaymentDelayAverage();

        $allDebtors = $debtorsObj->getAllDebtors();
        if(count($allDebtors) > 0) {
            foreach($allDebtors as $debtor) {
                $paymentDelayHistoryObj->addPaymentDelayHistory($debtor->DEBTOR_ID, 80, 1);
            }
        }

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
        $sql =  "UPDATE FILES\$PAYMENTS SET PAYMENT_DATE = CURRENT_DATE WHERE PAYMENT_ID IN (5071514,5071512,5071522,5071524,5071520,5071561,5071552,5071557,507154,5071567)";
        print "<br>".$sql;
        $this->db->query($sql);
        $sql =  "update imported_mails set creation_date = '".date("Y-m-d")." 09:23:23' where IMPORTED_MAIL_ID  = 1";
        print "<br>".$sql;
        $this->db->query($sql);
        $sql =  "update imported_mails set creation_date = '".date("Y-m-d")." 08:00:05' where IMPORTED_MAIL_ID  = 11";
        print "<br>".$sql;
        $this->db->query($sql);
        $sql =  "update imported_mails set creation_date = '".date("Y-m-d")." 10:20:15' where IMPORTED_MAIL_ID  = 6";
        print "<br>".$sql;
        $this->db->query($sql);
        $sql =  "UPDATE  FILES\ $REFERENCES r  SET DISPUTE_DATE=CURRENT_DATE WHERE REFERENCE_ID IN (5070580,5070583,5071506)";
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
            $this->db->query("DELETE FROM IMPORTED_MAIL_ATTACHMENTS");
            $this->db->query("DELETE FROM IMPORTED_MAILS");
            $this->db->query("DELETE FROM SUBDEBTORS");
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
            $this->db->query("DELETE FROM LOGGING");
            $this->db->query("DELETE FROM IBE\$LOG_KEYS");
            $this->db->query("DELETE FROM IBE\$LOG_TABLES");
            $this->db->query("DELETE FROM CLIENTS\$POINTS");
            $this->db->query("DELETE FROM IMPORT\$INVOICES ");
            $this->db->query("DELETE FROM FILES\$TIME_REGISTRATION ");
            $this->db->query("DELETE FROM DEBTORS\$PAYMENT_DELAY ");
            $this->db->query("DELETE FROM BINFO ");
            $this->db->query("DELETE FROM BINFO ");
            $this->db->query("DELETE FROM SUPPORT\$POPULATION_PLACES ");


            die("All files deleted");
        } else {
            die("No pass provided");
        }
    }

    public function testdbAction(){
        $sql = "select rdb\$relation_name AS TABLENAME
                from rdb\$relations
                where rdb\$view_blr is null";
        $results = $this->db->get_results($sql);
        if (!empty($results)) {
            foreach ($results as $row) {
                $count = $this->db->get_var("SELECT COUNT(*) as counter FROM {$row->TABLENAME}");
                print "<br>$row->TABLENAME : {$count}";
            }
        }

        die ("done testdbAction ");

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

    public function resetClientScoreAction(){

        $debtorsObj = new Application_Model_Debtors();

        $sql = "SELECT * FROM FILES\$DEBTORS";
        $results = $this->db->get_results($sql);
        if (!empty($results)){
            foreach ($results as $row) {
                $debtorsObj->changeDebtorScore(3,$row->DEBTOR_ID,1);
            }
        }


        die("done resetClientScoreAction");

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

    public function cleanupZipAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $sql = 'delete from support$zip_codes  where zip_code_id >1  and zip_code_id not in (SELECT ZIP_CODE_ID FROM FILES$FILES_ALL_INFO F WHERE F.DEBTOR_ZIP_CODE_ID = support$zip_codes.zip_code_id)  and zip_code_id not in (SELECT ZIP_CODE_ID FROM SYSTEM$USERS U WHERE U.ZIP_CODE_ID = support$zip_codes.zip_code_id) and zip_code_id not in (SELECT ZIP_CODE_ID FROM SYSTEM$COLLECTORS C WHERE C.ZIP_CODE_ID = support$zip_codes.zip_code_id) and zip_code_id not in (SELECT ZIP_CODE_ID FROM CLIENTS$CLIENTS CL WHERE CL.ZIP_CODE_ID = support$zip_codes.zip_code_id)  and zip_code_id not in (SELECT INVOICE_ZIP_ID FROM CLIENTS$CLIENTS CL2 WHERE CL2.INVOICE_ZIP_ID = support$zip_codes.zip_code_id)';
        $this->db->query($sql);

        die("zip codes database cleaned");
    }


}

