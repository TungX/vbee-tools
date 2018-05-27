<?php

class UpdateController extends Amobi_Controller_Action {

    private $_type;

    public function init() {
        parent::init();
        Zend_Loader::loadClass('Model_Software');
        $this->_model = new Model_Software();
    }

    public function predisPatch() {
        parent::predisPatch();
        $this->view->headScript()->appendFile('/templates/default/js/dictionary_page.js', 'text/javascript');
    }

    public function indexAction() {
        $this->view->softwares = $this->_model->fetchAll();
    }

    public function createAction() {
        $this->_helper->layout()->disableLayout();
        $nameFile = $_FILES['name']['name'];
        echo $_FILES['name']['tmp_name'] . '<br>';
        if (move_uploaded_file($_FILES['name']['tmp_name'], "uploads/softwares/" . $nameFile)) {
            $this->_model->save(array('name' => $nameFile));
        }
        $this->_helper->redirector('index', 'update', 'default', array());
    }

    public function updateAction() {
        $this->_helper->layout()->disableLayout();
        $nameFile = $_FILES['name']['name'];
        echo $_FILES['name']['tmp_name'] . '<br>';
        $id = $this->_arrParam['id'];
        if (move_uploaded_file($_FILES['name']['tmp_name'], "uploads/softwares/" . $nameFile)) {
            $this->_model->save(array('id' => $id, 'name' => $nameFile));
        }
        $this->_helper->redirector('index', 'update', 'default', array());
    }

    public function destroyAction() {
        $this->_helper->layout()->disableLayout();
        $param = $this->_arrParam;
        $this->view->result = json_encode(array('status' => 1, 'id' => $this->_model->delete($param)));
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
            $servers = $serverModel->beforeSynchronize('software');
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
        $id = $this->_arrParam['id'];
        Zend_Loader::loadClass('Model_Server');
        $serverModel = new Model_Server();
        Zend_Loader::loadClass('Model_Software');
        $softwareModel = new Model_Software();
        $softwares = $softwareModel->find($id);
        $servers = $serverModel->fetchWithSynServer('software');
        $restart = $this->_arrParam['restart'];
        foreach ($servers as $server) {
            $server->synchronizeSoftwares($softwares, 'update-software', $restart);
        }
    }

    public function checksynchronizeAction() {
        $this->_helper->layout()->disableLayout();
        Zend_Loader::loadClass('Model_ServerSynchronize');
        $serverSynModel = new Model_ServerSynchronize();
        $servers = $serverSynModel->fetchAll("type='software' and status <> 1");
        $result = array();
        foreach ($servers as $value) {
            $result[] = array('id' => $value['server_id'], 'status' => $value['status']);
        }
        $this->view->result = json_encode($result);
    }

}
