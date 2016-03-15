<?php

require_once 'application/controllers/BaseController.php';

class TestController extends BaseController
{
    //NOTE: a test method to see whether the standardizePhoneNumber method behaves like it should.
    //      Can be safely removed. It would make a good unit test though.
    public function testStandardizePhoneNumberAction() {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $filesActionsObj = new Application_Model_FilesActions();

        $rawInternational = '+3214567891';
        $standRawInternational =  $filesActionsObj->standardizePhoneNumber($rawInternational);
        echo "<br>{$rawInternational} => {$standRawInternational}";

        $international = '003214567892';
        $standInternational =  $filesActionsObj->standardizePhoneNumber($international);
        echo "<br>{$international} => {$standInternational}";

        $belgian = '014567893';
        $standBelgian =  $filesActionsObj->standardizePhoneNumber($belgian);
        echo "<br>{$belgian} => {$standBelgian}";
    }

    public function importAction() {

        $filesObj = new Application_Model_Files();

        $stateId = $this->functions->getUserSetting('factuur_aanmaak_status');

        $dataRow = $this->db->get_row("SELECT FIRST 1 *  FROM IMPORT\$INVOICES WHERE CLIENT_NUMBER = '177562/14008367/EUR'");
        if (empty($fileId)) {
            $data = array(
                'FILE_NR' => $filesObj->getNextFileNr(false),
                'CLIENT_ID' => $dataRow->CLIENT_ID,
                'DEBTOR_ID' => $dataRow->DEBTOR_ID,
                'REFERENCE' => '177562/14008367/EUR',
                'COLLECTOR_ID' => $dataRow->COLLECTOR_ID,
                'STATE_ID' => $stateId,
                'VALUTA' => $dataRow->VALUTA,
                'CONTRACT_REFERENCE' => $dataRow->CONTRACT_REFERENCE,
                'CONTRACT_DESCRIPTION' => $dataRow->CONTRACT_DESCRIPTION,
            );
            $filesObj = new Application_Model_Files();
            $fileId = $filesObj->create($data);

            die ("fileid : {$fileId} ");

        }
    }


}
