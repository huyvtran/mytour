<?php

class ServiceHotelController extends Zone_Action {

    public function indexAction() {
        self::removeLayout();
        $soap_xml = currentDomain() . baseUrl() . '/Service/Hotel/Soap';
        $sv = new SoapServer($soap_xml);
        $sv->setClass(get_class($this));
        $sv->handle();
        exit;
    }

    public function soapAction() {
        self::removeLayout();
        $domain = currentDomain() . baseUrl() . '/Service/Hotel';
        $xml_content = file_get_contents(dirname(__FILE__) . '/soap-xml/hotel.wsdl');
        $xml_content = preg_replace("#_URL_#is", $domain, $xml_content);
        header('Content-Type:text/xml');
        header('Cache-Control: max-age=0');
        _e($xml_content);
        exit;
    }

    public function getList($page_range = 15, $current_page = 1, $sort_field = 'title', $sort_type = 'asc') {
        try {
            loadClass('ZList');
            $list = new ZList();
            $list->setPageLink('#Hotel');
            $list->setSqlCount("SELECT COUNT(*) FROM `hotels` as `a`");
            $list->setSqlSelect("SELECT
                `a`.`ID`,
                `a`.`title`,
                `a`.`address`,
                `a`.`img`,
                `a`.`star`
                FROM `hotels` as `a`");

            $orders = array(
                title => '`a`.`title`'
            );

            $sort_type = $sort_type == 'desc' ? $sort_type : 'asc';
            if (array_key_exists($sort_field, $orders)) {
                $f = $orders[$sort_field];
                $list->setOrder("$f $sort_type");
            }

            $list->setWhere("`a`.`is_active`='1'");
            $list->setCurrentPage($current_page);
            $list->setPageRange($page_range);

            $list->addFieldOrder(array(
                '`a`.`title`' => 'title',
                '`a`.`is_active`' => 'is_active',
            ));

            $list->run();
            $hotels = $list->getPosts();
            $total_page = $list->getTotalPage();
            $current_page = $list->getCurrentPage();

            foreach ($hotels as $k => $a) {
                $hotels[$k]['img'] = currentDomain() . baseUrl() . "/files/hotel/{$a['img']}";
            }

            $result = array(
                hotels => $hotels,
                current_page => $current_page,
                total_page => $total_page
            );
            return json_encode($result);
        } catch (Exception $e) {
            return 'Server is busy'; //$e->getMessage();
        }
    }

    public function getRoomType($hotel_id, $page_range = 4, $current_page = 1, $sort_field = 'title', $sort_type = 'asc') {
        try {
            $hotel = self::$Model->fetchRow("SELECT `a`.*
                FROM `hotels` as `a`
                    WHERE `a`.`ID`='$hotel_id' AND `a`.`is_active`='1'");

            if (!$hotel) {
                return json_encode(array(
                            status => 203,
                            status_text => 'Non-Authoritative Information',
                            message => 'Khách sạn không tồn tại'
                        ));
            }

            loadClass('ZList');
            $list = new ZList();
            $list->setPageLink('#Hotel');
            $list->setSqlCount("SELECT COUNT(*) FROM `hotel_room_types` as `a`");
            $list->setSqlSelect("SELECT
                `a`.`ID`,
                `a`.`title`,
                `a`.`size_people`,
                `a`.`img`,
                `a`.`price`,
                `a`.`area`
                FROM `hotel_room_types` as `a`");

            $orders = array(
                title => '`a`.`title`'
            );

            $sort_type = $sort_type == 'desc' ? $sort_type : 'asc';
            if (array_key_exists($sort_field, $orders)) {
                $f = $orders[$sort_field];
                $list->setOrder("$f $sort_type");
            }

            $list->setWhere("`a`.`hotel_id`='$hotel_id'");
            $list->setCurrentPage($current_page);
            $list->setPageRange($page_range);

            $list->addFieldOrder(array(
                '`a`.`title`' => 'title'
            ));

            $list->run();
            $room_types = $list->getPosts();
            $total_page = $list->getTotalPage();
            $current_page = $list->getCurrentPage();

            foreach ($room_types as $k => $a) {
                if (isset($a['img'])) {
                    $room_types[$k]['img'] = currentDomain() . baseUrl() . "/files/rooms/{$a['img']}";
                } else {
                    $room_types[$k]['img'] = currentDomain() . baseUrl() . "/files/photo/noavatar.gif";
                }
            }


            $result = array(
                room_types => $room_types,
                current_page => $current_page,
                total_page => $total_page
            );
            return json_encode($result);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function getInfo($hotel_id) {
        $hotel = self::$Model->fetchRow("SELECT
                `a`.*,
                `b`.`title` as `type_title`,
                `c`.`title` as `country_title`,
                `d`.`title` as `state_title`
             FROM `hotels` as `a`
                LEFT JOIN `hotel_types` as `b`
                    ON `a`.`type_id` = `b`.`ID`
                LEFT JOIN `locations` as `c`
                    ON `a`.`country_id` = `c`.`ID`
                LEFT JOIN `locations` as `d`
                    ON `a`.`state_id` = `d`.`ID`
                WHERE `a`.`ID`='$hotel_id'");

        if (!$hotel)
            return false;

        $hotel['img'] = currentDomain() . baseUrl() . "/files/hotel/{$hotel['img']}";
        $hotel['activities'] = self::$Model->fetchAll("SELECT
                `a`.*,
                IF(`b`.`hotel_id`,' checked','') as `checked`
             FROM `hotel_activity_types` as `a`
                LEFT JOIN `hotel_activities` as `b`
                    ON `a`.`ID`=`b`.`service_id`
                        AND `b`.`hotel_id`='$hotel_id'");

        $hotel['facilities'] = self::$Model->fetchAll("SELECT
                `a`.*,
                IF(`b`.`hotel_id`,' checked','') as `checked`
             FROM `hotel_facility_types` as `a`
                LEFT JOIN `hotel_facilities` as `b`
                    ON `a`.`ID`=`b`.`service_id`
                        AND `b`.`hotel_id`='$hotel_id'");

        $hotel['services'] = self::$Model->fetchAll("SELECT
                `a`.*,
                IF(`b`.`hotel_id`,' checked','') as `checked`
             FROM `hotel_service_types` as `a`
                LEFT JOIN `hotel_services` as `b`
                    ON `a`.`ID`=`b`.`service_id`
                        AND `b`.`hotel_id`='$hotel_id'");

        return json_encode($hotel);
    }

    public function getInfoRoomType($hotel_id, $room_type_id) {
        $room_type = self::$Model->fetchRow("
            SELECT `a`.*,`c`.`title` as `currency_title`,
                    `a`.`number` - ( SELECT COUNT(*) FROM `hotel_orders` as `k`
                                       WHERE `k`.`room_type_id`=`a`.`ID` AND `k`.`status` != '2' ) 
                           as `number_free`
                FROM `hotel_room_types` AS `a`
                LEFT JOIN `hotels` AS `b`
                    ON `a`.`hotel_id` = `b`.`ID`
                LEFT JOIN `currencies` as `c`
                    ON `a`.`currency_id` = `c`.`ID`
                WHERE `a`.`ID`='$room_type_id'
                    AND `b`.`ID` = '{$hotel_id}'
              ");

        if (!$room_type)
            return false;

        $room_type['img'] = currentDomain() . baseUrl() . "/files/rooms/{$room_type['img']}";

        $room_type['services'] = self::$Model->fetchAll("SELECT
                `a`.*,
                IF(`b`.`room_id`,' checked','') as `checked`
             FROM `hotel_room_service_types` as `a`
                LEFT JOIN `hotel_room_services` as `b`
                    ON `a`.`ID`=`b`.`service_id` AND `b`.`room_id`='$room_type_id'");

        $room_type['rules_checked'] = self::$Model->fetchAll("
                SELECT `a`.`ID`
                FROM `hotel_rules` as `a`
                LEFT JOIN `hotel_room_rules` as `b`
                    ON `a`.`ID` = `b`.`rule_id`
                LEFT JOIN `hotel_room_types` as `c`
                    ON `b`.`room_type_id` = `c`.`ID`
                 WHERE `c`.`hotel_id` = '{$hotel_id}' and `b`.`room_type_id` ='{$room_type_id}'
            ");

        if (count($room_type['rules_checked']) > 0) {
            $rules_checked = array();
            foreach ($room_type['rules_checked'] as $a) {
                $rules_checked[] = $a['ID'];
            };
        }

        $room_type['rules_all'] = self::$Model->fetchAll(
                "SELECT `ID`,`title` FROM `hotel_rules`
                 WHERE `hotel_id` = '{$hotel_id}'
                    ");
        return json_encode($room_type);
    }

    public function getFeedback($hotel_id, $customer_id, $feedback) {
                
        $data = array(
            hotel_id => $hotel_id,
            customer_id => $customer_id,
            time => date('Y-m-d H:i:s'),
            comment => $feedback,
            user_id => 0,
            status => 0
        );

        self::$Model->insert('comments', $data);
        $last_feed_id = self::$Model->lastId();
        self::$Model->update('comments', array(
            root => $last_feed_id
                ), "`ID`='$last_feed_id'");


        $feed = self::$Model->fetchRow("
            SELECT `a`.*, `b`.`fullname` as `fullname`
            FROM `comments` as `a`
            LEFT JOIN `customers` as `b`
            ON `a`.`customer_id` = `b`.`ID`
            WHERE `a`.`customer_id` <> 0 AND `a`.`ID`= '$last_feed_id'
            ");

        return json_encode($feed);
    }

    public function getAllFeedback($hotel_id) {

        $feed = self::$Model->fetchAll("
            SELECT `a`.*, 
            `b`.`fullname` as `customer_name`,
            `c`.`fullname` as `user_fullname`
            FROM `comments` as `a`
            LEFT JOIN `customers` as `b`
            ON `a`.`customer_id` = `b`.`ID`
            LEFT JOIN `users` as `c`
            ON `a`.`user_id` = `c`.`ID`
            WHERE `a`.`hotel_id` = '$hotel_id'
            ORDER BY `a`.`ID` DESC
            ");
        return json_encode($feed);
    }

    public function getRulePriceInfo($hotel_id, $rule_id) {
        $rule_price = self::$Model->fetchRow(
                "SELECT `a`.*,`b`.*,`c`.`hotel_id`,
                     IF(`d`.`ID`,`d`.`title`,'%') as `title_currency`
                        FROM `hotel_rules` AS `a`
                            LEFT JOIN `hotel_room_rules` AS `b`
                                ON `a`.`ID` = `b`.`rule_id`
                            LEFT JOIN `hotel_room_types` AS `c`
                                ON `b`.`room_type_id` = `c`.`ID`
                            LEFT JOIN `currencies` as `d`
                                ON `a`.`currency_id` = `d`.`ID`
                        WHERE `a`.`ID` = '{$rule_id}'
                                AND `a`.`hotel_id` = '{$hotel_id}'
                        GROUP BY `c`.`hotel_id` 
                    ");
        if (!$rule_price) {
            return json_encode(array(
                        status => 203,
                        status_text => 'Non-Authoritative Information',
                        message => 'Không tồn tại Quản lý giá'
                    ));
        }

        $rule_price['room_types'] = self::$Model->fetchAll(
                "SELECT `a`.`ID`,`a`.`title`,
                                  IF(`b`.`rule_id`,' checked','') as `checked`
                               FROM `hotel_room_types` AS `a`
                               LEFT JOIN `hotel_room_rules` AS `b`
                                    ON `a`.`ID` = `b`.`room_type_id` AND `b`.`rule_id` = '{$rule_id}'
                               WHERE `a`.`hotel_id` = '{$hotel_id}' ");
        return json_encode($rule_price);
    }

    public function findRoomTypeHotel($hotel_id, $date_start, $date_end) {

        if (empty($date_start) || empty($date_end)) {
            return json_encode(array(
                        status => 203,
                        status_text => 'Non-Authoritative Information',
                        message => 'Ngày b?t d?u ho?c ngày k?t thúc không du?c r?ng'
                    ));
        }
        if (strtotime($date_start) > strtotime($date_end)) {
            return json_encode(array(
                        status => 203,
                        status_text => 'Non-Authoritative Information',
                        message => 'Ngày b?t d?u không du?c nh? hon ngày k?t thúc'
                    ));
        }
        if (((strtotime($date_end) - strtotime($date_start)) / 86400) > 30) {
            return json_encode(array(
                        status => 203,
                        status_text => 'Non-Authoritative Information',
                        message => 'Không du?c d?t phòng quá 30 ngày'
                    ));
        }

        $date_start = date('Y-m-d', strtotime($date_start));
        $date_end = date('Y-m-d', strtotime($date_end));

        //L?y lo?i phòng b? dóng trong kho?ng th?i gian này
        $room_type_closes = self::$Model->fetchAll(
                "SELECT *
                    FROM `close_services` AS `a`
                    WHERE `a`.`hotel_id` = '14'
                        AND (`a`.`date` BETWEEN '{$date_start}' AND  '{$date_end}' )
                        AND `a`.`no_room_type` = '1'
                    GROUP BY `a`.`room_type_id`
                    ");
        $close_ids = array();
        $sql = '';
        if (!empty($room_type_closes)) {
            foreach ($room_type_closes as $value) {
                $close_ids[] = $value['room_type_id'];
            }
        }
        if (!empty($close_ids)) {
            $str_close_ids = implode(',', $close_ids);
            $sql = "and `a`.`ID` NOT IN ($str_close_ids)";
        }

        $room_types_tmp = self::$Model->fetchAll("
                SELECT `a`.*,`c`.`title` AS `currency_title`,
                    `a`.`number`-SUM( IFNULL(`b`.`amount`,0)) AS `number_free`          
                FROM `hotel_room_types` AS `a`             
                LEFT JOIN `hotel_orders` AS `b`
                        ON `b`.`room_type_id`=`a`.`ID` 
                        AND `b`.`status` != '2'
                        AND NOT(`b`.`date_end` < '{$date_start}' OR `b`.`date_start` > '{$date_end}' ) 
                LEFT JOIN `currencies` as `c`
                    ON `a`.`currency_id` = `c`.`ID`
                WHERE `a`.`hotel_id` = '{$hotel_id}'
                   " . $sql . " 
                GROUP BY `a`.`ID`
        ");

        //Lay Loai phong co so phong trong > 0
        $room_types = array();
        if (($room_types_tmp) && count($room_types_tmp) > 0) {
            foreach ($room_types_tmp as $k => $room_type) {
                if ($room_type['number_free'] > 0) {
                    $room_types[] = $room_type;
                }
            }
        }

        $list_rooms = array();
        if (($room_types) && count($room_types) > 0) {

            foreach ($room_types as $k => $room_type) {
                $check = true;
                $price_avg_yes_campaign = 0;
                $price_avg_no_campaign = 0;

                for ($i = strtotime($date_start); $i <= strtotime($date_end); $i = $i + 86400) {
                    $date_i = date('Y-m-d', $i);

                    $arrayPriceDates = price_of_day($date_i, $room_type['ID'], $hotel_id, 'yes');
                    $arrayPriceDatesNo = price_of_day($date_i, $room_type['ID'], $hotel_id, 'no');

                    $price_avg_yes_campaign = $price_avg_yes_campaign + $arrayPriceDates['price_end'];
                    $price_avg_no_campaign = $price_avg_no_campaign + $arrayPriceDatesNo['price_end'];

                    if ($arrayPriceDates['is_plan_price'] == 0) {
                        $check = false;
                    }
                }
                if ($check == true) {
                    $room_types[$k]['img'] = currentDomain() . baseUrl() . "/files/rooms/{$room_type['img']}";
                    $room_types[$k]['price_avg_yes_campaign'] = $price_avg_yes_campaign / ((strtotime($date_end) - strtotime($date_start)) / 86400 + 1);
                    $room_types[$k]['price_avg_no_campaign'] = $price_avg_no_campaign / ((strtotime($date_end) - strtotime($date_start)) / 86400 + 1);
                    $list_rooms[] = $room_types[$k];
                };
            }
        }

        $list_room_results = array();
        if (isset($list_rooms) && count($list_rooms) > 0) {
            $i = 0;
            foreach ($list_rooms as $k => $value) {
                $list_room_results[$k + $i] = $value;
                $list_room_results[$k + $i]['is_apply_campaign'] = 'no';
                $list_room_results[$k + $i]['price_avg'] = round($value['price_avg_no_campaign']);
                unset($list_room_results[$k + $i]['price_avg_yes_campaign']);

                $list_room_results[$k + $i + 1] = $value;
                $list_room_results[$k + $i + 1]['is_apply_campaign'] = 'yes';
                $list_room_results[$k + $i + 1]['price_avg'] = round($value['price_avg_yes_campaign']);
                unset($list_room_results[$k + $i + 1]['price_avg_no_campaign']);

                $i++;
            }
        }

        return json_encode($list_room_results);
    }

    /*
     * Gia lap trang thai payment
     */

    public function sendPayment($status) {
        //0 : gap loi khi thanh toan
        //1 : thanh toan thanh cong
        if ($status == 0) {
            json_encode(array(
                status => 203,
                status_text => 'Non-Authoritative Information',
                message => 'Gặp lỗi trong quá trình thanh toán !'
            ));
        } elseif ($status == 1) {
            json_encode(array(
                status => 202,
                status_text => 'Accept',
                message => 'Thanh toán thành công !'
            ));
        }
    }

    /**
     * Book a room_type
     */
    public function bookRoom($title, $desc, $hotel_id, $room_type_id, $amount, $date_start, $date_end, $customer_name, $customer_email, $customer_address, $customer_phone, $location_id, $state_id, $has_extrabed, $order_type_id, $is_apply_campaign, $customer_id, $total_price, $date_created, $date_updated, $root_id, $is_last,$payment_status, $status) {

        //check dữ liệu
        $hotel = self::$Model->fetchRow("SELECT `a`.*
            FROM `hotels` as `a`
                WHERE `a`.`ID`='{$hotel_id}' AND `a`.`is_active`='1'");


        if (!isset($hotel)) {
            return json_encode(array(
                        status => 203,
                        status_text => 'Non-Authoritative Information',
                        message => 'Khách sạn không tồn tại',
                        last_order_id => null
                    ));
        }
        $date_start = date('Y-m-d', strtotime($date_start));
        $date_end = date('Y-m-d', strtotime($date_end));
        //check room free
        $room_types = self::$Model->fetchRow(
                "SELECT `a`.*,`c`.`title` AS `currency_title`,
                    `a`.`number`-SUM( IFNULL(`b`.`amount`,0)) AS `number_free`          
                FROM `hotel_room_types` AS `a`             
                LEFT JOIN `hotel_orders` AS `b`
                        ON `b`.`room_type_id`=`a`.`ID` 
                        AND `b`.`status`='1'
                        AND NOT(`b`.`date_end` < '{$date_start}' OR `b`.`date_start` > '{$date_end}' ) 
                             and `b`.`is_last` = '1'
                LEFT JOIN `currencies` as `c`
                    ON `a`.`currency_id` = `c`.`ID`
                WHERE `a`.`hotel_id` = '{$hotel_id}' and `a`.`ID` = '{$room_type_id}'
                GROUP BY `a`.`ID`
        ");
        if (!isset($room_types)) {
            return json_encode(array(
                        status => 203,
                        status_text => 'Non-Authoritative Information',
                        message => 'Loại phòng không tồn tại',
                        last_order_id => null
                    ));
        }

        if ($amount > $room_types['number_free']) {
            return json_encode(array(
                        status => 203,
                        status_text => 'Non-Authoritative Information',
                        message => 'Không đủ số phòng cho loại phòng bạn đặt',
                        last_order_id => null
                    ));
        }

        $arrInsert = array(
            title => $title,
            desc => $desc,
            hotel_id => $hotel_id,
            room_type_id => $room_type_id,
            amount => $amount,
            date_start => $date_start,
            date_end => $date_end,
            customer_name => $customer_name,
            customer_email => $customer_email,
            customer_address => $customer_address,
            customer_phone => $customer_phone,
            location_id => $location_id,
            state_id => $state_id,
            has_extrabed => $has_extrabed,
            order_type_id => $order_type_id,
            is_apply_campaign => $is_apply_campaign,
            date_created => date("Y-m-d"),
            customer_id => $customer_id,
            total_price => $total_price,
            date_created => $date_created,
            date_updated => $date_updated,
            root_id => $root_id,
            is_last => 1,
            payment_status => $payment_status,
            status => $status,
        );
        return json_encode($status);
        
        $bool = self::$Model->insert('hotel_orders', $arrInsert);

        $lastID = self::$Model->lastId();

        if (isset($bool)) {

            $order_id = self::$Model->lastId();
            for ($i = strtotime($date_start); $i <= strtotime($date_end); $i = $i + 86400) {
                $arrayPriceDates = price_of_day(date('Y-m-d', $i), $room_type_id, $hotel_id, $is_apply_campaign);
                $data_price_date = array();
                $data_price_date['price'] = $arrayPriceDates['price_end'];
                $data_price_date['date'] = date('Y-m-d', $i);
                $data_price_date['order_id'] = $order_id;
                self::$Model->insert('order_price_dates', $data_price_date);
            }
            return
                    json_encode(array(
                        status => 202,
                        status_text => 'Accepted',
                        message => 'Đã đặt thành công',
                        last_order_id => $lastID
                    ));
        } else {
            return
                    json_encode(array(
                        status => 204,
                        status_text => 'No Content',
                        message => 'Có lỗi xảy ra từ hệ thống',
                        last_order_id => null
                    ));
        }
    }

    public function getCountries() {
        $countries = self::$Model->fetchAll("SELECT * FROM `locations`
            WHERE `type`='1' ORDER BY `title`");
        return json_encode($countries);
    }

    public static function getDefaultStates() {
        $states = self::$Model->fetchAll("SELECT * FROM `locations`
            WHERE `parent_id`='1' ORDER BY `title`");
        return json_encode($states);
    }

//    //Gia cua ngay do trong bang price_of_dates
//    function order_price_dates($date_input, $order_id) {
//        $price = Zone_Base::$Model->fetchOne(
//                "SELECT `price`
//           FROM `order_price_dates`
//           WHERE `date` = '{$date_input}'
//               AND `order_id` = '{$order_id}'
//           ");
//        return $price;
//    }

    /*
     * Tính giá của ngày đó
     */
    function priceOfDate($date_input, $room_type_id, $hotel_id, $is_apply_campaign) {
        //gia fix cung cua phong do
        $room = Zone_Base::$Model->fetchRow("
                SELECT * 
                FROM `hotel_room_types`
                WHERE `ID` = '{$room_type_id}'
            ");
        $room_price_fix = $room['price'];

        //Kiem tra ngay co kế hoach giá chưa
        $is_plan_price = 1; //mac dinh da co ke hoach gia
        //tinh thu cua ngay nay
        $jd = cal_to_jd(CAL_GREGORIAN, date('m', strtotime($date_input)), date('d', strtotime($date_input)), date('Y', strtotime($date_input)));
        $thu_of_date = jddayofweek($jd, 0);

        //KM có mức ưu tiên cao nhất trong ngày này
        $campaign_of_day = Zone_Base::$Model->fetchRow("
                SELECT a.*, `c`.`title` as `room_type_title`,
                       `d`.`date_remove_start`,`d`.`date_remove_end`
                FROM `hotel_campaigns` as `a`
                LEFT JOIN `hotel_campaign_room_types` as `b`
                    ON `a`.`ID` = `b`.`campaign_id`
                LEFT JOIN `hotel_room_types` as `c`
                    ON `b`.`room_type_id` = `c`.`ID`
                LEFT JOIN `campaign_date_removes` as `d`
                    ON `a`.`ID` = `d`.`campaign_id`              
                WHERE  `a`.`hotel_id` = '{$hotel_id}'
                       AND `c`.`ID` = '{$room_type_id}'
                       AND (`a`.`date_start` <= '{$date_input}' AND `a`.`date_end` >= '{$date_input}')
                       AND (`d`.`date_remove_start` > '{$date_input}' OR `d`.`date_remove_end` < '{$date_input}')
                       AND ( (CONCAT(',',`a`.`days`,',')  LIKE '%{$thu_of_date}%' ) )   
                GROUP BY `a`.`ID`
                ORDER BY `a`.`priority` DESC
            ");

        //RBG có mức ưu tiên cao nhất trong ngày này
        $all_rule_days = Zone_Base::$Model->fetchAll("
                SELECT `a`.*
                FROM `hotel_rules` as `a`
                LEFT JOIN `hotel_room_rules` as `b`
                    ON `a`.`ID` = `b`.`rule_id`
                  LEFT JOIN `hotel_room_types` as `c`
                    ON `b`.`room_type_id` = `c`.`ID`
                WHERE `a`.`hotel_id` = '{$hotel_id}'
                      AND `c`.`ID` = '{$room_type_id}'
                      AND (`a`.`date_start` <= '{$date_input}' AND `a`.`date_end` >= '{$date_input}')
                      AND ( (CONCAT(',',`a`.`days`,',')  LIKE '%{$thu_of_date}%' ) )   
                 GROUP BY `a`.`ID`
                ORDER BY `a`.`priority` DESC
            ");

        $rule_of_day = array();
        if ($all_rule_days && count($all_rule_days) > 0) {
            foreach ($all_rule_days as $value) {
                if ($value['sign'] == '=') {
                    $rule_of_day = $value;
                    break;
                }
            }
            if (empty($rule_of_day)) {
                $rule_of_day = $all_rule_days[0];
            }
        }

        /*
         *  Neu RBG co dau = thi tinh RBG->KM .  Nguoc lai thi tinh KM->RBG
         * 
         */
        //1. Ton tai RBG
        if (!empty($rule_of_day)) {
            //Ton tai RBG =
            if ($rule_of_day['sign'] == '=') {
                //RBG = va ton tai KM
                if (!empty($campaign_of_day)) {
                    $price_of_campaign = convertCurrencies($campaign_of_day['sign'], $campaign_of_day['currency_id'], $campaign_of_day['value'], $rule_of_day['currency_id']);

                    if ($is_apply_campaign == 'yes') {
                        $price_end = $rule_of_day['value'] + $price_of_campaign;
                    } elseif ($is_apply_campaign == 'no') {
                        $price_end = $rule_of_day['value'];
                    }
                }
                //RBG = va khong ton tai KM
                else {
                    $price_end = $rule_of_day['value'];
                }
            }
            //Ton tai RBG va khac =
            else {
                //RBG khac  = va ton tai KM
                if (!empty($campaign_of_day)) {
                    $price_of_campaign = convertCurrencies($campaign_of_day['sign'], $campaign_of_day['currency_id'], $campaign_of_day['value'], $room['currency_id']);
                    $price_of_rule = convertCurrencies($rule_of_day['sign'], $rule_of_day['currency_id'], $rule_of_day['value'], $room['currency_id']);
                    $price_end = $room_price_fix + $price_of_campaign + $price_of_rule;
                    if ($is_apply_campaign == 'yes') {
                        $price_end = $room_price_fix + $price_of_campaign + $price_of_rule;
                    } else {
                        $price_end = $room_price_fix + $price_of_rule;
                    }
                }
                //RBG khac  = va khong ton tai KM
                else {
                    $price_of_rule = convertCurrencies($rule_of_day['sign'], $rule_of_day['currency_id'], $rule_of_day['value'], $room['currency_id']);
                    $price_end = $room_price_fix + $price_of_rule;
                }
            }
        }
        // 2. Khong ton tai RBG
        if (empty($rule_of_day)) {
            //Khong ton tai RBG va ton tai Km
            if (!empty($campaign_of_day)) {
                $price_of_campaign = convertCurrencies($campaign_of_day['sign'], $campaign_of_day['currency_id'], $campaign_of_day['value'], $room['currency_id']);
                if ($is_apply_campaign == 'yes') {
                    $price_end = $room_price_fix + $price_of_campaign;
                } else {
                    $price_end = $room_price_fix;
                }
            }
            //Khong ton tai RBG va khong ton tai Km 
            else {
                $price_end = $room_price_fix;
                $is_plan_price = 0;
            }
        }

        $arrayPriceDates = array(
            'price_end' => round($price_end),
            'is_plan_price' => $is_plan_price,
            'rule_of_day' => $rule_of_day,
            'campaign_of_day' => $campaign_of_day,
        );
        return json_encode($arrayPriceDates);
    }

    public function orderPendings($date_start, $date_end, $time, $number, $room_type_id, $hotel_id, $ip) {

        if (empty($date_start) || empty($date_end)) {
            return json_encode(array(
                        status => 203,
                        status_text => 'Non-Authoritative Information',
                        message => 'Ngày bắt đầu hoặc ngày kết thúc không được rỗng'
                    ));
        }
        $date_start = date('Y-m-d', strtotime($date_start));
        $date_end = date('Y-m-d', strtotime($date_end));

        if (strtotime($date_start) > strtotime($date_end)) {
            return json_encode(array(
                        status => 203,
                        status_text => 'Non-Authoritative Information',
                        message => 'Ngày bắt đầu không được nhỏ hơn ngày kết thúc'
                    ));
        }
        if (((strtotime($date_end) - strtotime($date_start)) / 86400) > 30) {
            return json_encode(array(
                        status => 203,
                        status_text => 'Non-Authoritative Information',
                        message => 'Không được đặt phòng quá 30 ngày'
                    ));
        }

        $arrInsert = array(
            'date_start' => $date_start,
            'date_end' => $date_end,
            'time' => $time,
            'number' => $number,
            'room_type_id' => $room_type_id,
            'hotel_id' => $hotel_id,
            'ip' => $ip,
        );
        $bool = self::$Model->insert('order_pendings', $arrInsert);
        if (isset($bool)) {
            return
                    json_encode(array(
                        status => 202,
                        status_text => 'Accepted',
                    ));
        } else {
            return
                    json_encode(array(
                        status => 204,
                        status_text => 'No Content',
                        message => 'Có lỗi xảy ra từ hệ thống'
                    ));
        }
    }

    /*
     * Service tính số phòng trống trong một khoảng thời gian
     * Áp dụng cho người đặt phòng từ website khách sạn (chưa tính allotment của đối tác)
     */

    public function getRoomNumberFree($date_start, $date_end, $room_type_id, $hotel_id, $allotment = false) {
        if ($allotment == false) {
            $number_free = self::$Model->fetchOne("
                    SELECT `a`.`number`-SUM( IFNULL(`b`.`amount`,0)) AS `number_free`          
                    FROM `hotel_room_types` AS `a`             
                    LEFT JOIN `hotel_orders` AS `b`
                            ON `b`.`room_type_id`=`a`.`ID` 
                            AND `b`.`status` != '2'
                            AND NOT(`b`.`date_end` < '{$date_start}' OR `b`.`date_start` > '{$date_end}' ) 
                            AND `b`.`is_last` = '1'
                    WHERE `a`.`hotel_id` = '{$hotel_id}' and `a`.`ID` = '{$room_type_id}'
                    GROUP BY `a`.`ID`
            ");
            return json_encode($number_free);
        }
    }

    /*
     * Service lay ra thoi gian  : Độ trễ book phòng trong configs chung của hệ thống
     */

    public function getBookLatencyConfig() {
        $latency_time = self::$Model->fetchOne("SELECT `book_latency` FROM `configs`");
        return json_encode($latency_time);
    }

    /*
     * Lay danh sach order cua customer_id
     */

    public function getOrderCustomer($customer_id) {
        $orders = self::$Model->fetchAll(
                "SELECT `a`.*,`c`.`title` as `currency_title`
                    FROM `hotel_orders` as `a`
                    LEFT JOIN `hotel_room_types` as `b`
                        ON `a`.`room_type_id` = `b`.`ID`
                    LEFT JOIN `currencies` as `c`
                        ON `b`.`currency_id` = `c`.`ID`
                    WHERE  
                        `a`.`customer_id`  = '{$customer_id}'
                        and `a`.`customer_id` != '0'
                        and `a`.`is_last` = '1'
                 GROUP BY `a`.`ID`
                    ");
        return json_encode($orders);
    }

    /*
     * Lay thong tin cua hoa don
     */

    public function getInfoOrder($order_id) {
        $order_info = self::$Model->fetchRow(
                "SELECT `a`.*,`b`.`title` as `room_type_title`,`c`.`title` as `currency_title`,
                        `b`.`extrabed_price`
                    FROM `hotel_orders`  as `a`
                     LEFT JOIN `hotel_room_types` as `b`
                        ON `a`.`room_type_id` = `b`.`ID`
                    LEFT JOIN `currencies` as `c`
                        ON `b`.`currency_id` = `c`.`ID`
                    WHERE `a`.`ID` = '{$order_id}'
                    GROUP BY `a`.`ID`
                    ");
        return json_encode($order_info);
    }

    /*
     * Update root_id
     */

    public function updateOrder($order_id, $title, $desc, $hotel_id, $room_type_id, $amount, $date_start, $date_end, $customer_name, $customer_email, $customer_address, $customer_phone, $location_id, $state_id, $has_extrabed, $order_type_id, $is_apply_campaign, $customer_id, $total_price, $date_created, $date_updated, $root_id, $is_last,$payment_status, $status) {

        $date_start = date('Y-m-d', strtotime($date_start));
        $date_end = date('Y-m-d', strtotime($date_end));

        $arrUpdate = array(
            title => $title,
            desc => $desc,
            hotel_id => $hotel_id,
            room_type_id => $room_type_id,
            status => 0,
            amount => $amount,
            date_start => $date_start,
            date_end => $date_end,
            customer_name => $customer_name,
            customer_email => $customer_email,
            customer_address => $customer_address,
            customer_phone => $customer_phone,
            location_id => $location_id,
            state_id => $state_id,
            has_extrabed => $has_extrabed,
            order_type_id => $order_type_id,
            is_apply_campaign => $is_apply_campaign,
            date_created => date("Y-m-d"),
            customer_id => $customer_id,
            total_price => $total_price,
            date_created => $date_created,
            date_updated => $date_updated,
            root_id => $root_id,
            is_last => $is_last,
            payment_status => $payment_status,
            status => $status,
        );
        $bool = self::$Model->update('hotel_orders', $arrUpdate, "ID = '{$order_id}'");

        if (isset($bool)) {
            return
                    json_encode(array(
                        status => 202,
                        status_text => 'Accepted',
                        message => 'Cập nhật thành công',
                    ));
        } else {
            return
                    json_encode(array(
                        status => 204,
                        status_text => 'No Content',
                        message => 'Có lỗi xảy ra từ hệ thống',
                    ));
        }
    }

    /*
     * Tìm tất cả hóa đơn cũ của hóa đơn đó ( bao gồm cả hóa đơn đang xét)
     */

    public function getOldOrders($order_id) {
        $results = array();
        $order_current = self::$Model->fetchRow(
                "SELECT * 
                    FROM `hotel_orders` 
                    WHERE `ID` = '{$order_id}'
                    ");
        if (!isset($order_current)) {
            return json_encode(array(
                        status => 203,
                        status_text => 'Non-Authoritative Information',
                        message => 'Đơn đặt phòng không tồn tại',
                    ));
        }
        $results[] = $order_current;
        $order_olds = self::$Model->fetchAll(
                "SELECT *
                    FROM `hotel_orders`
                    WHERE `root_id` = '{$order_current['root_id']}'
                        and `ID`!= '{$order_id}'
                    ORDER BY `date_updated` DESC
                    ");
        if ($order_olds) {
            foreach ($order_olds as $value) {
                $results[] = $value;
            }
        }

        return json_encode($results);
    }

    public function checkRoomClose($date_start, $date_end, $room_type_id, $hotel_id) {
        $date_start = date('Y-m-d', strtotime($date_start));
        $date_end = date('Y-m-d', strtotime($date_end));
        $room_closes = self::$Model->fetchAll(
                "SELECT * 
                    FROM `close_services`
                    WHERE `hotel_id` = '{$hotel_id}'
                          and `room_type_id` = '{$room_type_id}'
                          and (`date` >= '{$date_start}'  and `date` <= '{$date_end}')
                          and  `no_room_type` = 1
                    ");
        return json_encode($room_closes);
    }

}
