<?php

class LoginGetpassController extends Zone_Action {

    public function init() {
        $configs = self::$Model->fetchRow("SELECT * FROM `configs` LIMIT 1");

        $user_id = get_user_id();
        $user = self::$Model->fetchRow("SELECT * FROM `users` WHERE `ID`='$user_id'");

        self::set(array(
            configs => $configs,
            user => $user
        ));
    }

    public function indexAction() {
        self::removeLayout();
        if (isPost()) {
            self::setJSON(array(
                data => 'aaaaaa'
            ));
        }
    }

}
