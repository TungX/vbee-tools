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
Zend_Loader::loadClass('Model_Application');

class Model_Server extends Model_Application {

    protected $_name = "server";
    protected $_primary = "id";
    protected $_rowClass = 'Model_Row_Server';

    public function createPattern() {
        $this->id = '';
        $this->name = '';
        $this->ip_address = '';
        $this->username = '';
        $this->password = '';
        return $this;
    }

    public function beforeSynchronize($type_name) {
        Zend_Loader::loadClass('Model_ServerSynchronize');
        $serverSynModel = new Model_ServerSynchronize();
        $servers = $this->fetchAll();
        $server_ids = array();
        foreach ($servers as $server) {
            $server_ids[$server['id']] = $server;
        }
        $serverSyns = $serverSynModel->fetchAll("type = '$type_name'");
        foreach ($serverSyns as $server) {
            if (array_key_exists($server['server_id'], $server_ids)) {
                $serverSynModel->save(array('id' => $server['id'], 'status' => 1));
                unset($server_ids[$server['server_id']]);
            } else {
                $serverSynModel->delete(array('id' => $server['id']));
            }
        }
        foreach ($server_ids as $id=>$server) {
            $data = array('server_id' => $id, 'status' => 1, 'type' => $type_name);
            $serverSynModel->save($data);
        }
        return $servers;
    }
    
    public function fetchWithSynServer($type_name) {
        Zend_Loader::loadClass('Model_ServerSynchronize');
        $serverSynModel = new Model_ServerSynchronize();
        $servers = $this->fetchAll();
        $serverSyns = $serverSynModel->fetchAll("type = '$type_name'");
        $server_ids = array();
        foreach ($servers as $server) {
            $server_ids[$server['id']] = $server;
        }
        foreach ($serverSyns as $serverSyn) {
            $server_ids[$serverSyn['server_id']]->setServerSyn($serverSyn);
        }
        return $servers;
    }
}
