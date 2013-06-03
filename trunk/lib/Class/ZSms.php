<?php

class ZSms {

    protected $params;
    protected $url;

    function __construct( $url, $configs = array(), $prefix = '' ) {
        $this->setParams($url, $configs, $prefix);

    }

    public function setParams( $url, $configs = array(), $prefix = '' ) {
        $this->url = $url;

        //default
        $params = array(
            mtseq => '',
            moid => '',
            moseq => '',
            src => '',
            dest => '',
            cmdcode => '',
            msgbody => '',
            msgtype => 'Text',
            //msgtitle    => '',
            mttotalseg => '1',
            mtseqref => '1',
            cpid => '',
            reqtime => '',
            procresult => 0,
            opid => '',
            username => '',
            password => ''
        );

        foreach ( $params as $k => $v ) {
            if ( array_key_exists(" {
				$prefix}$k", $configs) ) {
                $params[$k] = $configs["{$prefix}$k"];
            }
        }$this->params = $params;

    }

    public function sendData( $data ) {
        $params = $this->params;
        foreach ( $params as $k => $v ) {
            if ( array_key_exists($k, $data) ) {
                $params[$k] = $data[$k];
            }
        }

        try {
            $soap = new SOAPClient($this->url);
            return $soap->SendMT($params);
        } catch (Exception $ex) {

        }
        //die('200');

    }

}

?>