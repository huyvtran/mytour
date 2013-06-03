<?php

/**
 * @name MySQL
 * @author Nguyen Duc Minh
 * @date Jul 12, 2012
 */
function mysql_join_group( $a ) {
    $b = array();
    foreach ( $a as $v )
        $b[] = "'" . mysql_escape_string($v) . "'";
    return '(' . implode(',', $b) . ')';

}

function get_model() {

}

?>
