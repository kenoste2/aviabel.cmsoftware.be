<?php

require_once 'application/controllers/BaseClientController.php';

class ClientDetailController extends BaseClientController {

    public function viewAction() {
        global $config;

        $this->view->printButton = true;

        $obj = new Application_Model_Clients();
        $clientData = $obj->getArrayData($this->clientId);

        $generalForm = new Application_Form_GeneralClient();
        $data = array();
        if ($this->getRequest()->isPost()) {
            if ($generalForm->isValid($_POST)) {
                
                $update = $data = $generalForm->getValues();

                $update['CURRENT_INTREST_PERCENT'] = $this->functions->dbBedrag($update['CURRENT_INTREST_PERCENT']);
                $update['CURRENT_COST_PERCENT'] = $this->functions->dbBedrag($update['CURRENT_COST_PERCENT']);
                $update['CURRENT_INTREST_MINIMUM'] = $this->functions->dbBedrag($update['CURRENT_INTREST_MINIMUM']);
                $update['CURRENT_COST_MINIMUM'] = $this->functions->dbBedrag($update['CURRENT_COST_MINIMUM']);

                
                if (empty($update['INVOICE_CONTACT'])) {
                    $data['INVOICE_CONTACT'] = $update['INVOICE_CONTACT'] = $update['NAME'];
                }
                if (empty($update['INVOICE_ADDRESS'])) {
                    $data['INVOICE_ADDRESS'] = $update['INVOICE_ADDRESS'] = $update['ADDRESS'];
                }
                if (empty($update['INVOICE_ZIP_CODE'])) {
                    $data['INVOICE_ZIP_CODE'] = $update['INVOICE_ZIP_CODE'] = $update['ZIP_CODE'];
                }
                if (empty($update['INVOICE_CITY'])) {
                    $data['INVOICE_CITY'] = $update['INVOICE_CITY'] = $update['CITY'];
                }
                if (empty($update['INVOICE_CONTACT'])) {
                    $data['INVOICE_CONTACT'] = $update['INVOICE_CONTACT'] = $update['NAME'];
                }
                if (empty($update['INVOICE_COUTRY_ID'])) {
                    $data['INVOICE_COUNTRY_ID'] = $update['INVOICE_COUNTRY_ID'] = $update['COUNTRY_ID'];
                }

                $passError = false;
                $updatePassword = false;
                if (!empty($update['PASSWORD'])) {
                    $updatePassword = true;
                    $valide = $this->functions->validatePassword($update["PASSWORD"], $update["PASSWORD2"]);
                    if ($valide !== true) {
                        $this->view->generalPassError = true;
                        $passError = true;
                    }
                }

                if (!$passError && $updatePassword) {

                    $usersObj = new Application_Model_Users();

                    $userId = $usersObj->getUserByClientId($this->clientId);
                    if (empty($userId)) {
                        $userData = array(
                            'PASS' => $update["PASSWORD"],
                            'CLIENT_ID' => $this->clientId,
                            'CODE' => $clientData['CODE'],
                            'NAME' => $clientData['NAME'],
                            'ADDRESS' => $clientData['ADDRESS'],
                            'E_MAIL' => $clientData['E_MAIL'],
                            'ZIP_CODE_ID' => $clientData['ZIP_CODE_ID'],
                            'RIGHTS' => '5',
                        );
                        $userId = $usersObj->createUser($userData,"USER_ID");
                    } else {
                        $usersObj->updatePassword($userId, $update["PASSWORD"]);
                        unset($update['PASSWORD']);
                        unset($update['PASSWORD2']);
                    }

                    $this->view->generalFormSaved = true;
                }
                unset($update['PASSWORD']);
                unset($update['PASSWORD2']);
                $obj->update($update, $this->clientId);
            } else {
                $this->view->generalFormError = true;
                $this->view->errors = $generalForm->getErrors();
            }
        } else {
            $data = $clientData;
            $data['CURRENT_INTREST_PERCENT'] = $this->functions->amount($data['CURRENT_INTREST_PERCENT']);
            $data['CURRENT_COST_PERCENT'] = $this->functions->amount($data['CURRENT_COST_PERCENT']);
            $data['CURRENT_INTREST_MINIMUM'] = $this->functions->amount($data['CURRENT_INTREST_MINIMUM']);
            $data['CURRENT_COST_MINIMUM'] = $this->functions->amount($data['CURRENT_COST_MINIMUM']);
            $data['MAINCLIENT'] = $obj->getMainClient($this->clientId);

        }
        $generalForm->populate($data);
        $this->view->generalForm = $generalForm;
        
        $objClientsContacts = new Application_Model_ClientsContacts();
        $this->view->contacts = $objClientsContacts->getClientContacts($this->clientId);
    }

}

