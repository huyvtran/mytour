<?php

class ZCache {

    public function addGlobal( $data, $id = NULL ) {
        global $_ZONECACHE;
        if ( !$_ZONECACHE )
            $_ZONECACHE = array();

        if ( $id === NULL )
            $id = count($_ZONECACHE);
        $_ZONECACHE[$id] = $data;
        return $id;

    }

    public function getGlobal( $id ) {
        global $_ZONECACHE;
        if ( !$_ZONECACHE )
            $_ZONECACHE = array();
        return $_ZONECACHE[$id];

    }

    public function addFile() {

    }

    public function getFile() {

    }

}
