<?php

/*
  Helper Functions.

  @author   ducminh_ajax <ducminh_ajax@yahoo.com.vn>
  @date 05/11/2011
  @update:
 */

require_once ROOT . '/apps/helpers/templates.php';
require_once ROOT . '/apps/helpers/search.php';
require_once ROOT . '/apps/helpers/sms.php';
require_once ROOT . '/apps/helpers/user.php';
require_once ROOT . '/apps/helpers/department.php';
require_once ROOT . '/apps/helpers/list.php';
require_once ROOT . '/apps/helpers/file.php';
require_once ROOT . '/apps/helpers/message.php';
require_once ROOT . '/apps/helpers/hotel.php';

function is_cellphone( $mobile_number ) {
    return $mobile_number != '';

}

function get_user_id() {
    return $_SESSION[USER];

}

function get_user_name() {
    return $_SESSION[USER . '_name'];

}

function is_login() {
    return isset($_SESSION[USER]);

}

function is_remember_login() {
    return isset($_COOKIE[USER . '_username']) && isset($_COOKIE[USER . '_password']);

}

function get_remember_login() {
    return array($_COOKIE[USER . '_username'], $_COOKIE[USER . '_password']);

}

function save_login( $user, $username = NULL, $password = NULL ) {
    //clear_login();
    $_SESSION[USER] = $user['ID'];
    $_SESSION[USER . '_name'] = $user['username'];

    if ( $username && $password ) {
        setcookie(USER . '_username', $username, time() + 3600 * 24 * 100, '/');
        setcookie(USER . '_password', $password, time() + 3600 * 24 * 100, '/');
    } else {
        setcookie(USER . '_username', '', time() - 3600 * 24 * 100, '/');
        setcookie(USER . '_password', '', time() - 3600 * 24 * 100, '/');
    }

}

function clear_login() {
    //remove from session
    unset($_SESSION[USER]);

    //remove from cookie
    setcookie(USER . '_username', '', time() - 3600 * 24 * 100, '/');
    setcookie(USER . '_password', '', time() - 3600 * 24 * 100, '/');

}

function get_name_of_day( $index ) {
    $data = array("Chủ Nhật", "Thứ Hai", "Thứ Ba", "Thứ Tư", "Thứ Năm", "Thứ Sáu", "Thứ Bảy");
    return $data[$index];

}

function error( $message ) {
    return "<div class='x-error'>$message</div>";

}

function success( $message ) {
    return "<div class='x-success'>$message</div>";

}

function content_format( $text, $full = false ) {
    $text = preg_replace("#\\n#is", "<br/>", $text);
    //$text = preg_replace("#^ #is","&nbsp;",$text);

    $icons = array(
        array(syntax => '&gt;:)', title => 'Devil', icon => '1.gif'),
        array(syntax => ':((', title => 'Crying', icon => '2.gif'),
        array(syntax => ';;)', title => 'batting eyelashes', icon => '3.gif'),
        array(syntax => '&gt;:D&lt;', title => 'big hug', icon => '4.gif'),
        array(syntax => '&lt;):)', title => 'cowboy', icon => '5.gif'),
        array(syntax => ':D', title => 'big grin', icon => '6.gif'),
        array(syntax => ':-/', title => 'confused', icon => '7.gif'),
        array(syntax => ':x', title => 'love struck', icon => '8.gif'),
        array(syntax => '&gt;:P', title => 'phbbbbt', icon => '10.gif'),
        array(syntax => ':-*', title => 'kiss', icon => '11.gif'),
        array(syntax => '=((', title => 'broken heart', icon => '12.gif'),
        array(syntax => ':-O', title => 'surprise', icon => '13.gif'),
        array(syntax => '~X(', title => 'at wits\' end', icon => '14.gif'),
        array(syntax => ':&gt;', title => 'smug', icon => '15.gif'),
        array(syntax => 'B-)', title => 'cool', icon => '16.gif'),
        array(syntax => ':-SS', title => 'nail biting', icon => '17.gif'),
        array(syntax => '#:-S', title => 'whew!', icon => '18.gif'),
        array(syntax => ':))', title => 'laughing', icon => '19.gif'),
        array(syntax => ':(', title => 'sad', icon => '20.gif'),
        array(syntax => '/:)', title => 'raised eyebrows', icon => '21.gif'),
        array(syntax => '(:|', title => 'yawn', icon => '22.gif'),
        array(syntax => ':)]', title => 'on the phone', icon => '23.gif'),
        array(syntax => '=))', title => 'rolling on the floor', icon => '24.gif'),
        array(syntax => 'O:-)', title => 'angel', icon => '25.gif'),
        array(syntax => ':-B', title => 'nerd', icon => '26.gif'),
        array(syntax => '=;', title => 'talk to the hand', icon => '27.gif'),
        array(syntax => '8-|', title => 'rolling eyes', icon => '29.gif'),
        array(syntax => 'L-)', title => 'loser', icon => '30.gif'),
        array(syntax => ':-&amp;', title => 'sick', icon => '31.gif'),
        array(syntax => ':-$', title => 'don\'t tell anyone', icon => '32.gif'),
        array(syntax => '[-(', title => 'no talking', icon => '33.gif'),
        array(syntax => ':O)', title => 'clown', icon => '34.gif'),
        array(syntax => '8-}', title => 'silly', icon => '35.gif'),
        array(syntax => '&lt;:-P', title => 'party', icon => '36.gif'),
        array(syntax => ':|', title => 'straight face', icon => '37.gif'),
        array(syntax => '=P~', title => 'drooling', icon => '38.gif'),
        array(syntax => ':-?', title => 'thinking', icon => '39.gif'),
        array(syntax => '#-o', title => 'd\'oh', icon => '40.gif'),
        array(syntax => '=D&gt;', title => 'applause', icon => '41.gif'),
        array(syntax => ':-S', title => 'worried', icon => '42.gif'),
        array(syntax => '@-)', title => 'hypnotized', icon => '43.gif'),
        array(syntax => ':^o', title => 'liar', icon => '44.gif'),
        array(syntax => ':-w', title => 'waiting', icon => '45.gif'),
        array(syntax => ':-&lt;', title => 'sigh', icon => '46.gif'),
        array(syntax => ':P', title => 'tongue', icon => '47.gif'),
        array(syntax => ';)', title => 'winking', icon => '48.gif'),
        array(syntax => ':)', title => 'happy', icon => '100.gif'),
        array(syntax => ':-c', title => 'call me', icon => '101.gif'),
        array(syntax => 'X(', title => 'angry', icon => '102.gif'),
        array(syntax => ':-h', title => 'wave', icon => '103.gif'),
        array(syntax => '8-&gt;', title => 'day dreaming', icon => '105.gif')
    );

    //if( !$full )
    //return $text;
    //loadClass('BBCode');
    //$bbcode = new BBCode();
    //$text = $bbcode->parse($text);

    foreach ( $icons as $a ) {
        $text = str_replace($a['syntax'], "<img src='" . baseUrl() . "/style/css/chat/emo/{$a['icon']}' title='{$a['title']}'/>", $text);
    }
    return $text;

}

function list_emo( $id ) {
    $icons = array(
        array(syntax => '&gt;:)', title => 'Devil', icon => '1.gif'),
        array(syntax => ':((', title => 'Crying', icon => '2.gif'),
        array(syntax => ';;)', title => 'batting eyelashes', icon => '3.gif'),
        array(syntax => '&gt;:D&lt;', title => 'big hug', icon => '4.gif'),
        array(syntax => '&lt;):)', title => 'cowboy', icon => '5.gif'),
        array(syntax => ':D', title => 'big grin', icon => '6.gif'),
        array(syntax => ':-/', title => 'confused', icon => '7.gif'),
        array(syntax => ':x', title => 'love struck', icon => '8.gif'),
        array(syntax => '&gt;:P', title => 'phbbbbt', icon => '10.gif'),
        array(syntax => ':-*', title => 'kiss', icon => '11.gif'),
        array(syntax => '=((', title => 'broken heart', icon => '12.gif'),
        array(syntax => ':-O', title => 'surprise', icon => '13.gif'),
        array(syntax => '~X(', title => 'at wits\' end', icon => '14.gif'),
        array(syntax => ':&gt;', title => 'smug', icon => '15.gif'),
        array(syntax => 'B-)', title => 'cool', icon => '16.gif'),
        array(syntax => ':-SS', title => 'nail biting', icon => '17.gif'),
        array(syntax => '#:-S', title => 'whew!', icon => '18.gif'),
        array(syntax => ':))', title => 'laughing', icon => '19.gif'),
        array(syntax => ':(', title => 'sad', icon => '20.gif'),
        array(syntax => '/:)', title => 'raised eyebrows', icon => '21.gif'),
        array(syntax => '(:|', title => 'yawn', icon => '22.gif'),
        array(syntax => ':)]', title => 'on the phone', icon => '23.gif'),
        array(syntax => '=))', title => 'rolling on the floor', icon => '24.gif'),
        array(syntax => 'O:-)', title => 'angel', icon => '25.gif'),
        array(syntax => ':-B', title => 'nerd', icon => '26.gif'),
        array(syntax => '=;', title => 'talk to the hand', icon => '27.gif'),
        array(syntax => '8-|', title => 'rolling eyes', icon => '29.gif'),
        array(syntax => 'L-)', title => 'loser', icon => '30.gif'),
        array(syntax => ':-&amp;', title => 'sick', icon => '31.gif'),
        array(syntax => ':-$', title => 'don\'t tell anyone', icon => '32.gif'),
        array(syntax => '[-(', title => 'no talking', icon => '33.gif'),
        array(syntax => ':O)', title => 'clown', icon => '34.gif'),
        array(syntax => '8-}', title => 'silly', icon => '35.gif'),
        array(syntax => '&lt;:-P', title => 'party', icon => '36.gif'),
        array(syntax => ':|', title => 'straight face', icon => '37.gif'),
        array(syntax => '=P~', title => 'drooling', icon => '38.gif'),
        array(syntax => ':-?', title => 'thinking', icon => '39.gif'),
        array(syntax => '#-o', title => 'd\'oh', icon => '40.gif'),
        array(syntax => '=D&gt;', title => 'applause', icon => '41.gif'),
        array(syntax => ':-S', title => 'worried', icon => '42.gif'),
        array(syntax => '@-)', title => 'hypnotized', icon => '43.gif'),
        array(syntax => ':^o', title => 'liar', icon => '44.gif'),
        array(syntax => ':-w', title => 'waiting', icon => '45.gif'),
        array(syntax => ':-&lt;', title => 'sigh', icon => '46.gif'),
        array(syntax => ':P', title => 'tongue', icon => '47.gif'),
        array(syntax => ';)', title => 'winking', icon => '48.gif'),
        array(syntax => ':)', title => 'happy', icon => '100.gif'),
        array(syntax => ':-c', title => 'call me', icon => '101.gif'),
        array(syntax => 'X(', title => 'angry', icon => '102.gif'),
        array(syntax => ':-h', title => 'wave', icon => '103.gif'),
        array(syntax => '8-&gt;', title => 'day dreaming', icon => '105.gif')
    );
    $str = '';
    foreach ( $icons as $icon ) {
        $str .= "<img style='margin:3px' src='" . BASE_URL . "/style/css/chat/emo/{$icon['icon']}' onclick='add_emo(\"$id\",\"{$icon['syntax']}\")'/>";
    }
    return $str;

}

function show_order_link( $title, $field, $link, $vars, $groups = array() ) {
    $sign = preg_match("#\?#is", $link) ? '&' : '?';
    //off this feature now
    $groups = array();

    $vars['order_by'] = $field;
    if ( get('order_by') == $field ) {
        $order_type = get('order_type') != 'desc' ? 'desc' : 'asc';
        $a = "";
    } else {
        $order_type = 'asc';
        $a = " x-order-hidden";
    }

    $sub = "";
    if ( count($groups) > 0 ) {
        $a .=' list-filter';

        $ids = isset($vars[$field]) ? explode(',', $vars[$field]) : array();

        $has_filter = false;
        foreach ( $groups as $c ) {
            $checked = in_array($c['ID'], $ids) ? ' checked' : '';
            if ( $checked )
                $has_filter = true;
            $vars_select = $vars;
            $ids_select = $ids;
            $ids_select[] = $c['ID'];

            if ( count($ids_select) > 0 ) {
                $vars_select[$field] = implode(',', $ids_select);
            } else {
                unset($vars_select[$field]);
            }
            $url_select = $link . $sign . to_query_configs($vars_select, false);

            $vars_noselect = $vars;
            $ids_noselect = $ids;

            foreach ( $ids_noselect as $j => $v )
                if ( $v == $c['ID'] ) {
                    unset($ids_noselect[$j]);
                }

            if ( count($ids_noselect) > 0 ) {
                $vars_noselect[$field] = implode(',', $ids_noselect);
            } else {
                unset($vars_noselect[$field]);
            }
            $url_noselect = $link . $sign . to_query_configs($vars_noselect, false);

            $js = " onchange=\"(function($){ location.href= this.checked ? '$url_select':'$url_noselect' }).call(this,Owl)\" ";
            $sub .="<tr><td>
                <input type='checkbox' $js value='{$c['ID']}'$checked/>
                </td><td>
                    {$c['title']}
                </td></tr>";
        }
        $sub = "<div class='links'><table>$sub</table><div style='clear:both'></div></div>";
    }

    if ( $order_type == 'desc' ) {
        $vars['order_type'] = $order_type;
    } else {
        unset($vars['order_type']);
    }

    $query = to_query_configs($vars, false);
    if ( $query != "" )
        $query = $sign . "$query";
    if ( $has_filter )
        $a .=' has-filter';
    _e("<div class='x-order x-$order_type{$a}'>
        <a href='$link{$query}'>$title</a>
        $sub
      </div>");

}

function get_status( $text ) {
    switch ($text) {
        case 'inactive':
            return 'Tạm dừng';
        case 'complete':
            return 'Hoàn thành';
        case 'cancel':
            return 'Hủy bỏ';
        case 'active':
            return 'Thực hiện';
        default:
            return $text;
    }

}

function get_priority( $i ) {
    $i = (string) $i;
    switch ($i) {
        case '0':
            return 'Thấp';
        case '2':
            return 'Cao';
        default:
            return 'Bình thường';
    }

}

function get_priority_icon( $i ) {
    $i = (string) $i;
    switch ($i) {
        case '0':
            return "<img src='" . baseUrl() . "/style/images/low.gif' title='Mức ưu tiên thấp'/>";
        case '2':
            return "<img src='" . baseUrl() . "/style/images/hight.gif' title='Mức ưu tiên cao'/>";
        default:
            return '';
    }

}

function show_thumb( $file, $path ) {
    //format size
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $unit = 0;
    $size = $file['size'] * 1024 * 1024;
    for ( $i = 0; $i < count($units); $i++ ) {
        if ( $size > 1024 ) {
            $size /= 1024;
        } else {
            $unit = $i;
            break;
        }
    }

    $size = round($size, 2) . " <small>{$units[$unit]}</small>";

    $view = '';
    _e("<table cellpadding='5'><tr><td>");
    if ( in_array(strtolower($file['type']), array('image/png', 'image/x-png', 'image/jpg', 'image/jpeg', 'image/gif')) ) {
        _e("<a href='$path&type=view' target='_blank'><img src='$path&type=icon' style='padding:1px;border:1px solid #bbb;max-width:60px'/></a>");
        $view = "<a href='$path&type=view' style='text-decoration:underline;padding:5px' target='_blank'>Xem</a>";
    } else {
        // _e("<img src='" . baseUrl() . "/files/static/icon.gif'/>");
        $filetype = pathinfo($file['filename'], PATHINFO_EXTENSION);
        _e("<span class='icon-file-thumb icon-file-thumb-{$filetype}'></span>");
    }
    _e("</td><td>
		<b>{$file['filename']}</b>
		<div>
			{$size}
			$view <a href='$path' style='text-decoration:underline;padding:5px'>Tải xuống</a>
		</div>
	</td></tr></table>");

}

function show_smart_time( $date, $title = null ) {
    $time = strtotime($date);
    $ins = (time() - $time);
    $short_time = date('h:i', $time);
    $title = is_null($title) ? date(DATETIME_FORMAT, $time) : $title;

    if ( $ins < 0 )
        return $date;
    if ( $ins < 60 ) {
        return "<span title='$title'>$short_time ( cách đây $ins giây )</span>";
    }
    if ( $ins / 60 < 60 ) {
        return "<span title='$title'>$short_time ( cách đây " . round($ins / 60) . " phút )</span>";
    }

    if ( $ins / 3600 <= 24 ) {
        return "<span title='$title'>$short_time ( cách đây " . round($ins / 3600) . " tiếng )</span>";
    }

    if ( $ins / (24 * 3600) <= 7 ) {
        return "<span title='$title'>$short_time ( cách đây " . round($ins / (24 * 3600)) . " ngày )</span>";
    }
    return $title;

}

function vn_day( $time ) {
    $a = array("Thứ hai", "Thứ ba", "Thứ tư", "Thứ năm", "Thứ sáu", "Thứ bảy", "Chủ nhật");
    $n = date("N", $time) - 1;
    return $a[$n];

}

/*
  return a list of options tag to choose department
 */

function select_options( $items, $name = 'title', $selected_id = 0, $disable_id = -1 ) {
    foreach ( $items as $k => $item ) {
        $t = true;
        for ( $i = 0; $i < count($items); $i++ ) {
            if ( $item['parent_id'] == $items[$i]['ID'] ) {
                $t = false;
            }
        }
        if ( $t ) {
            $items[$k]['parent_id'] = '0';
        }
    }
    return
            '<option></option>'
            . select_recruise($items, $name, $selected_id, 0, $disable_id, 0);

}

function select_recruise( $items, $name = 'title', $selected_id, $parent_id = '0', $disable_id = -1, $c = 0, $always_disabled = false ) {
    //limit deep
    if ( $c > 50 )
        return '';
    $html = '';
    foreach ( $items as $k => $item ) {
        if ( $item['parent_id'] == $parent_id ) {
            $disabled = $disable_id == $item['ID'] ? ' disabled' : '';
            $selected = $selected_id == $item['ID'] ? ' selected' : '';

            $always_disabled1 = $always_disabled || $disabled != '' ? true : false;

            $html .= "<option value='{$item['ID']}'{$disabled}{$selected}>" . str_repeat('--', $c) . ' ' . $item[$name] . '</option>';
            unset($items[$k]);
            $html .= select_recruise($items, $name, $selected_id, $item['ID'], $disable_id = -1, $c + 1, $always_disabled1);
        }
    }
    return $html;

}

function get_user_link( $user, $name = NULL, $deleted = 'no' ) {
    if ( is_array($user) && !array_key_exists('is_deleted', $user) ) {
        $user['is_deleted'] = 'no';
    }

    if ( is_array($user) ) {
        return $user['is_deleted'] == 'no' ? "<a href='#User/Info?ID={$user['ID']}' class='userlink'>{$user['fullname']}</a>" : "<a href='#User/Info?ID={$user['ID']}' class='user-deleted' title='Người dùng đã bị xóa'>{$user['fullname']}</a>";
    } else {
        return $deleted == 'no' ? "<a href='#User/Info?ID=$user' class='userlink'>$name</a>" : "<a href='#User/Info?ID=$user' class='user-deleted' title='Người dùng đã bị xóa'>$name</a>";
    }

}

function get_user_link_online( $user ) {
    if ( is_array($user) && !array_key_exists('is_deleted', $user) ) {
        $user['is_deleted'] = 'no';
    }

    $s = '';
    $uid = 0;
    if ( is_array($user) ) {
        $uid = $user['ID'];
        $name = $user['fullname'];
        //if($uid == get_user_id())
        // $name = 'tôi';
        $s = $user['is_deleted'] == 'no' ? "<a href='#User/Info?ID={$user['ID']}' class='userlink'>{$name}</a>" : "<a href='#User/Info?ID={$user['ID']}' class='user-deleted' title='Người dùng đã bị xóa'>{$name}</a>";
    }
    return "<span class='chaticon-{$uid}'><span class='offline'></span> $s</span>";

}

?>