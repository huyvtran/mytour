<?php

function translate() {
    if ( !Zone_Base::getModule() )
        throw App_Exception('App is not ready');

    $args = func_get_args();
    $lang = Zone_Base::getLanguage();

    //return null if not set any argument
    if ( count($args) == 0 )
        return null;

    if ( !is_array(Zone_Base::$langData) ) {
        Zone_Base::$langData = array();
        $path = ROOT . "/apps/languages/$lang";
        $dir = opendir($path);

        while ($file = readdir($dir)) {
            if ( !is_file("$path/$file") && !preg_match("#\.php$#is", $file) ) {
                continue;
            }
            $data = (array) include "$path/$file";
            Zone_Base::$langData = array_merge($data, Zone_Base::$langData);
        }
        closedir($dir);
    }

    $langs = Zone_Base::$langData;

    $str = func_get_arg(0);
    array_shift($args);

    if ( !isset($langs[$str]) ) {
        $file_name = preg_match("#^([a-z0-9]+)\.#is", $str, $matches) ? $matches[1] : 'default';
        $file = ROOT . "/apps/languages/$lang/" . $file_name . ".php";
        if ( !file_exists($file) ) {
            $file = ROOT . "/apps/languages/$lang/default.php";
        }

        $terms = (array) include $file;
        $terms[$str] = $str;
        arsort($terms);
        file_put_contents($file, "<?php\n return " . var_export($terms, true) . ";");
    }

    $str = isset($langs[$str]) ? $langs[$str] : $str;

    return call_user_func_array('vsprintf', array($str, $args));

}
