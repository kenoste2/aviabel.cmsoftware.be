<?php

require_once 'application/models/Base.php';

class Application_Model_StatisticsForClient extends Application_Model_Base
{
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

                $dateExtra = "(CURRENT_DATE - R.START_DATE) <=90";
                $aging[$type->CODE]['1Q'] = $this->getSumByValuta($dateExtra, $groupField, $type->GROUPCODE, $underwriterExtra, $collectorExtra, $lobExtra);

                $dateExtra = "(CURRENT_DATE - R.START_DATE) >90 AND (CURRENT_DATE - R.START_DATE) <=180";
                $aging[$type->CODE]['2Q'] = $this->getSumByValuta($dateExtra, $groupField, $type->GROUPCODE, $underwriterExtra, $collectorExtra, $lobExtra);

                $dateExtra = "(CURRENT_DATE - R.START_DATE) >180 AND (CURRENT_DATE - R.START_DATE) <=270";
                $aging[$type->CODE]['3Q'] = $this->getSumByValuta($dateExtra, $groupField, $type->GROUPCODE, $underwriterExtra, $collectorExtra, $lobExtra);

                $dateExtra = "(CURRENT_DATE - R.START_DATE) >270 AND (CURRENT_DATE - R.START_DATE) <= 360";
                $aging[$type->CODE]['4Q'] = $this->getSumByValuta($dateExtra, $groupField, $type->GROUPCODE, $underwriterExtra, $collectorExtra, $lobExtra);

                $dateExtra = "(CURRENT_DATE - R.START_DATE) >360 AND (CURRENT_DATE - R.START_DATE) <= 730";
                $aging[$type->CODE]['1Y'] = $this->getSumByValuta($dateExtra, $groupField, $type->GROUPCODE, $underwriterExtra, $collectorExtra, $lobExtra);

                $dateExtra = "(CURRENT_DATE - R.START_DATE) >730 AND (CURRENT_DATE - R.START_DATE) <= 1095";
                $aging[$type->CODE]['2Y'] = $this->getSumByValuta($dateExtra, $groupField, $type->GROUPCODE, $underwriterExtra, $collectorExtra, $lobExtra);

                $dateExtra = "(CURRENT_DATE - R.START_DATE) >1095";
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
                  (CURRENT_DATE - R.START_DATE) <=90 $collectorExtra");


        $aging['2Q'] = $this->db->get_row("select COUNT(*),SUM(R.AMOUNT)
                  from files\$references R
                  JOIN FILES\$FILES F ON F.FILE_ID = R.FILE_ID WHERE
                  (CURRENT_DATE - R.START_DATE) >90 AND (CURRENT_DATE - R.START_DATE) <=180 $collectorExtra");

        $aging['3Q'] = $this->db->get_row("select COUNT(*),SUM(R.AMOUNT)
                  from files\$references R
                  JOIN FILES\$FILES F ON F.FILE_ID = R.FILE_ID WHERE
                  (CURRENT_DATE - R.START_DATE) >180 AND (CURRENT_DATE - R.START_DATE) <=270 $collectorExtra");

        $aging['4Q'] = $this->db->get_row("select COUNT(*),SUM(R.AMOUNT)
                  from files\$references R
                  JOIN FILES\$FILES F ON F.FILE_ID = R.FILE_ID WHERE
                  (CURRENT_DATE - R.START_DATE) >270 AND (CURRENT_DATE - R.START_DATE) <= 360 $collectorExtra");

        $aging['1Y'] = $this->db->get_row("select COUNT(*),SUM(R.AMOUNT)
                  from files\$references R
                  JOIN FILES\$FILES F ON F.FILE_ID = R.FILE_ID WHERE
                  (CURRENT_DATE - R.START_DATE) >360 AND (CURRENT_DATE - R.START_DATE) <= 730 $collectorExtra");

        $aging['2Y'] = $this->db->get_row("select COUNT(*),SUM(R.AMOUNT)
                  from files\$references R
                  JOIN FILES\$FILES F ON F.FILE_ID = R.FILE_ID WHERE
                  (CURRENT_DATE - R.START_DATE) >730 AND (CURRENT_DATE - R.START_DATE) <= 1095 $collectorExtra");

        $aging['3Y'] = $this->db->get_row("select COUNT(*),SUM(R.AMOUNT)
                  from files\$references R
                  JOIN FILES\$FILES F ON F.FILE_ID = R.FILE_ID WHERE
                  (CURRENT_DATE - R.START_DATE) >1095 $collectorExtra");
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
}

?>
