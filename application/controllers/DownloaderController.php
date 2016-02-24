<?php

require_once 'application/controllers/BaseController.php';

class DownloaderController extends BaseController
{

    public function documentAction()
    {

        global $config;

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();


        $verify = $this->getParam('verify');
        $id = $this->getParam('id');


        if ($verify !== sha1("{$id}Triple@")) {
            $this->redirect('/Auth/Login');
        };

        $docObj =  new Application_Model_FilesDocuments();

        $doc =  $docObj->getById($id);


        header('Content-type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $doc->FILENAME . '"');

        $downloadRoot = $config->rootFileDocuments . '/' . $doc->FILENAME;
        $content = file_get_contents($downloadRoot);
        print $content;

    }

}

