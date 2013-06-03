<?php

class HotelOrderController extends Zone_Action {

    const STATUS_PENDING = 0;
    const STATUS_ACCEPT = 1;
    const STATUS_CANCEL = 2;
    const STATUS_EDIT = 3;

    public function indexAction() {
        $hotel_id = get_hotel_id();
        loadClass('ZList');
        $list = new ZList();
        $list->setPageLink('#Hotel/Order');
        $list->setSqlCount("SELECT COUNT(*)
                            FROM `hotel_orders` as `a`
                             LEFT JOIN `hotel_room_types` as `c`
                                ON `c`.`ID` = `a`.`room_type_id`
                           ");
        $list->setSqlSelect("SELECT `a`.*,
                                    `c`.`title` AS `room_title`,`c`.`ID` AS `room_id`
                            FROM `hotel_orders` as `a`
                            LEFT JOIN `hotel_room_types` as `c`
                                ON `c`.`ID` = `a`.`room_type_id`
                           ");

        $list->setOrder("`a`.`status` ASC");

        $list->setWhere("`a`.`hotel_id` = '{$hotel_id}'");

        $list->setWhere("`a`.`is_last` = '1'");

        $list->addFieldOrder(array(
            '`a`.`root_id`' => 'root_id',
            '`a`.`amount`' => 'amount',
            '`a`.`date_start`' => 'date_start',
            '`a`.`date_end`' => 'date_end',
            '`a`.`customer_name`' => 'customer_name',
            '`room_title`' => 'room_title',
            '`a`.`status`' => 'status',
        ));
        $list->addFieldEqual(array(
            '`a`.`hotel_id`' => 'hotel_id',
            '`a`.`status`' => 'status',
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
        $room_types = self::$Model->fetchAll("
                SELECT * FROM `hotel_room_types`
                WHERE `hotel_id` = '{$hotel['ID']}'
            ");
        self::set('room_type_id', $room_types);

        $order_types = self::$Model->fetchAll("
                SELECT * FROM `order_types`
            ");
        self::set('order_type_id', $order_types);

        $countries = self::$Model->fetchAll('list_countries', "SELECT * FROM `locations` WHERE `type`='1' ORDER BY `title`");
        self::set('countries', $countries);

        $status = array(
            self::STATUS_PENDING => 'Đang chờ',
            self::STATUS_ACCEPT => 'Xác nhận',
            self::STATUS_CANCEL => 'Hủy bỏ',
                //self::STATUS_EDIT => 'Chờ xác nhận sửa',
        );
        self::set('status', $status);

        if (isPost()) {
            loadClass('ZData');
            $f = new ZData();
            $f->addfields(self::fields());
            $data = self::checkData($f->getData());

            if (!is_array($data)) {
                self::setJSON(array(
                    alert => error($data)
                ));
            } else {
                //Lấy KM có mức ưu tiên cao nhất
                $campain_highest = getCampaignHighest(date('Y-m-d', time()), $data['date_start'], $data['date_end'], $data['room_type_id'], $hotel['ID']);

                //check ke hoach gia
                $is_plan_price = true;
                for ($i = strtotime($data['date_start']); $i <= strtotime($data['date_end']); $i = $i + 86400) {
                    $arrayPriceDates = price_of_day(date('Y-m-d', $i), $data['room_type_id'], $hotel['ID'], $data['is_apply_campaign'], $campain_highest);
                    if ($arrayPriceDates['is_plan_price'] == 0) {
                        $is_plan_price = false;
                        break;
                    }
                }
                if ($is_plan_price == false) {
                    self::setJSON(array(
                        alert => error('Tồn tại ngày chưa có kế hoạch giá')
                    ));
                } else {

                    //luu hoa don va luu gia
                    $has_extrabed = $_POST['has_extrabed'];
                    $total_price_extrabed = 0;
                     $room_type = self::$Model->fetchRow(
                                "SELECT `a`.*
                                    FROM `hotel_room_types` as `a`
                                    WHERE `a`.`ID` = '{$data['room_type_id']}'
                                        and `a`.`hotel_id` = '{$hotel['ID']}'
                                    ");
                                        
                    if (isset($has_extrabed)) {
                        $data['has_extrabed'] = 1;
                        $data['extrabed_price'] = $room_type['extrabed_price'];
                        $total_price_extrabed = $data['extrabed_price'] * $data['amount']
                                * (int) ( (strtotime($data['date_end']) - strtotime($data['date_start'])) / 86400 + 1);
                    }
                    $data['currency_id'] = $room_type['currency_id'];
                    $data['created_by_id'] = get_user_id();
                    $data['date_created'] = new Model_Expr('NOW()');
                    $data['hotel_id'] = $hotel['ID'];
                    $data['is_last'] = 1;

                    self::$Model->insert('hotel_orders', $data);
                    //update root_id
                    $order_id = self::$Model->lastId();


                    $price_order = 0;
                    for ($i = strtotime($data['date_start']); $i <= strtotime($data['date_end']); $i = $i + 86400) {
                        $arrayPriceDates = price_of_day(date('Y-m-d', $i), $data['room_type_id'], $hotel['ID'], $data['is_apply_campaign'], $campain_highest);
                        $price_order += $arrayPriceDates['price_end'];

                        $data_price_date = array();
                        $data_price_date['price'] = round($arrayPriceDates['price_end'], 2);
                        $data_price_date['date'] = date('Y-m-d', $i);
                        $data_price_date['order_id'] = $order_id;
                        $data_price_date['currency_id'] = $room_type['currency_id'];
                        self::$Model->insert('order_price_dates', $data_price_date);
                    }

                    //update_hoa don
                    $total_price = $price_order*$data['amount'] + $total_price_extrabed;
                    $data_update = array('is_last' => 1, 'total_price' => $total_price, 'root_id' => $order_id);
                    self::$Model->update('hotel_orders', $data_update, "ID = '{$order_id}'");
                }
            }
            self::setJSON(array(
                redirect => "#Hotel/Order/View?ID=$order_id"
            ));
        }
    }

    public function editAction() {
        $hotel = get_hotel();
        $order_id = getInt('ID', 0);

        self::set('hotel', $hotel);
        $room_types = self::$Model->fetchAll("
                SELECT * FROM `hotel_room_types`
                WHERE `hotel_id` = '{$hotel['ID']}'
            ");
        self::set('room_type_id', $room_types);

        $order_types = self::$Model->fetchAll("
                SELECT * FROM `order_types`
            ");
        self::set('order_type_id', $order_types);

        $order_price_days = self::$Model->fetchAll("
                SELECT *
                FROM `order_price_dates`
                WHERE `order_id` = '{$order_id}'
                ORDER BY `date` ASC
            ");
        self::set('order_price_days', $order_price_days);

        $status = array(
            self::STATUS_PENDING => 'Đang chờ',
            self::STATUS_ACCEPT => 'Xác nhận',
            self::STATUS_CANCEL => 'Hủy bỏ',
                //self::STATUS_EDIT => 'Chờ xác nhận sửa',
        );
        self::set('status', $status);


        $post = self::$Model->fetchRow(" 
            SELECT `a`.*,`c`.`title` AS `room_title`,`c`.`ID` AS `room_id`,
                                    `d`.`title` AS `order_type_title`, `d`.`ID` AS `order_type_id`,
                                    `e`.`symbol`
                            FROM `hotel_orders` AS `a`
                            
                            LEFT JOIN `hotel_room_types` AS `c`
                                ON `c`.`ID` = `a`.`room_type_id`
                            LEFT JOIN `order_types` AS `d`
                                ON `a`.`order_type_id` = `d`.`ID`
                            LEFT JOIN `currencies` AS `e`
                                ON `a`.`currency_id` = `e`.`ID`
                            WHERE `a`.`ID` = '{$order_id}' AND `a`.`hotel_id` ='{$hotel['ID']}'
                           ");

        if (!$post || $post['status'] == self::STATUS_CANCEL || $post['is_last'] != 1) {
            self::setJSON(array(
                content => error(translate('default.edit.post_not_found'))
            ));
        }

        if ($post['location_id']) {
            $states = self::$Model->fetchAll("SELECT * FROM `locations` 
					WHERE `type`='2' AND `parent_id`='{$post['location_id']}' ORDER BY `title`");
            self::set('states', $states);
        }


        if ($post['state_id']) {
            $state = self::$Model->fetchAll("SELECT * FROM `locations` 
					WHERE `type`='2' AND `ID`='{$post['state_id']}'");
            if ($state) {
                $districts = self::$Model->fetchAll("SELECT * FROM `locations` 
						WHERE `type`='3' AND `parent_id`='{$post['state_id']}' ORDER BY `title`");
                self::set('districts', $districts);
            }
        }

        self::set('post', $post);
        if (isPost()) {

            loadClass('ZData');
            $f = new ZData();
            $f->addfields(self::fields($post));
            $data = self::checkData($f->getData());
            if (!is_array($data)) {
                self::setJSON(array(
                    alert => error($data)
                ));
            }

            $campain_highest = getCampaignHighest(date('Y-m-d', time()), $data['date_start'], $data['date_end'], $data['room_type_id'], $hotel['ID']);
            $is_plan_price = true;
            for ($i = strtotime($data['date_start']); $i <= strtotime($data['date_end']); $i = $i + 86400) {
                $arrayPriceDates = price_of_day(date('Y-m-d', $i), $data['room_type_id'], $hotel['ID'], $data['is_apply_campaign'], $campain_highest);
                if ($arrayPriceDates['is_plan_price'] == 0) {
                    $is_plan_price = false;
                    break;
                }
            }
            if ($is_plan_price == false) {
                self::setJSON(array(
                    alert => error('Tồn tại ngày chưa có kế hoạch giá')
                ));
            } else {
                //luu hoa don va luu gia
                $has_extrabed = $_POST['has_extrabed'];

                $total_price_extrabed = 0;
                $room_type = self::$Model->fetchRow(
                        "SELECT `a`.*
                                    FROM `hotel_room_types` as `a`
                                    WHERE `a`.`ID` = '{$data['room_type_id']}'
                                        and `a`.`hotel_id` = '{$hotel['ID']}'
                                    ");

                if (isset($has_extrabed)) {
                    $data['has_extrabed'] = 1;
                    $data['extrabed_price'] = $room_type['extrabed_price'];
                    $total_price_extrabed = $data['extrabed_price'] * $data['amount']
                            * (int) ( (strtotime($data['date_end']) - strtotime($data['date_start'])) / 86400 +1);
                }

                $data['currency_id'] = $room_type['currency_id'];
                $data['updated_by_id'] = get_user_id();
                $data['date_updated'] = new Model_Expr('NOW()');

                $data['created_by_id'] = get_user_id();
                $data['date_created'] = new Model_Expr('NOW()');

                $data['hotel_id'] = $hotel['ID'];
                $data['is_last'] = 1;

                $data['root_id'] = $post['root_id'];

                self::$Model->insert('hotel_orders', $data);
                $order_last = self::$Model->lastId();

                $price_order = 0;
                for ($i = strtotime($data['date_start']); $i <= strtotime($data['date_end']); $i = $i + 86400) {
                    $arrayPriceDates = price_of_day(date('Y-m-d', $i), $data['room_type_id'], $hotel['ID'], $data['is_apply_campaign'], $campain_highest);
                    $price_order += $arrayPriceDates['price_end'];

                    $data_price_date = array();
                    $data_price_date['price'] = $arrayPriceDates['price_end'];
                    $data_price_date['date'] = date('Y-m-d', $i);
                    $data_price_date['order_id'] = $order_last;
                    $data_price_date['currency_id'] = $room_type['currency_id'];
                    self::$Model->insert('order_price_dates', $data_price_date);
                }

                $total_price = $price_order*$data['amount'] + $total_price_extrabed;
                $data_update = array('is_last' => 1, 'total_price' => round($total_price, 2));
                self::$Model->update('hotel_orders', $data_update, "ID = '{$order_last}'");

                self::$Model->update('hotel_orders', array('is_last' => 0), "ID = '{$order_id}'");

                self::setJSON(array(
                    redirect => "#Hotel/Order/View?ID=$order_last"
                ));
            }
        }
    }

    protected function checkData($data) {
        if (!is_array($data)) {
            return $data;
        }
        if (strtotime($data['date_end']) < strtotime($data['date_start'])) {
            return '+) Ngày trả phòng không được nhỏ hơn ngày đặt phòng ';
        }
        if ((strtotime($data['date_end']) - strtotime($data['date_start'])) > 30 * 86400) {
            return '+) Không được đặt phòng quá 30 ngày ';
        }

        //Check phòng đóng trong khoảng thời gian check-in, check-out
        $hotel_id = get_hotel_id();
        $room_closes = getRoomCloseServices($data['date_start'], $data['date_end'], $data['room_type_id'], $hotel_id);
        if (isset($room_closes) && !empty($room_closes)) {
            return '+) Trong khoảng thời gian bạn book phòng, phòng đã bị khóa !';
        }

        //Check số phòng trống trong khoảng thời gian check-in, check-out
        $number_free = getRoomNumberFree($data['date_start'], $data['date_end'], $data['room_type_id'], $hotel_id, false);
        if ($data['amount'] > $number_free) {
            return '+) Số phòng book vượt quá số phòng còn trống !';
        }

        return $data;
    }

    public function viewAction() {

        $hotel = get_hotel();
        self::set('hotel', $hotel);
        $order_id = getInt('ID', 0);

        $post = self::$Model->fetchRow("SELECT `a`.*,
                                    `b`.`fullname`, `b`.`username` as `updated_by_name`,`b1`.`username` as `created_by_name`,
                                    `c`.`title` AS `room_title`,`c`.`ID` AS `room_id` ,
                                    `d`.`title` AS `order_type_title`,
                                    `e`.`symbol`
                            FROM `hotel_orders` as `a`
                            LEFT JOIN `users` as `b`
                                ON `a`.`created_by_id` = `b`.`ID`
                            LEFT JOIN `users` as `b1`
                                ON `a`.`created_by_id` = `b1`.`ID`
                            LEFT JOIN `hotel_room_types` as `c`
                                ON `c`.`ID` = `a`.`room_type_id`
                            LEFT JOIN `order_types` as `d`
                                ON `a`.`order_type_id` = `d`.`ID`
                            LEFT JOIN `currencies` as `e`
                                ON `c`.`currency_id` = `e`.`ID`
                            WHERE `a`.`ID` = '{$order_id}' AND `a`.`hotel_id` ='{$hotel['ID']}'
                           ");
        if (!$post || $post['is_last'] != 1) {
            self::setJSON(array(
                content => error(translate('default.edit.post_not_found'))
            ));
        }

        $status = array(
            self::STATUS_PENDING => 'Đang chờ',
            self::STATUS_ACCEPT => 'Xác nhận',
            self::STATUS_CANCEL => 'Hủy bỏ',
            self::STATUS_EDIT => 'Chờ xác nhận sửa'
        );

        self::set('status', $status);

        $order_price_days = self::$Model->fetchAll("
                SELECT *
                FROM `order_price_dates`
                WHERE `order_id` = '{$order_id}'
                ORDER BY `date` ASC
            ");

        self::set('order_price_days', $order_price_days);

        $orderOlds = getOldOrders($order_id);

        $districts = self::$Model->fetchOne("SELECT `title` FROM `locations` 
						WHERE `type`='3' AND `ID`='{$post['district_id']}' ORDER BY `title`");

        self::set(array(
            post => $post,
            districts => $districts,
            orderOlds => $orderOlds
        ));
    }

    public function deleteAction() {
        $ids = getInt('ID', array(), true);

        if (count($ids) > 0) {
            $cond = implode(',', $ids);
            self::$Model->delete('hotel_orders', "`ID` IN ($cond)");
            self::$Model->delete('order_price_dates', "`order_id` IN ($cond)");
        }
        self::setJSON(array(
            redirect => "#Hotel/Order"
        ));
    }

    protected function fields() {
        $data = array(
//            title => array(
//                type => 'CHAR',
//                no_empty => true,
//                label => translate('default.hotel.orders.title'),
//            ),
            desc => array(
                type => 'CHAR',
                label => translate('default.hotel.orders.desc'),
            ),
            room_type_id => array(
                type => 'INT',
                no_empty => true,
                label => translate('default.hotel.orders.room_type_id'),
            ),
            status => array(
                type => 'INT',
                label => translate('default.hotel.field.status'),
            ),
            amount => array(
                type => 'INT',
                no_empty => true,
                min=>0,
                label => translate('default.hotel.orders.amount'),
            ),
            date_start => array(
                type => 'DATE',
                no_empty => true,
                label => translate('default.hotel.orders.date_start'),
            ),
            date_end => array(
                type => 'DATE',
                no_empty => true,
                label => translate('default.hotel.orders.date_end'),
            ),
            is_apply_campaign => array(
                type => 'ENUM',
                no_empty => true,
                value => array('yes', 'no'),
                label => 'Áp dụng KM',
            ),
            customer_name => array(
                type => 'CHAR',
                no_empty => true,
                label => translate('default.hotel.orders.customer_name'),
            ),
            customer_email => array(
                type => 'EMAIL',
                no_empty => true,
                label => translate('default.hotel.orders.customer_email'),
            ),
            location_id => array(
                type => 'INT',
                no_empty => true,
                label => translate('default.hotel.orders.location_id'),
                error_format_message => 'Bạn phải chọn Quốc Gia'
            ),
            state_id => array(
                type => 'INT',
                no_empty => true,
                label => translate('default.hotel.orders.state_id'),
                error_format_message => 'Bạn phải chọn Tỉnh / Thành Phố'
            ),
            district_id => array(
                type => 'INT',
                no_empty => true,
                error_format_message => 'Bạn phải chọn Quận huyện'
            ),
            customer_address => array(
                type => 'CHAR',
                no_empty => true,
                label => translate('default.hotel.orders.customer_address'),
            ),
            customer_phone => array(
                type => 'CHAR',
                label => translate('default.hotel.orders.customer_phone'),
            ),
            status => array(
                type => 'INT',
                no_empty => true,
                label => translate('default.hotel.orders.status'),
            ),
            order_type_id => array(
                type => 'INT',
                no_empty => true,
                label => 'Hình thức đặt',
            ),
            extrabed_price => array(
                type => 'INT',
                label => 'Giá thêm giường',
            ),
        );
        return $data;
    }

    public function addbillAction() {
        self::removeLayout();
        $order_id = getInt('order_id');
        $number_date_add = getInt('number_date_add');
        $is_apply_campaign = get('is_apply_campaign');

        if ($number_date_add > 30) {
            die('Không được ở thêm quá 30 ngày');
        }
        $order = self::$Model->fetchRow("
                SELECT * FROM `hotel_orders`
                WHERE `ID` = '{$order_id}'
            ");

        $room_type = self::$Model->fetchRow("
                SELECT `a`.*,`b`.`symbol`
                FROM `hotel_room_types` as `a`
                LEFT JOIN `currencies` as `b`
                    ON `a`.`currency_id` = `b`.`ID`
                WHERE `a`.`ID` = '{$order['room_type_id']}'
            ");

        $type = get('type');
        self::set(array(
            order_id => $order_id,
            number_date_add => $number_date_add,
            type => $type,
            room_type => $room_type,
            order => $order,
            is_apply_campaign => $is_apply_campaign,
        ));
    }

    public function loadbillcurrentAction() {
        self::removeLayout();

        $date_start = change_date_format(get('date_start'));
        $date_end = change_date_format(get('date_end'));
        $room_type_id = getInt('room_type_id');
        $is_apply_campaign = get('is_apply_campaign');

        if (empty($date_start) || empty($date_end) || empty($room_type_id) || empty($is_apply_campaign)) {
            die('Khong thoa dieu kien');
        } else {
            $hotel_id = get_hotel_id();
            if (strtotime($date_end) < strtotime($date_start)) {
                die('Ngày trả phòng không được nhỏ hơn ngày đặt phòng ');
            }
            if ((strtotime($date_end) - strtotime($date_start)) > 30 * 86400) {
                die('Không được đặt phòng quá 30 ngày ');
            }
            $room_type = self::$Model->fetchRow("
                    SELECT `b`.`symbol`
                    FROM `hotel_room_types` as `a`
                    LEFT JOIN `currencies` as `b`
                        ON `a`.`currency_id` = `b`.`ID`
                    WHERE `a`.`ID` = '{$room_type_id}'
                ");

            $symbol = $room_type['symbol'];

            //Lấy KM có mức ưu tiên cao nhất
            $campain_highest = getCampaignHighest(date('Y-m-d', time()), $date_start, $date_end, $room_type_id, $hotel_id);
            
            $policy = getPolicyOrder($date_start, $date_end, $room_type_id, $hotel_id, $is_apply_campaign, $campain_highest);

//            $results = array();
//            $results['policy'] = $policy;
//            for ($i = strtotime($date_start); $i <= strtotime($date_end); $i = $i + 86400) {
//                 $arrayPriceDates = price_of_day(date('Y-m-d', $i), $room_type_id , $hotel_id,$is_apply_campaign,$campain_highest);
//                 $arrayPriceDates['symbol'] = $symbol;
//                 $results['arrayPriceDates'][date('Y-m-d',  $i)][] = $arrayPriceDates;
//            }
            self::set(array(
                date_start => $date_start,
                date_end => $date_end,
                room_type_id => $room_type_id,
                hotel_id => $hotel_id,
                is_apply_campaign => $is_apply_campaign,
                symbol => $symbol,
                campain_highest => $campain_highest
            ));
        }
    }

    public function loadbillAction() {
        self::removeLayout();
        if (isPost()) {
            $date_start = change_date_format(get('date_start'));
            $date_end = change_date_format(get('date_end'));
            $room_type_id = getInt('room_type_id');
            $order_id = getInt('order_id');
            $is_apply_campaign = get('is_apply_campaign');

            if (empty($date_start) || empty($date_end) || empty($room_type_id) || empty($is_apply_campaign)) {
                die;
            };
            if (strtotime($date_end) < strtotime($date_start)) {
                die('Ngày trả phòng không được nhỏ hơn ngày đặt phòng ');
            };
            if ((strtotime($date_end) - strtotime($date_start)) > 30 * 86400) {
                die('Không được đặt phòng quá 30 ngày ');
            };

            $room_type = self::$Model->fetchRow("
                    SELECT `a`.*, `b`.`symbol`
                    FROM `hotel_room_types` as `a`
                    LEFT JOIN `currencies` as `b`
                    ON `a`.`currency_id` = `b`.`ID`
                    WHERE `a`.`ID` = '{$room_type_id}'
            ");
            $order = self::$Model->fetchRow("
                        SELECT `a`.*
                        FROM `hotel_orders` as `a`
                        WHERE `a`.`ID` = '{$order_id}'
                    ");

            if ($type_change == 'PEDDING_NO_AUTO') {
                //check thay doi
                if ((strtotime($order['date_start']) == strtotime($date_start))
                        && (strtotime($order['date_end']) == strtotime($date_end))
                        && ($order['room_type_id'] == $room_type_id)) {
                    die('Không có thay đổi ');
                }
                //co thay doi
                else {
                    $arr_date_old = array();
                    $arr_date_new = array();

                    for ($i = strtotime($order['date_start']); $i <= strtotime($order['date_end']); $i = $i + 86400) {
                        $arr_date_old[] = date('Y-m-d', $i);
                    }

                    for ($i = strtotime($date_start); $i <= strtotime($date_end); $i = $i + 86400) {
                        $arr_date_new[] = date('Y-m-d', $i);
                    }
                    //lay nhung ngay can luu gia va load ra gia nhung ngay do
                    $array_save = array_intersect($arr_date_new, $arr_date_old);
                    /* $array_save_2 = array();
                      if (!empty($array_save)) {
                      foreach ($array_save as $key => $value) {
                      $array_save_2[$key] = "'" . $value . "'";
                      }
                      $array_save_str = implode(',', $array_save_2);
                      $price_saves = self::$Model->fetchAll("
                      SELECT *
                      FROM `order_price_dates`
                      WHERE `order_id` = '{$order_id}'
                      AND `date` IN ($array_save_str)
                      ");
                      }
                      //nhung ngay can cap nhat gia
                      // $arr_update = array_diff($arr_date_new, $arr_date_old);
                     */
                }
            } elseif ($type_change == 'PEDDING_AUTO') {
                $array_save = array();
            }
            self::set(array(
                date_start => $date_start,
                date_end => $date_end,
                order => $order,
                room_type => $room_type,
                array_save => $array_save,
                type_change => $type_change,
                is_apply_campaign => $is_apply_campaign,
            ));
        }
    }

    public function loadextrabedAction() {
        self::removeLayout();
        $room_type_id = getInt('room_type_id', 0);
        $room_type = self::$Model->fetchRow(
                "SELECT `a`.*,`b`.`title` as `currency_title` 
                    FROM `hotel_room_types` as `a`
                     LEFT JOIN `currencies` as `b`
                            ON `a`.`currency_id` = `b`.`ID`
                    WHERE `a`.`ID` = '{$room_type_id}'
                    ");
        if (empty($room_type)) {
            die('Hãy chọn loại phòng !');
        }
        self::set(array(
            room_type => $room_type,
        ));
    }

    public function acceptEditAction() {
        $ID = getInt('ID', 0);
        $status = getInt('status');
        $is_accept = getInt('is_accept');
        $post = self::$Model->fetchRow(
                "SELECT * 
                  FROM `hotel_orders` as `a`
                  WHERE `a`.`ID` = '{$ID}'
                      and `a`.`status` = '{$status}'
                  ");
        if (!$post) {
            self::setJSON(array(
                alert => error('Hóa đơn không tồn tại hoặc đã bị xóa !'),
            ));
        }

        $order_olds = self::$Model->fetchRow(
                "SELECT *
                    FROM `hotel_orders`
                    WHERE `root_id` = '{$post['root_id']}'
                        and `ID`!= '{$ID}'
                    ORDER BY `date_updated` DESC
                    ");

        if (!$order_olds) {
            self::setJSON(array(
                alert => error('Hóa đơn không tồn tại hoặc đã bị xóa !'),
            ));
        }

        if ($is_accept == 1) {
            $data = array('status' => $order_olds['status']);
            self::$Model->update('hotel_orders', $data, " ID = '{$ID}'");
        } elseif ($is_accept == 0) {
            self::$Model->delete('hotel_orders', " ID = '{$ID}'");

            $data = array('is_last' => 1);
            self::$Model->update('hotel_orders', $data, " ID = '{$order_olds['ID']}'");
        }

        self::setJSON(array(
            redirect => "#Hotel/Order"
        ));
    }

    public function acceptOrderAction() {
        $ID = getInt('ID', 0);
        $post = self::$Model->fetchRow(
                "SELECT * 
                  FROM `hotel_orders` as `a`
                  WHERE `a`.`ID` = '{$ID}'
                      and `a`.`status` = '0'
                  ");
        if (!$post) {
            self::setJSON(array(
                alert => error('Hóa đơn không tồn tại hoặc đã bị xóa !'),
            ));
        }
        $data['status'] = 1;
        $data['updated_by_id'] = get_user_id();
        $data['date_updated'] = new Model_Expr('NOW()');
        self::$Model->update('hotel_orders', $data, " ID = '$ID'");
        self::setJSON(array(
            redirect => "#Hotel/Order"
        ));
    }
    public function cancelOrderAction(){
        $ID = getInt('ID', 0);
        $post = self::$Model->fetchRow(
                "SELECT * 
                  FROM `hotel_orders` as `a`
                  WHERE `a`.`ID` = '{$ID}'
                      and `a`.`status` = '0'
                  ");
        if (!$post) {
            self::setJSON(array(
                alert => error('Hóa đơn không tồn tại hoặc đã bị xóa !'),
            ));
        }
        $data['status'] = 2;
        $data['updated_by_id'] = get_user_id();
        $data['date_updated'] = new Model_Expr('NOW()');
       
        self::$Model->update('hotel_orders', $data, " ID = '$ID'");
        self::setJSON(array(
            redirect => "#Hotel/Order"
        ));
    }

}
