<?php

class AdminHotelsController extends Zone_Action {

    public function indexAction() {
        loadClass('ZList');
        $list = new ZList();

        $list->setPageLink('#Admin/Hotels');
        $list->setSqlCount("
                SELECT COUNT(*) 
                FROM `hotels` as `a`
                LEFT JOIN `users` as `b`
                    ON `a`.`user_id` = `b`.`ID`
             ");
        $list->setSqlSelect("SELECT `a`.*, `b`.`fullname`
                            FROM `hotels` as `a`
                            LEFT JOIN `users` as `b`
                                ON `a`.`user_id` = `b`.`ID`");

        $list->setOrder("`a`.`title` DESC");

        $list->addFieldOrder(array(
            '`a`.`title`' => 'title',
            '`a`.`is_active`' => 'is_active',
            '`b`.`fullname`' => 'fullname',
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

    public function viewAction() {
        $hotel_id = getInt('ID', 0);
        $post = self::$Model->fetchRow(
                "SELECT `a`.*,`b`.`title` AS `type_title`,
                                `c`.`title` AS `country_title`,`d`.`title` AS `state_title`,
                                `c1`.`username` as `updated_by_name`,
				`c2`.`username` as `created_by_name`,
                                `c3`. `fullname` as `fullname`
                         FROM `hotels` AS `a`
                         LEFT JOIN `hotel_types` AS `b`
                            ON `a`.`type_id` = `b`.`ID`
                         LEFT JOIN `locations` AS `c`
                            ON `a`.`country_id` = `c`.`ID`
                         LEFT JOIN `locations` AS `d`
                            ON `a`.`state_id` = `d`.`ID`

                         LEFT JOIN `users` as `c1`
                            ON `c1`.`ID`=`a`.`updated_by_id`
			LEFT JOIN `users` as `c2`
                            ON `c2`.`ID`=`a`.`created_by_id`
                        LEFT JOIN `users` as `c3`
                            ON `c3`.`ID` = `a`.`user_id`
                         WHERE `a`.`ID`='$hotel_id' ");

        if (!$post) {
            self::setJSON(array(
                content => error(translate('default.edit.post_not_found'))
            ));
        }

        $type_id = self::$Model->fetchAll('SELECT * FROM `hotel_types`');

        $activity_types = self::$Model->fetchAll("SELECT 
                `a`.*,
                IF(`b`.`hotel_id`,' checked','') as `checked`
             FROM `hotel_activity_types` as `a`
                LEFT JOIN `hotel_activities` as `b`
                    ON `a`.`ID`=`b`.`service_id` AND `b`.`hotel_id`='$hotel_id'");

        $facility_types = self::$Model->fetchAll("SELECT 
                `a`.*,
                IF(`b`.`hotel_id`,' checked','') as `checked`
             FROM `hotel_facility_types` as `a`
                LEFT JOIN `hotel_facilities` as `b`
                    ON `a`.`ID`=`b`.`service_id` AND `b`.`hotel_id`='$hotel_id'");

        $service_types = self::$Model->fetchAll("SELECT 
                `a`.*,
                IF(`b`.`hotel_id`,' checked','') as `checked`
             FROM `hotel_service_types` as `a`
                LEFT JOIN `hotel_services` as `b`
                    ON `a`.`ID`=`b`.`service_id` AND `b`.`hotel_id`='$hotel_id'");

        self::set(array(
            post => $post,
            type_id => $type_id,
            activity_types => $activity_types,
            facility_types => $facility_types,
            service_types => $service_types,
        ));
    }

    public function addAction() {
        self::set(array(
            type_id => self::$Model->fetchAll('SELECT * FROM `hotel_types`'),
            activity_types => self::$Model->fetchAll('SELECT * FROM `hotel_activity_types`'),
            facility_types => self::$Model->fetchAll('SELECT * FROM `hotel_facility_types`'),
            service_types => self::$Model->fetchAll('SELECT * FROM `hotel_service_types`'),
        ));

        if (isPost()) {
            loadClass('ZData');
            $f = new ZData();
            $f->addfields(self::fields());
            $data = self::checkData($f->getData(), null);
            if (!is_array($data)) {
                self::setJSON(array(
                    alert => error($data)
                ));
            }
            //files
            $file_id = getInt('img');
            if (isset($file_id)) {
                $files = get_file_upload($file_id, 'hotel');
                $fids[] = $files['ID'];
                remove_file_upload($fids);
            }
           
            $data['img'] = $files['filename'];
            $data['date_created'] = new Model_Expr('NOW()');
            $data['created_by_id'] = get_user_id();
            $data['user_id'] = get('user_id');
            $data['is_active'] = 1;
            self::$Model->insert('hotels', $data);
            $hotel_id = self::$Model->lastId();

            $arr_activities = $_POST['activities'];
            $arr_facilities = $_POST['facilities'];
            $arr_services = $_POST['services'];
            if (isset($arr_activities) && count($arr_activities) > 0) {
                foreach ($arr_activities as $value) {
                    $data = array('hotel_id' => $hotel_id, 'service_id' => $value);
                    self::$Model->insert('hotel_activities', $data);
                }
            }
            if (isset($arr_facilities) && count($arr_facilities) > 0) {
                foreach ($arr_facilities as $value) {
                    $data = array('hotel_id' => $hotel_id, 'service_id' => $value);
                    self::$Model->insert('hotel_facilities', $data);
                }
            }
            if (isset($arr_services) && count($arr_services) > 0) {
                foreach ($arr_services as $value) {
                    $data = array('hotel_id' => $hotel_id, 'service_id' => $value);
                    self::$Model->insert('hotel_services', $data);
                }
            }
            self::setJSON(array(
                redirect => "#Admin/Hotels"
            ));
        }
    }

    public function editAction() {

        $hotel_id = getInt('ID', 0);
        $post = self::$Model->fetchRow("SELECT * FROM `hotels` WHERE `ID`='$hotel_id'");
        if (!$post) {
            self::setJSON(array(
                content => error(translate('default.edit.post_not_found'))
            ));
        }
        $type_id = self::$Model->fetchAll('SELECT * FROM `hotel_types`');

        $activity_types = self::$Model->fetchAll("SELECT 
                `a`.*,
                IF(`b`.`hotel_id`,' checked','') as `checked`
             FROM `hotel_activity_types` as `a`
                LEFT JOIN `hotel_activities` as `b`
                    ON `a`.`ID`=`b`.`service_id` AND `b`.`hotel_id`='$hotel_id'");

        $facility_types = self::$Model->fetchAll("SELECT 
                `a`.*,
                IF(`b`.`hotel_id`,' checked','') as `checked`
             FROM `hotel_facility_types` as `a`
                LEFT JOIN `hotel_facilities` as `b`
                    ON `a`.`ID`=`b`.`service_id` AND `b`.`hotel_id`='$hotel_id'");

        $service_types = self::$Model->fetchAll("SELECT 
                `a`.*,
                IF(`b`.`hotel_id`,' checked','') as `checked`
             FROM `hotel_service_types` as `a`
                LEFT JOIN `hotel_services` as `b`
                    ON `a`.`ID`=`b`.`service_id` AND `b`.`hotel_id`='$hotel_id'");

        if (isPost()) {
            loadClass('ZData');
            $form = new ZData();
            $form->addField(self::fields($post));
            $data = self::checkData($form->getData(), $hotel_id);

            if (!is_array($data)) {
                self::setJSON(array(
                    alert => error($data)
                ));
            }

            $file_id = getInt('img', 0);
            if (isset($file_id)) {
                $file = get_file_upload($file_id);
                if ($file) {
                    remove_file_upload($file_id, false);
                    @unlink("files/hotel/{$post['img']}");
                    $data['img'] = $file['name'];
                }
            }
            $data['date_updated']  = new Model_Expr('NOW()');
            $data['updated_by_id'] = get_user_id();
            $data['user_id']       = get('user_id');
            self::$Model->update('hotels', $data, "`ID`='$hotel_id'");

            $arr_activities = $_POST['activities'];
            $arr_facilities = $_POST['facilities'];
            $arr_services = $_POST['services'];

            self::$Model->delete('hotel_activities', "`hotel_id`= '$hotel_id'");
            if (isset($arr_activities) && count($arr_activities) > 0) {
                foreach ($arr_activities as $value) {
                    $data = array('hotel_id' => $hotel_id, 'service_id' => $value);
                    self::$Model->insert('hotel_activities', $data);
                }
            }

            self::$Model->delete('hotel_facilities', "`hotel_id`= '$hotel_id'");
            if (isset($arr_facilities) && count($arr_facilities) > 0) {
                foreach ($arr_facilities as $value) {
                    $data = array('hotel_id' => $hotel_id, 'service_id' => $value);
                    self::$Model->insert('hotel_facilities', $data);
                }
            }

            self::$Model->delete('hotel_services', "`hotel_id`= '$hotel_id'");
            if (isset($arr_services) && count($arr_services) > 0) {
                foreach ($arr_services as $value) {
                    $data = array('hotel_id' => $hotel_id, 'service_id' => $value);
                    self::$Model->insert('hotel_services', $data);
                }
            }

            self::setJSON(array(
                redirect => "#Admin/Hotels"
            ));
        }


        self::set(array(
            post => $post,
            type_id => $type_id,
            facility_types => $facility_types,
            service_types => $service_types,
            activity_types => $activity_types,
            user => self::$Model->fetchRow("SELECT `ID`,`fullname`, CONCAT(`fullname`,' - ',`username`) as `title`
					FROM `users` WHERE `ID`='{$post['user_id']}'"),
        ));
    }

    public function deleteAction() {
        $ids = getInt('ID', array(), true);

        if (count($ids) > 0) {
            //xoa khach san
            $cond = implode(',', $ids);

            $imgs = self::$Model->fetchAll("SELECT `img`
                FROM `hotels` WHERE `ID` IN($cond)");
            if (isset($imgs) && count($imgs) > 0) {
                foreach ($imgs as $img) {
                    @unlink("files/hotel/{$img['img']}");
                }
            }
            self::$Model->delete('hotels', "`ID` IN ($cond)");
            foreach ($ids as $id) {
                self::$Model->delete('hotel_services', "`hotel_id` = '{$id}'");
                self::$Model->delete('hotel_activities', "`hotel_id` = '{$id}'");
                self::$Model->delete('hotel_facilities', "`hotel_id` = '{$id}'");
            }

            //xoa loai phong
            $room_types = self::$Model->fetchAll("SELECT *
                 FROM `hotel_room_types` WHERE `hotel_id` IN($cond)");

            if ($room_types && count($room_types) > 0) {
                $img_rooms = array();
                $ids_rooms = array();
                foreach ($room_types as $value) {
                    $img_rooms[] = $value['img'];
                    $ids_rooms[] = $value['ID'];
                }

                if (isset($img_rooms) && count($img_rooms) > 0) {
                    foreach ($img_rooms as $value) {
                        @unlink("files/rooms/{$value['img']}");
                    }
                }
                self::$Model->delete('hotel_room_types', "`hotel_id` IN ($cond)");

                //xoa rang buoc
                if (count($ids_rooms) > 0) {
                    $cond_room_types = implode(',', $ids_rooms);
                    $rule_ids = self::$Model->fetchAll("
                        SELECT `rule_id` 
                        FROM `hotel_room_rules` 
                        WHERE `room_type_id` IN ($cond_room_types)
                    ");

                    if ($rule_ids && count($rule_ids) > 0) {
                        $arr_rule_ids = array();
                        foreach ($rule_ids as $value) {
                            $arr_rule_ids[] = $value['rule_id'];
                        }
                        $arr_rule_ids = array_unique($arr_rule_ids);
                        $cond_rule_ids = implode(',', $arr_rule_ids);
                        self::$Model->delete('hotel_rules', "`ID` IN ($cond_rule_ids)");
                        self::$Model->delete('hotel_room_rules', "`rule_id` IN ($cond_rule_ids)");
                    }
                }
            }
            //xoa hoa don
            self::$Model->delete('hotel_orders', "`hotel_id` IN ($cond) ");
        }
        self::setJSON(array(
            redirect => "#Admin/Hotels"
        ));
    }

    public function ActiveAction() {
        self::removeLayout();
        $ids = getInt('ID', array(), true);
        if (count($ids) > 0) {
            foreach ($ids as $id) {
                $data = array('is_active' => 1);
                self::$Model->update('hotels', $data, "`ID` = '{$id}'");
            }
        }
        self::setJSON(array(
            redirect => "#Admin/Hotels"
        ));
    }

    public function NoactiveAction() {
        self::removeLayout();
        $ids = getInt('ID', array(), true);
        if (count($ids) > 0) {
            foreach ($ids as $id) {
                $data = array('is_active' => 0);
                self::$Model->update('hotels', $data, "`ID` = '{$id}'");
            }
        }
        self::setJSON(array(
            redirect => "#Admin/Hotels"
        ));
    }

     public function userAction() {
        self::removeLayout();
        $s = get('s', '');
        $items = array();
        if ( strlen($s) > 0 ) {
            $items = self::$Model->fetchAll("SELECT
                `ID`,`fullname`, CONCAT(`fullname`,' - ',`username`) as `title`
		FROM `users` as `a`
                WHERE `fullname` LIKE '%$s%' LIMIT 10");
        }
        die(json_encode($items));
    }

    protected function checkData($data, $hotel_id = NULL) {
        if (!is_array($data)) {
            return $data;
        }
        if ($hotel_id) {
            $hotel_info = self::$Model->fetchAll("SELECT `ID`,`email`,`phone` FROM `hotels` WHERE `ID` != '{$hotel_id}'");
        } else {
            $hotel_info = self::$Model->fetchAll("SELECT `ID`,`email`,`phone` FROM `hotels`");
        }
        $hotel_emails = array();
        $hotel_phones = array();
        if (count($hotel_info) > 0) {
            foreach ($hotel_info as $info) {
                $hotel_emails[] = $info['email'];
                $hotel_phones[] = $info['phone'];
            }
        }
        if (in_array($data['email'], $hotel_emails)) {
            return 'Email khách sạn đã được sử dụng ';
        }
        if (in_array($data['phone'], $hotel_phones)) {
            return 'Số điện thoại khách sạn đã được sử dụng ';
        }
        return $data;
    }

    protected function fields() {
        $data = array(
            title => array(
                type => 'CHAR',
                no_empty => true,
                label => translate('default.hotel.field.title'),
            ),
            star => array(
                type => 'ENUM',
                value => array('1', '2', '3', '4', '5'),
                default_value => '1',
                label => translate('default.hotel.field.star'),
            ),
            phone => array(
                type => 'PHONE',
                no_empty => true,
                label => translate('default.hotel.field.phone'),
            ),
            phone1 => array(
                type => 'PHONE',
                label => translate('default.hotel.field.phone1'),
            ),
            phone2 => array(
                type => 'PHONE',
                label => translate('default.hotel.field.phone2'),
            ),
            email => array(
                type => 'EMAIL',
                no_empty => true,
                label => translate('default.hotel.field.email'),
            ),
            country_id => array(
                type => 'INT',
                no_empty => true,
                label => translate('default.hotel.field.country_id'),
            ),
            state_id => array(
                type => 'INT',
                no_empty => true,
                label => translate('default.hotel.field.state_id'),
            ),
            address => array(
                type => 'CHAR',
                no_empty => true,
                label => translate('default.hotel.field.address'),
            ),
            website => array(
                type => 'CHAR',
                label => translate('default.hotel.field.website'),
            ),
            fax => array(
                type => 'CHAR',
                label => translate('default.hotel.field.fax'),
            ),
            checkin_time => array(
                type => 'TIME',
                no_empty => true,
                label => translate('default.hotel.field.checkintime')
            ),
            checkout_time => array(
                type => 'TIME',
                no_empty => true,
                label => translate('default.hotel.field.checkouttime')
            ),
            type_id => array(
                type => 'INT',
                no_empty => true,
                label => translate('default.hotel.field.type_id')
            ),
            floor => array(
                type => 'INT',
                label => translate('default.hotel.field.floor')
            ),
            is_active => array(
                type => 'INT',
                default_value => 0
            ),
            desc => array(
                type => 'CHAR',
                label => translate('default.desc')
            ),
        );
        return $data;
    }

}
