<?php

class SubtitleController extends Amobi_Controller_Action {

    public function init() {
        $this->_action_non_auth = array('update-status', 'callback');
        parent::init();
        Zend_Loader::loadClass('Model_Subtitle');
        $this->_model = new Model_Subtitle();
    }

    public function predisPatch() {
        parent::predisPatch();
        $this->view->headScript()->appendFile('/templates/default/js/subtitle_page.js', 'text/javascript');
    }

    public function indexAction() {
        $this->view->subtitles = $this->_model->fetchAll();
    }

    public function callbackAction() {
        $this->_helper->layout()->disableLayout();
        $content = file_get_contents("php://input");
        try {
            $result = json_decode($content);
            $params = array();
            if ($result->status == 'done') {
                $params['status'] = 1;
                $params['audio_path'] = $result->downloadUrl;
                $params['audio_format'] = $result->fileFormat;
            } else {
                $params['status'] = 2;
            }
            $this->_model->update($params, array('request_id = ?' => $result->requestId));
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
        echo 'done';
    }

    public function createAction() {
        $param = $this->_arrParam;
        $param['id'] = null;
        $subtitleFile = $_FILES['subtitle_path']['name'];
        $subtitleFileNormalization = str_replace("\\s+", "-", $subtitleFile);
        if (!file_exists('uploads/subtitles')) {
            mkdir('uploads/subtitles', 0777, true);
        }
        move_uploaded_file($_FILES['subtitle_path']['tmp_name'], "uploads/subtitles/" . $subtitleFileNormalization);
        $param['subtitle_path'] = "uploads/subtitles/" . $subtitleFileNormalization;
        $param['time_create'] = date('Y-m-d');
        $actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
        $subtitleUrl = $actual_link . "/" . $param['subtitle_path'];
        $url = 'http://' . $param['subtitle_server'] . "/synthesis?subtitle-url=" . urlencode($subtitleUrl) . "&callback=" . urlencode($param['callback']);
        $url = preg_replace("/ /", "%20", $url);
        $contents = file_get_contents($url);
        if ($contents === FALSE) {
            $this->view->subtitles = $this->_model->fetchAll();
            $this->view->error_message = "cannot connect http://" . $param['subtitle_server'];
            $this->render('index');
            return;
        }

        $result = json_decode($contents);
        if ($result->status == 'success') {
            $param['request_id'] = $result->requestId;
            $id = $this->_model->save($param);
            if ($id > 0) {
                $this->_helper->redirector('index', 'subtitle', 'default', array());
            } else {
                $this->view->subtitles = $this->_model->fetchAll();
                $this->view->error_message = "cannot save subtitle";
                $this->render('index');
            }
            return;
        }
        $this->view->subtitles = $this->_model->fetchAll();
        $this->view->error_message = $result->message;
        $this->render('index');
    }

    public function synthesizeAction() {
        $param = $this->_arrParam;
        $id = $param['id'];
        try {
            $subtitle = $this->_model->find($id)->current();
            $actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
            $subtitleUrl = $actual_link . "/" . $subtitle['subtitle_path'];
            $url = 'http://' . $subtitle['subtitle_server'] . "/synthesis?subtitle-url=" . urlencode($subtitleUrl) . "&callback=" . urlencode($subtitle['callback']);
            $url = preg_replace("/ /", "%20", $url);
            $contents = file_get_contents($url);
            if ($contents === FALSE) {
                $this->view->subtitles = $this->_model->fetchAll();
                $this->view->error_message = "cannot connect http://" . $subtitle['subtitle_server'];
                $this->render('index');
                return;
            }

            $result = json_decode($contents);
            if ($result->status == 'success') {
                $param['request_id'] = $result->requestId;
                $param['status'] = 0;
                $id = $this->_model->save($param);
                if ($id > 0) {
                    $this->_helper->redirector('index', 'subtitle', 'default', array());
                } else {
                    $this->view->subtitles = $this->_model->fetchAll();
                    $this->view->error_message = "cannot save subtitle";
                    $this->render('index');
                }
                return;
            }
            $this->view->subtitles = $this->_model->fetchAll();
            $this->view->error_message = $result->message;
            $this->render('index');
        } catch (Exception $exc) {
            $this->view->subtitles = $this->_model->fetchAll();
            $this->view->error_message = $exc->getTraceAsString();
            $this->render('index');
        }
    }

    public function updateAction() {
        $param = $this->_arrParam;
        $id = $param['id'];
        if (key_exists('subtitle_path', $_FILES) && !empty($_FILES['subtitle_path']['name'])) {
            $subtitleFile = $_FILES['subtitle_path']['name'];
            $subtitleFileNormalization = str_replace("\\s+", "-", $subtitleFile);
            if (!file_exists('uploads/subtitles')) {
                mkdir('uploads/subtitles', 0777, true);
            }
            move_uploaded_file($_FILES['subtitle_path']['tmp_name'], "uploads/subtitles/" . $subtitleFileNormalization);
            $param['subtitle_path'] = "uploads/subtitles/" . $subtitleFileNormalization;
        }

        try {
            $subtitle = $this->_model->find($id)->current();
            $subtitle_synthesize = false;
            if (!empty($param['subtitle_path']) && $param['subtitle_path'] != $subtitle['subtitle_path']) {
                echo 'subtitle path diff: ' . $param['subtitle_path'];
                $subtitle_synthesize = $subtitle_synthesize || true;
            } else {
                $param['subtitle_path'] = $subtitle['subtitle_path'];
            }
            if (!empty($param['voice_name']) && $param['voice_name'] != $subtitle['voice_name']) {
                echo 'voice_name diff';
                $subtitle_synthesize = $subtitle_synthesize || true;
            } else {
                $param['voice_name'] = $subtitle['voice_name'];
            }

            if (!empty($param['subtitle_server']) && $param['subtitle_server'] != $subtitle['subtitle_server']) {
                echo 'subtitle_server diff';
                $subtitle_synthesize = $subtitle_synthesize || true;
            } else {
                $param['subtitle_server'] = $subtitle['subtitle_server'];
            }
            if (empty($param['callback'])) {
                $param['callback'] = $subtitle['callback'];
            }

            if ($subtitle_synthesize) {
                $actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
                $subtitleUrl = $actual_link . "/" . $param['subtitle_path'];
                $url = 'http://' . $param['subtitle_server'] . "/synthesis?subtitle-url=" . urlencode($subtitleUrl) . "&callback=" . urlencode($param['callback']);
                $url = preg_replace("/ /", "%20", $url);
                $contents = file_get_contents($url);
                if ($contents === FALSE) {
                    $this->view->subtitles = $this->_model->fetchAll();
                    $this->view->error_message = "cannot connect http://" . $param['subtitle_server'];
                    $this->render('index');
                    return;
                }
                $result = json_decode($contents);
                if ($result->status == 'success') {
                    $param['request_id'] = $result->requestId;
                    $param['status'] = 0;
                } else {
                    $this->view->subtitles = $this->_model->fetchAll();
                    $this->view->error_message = $result->message;
                    $this->render('index');
                    return;
                }
            }
            $id = $this->_model->save($param);
            if ($id > -1) {
                $this->_helper->redirector('index', 'subtitle', 'default', array());
            } else {
                $this->view->subtitles = $this->_model->fetchAll();
                $this->view->error_message = "cannot save subtitle";
                $this->render('index');
            }
        } catch (Exception $exc) {
            $this->view->subtitles = $this->_model->fetchAll();
            $this->view->error_message = $exc->getTraceAsString();
            $this->render('index');
        }
    }

    public function destroyAction() {
        $this->_helper->layout()->disableLayout();
        $param = $this->_arrParam;
        $this->view->result = json_encode(array('id' => $this->_model->delete($param)));
    }

    public function searchAction() {
        parent::searchAction();
        $result = array();
        foreach ($this->view->result as $key => $server) {
            $result[$key] = $server->toArray();
        }
        $this->view->result = json_encode($result);
    }

}
