<?php
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected function _initAutoLoad(){
        $autoloader = new Zend_Application_Module_Autoloader( array(
            'namespace' => '',
            'basePath' => APPLICATION_PATH,
        ));        
	
     
        return $autoloader;
    }
    
    protected function _initDb(){

        
         $params = array(
            'host'           => 'localhost',
            'port'           => '3306',
            'username'       => 'root',
            'password'       => '',
            'dbname'         => 'vbee_gateway',
            'charset'        => 'utf8'
        );
         
        $db = Zend_Db::factory('Pdo_Mysql', $params);
        $db->setFetchMode(Zend_Db::FETCH_ASSOC);
        
        Zend_Registry::set('connectDB', $db);
        // Khi thiet la che do nay model moi co the su dung duoc
        Zend_Db_Table::setDefaultAdapter($db);
        Zend_Db_Table_Abstract::setDefaultAdapter($db);
        return $db;
    }
    
    // Them file cau hinh cac hang so su dung trong project
    protected function _initSetConstants(){
        $config = parse_ini_file(APPLICATION_PATH . '/configs/constant.ini');
        foreach($config as $key=>$value){
            // neu chua dinh nghia thi moi dinh nghia lai
            if(!defined($key)){
                define($key, $value);
            }
        }
    }
    
    protected function _initFrontController(){
        $front = Zend_Controller_Front::getInstance();  
        // Dang ki plug in
        //$front->registerPlugin(new Amobi_Plugin_Auth());
        //$front->registerPlugin(new Amobi_Plugin_ACL());
             
        $front->addModuleDirectory(APPLICATION_PATH . "/modules");
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/routes.ini', 'thietlap');
        $router = new Zend_Controller_Router_Rewrite();
        $router->addConfig($config, 'routes');
        $front->setRouter($router);
        return $front;
    }
}