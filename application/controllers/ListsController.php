<?php

require_once 'application/controllers/BaseController.php';

class ListsController extends BaseController {

    public function actionRemarksAction() {
        global $config;

        $this->checkAccessAndRedirect(array('lists/action-remarks'));
        $this->view->bread = $this->functions->T("menu_traject") . "->" . $this->functions->T("menu_lists_action-remarks")  ;

        $obj = new Application_Model_FilesActions();

        $form = new Application_Form_ListsActionRemarks();

        if ($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {

                $data = $form->getValues();

                $usersObj = new Application_Model_Users();

                $this->view->results = $obj->getRemarkActions(
                    $this->functions->date_dbformat($data['FROM_DATE']),
                    $this->functions->date_dbformat($data['TO_DATE']),
                    $data['ACTION_ID'],
                    $usersObj->getUserField($data['USER_ID'], 'CODE')
            );
            }
        }
        $this->view->form = $form;
    }

    public function delayedInvoicesAction() {
        global $config;
        $this->checkAccessAndRedirect(array('lists/action-remarks'));
        $this->view->bread = $this->functions->T("menu_traject") . "->" . $this->functions->T("menu_lists_delayed-invoices")  ;
        $obj = new Application_Model_FilesReferences();
        $this->view->results = $obj->getPauzedDelayed();
    }







}
