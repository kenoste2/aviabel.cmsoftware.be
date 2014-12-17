<?php

require_once 'application/controllers/BaseController.php';

class ConvertController extends BaseController
{

    public function exportAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $sql = "SELECT * FROM TEKSTEN WHERE SETTINGS = 0";
        $results = $this->db->get_results($sql);

        $export = array();

        if (!empty($results)) {
            foreach ($results as $row) {
                if (!array_key_exists($row->CODE, $export)) {
                    $query = "INSERT INTO TEKSTEN (TEKSTEN_ID,CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('{$row->TEKSTEN_ID}','{$row->CODE}','{$row->NAV}','" . $this->db->escape(trim($row->NL)) . "','" . $this->db->escape(trim($row->FR)) . "','" . $this->db->escape(trim($row->EN)) . "',0);";
                    $export[$row->CODE] = $query;
                }
            }
        }
        print implode("\n", $export);
        die();
    }

    public function importAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $sql = "DELETE FROM TEKSTEN WHERE SETTINGS = 0";
        $this->db->query($sql);

        $content = file_get_contents("updates/teksten.sql");
        $queries = explode(";", $content);

        if (!empty($queries)) {
            foreach ($queries as $sql) {
                //$sql = utf8_decode($sql);
                $this->db->query($sql);
            }
        }

        $sql = "INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('selectPaymentFile_c','financial_insert_payments','Selecteer a.u.b. een bestand met het volgende formaat of een coda bestand, na het selecteren wordt dit bestand automatisch ingelezen<br>formaat voorbeeld (Dossier, bedrag, datum,rekening code):<br>KLNT2;;1024,76;01/12/2012;EXTERNAL<br>KLNT3;JAARABONNEMENT;102,60;01/12/2012;INTERNAL','Sélectionnez un fichier au format suivant ou un fichier coda, après quoi ce fichier est lu automatiquement en exemple format (Référence,N° de facture, date, code de compte):<br>KLNT2;;1024,76;01/12/2012;EXTERNAL','Please select the client and file with payments format (Filenumber, amount, date,account code):<br>KLNT2;;1024,76;01/12/2012;EXTERNAL<br>KLNT3;JAARABONNEMENT;102,60;01/12/2012;EXTERNAL',0)";
        $this->db->query($sql);
        die("texts imported");
    }


    public function fieldsAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();


        $content = file_get_contents("updates/updates.sql");
        $queries = explode(";", $content);

        if (!empty($queries)) {
            foreach ($queries as $sql) {
                $this->db->query($sql);
            }
        }
        die("fields created");
    }

    public function utf8decodeAction () {

        $TemplateObj = new Application_Model_Templates();

        $sql = "SELECT * FROM TEKSTEN WHERE SETTINGS = '1'";
        $results = $this->db->get_results($sql);
        if (!empty($results)) {
            foreach ($results as $row) {
                $sql = "UPDATE TEKSTEN SET NL = '".utf8_encode($row->NL)."',FR = '".utf8_encode($row->FR)."', EN = '".utf8_encode($row->EN)."'
                WHERE TEKSTEN_ID = {$row->TEKSTEN_ID}";
                $this->db->query($sql);
            }
        }
        die ("done converting settings");
    }


    public function tekstenAction()
    {


        $nextId = $this->db->get_var("SELECT MAX(TEKSTEN_ID)+1 AS NEWID FROM TEKSTEN");

        $sql = "DROP TRIGGER TEKSTEN_BI";
        $this->db->query($sql);
        $sql = "DROP TRIGGER TEKSTEN_BI2";
        $this->db->query($sql);

        $sql = "DROP TRIGGER TEKSTEN_INCR";
        $this->db->query($sql);

        $sql = "DROP GENERATOR TEKSTEN_NEXT_ID";
        $this->db->query($sql);



        $sql = "CREATE TRIGGER TEKSTEN_INCR FOR TEKSTEN
	BEFORE INSERT AS BEGIN
  IF (NEW.TEKSTEN_ID IS NULL) THEN
      NEW.TEKSTEN_ID = GEN_ID(TEKSTEN_NEXT_ID, 1);
      END";
        $this->db->query($sql);
        $sql = "CREATE GENERATOR TEKSTEN_NEXT_ID";
        $this->db->query($sql);
	    $sql = "SET GENERATOR TEKSTEN_NEXT_ID TO $nextId";
        $this->db->query($sql);
        die("done");
    }



}

