<?php

class Zone_Base {

    //default base url
    public static $baseUrl;
    //default language of app
    static $language = null;
    //default module of app
    static $module = null;
    //default contronller of app
    static $controller = null;
    //default action of app
    static $action = null;
    //store params and
    static $params = array();
    //store data of language items, cache
    static $data = array();
    //stop status
    static $stop = false;
    //role for user of app
    static $roles = array();
    //config for app
    static $config = array();
    //set headers
    static $headers = array(
        'Content-Type' => 'text/html'
    );
    //content replace for default action content
    static $contentAction = null;
    //view name replace for default view
    static $viewName = null;
    //layout path
    static $layout = null;
    //json data
    static $jsonData = array();

    /**
     *
     * @var App_Model
     */
    public static $Model = null;
    //store item of language
    public static $langData;
    //cache
    public static $cache = array();

    function __constructor() {
        self::$Model = new App_Model();

    }

    /**
     * Get language of app
     * @return String
     */
    final public static function getLanguage() {
        if ( empty(self::$language) )
            return self::$config['defaultLanguage'];
        return self::$language;

    }

    /**
     * Get module of app
     * ex: home
     * @return String
     */
    final public static function getModule() {
        return self::$module;

    }

    /**
     * Get controller of app
     * ex: post
     * @return String
     */
    final public static function getController() {
        return self::$controller;

    }

    /**
     * Get action of app
     * ex: indexAction
     * @return String
     */
    final public static function getAction() {
        return self::$action;

    }

    /**
     * Get module name of app
     * ex: Home, User
     * @return String
     */
    final public static function getModuleName() {
        return ucfirst(self::$module);

    }

    /**
     * Get controller name of app
     * ex: HomeIndexController
     * @return String
     */
    final public static function getControllerName() {
        return ucfirst(self::$module)
                . ucfirst(self::$controller)
                . 'Controller';

    }

    /**
     * Get action name of app
     * ex: indexAction
     * @return String
     */
    final public static function getActionName() {
        return strtolower(self::$action) . 'Action';

    }

    /**
     * Set Param for app
     *
     * @param array $params
     */
    final public static function setParams( $params ) {
        if ( !is_array($this->params) ) {
            $this->params = array();
        }
        foreach ( $params as $k => $p ) {
            $this->params[$k] = $p;
        }

    }

    /**
     *
     * @param String $k
     * @param N/A $df
     * @return N/A
     */
    final public static function getParam( $k, $df = '' ) {
        return isset(self::$params[$k]) ? self::$params[$k] : $df;

    }

    /**
     * Set role of user request to app
     * @param Array $role
     * @return null
     */
    final public static function setRole( $roles ) {
        foreach ( $roles as $role ) {
            self::$roles[] = $role;
        }

    }

    /**
     * Check role exists or not
     *
     * @param String $role
     * @return Boolean
     */
    final public static function hasRole( $role ) {
        return in_array($role, self::$roles);

    }

    /**
     * Put a variable in to store
     *
     * @param Array|String,String
     * @return null
     */
    final public static function set() {
        if ( func_num_args() == 1 ) {
            $values = $key = func_get_arg(0);
            ;
            foreach ( $values as $key => $value ) {
                self::$data[$key] = $value;
            }
        } else if ( func_num_args() > 1 ) {
            $key = func_get_arg(0);
            $value = func_get_arg(1);
            self::$data[$key] = $value;
        }

    }

    /**
     * Get a variable in store
     *
     * @return
     */
    final public static function get( $key, $default = null ) {
        if ( is_array($key) ) {
            $default = (array) $default;
            $result = array();
            foreach ( $key as $i => $k ) {
                $result[] = self::get($k, $default[$i]);
            }
            return $result;
        }
        return array_key_exists($key, self::$data) ? self::$data[$key] : $default;

    }

    /**
     * Echo a variable in store
     *
     * @param type $key
     * @param type $begin
     * @param type $end
     */
    final public static function e( $key, $begin = '', $end = '' ) {
        if ( isset(self::$data[$key]) ) {
            echo $begin . (self::$data[$key]) . $end;
        } else {
            echo '';
        }

    }

    /**
     * Set atrribute for header
     *
     * @param String $attr
     * @param String $value
     */
    final public static function setHeader( $attr, $value ) {
        self::$headers[$attr] = $value;

    }

    /**
     * Get attribute which is set in header
     *
     * @param type $attr
     * @param type $value
     * @return String
     */
    final public static function getHeader( $attr ) {
        return self::$headers[$attr];

    }

    /**
     *
     * @param type $attr
     * @return type
     */
    final public static function getHeaders() {
        return self::$headers;

    }

    /**
     * Remove current view and set a new HTML content
     *
     * @param String $html
     */
    final public static function setContentAction( $html ) {
        self::$contentAction = $html;

    }

    /**
     * Remove current view and set a new HTML content
     *
     * @param String $html
     */
    final public static function getContentAction() {
        return self::$contentAction;

    }

    /**
     * Set other view name
     *
     * @param String $name
     */
    final public static function setView( $name ) {
        self::$viewName = $name;

    }

    /**
     * Get current view name
     *
     * @param String $name
     */
    final public static function getView() {
        return self::$viewName;

    }

    /**
     * Set layout name for app
     *
     * @param String $layout
     */
    final public static function setLayout( $layout ) {
        self::$layout = $layout;

    }

    /**
     * get layout name
     *
     * @param String $name
     */
    final public static function getLayout() {
        return self::$layout;

    }

    /**
     * Remove layout
     *
     * @param String $name
     */
    final public static function removeLayout() {
        self::$layout = null;

    }

    /**
     * I still use isStop because of I'm not sure about exit is legal
     * May be update
     */
    final public static function stop() {
        self::$stop = true;
        exit;

    }

    final public static function isStop() {
        return self::$stop;

    }

    final public static function getConfig() {
        $config = self::$config;
        if ( func_num_args() == 0 ) {
            return $config;
        } else
        if ( func_num_args() > 0 ) {
            $item = func_get_arg(0);

            if ( is_string($item) ) {
                $a = explode('.', $item);
                $b = $config;
                foreach ( $a as $i ) {
                    if ( array_key_exists($i, $b) ) {
                        $b = $b[$i];
                    } else {
                        return NULL;
                    }
                }
                return $b;
            }
        }
        return array_key_exists($item, $config) ? $config[$item] : NULL;

    }

    final public static function setConfig() {
        if ( func_num_args() == 1 ) {
            $configs = func_get_arg(0);
            if ( is_array($configs) ) {
                foreach ( $configs as $item => $value ) {
                    self::$config[$item] = $value;
                }
            }
        } else
        if ( func_num_args() > 1 ) {
            $item = func_get_arg(0);
            $value = func_get_arg(1);
            self::$config[$item] = $value;
        }

    }

    /**
     *
     * @param type $model
     */
    final public static function setModel( $model ) {
        self::$Model = $model;

    }

    /**
     *
     * @return type
     */
    final public static function getModel() {
        return self::$Model;

    }

    /**
     *
     */
    final public static function setError( $message = null ) {
        include ROOT . '/apps/layouts/404.phtml';
        exit;

    }

    /**
     *
     */
    final public static function setLayoutJSON() {
        self::setHeader('Content-Type', 'application/json');

    }

    /**
     *
     * @param type $data
     */
    final public static function addJSON( $data ) {
        self::$jsonData = array_merge(self::$jsonData, (array) $data);

    }

    /**
     *
     * @param type $data
     */
    final public static function setJSON( $data ) {
        die(json_encode(array_merge(self::$jsonData, (array) $data)));

    }

    /**
     * Add cache
     */
    final public static function setCache( $index, $value ) {
        self::$cache[$index] = $value;

    }

    /**
     * Add cache
     */
    final public static function getCache( $index ) {
        return self::$cache[$index];

    }

    /**
     *
     * @param Object $index
     * @return Object
     */
    final public static function hasCache( $index ) {
        return array_key_exists($index, self::$cache);

    }

    /**
     *
     */
    public static function setContent( $html ) {
        self::setContentAction($html);
        self::display();
        exit;

    }

    public static function setFullContent( $html ) {
        self::removeLayout();
        self::setContent($html);

    }

    /* View method */

    /**
     *
     */
    public static function getContent() {
        if ( self::getContentAction() !== null ) {
            $source = self::getContentAction();
        } else {
            $root_view = ROOT . '/apps/modules/' . self::getModuleName() . '/views/';
            ob_start();
            if ( self::getView() !== null ) {
                include $root_view . '/' . self::getView() . '.phtml';
            } else {
                include $root_view . ucfirst(self::getController()) . ucfirst(self::getAction()) . '.phtml';
            }
            $source = ob_get_contents();
            ob_end_clean();
        }
        return $source;

    }

    /**
     *
     */
    final public static function showContent() {
        echo self::getContent();

    }

    /**
     *
     */
    final public static function display() {
        $headers = self::getHeaders();
        foreach ( $headers as $att => $value ) {
            header($att . ':' . $value);
        }

        $layout = self::getLayout();
        $layoutPath = ROOT . "/apps/layouts/{$layout}.phtml";

        if ( $layout && file_exists($layoutPath) ) {
            ob_start();
            require $layoutPath;
            $source = ob_get_contents();
            ob_end_clean();
        } else {
            $source = self::getContent();
        }
        if ( self::getHeader('Content-Type') == 'application/json' ) {
            $data = array(content => $source);
            $data = array_merge(self::$jsonData, $data);
            //var_dump( $data );
            echo json_encode($data);
            //echo  json_last_error();
        } else {
            echo $source;
        }

    }

}
