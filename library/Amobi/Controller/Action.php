<?php
require_once 'Zend/Session/Namespace.php';
$defaultNamespace = new Zend_Session_Namespace('Default');

class Amobi_Controller_Action extends Zend_Controller_Action {

    protected $_arrParam;
    protected $_model;
    protected $_user;
    protected $_authenticate = true;
    protected $_action;
    protected $_action_non_auth = array();

    public function init() {
        $GLOBALS['name'] = 'Vbee Gateway';
        $GLOBALS['email'] = '';

        $this->_arrParam = $this->_request->getParams();
        $this->_action = $this->_arrParam['action'];
        $GLOBALS['controller'] = $this->_arrParam['controller'];
        unset($this->_arrParam['module']);
        unset($this->_arrParam['controller']);
        unset($this->_arrParam['action']);

        $template_path = TEMPLATE_PATH . '/default/';
        $this->loadTemplate($template_path, 'template.ini', 'template');

        $doctypeHelper = new Zend_View_Helper_Doctype();
        $doctypeHelper->doctype('XHTML1_RDFA');
    }

    public function predisPatch() {
        $this->auth();
    }

    private function auth() {
        if (in_array($this->_action, $this->_action_non_auth)) {
            return;
        }
        Zend_Loader::loadClass('Model_User');
        $modelUser = new Model_User();
        try {
            $this->_user = $modelUser->find($_SESSION['id'])[0];
            $this->checkSession();
            $GLOBALS['name'] = $this->_user['name'];
            $GLOBALS['email'] = $this->_user['email'];
        } catch (Exception $exc) {
            $this->_helper->redirector('login', 'user', 'default', array());
        }
    }
    
    private function checkSession(){
        if($this->_user['session'] != $_SESSION['session']){
            $this->_helper->redirector('login', 'user', 'default', array());
        }
    }

    protected function loadTemplate($template_path, $fileConfig = 'template.ini', $sectionConfig = 'template') {
        $this->cleanLayout();

        $filename = $template_path . "/" . $fileConfig;
        $section = $sectionConfig;
        $config = (new Zend_Config_Ini($filename, $section))->toArray();

        $baseUrl = $this->_request->getBaseUrl();
        $templateUrl = $baseUrl . $config['url'];
        $cssUrl = $templateUrl . $config['dirCss'];
        $jsUrl = $templateUrl . $config['dirJs'];
        $imgUrl = $templateUrl . $config['dirImg'];

        $this->setTitle($config);
        $this->setMeta($config);
        $this->setCss($config, $cssUrl);
        $this->setJs($config, $jsUrl);

        $this->view->templateUrl = $templateUrl;
        $this->view->cssUrl = $cssUrl;
        $this->view->jsUrl = $jsUrl;
        $this->view->imgUrl = $imgUrl;

        $option = array('layoutPath' => $template_path, 'layout' => $config['layout']);
        Zend_Layout::startMvc($option);
    }

    private function setJs($config, $jsUrl) {
        if (array_key_exists('fileJs', $config) && count($config['fileJs']) > 0) {
            foreach ($config['fileJs'] as $js) {
                $this->view->headScript()->appendFile($jsUrl . $js, 'text/javascript');
            }
        }
    }

    private function setCss($config, $cssUrl) {
        if (array_key_exists('fileCss', $config) && count($config['fileCss']) > 0) {
            foreach ($config['fileCss'] as $css) {
                $this->view->headLink()->appendStylesheet($cssUrl . $css, 'screen');
            }
        }
    }

    private function setMeta($config) {
        if (array_key_exists('metaHttp', $config) && count($config['metaHttp']) > 0) {
            foreach ($config['metaHttp'] as $key => $value) {
                $tmp = explode("|", $value);
                $this->view->headMeta()->appendHttpEquiv($tmp[0], $tmp[1]);
            }
        }

        if (array_key_exists('metaName', $config) && count($config['metaName']) > 0) {
            foreach ($config['metaName'] as $key => $value) {
                $tmp = explode("|", $value);
                $this->view->headMeta()->appendName($tmp[0], $tmp[1]);
            }
        }
    }

    private function setTitle($config) {
        $this->view->headTitle($config['title']);
    }

    private function cleanLayout() {
        $this->view->headTitle()->set('');
        $this->view->headMeta()->getContainer()->exchangeArray(array());
        $this->view->headLink()->getContainer()->exchangeArray(array());
        $this->view->headScript()->getContainer()->exchangeArray(array());
    }

    public function setPagination($table_data, $arrParam) {
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('/components/pager/controls_pager.phtml');

        $paginator = Zend_Paginator::factory($table_data);

        $paginator->setCurrentPageNumber($arrParam['page'], 1);

        $paginator->setDefaultItemCountPerPage($arrParam['line_per_page']);

        $this->view->paginator = $paginator;
    }

    protected function setMetaDetail($metaDetail) {
        foreach ($metaDetail as $key => $value) {
            $this->view->headMeta()->setProperty('og:' . $key, $value);
            $this->view->headMeta()->setName($key, $value);
        }
    }

    protected function searchAction() {
        $this->_helper->layout()->disableLayout();
        $param = $this->_arrParam;
        $where = "";
        foreach ($param as $key => $value) {
            $where .= $key . ' like "%' . $value . '%"';
            if (next($param)) {
                $where .= ' and ';
            }
        }

        if (strlen($where) < 1) {
            $where = NULL;
        }
        $this->view->result = $this->_model->fetchAll($where);
    }

}
