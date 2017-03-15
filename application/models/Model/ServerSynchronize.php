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
class Model_ServerSynchronize extends Model_Application {

    protected $_name = "server_synchronize";
    protected $_primary = "id";
    protected $_rowClass = 'Model_Row_ServerSynchronize';
}
