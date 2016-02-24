<?php

require_once 'application/controllers/BaseController.php';

class ExportController extends BaseController {

    public function csvAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $rand = rand(0, 999);

        $fileName = "export_{$rand}.csv";

        header('Content-type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');

        $patterns = array('/([0-9]+)\.([0-9]+)/');
        $replace = array('\1,\2', '\1.\2');


        $sql = $this->export->sql;
        $results = $this->db->get_results($sql, ARRAY_N);
        if (!empty($results)) {
            $content = "";

            $cols = $this->db->get_col_info();

            $content.=implode(";", $cols) . "\n";

            foreach ($results as $file) {
                $file = implode("££", $file);
                $file = str_replace(";", ",", $file);
                $file = str_replace("\n", "", $file);
                $file = str_replace("\r", "", $file);
                $file = str_replace("££", ";", $file);
                $file = preg_replace($patterns, $replace, $file);
                $content.=$file . "\n";
            }
        }
        print $content;
    }

}

