<?php

/*
  Zone Version 1.0
  Zone_App create and run a application MVC
  Created: 28/06/2010
  Updated: 30/06/2011
 */

$baseUrl = str_ireplace('\\', '/', dirname($_SERVER['PHP_SELF']));
$baseUrl = preg_replace('/^\/$/is', '', $baseUrl);
defined('BASE_URL') || define('BASE_URL', $baseUrl);

defined('ROOT') || define('ROOT', str_ireplace('\\', '/', dirname(dirname(__FILE__))));

require_once 'Exception.php';
require_once 'Ui.php';
require_once 'App/Base.php';
require_once 'App/Action.php';
require_once 'App/View.php';
require_once 'App/Bootstraps.php';

class Zone_App extends Zone_Base {

    function __construct() {

        self::$baseUrl = BASE_URL;

        $config = include ROOT . '/apps/configs/App.php';

        self::setConfig($config);

        if ( isset($config['adapter']) ) {
            include_once 'App/Model.php';
            $adapter = $config['adapter'];
            $dbconfig = $config['db'];

            $db = Zend_Db::factory($adapter, $dbconfig);
            self::$Model = new App_Model(array('db' => $db));
        }

        if ( isset($config['default_timezone']) ) {
            date_default_timezone_set($config['default_timezone']);
        }

        if ( isset($config['define']) ) {
            foreach ( $config['define'] as $key => $term ) {
                defined($key) || define($key, $term);
            }
        }

        $this->run();

    }

    //init module from path
    private function run() {
        //escape and get relative path
        $full_path = $_SERVER['REQUEST_URI'];
        $full_path = substr($full_path, strlen(BASE_URL));
        $full_path = preg_replace(array('/\?.*$/', '/(^\/|\/$)/', '/\/(?=\/)/'), '', $full_path);

        $slots = explode('/', $full_path);
        $config = self::$config;
        $module = isset($slots[0]) && $slots[0] != '' ? ucfirst(strtolower($slots[0])) : ( isset($config['defaultModule']) ? $config['defaultModule'] : 'Index');

        $controller = isset($slots[1]) && $slots[1] != '' ? ucfirst(strtolower($slots[1])) : 'Index';
        $action = isset($slots[2]) && $slots[2] != '' ? ucfirst(strtolower($slots[2])) : 'Index';

        $params = array();
        $p_start = 3;

        for ( $i = $p_start; $i < count($slots); $i++ ) {
            $params[] = $slots[$i];
        }

        $module = ucfirst(strtolower($module));
        $controller = ucfirst(strtolower($controller));
        $action = strtolower($action);

        self::$module = $module;
        self::$controller = $controller;
        self::$action = $action;
        self::$params = $params;

        $dir = ROOT . "/apps/modules/$module";
        $debug = isset(self::$config['debug']) ? self::$config['debug'] : false;

        if ( !is_dir($dir) ) {
            if ( $debug ) {
                throw new App_Exception("Module $module is not found");
            } else {
                return $this->setError('404');
            }
        }

        $dir = $dir . "/controllers/{$controller}Controller.php";

        if ( !file_exists($dir) ) {
            if ( $debug ) {
                throw new App_Exception("Controller $controller is not found");
            } else {
                return $this->setError('404');
            }
        } else {
            require_once 'App/Action.php';
            require_once $dir;

            $controllerClass = "{$module}{$controller}Controller";

            if ( class_exists($controllerClass) ) {
                $dir_plugin = ROOT . '/apps/Plugins.php';
                if ( file_exists($dir_plugin) ) {
                    require_once 'App/Plugins.php';
                    require_once $dir_plugin;
                }

                $ctrClass = new $controllerClass();
            } else {
                if ( $debug ) {
                    throw new App_Exception("Class $controllerClass is not found");
                } else {
                    return self::setError('404');
                }
            }

            if ( !method_exists($ctrClass, "{$action}Action") ) {
                if ( $debug ) {
                    throw new App_Exception("Action $action is not found");
                } else {
                    return self::setError('404');
                }
            }
            return $ctrClass->run();
        }

    }

}
