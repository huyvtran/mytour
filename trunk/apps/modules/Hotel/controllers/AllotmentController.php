<?php

class HotelAllotmentController extends Zone_Action {

    const LOG_TYPE = 'ALLOTMENT';

    public function indexAction() {
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

        $hotel_id = get_hotel_id();
        loadClass('ZList');
        $list = new ZList();

        $list->setVar(array(
            date_from => getDateRq('date_from', date('d/m/Y')),
            date_to => getDateRq('date_to'),
            room_type_id => get('room_type_id')
        ));
        $list->setPageLink('#Hotel/Allotment');
        $list->setSqlCount("SELECT COUNT(*) 
                 FROM `allotments` AS `a`
                 LEFT JOIN `hotel_room_types` AS `b`
                    ON `a`.`room_type_id` = `b`.`ID`
                 LEFT JOIN `partners` as `c`
                    ON `a`.`partner_id` = `c`.`ID`
                ");

        $list->setSqlSelect("
            SELECT `a`.*,`b`.`title` as `room_type_title`,`c`.`title` as `partner_title`
                 FROM `allotments` AS `a`
                 LEFT JOIN `hotel_room_types` AS `b`
                    ON `a`.`room_type_id` = `b`.`ID`
                 LEFT JOIN `partners` as `c`
                    ON `a`.`partner_id` = `c`.`ID`
                ");
        $list->setWhere(" `a`.`hotel_id` = '{$hotel_id}'");
        $list->setWhere(" `a`.`date` BETWEEN '{$date_from}' AND '{$date_to}' ");

        $list->setOrder("`a`.`date` ASC ");

        $list->addFieldOrder(array(
            '`a`.`date`' => 'date',
            '`a`.`number`' => 'number',
            '`a`.`room_type_id`' => 'room_type_id',
            '`a`.`partner_id`' => 'partner_id',
        ));

//        $list->addFieldText(array(
//            '`a`.`title`' => 's',
//        ));
        $list->addFieldEqual(array(
            '`a`.`room_type_id`' => 'room_type_id',
            '`a`.`partner_id`' => 'partner_id'
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
            vars => $list->getVars(),
            room_type_id => get('room_type_id')
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

        //partners
        $partners = self::$Model->fetchAll(
                "SELECT *
                    FROM `partners`
                    WHERE `hotel_id` = '{$hotel['ID']}'
                    ");
        self::set('partners', $partners);

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

            $arrDates = array();
            for ($i = strtotime($data['date_start']); $i <= strtotime($data['date_end']); $i = $i + 86400) {
                $jd = cal_to_jd(CAL_GREGORIAN, date('m', $i), date('d', $i), date('Y', $i));
                $thu_of_date = jddayofweek($jd, 0);
                $arrDates[date('Y-m-d', $i)] = $thu_of_date;
            }

            if ($data['days'] && count($data['days']) > 0) {
                foreach ($arrDates as $key => $value) {
                    if (in_array($value, $data['days'])) {
                        $arrData = array(
                            'room_type_id' => $data['room_type_id'],
                            'number' => $data['number'],
                            'partner_id' => $data['partner_id'],
                            'hotel_id' => get_hotel_id(),
                            'date' => $key,
                        );
                        self::$Model->insert('allotments', $arrData);
                        $allotment_id = self::$Model->lastId();
                    }
                }
            }

            //add to log actions
            $desc = 'Thêm mới allotment ' . $allotment_id;
            Plugins::logActions(self::LOG_TYPE, $desc);

            self::setJSON(array(
                redirect =>  '#Hotel/Allotment?room_type_id=' . $data['room_type_id'] . '&date_from=' . date('d/m/Y',  strtotime($data['date_start'])) . '&date_to=' . date('d/m/Y',  strtotime($data['date_end']))
            ));
        }
    }

    public function editAction() {
        self::removeLayout();
        $hotel = get_hotel();
        $allotment_id = getInt('ID', 0);

        //rooom_types
        $room_types = self::$Model->fetchAll(
                "SELECT `a`.`ID`,`a`.`title`
                             FROM `hotel_room_types` AS `a`
                             WHERE `a`.`hotel_id` = '{$hotel['ID']}' ");
        self::set('room_types', $room_types);

        //partners
        $partners = self::$Model->fetchAll(
                "SELECT *
                    FROM `partners`
                    WHERE `hotel_id` = '{$hotel['ID']}'
                    ");
        self::set('partners', $partners);

        $post = self::$Model->fetchRow(
                "SELECT `a`.*,`b`.`title` as `room_type_title`
                        FROM `allotments` AS `a`
                        LEFT JOIN `hotel_room_types` as `b`
                            ON `a`.`room_type_id` = `b`.`ID`
                        WHERE `a`.`ID` = '{$allotment_id}'  
                    ");
        if (isPost()) {
            loadClass('ZData');
            $f = new ZData();
            $f->addfields(self::fieldsEdit());
            $data = self::checkDataEdit($f->getData());
            if (!is_array($data)) {
                self::setJSON(array(
                    message => error($data)
                ));
            }
            self::$Model->update('allotments', $data, "`ID`='$allotment_id'");

            $desc = 'Sửa allotment ' . $allotment_id;
            Plugins::logActions(self::LOG_TYPE, $desc);

            self::setJSON(array(
                reload => true,
                close => true,
            ));
        }
        self::set(array(
            'hotel' => $hotel,
            'post' => $post,
        ));
    }

    public function deleteAction() {
        $ids = getInt('ID', array(), true);
        if (count($ids) > 0) {
            $cond = implode(',', $ids);
            self::$Model->delete('allotments', "`ID` IN ($cond)");
        }

        // add to log actions
        $desc = 'Xóa allotment ' . $cond;
        Plugins::logActions(self::LOG_TYPE, $desc);
        self::setJSON(array(
            reload => true                
        ));
    }

    public function editNumberAction() {
        $ids = getInt('ID', array(), true);
        $arr_numbers = getInt('number', array(), true);
//        $cond = implode(',', $ids);
//        echo "<pre>";
//        var_dump($cond);
//        exit();

        if (!empty($arr_numbers) && !empty($ids)) {
            $arrUpdates = array();
            foreach ($arr_numbers as $key => $value) {
                if (in_array($key, $ids)) {
                    $arrUpdates[$key] = $value;
                }
            }
            if ($arrUpdates) {
                $check_free = true;
                foreach ($arrUpdates as $k => $v) {
                    $allotment = self::$Model->fetchRow("
                                    SELECT *
                                    FROM `allotments`
                                    WHERE `ID` = {$k}
                                ");

                    $free_date = get_room_free_dates($allotment['date'], $allotment['date'], $allotment['room_type_id'], $allotment['hotel_id']);
                    if ($v > $free_date['number_free']) {
                        $check_free = false;
                        break;
                    }
                }
                if ($check_free == false) {
                    self::setJSON(array(
                        alert => 'Tồn tại ngày mà allotment lớn hơn số phòng còn trống !'
                    ));
                } else {
                    foreach ($arrUpdates as $key => $value) {
                        $dataUpdates = array('number' => $value);
                        self::$Model->update('allotments', $dataUpdates, "`ID`='$key'");
                    }
                    
                    // add to log actions
                    $cond = implode(',', $ids);
                    $desc = 'Cập nhật allotment ' . $cond;
                    Plugins::logActions(self::LOG_TYPE, $desc);
                    
                    self::setJSON(array(
                        reload => true
                            //redirect => "#Hotel/Allotment"
                    ));
                }
            }
        } else {
            self::setJSON(array(
                alert => 'Chưa có bản ghi nào được chọn'
            ));
        }
    }

    private function checkDataEdit($data) {
        if (!is_array($data)) {
            return $data;
        }
        $hotel_id = get_hotel_id();
        //check so luong allotment khong duoc vuot qua so phong trong hien co
        $room_type_free = get_room_free_dates($data['date'], $data['date'], $data['room_type_id'], $hotel_id);
        if ($room_type_free['number_free'] < $data['number']) {
            return 'Số phòng allotment vượt quá số phòng còn trống !';
        }
        return $data;
    }

    private function checkData($data) {
        if (!is_array($data)) {
            return $data;
        }
        if (isset($data['date_start']) && isset($data['date_end'])) {
            if (strtotime($data['date_start']) > strtotime($data['date_end'])) {
                return translate('default.hotel.dateend.not.lower.datestart');
            }
        }
        $hotel_id = get_hotel_id();
        //check so luong allotment khong duoc vuot qua so phong trong hien co
        $room_type_free = get_room_free_dates($data['date_start'], $data['date_end'], $data['room_type_id'], $hotel_id);
        if ($room_type_free['number_free'] < $data['number']) {
            return 'Số phòng allotment vượt quá số phòng còn trống !';
        }

        //Kiem tra xem ngay add them co ngay nao trung voi ngay da co chua
        $check = true;
        if (!empty($data['days'])) {
            $dateAllotments = self::$Model->fetchAll(
                    "SELECT `date`
                    FROM `allotments`
                    WHERE `partner_id` = '{$data['partner_id']}'
                        AND `hotel_id` = '{$hotel_id}'
                        AND `room_type_id` = '{$data['room_type_id']}'
                    ");
            if (!$dateAllotments) {
                $check = true;
            } else {
                //Lay ngay da co
                $arrDateOlds = array();
                foreach ($dateAllotments as $value) {
                    $arrDateOlds[] = $value['date'];
                }
                // Lay ngay add vao
                $arrDateNews = array();
                for ($i = strtotime($data['date_start']); $i <= strtotime($data['date_end']); $i = $i + 86400) {
                    //Tinh thu cua ngay i
                    $jd = cal_to_jd(CAL_GREGORIAN, date('m', $i), date('d', $i), date('Y', $i));
                    $thu_of_date = jddayofweek($jd, 0);
                    if (in_array($thu_of_date, $data['days'])) {
                        $arrDateNews[] = date('Y-m-d', $i);
                    }
                }
                if (count($arrDateNews) > 0) {
                    $arr_check = array_intersect($arrDateNews, $arrDateOlds);
                    if (count($arr_check) > 0) {
                        $check = false;
                    }
                } else {
                    $check = true;
                }
            }
        } else {
            $check = true;
        }
        if ($check == false) {
            return 'Tồn tại một ngày đã có kế hoạch allotment !';
        }
        return $data;
    }

    protected function fieldsEdit() {
        $data = array(
            date => array(
                type => 'DATE',
                no_empty => true,
                label => 'Ngày',
            ),
            room_type_id => array(
                type => 'INT',
                no_empty => true,
                lable => 'Loại phòng',
            ),
            number => array(
                type => 'INT',
                no_empty => true,
                label => 'Số phòng'
            ),
            partner_id => array(
                type => 'INT',
                no_empty => true,
                label => 'Đối tác'
            ),
            desc => array(
                type => 'CHAR',
                label => 'Mô tả'
            ),
        );
        return $data;
    }

    protected function fields() {
        $data = array(
            date_start => array(
                type => 'DATE',
                no_empty => true,
                label => 'Ngày bắt đầu',
            ),
            date_end => array(
                type => 'DATE',
                no_empty => true,
                label => 'Ngày kết thúc',
            ),
            days => array(
                type => 'CHAR',
                label => 'Thứ áp dụng'
            ),
            room_type_id => array(
                type => 'INT',
                no_empty => true,
                lable => 'Loại phòng',
            ),
            number => array(
                type => 'INT',
                no_empty => true,
                label => 'Số phòng'
            ),
            partner_id => array(
                type => 'INT',
                no_empty => true,
                label => 'Đối tác'
            ),
            days => array(
                type => 'CHAR',
                label => 'Mô tả'
            ),
        );
        return $data;
    }

}

?>
