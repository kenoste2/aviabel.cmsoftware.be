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

    public function getAging($underwriter = false, $collectorId = false, $lob = false)
    {

        $refObj = new Application_Model_FilesReferences();


        $refTypes = $refObj->getReferenceTypes(false,$underwriter, $lob);


        $aging = array();

        if (!empty($refTypes)) {
            foreach ($refTypes as $row) {

                $type = $row->REFERENCE_TYPE;

                if ($underwriter) {
                    $underwriterExtra = "AND R.CONTRACT_UNDERWRITER = '{$underwriter}'";
                }
                if ($collectorId) {
                    $collectorExtra = "AND F.COLLECTOR_ID = {$collectorId}";
                }

                if ($lob) {
                    $lobExtra = "AND R.CONTRACT_LINEOFBUSINESS = '{$lob}'";
                }

                $aging[$type]['1Q'] = $this->db->get_row("select COUNT(*),SUM(R.AMOUNT)
                  from files\$references R
                  JOIN FILES\$FILES F ON F.FILE_ID = R.FILE_ID WHERE
                  (CURRENT_DATE - R.START_DATE) <=90
                  AND REFERENCE_TYPE = '{$type}'
                  {$underwriterExtra}
                  {$collectorExtra}
                  {$lobExtra}
                  GROUP BY REFERENCE_TYPE");


                $aging[$type]['2Q'] = $this->db->get_row("select COUNT(*),SUM(R.AMOUNT)
                  from files\$references R
                  JOIN FILES\$FILES F ON F.FILE_ID = R.FILE_ID WHERE
                  (CURRENT_DATE - R.START_DATE) >90 AND (CURRENT_DATE - R.START_DATE) <=180
                  AND REFERENCE_TYPE = '{$type}'
                  {$underwriterExtra}
                  {$collectorExtra}
                  {$lobExtra}
                  GROUP BY REFERENCE_TYPE");

                $aging[$type]['3Q'] = $this->db->get_row("select COUNT(*),SUM(R.AMOUNT)
                  from files\$references R
                  JOIN FILES\$FILES F ON F.FILE_ID = R.FILE_ID WHERE
                  (CURRENT_DATE - R.START_DATE) >180 AND (CURRENT_DATE - R.START_DATE) <=270
                  AND REFERENCE_TYPE = '{$type}'
                  {$underwriterExtra}
                  {$collectorExtra}
                  {$lobExtra}
                  GROUP BY REFERENCE_TYPE");

                $aging[$type]['4Q'] = $this->db->get_row("select COUNT(*),SUM(R.AMOUNT)
                  from files\$references R
                  JOIN FILES\$FILES F ON F.FILE_ID = R.FILE_ID WHERE
                  (CURRENT_DATE - R.START_DATE) >270 AND (CURRENT_DATE - R.START_DATE) <= 360
                  AND REFERENCE_TYPE = '{$type}'
                  {$underwriterExtra}
                  {$collectorExtra}
                  {$lobExtra}
                  GROUP BY REFERENCE_TYPE");

                $aging[$type]['1Y'] = $this->db->get_row("select COUNT(*),SUM(R.AMOUNT)
                  from files\$references R
                  JOIN FILES\$FILES F ON F.FILE_ID = R.FILE_ID WHERE
                  (CURRENT_DATE - R.START_DATE) >360 AND (CURRENT_DATE - R.START_DATE) <= 730
                  AND REFERENCE_TYPE = '{$type}'
                  {$underwriterExtra}
                  {$collectorExtra}
                  {$lobExtra}
                  GROUP BY REFERENCE_TYPE");

                $aging[$type]['2Y'] = $this->db->get_row("select COUNT(*),SUM(R.AMOUNT)
                  from files\$references R
                  JOIN FILES\$FILES F ON F.FILE_ID = R.FILE_ID WHERE
                  (CURRENT_DATE - R.START_DATE) >730 AND (CURRENT_DATE - R.START_DATE) <= 1095
                  AND REFERENCE_TYPE = '{$type}'
                  {$underwriterExtra}
                  {$collectorExtra}
                  {$lobExtra}
                  GROUP BY REFERENCE_TYPE");

                $aging[$type]['3Y'] = $this->db->get_row("select COUNT(*),SUM(R.AMOUNT)
                  from files\$references R
                  JOIN FILES\$FILES F ON F.FILE_ID = R.FILE_ID WHERE
                  (CURRENT_DATE - R.START_DATE) >1095
                  AND REFERENCE_TYPE = '{$type}'
                  {$underwriterExtra}
                  {$collectorExtra}
                  {$lobExtra}
                  GROUP BY REFERENCE_TYPE");
            }
            return $aging;
        }
        return false;
    }

    public function getGeneralAging()
    {
        $aging = array();

        $aging['1Q'] = $this->db->get_row("select COUNT(*),SUM(R.AMOUNT)
                  from files\$references R
                  JOIN FILES\$FILES F ON F.FILE_ID = R.FILE_ID WHERE
                  (CURRENT_DATE - R.START_DATE) <=90");


        $aging['2Q'] = $this->db->get_row("select COUNT(*),SUM(R.AMOUNT)
                  from files\$references R
                  JOIN FILES\$FILES F ON F.FILE_ID = R.FILE_ID WHERE
                  (CURRENT_DATE - R.START_DATE) >90 AND (CURRENT_DATE - R.START_DATE) <=180");

        $aging['3Q'] = $this->db->get_row("select COUNT(*),SUM(R.AMOUNT)
                  from files\$references R
                  JOIN FILES\$FILES F ON F.FILE_ID = R.FILE_ID WHERE
                  (CURRENT_DATE - R.START_DATE) >180 AND (CURRENT_DATE - R.START_DATE) <=270");

        $aging['4Q'] = $this->db->get_row("select COUNT(*),SUM(R.AMOUNT)
                  from files\$references R
                  JOIN FILES\$FILES F ON F.FILE_ID = R.FILE_ID WHERE
                  (CURRENT_DATE - R.START_DATE) >270 AND (CURRENT_DATE - R.START_DATE) <= 360");

        $aging['1Y'] = $this->db->get_row("select COUNT(*),SUM(R.AMOUNT)
                  from files\$references R
                  JOIN FILES\$FILES F ON F.FILE_ID = R.FILE_ID WHERE
                  (CURRENT_DATE - R.START_DATE) >360 AND (CURRENT_DATE - R.START_DATE) <= 730");

        $aging['2Y'] = $this->db->get_row("select COUNT(*),SUM(R.AMOUNT)
                  from files\$references R
                  JOIN FILES\$FILES F ON F.FILE_ID = R.FILE_ID WHERE
                  (CURRENT_DATE - R.START_DATE) >730 AND (CURRENT_DATE - R.START_DATE) <= 1095");

        $aging['3Y'] = $this->db->get_row("select COUNT(*),SUM(R.AMOUNT)
                  from files\$references R
                  JOIN FILES\$FILES F ON F.FILE_ID = R.FILE_ID WHERE
                  (CURRENT_DATE - R.START_DATE) >1095");
        return $aging;

    }
}

?>
