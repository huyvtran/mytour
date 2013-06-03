<?php

/**
 * @name layout
 * @author Nguyen Duc Minh
 * @date Oct 20, 2012
 *
 * This file contains API for layout 2.0
 */

/**
 * Display a input text with layout 2.0
 *
 * @param type $name
 * @param type $value
 * @param type $atts
 * @return type
 */
function html_input( $name, $value = null, $atts = array() ) {
    if ( !is_null($value) )
        $value = show_date($value);

    $att = array();
    foreach ( $atts as $k => $a )
        $att[] = $k . '="' . $a . '"';
    $att = implode(' ', $att);

    if ( $att != '' )
        $att = ' ' . $att;

    return "<input type='text' name='$name' value='$value' class='input-text'$att/>";

}

/**
 * Display a date input 2.0
 *
 * @param String $name
 * @param String $value
 * @param Array $atts
 * @return String
 */
function html_input_date( $name, $value = null, $atts = array() ) {
    if ( !is_null($value) )
        $value = show_date($value);

    $att = array();
    foreach ( $atts as $k => $a )
        $att[] = $k . '="' . $a . '"';
    $att = implode(' ', $att);

    if ( $att != '' )
        $att = ' ' . $att;

    return "<input type='text' name='$name' value='$value' onclick='date_picker(this,{format: \"d/m/Y\" })' class='input-date'$att/>";

}

/**
 * Display a simple editor
 *
 * @param String $name
 * @param String $value
 * @return String
 */
function html_text_editor( $name, $value = null ) {
    require_once ROOT . '/style/plugins/editor/Editor.php';
    return
            Editor::create(array(
                name => $name,
                value => $value,
                width => '100%'
            ));

}

/**
 * Display a multiply uploads
 *
 * @param String $url
 * @param Array $atts
 * @return String
 */
function html_input_uploads( $url, $atts = array() ) {
    return "<div class='x-files' url='{$url}'></div>";

}

/**
 * Display a button submit for add form
 *
 * @return String
 */
function html_button_add() {
    return '<button class="btn">Cập nhật</button>';

}

/**
 * Display a button cancel
 *
 * @return String
 */
function html_button_cancel() {
    return '<button class="btn btn-white">Hủy bỏ</button>';

}

/**
 * Display a error message
 *
 * @param String $message
 * @return String
 */
function html_error( $message ) {
    return "<div class='error'><div class='x' onclick='$(this.parentNode).fadeOut(200)'></div>$message</div>";

}

?>
