<?php

require_once 'application/models/Base.php';

class Application_Model_AgendaStates extends Application_Model_Base
{

    public function getList($collectorId = false)
    {
        $agendaStates = $this->functions->getUserSetting("AGENDA_STATES");
        $agendaStates = "'".str_replace(",","','",$agendaStates)."'";

        $extraQuery = "";
        if (!empty($collectorId)) {
            $extraQuery .= " AND COLLECTOR_ID = {$collectorId}";
        }



        $sql = "SELECT COUNT(*) AS COUNTER,STATE_CODE,STATE_DESCRIPTION,STATE_ID FROM FILES\$FILES_ALL_INFO
                WHERE STATE_CODE IN ({$agendaStates}) AND LAST_ACTION_DATE<=CURRENT_DATE AND DATE_CLOSED IS NULL $extraQuery
                GROUP BY STATE_CODE,STATE_DESCRIPTION,STATE_ID";
        $results = $this->db->get_results($sql);
        return $results;
    }


}

?>
