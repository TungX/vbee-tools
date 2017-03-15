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
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'ssl' => 'tls',
        'auth' => 'login',
        'username' => 'blue.rose.hut@gmail.com',
        'password' => 'iamSmt193',
    );

    public function sendEmail($email, $subject, $content) {
        $transport = new Zend_Mail_Transport_Smtp('smtp.gmail.com', $this->config);
        $mail = new Zend_Mail();
        $mail->setBodyHtml($content);
        $mail->setFrom($this->config['username'], 'Vbee Gateway');
        $mail->addTo($email);
        $mail->setSubject($subject);
        $mail->send($transport);
    }

}
