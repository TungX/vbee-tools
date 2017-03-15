<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author YINLONG
 */
Zend_Loader::loadClass('Model_Application');

class Model_User extends Model_Application {

    protected $_name = "user";
    protected $_primary = "id";
    
    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    
    public function updatePassword($param) {
        $password_by_system = $param['password_by_system'];
        $param['password_by_system'] = NULL;
        $this->update($param, array('password_by_system = ?' => $password_by_system));
    }

}
