<?php

/**
 * Description of Editor
 *
 * @author Nguyen Duc Minh
 * @date created Aug 17, 2012
 */
class Editor {

    const EMO_DIRECTORY = "/style/plugins/editor/emos";

    public static $id = 1;
    //put your code here
    public static $options = array(
        width => 'auto',
        height => 'auto',
        className => 'editor-screen',
        baseUrl => BASE_URL,
        iframe => '/style/plugins/editor/iframe.php',
        button_basics => array()
    );

    public static function create( $options = array() ) {

        //extend settings
        self::$options = array_merge(self::$options, $options);

        echo self::build();

    }

    public static function getId() {
        $i = self::$id++;
        return implode('_', array('editor', session_id(), $i));

    }

    public static function build() {
        $s = self::$options;
        $id = self::getId();
        $name = $s['name'];

        $width = $s['width'];
        $height = $s['height'];
        $iframe_url = $s['baseUrl'] . $s['iframe'] . '?';
        $data = $s['value'];


        //some basic button
        // $reset_btn ="<div class='icon-group'>";
        //$reset_btn .= self::getSimpleBtn('return');
        //$reset_btn .= self::getSimpleBtn('removeformat');
        //$reset_btn .= self::getSimpleBtn('redo');
        //$reset_btn .= self::getSimpleBtn('undo');
        //$reset_btn .= self::getSimpleBtn('underline');
        //$reset_btn .= self::getSimpleBtn('strikethrough');
        //$reset_btn .="</div>";
        //some basic button
        $basic_btn = "<div class='icon-group'>";
        $basic_btn .= self::getSimpleBtn('bold', 'Bôi đậm');
        $basic_btn .= self::getSimpleBtn('italic', 'Làm nghiêng');
        $basic_btn .= self::getSimpleBtn('underline', 'Gạch dưới');
        $basic_btn .= self::getSimpleBtn('strikethrough', 'Gạch ngang');
        //$basic_btn .= self::getSimpleBtn('subscript');
        //$basic_btn .= self::getSimpleBtn('superscript');
        $basic_btn .="</div>";

        //some basic button
        $font_btn = "<div class='icon-group'>";
        $font_btn .= self::getFontBtn('font', 'Chọn font chữ');
        $font_btn .= self::getFontSizeBtn('fontsize', 'Chọn kích cỡ chữ');
        $font_btn .= self::getForecolorBtn('forecolor', 'Màu chữ');
        $font_btn .= self::getBgcolorBtn('backcolor', 'Màu nền chữ');
        $font_btn .="</div>";

        //some basic button
        $align_btn = "<div class='icon-group'>";
        $align_btn .= self::getSimpleBtn('justifycenter', 'Căn lề chính giữa');
        $align_btn .= self::getSimpleBtn('justifyfull', 'Căn lề hai bên');
        $align_btn .= self::getSimpleBtn('justifyright', 'Căn lề phải');
        $align_btn .= self::getSimpleBtn('justifyleft', 'Căn lề trái');
        $align_btn .="</div>";

        //some basic button
        $list_btn = "<div class='icon-group'>";
        $list_btn .= self::getSimpleBtn('indent', 'Lùi vào đầu dòng');
        $list_btn .= self::getSimpleBtn('outdent', 'Lùi ra đầu dòng');
        $list_btn .= self::getSimpleBtn('insertorderedlist', 'Danh sách có thứ tự');
        $list_btn .= self::getSimpleBtn('insertunorderedlist', 'Danh sách không thứ tự');
        $list_btn .="</div>";

        $web_btn = "<div class='icon-group'>";
        $web_btn .= self::getEmoBtn();
        $web_btn .= self::getSimpleBtn('insertlink', 'Chèn liên kết');
        // $web_btn .= self::getSimpleBtn('insertimage');
        //$web_btn .= self::getSimpleBtn('inserttable');
        //$web_btn .= self::getSimpleBtn('insertvideo');
        //$web_btn .= self::getSimpleBtn('insertaudio');
        $web_btn .="</div>";

        $html = "<div id='$id' class='editor'>
            <table cellpadding='0' cellspacing='0' border='0' width='100%'>";
        $html .= "<tr>";
        $html .= "<td>
                <div class='editor-panel'>
                    {$reset_btn}
                    {$basic_btn}
                    {$font_btn}
                    {$align_btn}
                    {$list_btn}
                    {$web_btn}
                </div>
            </td>";
        $html .= "</tr>";

        $html .="<tr>";
        $html .="<td>
                    <div class='text editor-screen' style='width:$width;height:$height'>
                    <textarea name='$name' class='plain' style='display:none'>{$data}</textarea>
                    <iframe style='border:none;width:100%;height:100%' border='0' src='$iframe_url#id=$id'></iframe>
                    </div>
                </td>";
        $html .= "</tr>";

        $html .= "</table></div>";
        return $html;

    }

    public static function getSimpleBtn( $name, $title = '' ) {
        return "<i class='icon icon-simple icon_$name' editor-command='$name' title='$title'></i>";

    }

    public static function getForecolorBtn( $name, $title = '' ) {
        $tb = self::getPickerColor();
        return "<i class='icon icon-simple icon_$name' editor-command='$name'>
            $tb</i>";

    }

    public static function getBgcolorBtn( $name, $title = '' ) {
        $tb = self::getPickerColor();
        return "<i class='icon icon-simple icon_$name' editor-command='$name'>
            $tb</i>";

    }

    public static function getPickerColor() {
        $colors = array(
            '246, 197, 190', '255, 230, 199', '254, 241, 209', '185, 228, 208',
            '198, 243, 222', '201, 218, 248', '228, 215, 245', '252, 222, 232',
            '239, 160, 147', '255, 214, 162', '252, 232, 179', '137, 211, 178',
            '160, 234, 201', '164, 194, 244', '208, 188, 241', '251, 200, 217',
            '230, 101, 80', '255, 188, 107', '252, 218, 131', '68, 185, 132',
            '104, 223, 169', '109, 158, 235', '182, 148, 232', '247, 167, 192',
            '204, 58, 33', '234, 160, 65', '242, 201, 96', '20, 158, 96',
            '61, 199, 137', '60, 120, 216', '142, 99, 206', '224, 119, 152',
            '172, 43, 22', '207, 137, 51', '213, 174, 73', '11, 128, 75',
            '42, 156, 104', '40, 91, 172', '101, 62, 155', '182, 87, 117',
            '130, 33, 17', '164, 106, 33', '170, 136, 49', '7, 98, 57',
            '26, 118, 77', '28, 69, 135', '65, 35, 109', '131, 51, 76');
        $tb = "<table>";

        for ( $i = 3; $i <= 255; $i+=36 ) {
            $tb .="<td><div onclick=\"$(this).parent('.icon-simple').set('currentValue','rgb($i,$i,$i)')\" style='background:rgb($i,$i,$i)'></div></td>";
        }

        for ( $i = 0; $i < count($colors); ) {
            $tb.="<tr>
                    <td><div onclick=\"$(this).parent('.icon-simple').set('currentValue','rgb({$colors[$i]})')\" style='background:rgb({$colors[$i++]})'></div></td>
                    <td><div onclick=\"$(this).parent('.icon-simple').set('currentValue','rgb({$colors[$i]})')\" style='background:rgb({$colors[$i++]})'></div></td>
                    <td><div onclick=\"$(this).parent('.icon-simple').set('currentValue','rgb({$colors[$i]})')\" style='background:rgb({$colors[$i++]})'></div></td>
                    <td><div onclick=\"$(this).parent('.icon-simple').set('currentValue','rgb({$colors[$i]})')\" style='background:rgb({$colors[$i++]})'></div></td>
                    <td><div onclick=\"$(this).parent('.icon-simple').set('currentValue','rgb({$colors[$i]})')\" style='background:rgb({$colors[$i++]})'></div></td>
                    <td><div onclick=\"$(this).parent('.icon-simple').set('currentValue','rgb({$colors[$i]})')\" style='background:rgb({$colors[$i++]})'></div></td>
                    <td><div onclick=\"$(this).parent('.icon-simple').set('currentValue','rgb({$colors[$i]})')\" style='background:rgb({$colors[$i++]})'></div></td>
                    <td><div onclick=\"$(this).parent('.icon-simple').set('currentValue','rgb({$colors[$i]})')\" style='background:rgb({$colors[$i++]})'></div></td>
                </tr>";
        }

        $tb .="</table>";
        return "<div class='editor-pickcolor'>$tb</div>";

    }

    public static function getEmoBtn() {
        $div = "<div class='editor-options'>";
        $path = ROOT . Editor::EMO_DIRECTORY;
        $d = opendir($path);
        while (($f = readdir($d)) != null) {
            if ( $f == '.' || $f == '..' ) {
                continue;
            }
            $file = "$path/$f";
            if ( is_file($file) ) {
                $img = BASE_URL . Editor::EMO_DIRECTORY . '/' . $f;
                $div .="<img src='$img' onclick=\"$(this).parent('.icon-simple').set('currentValue','$img')\"/>";
            }
        }
        $div .="</div>";
        return "<i class='icon icon-simple icon_insertemo' editor-command='insertemo'>$div</i>";

    }

    public static function getFontBtn( $name ) {
        $fonts = array(
            "arial, helvetica, sans-serif" => 'Sans Serif',
            "'times new roman',serif" => 'Serif',
            "'arial black', sans-serif" => 'Wide',
            "'arial narrow', sans-serif" => 'Narrow',
            "'comic sans ms', sans-serif" => 'Comic Sans MS',
            "'courier new', monospace" => 'Courier New',
            "garamond, serif" => 'Garamond',
            "georgia, serif" => 'Georgia',
            "tahoma, sans-serif" => 'Tahoma',
            "'trebuchet ms', sans-serif" => 'Trebuchet MS',
            "verdana, sans-serif" => 'Verdana'
        );

        $div = "<div class='editor-options'>";
        foreach ( $fonts as $font => $font_name ) {
            $font = addslashes($font);
            $div .="<div onclick=\"$(this).parent('.icon-simple').set('currentValue','$font')\" style=\"font-family:$font\" class='editor-options-item'>
                $font_name</div>";
        }
        $div .="</div>";
        return "<i class='icon icon-simple icon_$name' editor-command='$name'>$div</i>";

    }

    public static function getFontSizeBtn( $name ) {
        $fonts = array(
            'x-small' => 'Nnhỏ',
            'medium' => 'Bình thường',
            'large' => 'Lớn',
            'xx-large' => 'Cực lớn'
        );
        $div = "<div class='editor-options'>";
        foreach ( $fonts as $size => $size_name )
            $div .="<div onclick=\"$(this).parent('.icon-simple').set('currentValue','$size')\" style='font-size:$size' class='editor-options-item'>$size_name</div>";
        $div .="</div>";
        return "<i class='icon icon-simple icon_$name' editor-command='$name'>$div</i>";

    }

}

?>
