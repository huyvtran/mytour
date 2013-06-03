<?php

/**
 * @name Mail
 * @author Nguyen Duc Minh
 * @date Jul 10, 2012
 */
function load_zend_mail() {
    require_once(ROOT . '/lib/Zend/Loader.php');
    require_once(ROOT . '/lib/Zend/Mail.php');
    require_once(ROOT . '/lib/Zend/Mail.php');
    require_once(ROOT . '/lib/Zend/Mail/Transport/Smtp.php');

}

/**
 *
 * @param String $host
 * @param String $email_from
 * @param String $name_from
 * @param String $email_to
 * @param String $name_to
 * @param String $subject
 * @param String $body
 * @param String $configs
 * @return Boolean
 */
function send_mail_smpt( $host, $email_from, $name_from, $email_to, $name_to, $subject, $body, $configs = array(), $re = true ) {

    load_zend_mail();

    if ( $host == 'smtp.gmail.com' && $re ) {
        $password = $configs['password'];
        return send_gmail($email_from, $name_from, $password, $email_to, $name_to, $subject, $body);
    }

    $transport = new Zend_Mail_Transport_Smtp($host, $configs);

    $mail = new Zend_Mail('utf-8');
    $mail->setBodyHtml($body);
    $mail->setFrom($email_from, $name_from);
    $mail->addTo($email_to, $name_to);
    $mail->setSubject($subject);
    try {
        $mail->send($transport);
        return true;
    } catch (Exception $e) {
        return false;
    }

}

/**
 * Send mail with gmail account
 *
 * @param String $email_from
 * @param String $name_from
 * @param String $password
 * @param String $email_to
 * @param String $name_to
 * @param String $subject
 * @param String $body
 */
function send_gmail( $email_from, $name_from, $password, $email_to, $name_to, $subject, $body ) {

    load_zend_mail();

    $host = 'smtp.gmail.com';
    $configs = array(
        'auth' => 'login',
        'ssl' => 'ssl',
        'port' => '465',
        'username' => $email_from,
        'password' => $password
    );

    return send_mail_smpt($host, $email_from, $name_from, $email_to, $name_to, $subject, $body, $configs, false);

}

?>
