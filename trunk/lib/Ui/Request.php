<?php

function mysql_escape_data( $result ) {
    return function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc() ? $result : addslashes((string) $result);

}

function parse_request_param( $value, $type = 0, $filter = null ) {
    if ( is_callable($filter) && !$filter($value) ) {
        return null;
    } else {
        return $type == 0 ? mysql_escape_data($value) : addslashes(stripslashes($value));
    }

}

/**
 *
 * @param String $name
 * @param String|Array $default
 * @param Integer $type
 *      0: get value of a single name input
 *      1: get value of a mutiply name input without convert
 *      2: get value of a multiply name input, if has single then auto convert
 * @param Function|Null $filter
 * @return String|Array|Null
 */
function get( $name, $default = null, $type = 0, $filter = null ) {
    $params = $_REQUEST;
    if ( !is_numeric($type) ) {
        if ( $type == false )
            $type = 0;
        if ( $type == true )
            $type = 1;
    }
    if ( $type > 0 ) {
        if ( !is_array($default) )
            $default = array();
    }else {
        $default = parse_request_param($default, 1, $filter);
    }

    if ( is_array($default) ) {
        $b = array();
        foreach ( $default as $k => $v ) {
            $a = parse_request_param($v, 1, $filter);
            if ( $a != null ) {
                $b[] = $a;
            }
        }
        $default = $b;
    }

    if ( array_key_exists($name, $params) ) {
        $value = $params[$name];
        if ( $type == 0 ) {
            if ( is_array($value) ) {
                return parse_request_param((string) $default, 1, $filter);
            } else {
                return parse_request_param($value, 0, $filter);
            }
        } else {
            //get array params
            if ( !is_array($value) ) {
                if ( $type == 2 ) {
                    if ( !is_callable($filter) || $filter($value) ) {
                        return array(parse_request_param($value, 0, $filter));
                    }
                }
                return $default;
            }
        }
        foreach ( $value as $k => $v ) {
            $a = parse_request_param($v, 0, $filter);
            if ( $a != null )
                $value[$k] = $v;
        }
        return $value;
    }
    return $default;

}

/**
 * Get prameter with interger value
 *
 * @param String $name
 * @param Array|String $default
 * @param Interger $type
 * @return String|Array
 */
function getInt( $name, $default = null, $type = 0 ) {
    return get($name, $default, $type, is_numeric);

}

/**
 * Get prameter with interger value
 *
 * @param String $name
 * @param Array|String $default
 * @param Interger $type
 * @return String|Array
 */
function getDateRq( $name, $default = null, $type = 0 ) {
    return get($name, $default, $type, is_date);

}

function isPost( $name = NULL ) {
    if ( $name ) {
        return $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST[$name]);
    }
    return $_SERVER['REQUEST_METHOD'] == 'POST';

}

function isAjax() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

}

function redirect( $url, $abs = false ) {
    /* Keep _json & _ajax */
    if ( isset($_REQUEST['_json']) ) {
        $url .= preg_match("/\?/ui", $url, $match) ? "&_json=yes" : "?_json=yes";
    }

    if ( $abs )
        $url = BASE_URL . $url;
    header("Location: $url");
    exit;

}

function getEnumInt( $rq ) {
    if ( !is_array($_REQUEST[$rq]) ) {
        $s = getInt($rq, '');
        return preg_match('#^((?:\d+,)*)?\d+$#i', $s) ? $s : '';
    } else {
        $s = getInt($rq, array(), true);
        return count($s) > 0 ? implode(',', $s) : '';
    }

}

/**
 * Parse user-agent to array
 * @param type $u_agent
 * @return type
 */
function parse_user_agent( $u_agent = null ) {
    if ( is_null($u_agent) )
        $u_agent = $_SERVER['HTTP_USER_AGENT'];

    $data = array(
        'platform' => null,
        'browser' => null,
        'version' => null,
    );

    if ( preg_match('/\((.*?)\)/im', $u_agent, $regs) ) {

        // (?<platform>Android|iPhone|iPad|Windows|Linux|Macintosh|Windows Phone OS|Silk|linux-gnu|BlackBerry)(?: x86_64)?(?: NT)?(?:[ /][0-9._]+)*(;|$)
        preg_match_all('%(?P<platform>Android|iPhone|iPad|Windows|Linux|Macintosh|Windows Phone OS|Silk|linux-gnu|BlackBerry)(?: x86_64)?(?: NT)?(?:[ /][0-9._]+)*(;|$)%im', $regs[1], $result, PREG_PATTERN_ORDER);
        $result['platform'] = array_unique($result['platform']);
        if ( count($result['platform']) > 1 ) {
            if ( ($key = array_search('Android', $result['platform'])) !== false ) {
                $data['platform'] = $result['platform'][$key];
            }
        } elseif ( isset($result['platform'][0]) ) {
            $data['platform'] = $result['platform'][0];
        }
    }

    // (?<browser>Camino|Kindle|Firefox|Safari|MSIE|AppleWebKit|Chrome|IEMobile|Opera|Silk|Lynx|Version|Wget)(?:[/ ])(?<version>[0-9.]+)
    preg_match_all('%(?P<browser>Camino|Kindle|Firefox|Safari|MSIE|AppleWebKit|Chrome|IEMobile|Opera|Silk|Lynx|Version|Wget|curl)(?:[/ ])(?P<version>[0-9.]+)%im', $u_agent, $result, PREG_PATTERN_ORDER);

    if ( $data['platform'] == 'linux-gnu' ) {
        $data['platform'] = 'Linux';
    }

    if ( ($key = array_search('Kindle', $result['browser'])) !== false || ($key = array_search('Silk', $result['browser'])) !== false ) {
        $data['browser'] = $result['browser'][$key];
        $data['platform'] = 'Kindle';
        $data['version'] = $result['version'][$key];
    } elseif ( $result['browser'][0] == 'AppleWebKit' ) {
        if ( ( $data['platform'] == 'Android' && !($key = 0) ) || $key = array_search('Chrome', $result['browser']) ) {
            $data['browser'] = 'Chrome';
            if ( ($vkey = array_search('Version', $result['browser'])) !== false ) {
                $key = $vkey;
            }
        } elseif ( $data['platform'] == 'BlackBerry' ) {
            $data['browser'] = 'BlackBerry Browser';
            if ( ($vkey = array_search('Version', $result['browser'])) !== false ) {
                $key = $vkey;
            }
        } elseif ( $key = array_search('Kindle', $result['browser']) ) {
            $data['browser'] = 'Kindle';
        } elseif ( $key = array_search('Safari', $result['browser']) ) {
            $data['browser'] = 'Safari';
            if ( ($vkey = array_search('Version', $result['browser'])) !== false ) {
                $key = $vkey;
            }
        } else {
            $key = 0;
        }

        $data['version'] = $result['version'][$key];
    } elseif ( ($key = array_search('Opera', $result['browser'])) !== false ) {
        $data['browser'] = $result['browser'][$key];
        $data['version'] = $result['version'][$key];
        if ( ($key = array_search('Version', $result['browser'])) !== false ) {
            $data['version'] = $result['version'][$key];
        }
    } elseif ( $result['browser'][0] == 'MSIE' ) {
        if ( $key = array_search('IEMobile', $result['browser']) ) {
            $data['browser'] = 'IEMobile';
        } else {
            $data['browser'] = 'MSIE';
            $key = 0;
        }
        $data['version'] = $result['version'][$key];
    } elseif ( $key = array_search('Kindle', $result['browser']) ) {
        $data['browser'] = 'Kindle';
        $data['platform'] = 'Kindle';
    } else {
        $data['browser'] = $result['browser'][0];
        $data['version'] = $result['version'][0];
    }

    return $data;

}