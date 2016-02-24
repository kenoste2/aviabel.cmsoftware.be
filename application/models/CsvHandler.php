<?php

class Application_Model_CsvHandler extends Application_Model_Base {

    public function readCsvToArray($fileName) {

        $handle = fopen($fileName, "r");

        $columnLine = null;
        $otherLines = array();

        while (($data = fgetcsv($handle, null, ';')) !== false) {
            if(!$columnLine) {
                $columnLine = $data;
            } else {
                $otherLines []= $data;
            }
        }


        $csvData = array();
        for($i = 0; $i < count($otherLines); $i++) {

            $item = array();
            for($j = 0; $j < count($columnLine); $j++) {
                $item[$columnLine[$j]] = $otherLines[$i][$j];
                $item[$j] = $otherLines[$i][$j];
            }

            $csvData []= $item;

        }

        fclose($handle);
        return $csvData;
    }

    public function createCsvFromSql($sql, $fileName) {
        $content = "";

        $patterns = array('/([0-9]+)\.([0-9]+)/');
        $replace = array('\1,\2', '\1.\2');

        $results = $this->db->get_results($sql, ARRAY_N);
        if (!empty($results)) {
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
            $fp = fopen($fileName,"w");
            fwrite($fp,$content);
            fclose($fp);
            return true;
        }
        return false;
    }
}