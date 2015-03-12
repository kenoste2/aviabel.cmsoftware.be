<?php

class Application_Model_ImportedMails extends Application_Model_Base
{
    public function add($data)
    {
        $newId = $this->db->get_var("SELECT COALESCE(MAX(IMPORTED_MAIL_ID), 0) + 1 AS NEWID FROM IMPORTED_MAILS");
        $data['IMPORTED_MAIL_ID'] = $newId;

        $this->adddata("imported_mails", $data, false);
        return $newId;
    }

    public function addAttachment($data) {
        $data['IMPORTED_MAIL_ATTACHMENT_ID'] = $this->db->get_var("SELECT COALESCE(MAX(IMPORTED_MAIL_ATTACHMENT_ID), 0) + 1 AS NEWID FROM IMPORTED_MAIL_ATTACHMENTS");

        return $this->adddata("imported_mail_attachments", $data);
    }

    public function retrieveImportedMailById($importedMailId) {
        if(!$importedMailId) {
            $importedMailId = '0';
        }
        $escImportedMailId = $this->db->escape($importedMailId);
        return $this->db->get_row("SELECT * FROM IMPORTED_MAILS WHERE IMPORTED_MAIL_ID = {$escImportedMailId}");
    }

    public function retrieveAttachmentById($attachmentId) {
        if(!$attachmentId) {
            $attachmentId = '0';
        }
        $escAttachmentId = $this->db->escape($attachmentId);
        return $this->db->get_row("SELECT * FROM IMPORTED_MAIL_ATTACHMENTS WHERE IMPORTED_MAIL_ATTACHMENT_ID = {$escAttachmentId}");
    }

    public function retrieveAttachmentsById($attachmentIds) {
        if(count($attachmentIds) <= 0) {
            return array();
        }

        $escAttachmentIds = array();
        foreach($attachmentIds as $attachmentId) {
            $escAttachmentIds []= $this->db->escape($attachmentId);
        }
        $escAttachmentIdsStr = implode(',', $escAttachmentIds);
        return $this->db->get_results("SELECT * FROM IMPORTED_MAIL_ATTACHMENTS WHERE IMPORTED_MAIL_ATTACHMENT_ID IN ({$escAttachmentIdsStr})");
    }

    public function retrieveAttachmentsByMailId($mailId) {
        if(!$mailId) {
            $mailId = '0';
        }
        $escMailId = $this->db->escape($mailId);
        return $this->db->get_results("SELECT * FROM IMPORTED_MAIL_ATTACHMENTS WHERE IMPORTED_MAIL_ID = {$escMailId}");
    }

    public function retrieveImportedMailsByFileId($fileId) {
        if(!$fileId) {
            $fileId = '0';
        }
        $escFileId = $this->db->escape($fileId);
        return $this->db->get_results("SELECT * FROM IMPORTED_MAILS WHERE FILE_ID = {$escFileId} ORDER BY IMPORTED_MAIL_ID DESC ");
    }

    public function retrieveByDateRange($fromDate, $toDate)
    {
        if($fromDate && $toDate) {
            $escFromDate = $this->db->escape($fromDate);
            $escToDate = $this->db->escape($toDate);
            $sql = "SELECT i.FILE_ID,
                           i.FROM_EMAIL,
                           i.TO_EMAIL,
                           i.MAIL_BODY,
                           i.MAIL_SUBJECT,
                           i.CREATION_DATE,
                           (SELECT FIRST 1 FILE_NR FROM FILES\$FILES WHERE FILE_ID = i.FILE_ID) AS FILE_NR,
                           (SELECT FIRST 1 DEBTOR_NAME FROM FILES\$FILES_ALL_INFO WHERE FILE_ID = i.FILE_ID) AS CLIENT_NAME,
                           (SELECT FIRST 1 REFERENCE FROM FILES\$FILES_ALL_INFO WHERE FILE_ID = i.FILE_ID) AS REFERENCE
                    FROM IMPORTED_MAILS i WHERE i.CREATION_DATE >= '{$escFromDate} 00:00:00' AND i.CREATION_DATE <= '{$escToDate} 23:59:59' ORDER BY IMPORTED_MAIL_ID DESC";
            return $this->db->get_results($sql);
        }
        return array();
    }
}
