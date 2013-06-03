<?php

class LogoutIndexController extends Zone_Action {

    public function indexAction() {
        clear_login();
        redirect('Login');
    }

}
