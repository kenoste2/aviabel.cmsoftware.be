<?php

require_once 'application/models/Base.php';

class Application_Model_DebtorExternalAccess extends Application_Model_Base {

    public function getDebtorIdByExternalAccessCode($accessCode) {
        $escAccessCode = $this->db->escape($accessCode);

        $sql = "SELECT DEBTOR_ID FROM FILES\$DEBTORS
                WHERE EXTERNAL_AUTH_CODE = '{$escAccessCode}' AND EXTERNAL_AUTH_EXPIRATION >= CURRENT_TIME";

        return $this->db->get_var($sql);
    }

    public function createExternalAccessCode($debtorId) {
        $debtorsObj = new Application_Model_Debtors();
        $debtor = $debtorsObj->getDebtor($debtorId);

        //NOTE: only create a new access code when none available or the old one has expired.
        if(!$debtor->EXTERNAL_AUTH_EXPIRATION || !$debtor->EXTERNAL_AUTH_CODE)
        {
            return $this->createAndSetAccessCode($debtorId);
        }
        $now = new DateTime();
        $expiration = DateTime::createFromFormat('Y-m-d H:i:s', $debtor->EXTERNAL_AUTH_EXPIRATION);
        if(!$expiration || $expiration < $now) {
            return $this->createAndSetAccessCode($debtorId);
        }
        return $debtor->EXTERNAL_AUTH_CODE;
    }

    public function sendExternalAccessInviteMail($debtor) {
        global $config;


        $rawMailSubject = "Uitnodiging overzicht openstaande facturen";
        $rawMailBody = "Beste klant,

                    We merkten op dat u nog een aantal openstaande facturen bij ons hebt staan. U kan ze bekijken
                    op onderstaande link. Eventueel kan u ook op nalatigheden van onze kant duiden.

                    xEXTERNAL_ACCESS_LINKx

                    Vriendelijke groeten,

                    xCLIENT_NAMEx";

        $mailBody = $this->ReplaceReplacementFieldsBasedOnDebtor($debtor, $rawMailBody);
        $mailSubject = $this->ReplaceReplacementFieldsBasedOnDebtor($debtor, $rawMailSubject);

        //TODO: call method from somewhere

        //TODO: remove test statement below
        print "send mail: <br>to: {$debtor->EMAIL}<br>subject: {$mailSubject}<br>body HTML: {$mailBody}<br>body text: {$mailBody}<br>from: {$config->fromEmail}";
        //TODO: comment mail back in on LIVE environment.
        //$mailObj->sendMail($debtor->EMAIL, $mailSubject, $mailBody, $mailBody, $config->fromEmail, false, false, true);
    }

    public function sendDisputeWarningMail($reference) {
        global $config;

        $rawMailSubject = "Dispuut gesuggereerd voor factuur xREFERENCEx #xFILE_NRx#";
        $rawMailBody = "Beste Medewerker,

                    De debiteur xDEBTOR_NAMEx, in dossier xFILE_NRx heeft een dispuut gesuggereerd voor factuur xREFERENCEx met de volgende boodschap.

                    ------
                    xMESSAGEx
                    ------

                    Vriendelijke groeten,

                    Het CM-softwarepakket";

        $mailBody = $this->replaceReplacementFieldsBasedOnReference($reference, $rawMailBody);
        $mailSubject = $this->replaceReplacementFieldsBasedOnReference($reference, $rawMailSubject);

        $mailObj = new Application_Model_Mail();

        //TODO: remove test statement below
        print "send mail: <br>to: {$config->fromEmail}<br>subject: {$mailSubject}<br>body HTML: {$mailBody}<br>body text: {$mailBody}<br>from: {$config->fromEmail}";
        //TODO: comment mail back in on LIVE environment.
        //$mailObj->sendMail($config->fromEmail, $mailSubject, $mailBody, $mailBody, $config->fromEmail, false, false, true);
    }

    /**
     * @param $reference
     * @param $rawText
     * @return mixed
     */
    public function replaceReplacementFieldsBasedOnReference($reference, $rawText)
    {
        $debtorsObj = new Application_Model_Debtors();
        $filesObj = new Application_Model_Files();
        $debtor = $debtorsObj->getDebtorByReferenceId($reference->REFERENCE_ID);
        $file = $filesObj->getFileByReferenceId($reference->REFERENCE_ID);

        $replacementFields = array(
            'REFERENCE' => $reference->REFERENCE,
            'FILE_NR' => $file->FILE_NR,
            'DEBTOR_NAME' => $debtor->NAME,
            'MESSAGE' => $reference->DEBTOR_DISPUTE_COMMENT
        );

        return $this->replaceInText($rawText, $replacementFields);
    }

    public function replaceReplacementFieldsBasedOnDebtor($debtor, $rawText) {
        global $config;

        $externalAccessCode = $this->createExternalAccessCode($debtor->DEBTOR_ID);
        $replacementFields = array(
            //TODO: add proper client name
            'CLIENT_NAME' => 'client name',
            'EXTERNAL_ACCESS_LINK' => "{$config->rootLocation}/external-access/check-invoices/a/{$externalAccessCode}"
        );

        return $this->replaceInText($rawText, $replacementFields);
    }

    /**
     * @param $rawText
     * @param $replacementFields
     * @return mixed
     */
    public function replaceInText($rawText, $replacementFields)
    {
        $text = $rawText;
        foreach ($replacementFields as $key => $replacementField) {
            $text = str_replace("x{$key}x", $replacementField, $text);
        }
        return $text;
    }

    /**
     * @param $debtorId
     * @return null|string
     */
    public function createAndSetAccessCode($debtorId)
    {
        $externalAccessToken = uniqid("", true);
        $escDebtorId = $this->db->escape($debtorId);
        $date = new DateTime();
        $date->add(new DateInterval('P30D'));
        $strDate = $date->format("Y-m-d H:i:s");

        $sql = "UPDATE FILES\$DEBTORS
                SET EXTERNAL_AUTH_CODE = '{$externalAccessToken}',
                    EXTERNAL_AUTH_EXPIRATION = '{$strDate}'
                WHERE DEBTOR_ID = {$escDebtorId}";
        $this->db->query($sql);
        return $externalAccessToken;
    }
}

?>
