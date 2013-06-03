<?php

class HotelCloseController extends Zone_Action {

    public function indexAction() {
        if (isPost()) {
            $date_from = change_date_format(getDateRq('date_from'));
            $date_to = change_date_format(getDateRq('date_to'));
            $hotel_id = get_hotel_id();
            $room_type_id = getInt('room_type_id', 0);
            $no_allotment = getInt('no_allotment', array(), 2);
            $no_campaign = getInt('no_campaign', array(), 2);
            $no_room_type = getInt('no_room_type', array(), 2);

            if ($no_allotment || $no_campaign || $no_room_type) {
                $close_olds = self::$Model->fetchAll(
                        "SELECT * 
                            FROM `close_services`
                            WHERE `hotel_id` = '{$hotel_id}'
                                and `room_type_id` = '{$room_type_id}'
                                and `date` BETWEEN '{$date_from}' AND '{$date_to}'
                            ");
                if (!empty($close_olds)) {
                    $ids = array();
                    foreach ($close_olds as $value) {
                        $ids[] = $value['ID'];
                    }
                    $str_ids = implode(',', $ids);
                    self::$Model->delete('close_services', "`ID` IN ($str_ids)");
                }
                
                $results = array();
                $k = 0;
                for ($i = strtotime(change_date_format($date_from)); $i <= strtotime(change_date_format($date_to)); $i = $i + 86400) {
                    $date_i = date('Y-m-d', $i);
                    if (isset($no_allotment[$date_i]) || isset($no_campaign[$date_i]) || isset($no_room_type[$date_i])) {
                        (isset($no_allotment[$date_i])) ? $results[$k]['no_allotment'] = 1 : $results[$k]['no_allotment'] = 0;
                        (isset($no_campaign[$date_i])) ? $results[$k]['no_campaign'] = 1 : $results[$k]['no_campaign'] = 0;
                        (isset($no_room_type[$date_i])) ? $results[$k]['no_room_type'] = 1 : $results[$k]['no_room_type'] = 0;
                        $results[$k]['date'] = $date_i;
                        $results[$k]['room_type_id'] = $room_type_id;
                        $results[$k]['hotel_id'] = $hotel_id;
                        $k++;
                    }
                }
                
                if(!empty($results)){
                    self::$Model->insertMany('close_services', $results);
                }
            }
            self::setJSON(array(
                reload => true,
                close=>true,
            ));
        }

        $date_from = change_date_format(getDateRq('date_from', date('d/m/Y')));
        $date_to = change_date_format(getDateRq('date_to'));

        if (!$date_to) {
            $date_to = $date_from;
        }
        if ($date_from > $date_to) {
            self::setJSON(array(
                alert => error('Ngày bắt đầu lớn hơn ngày kết thúc'),
                close => true
            ));
        }

        if (((strtotime($date_to) - strtotime($date_from)) / 86400) > 365) {
            self::setJSON(array(
                alert => error('Không tìm kiếm quá 1 năm'),
                close => true
            ));
        }

        $hotel_id = get_hotel_id();
        loadClass('ZList');
        $list = new ZList();

        $list->setVar(array(
            date_from => getDateRq('date_from', date('d/m/Y')),
            date_to => getDateRq('date_to')
        ));

        $list->setPageLink('#Hotel/Close');

        $list->setSqlCount("SELECT COUNT(*) 
                 FROM `close_services` AS `a`
                ");

        $list->setSqlSelect("
            SELECT `a`.*
                 FROM `close_services` AS `a`
                ");

        $list->setWhere(" `a`.`hotel_id` = '{$hotel_id}'");

        $list->setWhere(" `a`.`date` BETWEEN '{$date_from}' AND '{$date_to}' ");

        $list->setOrder("`a`.`date` ASC ");

        $list->addFieldOrder(array(
            '`a`.`date`' => 'date',
            '`a`.`room_type_id`' => 'room_type_id',
        ));

        $list->addFieldEqual(array(
            '`a`.`room_type_id`' => 'room_type_id',
        ));

        $list->run();

        //rooom_types
        $room_types = self::$Model->fetchAll(
                "SELECT `a`.`ID`,`a`.`title`
                             FROM `hotel_room_types` AS `a`
                             WHERE `a`.`hotel_id` = '{$hotel_id}' ");
        self::set('room_types', $room_types);

        self::set(array(
            date_from => date('d/m/Y', strtotime($date_from . ' 00:00:00')),
            date_to => getDateRq('date_to'),
            posts => $list->getPosts(),
            page => $list->getPage(),
            vars => $list->getVars()
        ));
    }

}