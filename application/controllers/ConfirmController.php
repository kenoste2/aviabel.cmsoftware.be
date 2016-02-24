<?php

require_once 'application/controllers/BaseController.php';

class ConfirmController extends BaseController {

    public function viewAction()
    {

        $session = new Zend_Session_Namespace('FILES');

        $this->checkAccessAndRedirect(array('confirm/view'));
        $this->view->bread = $this->functions->T("menu_traject") . "->" . $this->functions->T("menu_confirm_view")  ;
        $this->view->printButton = true;

        $obj = new Application_Model_ConfirmActions();
        $searchForm = new Application_Form_SearchConfirm();

        if ($this->getRequest()->isPost()) {
            
            $data =  $this->getRequest()->getPost();

            $confirmSelection = $data['confirmSelection'];
            if (!empty($confirmSelection)) {
                $obj->confirmActions($confirmSelection);
            }

            $searchForm->isValid($this->getRequest()->getPost());
            $results = $obj->getUnConfirmedActions($searchForm->ACTION_ID->getValue());
            $this->view->results = $results;

            if (!empty($results)) {
                $fileList = array();
                foreach ($results as $row) {
                    $fileList[] = array(
                        "FILE_ID" => $row->FILE_ID,
                        "FILE_NR" => $row->FILE_NR,
                        "DEBTOR_NAME" => $row->DEBTOR_NAME,
                    );
                }
                $session->fileList = $fileList;
                $this->view->results = $results;
            } else {
                $this->export->sql = "";
                $this->view->exportButton = false;
            }



        }

        $this->view->searchForm = $searchForm;
        $this->view->exportButton = count($this->view->results) ? true : false;
        $this->export->sql = count($this->view->results) ? $obj->getExportSql() : '';
    }
}

