<?php

require_once 'application/controllers/BaseDebtorController.php';

class DebtorSubdebtorsController extends BaseDebtorController {

    public function viewAction() {

        $debtorsObj = new Application_Model_Debtors();
        $usedIds = array();

        $debtors = $this->getSubdebtors($debtorsObj, $this->debtor->DEBTOR_ID, $usedIds, 0);

        $this->view->subDebtors = $debtors;
    }

    /**
     * @param $debtorsObj
     * @param $debtorId
     * @param $usedIds
     * @param $depth
     * @return array
     */
    public function getSubdebtors($debtorsObj, $debtorId, $usedIds, $depth)
    {
        if($depth >= 3) {
            return array();
        }
        $debtors = $debtorsObj->getSubdebtorsByDebtorId($debtorId, $usedIds);
        $usedIds []= $debtorId;
        $finalDebtors = array();
        if(count($debtors) > 0) {
            foreach ($debtors as $debtor) {
                $finalDebtors []= array('debtor' => $debtor, 'depth' => $depth);
                $subdebtors = $this->getSubdebtors($debtorsObj, $debtor->DEBTOR_ID, $usedIds, $depth + 1, $debtors);

                if(count($subdebtors) > 0) {
                    foreach($subdebtors as $subdebtor) {
                        $finalDebtors []= $subdebtor;
                    }
                }
            }
        }
        return $finalDebtors;
    }

}

