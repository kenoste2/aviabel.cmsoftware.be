<?php

require_once 'application/models/Base.php';

class Application_Model_DebtorExternalAccess extends Application_Model_Base {

    public function getDebtorIdByExternalAccessCode($accessCode) {
        $escAccessCode = $this->db->escape($accessCode);

        $sql = "SELECT DEBTOR_ID FROM FILES\$DEBTORS
                WHERE EXTERNAL_AUTH_CODE = '{$escAccessCode}' AND EXTERNAL_AUTH_EXPIRATION >= CURRENT_TIME";

        return $this->db->get_var($sql);
    }

    public function shouldBeEditableByDebtor($disputeStatus) {
        return !$disputeStatus || $disputeStatus === 'DEBTOR_REMARK';
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

    public function sendExternalAccessInviteMail($debtor, $file) {
        global $config;

        $templates = array(
            "FRENCH" => array(
                "subject" => "Invitation pour le résumé de vos factures ouvertes",
                "body" => "Monsieur, madame,

                Nous avons noté que vous avez encore quelques factures ouvertes chez nous.
                Veuillez les contrôler sur le lien ci-dessous.

                xEXTERNAL_ACCESS_LINKx

                Bien à vous,

                xCLIENT_NAMEx"
            ),
            "DUTCH" => array(
                "subject" => "Uitnodiging overzicht openstaande facturen",
                "body" => "Beste klant,

                    We merkten op dat u nog een aantal openstaande facturen bij ons open hebt staan. U kan ze bekijken
                    op onderstaande link.

                    xEXTERNAL_ACCESS_LINKx

                    Vriendelijke groeten,

                    xCLIENT_NAMEx"
            ),
            "ENGLISH" => array(
                "subject" => "Invitation open invoice overview",
                "body" => "Dear customer,

                We have noticed you still have a number of open invoices with us. You can review them via the link below.

                xEXTERNAL_ACCESS_LINKx

                Kind regards,

                xCLIENT_NAMEx"
            ),
        );

        $rawMailBody = $templates['ENGLISH']['body'];
        $rawMailSubject = $templates['ENGLISH']['subject'];
        if(array_key_exists(strtoupper($debtor->LANGUAGE_CODE), $templates)) {
            $rawMailBody = $templates[strtoupper($debtor->LANGUAGE_CODE)]['body'];
            $rawMailSubject = $templates[strtoupper($debtor->LANGUAGE_CODE)]['subject'];
        }

        $mailBody = $this->ReplaceReplacementFieldsBasedOnDebtor($debtor, $file, $rawMailBody);
        $mailSubject = $this->ReplaceReplacementFieldsBasedOnDebtor($debtor, $file, $rawMailSubject);

        $mailObj = new Application_Model_Mail();
        //TODO: test line, remove on LIVE
        print "to : {$debtor->E_MAIL}, subject : {$mailSubject}, body : {$mailBody}, from : {$config->fromEmail}";
        //TODO: comment back in
        //$mailObj->sendMail($debtor->E_MAIL, $mailSubject, $mailBody, $mailBody, $config->fromEmail, false, false, true);
    }

    public function sendDisputeWarningMail($reference) {
        global $config;

        $templates = array(
            "FRENCH" => array(
                "subject" => "Commentaire du client pour facture xREFERENCEx",
                "body" => "Monsieur, Madame,

                    Le client xDEBTOR_NAMEx a ajouté un commentaire pour facture xREFERENCEx avec le contenu suivant:

                    ------
                    xMESSAGEx
                    ------

                    C'est possible qu'il faut mettre ce dossier en dispute.

                    Bien à vous,

                    Le logiciel CM"
            ),
            "DUTCH" => array(
                "subject" => "Opmerking van klant voor factuur xREFERENCEx",
                "body" => "Beste medewerker,

                    De klant xDEBTOR_NAMEx heeft een opmerking toegevoegd bij factuur xREFERENCEx met de volgende boodschap.

                    ------
                    xMESSAGEx
                    ------

                    Mogelijk moet dit dossier in betwisting worden geplaatst.

                    Vriendelijke groeten,

                    Het CM-softwarepakket"
            ),
            "ENGLISH" => array(
                "subject" => "Remark from client for invoice xREFERENCEx",
                "body" => "Dear sir, madam,

                    The client xDEBTOR_NAMEx has added a remark to invoice xREFERENCEx with the following message.

                    ------
                    xMESSAGEx
                    ------

                    You might need to mark this file as a disputed invoice.

                    Kind regards,

                    The CM-software system"
            ),
        );

        $collectorsObj = new Application_Model_Collectors();
        $collector = $collectorsObj->getCollectorByFileId($reference->FILE_ID);

        $rawMailBody = $templates['ENGLISH']['body'];
        $rawMailSubject = $templates['ENGLISH']['subject'];
        if(array_key_exists(strtoupper($collector->LANGUAGE_CODE), $templates)) {
            $rawMailBody = $templates[strtoupper($collector->LANGUAGE_CODE)]['body'];
            $rawMailSubject = $templates[strtoupper($collector->LANGUAGE_CODE)]['subject'];
        }

        $mailBody = $this->replaceReplacementFieldsBasedOnReference($reference, $rawMailBody);
        $mailSubject = $this->replaceReplacementFieldsBasedOnReference($reference, $rawMailSubject);

        $mailObj = new Application_Model_Mail();

        //TODO: 
        print "to : {$collector->E_MAIL}, subject : {$mailSubject}, body : {$mailBody}, from : {$config->fromEmail}";

        //TODO:
        //$mailObj->sendMail($collector->EMAIL, $mailSubject, $mailBody, $mailBody, $config->fromEmail, false, false, true);
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

    public function replaceReplacementFieldsBasedOnDebtor($debtor, $file, $rawText) {
        global $config;

        $externalAccessLink = $this->createExternalAccessLink($debtor);
        $replacementFields = array(
            'CLIENT_NAME' => $file->CLIENT_NAME,
            'EXTERNAL_ACCESS_LINK' => $externalAccessLink
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

    /**
     * @param $debtor
     * @return string
     */
    public function createExternalAccessLink($debtor) {
        global $config;
        $externalAccessCode = $this->createExternalAccessCode($debtor->DEBTOR_ID);
        $functions = new Application_Model_CommonFunctions();
        $languageCode = $functions->langToCode($debtor->LANGUAGE_CODE);
        $externalAccessLink = "{$config->rootLocation}/external-access/check-invoices/a/{$externalAccessCode}/l/{$languageCode}";
        return $externalAccessLink;
    }
}

?>
