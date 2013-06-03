<?php

class ConfigIndexController extends Zone_Action {

    public function indexAction() {
        self::setJSON(array(
            redirect => '#Config/Partners'
        ));
    }

}