<?php

/**
 *
 * @param String $name
 * @throws type
 * @desc load a opensource
 */
function loadOS( $name ) {
    require_once ROOT . "/lib/OS/$name/load.php";

}

/**
 *
 * @param String $file
 * @param Array $data
 * @param String $output
 * @desc fill data into a template
 */
//function parse_export_template( $file, $data, $output = null ) {
//    loadOS('PclZip');
//    $docx = new phpdocx($file);
//    foreach ( $data as $field => $value )
//        $docx->assign('{' . $field . '}', $value);
//    if ( $output != null ) {
//        $docx->save($output);
//    } else {
//        $docx->download();
//    }
//
//}
function parse_export_template( $file, $data, $name = null ) {
    loadOS('PclZip');
    $docx = new phpdocx($file);
    foreach ( $data as $field => $value ) {
        if ( !is_array($value) ) {
            $docx->assign('{' . $field . '}', $value);
        } else {
            $block = array();
            foreach ( $value as $v ) {
                $a = array();
                foreach ( $v as $j => $h ) {
                    $a["{" . $j . "}"] = $h;
                }
                $block[] = $a;
            }
            $docx->assignBlock($field, $block);
        }
    }

    $docx->download($name);

}

function get_parse_export_template( $file, $data ) {
    loadOS('PclZip');
    $docx = new phpdocx($file);
    foreach ( $data as $field => $value ) {
        if ( !is_array($value) ) {
            $docx->assign('{' . $field . '}', $value);
        } else {
            $block = array();
            foreach ( $value as $v ) {
                $a = array();
                foreach ( $v as $j => $h ) {
                    $a["{" . $j . "}"] = $h;
                }
                $block[] = $a;
            }
            $docx->assignBlock($field, $block);
        }
    }

    $file = $docx->getFile();
    $content = file_get_contents($file);
    @unlink($file);
    return $content;

}

?>
