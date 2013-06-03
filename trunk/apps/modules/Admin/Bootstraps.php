<?php

class AdminBootstraps extends Zone_Bootstraps {

    public function init() {

        self::addJSON(array(
            title => SITE_TITLE . ' - Admin'
        ));

        //if (!self::hasRole('ADMIN_ROLE')) {
        //	self::setJSON(array(
        //       alert => error(translate('default.you_dont_has_permission'))
        //   ));
        //}
    }

}
