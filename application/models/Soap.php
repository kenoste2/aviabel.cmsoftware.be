<?php

class Application_Model_Soap extends SoapClient
{
    public function __doRequest($req, $location, $action, $version = SOAP_1_1){
        global $config;
        $xml = explode("\r\n", parent::__doRequest($req, $location, $action, $version));
        $response = preg_replace( '/^(\x00\x00\xFE\xFF|\xFF\xFE\x00\x00|\xFE\xFF|\xFF\xFE|\xEF\xBB\xBF)/', "", $xml[11] );
        $fileName = "export_report" .rand(0,9999).".pdf";
        header('Content-type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        print $xml[11];
        die();
    }
}