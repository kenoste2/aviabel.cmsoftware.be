<?php

require_once 'application/controllers/BaseController.php';

class DisputesController extends BaseController {

    public function searchAction() {

        $form = new Application_Form_Disputes();

        if ($this->getRequest()->isPost()) {
            if ($form->isValid($_POST)) {
                $form->populate($_POST);

                $searchArray = array();
                if($form->DISPUTE_STATUS_ID)  {
                    $searchArray['DISPUTE_STATUS_ID'] = $form->DISPUTE_STATUS_ID;
                }

                if($form->DISPUTE_OWNER_ID) {
                    $searchArray['DISPUTE_OWNER_ID'] = $form->DISPUTE_OWNER_ID;
                }

                if($form->DATE_STARTED_FROM || $form->DATE_STARTED_TILL) {
                    $searchArray['DATE_STARTED'] = array('from' => $form->DATE_STARTED_FROM, 'till' => $form->DATE_STARTED_TILL);
                }

                if($form->DATE_ENDED_FROM || $form->DATE_ENDED_TILL) {
                    $searchArray['DATE_ENDED'] = array('from' => $form->DATE_ENDED_FROM, 'till' => $form->DATE_ENDED_TILL);
                }

                if($form->EXPIRY_DATE_FROM || $form->EXPIRY_DATE_TILL) {
                    $searchArray['EXPIRY_DATE'] = array('from' => $form->EXPIRY_DATE_FROM, 'till' => $form->EXPIRY_DATE_FROM);
                }

                //TODO: fill up search array
                $disputesObj = new Application_Model_Disputes();
                $disputedInvoices = $disputesObj->search($searchArray);

            }
        }
        $this->view->form = $form;
    }
}
