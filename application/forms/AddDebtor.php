<?php

class Application_Form_AddDebtor extends Zend_Form {

    private $_superDebtorId;

    public function __construct($superDebtorId) {
        $this->_superDebtorId = $superDebtorId;
        parent::__construct();
    }

    public function init() {

        global $db;
        $this->setMethod('post');
        $functions = new Application_Model_CommonFunctions();
        $train = new Application_Model_Train();


        $this->addElement('text', 'NAME', array('label'=> $functions->T('name_c'),'size' => 50, 'required' => true));
        $this->addElement('text', 'VATNR', array('label'=> $functions->T('vatnr_c'),'size' => 50, 'onchange' => 'getCreditInfo();'));

        $debtorNameField = new Zend_Form_Element_Text('SUPER_DEBTOR_NAME', array('label'=> $functions->T('super_debtor_c'),'size' => 50, 'required' => true));
        if($this->_superDebtorId) {
            $this->addDebtorLinkDecorator($debtorNameField);
        }
        $this->addElement($debtorNameField);
        $this->addElement('hidden', 'SUPER_DEBTOR_ID');

        $this->addElement('text', 'ADDRESS', array('label'=> $functions->T('address_c'),'size' => 50, 'required' => true));
        $countries = $db->get_results("select COUNTRY_ID,DESCRIPTION from SUPPORT\$COUNTRIES  order by DESCRIPTION", ARRAY_N);
        array_unshift($countries,array('0' =>4,1 => 'BELGIUM'));
        $this->addElement('select', 'COUNTRY_ID', array('label'=> $functions->T('country_c'),'MultiOptions' => $functions->db2array($countries,false)));
        $this->addElement('text', 'ZIP_CODE', array('label'=> $functions->T('zipcode_c'),'size' => 8, 'required' => true));
        $this->addElement('text', 'CITY', array('label'=> $functions->T('city_c'),'size' => 36, 'required' => true));
        $this->addElement('text', 'BIRTH_DAY', array('label'=> $functions->T('birthday_c'),'size' => 10,'validators'=>array (array('date', false, array('dd/MM/yyyy'))),));
        $this->addElement('text', 'E_MAIL', array('label'=> $functions->T('email_c'),'size' => 50,'validators'=>array (array('EmailAddress')),));
        $this->addElement('text', 'TELEPHONE', array('label'=> $functions->T('tel_c'),'size' => 50, 'required' => false));
        $this->addElement('text', 'TELEFAX', array('label'=> $functions->T('fax_c'),'size' => 50));
        $this->addElement('text', 'GSM', array('label'=> $functions->T('gsm_c'),'size' => 50));
        $languages = $db->get_results("select LANGUAGE_ID,DESCRIPTION from SUPPORT\$LANGUAGES  order by DESCRIPTION", ARRAY_N);
        $this->addElement('select', 'LANGUAGE_ID', array('label'=> $functions->T('language_c'),'MultiOptions' => $functions->db2array($languages,false)));
        $this->addElement('textarea', 'EXTRA_FIELD', array('label'=> $functions->T('remarks_c'),'rows' => 5, 'cols' => 45));
        $this->addElement('select', 'TRAIN_TYPE', array('label' => $functions->T('train_type_c'), 'MultiOptions' => $functions->db2array($train->getTrainTypes(), false)));
        $this->addElement('hidden', 'DEBTOR_ID');

        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => $functions->T('save_c'),
        ));
    }

    public function addDebtorLinkDecorator($clientNameField)
    {
        global $config;

        //NOTE: this is a lot of code to generate the following right behind the field:
        //      <a class=\"ui-icon ui-icon-zoomin inline-icon\" style=\"display: inline-block;\" href=\"{$this->_location}/client-detail/view/clientId/{$this->file->CLIENT_ID}\"></a>";
        //NOTE: check out this article for more on decorators: http://devzone.zend.com/1240/decorators-with-zend_form/
        $decorators = $clientNameField->getDecorators();

        //NOTE: just inserting our custom-decorator in the array won't get it at the right position (for weird PHP-internal reasons) so we need to create a new array of decorators.
        $newDecorators = array();
        $i = 0;
        foreach ($decorators as $decorator) {
            $newDecorators [] = $decorator;
            if ($i == 1) {
                //NOTE: insert the custom decorator at the 2nd position in the array.
                $newDecorators [] = array(array("link" => "HtmlTag"),
                    array('tag' => 'a',
                        'placement' => 'append',
                        'class' => 'ui-icon ui-icon-zoomin inline-icon',
                        'style' => 'display: inline-block;',
                        'href' => $config->rootLocation . "/debtor-detail/view/debtorId/" . $this->_superDebtorId
                    )
                );
            }
            $i++;
        }
        $clientNameField->setDecorators($newDecorators);
    }
}

