<?php

require_once 'application/controllers/BaseController.php';

class CronController extends BaseController
{

    public function trainAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        /*
         * 1.  actie_id, file_id en template_id ophalen volgens train logica uit train.php van Final
         */

        $train = new Application_Model_Train();
        $modules = $train->getMappedTrainModules();

        $print = new Application_Model_Print();
        $filesActions = new Application_Model_FilesActions();

        foreach ($modules as $module) {
            /* 2. fetch content from template */
            $content = $print->getTemplateContent($module->FILE_ID, $module->TEMPLATE_ID);
            $content = json_decode($content);
            $content = $content->CONTENT;
            $to = $print->getToContent($module->FILE_ID,$module->TEMPLATE_ID);

            $now = new DateTime();
            $action = array(
                'FILE_ID' => $module->FILE_ID,
                'ACTION_ID' => $module->ACTION_ID,
                'REMARKS' => '',
                'VIA' => 'POST',
                'ADDRESS' => "{$to['NAME']}\n{$to['ADDRESS']}\n{$to['ZIP_CODE']} {$to['CITY']}",
                'EMAIL' => $to['E_MAIL'],
                'PRINTED' => 0,
                'ACTION_DATE' => $now->format('Y-m-d'),
                'TEMPLATE_ID' => $module->TEMPLATE_ID,
                'FILE_STATE_ID' => $modules->STATE_ID,
                'CONTENT' => $content
            );
            /* 3. model Application_Model_FilesActions -> add */
            print "<pre>";
            print_r($action);
            $filesActions->add($action);
        }

        die("train has run");
    }

    public function intrestsAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $obj = new Application_Model_FilesReferences();

        $referencesToUpdate = $obj->getOpenReferencesWithAutoCalculate();

        if (!empty($referencesToUpdate)) {
            foreach($referencesToUpdate as $reference) {
                $data = array(
                    'AMOUNT' => $reference->AMOUNT,
                    'INTEREST_PERCENT' => $reference->INTEREST,
                    'INTEREST_PERCENT' => $reference->INTEREST_PERCENT,
                    'AUTO_CALCULATE' => 1,
                    'START_DATE' => $reference->START_DATE,
                    'END_DATE' => date("Y-m-d"),
                    'REFERENCE_ID' => $reference->REFERENCE_ID
                );
                $obj->update($data);
            }
        }
        die("intrests have been calculated");
    }


    public function saldoReportAction()
    {
        $obj = new Application_Model_Dso();

        $clientsModel = new Application_Model_Clients();

        $activeClients = $clientsModel->getAllClients();
        foreach($activeClients[0] as $client) {

            $openAmount = $obj->getOpenAmounts($client->CLIENT_ID);
            $interCompany = $obj->getIntercompany($client->CLIENT_ID);
            $data = array(
                'AMOUNT' => $openAmount ? $openAmount : 0,
                'INTERCOMPANY' => $interCompany ? $interCompany : 0,
                'CLIENT_ID' => $client->CLIENT_ID,
                'CREATION_DATE' => date("Y-m-d"),
                'CREATION_USER' => 'CRON',
            );

            $this->saveData('REPORTS$SALDO', $data);
        }

        die("saldo saved");
    }

    public function paymentdelayAction()
    {
        $obj = new Application_Model_Debtors();
        $results = $obj->getCurrentPaymentDelays();

        if ($results) {
            foreach ($results as $row) {
                if (!empty($row->DELAY))
                $data = array (
                    'DEBTOR_ID' => $row->DEBTOR_ID,
                    'PAYMENT_DELAY' => $row->DELAY,
                    'CREATION_USER' => 'CRON',
                    'CREATION_DATE' => date("Y-m-d"),
                );
                $this->saveData('DEBTORS$PAYMENT_DELAY', $data);
            }
        }
        die("delays saved");
    }


    public function importFileAction()
    {
        $importObj = new Application_Model_Import();
        $importObj->importFileCsv("/var/www/vhosts/3as.be/subdomains/aaacollector/httpdocs/v4/public/documents/imported_files/OI 261114.csv");
        die ("imported");
    }




    public function commissionAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $obj = new Application_Model_CalculateCommission();
        $obj->getList();
        die("commissions");
    }
}

