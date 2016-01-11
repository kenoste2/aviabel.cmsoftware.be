<?php

require_once 'application/models/Base.php';

class MappedTrainModule
{
    public $FILE_ID;
    public $ACTION_ID;
    public $CODE;
    public $STATE_ID;
    public $TEMPLATE_ID;
}

class Application_Model_Train extends Application_Model_Base
{

    public function getOrderCycleByState ($stateId, $trainType)
    {
        return $this->db->get_var("SELECT ORDER_CYCLE FROM TRAIN WHERE STATE_ID = {$stateId} AND TRAIN_TYPE='{$trainType}'");
    }
    public function getStateIdCycleByOrderCycle ($orderCycle, $trainType)
    {
        return $this->db->get_var("SELECT STATE_ID FROM TRAIN WHERE ORDER_CYCLE = {$orderCycle} AND TRAIN_TYPE='{$trainType}'");
    }


    public function getTrainTypes()
    {
        $results = $this->db->get_results("SELECT TRAIN_TYPE AS CODE ,TRAIN_TYPE AS DESRIPTION FROM TRAIN GROUP BY TRAIN_TYPE", ARRAY_N);
        return $results;
    }

    public function getTrainModules()
    {
        $sql = "select * from TRAIN where VISIBLE='Y' ORDER BY DAYS";
        return $this->db->get_results($sql);
    }

    public function getTrains()
    {
        return $this->db->get_results("select T.*, A.CODE AS ACTION_CODE, S.CODE AS STATE_CODE, T2.CODE AS TEMPLATE_CODE
            from TRAIN T
            LEFT JOIN FILES\$ACTIONS A ON T.SETACTION = A.ACTION_ID
            LEFT JOIN FILES\$STATES S ON T.STATE_ID = S.STATE_ID
            LEFT JOIN SYSTEM\$TEMPLATES T2 ON T.TEMPLATE_ID = T2.TEMPLATE_ID
            where T.VISIBLE='Y' order by TRAIN_TYPE,CODE");
    }

    public function getTrain($train_id)
    {
        return $this->db->get_row("SELECT * FROM TRAIN WHERE ID = " . $train_id);
    }

    public function getTrainFollowup($numberOfDays = 15)
    {
        return $this->db->get_results("select CODE,TRAIN_TYPE,DESCRIPTION,SQL,DAYS from TRAIN where ACTIEF=1 order by DESCRIPTION");
    }

    public function getTrainByType($type)
    {
        return $this->db->get_row("select * from TRAIN where CODE='$type' AND VISIBLE='Y'");
    }

    //TODO: extremely similar to performTrainSql. Consider merging the 2 methods.
    private function getTrainSql($trainModule)
    {
        $rootSql = "select DISTINCT I.FILE_ID $trainModule->SQL";
        $extraEndClause = " AND CLIENT_ID IN (SELECT CLIENT_ID FROM CLIENTS\$CLIENTS WHERE TRAIN_TYPE = '{$trainModule->TRAIN_TYPE}')";

        //WARNING: because most of the query syntax is fetched from the train-table, it is
        //         a bit hard to know for sure whether the query will be correct.
        if (preg_match("/\bwhere\b/i", $rootSql) && preg_match("/\bfiles\\\$files_all_info\b/i", $rootSql)) {
            $rootSql .= $extraEndClause;
        }

        $rootSql = str_replace("`","'",$rootSql);


        return $rootSql;
    }

    public function performTrainSql($row, $i, $collectorId = false)
    {
        $sql = "select DISTINCT I.FILE_ID,I.REFERENCE,I.DEBTOR_TELEPHONE,I.DEBTOR_GSM,I.FILE_NR,I.CREATION_DATE,I.DEBTOR_NAME,I.DEBTOR_ADDRESS,I.DEBTOR_ZIP_CODE,I.DEBTOR_CITY,I.STATE_CODE,I.AMOUNT,I.INTEREST,I.COSTS,I.TOTAL,I.PAYABLE $row->SQL";
        if ($row->DAYS > $i) {
            $days = "-" . $row->DAYS + $i;
        } else {
            $days = "+" . ($i - $row->DAYS);
        }

        $sql = str_replace("-$row->DAYS", $days, $sql);
        $sql = str_replace("`","'",$sql);

        if (!empty($collectorId)) {
            $sql = str_replace("WHERE", "WHERE F.COLLECTOR_ID = {$collectorId} AND ", $sql);
        }


        return $this->db->get_results($sql);
    }

    public function getMappedTrainModules()
    {

        $mappedTrainModules = array();
        $trainModules1 = $this->getTrainModules();

        foreach ($trainModules1 as $module) {

            $sql = $this->getTrainSql($module);

            if ($actualModules = $this->db->get_results($sql)) {
                foreach ($actualModules as $actualModule) {
                    $mappedModule = new MappedTrainModule();
                    $mappedModule->CODE = $module->CODE;
                    $mappedModule->FILE_ID = $actualModule->FILE_ID;
                    $mappedModule->STATE_ID = $module->STATE_ID;
                    $mappedModule->TEMPLATE_ID = $module->TEMPLATE_ID;
                    $mappedModule->ACTION_ID = $module->SETACTION;
                    $mappedTrainModules [] = $mappedModule;
                }
            }
        }

        return $mappedTrainModules;
    }

    public function getCountersForFollowup(array $results, $numberOfDays = 15, $collectorId = false)
    {
        $counters = array();
        foreach ($results as $row) {
            for ($i = 0; $i <= $numberOfDays; $i++) {
                $sql = "select COUNT(DISTINCT I.FILE_ID) " . $row->SQL;
                if ($row->DAYS > $i) {
                    $days = "-" . $row->DAYS + $i;
                } else {
                    $days = "+" . ($i - $row->DAYS);
                }
                $sql = str_replace("-$row->DAYS", $days, $sql);
                $sql = utf8_decode($sql);
                $sql = str_replace("`","'",$sql);

                if (!empty($collectorId)) {
                    $sql = str_replace("WHERE", "WHERE F.COLLECTOR_ID = {$collectorId} AND ", $sql);
                }

                $counter = $this->db->get_var($sql);
                if (!$counter) {
                    $counter = 0;
                }

                if ($row->DAYS == 0) {
                    if ($i != 1) {
                        $counter = 0;
                    }
                }

                $counters[$row->CODE][$i] = $counter;
            }
        }

        return $counters;
    }

    public function add($data)
    {
        $data = $this->setDefaults($data);

        return $this->addData("TRAIN", $data);
    }

    public function save($data, $where)
    {
        $data = $this->setDefaults($data);
        return $this->saveData("TRAIN", $data, $where);
    }

    public function delete($id)
    {
        return $this->db->query("delete from TRAIN where ID='$id'");
    }

    public function getTrainsForSelect()
    {
        return $this->db->get_results("select ID, DESCRIPTION from TRAIN order by CODE", ARRAY_N);
    }

    public function checkIsDeletable($id)
    {
        return true;
    }

    protected function setDefaults(array $data)
    {
        $action_date = 'F.ACTION_DATE';

        if (empty($data['ACTIONBOX'])) {
            $action_date = 'I.LAST_ACTION_DATE';
            $query = "from files\$files_all_info I where $action_date=CURRENT_DATE-{$data['DAYS']} ";
        } else {
            $query = "from files\$file_actions_all_info F,files\$files_all_info I where F.FILE_ID=I.FILE_ID AND $action_date=CURRENT_DATE-{$data['DAYS']} ";
        }

        if ($data['OPEN_FILES'] == "OPEN") {
            $query .= "AND I.DATE_CLOSED is null ";
        }
        if ($data['OPEN_FILES'] == "CLOSED") {
            $query .= "AND I.DATE_CLOSED not null ";
        }

        if (count($data['ACTIONBOX'])) {
            $query .= "AND (";
            foreach ($data['ACTIONBOX'] as $field => $value) {
                $query .= "F.ACTION_CODE='" . $value . "' OR ";
            }
            $query = substr($query, 0, strlen($query) - 4) . ") ";
        }
        if (count($data['STATEBOX'])) {
            $query .= "AND (";
            foreach ($data['STATEBOX'] as $field => $value) {
                $query .= "I.STATE_CODE='" . $value . "' OR ";
            }
            $query = substr($query, 0, strlen($query) - 4) . ") ";
        }

        if ($data['PAYMENTS'] == "Y") {
            $query .= " AND (select count(*) from FILES\$PAYMENTS where FILE_ID=I.FILE_ID and CREATION_DATE>CURRENT_DATE-{$data['DAYS']})>0 ";
        }
        if ($data['PAYMENTS'] == "N") {
            $query .= " AND (select count(*) from FILES\$PAYMENTS where FILE_ID=I.FILE_ID and CREATION_DATE>CURRENT_DATE-{$data['DAYS']})=0 ";
        }

        if (count($data['ACTIONBOX'])) {
            $actionCounter = 1;
        } else {
            $actionCounter = 0;
        }

        if ($data['OTHER_ACTIONS'] == "Y") {
            $query .= " AND (select count(*) from FILES\$FILE_ACTIONS where FILE_ID=I.FILE_ID and ACTION_DATE>=CURRENT_DATE-{$data['DAYS']})>=$actionCounter ";
        }
        if ($data['OTHER_ACTIONS'] == "N") {
            $query .= " AND (select count(*) from FILES\$FILE_ACTIONS where FILE_ID=I.FILE_ID and ACTION_DATE>=CURRENT_DATE-{$data['DAYS']})=$actionCounter ";
        }

        if ($data['EXTRA_RULES'] != "") {
            $query .= " AND ({$data['EXTRA_RULES']})";
        }


        if (!empty($data['TRAIN_TYPE'])) {
            $query .= " AND (SELECT TRAIN_TYPE FROM FILES\$DEBTORS D WHERE D.DEBTOR_ID = I.DEBTOR_ID) = '{$data['TRAIN_TYPE']}'";
        }


        if ($data['DAYS'] == 0) {
            $query = str_replace("{$action_date}=CURRENT_DATE-{$data['DAYS']}","1=1",$query);
            $query = str_replace("ACTION_DATE>=CURRENT_DATE-{$data['DAYS']}","1=1",$query);
        }


        $data['SQL'] = ($query);

        if (array_key_exists('ACTIONBOX', $data) && !empty($data['ACTIONBOX'])) {
            $data['ACTIONBOX'] = implode(',', $data['ACTIONBOX']);
        }

        if (array_key_exists('STATEBOX', $data) && !empty($data['STATEBOX'])) {
            $data['STATEBOX'] = implode(',', $data['STATEBOX']);
        }

        if (empty($data['TEMPLATE_ID'])) {
            $data['TEMPLATE_ID'] = '0';
        }

        if (empty($data['SETACTION'])) {
            $data['SETACTION'] = '0';
        }

        if (empty($data['STATE_ID'])) {
            $data['STATE_ID'] = '0';
        }
        return $data;
    }

}
