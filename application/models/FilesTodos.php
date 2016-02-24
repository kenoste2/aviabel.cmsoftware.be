<?php

require_once 'application/models/Base.php';

class Application_Model_FilesTodos extends Application_Model_Base {

    public function delete($id) {
        $sql = "DELETE FROM TODOS WHERE TODO_ID = {$id}";
        $this->db->query($sql);
    }

    public function save($data, $where) {
        $this->saveData('TODOS', $data, $where);
    }

    public function add($data) {
        $data['CREATION_DATE'] = date("Y-m-d");
        $data['CREATION_USER'] = $this->online_user;
        $this->addData('TODOS', $data);
        return true;
    }

    public function getTodoTypes() {

        $todoTypes = $this->functions->getUserSetting("setting_file_todo_types");
        $todoTypes = explode("\n", $todoTypes);

        $todoArray = array("" => "-");
        if (!empty($todoTypes)) {
            foreach ($todoTypes as $todoType) {
                list($key, $description) = explode(",", $todoType);
                $todoArray[$key] = $description;
            }
        }

        return $todoArray;
    }

    public function getTodoById($id) {
        return $this->db->get_row("SELECT * FROM TODOS WHERE TODO_ID = {$id}");
    }

    public function getTodoUsers() {

        $todoUsers = $this->functions->getUserSetting("todo_users_list");
        $todoUsers = explode("\n", $todoUsers);

        $todoArray = array();
        if (!empty($todoUsers)) {
            foreach ($todoUsers as $todoUser) {
                list($key, $description) = explode(",", $todoUser);
                $todoArray[$key] = $description;
            }
        }

        return $todoArray;
    }    
    
}

?>
