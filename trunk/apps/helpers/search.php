<?php

/*
  Helper For Search Function.

  @author   ducminh_ajax <ducminh_ajax@yahoo.com.vn>
  @date 05/11/2011
  @update:
 */

/*
  Search for text
 */

function app_search_sql_text( $field, $word ) {
    // $m = explode('+',$word);
    // $a = array();
    // foreach( $m as $i ){
    // $a[] = "$field LIKE '%$i%'";
    // }
    // if( count($a) == 0 ){
    // return '';
    // }
    // return ' ('. implode(' AND ', $a ) .')';
    return "$field LIKE '%$word%'";

}

/*
  Search for a collection ids, use comma to seperate in request
  Example: parent_id=1,2,4
 */

function app_search_sql_group( $field, $params ) {
    if ( $params === '' )
        return '';

    $ids = explode(',', $params);

    $a = array();
    foreach ( $ids as $i ) {
        $a[] = "'$i'";
    }

    return " $field IN (" . implode(',', $a) . ")";

}
