<?php

require_once 'application/controllers/BaseFileController.php';

class FileInvoicesController extends BaseFileController
{

    public function viewAction()
    {
        $obj = new Application_Model_FilesReferences();

        $this->_helper->_layout->setLayout('file-layout');

        if ($this->hasAccess('manageInvoices')) {
            $this->view->addButton = "/file-invoices/add/fileId/" . $this->fileId;
            $this->view->manageInvoices = true;
        }
        $this->view->printButton = true;


        if ($this->moduleAccess('intrestCosts')) {
            $this->view->showIntrestCosts = true;
        }



        if ($this->getParam("delete") && $this->hasAccess('manageInvoices')) {
            $this->delete($this->getParam("delete"));
            $this->view->deleted = true;
        }
        $results = $obj->getReferencesByFileId($this->fileId);
        $this->view->results = $results;
    }

    public function addAction()
    {
        $form = new Application_Form_FileAddInvoice();

        $fileReferenceObj = new Application_Model_FilesReferences();

        $data = array();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($_POST)) {
                $data = $update = $form->getValues();
                $update['FILE_ID'] = $this->fileId;
                $update['START_DATE'] = $this->functions->date_dbformat($update['START_DATE']);
                $update['INVOICE_DATE'] = $this->functions->date_dbformat($update['INVOICE_DATE']);
                $update['END_DATE'] = date("Y-m-d");
                $update['AMOUNT'] = $this->functions->dbBedrag($update['AMOUNT']);
                $update['COSTS'] = $this->functions->dbBedrag($update['COSTS']);
                $update['INTEREST'] = $this->functions->dbBedrag($update['INTEREST']);
                $update['REFERENCE_TYPE'] = "FACTUUR";
                $fileReferenceObj->create($update);
                $this->view->formSaved = true;
            } else {
                $this->view->formError = true;
                $this->view->errors = $form->getErrors();
            }
        } else {
            $data = array();
        }
// Populating form
        $form->populate($data);

        $this->view->form = $form;
    }

    public function editAction()
    {
        $form = new Application_Form_FileEditInvoice();
        $fileReferenceObj = new Application_Model_FilesReferences();

        if (!$this->hasAccess('manageInvoices')) {
            $this->_redirect('/error/noaccess');
            return;
        }

        if (!$this->hasAccess('changeFileReference')) {
            $form->removeElement('submit');
        }

        $data = array();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($_POST)) {
                $data = $update = $form->getValues();
                $update['FILE_ID'] = $this->fileId;
                $update['START_DATE'] = $this->functions->date_dbformat($update['START_DATE']);
                $update['END_DATE'] = $this->functions->date_dbformat($update['END_DATE']);
                $update['INVOICE_DATE'] = $this->functions->date_dbformat($update['INVOICE_DATE']);
                $update['AMOUNT'] = $this->functions->dbBedrag($update['AMOUNT']);
                $update['COSTS'] = $this->functions->dbBedrag($update['COSTS']);
                $update['INTEREST'] = $this->functions->dbBedrag($update['INTEREST']);
                $update['INTEREST_PERCENT'] = $this->functions->dbBedrag($update['INTEREST_PERCENT']);
                $update['INTEREST_MINIMUM'] = $this->functions->dbBedrag($update['INTEREST_MINIMUM']);
                $update['COST_PERCENT'] = $this->functions->dbBedrag($update['COST_PERCENT']);
                $update['COST_MINIMUM'] = $this->functions->dbBedrag($update['COST_MINIMUM']);
                $update['DISPUTE_DATE'] = $this->functions->date_dbformat($update['DISPUTE_DATE']);
                $update['DISPUTE_DUEDATE'] = $this->functions->date_dbformat($update['DISPUTE_DUEDATE']);
                $update['DISPUTE_ENDED_DATE'] = $this->functions->date_dbformat($update['DISPUTE_ENDED_DATE']);
                $fileReferenceObj->update($update);
                $this->view->formSaved = true;
            } else {
                $this->view->formError = true;
                $this->view->errors = $form->getErrors();
            }
        } else {
            $reference = $this->db->get_row("SELECT * FROM FILES\$REFERENCES WHERE REFERENCE_ID = {$this->getParam('id')}");
            $data = array(
                'REFERENCE_TYPE' => $reference->REFERENCE_TYPE,
                'REFERENCE_ID' => $reference->REFERENCE_ID,
                'REFERENCE' => $reference->REFERENCE,
                'TRAIN_TYPE' => $reference->TRAIN_TYPE,
                'REFUND_STATEMENT' => $reference->REFUND_STATEMENT,
                'INVOICE_DATE' => $this->functions->dateformat($reference->INVOICE_DATE),
                'START_DATE' => $this->functions->dateformat($reference->START_DATE),
                'INVOICE_DATE' => $this->functions->dateformat($reference->INVOICE_DATE),
                'END_DATE' => $this->functions->dateformat($reference->END_DATE),
                'AMOUNT' => $this->functions->amount($reference->AMOUNT),
                'AUTO_CALCULATE' => $reference->AUTO_CALCULATE,
                'INTEREST' => $this->functions->amount($reference->INTEREST),
                'COSTS' => $this->functions->amount($reference->COSTS),
                'INTEREST_PERCENT' => $this->functions->amount($reference->INTEREST_PERCENT),
                'COST_PERCENT' => $this->functions->amount($reference->COST_PERCENT),
                'INTEREST_MINIMUM' => $this->functions->amount($reference->INTEREST_MINIMUM),
                'COST_MINIMUM' => $this->functions->amount($reference->COST_MINIMUM),
                'STATE_ID' => $reference->STATE_ID,
                'DISPUTE' => $reference->DISPUTE,
                'DISPUTE_DATE' => $this->functions->dateformat($reference->DISPUTE_DATE),
                'DISPUTE_DUEDATE' => $this->functions->dateformat($reference->DISPUTE_DUEDATE),
                'DISPUTE_ENDED_DATE' => $this->functions->dateformat($reference->DISPUTE_ENDED_DATE)
            );
        }
        $form->populate($data);

        $this->view->form = $form;
    }

    private function delete($id)
    {
        $Obj = new Application_Model_FilesReferences();
        $Obj->delete($id);
    }

}

