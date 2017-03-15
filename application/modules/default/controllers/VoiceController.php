<?php

class VoiceController extends Amobi_Controller_Action {

    private $_type;

    public function init() {
        parent::init();
        Zend_Loader::loadClass('Model_Voice');
        $this->_model = new Model_Voice();
    }

    public function predisPatch() {
        parent::predisPatch();
        $this->_type = +($this->_arrParam["type"] == "uninstall");
        $this->view->headScript()->appendFile('/templates/default/js/dictionary_page.js', 'text/javascript');
        $type_name = array('install', 'uninstall');
        $this->view->type_name = $type_name[$this->_type];
    }

    public function indexAction() {
        $this->view->voices = $this->_model->fetchAll("type = '$this->_type'");
    }

    public function createAction() {
        $this->_helper->layout()->disableLayout();
        $nameFile = $_FILES['name']['name'];
        echo $_FILES['name']['tmp_name'] . '<br>';
        move_uploaded_file($_FILES['name']['tmp_name'], "uploads/voices/" . $nameFile);
        $this->_model->save(array('name' => $nameFile));
        $this->_helper->redirector('index', 'voice', 'default', array());
    }

    public function updateAction() {
        $this->_helper->layout()->disableLayout();
        $nameFile = $_FILES['name']['name'];
        echo $_FILES['name']['tmp_name'] . '<br>';
        move_uploaded_file($_FILES['name']['tmp_name'], "uploads/voices/" . $nameFile);
        $id = $this->_arrParam['id'];
        $this->_model->save(array('id' => $id, 'name' => $nameFile));
        $this->_helper->redirector('index', 'voice', 'default', array());
    }

    public function destroyAction() {
        $this->_helper->layout()->disableLayout();
        $param = $this->_arrParam;
        if($this->_type == 0){
            $this->view->result = json_encode(array('status' => 1, 'id' => $this->_model->save(array('id' => $param['id'], 'type' => 1))));
        }else{
            $this->view->result = json_encode(array('status' => 1, 'id' => $this->_model->delete($param)));
        }
        
    }

    public function searchAction() {
        parent::searchAction();
        $result = array();
        foreach ($this->view->result as $key => $server) {
            $result[$key] = $server->toArray();
        }
        $this->view->result = json_encode($result);
    }

    public function beforesynchronizeAction() {
        $this->_helper->layout()->disableLayout();
        try {
            Zend_Loader::loadClass('Model_Server');
            $serverModel = new Model_Server();
            $servers = $serverModel->beforeSynchronize($this->view->type_name);
            $result = array();
            foreach ($servers as $server) {
                $result[$server['id']] = $server['name'];
            }
            $this->view->result = json_encode(array('status' => 1, 'servers' => $result));
        } catch (Exception $e) {
            $this->view->result = json_encode(array('status' => 2, 'message' => 'Lỗi trong quá trình lưu'));
        }
    }

    public function synchronizeAction() {
        $this->_helper->layout()->disableLayout();
        Zend_Loader::loadClass('Model_Server');
        $serverModel = new Model_Server();
        Zend_Loader::loadClass('Model_Voice');
        $voiceModel = new Model_Voice();
        $voices = $voiceModel->fetchAll("type = '$this->_type'");
        $servers = $serverModel->fetchWithSynServer($this->view->type_name);
        $restart = $this->_arrParam['restart'];
        foreach ($servers as $server) {
            $server->synchronizeVoices($voices, $this->view->type_name, $restart);
        }
    }

    public function checksynchronizeAction() {
        $this->_helper->layout()->disableLayout();
        Zend_Loader::loadClass('Model_ServerSynchronize');
        $serverSynModel = new Model_ServerSynchronize();
        $type_name = $this->view->type_name;
        $servers = $serverSynModel->fetchAll("type='$type_name' and status <> 1");
        $result = array();
        foreach ($servers as $value) {
            $result[] = array('id' => $value['server_id'], 'status' => $value['status']);
        }
        $this->view->result = json_encode($result);
    }

}
