<?php

require_once 'application/models/Base.php';

class Application_Model_Journal extends Application_Model_Base
{
    protected $_transactionId = 0;

    /**
     * @return int
     */
    public function getTransactionId()
    {
        return $this->_transactionId;
    }

    public function add($data)
    {
        if (!array_key_exists('TRANSACTION_ID', $data)) {
            $transactionModel = new Application_Model_Transactions();
            $transactionData = $this->getTransactionData();
            $this->_transactionId = $transactionModel->add($transactionData);

            $data['TRANSACTION_ID'] = $this->_transactionId;
        }

        if (array_key_exists('FILE_NR', $data) && !empty($data['FILE_NR'])) {
            $fileModel = new Application_Model_Files();
            $fileId = $fileModel->getFileIdByNumber($data['FILE_NR']);
            $data['FILE_ID'] = $fileId;
            unset($data['FILE_NR']);
        }

        if (array_key_exists('VALUTA_DATE', $data) && !empty($data['VALUTA_DATE'])){
            $data['VALUTA_DATE'] = $this->dateDbFormat($data['VALUTA_DATE']);
        }

        unset($data['DEBTOR_NAME']);

        return $this->addData("ACCOUNTS\$JOURNAL", $data, 'JOURNAL_ID');
    }

    protected function getTransactionData()
    {
        return array(
            'CREATION_DATE' => date('Y-m-d'),
            'CREATION_USER' => $this->online_user,
            'DESCRIPTION' => rand(0, 10000)
        );
    }
}

?>
