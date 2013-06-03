<?php

//load a class from Zone/Class/
function loadClass( $class_name ) {
    require_once dirname(get_include_path()) . '/lib/Class/' . $class_name . '.php';

}

//get a class from Zone/Class
function getClass( $class_name ) {
    loadClass($class_name);
    return new $class_name();

}
