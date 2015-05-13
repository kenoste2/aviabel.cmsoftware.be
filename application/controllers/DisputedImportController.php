<?php
require_once 'application/controllers/BaseController.php';

class DisputedImportController extends BaseController {

    public function readCsvAction() {

        global $config;

        $this->checkAccessAndRedirect(array('disputed-import/read-csv'));

        $this->view->bread = $this->functions->T("menu_traject") . "->" . $this->functions->T("menu_disputed-import_read-csv");

        $fileNrColumn = 10;
        $dueDateColumn = 40;
        $checkDateColumn = 41;

        $form = new Application_Form_ImportCsv();
        $form->populate(array());
        $csvHandler = new Application_Model_CsvHandler();
        $warnings = array();
        $messages = array();
        $this->view->success = false;

        if ($this->getRequest()->isPost()) {
            if ($form->isValid($_POST)) {
                $data = $form->getValues();
                $form->populate($data);

                $import = new Application_Model_Import();
                $fileInfo = $form->csvfile->getFileInfo();

                $fileName = $import->processFileFullPath($fileInfo["csvfile"], $config->rootImportedFiles);
                $csvData = $csvHandler->readCsvToArray($fileName);

                $defaultDueDate = new DateTime();
                $defaultDueDate->add(new DateInterval('P30D'));

                //NOTE: prevalidate input data
                $i = 2;
                foreach($csvData as $item) {
                    if(!$item[$fileNrColumn]) {
                        $warnings []= $this->view->G('invoice_nr_not_found_c', $i);
                    } else if(!$item[$dueDateColumn]) {
                        $warnings []= $this->view->G('due_date_nr_not_found_c', $i);
                    } else {
                        $dueDate = DateTime::createFromFormat('d/m/y', $item[$dueDateColumn]);
                        if(!$dueDate) {
                            $warnings []= $this->view->G('due_date_not_in_correct_format_c', $i) . ':' . $item[$dueDateColumn];
                        }
                        if($item[$checkDateColumn]) {
                            $checkDate = DateTime::createFromFormat('d/m/y', $item[$checkDateColumn]);

                            if(!$checkDate) {
                                $warnings []= $this->view->G('ended_date_not_in_correct_format_c', $i). ':' . $item[$checkDateColumn];
                            }
                        }
                    }

                    $i += 1;
                }

                $references = new Application_Model_FilesReferences();

                $availableReferences = array();
                if(count($messages) <= 0) {

                    //NOTE: execute import
                    $referenceFieldLength = 9;
                    foreach($csvData as $item) {
                        if($item[$fileNrColumn]) {
                            $correctedReferenceName = $item[$fileNrColumn];

                            $availableReferences []= $correctedReferenceName;

                            $reference = $references->getReferenceByReferenceName($correctedReferenceName);

                            print "<pre>";
                            print_r(item);

                            if($reference) {

                                $dueDate = DateTime::createFromFormat('d/m/y', $item[$dueDateColumn]);
                                $checkDate = DateTime::createFromFormat('d/m/y', $item[$checkDateColumn]);

                                $disputeDate = DateTime::createFromFormat('Y/m/d', $reference['DISPUTE_DATE']);

                                $now = new DateTime();

                                $defaultDueDate = new DateTime();
                                $defaultDueDate->add(new DateInterval('P30D'));

                                $dueDateStr = $dueDate ? $dueDate->format('Y/m/d') : $defaultDueDate->format('Y/m/d');
                                $checkDateStr = $checkDate ? $checkDate->format('Y/m/d') : null;
                                $disputeDateStr = $disputeDate ? $disputeDate->format('Y/m/d') : $now->format('Y/m/d');

                                if(!$reference['DISPUTE_ENDED_DATE'] || !$checkDate) {
                                    $newReference = array(
                                        'REFERENCE_ID' => $reference['REFERENCE_ID'],
                                        'DISPUTE' => $checkDate ? 0 : 1,
                                        'DISPUTE_DATE' => $disputeDateStr,
                                        'DISPUTE_DUEDATE' => $dueDateStr,
                                        'DISPUTE_ENDED_DATE' => $checkDateStr
                                    );
                                    $references->update($newReference);
                                }
                            }
                        }
                    }

                    $references->switchDisputeOff($availableReferences);
                    $this->view->success = true;
                }
            }
        }

        $this->view->messages = $messages;
        $this->view->warnings = $warnings;
        $this->view->form = $form;

        $columnNames = array(
            $this->view->G('invoice_number_c') => $fileNrColumn + 1,
            $this->view->G('due_date_c') => $dueDateColumn + 1,
            $this->view->G('ended_date_c') => $checkDateColumn + 1);
        $columnNamesStr = implode(', ', array_map(function ($v, $k) { return $k . ' : ' . $v; }, $columnNames, array_keys($columnNames)));
        $this->view->explanation = $this->view->G('column_layout_c', $columnNamesStr);
    }

    /**
     * @param $item
     * @param $fileNrColumn
     * @param $referenceFieldLength
     * @return string
     */
    private function addZeroLeftPadding($item, $fileNrColumn, $referenceFieldLength)
    {
        $correctedReferenceName = $item[$fileNrColumn];

        for ($i = strlen($item[$fileNrColumn]); $i < $referenceFieldLength; $i++) {
            $correctedReferenceName = '0' . $correctedReferenceName;
        }
        return $correctedReferenceName;
    }
}
