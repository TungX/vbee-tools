<?php
// Define path to application directory
defined('APPLICATION_PATH')
        || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application/'));
// Define application enviroment
defined('APPLICATION_ENV')
        || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));
// Define path to public folder
defined('PUBLIC_PATH')
        || define('PUBLIC_PATH', realpath(dirname(__FILE__)));
// Duong dan den thu muc /templates
define('TEMPLATE_PATH', PUBLIC_PATH."/templates");
// Duong dan den thu muc templates
define('TEMPLATE_URL', "/templates");
//Duong dan den thu muc /library
defined('LIBRARY_PATH')
    || define('LIBRARY_PATH', realpath(dirname(__FILE__) . '/../library'));

// Ensure library/ is on include_path, them cac duong dan den cac file model de Zend co the load
set_include_path(implode(PATH_SEPARATOR, array(
    realpath('../library'),
    realpath(APPLICATION_PATH . '/models'),
    get_include_path(),
)));


/** Zend Application */
require_once('Zend/Application.php');

// Create application, bootstrap and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);
$application->bootstrap()
            ->run();