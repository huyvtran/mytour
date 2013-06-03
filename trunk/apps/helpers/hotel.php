<?php

function get_hotel() {
    $user_id = get_user_id();
    $hotel_id = get_hotel_id();

    if (is_null($hotel_id))
        return null;

    $hotel_id = addslashes(stripslashes($hotel_id));

    $hotel = Zone_Base::$Model->fetchRow("SELECT
        * FROM `hotels`
            WHERE `user_id`='{$user_id}'
                AND `ID`='{$hotel_id}' AND `is_active`='1'");

    //Zone_Base::setCache('current_hotel',$hotel);
    return $hotel;
}

function get_hotel_id() {
    return $_SESSION['hotel_id'];
}

function set_hotel($hotel_id) {
    $_SESSION['hotel_id'] = $hotel_id;
}

function autolocal($parent_id) {
    $id = $parent_id;
    $locals = Zone_Base::$Model->fetchAll("SELECT * 
			FROM `locations` WHERE `parent_id`='$id'");
    die(json_encode($locals));
}

function convertCurrencies($sign = null, $currency_id_current, $value, $currency_id_output = null) {
    $currencies = Plugins::getOptions('currencies');
    if ($currency_id_current == 0) {
        $value_end = $sign . '' . ($value / 100);
    } else {
        if ($currency_id_current == $currency_id_output) {
            $value_end = $sign . '' . $value;
        } else {
            foreach ($currencies as $val) {
                if ($val['ID'] == $currency_id_current) {
                    $currency_current = $val;
                }
                if ($val['ID'] == $currency_id_output) {
                    $currency_output = $val;
                }
            }

            $currency_current_dollar = $value / $currency_current['rate'];
            $value_end = $sign . '' . $currency_current_dollar * $currency_output['rate'];
        }
    }
    return $value_end;
}

//Gia cua ngay do trong bang price_of_dates
function order_price_dates($date_input, $order_id) {
    $price = Zone_Base::$Model->fetchOne(
            "SELECT `price`
           FROM `order_price_dates`
           WHERE `date` = '{$date_input}'
               AND `order_id` = '{$order_id}'
           ");
    return $price;
}

//So phong trong ( chua tinh den allotment) trong 1 khoang thoi gian
function get_room_free_dates($date_start, $date_end, $room_type_id, $hotel_id) {
    $room_types = Zone_Base::$Model->fetchRow("
                SELECT `a`.*,`c`.`title` AS `currency_title`,
                    `a`.`number`-SUM( IFNULL(`b`.`amount`,0)) AS `number_free`          
                FROM `hotel_room_types` AS `a`             
                LEFT JOIN `hotel_orders` AS `b`
                        ON `b`.`room_type_id`=`a`.`ID` 
                        AND `b`.`status` != '2'
                        AND NOT(`b`.`date_end` < '{$date_start}' OR `b`.`date_start` > '{$date_end}' ) 
                LEFT JOIN `currencies` as `c`
                    ON `a`.`currency_id` = `c`.`ID`
                WHERE `a`.`hotel_id` = '{$hotel_id}' and `a`.`ID` = '{$room_type_id}'
                GROUP BY `a`.`ID`
        ");
    return $room_types;
}

//Gia cua ngay do ap dung RBG, KM....
function price_of_day($date_input, $room_type_id, $hotel_id, $is_apply_campaign = 'yes', $campaign_of_day) {

    //gia fix cung cua phong do
    $room = Zone_Base::$Model->fetchRow("
                SELECT * 
                FROM `hotel_room_types`
                WHERE `ID` = '{$room_type_id}'
                    and `hotel_id` = '{$hotel_id}'
            ");
    $room_price_fix = $room['price'];

    //Kiem tra ngay co kế hoach giá chưa
    $is_plan_price = 1;

    //tinh thu cua ngay nay
    $jd = cal_to_jd(CAL_GREGORIAN, date('m', strtotime($date_input)), date('d', strtotime($date_input)), date('Y', strtotime($date_input)));
    $thu_of_date = jddayofweek($jd, 0);

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
                      AND ( '{$date_input}' BETWEEN `a`.`date_start` and  `a`.`date_end` )
                      AND ( (CONCAT(',',`a`.`days`,',')  LIKE '%{$thu_of_date}%' ) )   
                 GROUP BY `a`.`ID`
                ORDER BY `a`.`priority` DESC
                    
            ");
    $rule_of_day = array();
    if (!empty($all_rule_days)) {
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
     *  Neu RBG co dau = thi tinh RBG->KM
     *  Nguoc lai thi tinh KM->RBG
     */

    //1. Tồn tại Ràng Buộc Giá ------------------------------------------------------------------------------
    if (!empty($rule_of_day)) {
        //TH1 : Tồn tại ràng buộc giá bằng
        if ($rule_of_day['sign'] == '=') {
            //RBG = + Tồn tại Khuyến Mãi + Ngày không bị đóng KM
            if (!empty($campaign_of_day) && in_array($date_input, $campaign_of_day['date_results'])) {
                $price_of_campaign = convertCurrencies($campaign_of_day['sign'], $campaign_of_day['currency_id'], $campaign_of_day['value'], $room['currency_id']);
                $price_of_rule = convertCurrencies($rule_of_day['sign'], $rule_of_day['currency_id'], $rule_of_day['value'], $room['currency_id']);
                if ($is_apply_campaign == 'yes') {
                    $price_end = $price_of_rule + $price_of_campaign;
                } elseif ($is_apply_campaign == 'no') {
                    $price_end = $price_of_rule;
                }
            }
            //RBG = + Tồn tại  Khuyến Mãi + Ngày bị đóng KM
            elseif (!empty($campaign_of_day) && !in_array($date_input, $campaign_of_day['date_results'])) {
                $price_of_rule = convertCurrencies($rule_of_day['sign'], $rule_of_day['currency_id'], $rule_of_day['value'], $room['currency_id']);
                $price_end = $price_of_rule;
            }

            //RBG =  + Không tồn tại KM
            elseif (empty($campaign_of_day)) {
                $price_of_rule = convertCurrencies($rule_of_day['sign'], $rule_of_day['currency_id'], $rule_of_day['value'], $room['currency_id']);
                $price_end = $price_of_rule;
            }
        }

        //TH2 : Tồn tại ràng buộc giá khác bằng
        elseif ($rule_of_day['sign'] != '=') {
            //RBG != + Tồn tại KM + Ngày không bị đóng KM
            if (!empty($campaign_of_day) && in_array($date_input, $campaign_of_day['date_results'])) {
                $price_of_campaign = convertCurrencies($campaign_of_day['sign'], $campaign_of_day['currency_id'], $campaign_of_day['value'], $room['currency_id']);
                $price_of_rule = convertCurrencies($rule_of_day['sign'], $rule_of_day['currency_id'], $rule_of_day['value'], $room['currency_id']);
                $price_end = $room_price_fix + $price_of_campaign + $price_of_rule;
                if ($is_apply_campaign == 'yes') {
                    $price_end = $room_price_fix + $price_of_campaign + $price_of_rule;
                } else {
                    $price_end = $room_price_fix + $price_of_rule;
                }
            }
            //RBG != + Tồn tại KM + Ngày  bị đóng KM
            elseif (!empty($campaign_of_day) && !in_array($date_input, $campaign_of_day['date_results'])) {
                $price_of_rule = convertCurrencies($rule_of_day['sign'], $rule_of_day['currency_id'], $rule_of_day['value'], $room['currency_id']);
                $price_end = $room_price_fix + $price_of_rule;
            }

            //RBG != + Không tồn tại KM
            elseif (empty($campaign_of_day)) {
                $price_of_rule = convertCurrencies($rule_of_day['sign'], $rule_of_day['currency_id'], $rule_of_day['value'], $room['currency_id']);
                $price_end = $room_price_fix + $price_of_rule;
            }
        }
    }

    //2. Không tồn tại Ràng Buộc Giá -----------------------------------------------------------------
    if (empty($rule_of_day)) {
        //TH1 : Không tồn tại RBG + Tồn tại KM + Ngày không bị đóng KM
        if (!empty($campaign_of_day) && in_array($date_input, $campaign_of_day['date_results'])) {
            $price_of_campaign = convertCurrencies($campaign_of_day['sign'], $campaign_of_day['currency_id'], $campaign_of_day['value'], $room['currency_id']);
            if ($is_apply_campaign == 'yes') {
                $price_end = $room_price_fix + $price_of_campaign;
            } else {
                $price_end = $room_price_fix;
            }
        }
        //TH1 : Không tồn tại RBG + Tồn tại KM + Ngày  bị đóng KM
        elseif (!empty($campaign_of_day) && !in_array($date_input, $campaign_of_day['date_results'])) {
            $price_end = $room_price_fix;
        }
        //TH3 : Không tồn tại RBG + Không tồn tại KM
        elseif (empty($campaign_of_day)) {
            $price_end = $room_price_fix;
            $is_plan_price = 0;
        }
    }

    if (!empty($campaign_of_day)) {
        unset($campaign_of_day['date_results']);
    }
    $arrayPriceDates = array(
        'price_end' => $price_end,
        'is_plan_price' => $is_plan_price,
        'rule_of_day' => $rule_of_day,
        'campaign_of_day' => $campaign_of_day,
    );
    return $arrayPriceDates;
}

function thu_of_date($date_input) {
    //tinh thu cua ngay nay
    $jd = cal_to_jd(CAL_GREGORIAN, date('m', strtotime($date_input)), date('d', strtotime($date_input)), date('Y', strtotime($date_input)));
    $thu_of_date = jddayofweek($jd, 0);
    return translate('default.work.day' . ($thu_of_date));
}

function thu_of_date2($date_input) {
    //tinh thu cua ngay nay
    $jd = cal_to_jd(CAL_GREGORIAN, date('m', strtotime($date_input)), date('d', strtotime($date_input)), date('Y', strtotime($date_input)));
    $thu_of_date = jddayofweek($jd, 0);
    return $thu_of_date;
}

/*
 * Khuyến mãi có mức ưu tiên cao nhất được áp dụng trong khoảng thời gian đặt phòng 
 * @date_book : Ngày đặt phòng
 * @date_start : Ngày check-in
 * @date_end : Ngày check-out
 * @room_type_id : ID loại phòng
 * @hotel_id : ID khách sạn
 */

function getCampaignHighest($date_book, $date_start, $date_end, $room_type_id, $hotel_id) {
    $date_book = date('Y-m-d', strtotime($date_book));
    $date_start = date('Y-m-d', strtotime($date_start));
    $date_end = date('Y-m-d', strtotime($date_end));

    $campaigns = Zone_Base::$Model->fetchAll(
            "SELECT `a`.*, `c`.`title` as `room_type_title`
                FROM `hotel_campaigns` as `a`
                LEFT JOIN `hotel_campaign_room_types` as `b`
                    ON `a`.`ID` = `b`.`campaign_id`
                LEFT JOIN `hotel_room_types` as `c`
                    ON `b`.`room_type_id` = `c`.`ID`
                WHERE  `a`.`hotel_id` = '{$hotel_id}'
                            and  `c`.`ID` = '{$room_type_id}'
                            and (`a`.`date_start` <= '{$date_start}'  and  `a`.`date_end`>='{$date_end}')
                  ");

    if (!empty($campaigns)) {
        foreach ($campaigns as $key => $value) {
            //danh sách thứ loại trừ
            $arr_days = explode(',', $value['days']);
            for ($i = strtotime($value['date_start']); $i <= strtotime($value['date_end']); $i = $i + 86400) {
                $thu_i = thu_of_date2(date('Y-m-d', $i));
                if (in_array($thu_i, $arr_days)) {
                    $campaigns[$key]['date_applies'][] = date('Y-m-d', $i);
                }
            }
            //danh sách ngày bị loại bỏ (date_remove_start, date_remove_end)
            $date_remove_of_campaigns = Zone_Base::$Model->fetchAll(
                    "SELECT `date_remove_start`,`date_remove_end`
                         FROM `campaign_date_removes` as `a`
                         WHERE `a`.`campaign_id` = '{$value['ID']}'
                         ");
            if (!empty($date_remove_of_campaigns)) {
                foreach ($date_remove_of_campaigns as $k => $v) {
                    for ($i = strtotime($v['date_remove_start']); $i <= strtotime($v['date_remove_end']); $i = $i + 86400) {
                        $campaigns[$key]['arr_date_removes'][] = date('Y-m-d', $i);
                    }
                    $campaigns[$key]['arr_date_removes'] = array_unique($campaigns[$key]['arr_date_removes']);
                }
            }
        }

        foreach ($campaigns as $key => $value) {
            if (!empty($value['arr_date_removes'])) {
                $campaigns[$key]['date_results'] = array_diff($campaigns[$key]['date_applies'], $campaigns[$key]['arr_date_removes']);
            } else {
                $campaigns[$key]['date_results'] = $campaigns[$key]['date_applies'];
            }
            unset($campaigns[$key]['date_applies']);
            unset($campaigns[$key]['arr_date_removes']);
        }

//        //Mảng ngày checkin-checkout
//        for ($i = strtotime($date_start); $i <= strtotime($date_end); $i = $i + 86400) {
//            $arr_checkin_outs[] = date('Y-m-d', $i);
//        }
//        //KM có ngày check-in,  check-out thỏa mãn
//        
//        foreach ($campaigns as $key => $value) {
//            $arr_merge = (array_merge($value['date_results'], $arr_checkin_outs));
//            $arr_merge = array_unique($arr_merge);
//            if (count($arr_merge) == count($value['date_results'])) {
//                $campaigns_2[$key] = $value;
//                // unset($campaigns_2[$key]['date_results']);
//            }
//        }
        $campaigns_2 = $campaigns;
    
        $arr_campaign_results = array();

        if (!empty($campaigns_2)) {
            foreach ($campaigns_2 as $k => $v) {
                if ($v['type'] == 'NORMAL') {
                    for ($i = strtotime($v['date_start_book']); $i <= strtotime($v['date_end_book']); $i = $i + 86400) {
                        $tmp[] = date('Y-m-d', $i);
                    }
                    if (in_array($date_book, $tmp))
                        $arr_campaign_results[] = $v;
                }
                elseif ($v['type'] == 'LAST') {
                    for ($i = strtotime($v['date_start']); $i >= (strtotime($v['date_start']) - 86400 * $v['inteval_day']); $i = $i - 86400) {
                        $tmp2[] = date('Y-m-d', $i);
                    }
                    if (in_array($date_book, $tmp2))
                        $arr_campaign_results[] = $v;
                }
                elseif($v['type'] == 'EARLY'){
                    $kc_date = strtotime($v['date_start']) - strtotime(86400 * $v['inteval_day']);
                    if(strtotime($date_book) < $kc_date){
                        $arr_campaign_results[] = $v;
                    }
                }
            }
            if (!empty($arr_campaign_results)) {
                foreach ($arr_campaign_results as $k => $v) {
                    $sort_priority[] = $v['priority'];
                }
                array_multisort($sort_priority, SORT_DESC, $arr_campaign_results);
            }
        }
    }

    if (!empty($arr_campaign_results)) {
        $result = $arr_campaign_results[0];

        //Loại bỏ những ngày mà đóng khuyến mãi của kết quả
        $arr_close_campaigns = getCampaignCloseServices($date_start, $date_end, $room_type_id, $hotel_id);
       
        $result['date_results'] = array_diff($result['date_results'], $arr_close_campaigns);

        return $result;
        
    } else {
        return array();
    }
}

/*
 * Danh sách đóng phòng trong một khoảng thời gian ( dùng để check phòng bị đóng)
 */

function getRoomCloseServices($date_start, $date_end, $room_type_id, $hotel_id) {
    $room_closes = Zone_Base::$Model->fetchAll(
            "SELECT * 
                    FROM `close_services`
                    WHERE `hotel_id` = '{$hotel_id}'
                          and `room_type_id` = '{$room_type_id}'
                          and (`date` >= '{$date_start}'  and `date` <= '{$date_end}')
                          and  `no_room_type` = 1
                    ");
    return $room_closes;
}

/*
 * Danh sách đóng khuyến mãi trong một khoảng thời gian ( dùng để check phòng bị đóng)
 */

function getCampaignCloseServices($date_start, $date_end, $room_type_id, $hotel_id) {
    $campaign_closes = Zone_Base::$Model->fetchAll(
            "SELECT `date`
                    FROM `close_services`
                    WHERE `hotel_id` = '{$hotel_id}'
                          and `room_type_id` = '{$room_type_id}'
                          and (`date` >= '{$date_start}'  and `date` <= '{$date_end}')
                          and  `no_campaign` = 1
                    ");

    $results = array();
    if (!empty($campaign_closes)) {
        foreach ($campaign_closes as $k => $v) {
            $results[] = $v['date'];
        }
    }
    return $results;
}

/*
 * Số phòng trống trong một khoảng thời gian
 */

function getRoomNumberFree($date_start, $date_end, $room_type_id, $hotel_id, $allotment = false) {
    if ($allotment == false) {
        $number_free = Zone_Base::$Model->fetchOne("
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
        return $number_free;
    }
}

/*
 * Lấy chính sách hủy của hóa đơn
 * @ room_type_id : ID loại phòng
 * @ hotel_id : ID khách sạn
 * @ is_apply_campaign : Có áp dụng KM cho hóa đơn hay không
 * @ campaign_highest : KM có mức ưu tiên cao nhất của hóa đơn
 * @ date_start : Ngày check-in
 * @ date_end : Ngày check-out
 */

function getPolicyOrder($date_start, $date_end, $room_type_id, $hotel_id, $is_apply_campaign, $campaign_highest) {
    //Chính sách hủy của phòng trong khoảng ngày checkin-checkout
    $policy_room = Zone_Base::$Model->fetchAll(
            "SELECT `a`.*,`c`.`date_start`,`c`.`date_end`,`b`.`disabled`,`b`.`prior_checkin`,`b`.`type`,`b`.`unit`,`b`.`value`
                FROM `policies` as `a`
                LEFT JOIN `policy_details` as `b`
                    ON `a`.`ID` = `b`.`policy_id`
                LEFT JOIN `policy_room_types` as `c`
                    ON `a`.`ID` = `c`.`policy_id` 
                        and `c`.`room_type_id` = '{$room_type_id}'
                WHERE `a`.`hotel_id` = '{$hotel_id}'
                    and `policy_room_type` = '1'
                    and   (`c`.`date_start` <= '{$date_start}' AND `c`.`date_end` >= '{$date_end}')
                ");
    //Áp dụng KM
    if ($is_apply_campaign == 'yes') {
        //Tồn tại KM 
        if (!empty($campaign_highest)) {
            $policy_type = $campaign_highest['policy_type'];
            if ($policy_type == 'ROOM_POLICY') {
                $result_policy = $policy_room;
            } elseif ($policy_type == 'CREATE_POLICY') {
                $row_policies = Zone_Base::$Model->fetchRow(
                        "SELECT `policy_id` 
                            FROM `policy_campaign_types`
                            WHERE `campaign_id` = '{$campaign_highest['ID']}'
                            ");
                $policy_id = $row_policies['policy_id'];

                $post_details = Zone_Base::$Model->fetchAll(
                        "SELECT * 
                            FROM `policy_details`
                            WHERE `policy_id` = '{$policy_id}'
                        ");
                $result_policy = $post_details;
            }
        }
        //Không tồn tại KM
        else {
            $result_policy = $policy_room;
        }
    }
    //Không áp dụng KM
    elseif ($is_apply_campaign == 'no') {
        $result_policy = $policy_room;
    }
    return $result_policy;
}

/*
 * Lấy lịch sử hóa đơn
 */

function getOldOrders($order_id) {
    $results = array();
    $order_current = Zone_Base::$Model->fetchRow(
            "SELECT `a`.*,`b`.`symbol` , 
                    `c`.`title` AS `room_title`,`c`.`ID` AS `room_id` ,
                     `d`.`title` AS `order_type_title`
                    FROM `hotel_orders` as `a` 
                     LEFT JOIN `currencies` as `b`
                                ON `a`.`currency_id` = `b`.`ID`
                                 LEFT JOIN `hotel_room_types` as `c`
                                ON `c`.`ID` = `a`.`room_type_id`
                     LEFT JOIN `order_types` as `d`
                                ON `a`.`order_type_id` = `d`.`ID`
                    WHERE `a`.`ID` = '{$order_id}'
                    ");
    $results[] = $order_current;

    $order_olds = Zone_Base::$Model->fetchAll(
            "SELECT `a`.*,`b`.`symbol` ,
                    `c`.`title` AS `room_title`,`c`.`ID` AS `room_id` ,
                     `d`.`title` AS `order_type_title`
                    FROM `hotel_orders` as `a`
                     LEFT JOIN `currencies` as `b`
                                ON `a`.`currency_id` = `b`.`ID`
                     LEFT JOIN `hotel_room_types` as `c`
                                ON `c`.`ID` = `a`.`room_type_id`
                     LEFT JOIN `order_types` as `d`
                                ON `a`.`order_type_id` = `d`.`ID`
                    WHERE `a`.`root_id` = '{$order_current['root_id']}'
                          and `a`.`ID`!= '{$order_id}'
                    ORDER BY `a`.`ID` DESC
                    ");
    if ($order_olds) {
        foreach ($order_olds as $value) {
            $results[] = $value;
        }
    }

    return $results;
}