<?php
class Amobi_Plugin_ACL extends Zend_Controller_Plugin_Abstract {

    function curPageURL() {
        $pageURL = 'http';
        if ($_SERVER["HTTPS"] == "on") {
            $pageURL .= "s";
        }
        $pageURL .= "://";
        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        }
        return $pageURL;
    }

    public function preDispatch(Zend_Controller_Request_Abstract $request) {
        $moduleName = $this->_request->getModuleName();
        $controllerName = $this->_request->getControllerName();
        $actionName = $this->_request->getActionName();
        try {
            // Them title cho trang
            
            if (strcasecmp($moduleName, 'user') == 0) {
                $auth = Zend_Auth::getInstance();
                if ($auth->hasIdentity()) {
                    $identity = $auth->getIdentity();
                    $layout = Zend_Layout::getMvcInstance();
                    $view = $layout->getView();
                    $menuModel = new Model_User_UserMenuModel();

                    $menu_2_arr = $menuModel->getMenuList(2, 1);
                    $menu_2 = array();
                    foreach ($menu_2_arr as $v) {
                        $menu_2[] = $v["id"];
                    }
                    $view->menu_2 = $menu_2;

                    $url = "/" . $moduleName . "/" . $controllerName . "/" . $actionName;
                    $view->cUrl = $url;
                    $menus = $menuModel->getList(1, 1, 1);
                    $view->menus = $menus;
                    $admin_menu = array();
                    if (count($menus) > 0) {
                        foreach ($menus as $menu) {
                            if ($menu["level"] == 2) {
                                $admin_menu[$menu["parent_id"]][] = $menu["id"];
                            } else if ($menu["level"] == 1) {
                                $admin_menu[$menu["id"]][] = array();
                            }
                        }
                    }
                    $view->admin_menu = $admin_menu;
                }
            } if (strcasecmp($moduleName, 'admin') == 0) {
                $auth = Zend_Auth::getInstance();
                $auth->setStorage(new Zend_Auth_Storage_Session('Zend_Auth_Admin'));
                if ($auth->hasIdentity()) {
                    $identity = $auth->getIdentity();
                    $layout = Zend_Layout::getMvcInstance();
                    $view = $layout->getView();
                    $menuModel = new Model_Admin_AdminMenuModel();

                    $menu_2_arr = $menuModel->getMenuList(2, 1);
                    $menu_2 = array();
                    foreach ($menu_2_arr as $v) {
                        $menu_2[] = $v["id"];
                    }
                    $view->menu_2 = $menu_2;

                    $url = "/" . $moduleName . "/" . $controllerName . "/" . $actionName;
                    $view->cUrl = $url;
                    $menus = $menuModel->getList(1, 1, 1);
                    $view->menus = $menus;
                    $boss_menu = array();
                    if (count($menus) > 0) {
                        foreach ($menus as $menu) {
                            if ($menu["level"] == 2) {
                                $boss_menu[$menu["parent_id"]][] = $menu["id"];
                            } else if ($menu["level"] == 1) {
                                $boss_menu[$menu["id"]][] = array();
                            }
                        }
                    }
                    $view->boss_menu = $boss_menu;
                }
            }
        } catch (exception $e) {
            echo $e->getMessage();die;
        }
    }
    

}