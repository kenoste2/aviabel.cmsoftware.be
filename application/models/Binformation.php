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

        $data = array();

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
        $this->log($vat,"C-ONLINE", $xml);
        return $data;
    }

    public function getPdfReport($vat)
    {

        global $lang;

        $wsdlFile = APPLICATION_PATH . '/../library/bravo_iis.wsdl';

        $client = new Zend_Soap_Client($wsdlFile, array("login" => "conlinep", "password" => "cOnl1nepr", 'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | 5));

        $vat = str_replace(" ", "", $vat);
        $vat = str_replace(".", "", $vat);
        $vat = str_replace("BE", "", $vat);
        $vat = str_replace("NL", "", $vat);




        $data = "";
        //print "<pre>";
        //print_r($client->GetDemoCompanies(array('CountryCode' => 'BE')));
        //die();
        $result = $client->Search(array('NationalNumber' => $vat, 'CountryCode' => 'BE'));
        if (!empty($result)) {
            $clientAttachments =  new Application_Model_Soap($wsdlFile, array("login" => "conlinep", "password" => "cOnl1nepr", array("trace" => 0, "exception" => 0)));
            $this->updateCounter($vat);
            $this->log($vat,"CREDIT_PDF");
            $data = $clientAttachments->OrderReport(array('ReportName' => 'CREDIT', 'ReportLanguage' => $lang,'ReportType' => 'application/pdf', 'CompanyId' => $result->Companies->Company->CompanyId));
        }
        return $data;
    }

    public function OrderReport($id)
    {

        global $lang;
        $wsdlFile = APPLICATION_PATH . '/../library/bravo.wsdl';
        $client = new Zend_Soap_Client ($wsdlFile, array("login" => "conlinep", "password" => "cOnl1nepr"));

        try {
            $result = $client->OrderReport(array('ReportName' => 'C-ONLINE', 'ReportLanguage' => $lang, 'CompanyId' => $id));
        } catch (Exception $e) {
            return false;
        }
        $xml = $result->ReportContent;
        return $xml;
    }

    public function updateCounter($vat)
    {
        $sql = "SELECT COUNT(*) FROM BINFO WHERE VAT = '{$vat}' AND CREATION_DATE >=CURRENT_DATE-93 AND REPORT_TYPE='CREDIT_PDF'";
        $last3month = $this->db->get_var($sql);
        if (empty($last3month)) {
            $sql = "SELECT COUNTER FROM BINFO_COUNTER";
            $counter = $this->db->get_var($sql);
            $counter--;
            $sql = "UPDATE BINFO_COUNTER SET COUNTER = $counter";
            $this->db->query($sql);
        }
    }

    public function getCounter()
    {
        $sql = "SELECT COUNTER FROM BINFO_COUNTER";
        $counter = $this->db->get_var($sql);
        return $counter;
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

    /**
     * @param $vat
     * @param $xml
     * @return array
     */
    private function log($vat, $reportType, $xml = '')
    {
        $data = array(
            'VAT' => $vat,
            'XML_CONTENT' => $xml,
            'REPORT_TYPE' => $reportType,
            'CREATION_DATE' => date("Y-m-d"),
            'CREATION_USER' => $this->online_user
        );
        $this->addData("BINFO", $data);
        return true;
    }

}

?>
