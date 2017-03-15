<?php

class DictionaryController extends Amobi_Controller_Action {

    private $_type;
    private $_dic_name = array('viết tắt', 'vay mượn');
    private $_dic_file = array('user-abbrev-vi.txt', 'user-loanword-vi.txt');
    private $_make_line = array('makeAbbrevLine', 'makeLoanLine');

    public function init() {
        parent::init();
        Zend_Loader::loadClass('Model_Dictionary');
        $this->_model = new Model_Dictionary();
    }

    public function predisPatch() {
        parent::predisPatch();
        $this->view->errors = array();
        $this->_type = +($this->_arrParam["type"] == "loan");
        $this->view->headScript()->appendFile('/templates/default/js/dictionary_page.js', 'text/javascript');
        $type_name = array('abbrev', 'loan');
        $this->view->type_name = $type_name[$this->_type];
    }

    public function indexAction() {
        $this->view->dic_name = $this->_dic_name[$this->_type];
        $this->view->type = $this->_type;
        $this->view->dictionary = $this->_model->fetchAll("type = '" . $this->_type . "'");
    }

    public function createAction() {
        $this->_helper->layout()->disableLayout();
        $param = $this->_arrParam;
        $param['id'] = null;
        $id = $this->_model->save($param);
        if ($id == -1) {
            $this->view->result = json_encode(array('status' => 2, 'message' => 'Lỗi trong quá trình lưu'));
        } else {
            $this->view->result = json_encode(array('status' => 1, 'id' => $id));
        }
    }

    public function updateAction() {
        $this->_helper->layout()->disableLayout();
        $param = $this->_arrParam;
        $id = $this->_model->save($param);
        if ($id == -1) {
            $this->view->result = json_encode(array('status' => 2, 'message' => 'Lỗi trong quá trình lưu'));
        } else {
            $this->view->result = json_encode(array('status' => 1, 'id' => $id));
        }
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
        if ($this->makeFileToSynchronize()) {
            Zend_Loader::loadClass('Model_Server');
            $serverModel = new Model_Server();
            $servers = $serverModel->beforeSynchronize($this->view->type_name);
            $result = array();
            foreach ($servers as $server) {
                $result[$server['id']] = $server['name'];
            }
            $this->view->result = json_encode(array('status' => 1, 'servers' => $result));
        } else {
            $this->view->result = json_encode(array('status' => 2, 'message' => 'Lỗi trong quá trình lưu'));
        }
    }

    public function synchronizeAction() {
        $this->_helper->layout()->disableLayout();
        Zend_Loader::loadClass('Model_Server');
        $serverModel = new Model_Server();
        $servers = $serverModel->fetchWithSynServer($this->view->type_name);
        $restart = $this->_arrParam['restart'];
        foreach ($servers as $server) {
            $server->synchronizeDictionary($this->_dic_file[$this->_type], $restart);
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

    private function readFilePhonetic() {
        $dictionary_phone = array();
        if (($file = fopen("g2p_loan_phonetic.txt", "r"))) {
            while (!feof($file)) {
                $line = fgets($file);
                $elements = split(" ", $line);
                $phonetic_arr = preg_split("/[^a-zA-Z0-9_]+/", $elements[1]);
                $dictionary_phone[$elements[0]] = implode(" ", $phonetic_arr);
            }
            fclose($file);
        }
        return $dictionary_phone;
    }

    private function makeFileToSynchronize() {

        try {
            $file = fopen($this->_dic_file[$this->_type], "w");
            $dictionary = $this->_model->fetchAll("type = '" . $this->_type . "'");
            $function_make_line = $this->_make_line[$this->_type];
            if ($this->_type == 1) {
                $dictionary_phone = $this->readFilePhonetic();
            }
            foreach ($dictionary as $line) {
                fwrite($file, $this->$function_make_line($line['word'], $line['spelling'], $dictionary_phone));
            }
            fclose($file);
        } catch (Exception $e) {
            echo $e->getMessage();
            return false;
        }
        return true;
    }

    private function makeAbbrevLine($word, $spelling) {
        return $word . "\t/\t/" . $spelling . "\n";
    }

    private function makeLoanLine($word, $spelling, $dictionary_phone) {
        $phones = preg_split("/(\s|-)+/", $spelling);
        $phonetic_arr = array();
        foreach ($phones as $phone) {
            $spell = $dictionary_phone[strtolower($phone)];
            if (strlen($spell) > 0) {
                array_push($phonetic_arr, $spell);
            }
        }
        $phonetic = implode(" - ", $phonetic_arr);
        $phonetic = trim(preg_replace("/\s+/", " ", $phonetic));
        return  "$word / $spelling [$phonetic]\n";
    }

}
