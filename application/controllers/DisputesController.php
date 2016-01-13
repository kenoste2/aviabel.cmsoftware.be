<?php

require_once 'application/controllers/BaseController.php';

class DisputesController extends BaseController {

    public function searchAction() {

        $this->checkAccessAndRedirect(array('disputes/search'));

        $form = new Application_Form_Disputes();

        if ($this->getRequest()->isPost()) {
            $form->populate($this->getRequest()->getPost());
            if ($form->isValid($this->getRequest()->getPost())) {

                $searchArray = array();
                if($form->DISPUTE_STATUS->getValue())  {
                    $searchArray['DISPUTE_STATUS'] = $form->DISPUTE_STATUS->getValue();
                }

                if($form->DISPUTE_ASSIGNEE->getValue()) {
                    $searchArray['DISPUTE_ASSIGNEE'] = $form->DISPUTE_ASSIGNEE->getValue();
                }

                if($form->DATE_STARTED_FROM->getValue() || $form->DATE_STARTED_TILL->getValue()) {
                    $searchArray['DATE_STARTED'] = array('from' => $form->DATE_STARTED_FROM->getValue(), 'till' => $form->DATE_STARTED_TILL->getValue());
                }

                if($form->DATE_ENDED_FROM->getValue() || $form->DATE_ENDED_TILL->getValue()) {
                    $searchArray['DATE_ENDED'] = array('from' => $form->DATE_ENDED_FROM->getValue(), 'till' => $form->DATE_ENDED_TILL->getValue());
                }

                if($form->EXPIRY_DATE_FROM->getValue() || $form->EXPIRY_DATE_TILL->getValue()) {
                    $searchArray['EXPIRY_DATE'] = array('from' => $form->EXPIRY_DATE_FROM->getValue(), 'till' => $form->EXPIRY_DATE_TILL->getValue());
                }

                if($form->DEBTOR_NAME->getValue()) {
                    $searchArray['DEBTOR_NAME'] = $form->DEBTOR_NAME->getValue();
                }

                if($form->FILE_REFERENCE->getValue()) {
                    $searchArray['FILE_REFERENCE'] = $form->FILE_REFERENCE->getValue();
                }



                $disputesObj = new Application_Model_Disputes();
                $disputedInvoices = $disputesObj->search($searchArray);

                $this->view->disputedInvoices = $disputedInvoices;
            } else {
                $this->view->errors = $form->getErrors();
            }
        }


        if ($form->DATE_STARTED_FROM->getValue() == "" && $form->DATE_STARTED_TILL->getValue() == "") {
            $data = array ();
            $form->populate($data);
        }



        $this->view->form = $form;
    }
}
