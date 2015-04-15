<?php

require_once 'application/controllers/BaseController.php';

class AjaxController extends BaseController
{

    public function debtorsAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $queryExtra = "";

        if (!$this->hasAccess('accessAllDebtors')) {
            if ($this->auth->online_rights == 5) {
                $queryExtra = " AND (SELECT COUNT(*)
                  FROM FILES\$FILES F
                WHERE CLIENT_ID = {$this->auth->online_client_id} AND F.DEBTOR_ID = D.DEBTOR_ID ) >= 1";
            }

        }


        $term = strtoupper($this->getParam('term'));
        $sql = "SELECT FIRST 100 D.DEBTOR_ID,D.NAME,D.ADDRESS,Z.CODE,Z.CITY,Z.COUNTRY_ID,D.VATNR,D.TELEPHONE,D.TELEFAX,D.GSM,D.E_MAIL,D.LANGUAGE_ID 
            FROM FILES\$DEBTORS D
            JOIN SUPPORT\$ZIP_CODES Z ON D.ZIP_CODE_ID = Z.ZIP_CODE_ID
            WHERE UPPER(D.NAME) LIKE UPPER('%{$term}%') $queryExtra ORDER BY D.NAME";

        $array = array();
        $results = $this->db->get_results($sql);
        if ($results) {

            foreach ($results as $row) {
                $array [] = "{$row->NAME}|{$row->ADDRESS}|{$row->CODE}|{$row->CITY}|{$row->COUNTRY_ID}|{$row->VATNR}|{$row->TELEPHONE}|{$row->TELEFAX}|{$row->GSM}|{$row->E_MAIL}|{$row->LANGUAGE_ID}|$row->DEBTOR_ID";
            }
        }
        print json_encode($array);
    }

    public function clientsAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $term = strtoupper($this->getParam('term'));
        $sql = "SELECT FIRST 100 CLIENT_ID,NAME 
            FROM CLIENTS\$CLIENTS
            WHERE UPPER(NAME) LIKE UPPER('%{$term}%') AND IS_ACTIVE = 1 ORDER BY NAME";
        $array = array();
        $results = $this->db->get_results($sql);
        if ($results) {

            foreach ($results as $row) {
                $array [] = "{$row->NAME}|{$row->CLIENT_ID}";
            }
        }
        print json_encode($array);
    }

    public function fileActionsAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $term = trim(strtoupper($this->getParam('term')));
        $sql = "SELECT FIRST 100 ACTION_ID,CODE,DESCRIPTION
            FROM FILES\$ACTIONS
            WHERE (CODE CONTAINING '{$term}' OR DESCRIPTION CONTAINING '{$term}') AND ACTIEF = 1 ORDER BY DESCRIPTION";
        $array = array();
        $results = $this->db->get_results($sql);
        if ($results) {
            foreach ($results as $row) {
                $array [] = "{$row->CODE}|{$row->DESCRIPTION}";
            }
        }
        print json_encode($array);
    }

    public function templatesAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $actionsModel = new Application_Model_FilesActions();
        $term = strtoupper($this->getParam('term'));
        $actionId = $actionsModel->getActionByCode($term);

        $sql = "SELECT FIRST 100 TEMPLATE_ID,CODE,DESCRIPTION
            FROM SYSTEM\$TEMPLATES
            WHERE ACTION_ID = $actionId ORDER BY CODE";

        $array = array();
        $array[] = array('ID' => 0, 'CODE' => "-");
        $results = $this->db->get_results($sql);
        if ($results) {
            foreach ($results as $row) {
                $array [] = array(
                    'ID' => $row->TEMPLATE_ID,
                    'CODE' => "{$row->CODE} - {$row->DESCRIPTION}"
                );
            }
        }
        print json_encode($array);
    }

    public function templatedataAction()
    {
        $templatesObj = new Application_Model_Print();


        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();


        $fileObj = new Application_Model_File();
        $debtorObj = new Application_Model_Debtors();

        $fileId = $this->getParam('fileId');
        $templateId = $this->getParam('term');


        $debtorData = $templatesObj->getToContent($fileId, $templateId);
        $array [] = array(
            'EMAIL' => $debtorData['E_MAIL'],
            'GSM' => $debtorData['GSM'],
            'ADDRESS' => $templatesObj->formatAddress($debtorData),
        );
        print json_encode($array);
    }

    public function filesAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $term = strtoupper($this->getParam('term'));

        $fileModel = new Application_Model_Files();
        $results = $fileModel->getFilesByTerm($term);

        $array = array();
        if ($results) {
            foreach ($results as $row) {
                $array[] = "{$row->FILE_NR} | {$row->DEBTOR_NAME}";
            }
        }

        echo json_encode($array);
    }

    public function binfoAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $countryObj = new Application_Model_Countries();

        $name = strtoupper($this->getParam('name'));
        $vat = strtoupper($this->getParam('vat'));
        $country = $countryObj->getCountryCodeById($this->getParam('country'));


        $binfo = new Application_Model_Binformation();
        $results = $binfo->Search($name,$vat,$country);
        echo json_encode($results);
    }
}

