<?php

/*
  Join a field from array
 */

function get_query_field( $items, $field ) {
    $result = array();
    foreach ( $items as $a ) {
        $result[] = $a[$field];
    }
    return $result;

}

/*
  This function will re order a list of a fetchAll
  children of every item will store in 'children' property
 */

function order_from_lists( $items ) {
    /*
      Convert a array which obtains from fetchAll
      to a array which has key is 'ID' field
     */
    if ( !function_exists('_to_indexs') ) {

        function _to_indexs( $items ) {
            $result = array();
            foreach ( $items as $item ) {
                $result[$item['ID']] = $item;
            }
            return $result;

        }

    }

    $items = _to_indexs($items);

    if ( !function_exists('_to_order') ) {

        function _to_order( $items, $parent = 0, $ord = 0, $origin_items ) {
            $result = array();
            foreach ( $items as $item ) {
                $curent_parent = ((!is_numeric($item["parent_id"])) OR !isset($origin_items[$item["parent_id"]])) ? "0" : $item["parent_id"];
                if ( $curent_parent == $parent ) {
                    $item['_deep'] = $ord;
                    $item['_children'] = _to_order($items, $item['ID'], $ord + 1, $origin_items);
                    array_push($result, $item);
                }
            }
            return $result;

        }

    }

    return _to_order($items, 0, 0, $items);

}
