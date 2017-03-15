<?php
class Amobi_System_Auth{

	private $_messages;
    
    public function login($arrParam =  null, $auth_type = USER_CHECK){
        if ($auth_type == USER_CHECK) {
            $db = Zend_Registry::get('connectDB2');
            $auth = Zend_Auth::getInstance();	
    	   	$authAdapter = new Zend_Auth_Adapter_DbTable($db);
    	    $authAdapter->setTableName('user')
    	    			->setIdentityColumn('user_name')
    	    			->setCredentialColumn('password');
    	    $uname = trim($arrParam['userName']);
    	    $password = trim($arrParam['password']);
    		if ($uname == "" || $password == "") {
    			return false;
    		} else {
    		    $paswd = sha1($password,false);
        		$authAdapter->setIdentity($uname);
        		$authAdapter->setCredential($paswd);
        		$select = $authAdapter->getDbSelect();
        		$select->where('status ='.STATUS_PUBLIC);	
    			$result = $auth->authenticate($authAdapter);            
        		if ($result->isValid()) {
                    $omitColumns = array('password');
        			$data = $authAdapter->getResultRowObject(null,$omitColumns);
        			$auth->getStorage()->write($data);
    	            $session = new Zend_Session_Namespace('Zend_Auth');
            //      Set the time of user logged in
    	            $session->setExpirationSeconds(24*3600);        
            //      If "remember" was marked
    	            if (!empty($arrParam['remember'])) {
    	                Zend_Session::rememberMe();
    	            }
                    // Tao lai session de chong session fixation
                    session_regenerate_id();    
                    return true;
        		} else {
        			return false;
        		}
        	}
        } else {
            $db = Zend_Registry::get('connectDB');
    	   	$auth = Zend_Auth::getInstance();	
            $auth->setStorage(new Zend_Auth_Storage_Session('Zend_Auth_Admin'));
    	   	$authAdapter = new Zend_Auth_Adapter_DbTable($db);
    	    $authAdapter->setTableName('admin')
    	    			->setIdentityColumn('userName')
    	    			->setCredentialColumn('password');
    	    $uname = trim($arrParam['userName']);
    	    $password = trim($arrParam['password']);
    		if ($uname == "" || $password == "") {
    			return false;
    		} else {
    		    $paswd = sha1($password,false);
        		$authAdapter->setIdentity($uname);
        		$authAdapter->setCredential($paswd);
        		$select = $authAdapter->getDbSelect();
        		$select->where('status ='.STATUS_PUBLIC);	
    			$result = $auth->authenticate($authAdapter);         
        		if ($result->isValid()) {
        			$omitColumns = array('password');
        			$data = $authAdapter->getResultRowObject(null,$omitColumns);
    	            $session = new Zend_Session_Namespace('Zend_Auth_Admin');
    //      Set the time of user logged in
    	            $session->setExpirationSeconds(24*3600);        
    //      If "remember" was marked
    	            if (!empty($arrParam['remember'])) {
    	                Zend_Session::rememberMe();
    	            }
                    // Tao lai session de chong session fixation
                    session_regenerate_id();
                    
        			return true;
        		} else {
        			return false;
        		}
        	}
        }
        
    }
    
    public function logout($auth_type = USER_CHECK){
        if ($auth_type == USER_CHECK) {
            $auth = Zend_Auth::getInstance();
            $auth->clearIdentity();    
        } else {
            $auth = Zend_Auth::getInstance();
            $auth->setStorage(new Zend_Auth_Storage_Session('Zend_Auth_Admin'));
            $auth->clearIdentity();    
        }
    }
}
