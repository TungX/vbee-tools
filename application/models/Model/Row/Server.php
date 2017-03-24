<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Server
 *
 * @author YINLONG
 */
class Model_Row_Server extends Zend_Db_Table_Row_Abstract {

    private $_serverSyn;

    public function checkStatus() {
        $result = exec("java -jar jar/VbeeController.jar " . str_replace(":", " ", $this->ip_address) . " $this->username $this->password $this->dir_base check");
        return strlen($result) > 0 ? $result : 'Not connect';
    }

    public function start() {
        exec("java -jar jar/VbeeController.jar " . str_replace(":", " ", $this->ip_address) . " $this->username $this->password $this->dir_base start");
        return $this->checkStatus();
    }

    public function stop() {
        exec("java -jar jar/VbeeController.jar " . str_replace(":", " ", $this->ip_address) . " $this->username $this->password $this->dir_base stop");
        return $this->checkStatus();
    }

    public function restart() {
        exec("java -jar jar/VbeeController.jar " . str_replace(":", " ", $this->ip_address) . " $this->username $this->password $this->dir_base restart");
        return $this->checkStatus();
    }

    public function synchronizeDictionary($fileName, $restart = 0) {
        $action = "update-dictionary";
        $filePath = $fileName;
        $result = exec("java -jar jar/VbeeController.jar " . str_replace(":", " ", $this->ip_address) . " $this->username $this->password $this->dir_base $action $filePath $fileName");
        if ($result == "Not ok") {
            $this->_serverSyn->status = 0;
            $this->_serverSyn->save();
        } else {
            echo $result;
            $this->_serverSyn->status = 2;
            $this->_serverSyn->save();
            if ($restart == 1) {
                $this->restart();
            }
        }
    }

    public function synchronizeVoices($voices, $action, $restart = 0) {
        $number_voice_complete = 0;
        $action = $action . '-voice';
        foreach ($voices as $voice) {
            if ($this->synchronizeVoice($voice['name'], $action)) {
                $number_voice_complete++;
            }
            if ($number_voice_complete == count($voices)) {
                $this->_serverSyn->status = 2;
                $this->_serverSyn->save();
                if ($restart == 1) {
                    $this->restart();
                }
            } else {
                $this->_serverSyn->status = 0;
                $this->_serverSyn->save();
            }
        }
    }
    
    public function synchronizeSoftwares($softwares, $action, $restart = 0) {
        $number_software_complete = 0;
        foreach ($softwares as $softwares) {
            if ($this->synchronizeSoftware($softwares['name'], $action)) {
                $number_software_complete++;
            }
            if ($number_software_complete == count($softwares)) {
                $this->_serverSyn->status = 2;
                $this->_serverSyn->save();
                if ($restart == 1) {
                    $this->restart();
                }
            } else {
                $this->_serverSyn->status = 0;
                $this->_serverSyn->save();
            }
        }
    }
    
    private function synchronizeSoftware($fileName, $action) {
        $filePath = 'uploads/softwares/'.$fileName;
        $result = exec("java -jar jar/VbeeController.jar " . str_replace(":", " ", $this->ip_address) . " $this->username $this->password $this->dir_base $action $filePath $fileName");
        return $result != "Not ok";
    }

    private function synchronizeVoice($fileName, $action) {
        $filePath = 'uploads/voices/'.$fileName;
        $result = exec("java -jar jar/VbeeController.jar " . str_replace(":", " ", $this->ip_address) . " $this->username $this->password $this->dir_base $action $filePath $fileName");
        return $result != "Not ok";
    }

    public function setServerSyn($serverSyn) {
        $this->_serverSyn = $serverSyn;
    }

    public function check() {
        exec("java -jar jar/ProjectTest.jar");
    }

    public function __call($method, array $args) {
        if (!method_exists($this, $method)) {
            return "Method isn't exist";
        }
        parent::__call($method, $args);
    }

}
