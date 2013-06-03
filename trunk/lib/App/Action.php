<?php

class Zone_Action extends Zone_Base {

    function __construct() {

    }

    final public function run() {

        //set config for module
        $config = include ROOT . '/apps/configs/Module.php';
        self::setConfig('modules', $config);
        self::setLayout(self::getModuleName());

        //append some unitily functions if exists
        $helper_file = ROOT . '/apps/Helpers.php';

        if ( file_exists($helper_file) ) {
            require_once $helper_file;
        }

        //excute bootstraps for all module
        $bootstrap_file = ROOT . '/apps/Bootstraps.php';

        $ctr = $this;
        if ( file_exists($bootstrap_file) ) {
            require_once $bootstrap_file;
            if ( class_exists('Bootstraps') ) {
                $boot = new Bootstraps();

                if ( method_exists($boot, 'init') ) {
                    $boot->init();
                }

                $initModule = 'init' . self::getModuleName();
                if ( method_exists($boot, $initModule) ) {
                    $boot->$initModule();
                }
            }
        }

        //append some unitily functions of module if exists
        $helper_file = ROOT . '/apps/modules/' . self::getModuleName() . '/Helpers.php';

        if ( file_exists($helper_file) ) {
            require_once $helper_file;
        }

        //excute bootstraps for current module
        $bootstrap_module_file = ROOT . '/apps/modules/' . self::getModuleName() . '/Bootstraps.php';

        if ( !self::isStop() ) {
            if ( file_exists($bootstrap_module_file) ) {
                require_once $bootstrap_module_file;
                $boot_module_name = self::getModuleName() . 'Bootstraps';
                if ( class_exists($boot_module_name) ) {
                    $boot = new $boot_module_name();

                    if ( method_exists($boot, 'init') ) {
                        $boot->init();
                    }

                    $initController = 'init' . self::getControllerName();
                    if ( method_exists($boot, $initController) ) {
                        $boot->$initController();
                    }
                }
            }
        }

        //call a function before all real action
        if ( !self::isStop() ) {
            if ( method_exists($ctr, 'init') ) {
                $ctr->init();
            }
        }

        //operacting current action
        if ( !self::isStop() ) {
            $action = self::getActionName();
            $ctr->$action();
        }
        self::display();

    }

}
