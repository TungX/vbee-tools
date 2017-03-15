<?php

class IndexController extends Amobi_Controller_Action {

    public function init() {
        parent::init();
        Zend_Loader::loadClass('Model_Server');
        $this->_model = new Model_Server();
    }

    public function predisPatch() {
        parent::predisPatch();
        $this->view->headScript()->appendFile('/templates/default/js/server_page.js', 'text/javascript');
    }

    public function indexAction() {
        $servers = $this->_model->fetchAll();
        $this->view->servers = array();
        foreach ($servers as $key => $server) {
            $this->view->servers[$key] = $server->toArray();
            $this->view->servers[$key]['status'] = $server->checkStatus();
        }

        $this->view->serverModel = $this->_model;
        $this->_model->createPattern();
    }

    public function createAction() {
        $this->_helper->layout()->disableLayout();
        $param = $this->_arrParam;
        $param['id'] = null;
        $this->view->result = json_encode(array('status' => 1, 'id' => $this->_model->save($param)));
    }

    public function updateAction() {
        $this->_helper->layout()->disableLayout();

        $param = $this->_arrParam;
        if (array_key_exists("manager_action", $param)) {
            $server = $this->_model->find($param['id']);
            if (count($server) > 0) {
                $server = $server[0];
                $this->view->result = json_encode(array('status' => 1, 'message' => $server->{$param['manager_action']}()));
            } else {
                $this->view->result = json_encode(array('status' => 2, 'message' => 'Server is not exist!'));
            }
        } else {
            $this->view->result = json_encode(array('status' => 1, 'id' => $this->_model->save($param)));
        }
    }

    public function destroyAction() {
        $this->_helper->layout()->disableLayout();
        $param = $this->_arrParam;
        $this->view->result = json_encode(array('status' => 1, 'id' => $this->_model->delete($param)));
    }

    public function searchAction() {
        $status = NULL;
        if (array_key_exists('status', $this->_arrParam)) {
            $status = $this->_arrParam['status'];
            unset($this->_arrParam['status']);
        }
        parent::searchAction();
        $result = array();
        foreach ($this->view->result as $key => $server) {
            $result[$key] = $server->toArray();
            $result[$key]['status'] = $server->checkStatus();
        }
        if ($status != NULL) {
            $result_status = array();
            foreach ($result as $value) {
                if ($value['status'] == $status) {
                    $result_status[] = $value;
                }
            }
            $result = $result_status;
        }
        $this->view->result = json_encode($result);
    }

}
