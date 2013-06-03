<?php

function page_forward( $url ) {
    return @file_get_contents($url);

}

function case_compare( $str1, $str2 ) {
    return strtolower($str1) == strtolower($str2);

}

/*
  Split a string contains unicode
 */

function get_quote( $str, $limit, $more = " ..." ) {
    if ( $str == "" || $str == NULL || is_array($str) || strlen($str) == 0 )
        return $str;

    $str = strip_tags(trim($str));
    if ( strlen($str) <= $limit )
        return $str;
    $str = substr($str, 0, $limit);

    if ( !substr_count($str, " ") ) {
        if ( $more )
            $str .= " ...";
        return $str;
    }

    while (strlen($str) && ($str[strlen($str) - 1] != " ")) {
        $str = substr($str, 0, -1);
    }
    $str = substr($str, 0, -1);
    if ( $more ) {
        return $str . " ...";
    }

    return $str;

}

function short_title( $title, $length ) {
    if ( is_null($title) || !is_string($title) )
        return '';
    if ( preg_match("/^((&[^\s]+;|\w|\W){" . $length . "})/ui", $title, $match) ) {
        if ( preg_match("/^((&[^\s]+;|\w|\W){" . ($length - 4) . "})/ui", $title, $m) ) {
            return "<span title='" . escape_html($title) . "'>{$m[1]} ...</span>";
        } else {
            return $title;
        }
    } else {
        return $title;
    }

}

function html_escape( $data ) {
    return escape_html($data);

}

function escape_html( $data ) {
//    $data = str_ireplace('"', "&quot;", $data);
//    $data = str_ireplace("'", "&#039;", $data);
//    $data = str_ireplace('<', '&lt;', $data);
//    $data = str_ireplace('>', '&gt;', $data);
    $data = preg_replace('/"/ui', "&quot;", $data);
    $data = preg_replace("/'/ui", "&#039;", $data);
    $data = preg_replace('/</ui', '&lt;', $data);
    $data = preg_replace('/>/ui', '&gt;', $data);
    return $data;

}

function create_alias( $data ) {
    $data = preg_replace('/\s+/ui', '-', $data);

    //data must
    $maTViet = array(
        "à", "á", "ạ", "ả", "ã", "â", "ầ", "ấ", "ậ", "ẩ", "ẫ", "ă",
        "ằ", "ắ", "ặ", "ẳ", "ẵ", "è", "é", "ẹ", "ẻ", "ẽ", "ê", "ề",
        "ế", "ệ", "ể", "ễ",
        "ì", "í", "ị", "ỉ", "ĩ",
        "ò", "ó", "ọ", "ỏ", "õ", "ô", "ồ", "ố", "ộ", "ổ", "ỗ", "ơ",
        "ờ", "ớ", "ợ", "ở", "ỡ",
        "ù", "ú", "ụ", "ủ", "ũ", "ư", "ừ", "ứ", "ự", "ử", "ữ",
        "ỳ", "ý", "ỵ", "ỷ", "ỹ",
        "đ",
        "À", "Á", "Ạ", "Ả", "Ã", "Â", "Ầ", "Ấ", "Ậ", "Ẩ", "Ẫ", "Ă",
        "Ằ", "Ắ", "Ặ", "Ẳ", "Ẵ",
        "È", "É", "Ẹ", "Ẻ", "Ẽ", "Ê", "Ề", "Ế", "Ệ", "Ể", "Ễ",
        "Ì", "Í", "Ị", "Ỉ", "Ĩ",
        "Ò", "Ó", "Ọ", "Ỏ", "Õ", "Ô", "Ồ", "Ố", "Ộ", "Ổ", "Ỗ", "Ơ",
        "Ờ", "Ớ", "Ợ", "Ở", "Ỡ",
        "Ù", "Ú", "Ụ", "Ủ", "Ũ", "Ư", "Ừ", "Ứ", "Ự", "Ử", "Ữ",
        "Ỳ", "Ý", "Ỵ", "Ỷ", "Ỹ",
        "Đ", " ", "<", ">", "&", "#", "\'", '\"', "?", "#", "!", ":", "\/"
    );

    $maKoDau = array(
        "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a",
        "a", "a", "a", "a", "a", "a",
        "e", "e", "e", "e", "e", "e", "e", "e", "e", "e", "e",
        "i", "i", "i", "i", "i",
        "o", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o",
        "o", "o", "o", "o", "o",
        "u", "u", "u", "u", "u", "u", "u", "u", "u", "u", "u",
        "y", "y", "y", "y", "y",
        "d",
        "A", "A", "A", "A", "A", "A", "A", "A", "A", "A", "A", "A",
        "A", "A", "A", "A", "A",
        "E", "E", "E", "E", "E", "E", "E", "E", "E", "E", "E",
        "I", "I", "I", "I", "I",
        "O", "O", "O", "O", "O", "O", "O", "O", "O", "O", "O", "O",
        "O", "O", "O", "O", "O",
        "U", "U", "U", "U", "U", "U", "U", "U", "U", "U", "U",
        "Y", "Y", "Y", "Y", "Y",
        "D", "-", "-", "-", "-", "-", "-", "-", "-", "-", "-", "-", "-"
    );
    $data = str_replace($maTViet, $maKoDau, trim($data));
    $data = preg_replace('/-{2,}/ui', '-', $data);
    $data = preg_replace('/-/ui', '_', $data);

    return $data;

}

function mbupper( $str ) {
    return mb_strtoupper($str, 'utf-8');

}

function mblower( $str ) {
    return mb_strtolower($str, 'utf-8');

}

/* Upper or lower case for first char of utf8 string */

function utf8_ucfirst( $str ) {
    return ucfirst($str);

}

function utf8_lcfirst( $str ) {
    return function_exists('lcfirst') ? lcfirst($str) : mb_strtolower($str, 'utf-8');

}

function get_query_configs( $str ) {
    if ( trim($str) == "" )
        return array();
    $result = array();
    $a = explode('&', $str);
    foreach ( $a as $b ) {
        list($key, $value) = explode('=', $b);
        $result[$key] = urldecode($value);
    }
    return $result;

}

function to_query_configs( $data, $encode = true ) {
    $result = array();
    foreach ( $data as $k => $v )
        $result[] = $k . '=' . ($encode ? urlencode($v) : $v);
    return implode('&', $result);

}

/*
  Display a int
 */

function show_number( $number, $unit = '' ) {
    $str = $number === null ? '' : number_format($number, 0, '.', ',');
    return $str ? $str . $unit : '';

}

function show_decimal( $number ) {
    return $number === null ? '' : number_format($number, 2, '.', ',');

}

function show_money( $number, $currency = null ) {
    if ( $number == 0 || !is_numeric($number) )
        return '';
    return number_format($number, 2, '.', ',') . (is_null($currency) ? '' : " $currency"); //. '' . CURRENTCY;

}

function auto_br( $str ) {
    return str_ireplace("\n", "<br/>", $str);

}

function show_yahoo( $name ) {
    if ( $name == NULL )
        return '';
    return $name;

}

function show_skype( $name ) {
    if ( $name == NULL )
        return '';
    return $name;

}

function show_email( $name, $email = NULL, $limit = 500 ) {
    if ( $name == NULL )
        return '';
    if ( $email == NULL )
        $email = $name;
    return "<a href='mailto:$email'>" . short_title($email, $limit) . "</a>";

}

function show_url( $url, $limit = 500, $target = '_blank' ) {
    if ( $url == '' )
        return '';
    $url = strtolower($url);
    if ( preg_match("#^https?:\/\/#i", $url) ) {
        $title = short_title($url, $limit);
        return "<a href='$url' target='_blank'>$title</a>";
    }

    $title = short_title('http://' . $url, $limit);
    return "<a href='http://$url' target='_blank'>$title</a>";

}

function show_field_content( $text ) {
    $text = nl2br($text);
    return $text;

}

/**
 *
 * @param type $str
 * @param type $length
 * @param type $str_pad
 * @return type
 */
function str_pad_left( $str, $length, $str_pad ) {
    return str_pad($str, $length, $str_pad, STR_PAD_LEFT);

}

/**
 *
 * @param type $str
 * @param type $length
 * @param type $str_pad
 * @return type
 */
function str_pad_right( $str, $length, $str_pad ) {
    return str_pad($str, $length, $str_pad, STR_PAD_RIGHT);

}

/**
 * Random a tring with given length
 * @param type $length
 * @return string
 */
function rand_string( $length ) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

    $size = strlen($chars);
    for ( $i = 0; $i < $length; $i++ ) {
        $str .= $chars[rand(0, $size - 1)];
    }

    return $str;

}

/**
 * Trim a unicode string
 * @param type $str
 * @return type
 */
function unicode_trim( $str ) {
    return preg_replace('/^[\pZ|\pC]+([\PZ|\PC]*)[\pZ|\pC]+$/u', '$1', $str);

}

/**
 * create_username_alias
 * @param type $str
 * @return type
 */
function create_username_alias( $str ) {
    $str = mb_strtolower($str, 'UTF-8');

    $regs = array(
        '/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/u' => 'a',
        '/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/u' => 'e',
        '/(ì|í|ị|ỉ|ĩ)/u' => 'i',
        '/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/u' => 'o',
        '/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/u' => 'u',
        '/(ỳ|ý|ỵ|ỷ|ỹ)/u' => 'y',
        '/đ/u' => 'd'
    );

    foreach ( $regs as $reg => $repl )
        $str = preg_replace($reg, $repl, $str);

    $str = preg_replace('/[^a-z0-9]+/u', '', $str);

    return $str;

}

/**
 * create a username from full name
 * @param type $name
 * @return type
 */
function create_username( $name ) {
    $name = mb_strtolower($name, 'UTF-8');

    $name = unicode_trim($name);

    $words = preg_split('/([\pZ|\pC])+/u', $name);

    $username = create_username_alias(array_pop($words));

    foreach ( $words as $a ) {
        $username = $username . mb_substr(create_username_alias($a), 0, 1);
    }

    return $username;

}