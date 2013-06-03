<?php

/**
 * @name list
 * @author Nguyen Duc Minh
 * @date Jun 30, 2012
 */
function get_range_page() {
    return max(getInt('rp', (int) Zone_Base::getConfig('user.page', 20)), 10);

}

/**
 *
 * @param type $total_page
 * @return type
 */
function get_current_page( $total_page = null ) {
    $cur_page = max(getInt('p', 1), 1);
    if ( !$total_page )
        return $cur_page;

    return min($cur_page, $total_page);

}

/**
 * Get a menu for tree departments
 *
 * @author Nguyen Duc Minh
 * @date Jun 07, 2102
 *
 * @param type $link
 * @param type $id
 * @param type $depth
 * @param type $items
 * @return string
 */
function get_department_menu( $link, $show_count = true, $id = 0, $depth = 0, $items = null ) {
    if ( $depth == 0 )
        $link .= preg_match('/\?/u', $link) ? '&' : '?';
    $Model = Zone_Base::$Model;
    if ( !$items ) {
        $items = $Model->fetchAll("SELECT
					`a`.*,
                    IF(`b`.`ID`,`b`.`ID`,0) as `parent_id`
                    FROM `departments` as `a`
                    	LEFT JOIN `departments` as `b`
					ON `a`.`parent_id`=`b`.`ID` ORDER BY `ord`");
    }
    $sub_departments = array();
    foreach ( $items as $k => $a ) {
        if ( $a['parent_id'] != $id ) {
            continue;
        }
        // unset($items[$k]);
        $sub_departments[] = $a;
    }

    //if ( $count == 0 && count($sub_departments) == 0 ) {
    //   return '';
    // }
    //class
    $class = $depth == 0 ? "tree-list tree-root" : "tree-list";

    $count = $Model->fetchOne("SELECT COUNT(*)
                        FROM `personnels`
                            WHERE `department_id`='$id' AND `is_draft`='no' AND `job_status`<>'STOP_WORKING'");

    $count_left = $Model->fetchOne("SELECT COUNT(*)
                        FROM `personnels`
                            WHERE `department_id`='$id' AND `is_draft`='no' AND `job_status`='STOP_WORKING'");

    $list = "<div class='tree-list-outer'>
			<div class=\"$class\">";
    foreach ( $sub_departments as $k => $sub ) {
        $subs = get_department_menu($link, $show_count, $sub['ID'], $depth + 1, $items);
        //if ( is_array($subs) ) {
        $count += (int) $subs['count'];
        $count_left += (int) $subs['count_left'];
        $count1 = $subs['count'];
        $count_left1 = $subs['count_left'];
        $subs = $subs['html'];
        // }

        $list .= "<div class='tree-folder-outer'>
				<a class='tree-node' onclick='tree_collapse(this)'></a>
				<div class='tree-folder'>
					<a class='tree-title department-icon' href='{$link}department_id={$sub['ID']}'>
					{$sub['title']} <span style='color:#333;font-weight:normal'>";


        $total = $count1 + $count_left1;

        if ( $show_count && $total > 0 ) {
            $list .= "[";
            if ( $count1 > 0 )
                $list .= "<span title='Nhân sự đang làm việc'>{$count1}</span>";
            if ( $count_left1 > 0 )
                $list .=" <span style='opacity:0.5' title='Nhân sự đã nghỉ việc'>{$count_left1}</span>";
            $list .="]";
        }

        $list .="</a>$subs</div></div>";
    }
    $list .= "</div></div>";

    if ( $depth > 0 )
        return array(
            count => $count,
            count_left => $count_left,
            html => $list
        );
    return $list;

}

/**
 * Get a menu for tree departments with check cache
 *
 * @author Nguyen Duc Minh
 * @date Jun 07, 2102
 *
 * @param String $link
 * @param String $id
 * @param String $depth
 * @param String $items
 * @return String
 */
function get_department_menu_with_cache( $file, $link, $show_count = true ) {
    $data = getCache('departments', $file);
    if ( $data !== null ) {
        return $data;
    }
    $data = get_department_menu($link, $show_count);
    return setCache('departments', $file, $data);

}