<?php

/**
 * @name user
 * @author Nguyen Duc Minh
 * @date Jun 30, 2012
 */

/**
 * Get user from data
 * @param type $user_id
 * @return type
 */
function get_user( $user_id = null ) {
    if ( is_null($user_id) )
        $user_id = get_user_id();
    if ( is_numeric($user_id) ) {
        return Zone_Base::$Model->fetchRow("SELECT
                `a`.*,
                `b`.`title` as `department`
            FROM `users` as `a`
            LEFT JOIN `departments` as `b`
                ON `a`.`department_id`=`b`.`ID`
            WHERE `a`.`ID`='$user_id'");
    } else if ( is_array($user_id) && count($user_id) > 0 ) {
        $ids = implode(',', $user_id);
        return Zone_Base::$Model->fetchAll("SELECT
                `a`.*,
                `b`.`title` as `department`
            FROM `users` as `a`
            LEFT JOIN `departments` as `b`
                ON `a`.`department_id`=`b`.`ID`
            WHERE `a`.`ID` IN($ids)");
    }
    return null;

}

function get_department( $dep_id ) {
    if ( !$dep_id ) {
        return '';
    }

    $deps = Plugins::getOptions('departments');

    foreach ( $deps as $dep ) {
        if ( $dep['ID'] == $dep_id )
            return $dep['title'];
    }
    return '';

}

function get_position( $id ) {
    if ( !$id ) {
        return '';
    }

    $deps = Plugins::getOptions('positions');

    foreach ( $deps as $dep ) {
        if ( $dep['ID'] == $id )
            return $dep['title'];
    }
    return '';

}

function get_job_title( $id ) {
    if ( !$id ) {
        return '';
    }
    $deps = Plugins::getOptions('position_titles');
    foreach ( $deps as $dep ) {
        if ( $dep['ID'] == $id )
            return $dep['title'];
    }
    return '';

}