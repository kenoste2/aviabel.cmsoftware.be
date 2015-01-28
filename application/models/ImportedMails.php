<?php

class Application_Model_ImportedMails extends Application_Model_Base
{
    public function add($data)
    {
        $data['IMPORTED_MAIL_ID'] = $this->db->get_var("SELECT COALESCE(MAX(IMPORTED_MAIL_ID), 0) + 1 AS NEWID FROM IMPORTED_MAILS");
        return $this->adddata("imported_mails", $data);
    }

    public function retrieveImportedMailsByFileId($fileId) {
        if(!$fileId) {
            $fileId = '0';
        }
        $escFileId = $this->db->escape($fileId);
        return $this->db->get_results("SELECT * FROM IMPORTED_MAILS WHERE FILE_ID = {$escFileId}");
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
                           (SELECT FIRST 1 FILE_NR FROM FILES\$FILES WHERE FILE_ID = i.FILE_ID) AS FILE_NR
                    FROM IMPORTED_MAILS i WHERE i.CREATION_DATE >= '{$escFromDate}' AND i.CREATION_DATE <= '{$escToDate}'";
            return $this->db->get_results($sql);
        }
        return array();
    }
}