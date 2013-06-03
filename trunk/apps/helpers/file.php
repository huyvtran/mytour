<?php

/**
 * @name file
 * @author Nguyen Duc Minh
 * @date Jul 14, 2012
 */

/**
 * Get one or many file from caches
 * @param type $file_id
 * @return null
 */
function get_file_upload( $file_id, $path = null ) {
    $where = '';
    if ( !is_null($path) )
        $where = " AND `path`='$path'";
    if ( is_array($file_id) ) {
        if ( count($file_id) == 0 )
            return array();
        $ids = implode(',', $file_id);
        return Zone_Base::$Model->fetchAll("SELECT *
                FROM `cache_files` WHERE `ID` IN($ids) $where");
    }
    if ( $file_id == 0 )
        return null;
    return Zone_Base::$Model->fetchRow("SELECT *
            FROM `cache_files` WHERE `ID`='$file_id' $where");

}

/**
 * Remove file in cache files
 * @param type $file_id
 * @param type $delete_source
 * @return boolean
 */
function remove_file_upload( $file_id, $delete_source = false ) {
    $file_id = is_array($file_id) ? $file_id : array($file_id);

    if ( count($file_id) == 0 )
        return false;
    $ids = implode(',', $file_id);

    if ( $delete_source ) {
        $files = Zone_Base::$Model->fetchAll("SELECT *
                FROM `cache_files` WHERE `ID` IN ($ids)");

        foreach ( $files as $file ) {
            if ( !$file['path'] )
                $file['path'] = 'upload';
            @unlink("files/{$file['path']}/{$file['name']}");
        }

        Zone_Base::$Model->delete('cache_files', "`ID` IN ($ids)");
        return true;
    }else {
        Zone_Base::$Model->delete('cache_files', "`ID` IN ($ids)");
        return true;
    }

}

/**
 * Move file from cache to module files
 * @param type $file_id
 * @param type $table
 * @param type $params
 * @return boolean
 */
function move_file_upload( $file_id, $table, $params = array() ) {
    $file = Zone_Base::$Model->fetchRow("SELECT * FROM `cache_files` WHERE `ID`='$file_id'");
    if ( !$file )
        return false;
    foreach ( array('filename', 'name', 'size', 'type') as $k ) {
        $params[$k] = $file[$k];
    }
    Zone_Base::$Model->delete("`cache_files`", "`ID`='$file_id'");
    Zone_Base::$Model->insert($table, $params);

}

?>
