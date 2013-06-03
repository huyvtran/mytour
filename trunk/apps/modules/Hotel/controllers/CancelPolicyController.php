<?php

class HotelCancelPolicyController extends Zone_Action {
    
    const LOG_TYPE = 'CANCEL_POLICY';

    public function indexAction() {
        $hotel_id = get_hotel_id();
        loadClass('ZList');
        $list = new ZList();
        $list->setPageLink("#Hotel/CancelPolicy");
        $list->setSqlCount("
                SELECT COUNT(*)
                FROM `policies` as `a`
               
            ");
        $list->setSqlSelect("
                SELECT `a`.*
                FROM `policies` as `a`
            ");
        $list->setWhere("`a`.`hotel_id` = '{$hotel_id}'");
        $list->setWhere("`a`.`policy_room_type` = '1'");
        
        $list->setOrder("`a`.`ID` ASC");
        $list->addFieldOrder(array(
            '`a`.`title`' => 'title',
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

        //nhung khuyen mai chua co chinh sach huy
        $campaigns_all = self::$Model->fetchAll(
                "SELECT `a`.*,`b`.`policy_id`,
                    IF(`b`.`campaign_id`,'checked','') AS `checked`
                    FROM `hotel_campaigns` as `a`
                    LEFT JOIN `policy_campaign_types` as `b`
                        ON `a`.`ID` = `b`.`campaign_id`
                    WHERE `a`.`hotel_id` = '{$hotel['ID']}'
                    ");
        $campaigns = array();
        if (!empty($campaigns_all)) {
            foreach ($campaigns_all as $key => $value) {
                if (empty($value['checked'])) {
                    $campaigns[] = $value;
                }
            }
        }
        self::set('campaigns', $campaigns);

        //currencies
        $currencies = Plugins::getOptions('currencies');
        self::set('currencies', $currencies);

        if (isPost()) {
            loadClass('ZData');
            //check cac field chung (title,policy_default,desc)
            $f1 = new ZData();
            $f1->addfields(self::fields1());
            $result_general = $f1->getData();
            if (!is_array($result_general)) {
                self::setJSON(array(
                    alert => error($result_general)
                ));
            }
            //check cac field cua Chinh sach 1,2,3 (type,value,unit...)
            $result_policy = array();
            $arrIDs = getInt('IDD', array(), 2);
            $f2 = new ZData();
            $f2->addfields(self::fields2());
            foreach ($arrIDs as $k => $value) {
                if ($value == 1) {
                    $f2->setIndexItem($k);
                    $data2 = $f2->getData();
                    if (is_array($data2)) {
                        $data2['disabled'] = 1;
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

            //check cac field cua loai phong (date_end,date_start)
            $result_room = array();
            $arrRoomTypes = getInt('room_type_id', array(), 2);
            $f3 = new ZData();
            $f3->addfields(self::fields3());
            if ($arrRoomTypes) {
                foreach ($arrRoomTypes as $key2 => $value2) {
                    $f3->setIndexItem($key2);
                    $data3 = self::checkData($f3->getData(), array('task' => 'add'));
                    if (!is_array($data3)) {
                        self::setJSON(array(
                            alert => error($data3)
                        ));
                    }
                    $result_room[$key2] = $data3;
                }
            }

            //----------Luu policies-------------------------
            if ($result_general['policy_default'] == 'on') {
                //update policy dang co policy_default la 1 ve 0
                $policy_default = self::$Model->fetchRow(
                        "SELECT * FROM `policies` 
                            WHERE `hotel_id` ='{$hotel['ID']}' 
                                AND `policy_default` = '1'
                            ");
                if (!empty($policy_default)) {
                    $policy_default['policy_default'] = 0;
                    self::$Model->update('policies', $policy_default, "`ID` = {$policy_default['ID']}");
                }
            }

            $result_general['hotel_id'] = $hotel['ID'];
            self::$Model->insert('policies', $result_general);                        

            //--------Luu policy_details--------------------            
            $policy_id = self::$Model->lastId();
                        // add to log actions
            $desc = 'Thêm mới chính sách hủy '.$policy_id;
            Plugins::logActions(self::LOG_TYPE, $desc);
            
            foreach ($result_policy as $key => $result) {
                $result_policy[$key]['policy_id'] = $policy_id;
            }
            self::$Model->insertMany('policy_details', $result_policy);

            //--------Luu policy_room_types--------------------
            if (!empty($result_room)) {
                foreach ($result_room as $key => $value) {
                    $result_room[$key]['policy_id'] = $policy_id;
                }
                self::$Model->insertMany('policy_room_types', $result_room);
            }

            //--------Luu policy_campaign_types--------------------
            $campaigns = getInt('campaign', array(), 2);

            if (!empty($campaigns)) {
                $result_campaigns = array();
                foreach ($campaigns as $key => $value) {
                    $result_campaigns[$key]['policy_id'] = $policy_id;
                    $result_campaigns[$key]['campaign_id'] = $value;
                }
                self::$Model->insertMany('policy_campaign_types', $result_campaigns);
            }

            self::setJSON(array(
                redirect => "#Hotel/CancelPolicy"
            ));
        }
    }

    public function editAction() {
        $policy_id = getInt('ID', 0);
        $hotel = get_hotel();
        $currencies = Plugins::getOptions('currencies');
        self::set('currencies', $currencies);

        if (!isPost()) {

            $room_types = self::$Model->fetchAll(
                    "SELECT `a`.*,`b`.`date_start`,`b`.`date_end`,
                            IF(`b`.`policy_id`,'checked','') as checked
                       FROM `hotel_room_types` AS `a`
                       LEFT JOIN `policy_room_types` as `b`
                            ON `a`.`ID` = `b`.`room_type_id` and `b`.`policy_id` = '{$policy_id}'
                       WHERE `a`.`hotel_id` = '{$hotel['ID']}' ");

            $post = self::$Model->fetchRow(
                    "SELECT `a`.*
                          FROM `policies` as `a`
                          WHERE `a`.`ID` = '{$policy_id}' and `a`.`hotel_id` = '{$hotel['ID']}'
                        ");

            $post_details = self::$Model->fetchAll(
                    "SELECT * 
                        FROM `policy_details`
                        WHERE `policy_id` = '{$post['ID']}'
                        ORDER BY `ID` ASC
                        ");

            //------nhung khuyen mai chua co chinh sach huy tru nhung KM dang duoc tich---------
            //KM da duoc sd - km da duoc tich
            $campaign_uses = self::$Model->fetchAll(
                    "SELECT `campaign_id`
                        FROM `policy_campaign_types`
                        WHERE `policy_id` != '{$policy_id}'
                        ");
            $id_uses = array();
            if (!empty($campaign_uses)) {
                foreach ($campaign_uses as $value) {
                    $id_uses[] = $value['campaign_id'];
                }
            }

            $str_id_uses = implode(',', $id_uses);

            //danh sach KM hien thi
            $campaign_displays = self::$Model->fetchAll(
                    "SELECT `a`.*,
                        IF(`b`.`policy_id`,'checked','') as checked
                       FROM `hotel_campaigns` as `a`
                         LEFT JOIN `policy_campaign_types` as `b` 
                        ON `a`.`ID` = `b`.`campaign_id` and `b`.`policy_id` = '{$policy_id}'
                       WHERE `a`.`ID` NOT IN ($str_id_uses)
                        ");
            self::set('campaigns', $campaign_displays);


            if (!$post) {
                self::setJSON(array(
                    content => error(translate('default.edit.post_not_found'))
                ));
            }
        } else {
            loadClass('ZData');
            //check cac field chung (title,policy_default,desc)
            $f1 = new ZData();
            $f1->addfields(self::fields1());
            $result_general = $f1->getData();
            if (!is_array($result_general)) {
                self::setJSON(array(
                    alert => error($result_general)
                ));
            }

            //check cac field cua Chinh sach 1,2,3 (type,value,unit...)
            $result_policy = array();
            $arrIDs = getInt('IDD', array(), 2);
            $f2 = new ZData();
            $f2->addfields(self::fields2());
            foreach ($arrIDs as $k => $value) {
                if ($value == 1) {
                    $f2->setIndexItem($k);
                    $data2 = $f2->getData();
                    if (is_array($data2)) {
                        $data2['disabled'] = 1;
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
            //check cac field cua loai phong (date_end,date_start)
            $result_room = array();
            $arrRoomTypes = getInt('room_type_id', array(), 2);
            $f3 = new ZData();
            $f3->addfields(self::fields3());
            if ($arrRoomTypes) {
                foreach ($arrRoomTypes as $key2 => $value2) {
                    $f3->setIndexItem($key2);
                    $data3 = self::checkData($f3->getData(), array('task' => 'edit', 'policy' => $policy_id));
                    if (!is_array($data3)) {
                        self::setJSON(array(
                            alert => error($data3)
                        ));
                    }
                    $result_room[$key2] = $data3;
                }
            }

            //----------Update policies-------------------------
            if ($result_general['policy_default'] == 'on') {
                //update policy dang co policy_default la 1 ve 0
                $policy_default = self::$Model->fetchRow(
                        "SELECT * FROM `policies` 
                            WHERE `hotel_id` ='{$hotel['ID']}' 
                                AND `policy_default` = '1'
                            ");
                if (!empty($policy_default)) {
                    $policy_default['policy_default'] = 0;
                    self::$Model->update('policies', $policy_default, "`ID` = {$policy_default['ID']}");
                }
            }

            $result_general['hotel_id'] = $hotel['ID'];

            self::$Model->update('policies', $result_general, "`ID` = $policy_id");
            
            $desc = 'Sửa chính sách hủy '. $policy_id;
            Plugins::logActions(self::LOG_TYPE, $desc);

            //--------Update policy_details--------------------

            self::$Model->delete('policy_details', "`policy_id` = $policy_id");
            foreach ($result_policy as $key => $result) {
                $result_policy[$key]['policy_id'] = $policy_id;
            }
            self::$Model->insertMany('policy_details', $result_policy);

            //--------Luu policy_room_types--------------------
            self::$Model->delete('policy_room_types', "`policy_id` = $policy_id");
            if (!empty($result_room)) {
                foreach ($result_room as $key => $value) {
                    $result_room[$key]['policy_id'] = $policy_id;
                }
                self::$Model->insertMany('policy_room_types', $result_room);
            }

            //--------Luu policy_campaign_types--------------------
            $campaigns = getInt('campaign', array(), 2);
            self::$Model->delete('policy_campaign_types', "`policy_id` = $policy_id");
            if (!empty($campaigns)) {
                $result_campaigns = array();
                foreach ($campaigns as $key => $value) {
                    $result_campaigns[$key]['policy_id'] = $policy_id;
                    $result_campaigns[$key]['campaign_id'] = $value;
                }
                self::$Model->insertMany('policy_campaign_types', $result_campaigns);
            }
            self::setJSON(array(
                redirect => "#Hotel/CancelPolicy"
            ));
        }
        self::set(array(
            'hotel' => $hotel,
            'room_types' => $room_types,
            'post' => $post,
            'post_details' => $post_details,
        ));
    }

    public function deleteAction() {
        $ids = getInt('ID', array(), true);
        if (count($ids) > 0) {
            $cond = implode(',', $ids);
            //xoa bang policies
            self::$Model->delete('policies', "`ID` IN ($cond)");
            //xoa bang policy_detail
            self::$Model->delete('policy_details', "`policy_id` IN ($cond)");
            //xoa bang policy_room_types
            self::$Model->delete('policy_room_types', "`policy_id` IN ($cond)");
            //xoa bang policy_campaign_types
            self::$Model->delete('policy_campaign_types', "`policy_id` IN ($cond)");
        }
        
        // add to log actions
        $desc = 'Xóa chính sách hủy '.$cond;        
        Plugins::logActions(self::LOG_TYPE, $desc);
        self::setJSON(array(
            redirect => "#Hotel/CancelPolicy/"
        ));
    }

    protected function checkData($data, $options = null) {
        if (!is_array($data)) {
            return $data;
        }

        if (isset($data['date_start']) && isset($data['date_end'])) {
            if (strtotime($data['date_start']) > strtotime($data['date_end'])) {
                return translate('default.hotel.dateend.not.lower.datestart');
            }
        }
        if ($options['task'] == 'add') {
            //check trong khoang thoi gian do da ton tai chinh sach huy nao chua
            $cancel_policies = self::$Model->fetchAll("
                SELECT *
                FROM `policy_room_types`
                WHERE `room_type_id` = '{$data['room_type_id']}'
                    AND NOT(
                        (`date_start` >= '{$data['date_end']}')  OR (`date_end` <= '{$data['date_start']}')
                       )
            ");
            if ($cancel_policies) {
                return 'Tồn tại chính sách hủy trong khoảng thời gian này';
            }
        } elseif ($options['task'] == 'edit') {
            //Lay khoang ngay da co
            $policy_id = $options['policy'];

            //check trong khoang thoi gian do da ton tai chinh sach huy nao chua
            $cancel_policies = self::$Model->fetchAll("
                SELECT *
                FROM `policy_room_types`
                WHERE `room_type_id` = '{$data['room_type_id']}'
                    AND NOT(
                        (`date_start` >= '{$data['date_end']}')  OR (`date_end` <= '{$data['date_start']}')
                       )
                    AND `policy_id` != '{$policy_id}'
            ");
            if ($cancel_policies) {
                return 'Tồn tại chính sách hủy trong khoảng thời gian này';
            }
        }
        return $data;
    }

    protected function fields1() {
        $data = array(
            title => array(
                type => 'CHAR',
                label => 'Tiêu đề',
                no_empty => true,
            ),
            policy_default => array(
                type => 'CHAR',
                label => 'Chính sách mặc định',
                default_value => 'off',
            ),
            desc => array(
                type => 'CHAR',
                label => 'Mô tả',
            ),
        );
        return $data;
    }

    protected function fields3() {
        $data = array(
            room_type_id => array(
                type => 'INT',
                no_empty => true,
                label => 'Loại phòng'
            ),
            date_start => array(
                type => 'DATE',
                no_empty => true,
                label => 'Ngày bắt đầu'
            ),
            date_end => array(
                type => 'DATE',
                no_empty => true,
                label => 'Ngày kết thúc'
            ),
        );
        return $data;
    }

    protected function fields2() {
        $data = array(
            type => array(
                type => 'ENUM',
                value => array('no_show', 'within', 'any_days', 'no_cancellations'),
                label => 'Kiểu hủy',
                no_empty => true,
            ),
            value => array(
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

    public function policyDefaultAction() {
        self::removeLayout();

        $hotel = get_hotel();
        $currencies = Plugins::getOptions('currencies');
        self::set('currencies', $currencies);

        if (!isPost()) {
            $post = self::$Model->fetchRow(
                    "SELECT `a`.*
                          FROM `config_policy_defaults` as `a`
                          WHERE `a`.`hotel_id` = '{$hotel['ID']}'");
        } else {
            loadClass('ZData');
            $f = new ZData();
            $f->addfields(array(
                type_1 => array(
                    type => 'ENUM',
                    value => array('no_show', 'within', 'any_days', 'no_cancellations'),
                    label => 'Kiểu hủy',
                    no_empty => true,
                ),
                value_1 => array(
                    type => 'CHAR',
                    label => 'Giá trị',
                    no_empty => true,
                ),
                unit_1 => array(
                    type => 'INT',
                    label => 'Đơn vị',
                    no_empty => true,
                ),
                prior_checkin_1 => array(
                    type => 'INT',
                    label => 'Số ngày trước Checkin',
                    no_empty => true,
                ),
                disabled_1 => array(
                    type => 'INT',
                    label => 'Trạng thái',
                    value => 1
                )
            ));
        }
    }

}
