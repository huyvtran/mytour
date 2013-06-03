<?php

class ConfigBootstraps extends Zone_Bootstraps {

    public function init() {
        $hotel = get_hotel();

        $is_redirected = self::getController() == 'Choice';
        $is_index = self::getController() == 'Index';

        if (is_null($hotel) && !$is_redirected && !$is_index) {
            self::setJSON(array(
                redirect => '#Hotel/Choice'
            ));
        }
    }

}
