<?php

class HotelRulePriceController extends Zone_Action {

    const LOG_TYPE = 'RULE_PRICE';

    public function indexAction() {
        $hotel_id = get_hotel_id();
        $user_id = get_user_id();
        loadClass('ZList');

        $list = new ZList();

        $list->setPageLink('#Hotel/RulePrice');
        $list->setSqlCount("SELECT COUNT(*) 
                 FROM `hotel_rules` AS `a`
                 LEFT JOIN `hotel_room_rules` AS `b`
                    ON `a`.`ID` = `b`.`rule_id`
                  LEFT JOIN `currencies` as `d`
                    ON `a`.`currency_id` = `d`.`ID`
                ");

        $list->setSqlSelect("SELECT `a`.*,`d`.`title` as `currency_title`
                 FROM `hotel_rules` AS `a`                 
                 LEFT JOIN `currencies` as `d`
                    ON `a`.`currency_id` = `d`.`ID`
                ");
        $list->setWhere("`a`.`created_by_id` = '{$user_id}'");
        $list->setWhere(" `a`.`hotel_id` = '{$hotel_id}'");
        $list->setGroupBy("`a`.`ID`");

        $list->setOrder("`a`.`date_start` DESC");

        $list->addFieldOrder(array(
            '`a`.`title`' => 'title',
            '`a`.`date_start`' => 'date_start',
            '`a`.`date_end`' => 'date_end',
            '`a`.`priority`' => 'priority',
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
        $hotel = get_hotel();
        self::set('hotel', $hotel);
        //rooom_types
        $room_types = self::$Model->fetchAll(
                "SELECT `a`.`ID`,`a`.`title`
                             FROM `hotel_room_types` AS `a`
                             WHERE `a`.`hotel_id` = '{$hotel['ID']}' ");

        self::set('room_types', $room_types);

        //currencies
        $currencies = Plugins::getOptions('currencies');
        self::set('currencies', $currencies);

        if (isPost()) {
            loadClass('ZData');
            $f = new ZData();
            $f->addfields(self::fields());
            $data = self::checkData($f->getData());
            if (!is_array($data)) {
                self::setJSON(array(
                    alert => error($data)
                ));
            }
          
            $data['date_created'] = new Model_Expr('NOW()');
            $data['created_by_id'] = get_user_id();
            $data['hotel_id'] = get_hotel_id();

            if (isset($data['days'])) {
                $data['days'] = implode(',', $data['days']);
            }

            self::$Model->insert('hotel_rules', $data);
           
            
            $rules_id = self::$Model->lastId();
            if (!empty($_POST['room_types']) > 0) {
                $room_rules = $_POST['room_types'];
                foreach ($room_rules as $room_id) {
                    $dataRoomRules = array('room_type_id' => $room_id, 'rule_id' => $rules_id);
                    self::$Model->insert('hotel_room_rules', $dataRoomRules);
                }
            }
            
             // add log
            if ($rules_id) {
                $desc = 'Thêm mới ràng buộc giá ' . $rules_id;
                Plugins::logActions(self::LOG_TYPE, $desc);
            }
            self::setJSON(array(
                redirect => "#Hotel/RulePrice/View?ID=$rules_id"
            ));
        }
    }

    public function editAction() {
        $hotel = get_hotel();
        $rule_id = getInt('ID', 0);
        $user_id = get_user_id();

        // add to log_actions                
        $desc = 'Sửa ràng buộc giá ' . $rule_id;

        $currencies = Plugins::getOptions('currencies');
        self::set('currencies', $currencies);

        if (!isPost()) {
            $room_types = self::$Model->fetchAll(
                    "SELECT `a`.`ID`,`a`.`title`,
                                  IF(`b`.`rule_id`,' checked','') as `checked`
                               FROM `hotel_room_types` AS `a`
                               LEFT JOIN `hotel_room_rules` AS `b`
                                    ON `a`.`ID` = `b`.`room_type_id` AND `b`.`rule_id` = '{$rule_id}'
                               WHERE `a`.`hotel_id` = '{$hotel['ID']}' ");

            $post = self::$Model->fetchRow(
                    "SELECT `a`.*,`b`.*,`c`.`hotel_id`
                        FROM `hotel_rules` AS `a`
                            LEFT JOIN `hotel_room_rules` AS `b`
                                ON `a`.`ID` = `b`.`rule_id`
                            LEFT JOIN `hotel_room_types` AS `c`
                                ON `b`.`room_type_id` = `c`.`ID`
                        WHERE `a`.`ID` = '{$rule_id}'  
                                AND `a`.`hotel_id` = '{$hotel['ID']}'
                                AND `a`.`created_by_id` = '{$user_id}'
                        GROUP BY `c`.`hotel_id` 
                    ");

            if (!$post || ($post['sign'] == '=' && $post['currency_id'] == 0)) {
                self::setJSON(array(
                    content => error(translate('default.edit.post_not_found'))
                ));
            }
        } else {
            loadClass('ZData');
            $f = new ZData();
            $f->addfields(self::fields());
            $data = self::checkData($f->getData(), $rule_id);
            if (!is_array($data)) {
                self::setJSON(array(
                    alert => error($data)
                ));
            }
            $data['date_updated'] = new Model_Expr('NOW()');
            $data['updated_by_id'] = get_user_id();

            if (isset($data['days'])) {
                $data['days'] = implode(',', $data['days']);
            }

            $arr_rules = $_POST['room_types'];
            self::$Model->update('hotel_rules', $data, "`ID`='$rule_id'");

            self::$Model->delete('hotel_room_rules', "`rule_id`= '$rule_id'");
            if (isset($arr_rules) && count($arr_rules) > 0) {
                foreach ($arr_rules as $value) {
                    $data = array('room_type_id' => $value, 'rule_id' => $rule_id);
                    self::$Model->insert('hotel_room_rules', $data);
                }
            }

            Plugins::logActions(self::LOG_TYPE, $desc);

            self::setJSON(array(
                redirect => "#Hotel/RulePrice/View?ID=$rule_id"
            ));
        }
        self::set(array(
            'hotel' => $hotel,
            'room_types' => $room_types,
            'post' => $post,
        ));
    }

    public function viewAction() {
        $hotel = get_hotel();
        $rule_id = getInt('ID', 0);
        $user_id = get_user_id();
        
        $currencies = Plugins::getOptions('currencies');
        self::set('currencies', $currencies);

        $room_types = self::$Model->fetchAll(
                "SELECT `a`.`ID`,`a`.`title`,
                                  IF(`b`.`rule_id`,' checked','') as `checked`
                               FROM `hotel_room_types` AS `a`
                               LEFT JOIN `hotel_room_rules` AS `b`
                                    ON `a`.`ID` = `b`.`room_type_id` AND `b`.`rule_id` = '{$rule_id}'
                               WHERE `a`.`hotel_id` = '{$hotel['ID']}' ");

        $post = self::$Model->fetchRow(
                "SELECT `a`.*,`b`.*,`c`.`hotel_id`
                        FROM `hotel_rules` AS `a`
                            LEFT JOIN `hotel_room_rules` AS `b`
                                ON `a`.`ID` = `b`.`rule_id`
                            LEFT JOIN `hotel_room_types` AS `c`
                                ON `b`.`room_type_id` = `c`.`ID`
                        WHERE `a`.`ID` = '{$rule_id}'  
                                AND `a`.`hotel_id` = '{$hotel['ID']}'
                                AND `a`.`created_by_id` = '{$user_id}'
                        GROUP BY `c`.`hotel_id` 
                    ");
      
        if (!$post || ($post['sign'] == '=' && $post['currency_id'] == 0)) {
            self::setJSON(array(
                content => error(translate('default.edit.post_not_found'))
            ));
        }
         self::set(array(
            'hotel' => $hotel,
            'room_types' => $room_types,
            'post' => $post,
        ));
    }

    public function deleteAction() {
        $ids = getInt('ID', array(), true);

        if (count($ids) > 0) {
            $cond = implode(',', $ids);
            self::$Model->delete('hotel_rules', "`ID` IN ($cond)");
            self::$Model->delete('hotel_room_rules', "`rule_id` IN ($cond)");
        }

        $desc = 'Xóa ràng buộc giá ' . $cond;
        Plugins::logActions(self::LOG_TYPE, $desc);

        self::setJSON(array(
            redirect => "#Hotel/RulePrice/"
        ));
    }

    private function checkData($data, $rule_id = null) {
        if (!is_array($data)) {
            return $data;
        }
        if (isset($data['date_start']) && isset($data['date_end'])) {
            if (strtotime($data['date_start']) > strtotime($data['date_end'])) {
                return translate('default.hotel.dateend.not.lower.datestart');
            }
        }
        if ($data['sign'] == '=' && $data['currency_id'] == 0) {
            return 'Sai kiểu của giá trị áp dụng';
        }

        $date_start = date('Y-m-d', strtotime($data['date_start']));
        $date_end = date('Y-m-d', strtotime($data['date_end']));
        $days = $data['days'];
        $hotel = get_hotel();

        $room_types = $_POST['room_types'];
        
        if (count($room_types) > 0) {
            ($rule_id) ? $is_edit = " AND `a`.`ID` != '{$rule_id}' " : $is_edit = '';
            ($data['sign'] == '=') ? $is_sign = " AND `a`.`sign` = '=' " : $is_sign = '';

            foreach ($room_types as $room_id) {
                //Lay tat ca cac rule trung khoang time cua loai phong day
                $room_rules = self::$Model->fetchAll(" 
                            SELECT `a`.`ID`,`a`.`sign`,`a`.`title`,`a`.`date_start`,`a`.`date_end`,
                                   `a`.`days`,`a`.`priority`,
                                   `b`.`room_type_id` 
                            FROM `hotel_rules` as `a`
                            LEFT JOIN `hotel_room_rules` as `b`
                                ON `a`.`ID` = `b`.`rule_id`
                            WHERE `a`.`hotel_id` = '{$hotel['ID']}'
                               " . $is_sign . $is_edit . "
                                AND `b`.`room_type_id`  = '{$room_id}'
                               AND (
                                    (`a`.`date_start` <= '{$date_start}' AND `a`.`date_end` >= '{$date_end}' )
                                    OR (`a`.`date_start` >= '{$date_start}' AND `a`.`date_end` <= '{$date_end}' )
                                    OR (`a`.`date_start` <= '{$date_start}' AND `a`.`date_end` <= '{$date_end}' AND  `a`.`date_end` >= '{$date_start}')
                                    OR (`a`.`date_start` >= '{$date_start}' AND `a`.`date_end` >= '{$date_end}' AND  `a`.`date_end` <= '{$date_end}')
                                )
                        ");

                //Kiem tra co trung thu khong
                $check_day = true;
                if (count($room_rules) > 0) {
                    foreach ($room_rules as $value) {
                        $day_arr = explode(',', $value['days']);
                        $arr_check = array_intersect($day_arr, $days);
                        if (count($arr_check) > 0) {
                            $check_day = false;
                            break;
                        }
                    }
                }
                if ($data['sign'] == '=') {
                    if ($check_day == false) {
                        return 'Trong cùng một thời gian, chỉ có một loại quản lý giá được áp dụng kiểu "=" cho loại phòng đó';
                    }
                }

                /*
                  Trong cùng một thời gian, do uu tien la khong duoc trung nhau cho loai phong do
                  check_priority
                 */
                $check_priority = true;
                $arr_check_priority = array();
                if (count($room_rules) > 0) {
                    foreach ($room_rules as $value) {
                        $arr_check_priority[] = $value['priority'];
                    }
                    if (in_array($data['priority'], $arr_check_priority)) {
                        $check_priority = false;
                    }
                }
                if ($check_priority == false) {
                    return 'Trong cùng một khoảng thời gian, chỉ có một mức ưu tiên duy nhất cho loại phòng đó';
                }
            }
        }
        return $data;
    }

    protected function fields() {
        $data = array(
            title => array(
                type => 'CHAR',
                no_empty => true,
                label => 'Tên quản lý giá',
            ),
            date_start => array(
                type => 'DATE',
                no_empty => true,
                label => translate('default.hotel.field.date_start'),
            ),
            date_end => array(
                type => 'DATE',
                no_empty => true,
                label => translate('default.hotel.field.date_end')
            ),
            days => array(
                type => 'CHAR',
                label => translate('default.hotel.field.days')
            ),
            sign => array(
                type => 'ENUM',
                value => array('+', '-', '='),
                label => translate('default.hotel.field.sign'),
                no_empty => true,
            ),
            value => array(
                type => 'INT',
                label => translate('default.hotel.field.value'),
                no_empty => true,
                min => 0,
            ),
            currency_id => array(
                type => 'INT',
                label => translate('default.hotel.field.unit'),
                no_empty => true,
            ),
            priority => array(
                type => 'INT',
                label => 'Mức ưu tiên',
                default_value => 0,
                no_empty => true,
            ),
            desc => array(
                type => 'CHAR',
                label => translate('default.desc'),
            ),
        );
        return $data;
    }

}

?>
