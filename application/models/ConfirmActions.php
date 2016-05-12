<?php

require_once 'application/models/Base.php';

class Application_Model_ConfirmActions extends Application_Model_Base
{

    protected $exportSql;


    public function getUnConfirmedActions($actionId = false) {

        $extraQuery = "";
        if (!empty($actionId)) {
            $extraQuery .= " AND A.ACTION_ID = {$actionId}";
        }


        $this->exportSql = "SELECT A.*,B.CODE AS ACTION_CODE,B.DESCRIPTION AS ACTION_DESCRIPTION,F.REFERENCE,F.DEBTOR_NAME,D.TRAIN_TYPE
            FROM FILES\$FILE_AGENDA A
            JOIN  FILES\$ACTIONS B ON A.ACTION_ID = B.ACTION_ID
            JOIN  FILES\$FILES_ALL_INFO F ON F.FILE_ID = A.FILE_ID
            JOIN FILES\$DEBTORS D ON D.DEBTOR_ID = F.DEBTOR_ID
            WHERE A.CONFIRMED = 0  {$extraQuery}";
        $results = $this->db->get_results($this->exportSql);
        return $results;
    }

    public function getActionsToBeConfirmed() {
        $sql = "SELECT ACTION_ID, CODE FROM FILES\$ACTIONS WHERE CONFIRMATION_NEEDED = 1";
        $results = $this->db->get_results($sql,'ARRAY_N');

        return $results;
    }


    public function getExportSql() {
        return $this->exportSql;
    }

    public function confirmationNeeded($action_id)
    {
        $confirmationNeeded = $this->db->get_var("SELECT CONFIRMATION_NEEDED FROM FILES\$ACTIONS WHERE ACTION_ID = " . $action_id);
        if ($confirmationNeeded == '1') {
            return true;
        } else {
            return false;
        }
    }

    public function confirmActions($fileActionList) {

        $fileActionsObj = new Application_Model_FilesActions();

        if (!empty($fileActionList)) {
            foreach ($fileActionList as $id) {
                $sql = "UPDATE FILES\$FILE_AGENDA SET CONFIRMED =  '1' WHERE FILE_AGENDA_ID = {$id}";
                $this->db->query($sql);

                $row = $this->db->get_row("SELECT * FROM FILES\$FILE_AGENDA WHERE FILE_AGENDA_ID = {$id}");

                $data =  array(
                    'ACTION_ID' => $row->ACTION_ID,
                    'FILE_ID' => $row->FILE_ID,
                    'REMARKS' => $row->REMARKS,
                    'ACTION_USER' => $this->ONLINE_USER,
                    'TEMPLATE_ID' => $row->TEMPLATE_ID,
                    'VIA' => $row->VIA,
                    'EMAIL' => $row->EMAIL,
                    'ADDRESS' => $row->ADDRESS,
                    'GSM' => $row->GSM,
                    'PRINTED' => 'N',
                    'ACTION_DATE' => $row->ACTION_DATE,
                    'CONTENT' => $row->TEMPLATE_CONTENT,
                );
                $fileActionsObj->add($data,false,true);
            }
        }
    }

}

?>
