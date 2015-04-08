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

            if ($this->getRequest()->isPost()) {

                foreach($references as $reference) {
                    $disputeComment = $_POST["DEBTOR_DISPUTE_COMMENT_{$reference->REFERENCE_ID}"];

                    $shouldSendMail = false;
                    if($disputeComment) {
                        $shouldSendMail = !$reference->DEBTOR_DISPUTE_COMMENT;
                        $data = array(
                            'REFERENCE_ID' => $reference->REFERENCE_ID,
                            'DEBTOR_DISPUTE_COMMENT' => $disputeComment,
                            'DISPUTE_STATUS' => 'DEBTOR_REMARK');
                        $referenceObj->saveReference($data);

                        $reference->DEBTOR_DISPUTE_COMMENT = $disputeComment;
                    }

                    if($shouldSendMail) {
                        $debtorExternalAccessObj->sendDisputeWarningMail($reference);
                    }
                }
            }
            $this->view->references = $references;
            $this->view->authenticated = true;
        }
        else
        {
            $this->view->authenticated = false;
        }
    }
}
