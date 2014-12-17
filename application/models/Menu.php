<?php

require_once 'application/models/Base.php';

class Application_Model_Menu extends Application_Model_Base {

    public function getIniMenu($rights, $commingfrom = 0) {
        global $lang;

        $menu = new Zend_config_Ini(APPLICATION_PATH . '/configs/menu.ini', APPLICATION_ENV);
        $resultMenu = array();

        $mainMenus = $menu->mainMenus;
        $mainMenusArray = explode(",", $mainMenus);

        if (!empty($mainMenusArray)) {
            foreach ($mainMenusArray as $mainMenu) {
                $menuString = 'menu' . $mainMenu;
                $resultMenu[$mainMenu]['TITEL'] = $this->functions->T("menu_".lcfirst($mainMenu));
                $thisMenu = $menu->$menuString;
                foreach ($thisMenu as $key => $value) {
                    if (stripos($value, "{$rights}") !== false) {
                        $langString = str_replace("/", "_", $key);
                        $resultMenu[$mainMenu]['SUBMENU'][] = array(
                            'TITEL' => $this->functions->T("menu_{$langString}"),
                            'NAV' => $key,
                        );
                    }
                }
            }
        }

        return $resultMenu;
    }

}

?>
