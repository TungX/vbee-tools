<?php
/**
 * Enter description here...
 *
 */
class Amobi_Controller_Device extends Zend_Controller_Plugin_Abstract {
 
    /**
     * Enter description here...
     *
     * @param Zend_Controller_Request_Abstract $request
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request) {
        //code for your plugin here... :P
        $device = Zend_Registry::get("get_device");
        if($device == 1)
           header("Location:http://qplay.vn/");
    }
}