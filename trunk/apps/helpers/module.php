<?php

/**
 * Some Ultilities for module
 *
 * @name module
 * @author Nguyen Duc Minh
 * @date Oct 23, 2012
 */

/**
 * Get all record from a table for category type
 *
 * @param String $table_name
 * @param Boolean $cache
 * @return String
 */
function get_options( $table_name, $cache = true ) {
    $key = "OPTIONS_CACHE_{$table_name}";
    if ( !$cache ) {
        $posts = Zone_Base::$Model->fetchAll("SELECT * FROM `$table_name` ORDER BY `title`");
        Zone_Base::setCache($key, $posts);
        return $posts;
    } else {
        if ( Zone_Base::hasCache($key) ) {
            return Zone_Base::getCache($key);
        }
        return get_options($table_name, false);
    }

}

/**
 * Get a record from table for category type
 *
 * @param Numeric $id
 * @param String $table_name
 * @param String $cache
 * @return String
 */
function get_option_item( $id, $table_name, $cache = true ) {
    $items = get_options($table_name, $cache);
    foreach ( $items as $item ) {
        if ( $item['ID'] == $id )
            return $item['title'];
    }
    return null;

}

?>
