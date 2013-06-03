<?php

class HotelCampaignController extends Zone_Action {

    const LOG_TYPE = 'CAMPAIGN';
    
    public function indexAction() {
        $hotel_id = get_hotel_id();
        $user_id = get_user_id();
        loadClass('ZList');

        $list = new ZList();

        $list->setPageLink('#Hotel/Campaign');
        $list->setSqlCount("SELECT COUNT(*) 
                 FROM `hotel_campaigns` AS `a`
                 LEFT JOIN `hotel_campaign_room_types` AS `b`
                    ON `a`.`ID` = `b`.`campaign_id`
                  LEFT JOIN `currencies` as `d`
                    ON `a`.`currency_id` = `d`.`ID`
                ");

        $list->setSqlSelect("SELECT `a`.*,`d`.`title` as `currency_title`
                 FROM `hotel_campaigns` AS `a`                 
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
            '`a`.`type`' => 'type',
            '`a`.`priority`' => 'priority',
        ));

        $list->addFieldEqual(array(
            '`a`.`type`' => 'type',
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
        $hotel_id = get_hotel_id();
        self::set('currencies', Plugins::getOptions('currencies'));
        //rooom_types
        $room_types = self::$Model->fetchAll(
                "SELECT `a`.`ID`,`a`.`title`
                             FROM `hotel_room_types` AS `a`
                             WHERE `a`.`hotel_id` = '{$hotel_id}' ");

        self::set('room_types', $room_types);
        if (isPost()) {
            loadClass('ZData');
            $f = new ZData();
            $f->addfields(self::fields());

            //khoang ngay loai bo
            $arrParams = array();
            $date_remove_starts = getInt('date_remove_start', array(), 1);
            $date_remove_ends = getInt('date_remove_end', array(), 1);

            if ($date_remove_starts && count($date_remove_starts) > 0) {
                foreach ($date_remove_starts as $key => $value) {
                    $arrParams['date_removes'][$key]['date_remove_start'] = str_replace('/', '-', $value);
                    $arrParams['date_removes'][$key]['date_remove_end'] = str_replace('/', '-', $date_remove_ends[$key]);
                }
            }

            $data = self::checkData($f->getData(), $arrParams);

            if (!is_array($data)) {
                self::setJSON(array(
                    alert => error($data)
                ));
            }

            //check cac field cua Chinh sach 1,2,3 (type,value,unit...)
            if ($data['policy_type'] == 'CREATE_POLICY') {
                $result_policy = array();
                $arrIDs = getInt('IDD', array(), 2);

                $f2 = new ZData();
                $f2->addfields(self::fields2());
                foreach ($arrIDs as $k => $value) {
                    if ($value == 1) {
                        $f2->setIndexItem($k);
                        $data2 = $f2->getData();
                        if (is_array($data2)) {
                            $data2 = array(
                                'type' => $data2['policy_type_show'],
                                'value' => $data2['policy_value'],
                                'unit' => $data2['unit'],
                                'prior_checkin' => $data2['prior_checkin'],
                                'disabled' => 1,
                            );
                            $result_policy[$k] = $data2;
                        } else {
                            self::setJSON(array(
                                alert => error($data2)
                            ));
                        }
                    } elseif ($value == 0) {
                        $data2 = array(
                            'type' => null,
                            'value' => null,
                            'unit' => null,
                            'prior_checkin' => null,
                            'disabled' => 0,
                        );
                        $result_policy[$k] = $data2;
                    }
                }
            }

            //img
            $file_id = getInt('img');
            if (isset($file_id)) {
                $files = get_file_upload($file_id, 'campaigns');
                $fids[] = $files['ID'];
                remove_file_upload($fids);
            }
            $data['img'] = $files['filename'];

            $data['date_created'] = new Model_Expr('NOW()');
            $data['created_by_id'] = get_user_id();
            $data['hotel_id'] = get_hotel_id();

            if (isset($data['days'])) {
                $data['days'] = implode(',', $data['days']);
            }

            self::$Model->insert('hotel_campaigns', $data);
            $rules_id = self::$Model->lastId();
            $desc = 'Thêm mới khuyến mãi '. $rules_id;
            Plugins::logActions(self::LOG_TYPE, $desc);

            //Luu chinh sach huy phong
            if ($data['policy_type'] == 'CREATE_POLICY') {
                $polices = array(
                    'hotel_id' => get_hotel_id(),
                    'policy_default' => 0,
                    'policy_room_type' => 0
                );

                self::$Model->insert('policies', $polices);
                $policy_id = self::$Model->lastId();
                foreach ($result_policy as $key => $result) {
                    $result_policy[$key]['policy_id'] = $policy_id;
                }

                self::$Model->insertMany('policy_details', $result_policy);

                $result_campaigns = array('policy_id' => $policy_id, 'campaign_id' => $rules_id);
                self::$Model->insert('policy_campaign_types', $result_campaigns);
            }

            if ($arrParams['date_removes'] && count($arrParams['date_removes']) > 0) {
                foreach ($arrParams['date_removes'] as $value) {
                    $dataRemoves = array('campaign_id' => $rules_id,
                        'date_remove_start' => date('Y-m-d', strtotime($value['date_remove_start'])),
                        'date_remove_end' => date('Y-m-d', strtotime($value['date_remove_end'])));
                    self::$Model->insert('campaign_date_removes', $dataRemoves);
                }
            }
            if (isset($_POST['room_types']) && count($_POST['room_types']) > 0) {
                $room_rules = $_POST['room_types'];
                foreach ($room_rules as $room_id) {
                    $dataRoomCampaigns = array('room_type_id' => $room_id, 'campaign_id' => $rules_id);
                    self::$Model->insert('hotel_campaign_room_types', $dataRoomCampaigns);
                }
            }
            self::setJSON(array(
                redirect => "#Hotel/Campaign/View?ID=$rules_id"
            ));
        }
    }

    public function editAction() {
        $hotel = get_hotel();
        $campaign_id = getInt('ID', 0);
        $user_id = get_user_id();

        $currencies = Plugins::getOptions('currencies');
        self::set('currencies', $currencies);

        if (!isPost()) {
            $room_types = self::$Model->fetchAll(
                    "SELECT `a`.`ID`,`a`.`title`,
                                  IF(`b`.`campaign_id`,' checked','') as `checked`
                               FROM `hotel_room_types` AS `a`
                               LEFT JOIN `hotel_campaign_room_types` AS `b`
                                    ON `a`.`ID` = `b`.`room_type_id` AND `b`.`campaign_id` = '{$campaign_id}'
                               WHERE `a`.`hotel_id` = '{$hotel['ID']}' ");
            $date_removes = self::$Model->fetchAll(
                    "SELECT `a`.* 
                        FROM `campaign_date_removes` as `a`
                        LEFT JOIN `hotel_campaigns` as `b`
                            ON `a`.`campaign_id` = `b`.`ID`
                        WHERE  `b`.`ID` = '{$campaign_id}'
                        ");

            $post = self::$Model->fetchRow(
                    "SELECT `a`.*,`b`.*,`c`.`hotel_id`
                        FROM `hotel_campaigns` AS `a`
                            LEFT JOIN `hotel_campaign_room_types` AS `b`
                                ON `a`.`ID` = `b`.`campaign_id`
                            LEFT JOIN `hotel_room_types` AS `c`
                                ON `b`.`room_type_id` = `c`.`ID`
                        WHERE `a`.`ID` = '{$campaign_id}'  
                                AND `a`.`hotel_id` = '{$hotel['ID']}'
                                AND `a`.`created_by_id` = '{$user_id}'
                        GROUP BY `c`.`hotel_id` 
                    ");

            if ($post['policy_type'] == 'CREATE_POLICY') {
                $row_policies = self::$Model->fetchRow(
                        "SELECT `policy_id` 
                            FROM `policy_campaign_types`
                            WHERE `campaign_id` = '{$campaign_id}'
                            ");
                $policy_id = $row_policies['policy_id'];

                $post_details = self::$Model->fetchAll(
                        "SELECT * 
                            FROM `policy_details`
                            WHERE `policy_id` = '{$policy_id}'
                        ");
                self::set('post_details', $post_details);
            }



            if (!$post || ($post['sign'] == '=' && $post['currency_id'] == 0)) {
                self::setJSON(array(
                    content => error(translate('default.edit.post_not_found'))
                ));
            }
        } else {
            loadClass('ZData');
            $f = new ZData();
            $f->addfields(self::fields());

            //khoang ngay loai bo
            $arrParams = array();
            $date_remove_starts = getInt('date_remove_start', array(), 1);
            $date_remove_ends = getInt('date_remove_end', array(), 1);

            if ($date_remove_starts && count($date_remove_starts) > 0) {
                foreach ($date_remove_starts as $key => $value) {
                    $arrParams['date_removes'][$key]['date_remove_start'] = str_replace('/', '-', $value);
                    $arrParams['date_removes'][$key]['date_remove_end'] = str_replace('/', '-', $date_remove_ends[$key]);
                }
            }

            $arrParams['campaign_id'] = $campaign_id;

            $data = self::checkData($f->getData(), $arrParams);
            if (!is_array($data)) {
                self::setJSON(array(
                    alert => error($data)
                ));
            }

            //check cac field cua Chinh sach 1,2,3 (type,value,unit...)
            if ($data['policy_type'] == 'CREATE_POLICY') {
                $result_policy = array();
                $arrIDs = getInt('IDD', array(), 2);
                $arrIDs[0] = 1;
                $f2 = new ZData();
                $f2->addfields(self::fields2());
              
                foreach ($arrIDs as $k => $value) {
                    if ($value == 1) {
                        $f2->setIndexItem($k);
                        $data2 = $f2->getData();
                        if (is_array($data2)) {
                            $data2 = array(
                                'type' => $data2['policy_type_show'],
                                'value' => $data2['policy_value'],
                                'unit' => $data2['unit'],
                                'prior_checkin' => $data2['prior_checkin'],
                                'disabled' => 1,
                            );
                            $result_policy[$k] = $data2;
                        } else {
                            self::setJSON(array(
                                alert => error($data2)
                            ));
                        }
                    } elseif ($value == 0) {
                        $data2 = array(
                            'type' => null,
                            'value' => null,
                            'unit' => null,
                            'prior_checkin' => null,
                            'disabled' => 0,
                        );
                        $result_policy[$k] = $data2;
                    }
                }
               
                //lay police_id
                $policy_current_id = self::$Model->fetchOne("SELECT `policy_id` FROM `policy_campaign_types` WHERE `campaign_id`= '{$campaign_id}'");
                //Neu chinh sach huy da co
                if (isset($policy_current_id)) {
                    self::$Model->update('policies', array('policy_room_type' => 0), " ID = '{$policy_current_id}'");
                    self::$Model->delete('policy_details', " `policy_id` = '{$policy_current_id}' ");
                    foreach ($result_policy as $key => $result) {
                        $result_policy[$key]['policy_id'] = $policy_current_id;
                    }
                    self::$Model->insertMany('policy_details', $result_policy);
                }
                //Neu chinh sach huy chua co
                else {
                    $polices = array(
                        'hotel_id' => get_hotel_id(),
                        'policy_default' => 0,
                        'policy_room_type' => 0
                    );
                    self::$Model->insert('policies', $polices);
                    $policy_id = self::$Model->lastId();
                    foreach ($result_policy as $key => $result) {
                        $result_policy[$key]['policy_id'] = $policy_id;
                    }
                    self::$Model->insertMany('policy_details', $result_policy);

                    $result_campaigns = array('policy_id' => $policy_id, 'campaign_id' => $campaign_id);
                    self::$Model->insert('policy_campaign_types', $result_campaigns);
                }
            }

            if ($data['policy_type'] == 'ROOM_POLICY') {
                //lay police_id
                $policy_current_id = self::$Model->fetchOne("SELECT `policy_id` FROM `policy_campaign_types` WHERE `campaign_id`= '{$campaign_id}'");
                //Neu chinh sach huy da co
                if (isset($policy_current_id) && !empty($policy_current_id)) {
                    self::$Model->delete('policies', " `ID` = '{$policy_current_id}' ");
                    self::$Model->delete('policy_details', " `policy_id` = '{$policy_current_id}' ");
                    self::$Model->delete('policy_campaign_types', " `policy_id` = '{$policy_current_id}' ");
                }
            }


            $data['date_updated'] = new Model_Expr('NOW()');
            $data['updated_by_id'] = get_user_id();

            if (isset($data['days'])) {
                $data['days'] = implode(',', $data['days']);
            }
            //img
            $file_id = getInt('img', 0);
            if (isset($file_id)) {
                $file = get_file_upload($file_id);
                if ($file) {
                    remove_file_upload($file_id, false);
                    @unlink("files/campaigns/{$post['img']}");
                    $data['img'] = $file['name'];
                }
            }

            $arr_rules = $_POST['room_types'];
            self::$Model->update('hotel_campaigns', $data, "`ID`='$campaign_id'");

            //campaign room types
            self::$Model->delete('hotel_campaign_room_types', "`campaign_id`= '$campaign_id'");
            if (isset($arr_rules) && count($arr_rules) > 0) {
                foreach ($arr_rules as $value) {
                    $data = array('room_type_id' => $value, 'campaign_id' => $campaign_id);
                    self::$Model->insert('hotel_campaign_room_types', $data);
                }
            }

            //campaign_date_removes
            self::$Model->delete('campaign_date_removes', "`campaign_id`= '$campaign_id'");
            if ($arrParams['date_removes'] && count($arrParams['date_removes']) > 0) {
                foreach ($arrParams['date_removes'] as $value) {
                    $dataRemoves = array('campaign_id' => $campaign_id,
                        'date_remove_start' => date('Y-m-d', strtotime($value['date_remove_start'])),
                        'date_remove_end' => date('Y-m-d', strtotime($value['date_remove_end'])));
                    self::$Model->insert('campaign_date_removes', $dataRemoves);
                }
            }
            
            //add to log_actions
            $desc = 'Sửa khuyến mãi '.$campaign_id;            
            Plugins::logActions(self::LOG_TYPE, $desc);

            self::setJSON(array(
                redirect => "#Hotel/Campaign/View?ID=$campaign_id"
            ));
        }
        self::set(array(
            'hotel' => $hotel,
            'room_types' => $room_types,
            'date_removes' => $date_removes,
            'post' => $post,
        ));
    }

    public function viewAction() {
        $hotel = get_hotel();
        $campaign_id = getInt('ID', 0);
        $user_id = get_user_id();

        $currencies = Plugins::getOptions('currencies');
        self::set('currencies', $currencies);

        $room_types = self::$Model->fetchAll(
                "SELECT `a`.`ID`,`a`.`title`,
                                  IF(`b`.`campaign_id`,' checked','') as `checked`
                               FROM `hotel_room_types` AS `a`
                               LEFT JOIN `hotel_campaign_room_types` AS `b`
                                    ON `a`.`ID` = `b`.`room_type_id` AND `b`.`campaign_id` = '{$campaign_id}'
                               WHERE `a`.`hotel_id` = '{$hotel['ID']}' ");
        $date_removes = self::$Model->fetchAll(
                "SELECT `a`.* 
                        FROM `campaign_date_removes` as `a`
                        LEFT JOIN `hotel_campaigns` as `b`
                            ON `a`.`campaign_id` = `b`.`ID`
                        WHERE  `b`.`ID` = '{$campaign_id}'
                        ");

        $post = self::$Model->fetchRow(
                "SELECT `a`.*,`b`.*,`c`.`hotel_id`
                        FROM `hotel_campaigns` AS `a`
                            LEFT JOIN `hotel_campaign_room_types` AS `b`
                                ON `a`.`ID` = `b`.`campaign_id`
                            LEFT JOIN `hotel_room_types` AS `c`
                                ON `b`.`room_type_id` = `c`.`ID`
                        WHERE `a`.`ID` = '{$campaign_id}'  
                                AND `a`.`hotel_id` = '{$hotel['ID']}'
                                AND `a`.`created_by_id` = '{$user_id}'
                        GROUP BY `c`.`hotel_id` 
                    ");

        if (!$post) {
            self::setJSON(array(
                content => error(translate('default.edit.post_not_found'))
            ));
        }
        self::set(array(
            'hotel' => $hotel,
            'room_types' => $room_types,
            'post' => $post,
            'date_removes' => $date_removes
        ));
    }

    public function deleteAction() {
        $ids = getInt('ID', array(), true);
        
        if (count($ids) > 0) {
            $cond = implode(',', $ids);
            self::$Model->delete('hotel_campaigns', "`ID` IN ($cond)");
            self::$Model->delete('hotel_campaign_room_types', "`campaign_id` IN ($cond)");
            self::$Model->delete('campaign_date_removes', "`campaign_id` IN ($cond)");

            $imgs = self::$Model->fetchAll("SELECT `img`
                FROM `hotel_campaigns` WHERE `ID` IN($cond)");
            if (isset($imgs) && count($imgs) > 0) {
                foreach ($imgs as $img) {
                    @unlink("files/campaigns/{$img['img']}");
                }
            }
        }
        $desc = 'Xóa khuyến mãi '. $cond;
        Plugins::logActions(self::LOG_TYPE, $desc);
        
        self::setJSON(array(
            redirect => "#Hotel/Campaign/"
        ));
    }

    private function checkData($data, $arrParams = null) {
        if (!is_array($data)) {
            return $data;
        }

        if (isset($data['date_start']) && isset($data['date_end'])) {
            if (strtotime($data['date_start']) > strtotime($data['date_end'])) {
                return translate('default.hotel.dateend.not.lower.datestart');
            }
        }
        if (isset($data['date_start_book']) && isset($data['date_end_book'])) {
            if (strtotime($data['date_start_book']) > strtotime($data['date_end_book'])) {
                return 'Ngày BĐ cho đặt KM không được nhỏ hơn ngày kết thúc cho đặt KM';
            }
        }
        if ($data['type'] == 'NORMAL') {
            $data['inteval_day'] = null;
            if (empty($data['date_start_book'])) {
                return 'Ngày bắt đầu cho đặt KM không được trống';
            } elseif (empty($data['date_end_book'])) {
                return 'Ngày kết thúc cho đặt KM không được trống';
            } elseif (strtotime($data['date_start_book']) > strtotime($data['date_end_book'])) {
                return 'Ngày BĐ cho đặt KM không được lớn hơn ngày KT cho đặt KM';
            }
        } elseif ($data['type'] == 'EARLY' || $data['type'] == 'LAST') {
            $data['date_start_book'] = null;
            $data['date_end_book'] = null;
            if (empty($data['inteval_day'])) {
                return 'Số ngày đặt phòng trước không được rỗng';
            }
        }

        if (!empty($arrParams)) {
            //date_remove
            $date_removes = $arrParams['date_removes'];
            if ($date_removes && count($date_removes) > 0) {
                foreach ($date_removes as $value) {
                    if (empty($value['date_remove_start']) || empty($value['date_remove_end'])) {
                        return 'Ngày không áp dụng không được trống';
                    } else {
                        $date_remove_start = $value['date_remove_start'];
                        $date_remove_end = $value['date_remove_end'];

                        if (strtotime($date_remove_start) > strtotime($date_remove_end)) {
                            return 'Ngày kết thúc không được nhỏ hơn ngày bắt đầu trong Ngày không áp dụng';
                        }
                        if (
                                (strtotime($date_remove_start) > strtotime($data['date_end']))
                                || (strtotime($date_remove_start) < strtotime($data['date_start']))
                                || (strtotime($date_remove_end) > strtotime($data['date_end']))
                                || (strtotime($date_remove_end) < strtotime($data['date_start']))
                        ) {
                            return 'Khoảng ngày không áp dụng không hợp lệ';
                        }
                    }
                }
            }
        }

        //check muc uu tien
        $room_types = getInt('room_types', array(), 1);
        $hotel = get_hotel();
        $date_start = date('Y-m-d', strtotime($data['date_start']));
        $date_end = date('Y-m-d', strtotime($data['date_end']));

        if (count($room_types) > 0) {
            ($arrParams['campaign_id']) ? $is_edit = " AND `a`.`ID` != '{$arrParams['campaign_id']}' " : $is_edit = '';
            foreach ($room_types as $room_id) {
                //Lay tat ca cac campaigns trung khoang time cua loai phong day
                $room_campaigns = self::$Model->fetchAll(" 
                            SELECT `a`.`ID`,`a`.`sign`,`a`.`title`,`a`.`date_start`,`a`.`date_end`,
                                   `a`.`days`,`a`.`priority`,
                                   `b`.`room_type_id` 
                            FROM `hotel_campaigns` as `a`
                            LEFT JOIN `hotel_campaign_room_types` as `b`
                                ON `a`.`ID` = `b`.`campaign_id`
                            WHERE `a`.`hotel_id` = '{$hotel['ID']}'
                               " . $is_edit . "
                                AND `b`.`room_type_id`  = '{$room_id}'
                               AND (
                                    (`a`.`date_start` <= '{$date_start}' AND `a`.`date_end` >= '{$date_end}' )
                                    OR (`a`.`date_start` >= '{$date_start}' AND `a`.`date_end` <= '{$date_end}' )
                                    OR (`a`.`date_start` <= '{$date_start}' AND `a`.`date_end` <= '{$date_end}' AND  `a`.`date_end` >= '{$date_start}')
                                    OR (`a`.`date_start` >= '{$date_start}' AND `a`.`date_end` >= '{$date_end}' AND  `a`.`date_end` <= '{$date_end}')
                                )
                        ");
                $check_priority = true;
                $arr_check_priority = array();
                if (count($room_campaigns) > 0) {
                    foreach ($room_campaigns as $value) {
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
                label => 'Tên khuyến mãi',
            ),
            date_start_book => array(
                type => 'DATE',
                label => 'Ngày bắt đầu cho đặt KM',
            ),
            date_end_book => array(
                type => 'DATE',
                label => 'Ngày kết thúc cho đặt KM'
            ),
            date_start => array(
                type => 'DATE',
                no_empty => true,
                label => 'Ngày bắt đầu áp dụng KM',
            ),
            date_end => array(
                type => 'DATE',
                no_empty => true,
                label => 'Ngày kết thúc áp dụng KM'
            ),
            days => array(
                type => 'CHAR',
                label => 'Thứ áp dụng'
            ),
            sign => array(
                type => 'ENUM',
                value => array('+', '-'),
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
            desc => array(
                type => 'CHAR',
                label => translate('default.desc'),
            ),
            type => array(
                type => 'ENUM',
                value => array('NORMAL', 'EARLY', 'LAST'),
                label => 'Kiểu khuyến mại',
                no_empty => true
            ),
            policy_type => array(
                type => 'ENUM',
                value => array('ROOM_POLICY', 'CREATE_POLICY'),
                label => 'Chính sách hủy',
                no_empty => true
            ),
            inteval_day => array(
                type => 'INT',
                label => 'Khoảng cách ngày',
                min => 0,
            ),
            priority => array(
                type => 'INT',
                label => 'Mức ưu tiên',
                min => 0,
                no_empty => true
            ),
        );
        return $data;
    }

    protected function fields2() {
        $data = array(
            policy_type_show => array(
                type => 'ENUM',
                value => array('no_show', 'within', 'any_days', 'no_cancellations'),
                label => 'Kiểu hủy',
                no_empty => true,
            ),
            policy_value => array(
                type => 'CHAR',
                label => 'Giá trị',
                no_empty => true,
            ),
            unit => array(
                type => 'INT',
                label => 'Đơn vị',
                no_empty => true,
            ),
            prior_checkin => array(
                type => 'INT',
                label => 'Số ngày trước Checkin',
                no_empty => true,
            ),
            disabled => array(
                type => 'INT',
                label => 'Trạng thái',
                value => 0
            )
        );
        return $data;
    }

}
