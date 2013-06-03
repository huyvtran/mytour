<?php

function tpl_button_add( $title = 'Cập nhật' ) {
    return '<button type="submit" class="x-button x-save">' . $title . '</button>';

}

function tpl_button_search() {
    return '<button type="submit" class="x-button x-save">'
            . translate('default.button.search')
            . '</button>';

}

function tpl_button_reset() {
    return '<button type="reset" class="x-button x-fresh">Làm lại</button>';

}

function tpl_button_back( $title = 'Hủy bỏ', $link ) {
    return '<a class="x-button x-fresh" href="' . $link . '">' . $title . '</a>';

}

function tpl_button_cancel( $link = '' ) {
    return '<a href="' . $link . '" class="x-button x-fresh">'
            . translate('default.button.cancel')
            . '</a>';

}

function tpl_button_close() {
    return '<a onclick="$(this).parent(\'.lightbox\').remove()" class="x-button x-fresh">Hủy bỏ</a>';

}

function tpl_button_disabled( $title ) {
    return '<a class="x-button x-button-disabled">' . $title . '</a>';

}

function tpl_input_hidden( $name, $value ) {
    return '<input type="hidden" value="' . $value . '" name="' . $name . '"/>';

}

function tpl_input_code( $name, $value = '' ) {
    return '<input type="text" value="' . $value . '" name="' . $name . '" class="x-text x-code-text"/>';

}

function tpl_input_number( $name, $value = '' ) {
    return '<input type="number" value="' . $value . '" name="' . $name . '" class="x-text x-number-text"/>';

}

function tpl_input_phone( $name, $value = '' ) {
    return '<input type="text" value="' . $value . '" name="' . $name . '" class="x-text" style="width:150px"/>';

}

function tpl_input_fax( $name, $value = '' ) {
    return '<input type="text" value="' . $value . '" name="' . $name . '" class="x-text" style="width:150px"/>';

}

function tpl_input_price( $name, $value = '' ) {
    return '<input type="text" value="' . $value . '" name="' . $name . '" class="x-text x-small-text" style="width:150px"/>';

}

function tpl_input_money( $name, $value = '' ) {
    return '<input type="number" min="0" value="' . $value . '" name="' . $name . '" class="x-text x-money-text"/>';

}

function tpl_input_percent( $name, $value = '' ) {
    return '<input class="x-text x-status" type="text" name="' . $name . '" onclick="status_picker(this)" value="' . $value . '"/> %';

}

function tpl_input_date( $name, $value = '', $att = '' ) {
    return '<input type="text" name="' . $name . '" value="' . show_date($value) . '" onclick="date_picker(this,{format: \'d/m/Y\' })" class="x-text x-date"' . $att . '/>';

}

function tpl_input_datetime( $name, $value = '' ) {
    $date = "";
    $time = "";
    $fulldate = "";
    $id = 'time-picker-' . getId();
    if ( $value ) {
        $fulldate = show_date('d/m/Y H:i:s', $value);
        $date = show_date($value);
        $time = show_date('H:i', $value);
    }

    return "<input id='$id' type='hidden' name='$name' value='$fulldate'/>
        <input id='time_$id' type='text' value='$time' onclick=\"time_picker(this)\" class='x-text x-time' parent-date='$id'/>
        <input id='date_$id' type='text' value='$date' onclick=\"date_picker(this,{format: 'd/m/Y' })\" class='x-text x-date' parent-date='$id'/>";

}

function tpl_input_time( $name, $value = '' ) {
    return "<input type='text' autocomplete='off' name='$name' value='$value' onclick='time_picker(this)' class='x-text x-time'/>";

}

function tpl_input_duration( $name, $value = '' ) {
    return "<input type='text' name='{$name}' value='{$value}' class='x-text x-duration'/>";

}

function tpl_input_address( $name, $value = '' ) {
    return '<input type="text" value="' . $value . '" name="' . $name . '" class="x-text" style="width:400px"/>';

}

function tpl_input_email( $name, $value = '' ) {
    return '<input type="text" value="' . $value . '" name="' . $name . '" class="x-text x-email-text"/>';

}

function tpl_input_password( $name, $value = '' ) {
    return '<input type="password" value="' . $value . '" name="' . $name . '" class="x-text x-email-text"/>';

}

function tpl_input_url( $name, $value = '' ) {
    return '<input type="text" value="' . $value . '" name="' . $name . '" class="x-text x-url-text"/>';

}

function tpl_input_normal( $name, $value = '', $att = '' ) {
    return '<input type="text" value="' . $value . '" name="' . $name . '" class="x-text x-normal-text"' . $att . '/>';

}

function tpl_input_big( $name, $value = '' ) {
    return '<input type="text" value="' . $value . '" name="' . $name . '" class="x-text x-big-text"/>';

}

function tpl_textarea( $name, $value = '' ) {
    return '<textarea type="text" name="' . $name . '" class="x-textarea">' . $value . '</textarea>';

}

function tpl_textarea_small( $name, $value = '' ) {
    return '<textarea type="text" name="' . $name . '" class="x-small-textarea">' . $value . '</textarea>';

}

function tpl_textarea_desc( $name, $value = '', $att = '' ) {
    return '<textarea type="text" ' . $att . ' name="' . $name . '" class="x-textarea-desc">' . $value . '</textarea>';

}

function tpl_comment( $name, $value = '' ) {
    return '<textarea type="text" name="' . $name . '" class="x-textarea x-editor">' . $value . '</textarea>';

}

function tpl_editor( $name, $value = '' ) {
    require_once ROOT . '/style/plugins/editor/Editor.php';
    return Editor::create(array(
                name => $name,
                value => $value,
                width => '100%'
            ));

}

function tpl_full_editor( $name, $value = '' ) {
    return '<textarea type="text" name="' . $name . '" class="x-textarea x-full-editor">' . $value . '</textarea>';

}

function tpl_input_small( $name, $value = '', $att = '' ) {
    return '<input type="text" value="' . $value . '" name="' . $name . '" class="x-text x-small-text"' . $att . '/>';

}

function tpl_select( $name, $field_id = 'ID', $field_label = NULL, $data, $selected = NULL, $has_none = false ) {
    $str = '<select class="x-select" name="' . $name . '">';
    if ( $has_none ) {
        $str .= "<option value=''></option>";
    }
    foreach ( $data as $a ) {
        $str .= '<option value="' . $a[$field_id] . '"' . ($selected != NULL && $selected == $a[$field_id] ? ' selected' : '') . '>' . $a[$field_label] . '</option>';
    }
    $str .= '</select>';
    return $str;

}

function tpl_options( $items, $selected = NULL ) {
    $html = '';
    foreach ( $items as $k => $v ) {
        $sl = $k == $selected ? ' selected' : '';
        $html .= "\n<option value='{$k}'{$sl}>{$v}</option>";
    }
    return $html;

}

function tpl_checkbox( $name, $value, $disabled = NULL, $checked = NULL, $label = NULL, $att = "" ) {
    $checked = $checked ? ' checked' : '';
    $disabled = $disabled ? ' disabled' : '';
    $att = $att ? " $att" : "";
    if ( $label == NULL ) {
        return '<input type="checkbox" value="' . $value . '" name="' . $name . '"' . $att . $checked . $disabled . '/>';
    }

    return '<label>
			<input type="checkbox" value="' . $value . '" name="' . $name . '"' . $att . $checked . $disabled . '/>
			' . $label . '
		</label>';

}

function tpl_checkbox_item( $id, $disabled = '' ) {
    return '<input name="ID[]" value="' . $id . '" type="checkbox" class="x-checkbox x-checkall" on="$(this).each(function(){ if( this.checked ) $(this.parentNode.parentNode).addClass(\'row-select\'); else $(this.parentNode.parentNode).removeClass(\'row-select\'); })" $disabled/>';

}

function tpl_checkbox_priority( $name, $value = 1 ) {
    return '<input type="radio" name="' . $name . '" value="0"' . ($value == 0 ? ' checked' : '') . '/> <span class="p-low">Thấp</span>
		<input type="radio" name="' . $name . '" value="1"' . ($value == 1 ? ' checked' : '') . '> <span class="p-normal">Bình thường</span>
		<input type="radio" name="' . $name . '" value="2"' . ($value == 2 ? ' checked' : '') . '/> <span class="p-hight">Cao</span>';

}

function tpl_upload_files( $params = array() ) {
    return "<div class='x-files' url='{$params['url']}'></div>";

}

function tpl_upload_old_files( $name, $files ) {
    $s = '';
    foreach ( $files as $f ) {
        $smart_size = smart_file_size($f['size']);
        $s.="<div class='x-file-info'>
            <input type='hidden' name='{$name}' value='{$f['ID']}' disabled='disabled'/>
            <input checked='checked' type='checkbox' onclick=\"$(this).pre(0).set('disabled',this.checked)\" />
                {$f['filename']} [$smart_size]
            </div>";
    }
    return $s;

}

function tpl_upload( $name = 'cache_file', $path = 'upload' ) {
    $url = baseUrl() . "/User/File/Upload?name=$name&path=$path";
    return "<div><div class='x-file' url='$url'>Chọn file</div></div>";

}

function tpl_uploads( $name = 'cache_files[]', $path = 'upload', $options = array() ) {
    $files = $options['files'];
    $link = $options['link'];

    $s = "";
    if ( is_array($files) ) {
        foreach ( $files as $file ) {
            $size = smart_file_size($file['size']);
            $s .="<div class='x-file-info'>
			<input disabled type='hidden' name='delete_$name' value='{$file['ID']}'/>
			<input checked type='checkbox' onclick=\"Owl(this).pre('input').set('disabled',this.checked)\"/>
			<a href='$link{$file['ID']}' target='_blank'>
                {$file['filename']} $size</a></div>";
        }
    }

    $url = baseUrl() . "/User/File/Upload?name=$name&path=$path";
    return "$s<div class='x-files' url='$url'>Chọn file</div>";

}

function tpl_search_form( $link_form, $link_adv = null, $placeholder = 'Tìm kiếm' ) {
    $adv = $link_adv ?
            "<a href='$link_adv' title='Tìm kiếm nâng cao' class='adv-search'></a>" : "<a class='adv-search' style='opacity:0.3'></a>";
    return "<div class='x-pop'>
			<form action='$link_form' onsubmit='return ajax_form(this,true)' method='post'>
			<div class='simple-search'>
                $adv
				<input type='text' name='s' value='" . htmlentities(stripslashes(get('s')), ENT_QUOTES, "UTF-8") . "' class='s' autocomplete='off' spellcheck='false' placeholder='$placeholder'/>
			</div>
			</form>
		</div>";

}

function tpl_search_select( $name, $field_id, $field_label, $data ) {
    $str = '<div class="search-select">';
    foreach ( $data as $a ) {
        $str .= '<label><input type="checkbox" name="' . $name . '" value="' . $a[$field_id] . '"/>' . $a[$field_label] . '</label>';
    }
    $str .= '</div>';
    return $str;

}

/*
  Auto
 */

function tpl_select_users( $name, $title = '', $users = array() ) {
    $s = '<div class="x-select-users" x-name="' . $name . '" x-title="' . $title . '">';
    foreach ( $users as $u ) {
        $s .= '<span class="item">
				<input type="hidden" name="' . $name . '" value="' . $u['ID'] . '">' . get_user_link($u) . ' <span class="x" onclick="$(this.parentNode).remove()"></span>
			</span>';
    }
    $s .= '</div>';
    return $s;

}

function tpl_select_user( $name, $title = '', $user = NULL ) {
    $s = '<div class="x-select-user" x-name="' . $name . '" x-title="' . $title . '">';
    if ( $user ) {
        $s .= '<span class="item">
				<input type="hidden" name="' . $name . '" value="' . $user['ID'] . '">' . get_user_link($user) . ' <span class="x" onclick="$(this.parentNode).remove()"></span>
			</span>';
    }
    $s .= '</div>';
    return $s;

}

function tpl_auto_select( $options ) {
    $title = $options['title'];
    $link = $options['link'];

    $s = '<div class="x-auto" ' . $options['style'] . ' onclick="this.getElementsByTagName(\'input\')[0].focus()">';
    if ( $options['items'] ) {
        foreach ( $options['items'] as $a ) {
            if ( !$a )
                continue;
            $s .= '<div class="x-auto-current">';
            $s .= '<input type="hidden" name="' . $options['name'] . '" value="' . $a['ID'] . '"/>';
            $s .= '<a class="l" target="_blank" href="' . $link . $a['ID'] . '">' . $a[$title] . '</a>';
            $s .= '<a class="x" onclick="Owl(this).parent(\'.x-auto-current\').remove();return false"></a>';
            $s .= '</div>';
        }
    }

    $valid = array(
        type => $options['type'],
        url => $options['url'],
        data => array(
            name => $options['name'],
            link => $link,
            title => $title
        )
    );

    $attrs = array();
    $attrs[] = "valid='[" . json_encode($valid) . "]'";
    if ( array_key_exists('callback', $options) ) {
        $attrs[] = 'callback="' . $options['callback'] . '"';
    }

    $attrs = implode(' ', $attrs);

    $s .= "<input type='text' $attrs/>";
    $s .= '</div><span class="x-auto-icon"></span>';
    return $s;

}

function tpl_yahoo( $s ) {
    return $s;

}

function tpl_skype( $s ) {
    return $s;

}

/*
  icon-links
 */

function tpl_icon_quickview( $url ) {
    return "<a onclick=\"load_frame('$url',{ title: 'Xem nhanh', resize: {selector: '.lightbox-content'},mask: true })\" title='Xem nhanh' class='x-quickview'></a>";

}

function tpl_tags( $link, $tags ) {
    $m = array();
    if ( count($tags) == 0 )
        return '';
    foreach ( $tags as $a ) {
        $m[] = "<a href=\"#Contact?label_id={$a['ID']}\">{$a['label']}</a>";
    }
    return implode(', ', $m);

}

function tpl_add_property( $options = array() ) {
    $id = getId();
    $list = "";
    foreach ( (array) $options['properties'] as $a ) {
        $list .= "<div class='add-property'>
				<input type='hidden' name='p_old_id[]' value='{$a['ID']}'/>
				<input type='text' name='p_old_name[]' value='{$a['p_name']}' class='l' placeholder='" . translate('property name') . "'/>
				<input type='text' name='p_old_value[]' value='{$a['p_value']}' class='r' placeholder='" . translate('property value') . "'/>
				<a class='c' onclick=\"$(this.parentNode).remove()\"></a>
			</div>";
    }

    return "<div class='add-property-container'>
			<div id='$id' style='display:none'>
				<div class='add-property'>
					<input type='text' name='p_name[]' class='l' placeholder='" . translate('property name') . "'/>
					<input type='text' name='p_value[]' class='r' placeholder='" . translate('property value') . "'/>
					<a class='c' onclick=\"$(this.parentNode).remove()\"></a>
				</div>
			</div>
			<div class='m'>
				$list
			</div>
			<div class='b'>
				<a class='small-button' onclick=\"$('#$id').find('.add-property').each(function(){ $('#$id').next('.m').append(this.cloneNode(true) ) })\"> + " . translate('more') . "</a>
			</div>
		</div>";

}

function tpl_add_label( $options = array() ) {
    $list_tag = "";

    foreach ( (array) $options['labels'] as $a ) {
        $list_tag .= "<span class='tag1'>
				<span class='tag-title'>{$a['label']}</span>
				<span class='tag-remove'>x</span>
				<input type='hidden' name='labels[]' value='{$a['ID']}'>
			</span>";
    }

    return "
			<div class='add-labels'>
				<div class='add-label-lists'>
					{$list_tag}
				</div>
				<div style='display:none'>
					<textarea class='var-url'>{$options['url']}</textarea>
					<textarea class='var-id'>" . getId() . "</textarea>
				</div>
				<div class='small-button-container'>
					<a class='small-button'> + " . translate('add label button') . "</a>
				</div>
			</div>";

}

function tpl_list_label( $options = array() ) {
    $html = '<div class="x-options-container"><form>
				<a class="x-button">';
    $html .= translate('label button');
    $html .= '<span>&#9660;</span></a>';
    $html .= '<div class="x-options">';

    if ( count((array) ($options['labels'])) == 0 ) {
        $html .= '<div class="x-options-item">';
        $html .= "<i>chưa có nhãn nào</i>";
        $html .= "</div>";
    } else {
        $html .= '<div class="x-options-item">';

        $html .= '<div class="tt"><span>Chọn nhãn</span></div>';
        foreach ( (array) ($options['labels']) as $b ) {
            $html .= '<div style="padding:3px">' . tpl_checkbox('labels[]', $b['ID']) . '&nbsp;' . $b['label'] . '</div>';
        }

        $html .= '</div>';

        $html .= '<div class="x-options-item">';
        $html .= '<div style="padding:3px" onclick="doLabel(\'' . $options["moveUrl"] . '\',\'' . $options["id"] . '\',this)">' . translate('move label') . '</div>';
        $html .= '<div style="padding:3px" onclick="doLabel(\'' . $options["addUrl"] . '\',\'' . $options["id"] . '\',this)">' . translate('add label') . '</div>';
        $html .= '</div>';
    }
    $html .= '<div class="x-options-item">';
    $html .= '<div style="padding:3px"><a onclick="ajax_load(\'' . $options["fixUrl"] . '\')">' . translate('fix label') . '</a></div>';
    $html .= '<div style="padding:3px"><a onclick="ajax_load(\'' . $options["manageUrl"] . '\')">' . translate('manage label') . '</a></div>';
    $html .= '</div>';
    $html .= '</div></form></div>';
    return $html;

}

function tpl_progress( $p ) {
    return '<div class="x-progress">
        <div class="x-progress-bar" style="width:' . $p . '%"></div></div>';

}

function check_icon( $filename ) {

    $filetype = pathinfo($filename, PATHINFO_EXTENSION);
    if ( $filetype == "pdf" ) {
        $style_f = "<span class='icon-file icon-file-pdf'></span>";
    } elseif ( $filetype == "doc" || $filetype == "docx" ) {
        $style_f = "<span class='icon-file icon-file-doc'></span>";
    } elseif ( $filetype == "txt" ) {
        $style_f = "<span class='icon-file icon-file-txt'></span>";
    } elseif ( $filetype == "xlsx" ) {
        $style_f = "<span class='icon-file icon-file-xlsx'></span>";
    } elseif ( $filetype == "rar" ) {
        $style_f = "<span class='icon-file icon-file-rar'></span>";
    } elseif ( $filetype == "jpg" || $filetype == "png" || $filetype == "gif" ) {
        $style_f = "<span class='icon-file icon-file-img'></span>";
    }
    echo $style_f;

}

/**
 * Display a button drop
 * @param String $title
 * @param Array $items
 */
function tpl_button_options( $title, $items ) {
    $s = "<div class='x-options-container'>";
    $s .="<a class='x-button'>$title<span>&#9660;</span></a>";
    $s .="<div class='x-options'>";
    foreach ( $items as $item ) {
        $s .="<a href='{$item[1]}' class='x-options-item'>{$item[0]}</a>";
    }
    $s.="</div></div>";
    _e($s);

}

/**
 * Build a menu in side
 */
function tpl_menu_side( $items = array(), $menu_title = null ) {
    if ( count($items) == 0 )
        return "";
    if ( is_null($menu_title) ) {
        $menu_title = translate('default.menu.title');
    }

    $sub_menu = '';
    $html = "
        <li class='big-link'>
             <a>$menu_title</a>
                    </li>";
    foreach ( $items as $item ) {
        if ( isset($item['role']) && $item['role'] === false ) {
            continue;
        }
        $url = $item['url'];
        $title = $item['title'];
        $subs = (array) $item['sub_links'];
        $active = $item['active'] ? ' sub-link-current' : '';

        $html .="<li class='sub-link{$active}'>
           <a href='{$url}'>$title</a>
        </li>";

        if ( $item['active'] && count($subs) > 0 ) {
            $sub_menu_title = translate('default.menu.sub_title');
            $subs .="<li class='sub-big-link'>
                <div><a>$sub_menu_title</a></div></li>";
            foreach ( $subs as $a ) {
                $html .="<li class='sub-link{$a['active']}'>
                    <a href='{$a['url']}'>{$a['title']}</a>
                </li>";
            }
        }
    }
    $html .= "";
    return $html;

}

?>
