<?php

require_once 'application/controllers/BaseController.php';

class ImportController extends BaseController
{
    protected $_verjaring = false;

    public function selectAction()
    {

        $this->checkAccessAndRedirect(array('import/select'));

        $this->view->bread = $this->functions->T("menu_traject") . "->" . $this->functions->T("menu_import_select");
        $this->view->columns = $this->functions->getUserSetting('IMPORT_COLUMS');

        $form = new Application_Form_ImportFiles();
        $rowCount = 0;

        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $import = new Application_Model_Import();
                $fileName = $import->processFile($_FILES['userfile'], '/../public/documents/imported_files');
                $rowCount = $import->importFileCsv($fileName);
                //$rowCount = $import->linkFields();
                $this->view->showList = true;
                $this->view->showForm = false;
                $this->view->imported = true;
            }
        } else {
            $this->view->showForm = true;
        }
        $this->view->rowCount = $rowCount;
        $this->view->importFilesForm = $form;
    }

    public function processAction()
    {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        if ($this->getRequest()->isPost()) {
            $tempImportModel = new Application_Model_TempImport();
            $imports = $tempImportModel->getAllTempImports();
            $filesModel = new Application_Model_Files();
            $debtorModel = new Application_Model_Debtors();
            $remarkModel = new Application_Model_FilesRemarks();
            $clientModel = new Application_Model_Clients();
            $referenceModel = new Application_Model_FilesReferences();

            $imported = 0;
            foreach ($imports as $row) {
                //SEARCH/CREATE debtor
                $languageId = $this->getLanguage($row->DEBTOR_LANGUAGE, $row->DEBTOR_ZIP_CODE_ID);

                $debtorData = array(
                    'NAME' => $row->DEBTOR_NAME,
                    'VATNR' => $row->DEBTOR_VAT,
                    'NATNUMBER' => $row->NATNUMBER,
                    'ADDRESS' => $row->DEBTOR_ADDRESS,
                    'ZIP_CODE_ID' => $row->DEBTOR_ZIP_CODE_ID,
                    'LANGUAGE_ID' => $languageId,
                    'TITLE_ID' => $row->DEBTOR_TITLE,
                    'ADDRESS2' => "$row->USE_ADDRESS - $row->USE_ZIP_CODE $row->USE_PLACE",
                    'TELEPHONE' => $row->TEL,
                    'GSM' => $row->GSM,
                    'E_MAIL' => $row->E_MAIL,
                    'BIRTH_DAY' => $row->DEBTOR_BIRTH_DAY
                );
                if ($row->DEBTOR_ID == 0) {
                    $debtorId = $debtorModel->add($debtorData);
                } else {
                    $debtorId = $row->DEBTOR_ID;
                    $debtorModel->save($debtorData, $debtorId);
                }

                $collector_id = 1; // internal collector
                $useAddress = $row->USE_ADDRESS . " - " . $row->USE_ZIP_CODE . " " . $row->USE_PLACE;

                $nextFileNr = $filesModel->getNextFileNr();


                $data = array(
                    'STATE_ID' => 26,
                    'FILE_NR' => $nextFileNr,
                    'DEBTOR_ID' => $debtorId,
                    'REFERENCE' => $row->REFERENCE,
                    'CLIENT_ID' => $row->CLIENT_ID,
                    'COLLECTOR_ID' => $collector_id,
                    'TYPE_ID' => $row->FILE_TYPE,
                    'AFNAME_NAAM' => $row->USE_NAME,
                    'AFNAME_ADRES' => $useAddress,
                    'PARTNER' => $row->CONTRACTREKENING,
                );

                $fileId = $filesModel->create($data);

                if (strlen($row->INVOICE_REMARKS) > 3) {
                    $remarkModel->add(array(
                        'REMARK' => $row->INVOICE_REMARKS,
                        'REMARK_CLIENT' => $row->INVOICE_REMARKS,
                        'REMARK_COLLECTOR' => $row->INVOICE_REMARKS,
                        'FILE_ID' => $fileId
                    ));
                }

                if ($row->INVOICE_REFERENCES != "") {
                    $client = $clientModel->getClientIdById($row->CLIENT_ID);
                    $references = substr($row->INVOICE_REFERENCES, 0, strlen($row->INVOICE_REFERENCES) - 1);
                    $references = explode(";", $references);
                    if (count($references) >= 1) {
                        foreach ($references as $reference) {
                            list($ref, $ref_date, $ref_date2, $ref_amount, $ref_payed_before, $verbruiksAdres, $postType, $refund_statement) = explode("!", $reference, 8);
                            $current_intrest_minimum = (empty($client->CURRENT_INTREST_MINIMUM)) ? '0.00' : $client->CURRENT_INTREST_MINIMUM;
                            if ($ref_amount != 0) {
                                $referenceModel->create(array(
                                    'FILE_ID' => $fileId,
                                    'AUTO_CALCULATE' => 1,
                                    'REFERENCE' => $ref,
                                    'AMOUNT' => $ref_amount,
                                    'INTEREST_PERCENT' => $client->CURRENT_INTREST_PERCENT,
                                    'INTEREST_MINIMUM' => $current_intrest_minimum,
                                    'COST_PERCENT' => $client->CURRENT_COST_PERCENT,
                                    'COST_MINIMUM' => $client->CURRENT_COST_MINIMUM,
                                    'END_DATE' => date('Y-m-d'),
                                    'START_DATE' => $ref_date2,
                                    'COSTS' => 0.00,
                                    'INTEREST' => 0.00,
                                    'REFUND_STATEMENT' => '',
                                    'CREATION_DATE' => date('Y-m-d'),
                                    'CREATION_USER' => $this->auth->online_user,
                                    'PAYABLE' => 1,
                                    'REFERENCE_TYPE' => $postType,
                                    'USE_ADDRESS' => '',
                                    'INVOICE_DATE' => $ref_date
                                ));
                            }
                        }


                        list($jaar, $maand, $dag) = explode("-", $ref_date);
                        $date = new DateTime();
                        $date->setDate($jaar, $maand, $dag);
                        $date->modify("+21 month");
                        $datum_verjaring = $date->format("Y-m-d");

                        if (!empty($datum_verjaring) && $this->_verjaring === true) {
                            $filesModel->save(array('VERJARING' => $datum_verjaring), $fileId);
                        }

                    }
                }

                $nextFileNr++;
                $clientModel->save(array('NEXT_FILE_NO' => $nextFileNr), $row->CLIENT_ID);

                $imported++;
            }

            $tempImportModel->truncate();
            $this->_redirect('/import/select/imported/' . $imported);
        }
    }

    protected function getLanguage($language, $zip_code_id)
    {
        switch ($language) {
            case 'F':
            case 'FR':
                return 2;
                break;
            case 'N':
            case 'NL':
                return 1;
                break;
            case 'E':
            case 'EN':
                return 3;
                break;
            case 'D':
            case 'DE':
                return 4;
                break;
            default:
                $zipModel = new Application_Model_ZipCodes();
                $zipCode = $zipModel->getSetting($zip_code_id);

                if (!is_null($zipCode)) {
                    return $zipCode->LANGUAGE_ID;
                }

                break;
        }

        return 1;
    }

}

