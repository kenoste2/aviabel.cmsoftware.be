<?php

require_once 'application/models/Base.php';

class Application_Model_Tasks extends Application_Model_Base
{
    protected $_sql = '';

    /**
     * @return string
     */
    public function getSql()
    {
        return $this->_sql;
    }

    public function getTasks(array $data)
    {
        $addQuery = $this->getAddQuery($data);

        $this->_sql="select A.FILE_ID,A.ASSIGNED_TO,A.DONE_BY,A.DONE_DATE,A.DONE,A.TODO_TYPE,A.TODO_ID,A.REMARK,A.CREATION_DATE,A.CREATION_USER,B.FILE_NR,B.DEBTOR_NAME
            from TODOS A, FILES\$FILES_ALL_INFO B
			WHERE A.FILE_ID=B.FILE_ID
				AND A.CREATION_DATE>='".$this->dateDbFormat($data['STARTDATE'])."'
				AND A.CREATION_DATE<='".$this->dateDbFormat($data['ENDDATE'])."'
				$addQuery
            order by A.TODO_ID DESC";
        return $this->db->get_results($this->_sql);
    }

    public function getTask($id)
    {
        return $this->db->get_row("select * from TODOS where TODO_ID='$id'");
    }

    public function add(array $data)
    {
        if (array_key_exists('FILE_NR', $data) && !empty($data['FILE_NR'])) {
            $fileModel = new Application_Model_Files();
            $fileId = $fileModel->getFileIdByNumber($data['FILE_NR']);
            $data['FILE_ID'] = $fileId;
            unset($data['FILE_NR']);
        }

        $data['CREATION_USER'] = $this->online_user;

        unset($data['DEBTOR_NAME']);

        return $this->addData("TODOS", $data, 'TODO_ID');
    }

    public function save(array $data, $id)
    {
        $row = $this->getTask($id);

        if ($row->DONE > $data['DONE']) {
            $data['DONE_DATE'] = NULL;
            $data['DONE_BY'] = '';
        }
        if ($data['DONE'] > $row->DONE) {
            $data['DONE_DATE'] = date('Y-m-d');
            $data['DONE_BY'] = $this->online_user;
        }

        return $this->saveData("TODOS", $data, 'TODO_ID = ' . $id);
    }

    public function delete($id)
    {
        return $this->db->query("DELETE FROM TODOS WHERE TODO_ID = $id");
    }

    protected function getAddQuery(array $data)
    {
        $addQuery = '';
        if ($data['CLIENT_ID']) {
            $addQuery .= " AND B.CLIENT_ID = '{$data['CLIENT_ID']}'";
        }
        if ($data['TODOS_TYPE']!='-1') {
            $addQuery .= " AND A.TODO_TYPE = '{$data['TODOS_TYPE']}'";
        }
        if ($data['COMPLETED']!='-1') {
            $addQuery .= " AND A.DONE =  '{$data['COMPLETED']}'";
        }
        if ($data['SELECT_ASSIGNED_TO']!='-1') {
            $addQuery .= " AND A.ASSIGNED_TO like '%{$this->db->escape(strtoupper($data['SELECT_ASSIGNED_TO']))}%'";
        }

        return $addQuery;
    }

    public function checkIsDeletable($id)
    {
        $authNamespace = new Zend_Session_Namespace('Zend_Auth');


        if ($authNamespace->online_rights == 5)
        {
            $sql="select  TODO_TYPE,REMARK,DONE,FILE_ID from TODOS where TODO_ID='$id'";
            $row=$this->db->get_row($sql);

            $client_id = $authNamespace->online_client_id;

            if (!$this->db->get_var("SELECT COUNT(*) FROM FILES\$FILES WHERE FILE_ID = $row->FILE_ID AND CLIENT_ID = '$client_id'")) {
                return false;
            }
        }

        return true;
    }
}

?>
