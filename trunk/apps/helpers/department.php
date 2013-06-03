<?php

/**
 * get all departments
 * @return type
 */
function get_departments() {
    $departments = Zone_Base::getCache('DEPARTMENTS');
    if ( is_null($departments) ) {
        $departments = Zone_Base::$Model->fetchAll("SELECT
        `a`.*,
        IFNULL(`b`.`ID`,0) as `parent_id`
        FROM `departments` as `a`
        LEFT JOIN `departments` as `b`
            ON `a`.`parent_id`=`b`.`ID`
        ORDER BY `a`.`parent_id`,`a`.`ord`");
        Zone_Base::setCache('departments', $departments);
    }
    return $departments;

}

/**
 *
 * @param type $title
 * @param type $link
 * @param type $selected
 * @return type
 */
function get_department_button( $title, $link, $selected = 0 ) {
    $lists = _get_department_button(get_departments(), $link, $selected);

    return "<div class='x-options-container'>
        <a class='x-button x-options-hover'>$title<span>&#9660;</span></a>
        <div class='x-options scroll' style='overflow:auto;height:400px'>$lists</div>
    </div>";

}

function _get_department_button( $posts, $link, $selected = 0, $parent_id = 0, $c = 0 ) {
    if ( $c > 10 )
        return '';
    $html = '';
    foreach ( $posts as $k => $a ) {
        if ( $a['parent_id'] == $parent_id ) {
            $prefix = str_repeat(" ─ ─ ", $c);
            $html .= "<a " . ( $selected == $a['ID'] ? "style='font-weight:bold;' " : '' ) . " href='{$link}{$a['ID']}' class='x-options-item'>{$prefix}{$a['title']}</a>";
            unset($posts[$k]);
            $html .= _get_department_button($posts, $link, $selected, $a['ID'], $c + 1);
        }
    }
    return $html;

}

/**
 *
 * @param type $id
 * @return type
 */
function get_subdepartment_ids( $id ) {
    $departments = get_departments();
    $ids = array($id);
    foreach ( $departments as $a ) {
        if ( $a['parent_id'] == $id ) {
            $ids = array_merge($ids, get_subdepartment_ids($a['ID']));
        }
    }
    return $ids;

}

/**
 * Get root department (has parent_id = 0)
 *
 * @param Integer $id
 * @return Array
 */
function get_root_department( $id ) {
    $departments = get_departments();
    $depart = null;
    for ( $i = 0; $i < count($departments); $i++ ) {
        if ( $departments[$i]['ID'] == $id ) {
            $id = $departments[$i]['parent_id'];
            $depart = $departments[$i];
            if ( $id == 0 )
                return $depart;
            $i = 0;
        }
    }
    return $depart;

}

/**
 *
 * @param type $dep_id
 * @param type $deep
 * @param type $s
 * @return type
 */
function get_department_title( $dep_id = 0, $deep = 1, $s = ' / ' ) {
    $deps = get_departments();
    $arr = array();
    foreach ( $deps as $a ) {
        $arr[$a['ID']] = $a;
    }
    $deep = !is_numeric($deep) || $deep < -1 ? count($deps) : $deep;
    $t = array();
    $id = $dep_id;
    while ($deep > 0 && $id > 0) {
        if ( !isset($arr[$id]) )
            break;
        $t[] = $arr[$id]['title'];
        $deep--;
        $id = $arr[$id]['parent_id'];
    }
    return implode($s, array_reverse($t));

}