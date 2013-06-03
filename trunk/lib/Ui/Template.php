<?php

/* @Echo a HTML string */

function _e( $message = '', $begin = '', $end = '' ) {
    if ( $message !== NULL ) {
        echo $begin . $message . $end;
    }

}

/* doctype */

function doctype( $version = '' ) {
    //still a long time to decide using html5 doctype
    return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';

}

function _doctype( $version = '' ) {
    _e(doctype($version));

}

/* @Link rel css */

function htmlLink( $url, $rel = 'stylesheet', $type = 'text/css' ) {
    if ( !preg_match('/^http/i', $url) ) {
        $url = BASE_URL . $url;
    }
    return '<link rel="' . $rel . '" href="' . $url . '" type="' . $type . '"/>';

}

function _htmlLink( $url, $rel = 'stylesheet', $type = 'text/css' ) {
    _e(htmlLink($url, $rel, $type));

}

/* @Javscript */

function htmlScript( $url ) {
    if ( !preg_match('/^http/i', $url) ) {
        $url = BASE_URL . $url;
    }
    return '<script src="' . $url . '" type="text/javascript"></script>';

}

function _htmlScript( $url ) {
    _e(htmlScript($url));

}

function htmlFlash( $url = '', $width = 300, $height = 250 ) {
    return "<embed src='$url' pluginspage='http://www.macromedia.com/go/getflashplayer' wmode='transparent' type='application/x-shockwave-flash' allowscriptaccess='always' width='$width' align='middle' height='$height'>";

}

/**
 * The best way to make a id unique for every page HTML
 * @return String
 */
function getId() {
    return uniqid();

}

