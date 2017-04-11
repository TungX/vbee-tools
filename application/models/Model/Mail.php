<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Mail
 *
 * @author YINLONG
 */
class Model_Mail {

    private $config = array(
        'port' => 587,
        'ssl' => 'tls',
        'auth' => 'login',
        'username' => '',
        'password' => '',
    );

    public function sendEmail($email, $subject, $content) {
        $mail_config = parse_ini_file(APPLICATION_PATH . '/configs/mail.ini');
        foreach ($this->config as $key => $value) {
            $this->config[$key] = $mail_config["mail.".$key];
        }
        $transport = new Zend_Mail_Transport_Smtp($mail_config["mail.host"], $this->config);
        $mail = new Zend_Mail();
        $mail->setBodyHtml($content);
        $mail->setFrom($this->config['username'], 'Vbee Gateway');
        $mail->addTo($email);
        $mail->setSubject($subject);
        $mail->send($transport);
    }

}
