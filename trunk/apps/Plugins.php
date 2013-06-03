<?php

/**
 *
 * Enter description here ...
 * @author Nguyen Duc Minh
 *
 */
class Plugins extends Zone_Plugin {

    public static $CACHE_DB = array();

    public static function updateMememory( $file_size ) {
        $user_id = get_user_id();

        self::$Model->update('users', array(
            memory_upload => new Model_Expr("`memory_upload` + $file_size ")
                ), "`ID`='$user_id'");

    }

    public static function sendNotice( $data ) {
        self::$Model->insert('notices', array(
            title => $data['title'],
            user_id => $data['user_id'],
            created_by_id => array_key_exists('from_id', $data) ? $data['from_id'] : get_user_id(),
            url => $data['url'],
            content => $data['msg'],
            'date' => new Model_Expr('NOW()')
        ));

    }

    public static function getDepartmentMenu( $link, $current_id ) {
        $posts = self::$Model->fetchAll('departments', "SELECT * FROM departments ORDER BY parent_id, ord");
        $result = "";
        $title = translate('department');

        foreach ( $posts as $a ) {
            if ( $a['ID'] == $current_id )
                $title = $a['title'];
            $result .= "<a " . ($a['ID'] == $current_id ? ' style="font-weight:bold!important" ' : '') . "href='$link{$a['ID']}' class='x-options-item'>{$a['title']}</a>";
        }

        return "<div class='x-options-container'>
			<a class='x-button'>$title <span>&#9660;</span></a>
			<div class='x-options'>
			$result
			</div></div>";

    }

    public static function log() {
        self::$Model->insert('logs', array(
            date => new Model_Expr('NOW()'),
            ip => $_SERVER['REMOTE_ADDR'],
            browser => $_SERVER['HTTP_USER_AGENT'],
            user_id => get_user_id() ? get_user_id() : 0,
            username => get_user_id() ? get_user_name() : ''
        ));

    }

    public static function logActions($log_type, $desc){
        self::$Model->insert('log_actions', array(
            username => get_user_id() ? get_user_name() : '',
            user_id => get_user_id() ? get_user_id() : 0,
            ip => $_SERVER['REMOTE_ADDR'],
            time => new Model_Expr('NOW()'),
            log_type => $log_type,
            hotel_id => get_hotel_id(),
            desc => $desc
        ));        
    }
    
    public static function getCountries() {
        return self::$Model->fetchAll("SELECT * FROM `locations`
            WHERE `type`='1' ORDER BY `title`");

    }

    public static function getDefaultStates() {
        return self::$Model->fetchAll("SELECT * FROM `locations`
            WHERE `parent_id`='1' ORDER BY `title`");

    }

    /*
     * List all items from a table category
     * This function auto cache
     */

    public static function getOptions( $tb_name, $order = null ) {
        if ( array_key_exists($tb_name, self::$CACHE_DB) ) {
            return self::$CACHE_DB[$tb_name];
        }

        if ( $order ) {
            $order = "ORDER BY `$order`";
        }

        return self::$Model->fetchAll("SELECT * FROM `$tb_name` $order");

    }

    public static function getItem( $tb_name, $title, $id ) {
        return self::$Model->fetchOne("SELECT `$title`
               FROM `$tb_name` WHERE `ID`='$id'");

    }

    public static function getDepartmentIds( $id ) {
        $departments = Plugins::getOptions("departments");
        $ids = array($id);
        foreach ( $departments as $a ) {
            if ( $a['parent_id'] == $id ) {
                $ids = array_merge($ids, Plugins::getDepartmentIds($a['ID']));
            }
        }
        return $ids;

    }

}
