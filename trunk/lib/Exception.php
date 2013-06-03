<?php

class App_Exception extends Exception {

    public function __construct( $msg = '', $code = 0, Exception $previous = null ) {
        if ( version_compare(PHP_VERSION, '5.3.0', '<') ) {
            parent::__construct($msg, (int) $code);
            $this->_previous = $previous;
        } else {
            parent::__construct($msg, (int) $code, $previous);
        }

    }

}
