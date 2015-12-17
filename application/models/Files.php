<?php

require_once 'application/models/Base.php';

class Application_Model_Files extends Application_Model_Base
{

    public function create($data)
    {
        $fileReferenceObj = new Application_Model_FilesReferences();

        $invoices = $data['invoices'];
        unset($data['invoices']);
        $data['CREATION_YEAR'] = date("Y");
        $data['CREATION_MONTH'] = date("m");
        $data['CREATION_DATE'] = date("Y-m-d");
        $data['CREATION_USER'] = $this->online_user;
        $incassoKost = $this->functions->getUserSetting('STARTUP_INCASSO');
        if (empty($incassoKost)) {
            $incassoKost = 0;
        }
        $data['INCASSOKOST'] = $incassoKost ;

        if (empty($data['COLLECTOR_ID'])) {
            $collectorsObj =  new Application_Model_Collectors();
            $data["COLLECTOR_ID"] = $collectorsObj->getBaseCollectorId();
        }

        if (empty($data['STATE_ID'])) {
            $data['STATE_ID'] = $this->functions->getUserSetting('factuur_aanmaak_status');
        }

        if (empty($data['TYPE_ID'])) {
            $data['TYPE_ID'] = $this->db->get_var("SELECT TYPE_ID FROM CLIENTS\$FILE_TYPES WHERE CLIENT_ID = {$data['CLIENT_ID']}");
        }
        $fileId = $this->addData('FILES$FILES', $data, 'FILE_ID');

        if (!empty($invoices)) {
            foreach ($invoices as $invoice) {
                $invoice['FILE_ID'] = $fileId;
                $invoice['AUTO_CALCULATE'] = 1;
                $invoice['END_DATE'] = date("Y-m-d");
                $fileReferenceObj->create($invoice);
            }
        }

        $filesActionsModel = new Application_Model_FilesActions();
        $filesActionsModel->addStartAction($fileId);

        return $fileId;
    }

    public function getFilesByDebtorId($debtorId) {

        $escDebtorId = $this->db->escape($debtorId);
        $sql = "SELECT * FROM FILES\$FILES
                WHERE DEBTOR_ID = {$escDebtorId}";
        return $this->db->get_results($sql);
    }

    public function getFileNumberById($fileId) {
        return $this->db->get_var("select FILE_NR from FILES\$FILES WHERE FILE_ID='{$fileId}'");
    }

    public function save($data, $fileId) {
        $this->saveData('FILES$FILES', $data, 'FILE_ID = ' . $fileId);
    }

    public function getNextFileNr($client_id = false)
    {

        if (empty($client_id)) {
            $sql = "SELECT MAX(FILE_NR) FROM FILES\$FILES";
            $file_nr = $this->db->get_var($sql);
            if (!empty($file_nr)) {
                $file_nr++;
            } else {
                $file_nr = 1;
            }
        } else {
            $sql = "SELECT NEXT_FILE_NO FROM CLIENTS\$CLIENTS WHERE CLIENT_ID = {$client_id}";
            $file_nr = $this->db->get_var($sql);
        }
        return $file_nr;
    }

    public function getFilesByTerm($term)
    {
        $sql = "SELECT FIRST 100 F.*, D.NAME AS DEBTOR_NAME
            FROM FILES\$FILES F
            LEFT JOIN FILES\$DEBTORS D ON D.DEBTOR_ID = F.DEBTOR_ID
            WHERE UPPER(D.NAME) LIKE UPPER('%{$term}%') OR UPPER(F.FILE_NR) LIKE UPPER('%{$term}%') ORDER BY F.FILE_NR";

        return $this->db->get_results($sql);
    }

    public function getFileIdByNumber($fileNr)
    {
        return $this->db->get_var("select FILE_ID from FILES\$FILES WHERE FILE_NR='{$fileNr}'");
    }

    public function setState($fileId, $stateId)
    {
        $sql = "UPDATE FILES\$FILES SET STATE_ID = {$stateId} WHERE FILE_ID = {$fileId}";
        $this->db->query($sql);

    }

    public function getFileIdByReferenceAndClient($ref, $clientId)
    {
        return $this->db->get_var("select FILE_ID from FILES\$FILES where REFERENCE = '$ref' AND CLIENT_ID = '$clientId'");
    }

    public function getFileIdRefIdByRefAndInvoiceRef($ref,$invoice)
    {
        return $this->db->get_row("select A.FILE_ID,B.REFERENCE_ID from FILES\$FILES A
          JOIN FILES\$REFERENCES B ON A.FILE_ID = B.FILE_ID
        where A.REFERENCE = '$ref' AND B.REFERENCE = '$invoice'");
    }

    public function getFileIdRefIdByInvoiceRef($invoice)
    {
        return $this->db->get_row("select A.FILE_ID,B.REFERENCE_ID from FILES\$FILES A
          JOIN FILES\$REFERENCES B ON A.FILE_ID = B.FILE_ID
        where B.REFERENCE = '$invoice'");
    }

    public function getFileIdByReference($ref)
    {
        return $this->db->get_var("select FILE_ID from FILES\$FILES where REFERENCE = '$ref'");
    }

    public function deleteFile($fileId, $execute = false) {
        global $config;

        $file = $this->db->get_row("SELECT * FROM FILES\$FILES WHERE FILE_ID = $fileId");
        $this->deleteQueries[] = "DELETE FROM TODOS WHERE FILE_ID = $fileId";
        $this->deleteQueries[] = "DELETE FROM FILES\$PAYMENTS WHERE FILE_ID = $fileId";
        $sql = "SELECT TRANSACTION_ID FROM ACCOUNTS\$JOURNAL WHERE FILE_ID = $fileId";
        $results = $this->db->get_results($sql);
        if ($results) {
            foreach ($results as $row) {
                $this->deleteQueries[] = "DELETE FROM ACCOUNTS\$TRANSACTIONS WHERE TRANSACTION_ID = $row->TRANSACTION_ID";
            }
        }
        $this->deleteQueries[] = "DELETE FROM ACCOUNTS\$JOURNAL WHERE FILE_ID = $fileId;\r\n";
        $debtorCount = $this->db->get_var("SELECT COUNT(*) FROM FILES\$FILES WHERE DEBTOR_ID = $file->DEBTOR_ID");
        if ($debtorCount == 1) {
            $this->deleteQueries[] = "DELETE FROM FILES\$DEBTORS_HISTORY WHERE DEBTOR_ID = $file->DEBTOR_ID";
            $this->deleteQueries[] = "DELETE FROM FILES\$DEBTORS WHERE DEBTOR_ID = $file->DEBTOR_ID";
            $this->deleteQueries[] = "DELETE FROM FILES\$DEBTORS_LINKS WHERE DEBTOR_ID = $file->DEBTOR_ID or DEBTOR_ID2 = $file->DEBTOR_ID";
        }


        $this->deleteQueries[] = "DELETE FROM FILES\$FILE_COSTS WHERE FILE_ID = $fileId";
        $this->deleteQueries[] = "DELETE FROM FILES\$FILE_LINKS WHERE FILE_ID1 = $fileId OR FILE_ID2 = $fileId";
        $this->deleteQueries[] = "DELETE FROM FILES\$REFERENCES WHERE FILE_ID = $fileId";
        $this->deleteQueries[] = "DELETE FROM FILES\$REMARKS WHERE FILE_ID = $fileId";
        $this->deleteQueries[] = "DELETE FROM FILES\$FILES WHERE FILE_ID = $fileId";
        $this->deleteQueries[] = "DELETE FROM VISITS WHERE FILE_ID = $fileId";
        $this->deleteQueries[] = "DELETE FROM TODOS WHERE FILE_ID = $fileId";
        $this->deleteQueries[] = "DELETE FROM FILES\$FILE_ACTIONS WHERE FILE_ID = $fileId";

        $sql = "SELECT * FROM FILE_DOCUMENTS WHERE FILE_ID = $fileId";
        $results = $this->db->get_results($sql);
        if (!empty($results)) {
            $target_path = $config->rootFileDocuments;
            foreach ($results as $row) {
                $filename = $target_path . '/' . $file->FILENAME;
                if (file_exists($filename)) {
                    unlink($filename);
                }
            }
        }
        $this->deleteQueries[] = "DELETE FROM FILE_DOCUMENTS WHERE FILE_ID = $fileId";

        if (!empty($execute)) {
            foreach ($this->deleteQueries as $sql) {
                $this->db->query($sql);
            }
        }
        return $this->deleteQueries;
    }

    public function getDebtorId($fileId)
    {
        return $this->db->get_var("SELECT DEBTOR_ID FROM FILES\$FILES WHERE FILE_ID = {$fileId}");
    }

    public function setHighestStateAfterPayment($fileId)
    {
        $refObj = new Application_Model_FilesReferences();
        $oldRef = $refObj->getOldestReferenceFromFile($fileId);
        $currentStateId = $this->getFileStateId($fileId);

        if ($oldRef->STATE_ID !=  $currentStateId) {
            $sql = "UPDATE FILES\$FILES SET STATE_ID = {$oldRef->STATE_ID} WHERE FILE_ID = {$fileId}";
            $this->db->query($sql);
        }
    }

    public function getFileByReferenceId($referenceId) {
        $escReferenceId = $this->db->escape($referenceId);

        $sql = "SELECT * FROM FILES\$FILES
                WHERE FILE_ID IN
                    (SELECT FILE_ID FROM FILES\$REFERENCES
                     WHERE REFERENCE_ID = {$escReferenceId})";
        return $this->db->get_row($sql);
    }

    public function getFileStateId ($fileId) {
        return $this->db->get_var("SELECT STATE_ID FROM FILES\$FILES WHERE FILE_ID = {$fileId}");
    }

    /**
     * @param $auth
     * @return string
     */
    public function extraWhereClauseForUserRights($auth)
    {
        if ($auth->online_rights == 7) {
            return " and A.COLLECTOR_ID = '{$auth->online_collector_id}' AND COLLECTOR_VISIBLE = 1";
        }

        if ($auth->online_rights == 5) {
            if (empty($auth->online_subclients)) {
                return " and A.CLIENT_ID = '{$auth->online_client_id}' ";
            } else {
                $query_extra = " AND (A.CLIENT_ID = {$auth->online_client_id} ";
                foreach ($auth->online_subclients as $value) {
                    $query_extra .= " OR A.CLIENT_ID = $value";
                }
                $query_extra .= ")";
                return $query_extra;
            }
        }

        if ($auth->online_rights == 6) {
            return " and A.COLLECTOR_ID = '{$auth->online_collector_id}' ";
        }
        return "";
    }


    public function CheckAndCloseFile($fileId) {

        $stateId = $this->db->get_var("SELECT STATE_ID FROM FILES\$FILES WHERE FILE_ID = {$fileId}");
        $openAmount = $this->db->get_var("SELECT SALDO FROM FILES\$FILES WHERE FILE_ID = {$fileId}");

        if ($stateId != 40 && $openAmount <= 0.00) {

            $filesActionsObj = new Application_Model_FilesActions();

            $action = array(
                'FILE_ID' => $fileId,
                'ACTION_ID' => 4257876,
                'REMARKS' => '',
                'VIA' => '',
                'ADDRESS' => '',
                'E_MAIL' => '',
                'GSM' => '',
                'PRINTED' => 0,
                'ACTION_DATE' => date("Y-m-d"),
                'TEMPLATE_ID' => 0,
                'FILE_STATE_ID' => 40,
                'CONTENT' => '',
                'SMS_CONTENT' => ''
            );
            $filesActionsObj->add($action, true);
        }

        return true;
    }


}

?>
