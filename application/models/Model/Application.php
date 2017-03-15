<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Application
 *
 * @author YINLONG
 */
class Model_Application extends Zend_Db_Table_Abstract {

    protected $_dbTable;

    public function save($param) {
        try {
            if (null === ($id = $param['id'])) {
                unset($param['id']);
                return $this->insert($param);
            }
            return $this->update($param, array('id = ?' => $id));
        } catch (Exception $e) {
            echo $e->getMessage();
            return -1;
        }
    }

    public function delete($param) {
        $where = array('id = ?' => $param['id']);
        parent::delete($where);
    }
}
