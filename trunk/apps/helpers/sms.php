<?php

function sendSMS( $data, $model = null ) {
    if ( !$model )
        $model = Zone_Base::$Model;
    //url service
    $url = 'http://app.dos.com.vn/service/sms?wsdl';

    $dest = $data['address'];

    //fix multiply phone number
    $dest = preg_split('/\D+/u', $dest);
    $dest = $dest[0];

    if ( $dest[0] != "0" ) {
        $dest = "0" . $dest;
    }

    $msg = $data['content'];

    //time will send msg to VDC , below default is current time
    $reqtime = str_replace(' ', '', date('Y m d H i s', time()));

    $security_code = $model->fetchOne("SELECT `security_code` FROM `configs` LIMIT 1");

    $data['status'] = 0;
    $data['result_send'] = 'Error';

    //try{
    ini_set('soap.wsdl_cache_enabled', 0);
    $client = new SoapClient($url); //,array("trace" => 1, "exceptions" => 0));

    $smsResult = $client->SendMT($dest, $msg, $reqtime, $security_code);
    $data['status'] = $smsResult;
    $data['result_send'] = $smsResult; // == '202' ? 'Success' : 'Faile';
    //}catch(Exception $e){
    //die(var_dump($e));
    //}
    //save to history
    $model->insert('sms', $data);

}

function sendEmail( $data ) {

}