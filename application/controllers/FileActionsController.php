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
            $this->view->addButton = "/file-actions/add/fileId/" . $this->fileId;
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
        if ($this->getParam("confirmationNeeded")) {
            $this->view->confirmationNeeded = true;
        }
        $this->view->results = $obj->getActionsByFileId($this->fileId);
    }


    public function addAction()
    {
        $form = new Application_Form_FileAddAction();
        $form->setFileId($this->fileId);

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

                if (empty($update['CONTENT']) && !empty($update['CONTENT_HIDDEN'])) {
                    $update['CONTENT'] = $update['CONTENT_HIDDEN'];
                }
                if (empty($update['SMS_CONTENT']) && !empty($update['SMS_CONTENT_HIDDEN'])) {
                    $update['SMS_CONTENT'] = $update['SMS_CONTENT_HIDDEN'];
                }


                $fileActionId = $obj->getNextFileActionId();
                $update['FILE_ACTION_ID'] = $fileActionId;

                if (!empty($update['ATTACHMENT'])) {
                    $fileDocsobj = new Application_Model_FilesDocuments();
                    $fileDocsobj->add($this->fileId, $form->ATTACHMENT, $update['DESCRIPTION'],1, $fileActionId);
                }

                $confirmOverride = false;
                if ($this->auth->online_user == 'ADMIN')  //all hail to the testcase!
                {
                    $confirmOverride = true;
                }

                $actionId = $obj->add($update, false, $confirmOverride);
                
                if (is_numeric($actionId) && $actionId!='NEED_CONFIRMATION')
                {
                    if ($update['PRINTED'] == '1' && $update['VIA'] == 'POST') {
                        $this->_redirect("/file-actions/view/added/1/pdf/{$actionId}/fileId/" . $this->fileId);
                    } else {
                        $this->_redirect('/file-actions/view/added/1/fileId/' . $this->fileId);
                    }
                }
                elseif ($actionId == 'NEED_CONFIRMATION') {
                    $this->_redirect("/file-actions/view/confirmationNeeded/1/pdf/{$actionId}/fileId/" . $this->fileId);
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
                //$update['CONTENT'] = utf8_decode($update['CONTENT']);
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

