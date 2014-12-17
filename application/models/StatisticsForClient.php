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

    public function getAging($clientId)
    {

        $refObj = new Application_Model_FilesReferences();


        $refTypes = $refObj->getReferenceTypes($clientId);


        $aging = array();

        if (!empty($refTypes)) {
            foreach ($refTypes as $row) {

                $type = $row->REFERENCE_TYPE;

                $aging[$type]['0'] = $this->db->get_row("select COUNT(*),SUM(R.AMOUNT)
                  from files\$references R
                  JOIN FILES\$FILES F ON F.FILE_ID = R.FILE_ID WHERE
                  F.CLIENT_ID = {$clientId} AND
                  (CURRENT_DATE - R.START_DATE) <1
                  AND REFERENCE_TYPE = '{$type}'
                  GROUP BY REFERENCE_TYPE");


                $aging[$type]['1-30'] = $this->db->get_row("select COUNT(*),SUM(R.AMOUNT)
                  from files\$references R
                  JOIN FILES\$FILES F ON F.FILE_ID = R.FILE_ID WHERE
                  F.CLIENT_ID = {$clientId} AND
                  (CURRENT_DATE - R.START_DATE) >=1 AND (CURRENT_DATE - R.START_DATE) <=30
                  AND REFERENCE_TYPE = '{$type}'
                  GROUP BY REFERENCE_TYPE");

                $aging[$type]['31-90'] = $this->db->get_row("select COUNT(*),SUM(R.AMOUNT)
                  from files\$references R
                  JOIN FILES\$FILES F ON F.FILE_ID = R.FILE_ID WHERE
                  F.CLIENT_ID = {$clientId} AND
                  (CURRENT_DATE - R.START_DATE) >=31 AND (CURRENT_DATE - R.START_DATE) <=90
                  AND REFERENCE_TYPE = '{$type}'
                  GROUP BY REFERENCE_TYPE");

                $aging[$type]['91-180'] = $this->db->get_row("select COUNT(*),SUM(R.AMOUNT)
                  from files\$references R
                  JOIN FILES\$FILES F ON F.FILE_ID = R.FILE_ID WHERE
                  F.CLIENT_ID = {$clientId} AND
                  (CURRENT_DATE - R.START_DATE) >=91 AND (CURRENT_DATE - R.START_DATE) <= 180
                  AND REFERENCE_TYPE = '{$type}'
                  GROUP BY REFERENCE_TYPE");

                $aging[$type]['181-365'] = $this->db->get_row("select COUNT(*),SUM(R.AMOUNT)
                  from files\$references R
                  JOIN FILES\$FILES F ON F.FILE_ID = R.FILE_ID WHERE
                  F.CLIENT_ID = {$clientId} AND
                  (CURRENT_DATE - R.START_DATE) >=181 AND (CURRENT_DATE - R.START_DATE) <= 365
                  AND REFERENCE_TYPE = '{$type}'
                  GROUP BY REFERENCE_TYPE");

                $aging[$type]['366-730'] = $this->db->get_row("select COUNT(*),SUM(R.AMOUNT)
                  from files\$references R
                  JOIN FILES\$FILES F ON F.FILE_ID = R.FILE_ID WHERE
                  F.CLIENT_ID = {$clientId} AND
                  (CURRENT_DATE - R.START_DATE) >=366 AND (CURRENT_DATE - R.START_DATE) <= 730
                  AND REFERENCE_TYPE = '{$type}'
                  GROUP BY REFERENCE_TYPE");

                $aging[$type]['731+'] = $this->db->get_row("select COUNT(*),SUM(R.AMOUNT)
                  from files\$references R
                  JOIN FILES\$FILES F ON F.FILE_ID = R.FILE_ID WHERE
                  F.CLIENT_ID = {$clientId} AND
                  (CURRENT_DATE - R.START_DATE) >=731
                  AND REFERENCE_TYPE = '{$type}'
                  GROUP BY REFERENCE_TYPE");
            }
            return $aging;
        }
        return false;
    }


}

?>
