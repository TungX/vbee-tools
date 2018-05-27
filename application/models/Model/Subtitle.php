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

class Model_Subtitle extends Model_Application {

    protected $_name = "subtitle";
    protected $_primary = "id";

    public function createAudio($subtitle_path, $voice_name, $action = 'check', $updateAction = null, $subtitleId = 0) {
        Zend_Loader::loadClass('Model_Server');
        $serverModel = new Model_Server();
        $date = new DateTime();
        $command = "java -jar jar/VbeeMovieVoice.jar action=" . $action . " output-dir=subtitle-output/" . $date->getTimestamp() . " subtitle=" . $subtitle_path . ' voice-name=' . $voice_name;
        if ($updateAction != null && $subtitleId != 0) {
            $command = $command . ' update-action=' . $updateAction . ' subtitle-id=' . $subtitleId;
        }
        $servers = $serverModel->fetchAll();
        foreach ($servers as $server) {
            $ip = explode(':', $server['ip_address'])[0];
            $command = $command . ' host=' . $ip;
        }
//        echo $command;
        $result = shell_exec($command);
        return $result;
    }

}
