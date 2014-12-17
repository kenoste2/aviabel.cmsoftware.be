<?php

class Application_Form_Settings_Zipcodes extends Zend_Form {

    public function init() {
        global $db;

        $this->addPrefixPath('Application_Forms_Decorator', 'Application/Forms/Decorator', 'decorator');
        $functions = new Application_Model_CommonFunctions();

        $countryModel = new Application_Model_Countries();
        $populationModel = new Application_Model_Populations();
        $collectorModel = new Application_Model_Collectors();

        $this->addElement('text', 'ZIP_CODE', array('label' => $functions->T('Code_c'), 'required' => true, 'size' => 15, 'maxlength' => 20, 'size' => 50));
        $this->addElement('text', 'CITY_DUTCH', array('label' => $functions->T('City_dutch_c'), 'required' => true, 'maxlength' => 150, 'size' => 50));
        $this->addElement('text', 'CITY_FRENCH', array('label' => $functions->T('City_french_c'), 'required' => true , 'maxlength' => 150, 'size' => 50));
        $this->addElement('text', 'CITY_ENGLISH', array('label' => $functions->T('City_english_c'), 'required' => true , 'maxlength' => 150, 'size' => 50));
        $this->addElement('select', 'COUNTRY_ID', array('label' => $functions->T('Country_c'), 'required' => false, 'MultiOptions' => $functions->db2array($countryModel->getCountriesForSelect())));
        $this->addElement('text', 'NEW_COUNTRY_CODE', array('label' => $functions->T('New_codecountry_c'), 'required' => false, 'size' => 15));
        $this->addElement('text', 'NEW_COUNTRY_DESCRIPTION', array('label' => $functions->T('New_codecountry_c'), 'required' => false, 'size' => 30));
        $this->addElement('select', 'POPULATION_PLACE_ID', array('label' => $functions->T('Population_c'), 'required' => false, 'MultiOptions' => $functions->db2array($populationModel->getPopulationsForSelect())));
        $this->addElement('select', 'COLLECTOR_ID', array('label' => $functions->T('Collector_c'), 'required' => false, 'MultiOptions' => $functions->db2array($collectorModel->getCollectorsForSelect())));

        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('save_c'),
        ));
    }
}

