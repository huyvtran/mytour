<?php

function get_in_mysql( $data ) {
    if ( $data == "" || $data == null )
        return "('-1')";
    $a = array();
    foreach ( $data as $k ) {
        $a[] = "'" . addslashes(stripslashes($k)) . "'";
    }
    return "(" . implode(',', $a) . ")";

}

function get_enum( $str ) {
    if ( trim($str) === '' )
        return array();
    $result = array();
    $a = explode("\n", $str);
    foreach ( $a as $i ) {
        list($k, $v) = explode('=', $i);
        $result[str_replace("\n", "", $k)] = str_replace("\n", "", $v);
    }
    return $result;

}

function display_enum( $value, $arr ) {
    foreach ( $arr as $k => $title ) {
        if ( $value == $k )
            return $title;
    }
    return $value;

}

function zone_array_remove( $arr, $item, $max = NULL ) {
    $result = array();
    if ( !$max )
        $max = count($arr);
    $x = 0;

    for ( $i = 0; $i < count($arr); $i++ ) {

        if ( ($arr[$i] != $item) || $x >= $max ) {
            $result[] = $arr[$i];
        } else {
            $x++;
        }
    }

    return $result;

}

function Extend( $obj, $expand ) {
    foreach ( $expand as $k => $v ) {
        $obj[$k] = $v;
    }
    return $obj;

}

function array_get_item( $key1, $key2, $value, $array, $def = null ) {
    foreach ( $array as $a ) {
        if ( $a[$key1] == $value ) {
            return $a[$key2];
        }
    }
    return $def;

}
