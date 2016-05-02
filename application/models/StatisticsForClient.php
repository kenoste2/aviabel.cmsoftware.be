<?php

require_once 'application/models/Base.php';

class Application_Model_StatisticsForClient extends Application_Model_Base
{
    public function getAgingPeriodsOld($period){
        $periodsExtra = array('1Q' => '(CURRENT_DATE - R.ULTIMATE_DUE_DATE) <=90 AND (CURRENT_DATE - R.ULTIMATE_DUE_DATE) > 0',
                                '2Q' => '(CURRENT_DATE - R.ULTIMATE_DUE_DATE) >90 AND (CURRENT_DATE - R.ULTIMATE_DUE_DATE) <=180',
                                '3Q' => '(CURRENT_DATE - R.ULTIMATE_DUE_DATE) >180 AND (CURRENT_DATE - R.ULTIMATE_DUE_DATE) <=270',
                                '4Q' => '(CURRENT_DATE - R.ULTIMATE_DUE_DATE) >270 AND (CURRENT_DATE - R.ULTIMATE_DUE_DATE) <= 360',
                                '1Y' => '(CURRENT_DATE - R.ULTIMATE_DUE_DATE) >360 AND (CURRENT_DATE - R.ULTIMATE_DUE_DATE) <= 730',
                                '2Y' => '(CURRENT_DATE - R.ULTIMATE_DUE_DATE) >730 AND (CURRENT_DATE - R.ULTIMATE_DUE_DATE) <= 1095',
                                '3Y' => '(CURRENT_DATE - R.ULTIMATE_DUE_DATE) >1095',
                                'all' => '(CURRENT_DATE - R.ULTIMATE_DUE_DATE) > 0');
        return $periodsExtra[$period];
    }

    public function getAgingPeriods($period)
    {
        $today = date(("Y-m-d"));

        $min = "1900-01-01";
        $max = $today;

        if ($period == '1Q') {
            $min = date('Y-m-d',strtotime($today . "+1 days"));
            $min = date('Y-m-d',strtotime($min . "-3 months"));
        }

        if ($period == '2Q') {
            $min = date('Y-m-d',strtotime($today . "+1 days"));
            $min = date('Y-m-d',strtotime($min . "-6 months"));
                
            $max = date('Y-m-d',strtotime($today . "+1 days"));
            $max = date('Y-m-d',strtotime($max . "-3 months"));
            $max = date('Y-m-d',strtotime($max . "-1 days"));
        }

        if ($period == '3Q') {
            $min = date('Y-m-d',strtotime($today . "+1 days"));
            $min = date('Y-m-d',strtotime($min . "-9 months"));
    
            $max = date('Y-m-d',strtotime($today . "+1 days"));
            $max = date('Y-m-d',strtotime($max . "-6 months"));
            $max = date('Y-m-d',strtotime($max . "-1 days"));
        }

        if ($period == '4Q') {
            $min = date('Y-m-d',strtotime($today . "+1 days"));
            $min = date('Y-m-d',strtotime($min . "-12 months"));
    
            $max = date('Y-m-d',strtotime($today . "+1 days"));
            $max = date('Y-m-d',strtotime($max . "-9 months"));
            $max = date('Y-m-d',strtotime($max . "-1 days"));
         }

        if ($period == '1Y') {
            $min = date('Y-m-d',strtotime($today . "+1 days"));
            $min = date('Y-m-d',strtotime($min . "-24 months"));
    
            $max = date('Y-m-d',strtotime($today . "+1 days"));
            $max = date('Y-m-d',strtotime($max . "-12 months"));
            $max = date('Y-m-d',strtotime($max . "-1 days"));
        }
        
        if ($period == '2Y') {
            $min = date('Y-m-d',strtotime($today . "+1 days"));
            $min = date('Y-m-d',strtotime($min . "-36 months"));
    
            $max = date('Y-m-d',strtotime($today . "+1 days"));
            $max = date('Y-m-d',strtotime($max . "-24 months"));
            $max = date('Y-m-d',strtotime($max . "-1 days"));
        }

        if ($period == '3Y') {
            $max = date('Y-m-d',strtotime($today . "+1 days"));
            $max = date('Y-m-d',strtotime($max . "-36 months"));
            $max = date('Y-m-d',strtotime($max . "-1 days"));
        }
        
        $extra ="(R.ULTIMATE_DUE_DATE <= '{$max}') AND (R.ULTIMATE_DUE_DATE >= '{$min}')";

        return $extra;
    }


    public function getStats($client_id)
    {
        return $this->db->get_results("select CREATION_YEAR,CREATION_MONTH,NUMBER_OF_FILES,AMOUNT,COSTS,INTERESTS,MISSED_FILES,
          MISSED_AMOUNT,MISSED_INTEREST,MISSED_COSTS,PAYED_AMOUNT,PAYED_COSTS,PAYED_INTERESTS,EXTERNAL_NO_COMMISSION,EXTERNAL_WITH_COMMISSION
          from REPORTS\$STATISTICTS_FOR_CLIENT($client_id)
          order by CREATION_YEAR DESC ,CREATION_MONTH DESC");
    }

    public function getStatsTotal($client_id)
    {
        return $this->db->get_row("select sum(NUMBER_OF_FILES) as NUMBER_OF_FILES,sum(AMOUNT) as AMOUNT,sum(COSTS) as COSTS,
          sum(INTERESTS) as INTERESTS,sum(MISSED_FILES) as MISSED_FILES,sum(MISSED_AMOUNT) as MISSED_AMOUNT,sum(MISSED_INTEREST) as MISSED_INTEREST,
          sum(MISSED_COSTS) as MISSED_COSTS,sum(PAYED_AMOUNT) as PAYED_AMOUNT,sum(PAYED_COSTS) as PAYED_COSTS,sum(PAYED_INTERESTS) as PAYED_INTERESTS,
          sum(EXTERNAL_NO_COMMISSION) as EXTERNAL_NO_COMMISSION,sum(EXTERNAL_WITH_COMMISSION) as EXTERNAL_WITH_COMMISSION
          from REPORTS\$STATISTICTS_FOR_CLIENT($client_id)");
    }

    public function getAging($underwriter = false, $collectorId = false, $lob = false, $groupBy = false )
    {

        $refObj = new Application_Model_FilesReferences();


        $refTypes = $refObj->getReferenceTypes(false,$underwriter, $lob);


        switch ($groupBy) {
            default:
            case 'CASEWORKERS':
                $types = $this->db->get_results("SELECT COLLECTOR_ID AS GROUPCODE,CODE FROM SYSTEM\$COLLECTORS WHERE ACTIF='Y' ORDER BY CODE ");
                $groupField = 'F.COLLECTOR_ID';
                break;
                case 'LINEOBUSINESS':
                $types = $this->db->get_results("SELECT CONTRACT_LINEOFBUSINESS AS GROUPCODE, CONTRACT_LINEOFBUSINESS AS CODE FROM FILES\$REFERENCES GROUP BY CONTRACT_LINEOFBUSINESS ORDER BY CONTRACT_LINEOFBUSINESS");
                $groupField = 'R.CONTRACT_LINEOFBUSINESS';
                break;
            case 'UNDERWRITERS':
                $types = $this->db->get_results("SELECT CONTRACT_UNDERWRITER AS GROUPCODE, CONTRACT_UNDERWRITER AS CODE FROM FILES\$REFERENCES GROUP BY CONTRACT_UNDERWRITER ORDER BY CONTRACT_UNDERWRITER");
                $groupField = 'R.CONTRACT_UNDERWRITER';
                break;
        }

                $aging = array();

                if (!empty($types)) {
                    foreach ($types as $type) {

                        if (empty($type->CODE)) {
                            continue;
                        }

                        if ($underwriter) {
                            $underwriterExtra = "AND R.CONTRACT_UNDERWRITER = '{$underwriter}'";
                        }
                        if ($collectorId) {
                            $collectorExtra = "AND F.COLLECTOR_ID = {$collectorId}";
                        }
                        if ($lob) {
                            $lobExtra = "AND R.CONTRACT_LINEOFBUSINESS = '{$lob}'";
                }

                $dateExtra = $this->getAgingPeriods('1Q');
                $aging[$type->CODE]['1Q'] = $this->getSumByValuta($dateExtra, $groupField, $type->GROUPCODE, $underwriterExtra, $collectorExtra, $lobExtra);

                $dateExtra = $this->getAgingPeriods('2Q');
                $aging[$type->CODE]['2Q'] = $this->getSumByValuta($dateExtra, $groupField, $type->GROUPCODE, $underwriterExtra, $collectorExtra, $lobExtra);

                $dateExtra = $this->getAgingPeriods('3Q');
                $aging[$type->CODE]['3Q'] = $this->getSumByValuta($dateExtra, $groupField, $type->GROUPCODE, $underwriterExtra, $collectorExtra, $lobExtra);

                $dateExtra = $this->getAgingPeriods('4Q');
                $aging[$type->CODE]['4Q'] = $this->getSumByValuta($dateExtra, $groupField, $type->GROUPCODE, $underwriterExtra, $collectorExtra, $lobExtra);

                $dateExtra = $this->getAgingPeriods('1Y');
                $aging[$type->CODE]['1Y'] = $this->getSumByValuta($dateExtra, $groupField, $type->GROUPCODE, $underwriterExtra, $collectorExtra, $lobExtra);

                $dateExtra = $this->getAgingPeriods('2Y');
                $aging[$type->CODE]['2Y'] = $this->getSumByValuta($dateExtra, $groupField, $type->GROUPCODE, $underwriterExtra, $collectorExtra, $lobExtra);

                $dateExtra = $this->getAgingPeriods('3Y');
                $aging[$type->CODE]['3Y'] = $this->getSumByValuta($dateExtra, $groupField, $type->GROUPCODE, $underwriterExtra, $collectorExtra, $lobExtra);

            }
            return $aging;
        }
        return false;
    }

    public function getGeneralAging($collectorId = false)
    {
        $aging = array();

        $collectorExtra = "";
        if ($collectorId) {
            $collectorExtra = "AND F.COLLECTOR_ID = {$collectorId}";
        }


        $aging['1Q'] = $this->db->get_row("select COUNT(*),SUM(R.AMOUNT)
                  from files\$references R
                  JOIN FILES\$FILES F ON F.FILE_ID = R.FILE_ID WHERE
                  (CURRENT_DATE - R.ULTIMATE_DUE_DATE) <=90 $collectorExtra");


        $aging['2Q'] = $this->db->get_row("select COUNT(*),SUM(R.AMOUNT)
                  from files\$references R
                  JOIN FILES\$FILES F ON F.FILE_ID = R.FILE_ID WHERE
                  (CURRENT_DATE - R.ULTIMATE_DUE_DATE) >90 AND (CURRENT_DATE - R.ULTIMATE_DUE_DATE) <=180 $collectorExtra");

        $aging['3Q'] = $this->db->get_row("select COUNT(*),SUM(R.AMOUNT)
                  from files\$references R
                  JOIN FILES\$FILES F ON F.FILE_ID = R.FILE_ID WHERE
                  (CURRENT_DATE - R.ULTIMATE_DUE_DATE) >180 AND (CURRENT_DATE - R.ULTIMATE_DUE_DATE) <=270 $collectorExtra");

        $aging['4Q'] = $this->db->get_row("select COUNT(*),SUM(R.AMOUNT)
                  from files\$references R
                  JOIN FILES\$FILES F ON F.FILE_ID = R.FILE_ID WHERE
                  (CURRENT_DATE - R.ULTIMATE_DUE_DATE) >270 AND (CURRENT_DATE - R.ULTIMATE_DUE_DATE) <= 360 $collectorExtra");

        $aging['1Y'] = $this->db->get_row("select COUNT(*),SUM(R.AMOUNT)
                  from files\$references R
                  JOIN FILES\$FILES F ON F.FILE_ID = R.FILE_ID WHERE
                  (CURRENT_DATE - R.ULTIMATE_DUE_DATE) >360 AND (CURRENT_DATE - R.ULTIMATE_DUE_DATE) <= 730 $collectorExtra");

        $aging['2Y'] = $this->db->get_row("select COUNT(*),SUM(R.AMOUNT)
                  from files\$references R
                  JOIN FILES\$FILES F ON F.FILE_ID = R.FILE_ID WHERE
                  (CURRENT_DATE - R.ULTIMATE_DUE_DATE) >730 AND (CURRENT_DATE - R.ULTIMATE_DUE_DATE) <= 1095 $collectorExtra");

        $aging['3Y'] = $this->db->get_row("select COUNT(*),SUM(R.AMOUNT)
                  from files\$references R
                  JOIN FILES\$FILES F ON F.FILE_ID = R.FILE_ID WHERE
                  (CURRENT_DATE - R.ULTIMATE_DUE_DATE) >1095 $collectorExtra");
        return $aging;

    }

    public function getSumByValuta($dateExtra, $groupField, $groupCode, $underwriterExtra = false, $collectorExtra = false, $lobExtra = false)
    {
        $sumValutaTotal = 0;
        $sumCount = 0;

        $valutaSums = $this->db->get_results("select R.VALUTA, COUNT(*),SUM(R.AMOUNT)
                  from files\$references R
                  JOIN FILES\$FILES F ON F.FILE_ID = R.FILE_ID WHERE
                  {$dateExtra}
                  AND {$groupField} =  '{$groupCode}'
                  {$underwriterExtra}
                  {$collectorExtra}
                  {$lobExtra}
		GROUP BY R.VALUTA");

        foreach ($valutaSums as $valutaTotal)
        {
            $valuta = $valutaTotal->VALUTA;
            $conversionRates = $this->functions->getCurrencyRates($date = false);

            $sumValutaTotal += $valutaTotal->SUM / $conversionRates[$valuta]['RATE'];
            $sumCount += $valutaTotal->COUNT;
        }

        $roundSumTotal = round($sumValutaTotal, 2);

        $return = (object)[
            'COUNT' => $sumCount,
            'SUM' => $roundSumTotal,
        ];

        return $return;
    }

    public function getAgingExport($period, $limitResults, $type = false, $groupby = false, $collector = false, $underwriter = false, $lob = false, $xls = false) {

        $dateExtra = "";
        if ($period == '1q'){
            $dateExtra = $this->getAgingPeriods('1Q');
        }
        if ($period == '2q'){
            $dateExtra = $this->getAgingPeriods('2Q');
        }
        if ($period == '3q'){
            $dateExtra = $this->getAgingPeriods('3Q');
        }
        if ($period == '4q'){
            $dateExtra = $this->getAgingPeriods('4Q');
        }
        if ($period == '1y'){
            $dateExtra = $this->getAgingPeriods('1Y');
        }
        if ($period == '2y'){
            $dateExtra = $this->getAgingPeriods('2Y');
        }
        if ($period == '3y'){
            $dateExtra = $this->getAgingPeriods('3Y');
        }
        if ($period == 'all'){
            $dateExtra = $this->getAgingPeriods('all');
        }


        if (empty($groupby) OR $groupby == 'CASEWORKERS' OR $groupby == 'DEFAULT') {
            $typeAdd ="F.COLLECTOR_CODE";
        }
        if ($groupby == 'LINEOBUSINESS') {
            $typeAdd ="R.CONTRACT_LINEOFBUSINESS";
        }
        if ($groupby == 'UNDERWRITERS') {
            $typeAdd ="R.CONTRACT_UNDERWRITER";
        }

        $typeExtra= "";
        if (!empty($type)) {
            $typeExtra = "AND ".$typeAdd." = '".$type."'";
        }

        $limitExtra = "";
        if (!empty($xls)) {
            $limitExtra = "first ".$limitResults;
        }

        $query = "SELECT {$limitExtra} SUM(R.SALDO_AMOUNT) AS AMOUNT,
                    F.REFERENCE,
                    F.DEBTOR_NAME,
                    F.COLLECTOR_CODE,
                    R.VALUTA,
                    R.LEDGER_ACCOUNT,
                    F.LAST_ACTION_DATE,
                    F.STATE_CODE,
                    F.STATE_DESCRIPTION,
                    R.CONTRACT_UNDERWRITER,
                    F.FILE_ID,
                    SUM(R.DISPUTE) AS DISPUTE,
                    R.DISPUTE_STATUS
                  FROM files\$references R 
                  JOIN files\$files_all_info F ON F.FILE_ID = R.FILE_ID
                  WHERE {$dateExtra}
                  {$typeExtra}
                  GROUP BY F.REFERENCE,
                    F.DEBTOR_NAME,
                    F.DEBTOR_NAME,
                    F.COLLECTOR_CODE,
                    R.VALUTA,
                    R.LEDGER_ACCOUNT,
                    F.LAST_ACTION_DATE,
                    F.STATE_CODE,
                    F.STATE_DESCRIPTION,
                    R.CONTRACT_UNDERWRITER,
                    F.FILE_ID,
                    R.DISPUTE_STATUS";

        $results = $this->db->get_results($query);

        $returnArray = array();
        $brokerArray = array();

        foreach ($results as $row) {
            $currentBroker = substr($row->REFERENCE, 0,6);

            if (empty($brokerArray[$currentBroker])) {
                $brokerArray[$currentBroker] = $this->db->get_var("SELECT SUM(SALDO_AMOUNT) FROM FILES\$FILES WHERE REFERENCE LIKE '$currentBroker%'");
            }

            $sql = "SELECT first 5 REMARK from files\$remarks WHERE FILE_ID = '{$row->FILE_ID}' ORDER BY REMARK_ID DESC";
            $getRemarks = $this->db->get_results($sql);
            $remarksArray = array();
            foreach ($getRemarks as $remark) {
                $remarksArray[] = $remark->REMARK;
            }
            $remarksString = implode(" | ",$remarksArray);

            $row->BROKER_AMOUNT = $brokerArray[$currentBroker];
            $row->REMARKS = $remarksString;

            if ($row->DISPUTE == 0) {
                $row->DISPUTE_STATUS = "";
            }

            $row->PERIOD = $period;
            $returnArray[] = $row;

            $valuta = $row->VALUTA;
            $row->AMOUNT_EUR = "";
            $row->BROKER_AMOUNT_EUR = "";
            if ($valuta != 'EUR') {
                $conversionRates = $this->functions->getCurrencyRates($date = false);
                $amount = $row->AMOUNT / $conversionRates[$valuta]['RATE'];
                $row->AMOUNT_EUR = round($amount, 2);
                $brokerAmount = $row->BROKER_AMOUNT / $conversionRates[$valuta]['RATE'];
                $row->BROKER_AMOUNT_EUR = round($brokerAmount, 2);
            }
        }

        return $returnArray;
    }


}

?>
