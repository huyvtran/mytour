<?php

class HotelRoomController extends Zone_Action {

    public function indexAction() {
//        $result = getCampaignHighest('2012-12-05', '2012-12-01', '2012-12-10', 21, 14);
//        echo '<pre>';
//        print_r($result);
//        echo '</pre>';
        $user_id = get_user_id();
        $hotel_id = get_hotel_id();
        loadClass('ZList');

        $list = new ZList();

        $list->setPageLink('#Hotel/Room');
        $list->setSqlCount("
            SELECT COUNT(*)
            FROM `hotel_room_types` as `a`
            LEFT JOIN `hotels` as `b`
                ON `a`.`hotel_id` = `b`.`ID`
            LEFT JOIN `currencies` as `c`
                ON `a`.`currency_id` = `c`. `ID`");
        $list->setSqlSelect("SELECT
            `a`.* ,
            `b`.`title` as `hotel_title`, `c`.`title` as `currency_title`,
            `a`.`number` - ( SELECT COUNT(*) FROM `hotel_orders` as `k`
                WHERE `k`.`room_type_id`=`a`.`ID` AND `k`.`status`='1' ) as `number_free`
                             FROM `hotel_room_types` as `a`
                             LEFT JOIN `hotels` as `b`
                                ON `a`.`hotel_id` = `b`.`ID`
                             LEFT JOIN `currencies` as `c`
                                ON `a`.`currency_id` = `c`. `ID`");
        $list->setOrder("`a`.`title` DESC");
        $list->setWhere("`b`.`user_id` = '{$user_id}'");
        $list->setWhere("`b`.`ID` = '{$hotel_id}'");

        $list->addFieldOrder(array(
            '`a`.`title`' => 'title',
            '`a`.`area`' => 'area',
            '`a`.`number`' => 'number',
            '`number_free`' => 'number_free',
            '`a`.`size_people`' => 'size_people',
        ));

        $list->addFieldEqual(array(
            '`a`.`hotel_id`' => 'hotel_id',
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

    public function addAction() {
        $user_id = get_user_id();
        $hotel = get_hotel();
        $hotel_id = get_hotel_id();
        self::set('hotel', $hotel);

        self::set('room_services', Plugins::getOptions('hotel_room_service_types'));
        $hotel_rules = self::$Model->fetchAll(
                "SELECT `ID`,`title` FROM `hotel_rules`
                 WHERE `hotel_id` = '{$hotel_id}'
                    "
        );
        self::set('hotel_rules', $hotel_rules);

        if (isPost()) {
            loadClass('ZData');
            $f = new ZData();
            $has_extrabed = get('has_extrabed');
            if ($has_extrabed) {
                $f->addfields(array(
                    extrabed_price => array(
                        type => 'INT',
                        no_empty => true,
                        label => 'Giá thêm giường'
                    )
                ));
            }

            $f->addfields(self::fields());
            $data = self::checkData($f->getData());

            if (!is_array($data)) {
                self::setJSON(array(
                    alert => error($data)
                ));
            }

            $file_id = getInt('img');
            if (isset($file_id)) {
                $files = get_file_upload($file_id, 'rooms');
                $fids[] = $files['ID'];
                remove_file_upload($fids);
            }
            $data['img'] = $files['filename'];
            $data['hotel_id'] = $hotel['ID'];

            self::$Model->insert('hotel_room_types', $data);

            //services
            $room_id = self::$Model->lastId();

            $arr_services = $_POST['services'];
            if (isset($arr_services) && count($arr_services) > 0) {
                foreach ($arr_services as $value) {
                    $data = array('room_id' => $room_id, 'service_id' => $value);
                    self::$Model->insert('hotel_room_services', $data);
                }
            }
            //hotel_rules
            $arr_rules = $_POST['hotel_rules'];
            if ($arr_rules && count($arr_rules) > 0) {
                foreach ($arr_rules as $value) {
                    $data = array('room_type_id' => $room_id, 'rule_id' => $value);
                    self::$Model->insert('hotel_room_rules', $data);
                }
            }

            self::setJSON(array(
                close => true,
                redirect => "#Hotel/Room/View?ID=$room_id"
            ));
        }
    }

    public function editAction() {
        $user_id = get_user_id();
        $hotel_id = get_hotel_id();
        $post_id = getInt('ID', 0);
        $room_services = self::$Model->fetchAll("SELECT
                `a`.*,
                IF(`b`.`room_id`,' checked','') as `checked`
             FROM `hotel_room_service_types` as `a`
                LEFT JOIN `hotel_room_services` as `b`
                    ON `a`.`ID`=`b`.`service_id` AND `b`.`room_id`='$post_id'");
        self::set('room_services', $room_services);

        $rules = self::$Model->fetchAll("
                SELECT `a`.`ID`
                FROM `hotel_rules` as `a`
                LEFT JOIN `hotel_room_rules` as `b`
                    ON `a`.`ID` = `b`.`rule_id`
                LEFT JOIN `hotel_room_types` as `c`
                    ON `b`.`room_type_id` = `c`.`ID`
                 WHERE `c`.`hotel_id` = '{$hotel_id}' and `b`.`room_type_id` ='{$post_id}'
            ");
        if (count($rules) > 0) {
            $rules_checked = array();
            foreach ($rules as $a) {
                $rules_checked[] = $a['ID'];
            };
        }

        $rules_all = self::$Model->fetchAll(
                "SELECT `ID`,`title` FROM `hotel_rules`
                 WHERE `hotel_id` = '{$hotel_id}'
                    ");

        self::set(array('rules_checked' => $rules_checked, 'rules_all' => $rules_all));

        if (!isPost()) {

            $hotel = get_hotel();
            self::set('hotel', $hotel);

            $post = self::$Model->fetchRow(
                    "SELECT `a`.*,`c`.`title` as `currency_title`
                        FROM `hotel_room_types` AS `a`
                        LEFT JOIN `hotels` AS `b`
                            ON `a`.`hotel_id` = `b`.`ID`
                        LEFT JOIN `currencies` as `c`
                            ON `a`.`currency_id` = `c`.`ID`
                        WHERE `a`.`ID`='$post_id'
                            AND `b`.`user_id` = '{$user_id}'
                            AND `b`.`ID` = '{$hotel_id}'
                                       ");
            if (!$post) {
                self::setJSON(array(
                    content => error(translate('default.edit.post_not_found'))
                ));
            }
            self::set('post', $post);
        } else {
            loadClass('ZData');
            $f = new ZData();
            $has_extrabed = get('has_extrabed');
            if ($has_extrabed) {
                $f->addfields(array(
                    extrabed_price => array(
                        type => 'INT',
                        no_empty => true,
                        label => 'Giá thêm giường'
                    )
                ));
            }

            $f->addfields(self::fields($post));
            $data = self::checkData($f->getData());
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
                    @unlink("files/rooms/{$post['img']}");
                    $data['img'] = $file['name'];
                }
            }
            if ($data['has_extrabed'] == 0) {
                $data['extrabed_price'] = null;
            }
           
            self::$Model->update('hotel_room_types', $data, "`ID`='$post_id'");

            //services
            $arr_services = $_POST['services'];
            self::$Model->delete('hotel_room_services', "`room_id`= '$post_id'");
            if (isset($arr_services) && count($arr_services) > 0) {
                foreach ($arr_services as $value) {
                    $data = array('room_id' => $post_id, 'service_id' => $value);
                    self::$Model->insert('hotel_room_services', $data);
                }
            }

            //rang buoc gia
            self::$Model->delete('hotel_room_rules', "`room_type_id` = {$post_id}");
            $room_rules = $_POST['room_rules'];
            if ($room_rules && count($room_rules) > 0) {
                foreach ($room_rules as $value) {
                    $data = array('room_type_id' => $post_id, 'rule_id' => $value);
                    self::$Model->insert('hotel_room_rules', $data);
                }
            }
            self::setJSON(array(
                close => true,
                redirect => "#Hotel/Room/View?ID=$post_id"
            ));
        }
    }

    public function viewAction() {
        $user_id = get_user_id();
        $hotel_id = get_hotel_id();
        $post_id = getInt('ID', 0);
        $hotel = get_hotel();
        self::set('hotel', $hotel);

        $post = self::$Model->fetchRow("
            SELECT `a`.*,`c`.`title` as `currency_title`
                FROM `hotel_room_types` AS `a`
                LEFT JOIN `hotels` AS `b`
                    ON `a`.`hotel_id` = `b`.`ID`
                LEFT JOIN `currencies` as `c`
                    ON `a`.`currency_id` = `c`.`ID`
                WHERE `a`.`ID`='$post_id'
                    AND `b`.`user_id` = '{$user_id}'
                    AND `b`.`ID` = '{$hotel_id}'
                                   ");
        if (!$post) {
            self::setJSON(array(
                content => error(translate('default.edit.post_not_found'))
            ));
        }
        self::set('post', $post);
        //services
        $room_services = self::$Model->fetchAll("
            SELECT
                `a`.*,
                IF(`b`.`room_id`,' checked','') as `checked`
             FROM `hotel_room_service_types` as `a`
                LEFT JOIN `hotel_room_services` as `b`
                    ON `a`.`ID`=`b`.`service_id` AND `b`.`room_id`='$post_id'");
        self::set('room_services', $room_services);

        //rang buoc gia
        $rules = self::$Model->fetchAll("
                SELECT `a`.`ID`
                FROM `hotel_rules` as `a`
                LEFT JOIN `hotel_room_rules` as `b`
                    ON `a`.`ID` = `b`.`rule_id`
                LEFT JOIN `hotel_room_types` as `c`
                    ON `b`.`room_type_id` = `c`.`ID`
                 WHERE `c`.`hotel_id` = '{$hotel_id}' and `b`.`room_type_id` ='{$post_id}'
            ");
        if (count($rules) > 0) {
            $rules_checked = array();
            foreach ($rules as $a) {
                $rules_checked[] = $a['ID'];
            };
        }
        $rules_all = self::$Model->fetchAll(
                "SELECT `ID`,`title` FROM `hotel_rules`
                 WHERE `hotel_id` = '{$hotel_id}'
                    ");
        self::set(array('rules_checked' => $rules_checked, 'rules_all' => $rules_all));
    }

    public function deleteAction() {
        $ids = getInt('ID', array(), true);

        if (count($ids) > 0) {
            $cond = implode(',', $ids);
            //Neu loai phong do co hoa don dang trong tinh trang doi/xac nhan =>ko cho xoa
            $orders = self::$Model->fetchAll("
                    SELECT `ID`,`status` 
                    FROM `hotel_orders` 
                    WHERE `room_type_id` IN ($cond)
                         AND `status` != '2'
                    ");
            if ($orders && count($orders)) {
                self::setJSON(array(
                    alert => error('Đang tồn tại hóa đơn trong trạng thái đợi / xác nhận !')
                ));
            }
            //else xoa loai phong do
            $imgs = self::$Model->fetchAll("SELECT `img`
                FROM `hotel_room_types` WHERE `ID` IN($cond)");
            if (isset($imgs) && count($imgs) > 0) {
                foreach ($imgs as $img) {
                    @unlink("files/rooms/{$img['img']}");
                }
            }
            self::$Model->delete('hotel_room_types', "`ID` IN ($cond)");

            foreach ($ids as $id) {
                self::$Model->delete('hotel_room_services', "`room_id` = '{$id}'");
            }
        }
        self::setJSON(array(
            redirect => "#Hotel/Room/"
        ));
    }

    protected function checkData($data, $room_type_id = NULL) {
        if (!is_array($data)) {
            return $data;
        }
        if ($data['number_mytour'] > $data['number']) {
            return translate('Số phòng giành cho Mytour.vn không được lớn hơn tổng số phòng');
        }
        return $data;
    }

    protected function fields() {
        $data = array(
            title => array(
                type => 'CHAR',
                no_empty => true,
                label => translate('default.room.field.title')
            ),
            price => array(
                type => 'INT',
                no_empty => true,
                label => translate('default.hotel.field.price'),
                min => 0,
            ),
            currency_id => array(
                type => 'INT',
                no_empty => true,
                label => translate('default.hotel.field.unit'),
            ),
            area => array(
                type => 'FLOAT',
                no_empty => true,
                label => translate('default.hotel.field.area'),
            ),
            number => array(
                type => 'INT',
                no_empty => true,
                label => translate('default.hotel.field.number'),
            ),
            size_people => array(
                type => 'INT',
                no_empty => true,
                label => translate('default.hotel.field.size_people'),
            ),
            number_mytour => array(
                type => 'INT',
                label => 'Số phòng giành riêng cho mytour.vn',
                default_value => 0
            ),
            has_extrabed => array(
                type => 'CHAR',
                label => 'Dịch vụ thêm giường',
                default_value => 0
            ),
            desc => array(
                type => 'CHAR',
                label => translate('default.hotel.field.desc'),
            ),
        );
        return $data;
    }

}

?>
