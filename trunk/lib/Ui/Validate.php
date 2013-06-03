<?php

function isNumeric( $int, $options = array() ) {
    $options['options'] = array("regexp" => "/^\d+$/");
    return filter_var($int, FILTER_VALIDATE_REGEXP, $options);

}

function isInt( $int, $options = array() ) {
    return filter_var($int, FILTER_VALIDATE_INT, $options);

}

function isEmail( $email ) {
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    return !!filter_var($email, FILTER_VALIDATE_EMAIL);

}
