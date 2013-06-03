<?php

function page_simple( $link, $total, $every, $current = 1, $page_range = 5, $bg = false ) {
    $total_page = ceil($total / $every);
    $current = ceil($current);
    $every = ceil($every);
    $page_range = ceil($page_range);

    $link = preg_match("#\?#", $link) ? $link : "$link?";

    $bar = "";
    if ( $total / $every <= 1 )
        return "";

    //if first
    if ( $current > $page_range )
        $bar .= "<a href='{$link}1#page' class='page_first'>" . ($bg ? "&laquo;" : "") . "</a>";
    //show first page
    for ( $i = max(0, $current - $page_range - 1) + 1; $i <= $current - 1; $i++ ) {
        $bar .= "<a href='$link$i#page' class='page_item'>$i</a>";
    }
    //current
    $bar = $bar . "<a class='page_current'>$current</a>";
    //show next page
    for ( $i = $current + 1; $i < min($total_page + 1, $current + $page_range + 1); $i++ )
        $bar .= "<a href='$link$i#page' class='page_item'>$i</a>";
    //if last
    if ( $current + $page_range < $total_page )
        $bar .= "<a href='$link$total_page#page' class='page_last'>" . ($bg ? "&raquo;" : "") . "</a>";

    return "<div class='page-simple'>$bar</div>";

}

/*
  A page bar like: << < || > >>
 */

function page_advance( $link, $total, $range, $current, $html = array() ) {
    $range = max(1, ceil($range));
    $total_page = ceil($total / $range);
    $current = max(0, ceil($current));
    $bar = "";

    $title = str_replace(array("{link}", "{from}", "{to}", "{total}"), array($link, min(($current - 1) * $range + 1, $total), min($current * $range, $total), $total), $html['title']);

    if ( $current >= 3 ) {
        $bar .= str_replace("{link}", $html['first']);
    }

    if ( $current >= 2 ) {
        $bar .= str_replace(array("{link}", "{i}"), array($link, $current - 1), $html['pre']);
    }

    $bar .= $title;

    if ( $current <= $total_page - 2 ) {
        $bar .= str_replace(array("{link}", "{i}"), array($link, $current + 1), $html['last']);
    }

    if ( $current < $total_page ) {
        $bar .= str_replace(array("{link}", "{i}"), array($link, $current + 1), $html['next']);
    }

    return $bar;

}

function page_form( $current, $total, $range, $link = '', $r = 'p' ) {
    $total_page = ceil($total / $range);
    $bar = "<form action='$link' method='get'<div class='page_form'><table cellpadding='1'><tr>";

    if ( $total / $range <= 1 )
        return "";

    $current = min((int) $current, $total_page);
    $current = max($current, 1);

    if ( $current <= 1 ) {
        $bar .= "<td align='center'><a class='page_form_start page_form_start_disable'></a></td>";
        $bar .= "<td align='center'><a class='page_form_pre page_form_pre_disable'></a></td>";
    } else {
        $p = $current - 1;
        $bar .= "<td align='center'><a class='page_form_start' href='{$link}1'></a></td>";
        $bar .= "<td align='center'><a class='page_form_pre' href='$link{$p}'></a></td>";
    }

    $bar .= "<td align='center'><input type='text' size='1' name='$r' class='page_form_input' value='$current'/> / $total_page</td>";
    if ( $current >= $total_page ) {
        $bar .= "<td align='center'><a class='page_form_next page_form_next_disable'></a></td>";
        $bar .= "<td align='center'><a class='page_form_end page_form_end_disable'></a></td>";
    } else {
        $p = $current + 1;
        $bar .= "<td align='center'><a class='page_form_next' href='{$link}$p'></a></td>";
        $bar .= "<td align='center'><a class='page_form_end' href='{$link}{$total_page}'></a></td>";
    }
    return $bar . "</td></tr></table></div></form>";

}

function page_ajax( $link, $total, $range, $current ) {

    if ( $total == 0 ) {
        return "Không có bản ghi nào được tìm thấy";
    }


    $range = max(1, ceil($range));
    $total_page = ceil($total / $range);
    $current = max(1, ceil($current));

    $from = min($total, ($current - 1) * $range + 1);
    $to = min($current * $range, $total);

    $bar = "Hiển thị từ $from đến $to trong tổng số $total kết quả &nbsp;&nbsp;&nbsp;&nbsp;<div class='x-page-move'>";

    if ( $current > 1 ) {
        $bar .= "<a class='x-page-pre' href='$link" . ($current - 1) . "'></a>";
    } else {
        $bar .= "<a class='x-page-pre-empty'></a>";
    }

    if ( $current < $total_page ) {
        $bar .= "<a class='x-page-next' href='$link" . ($current + 1) . "'></a>";
    } else {
        $bar .= "<a class='x-page-next-empty'></a>";
    }

    return $bar . "</div>";

}
