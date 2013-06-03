<?php

/**
 * Get domain url
 * @param type $show_full_path
 * @return type
 */
function baseUrl( $show_full_path = false ) {
    if ( $show_full_path ) {
        $protocol = $_SERVER['HTTPS'] ? "https" : "http";
        return $protocol . '://' . $_SERVER['HTTP_HOST'] . Zone_Base::$baseUrl;
    }
    return Zone_Base::$baseUrl;

}

//get url with end module
function moduleUrl() {
    return implode('/', array(Zone_Base::$baseUrl, Zone_Base::getModuleName()));

}

//get url with end controller
function ctrUrl() {
    return implode('/', array(Zone_Base::$baseUrl,
                Zone_Base::getModuleName(),
                ucfirst(Zone_Base::getController())
            ));

}

//get url with end action
function actionUrl() {
    return implode('/', array(Zone_Base::$baseUrl,
                Zone_Base::getModuleName(),
                ucfirst(Zone_Base::getController()),
                ucfirst(Zone_Base::getAction())
            ));

}

function currentUrl() {
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? 'https://' : 'http://') . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . $_SERVER['QUERY_STRING'];

}

function currentDomain() {
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? 'https://' : 'http://') . $_SERVER['SERVER_NAME'];

}

function append_url( $url, $params = array() ) {
    if ( count($params) == 0 )
        return $url;
    $url .= preg_match('/\?/u', $url) ? '&' : '?';
    $url .= to_query_configs($params);
    return $url;

}