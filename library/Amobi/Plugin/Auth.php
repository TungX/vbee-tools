<?php
class Amobi_Plugin_Auth extends Zend_Controller_Plugin_Abstract {

	public function preDispatch(Zend_Controller_Request_Abstract $request) {

		$moduleName = $request->getModuleName();
		$controllerName = $request->getControllerName();
		
		$need_auth = TRUE;
		
		$free_access = array(
			"default" => array(),
			"user" => array("auth", "error"),
			"admin" => array("auth", "error")
		);
		
		if ( in_array($moduleName, array_keys($free_access) ) ) {
			if ((count($free_access[$moduleName]) == 0) || in_array($controllerName, $free_access[$moduleName])) {
				$need_auth = FALSE;
			}
		}
		
		if($need_auth){
            $auth = Zend_Auth::getInstance();            
            if ($moduleName == "user") {
                // Neu la vao phan user thi kiem tra session user
                if ($auth->hasIdentity()) {
                    // Neu da dang nhap thi cho vao
                    
                } else {
                    // Kiem tra request co phai la ajax ko?
                    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                        header("HTTP/1.1 403 Forbidden");
                        exit();
                    } else {
                        // Neu chua dang nhap thi chuyen den trang dang nhap
                        $request->setParam('uri_callback', $request->getRequestUri());
                        $request->setModuleName('user');
        				$request->setControllerName('auth');
        				$request->setActionName('login');    
                    }
                }
            } else if ($moduleName == "admin") {
                // Neu la vao phan admin thi kiem tra session admin
                $auth->setStorage(new Zend_Auth_Storage_Session('Zend_Auth_Admin'));
                if ($auth->hasIdentity()) {
                    // Neu da dang nhap thi cho vao
                    
                } else {
                     // Kiem tra request co phai la ajax ko?
                    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                        header("HTTP/1.1 403 Forbidden");
                        exit();
                    } else {
                        // Neu chua dang nhap thi chuyen den trang dang nhap
                        $request->setParam('uri_callback', $request->getRequestUri());
                        $request->setModuleName('admin');
        				$request->setControllerName('auth');
        				$request->setActionName('login');
                    }
                }
            }
		}
	}
}