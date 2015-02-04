<?php

require_once 'application/models/Base.php';

class Application_Model_FileSystem extends Application_Model_Base
{
    public function createFileFromContent($path, $content) {
        $handle = fopen($path, 'wb');
        $result = fwrite($handle, $content);
        if(!$result) {
            print '<br>saving failed';
        }
        fclose($handle);
    }
}