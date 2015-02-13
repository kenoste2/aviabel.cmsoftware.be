<?php

require_once 'application/models/Base.php';

class Application_Model_Binformation extends Application_Model_Base
{
    public function getDataByVat($vat)
    {

        global $lang;

        $wsdlFile = APPLICATION_PATH . '/../library/bravo_iis.wsdl';

        $client = new Zend_Soap_Client($wsdlFile, array("login" => "conlinep", "password" => "cOnl1nepr"));

        $vat = str_replace(" ", "", $vat);
        $vat = str_replace(".", "", $vat);
        $vat = str_replace("BE", "", $vat);
        $vat = str_replace("NL", "", $vat);

        //print "<pre>";
        //print_r($client->GetDemoCompanies(array('CountryCode' => 'BE')));
        //die();
        try {
            $result = $client->Search(array('NationalNumber' => $vat, 'CountryCode' => 'BE'));
            if (!empty($result)) {
                    $report = $client->OrderReport(array('ReportName' => 'C-ONLINE', 'ReportLanguage' => $lang, 'CompanyId' => $result->Companies->Company->CompanyId));
                $xml = $report->ReportContent;
                $data = array(
                    'COMPANYID' => $result->Companies->Company->CompanyId,
                    'NAME' => $result->Companies->Company->Name,
                    'STREET' => $result->Companies->Company->Address->Street,
                    'ZIPCODE' => $result->Companies->Company->Address->PostalCode,
                    'CITY' => $result->Companies->Company->Address->Locality,
                    'ACTIVE' => $result->Companies->Company->Active,
                    'XML' => $xml,
                );
            }
        } catch (Exception $e) {
            return false;
        }
            return $data;
    }

    public function OrderReport($id)
    {

        global $lang;
        $wsdlFile = APPLICATION_PATH . '/../library/bravo.wsdl';
        $client = new Zend_Soap_Client($wsdlFile, array("login" => "conlinep", "password" => "cOnl1nepr"));

        try {
            $result = $client->OrderReport(array('ReportName' => 'C-ONLINE', 'ReportLanguage' => $lang, 'CompanyId' => $id));
        } catch (Exception $e) {
            return false;
        }
        $xml = $result->ReportContent;
        return $xml;
    }


    public function Search($name, $vat, $country)
    {
        global $lang;
        $wsdlFile = APPLICATION_PATH . '/../library/bravo.wsdl';
        $client = new Zend_Soap_Client($wsdlFile, array("login" => "conlinep", "password" => "cOnl1nepr"));
        $params = array('Name' => $name, 'CountryCode' => $country, 'NationalNumber' => $vat);
        $result = $client->Search($params);

        $return = array();
        if (!empty($result)) {
            try {

                if (is_array($result->Companies->Company)) {

                    foreach ($result->Companies->Company as $row) {

                        if ($row->Address->Street != null) {
                            $street = $row->Address->Street;
                        } else {
                            $street = "";
                        }
                        if ($row->Address->PostalCode != null) {
                            $PostalCode = $row->Address->PostalCode;
                        } else {
                            $PostalCode = "";
                        }
                        if ($row->Address->Locality != null) {
                            $Locality = $row->Address->Locality;
                        } else {
                            $Locality = "";
                        }

                        $return[] = array(
                            'CompanyId' => $row->CompanyId,
                            'Name' => $row->Name,
                            'NationalNumber' => $row->NationalNumber,
                            'Street' => $street,
                            'PostalCode' => $PostalCode,
                            'Locality' => $Locality,
                        );
                    }
                } else {

                    if ($result->Companies->Company->Address->Street != null) {
                        $street = $result->Companies->Company->Address->Street;
                    } else {
                        $street = "";
                    }
                    if ($result->Companies->Company->Address->PostalCode != null) {
                        $PostalCode = $result->Companies->Company->Address->PostalCode;
                    } else {
                        $PostalCode = "";
                    }
                    if ($result->Companies->Company->Address->Locality != null) {
                        $Locality = $result->Companies->Company->Address->Locality;
                    } else {
                        $Locality = "";
                    }

                    $return[] = array(
                        'CompanyId' => $result->Companies->Company->CompanyId,
                        'Name' => $result->Companies->Company->Name,
                        'NationalNumber' => $result->Companies->Company->NationalNumber,
                        'Street' => $street,
                        'PostalCode' => $PostalCode,
                        'Locality' => $Locality,
                    );

                }

            } catch (Exception $e) {
                return false;
            }
        }
        return $return;
    }

}

?>
