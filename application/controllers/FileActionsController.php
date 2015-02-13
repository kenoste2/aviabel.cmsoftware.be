<?php

require_once 'application/controllers/BaseFileController.php';

class FileActionsController extends BaseFileController
{

    public function viewAction()
    {
        $obj = new Application_Model_FilesActions();

        $this->view->printButton = true;

        if ($this->hasAccess('deleteActions')) {
            $this->view->mayDelete = true;
        }
        if ($this->hasAccess('viewActionDocuments')) {
            $this->view->viewActionContent = true;
        }

        if ($this->hasAccess('addActions')) {
            $this->view->addButton = "/file-actions/add/index/" . $this->getParam("index");
        }


        if ($this->getParam("pdf")) {
            $this->view->pdf = $this->getParam("pdf");
        }

            if ($this->getParam("delete") && $this->hasAccess('deleteActions') ) {
            $this->delete($this->getParam("delete"));
            $this->view->deleted = true;
        }

        if ($this->getParam("added")) {
            $this->view->added = true;
        }
        $this->view->results = $obj->getActionsByFileId($this->fileId);
    }


    public function addAction()
    {
        $form = new Application_Form_FileAddAction();
        $obj = new Application_Model_FilesActions();
        $fileObj = new Application_Model_File();
        $this->view->fileId = $this->fileId;

        $this->view->index = $this->getParam('index');

        $session = new Zend_Session_Namespace('ADDACTION');


        $actionsIni = new Zend_Config_Ini(
            APPLICATION_PATH . '/configs/actions.ini', APPLICATION_ENV);

        $this->view->paymentPlanCode = $actionsIni->paymentPlanCode;


        $data = array();
        if ($this->getRequest()->isPost()) {
            $actionId = $obj->getActionByCode($this->getParam('ACTION_CODE'));
            if ($form->isValid($_POST) && !empty($actionId)) {
                $data = $update = $form->getValues();
                $update['ACTION_ID'] = $actionId;
                $update['FILE_ID'] = $this->fileId;
                $update['ACTION_DATE'] = $this->functions->date_dbformat($update['ACTION_DATE']);
                $update['BP_STARTDATE'] = $this->functions->date_dbformat($update['BP_STARTDATE']);

                if (empty($update['TEMPLATE_ID'])) {
                    unset($update['BP_STARTDATE']);
                    unset($update['BP_NR_PAYMENTS']);
                    unset($update['CONTENT']);
                    $update['PRINTED'] = 0;
                    $update['E_MAIL'] = '';
                    $update['ADDRESS'] = '';
                }



                //$update['CONTENT'] = utf8_decode($update['CONTENT']);

                $actionId = $obj->add($update);

                if ($update['PRINTED'] == '1' && $update['VIA'] == 'POST') {
                    $this->_redirect("/file-actions/view/added/1/pdf/{$actionId}/index/" . $this->getParam("index"));
                } else {
                    $this->_redirect('/file-actions/view/added/1/index/' . $this->getParam("index"));
                }
            } else {
                if (empty($actionId)) {
                    $this->view->actionCodeError = true;
                }
                $this->view->formError = true;
                $this->view->errors = $form->getErrors();
            }
        } else {
            $data = array();
            $data['VIA'] = "POST";
            $data['ACTION_DATE'] = date("d/m/Y");
            $data['PRINTED'] = 1;
            $data['BP_NR_PAYMENTS'] = 4;
            $data['BP_STARTDATE'] = date("d/m/Y");
            $form->populate($data);
        }


        $this->view->form = $form;

    }

    public function savePaymentPlanAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $session = new Zend_Session_Namespace('ADDACTION');
        $session->STARTDATE = $this->getParam("STARTDATE");
        $session->NR_PAYMENTS = $this->getParam("NRPAYMENTS");
        $result = "BP SET";
        print json_encode($result);
    }

    public function saveActionDateAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $session = new Zend_Session_Namespace('ADDACTION');
        $session->ACTION_DATE = $this->getParam("ACTION_DATE");
        $result = "ACTION_DATE SET";
        print json_encode($result);
    }


    private function delete($id)
    {
        $Obj = new Application_Model_FilesActions();
        $Obj->delete($id);
    }

}

