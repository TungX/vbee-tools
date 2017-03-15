<?php

class UserController extends Amobi_Controller_Action {

    public function init() {
        $this->_action_non_auth = array('login', 'forgot', 'resetpassword', 'updatepassword');
        parent::init();
        Zend_Loader::loadClass('Model_User');
        $this->_model = new Model_User();
    }

    public function predisPatch() {
        parent::predisPatch();
        $this->view->errors = array();
    }

    public function indexAction() {
        $this->view->users = $this->_model->fetchAll();
        $this->view->userModel = $this->_model;
    }

    public function createAction() {
        $this->_helper->layout()->disableLayout();
        $param = $this->_arrParam;
        $param['id'] = null;
        $password = $this->_model->generateRandomString(5) . time();
        $param['password_by_system'] = md5($password);
        unset($param['reset_password']);
        $id = $this->_model->save($param);
        if ($id == -1) {
            $this->view->result = json_encode(array('status' => 2, 'message' => 'Email đã tồn tại'));
        } else {
            Zend_Loader::loadClass('Model_Mail');
            $mailModel = new Model_Mail();
            $mailModel->sendEmail($param['email'], 'Active account', $this->createEmailForActiveEmail($password, $param['password_by_system']));
            $this->view->result = json_encode(array('status' => 1, 'id' => $id));
        }
    }

    public function updateAction() {
        $param = $this->_arrParam;

        $this->_helper->layout()->disableLayout();
        if ($param['reset_password'] == 1) {
            $password = $this->_model->generateRandomString(5) . time();
            $param['password_by_system'] = md5($password);
            $param['password'] = md5($this->_model->generateRandomString(5) . time());
        }
        unset($param['reset_password']);
        $id = $this->_model->save($param);
        if ($id == -1) {
            $this->view->result = json_encode(array('status' => 2, 'message' => 'Email đã tồn tại'));
        } else {
            $this->view->result = json_encode(array('status' => 1, 'id' => $id));
        }
        if (key_exists('password_by_system', $param)) {
            Zend_Loader::loadClass('Model_Mail');
            $mailModel = new Model_Mail();
            $mailModel->sendEmail($param['email'], 'Reset password', $this->createEmailForActiveEmail($password, $param['password_by_system']));
        }
    }

    public function updatepasswordAction() {
        $param = $this->_arrParam;
        $this->checkPasswordParam($param);
        if (count($this->view->errors) == 0) {
            $new_param = array();
            $new_param['password_by_system'] = $param['password'];
            $new_param['password'] = md5($param['new_password']);
            $this->_model->updatePassword($new_param);
            $this->_helper->redirector('index', 'index', 'default', array());
        } else {
            $this->view->password = $param['password'];
            $this->render('resetpassword');
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

    public function resetpasswordAction() {
        $this->logout();
        $param = $this->_arrParam;
        $this->view->password = $param['password'];
        $user = $this->_model->fetchAll("password_by_system = '" . $param['password'] . "'");
        if (count($user) == 0) {
            $this->view->errors[] = 'Password đã được thay đổi';
        }
    }

    public function loginAction() {
        $param = $this->_arrParam;
        if (key_exists('email', $param) && key_exists('password', $param)) {
            $user = $this->_model->fetchAll("password = '" . md5($param['password']) . "' and email= '" . $param['email'] . "'");
            if (count($user) == 0) {
                $this->view->errors[] = 'Email hoặc mật khẩu không đúng!';
            } else {
                $user = $user[0];
                $_SESSION['id'] = $user['id'];
                $_SESSION['session'] = $user['session'];
                $this->_helper->redirector('index', 'index', 'default', array());
            }
        }
    }

    public function forgotAction() {
        $param = $this->_arrParam;
        if (key_exists('email', $param)) {
            $user = $this->_model->fetchAll("email= '" . $param['email'] . "'");
            if (count($user) == 0) {
                $this->view->errors[] = 'Email không đúng!';
            } else {
                $this->view->email = $param['email'];
                $param = array();
                $password = $this->_model->generateRandomString(5) . time();
                $param['password_by_system'] = md5($password);
                $param['password'] = md5($this->_model->generateRandomString(5) . time());
                $param['id'] = $user[0]['id'];
                $this->_model->save($param);
                Zend_Loader::loadClass('Model_Mail');
                $mailModel = new Model_Mail();
                $mailModel->sendEmail($this->view->email, 'Reset password', $this->createEmailForActiveEmail($password, $param['password_by_system']));
                $this->render('resultforgot');
            }
        }
    }

    public function resultforgotAction() {
        
    }

    public function logoutAction() {
        $this->logout();
        $this->_helper->redirector('login', 'user', 'default', array());
    }
    
    private function logout(){
        unset($_SESSION['id']);
        unset($_SESSION['session']);
    }

    private function createEmailForActiveEmail($password, $md5) {
        $fullBaseUrl = $this->view->serverUrl() . $this->view->baseUrl();
        $html = 'Your account has been created and is awaiting activation, please click the ';
        $html .= '<a href="' . $fullBaseUrl . '/user/resetpassword?password=' . $md5 . '">Link</a> to activate<br>';
        $html .= 'Your password: ' . $password . '<br>';
        $html .= '<strong>Thanks!</strong>';
        return $html;
    }

    private function checkPasswordParam($param) {
        $this->view->errors = array();
        if (md5($param['current_password']) != $param['password']) {
            $this->view->errors[] = 'Mật khẩu được cấp không phù hợp';
        }
        if ($param['current_password'] == $param['new_password']) {
            $this->view->errors[] = 'Mật khẩu mới giống mật khẩu cũ';
        }
        if ($param['repeat_password'] != $param['new_password']) {
            $this->view->errors[] = 'Mật khẩu mới khác mật khẩu khẩu được nhập lại';
        }
    }

}
