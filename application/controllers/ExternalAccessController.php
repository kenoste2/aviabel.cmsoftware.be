<?php


class ExternalAccessController extends Zend_Controller_Action
{
    public function init() {
        parent::init();
        $this->_helper->_layout->setLayout('external-access');
    }

    public function checkInvoicesAction() {
        global $lang;

        $accessCode = $this->getParam('a');
        $allowedLanguages = array('NL', 'FR', 'EN');
        if(in_array($this->getParam('l'), $allowedLanguages)) {
            $lang = $this->getParam('l');
        } else {
            $lang = 'NL';
        }

        $this->view->activeCode = $accessCode;
        $debtorExternalAccessObj = new Application_Model_DebtorExternalAccess();
        $debtorId = $debtorExternalAccessObj->getDebtorIdByExternalAccessCode($accessCode);
        if($debtorId && $debtorId > 0) {
            $debtorObj = new Application_Model_Debtors();

            //NOTE: fetch debtor related info.
            $debtor = $debtorObj->getDebtor($debtorId);
            $this->_helper->layout()->title = $this->view->G('overview_invoices_for_c') . ': ' . $debtor->NAME;

            //NOTE: fetch invoices for debtor.
            $referenceObj = new Application_Model_FilesReferences();
            $references = $referenceObj->getAllOpenReferencesByDebtorId($debtorId);
            $formReferences = array();
            foreach($references as $reference) {
                $formReferences []= array('obj' => $reference, 'form' => new Application_Form_ExternalDebtorReference($reference));
            }

            if ($this->getRequest()->isPost()) {

                foreach($formReferences as $reference) {
                    $disputeComment = "DEBTOR_DISPUTE_COMMENT_{$reference['obj']->REFERENCE_ID}";
                    $disputeEmail = "DEBTOR_DISPUTE_EMAIL_{$reference['obj']->REFERENCE_ID}";
                    $disputePhone = "DEBTOR_DISPUTE_PHONE_{$reference['obj']->REFERENCE_ID}";

                    //NOTE: only populate the form that was sent.
                    $processForm = false;
                    if($_POST[$disputeComment] || $_POST[$disputeEmail] || $_POST[$disputePhone]) {

                        $processForm = true;
                        $reference['form']->populate($_POST);
                        $this->view->openReferenceId = $reference['obj']->REFERENCE_ID;
                    } else {
                        $reference['form']->populate(array(
                            $disputeComment => $reference['obj']->DEBTOR_DISPUTE_COMMENT,
                            $disputeEmail => $reference['obj']->DEBTOR_DISPUTE_EMAIL,
                            $disputePhone => $reference['obj']->DEBTOR_DISPUTE_PHONE
                        ));
                    }

                    $shouldSendMail = false;
                    if($processForm) {
                        $now = new DateTime();
                        $in30Days = new DateTime();
                        $in30Days->add(new DateInterval('P30D'));
                        if($reference['form']->isValid($_POST)) {
                            $shouldSendMail = !$reference['obj']->DEBTOR_DISPUTE_COMMENT;
                            $data = array(
                                'REFERENCE_ID' => $reference['obj']->REFERENCE_ID,
                                'DEBTOR_DISPUTE_COMMENT' => $reference['form']->$disputeComment->getValue(),
                                'DEBTOR_DISPUTE_EMAIL' => $reference['form']->$disputeEmail->getValue(),
                                'DEBTOR_DISPUTE_PHONE' => $reference['form']->$disputePhone->getValue(),
                                'DISPUTE' => 1,
                                'DISPUTE_DATE' => $now->format('Y-m-d'),
                                'DISPUTE_DUEDATE' => $in30Days->format('Y-m-d'),
                                'DISPUTE_STATUS' => 'DEBTOR_REMARK');
                            $referenceObj->saveReference($data);
                        }
                        $this->view->errors = $reference['form']->getErrors();
                    }

                    if($shouldSendMail) {
                        //$debtorExternalAccessObj->sendDisputeWarningMail($reference['obj']);
                    }
                }
            } else {

                foreach($formReferences as $reference) {
                    $reference['form']->populate(array(
                            "DEBTOR_DISPUTE_COMMENT_{$reference['obj']->REFERENCE_ID}" => $reference['obj']->DEBTOR_DISPUTE_COMMENT,
                            "DEBTOR_DISPUTE_EMAIL_{$reference['obj']->REFERENCE_ID}" => $reference['obj']->DEBTOR_DISPUTE_EMAIL,
                            "DEBTOR_DISPUTE_PHONE_{$reference['obj']->REFERENCE_ID}"  => $reference['obj']->DEBTOR_DISPUTE_PHONE
                        ));
                }
            }

            $this->view->references = $formReferences;
            $this->view->authenticated = true;
        }
        else
        {
            $this->view->authenticated = false;
        }
    }
}
