<?php

require_once 'application/controllers/BaseDebtorController.php';

class SettingsCurrencyController extends BaseController
{
    public function viewAction() {
        $this->checkAccessAndRedirect(array('settings-currency/view'));

        $this->view->bread = $this->functions->T("menu_settings") . "->" . $this->functions->T("exchengerate_c");
        $this->view->printButton = true;

        $form = new Application_Form_Currency();

        $date = false;

        if ($this->getRequest()->isPost()) {
            if ($form->isValid($_POST)) {
                $gegevens = $form->getValues();
                $date = $gegevens['CREATION_DATE'];
            } else {
                $this->view->formError = true;
                $this->view->errors = $form->getErrors();
            }
        }

        $obj = new Application_Model_CommonFunctions();
        $getCurrencyRates = $obj->getCurrencyRates($date);

        $this->view->currencyRates = $getCurrencyRates;
        $this->view->form = $form;
    }


}

