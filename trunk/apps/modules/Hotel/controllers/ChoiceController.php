<?php

class HotelChoiceController extends Zone_Action {

    public function indexAction() {
        $user_id = get_user_id();
        if ( isPost() ) {
            $hotel_id = getInt('hotel_id');
            $hotel = Zone_Base::$Model->fetchRow("SELECT
                * FROM `hotels`
                    WHERE `user_id`='{$user_id}'
                        AND `ID`='{$hotel_id}' AND `is_active`='1'");

            if ( !$hotel ) {
                self::setJSON(array(
                    alert => error('Khách sạn đã chọn chưa được xác nhận')
                ));
            }

            set_hotel($hotel_id);
            
            self::setJSON(array(
                redirect => "#Hotel/Home",
            ));
        }

        loadClass('ZList');
        $list = new ZList();

        $list->setPageLink('#Hotel');
        $list->setSqlCount("SELECT COUNT(*) FROM `hotels` as `a`");
        $list->setSqlSelect("SELECT * FROM `hotels` as `a`");
        $list->setWhere("`a`.`user_id` = '{$user_id}' ");
        $list->setOrder("`a`.`title` DESC");

        $list->addFieldOrder(array(
            '`a`.`title`' => 'title',
            '`a`.`is_active`' => 'is_active',
        ));

        $list->addFieldEqual(array(
            '`a`.`is_active`' => 'is_active',
        ));
        $list->addFieldText(array(
            '`a`.`title`' => 's',
        ));

        $list->run();
        self::set(array(
            posts => $list->getPosts(),
            page => $list->getPage(),
            vars => $list->getVars()
        ));

    }

}
