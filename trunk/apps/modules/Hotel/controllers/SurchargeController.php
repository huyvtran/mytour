<?php

class HotelSurchargeController extends Zone_Action {

//    const LOG_TYPE = 'RULE_PRICE';

    protected function fields() {
        $data = array(
            surcharge_id => array(
                type => 'INT',
                no_empty => true,
                label => 'Tên phụ phí'
            ),
            money => array(
                type => 'INT',
                no_empty => true,
                label => 'Số tiền'
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
            days => array(
                type => 'CHAR',
                label => 'Thứ áp dụng'
            ),
            currency => array(
                type => 'ENUM',
                no_empty => true,
                value => array('1', '2'),
                label => 'Loại tiền tệ'
            ),
            desc => array(
                type => 'CHAR',
                label => 'Mô tả'
            )
        );
        return $data;
    }

    public function indexAction() {
        $hotel_id = get_hotel_id();

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

        loadClass('ZList');
        $list = new ZList();

        $list->setPageLink("#Hotel/Surcharge");
        $list->setSqlCount("
            SELECT COUNT(*)             
            FROM `hotel_surcharge` as `a`
              LEFT JOIN `currencies` as `b`
                ON `a`.`currency` = `b`.`ID`
              LEFT JOIN `hotels` as `c`
                ON `a`.`hotel_id` = `c`.`ID`
              LEFT JOIN `surcharge_types` as `d`
                ON `a`.`surcharge_id` = `d`.`ID`
            ");
        $list->setSqlSelect("
            SELECT 
            `a`.*, 
            `d`.`title` as `surcharge_title`,
            `b`.`title` as `currency_name`,
            `c`.`title` as `hotel_name`
            FROM `hotel_surcharge` as `a`
              LEFT JOIN `currencies` as `b`
                ON `a`.`currency` = `b`.`ID`
              LEFT JOIN `hotels` as `c`
                ON `a`.`hotel_id` = `c`.`ID`
              LEFT JOIN `surcharge_types` as `d`
                ON `a`.`surcharge_id` = `d`.`ID`
            ");
        $list->setWhere("`a`.`hotel_id`='$hotel_id'");
        $list->addFieldOrder(array(
            '`surcharge_title`' => 'title',
            '`a`.`date_start`' => 'date_start',
            '`a`.`date_end`' => 'date_end'
        ));

        $list->addFieldText(array(
            '`d`.`title`' => 's'
        ));

        $list->run();
        self::set(array(
            posts => $list->getPosts(),
            page => $list->getPage(),
            vars => $list->getVars(),
            date_from => getDateRq('date_from', date('d/m/Y')),
            date_to => getDateRq('date_to')
        ));
    }

    private function getOptions() {
        self::set(array(
            currencies => self::$Model->fetchAll("SELECT * FROM `currencies`"),
            surcharge_types => self::$Model->fetchAll("SELECT * FROM `surcharge_types`")
        ));
    }

    public function viewAction() {
        $id = getInt('ID');
        $hotel_id = get_hotel_id();
        $post = self::$Model->fetchRow(
                "SELECT `a`.*, `b`.`title` as `currency_name`, `c`.`title` as `surcharge_title`
                 FROM `hotel_surcharge` as `a`
                   LEFT JOIN `currencies` as `b`
                     ON `a`.`currency` = `b`.`ID` AND `a`.`ID`='$id'
                   LEFT JOIN `surcharge_types` as `c`
                     ON `a`.`surcharge_id` = `c`.`ID`
                 WHERE `a`.`ID` = '$id'");
        self::set(array(
            post => $post
        ));
    }

    public function addAction() {

        $hotel_id = get_hotel_id();
        self::getOptions();
        $room_types = self::$Model->fetchAll(
                "SELECT `a`.`ID`,`a`.`title`
                             FROM `hotel_room_types` AS `a`
                             WHERE `a`.`hotel_id` = '{$hotel_id}' ");

        self::set('room_types', $room_types);

        if (isPost()) {

            loadClass('ZData');
            $f = new ZData();

            $f->addFields(self::fields());
            $data = self::checkData($f->getData());

            if (!is_array($data)) {
                self::setJSON(array(
                    alert => error($data)
                ));
            }



            if (isset($data['days'])) {
                $data['days'] = implode(',', $data['days']);
            }

            self::$Model->insert('hotel_surcharge', $data);
            $last_surcharge_id = self::$Model->lastId();

            self::$Model->update('hotel_surcharge', array(
                hotel_id => $hotel_id
                    ), "`ID`='$last_surcharge_id'");

            self::setJSON(array(
                redirect => '#Hotel/Surcharge'
            ));
        }
    }

    private function checkData($data, $edited_id = null) {
        if (!is_array($data)) {
            return $data;
        }

        if (isset($data['date_start']) && isset($data['date_end'])) {
            if (strtotime($data['date_start']) > strtotime($data['date_end'])) {
                return 'Ngày bắt đầu không lớn hơn ngày kết thúc';
            }
        }

        if (empty($data['days'])) {
            return 'Chưa chọn thứ áp dụng';
        }

        if ($data['date_start'] > $data['date_end']) {
            self::setJSON(array(
                alert => error('Ngày bắt đầu không lớn hơn ngày kết thúc')
            ));
        }

        if (empty($data['currency'])) {
            self::setJSON(array(
                alert => error('Loại tiền tệ bắt buộc phải nhập')
            ));
        }
        
        $surcharge_id = $data['surcharge_id'];
        $hotel_id = get_hotel_id();
        
        if (self::getActionName() == 'editAction') {
            $getdate = self::$Model->fetchAll("
             SELECT `date_start`, `date_end`
             FROM `hotel_surcharge`
             WHERE `surcharge_id` = '$surcharge_id'
               AND `hotel_id` = '$hotel_id'           
                AND `ID` <> '$edited_id'
            ");
        } else {        
           $getdate = self::$Model->fetchAll("
             SELECT `date_start`, `date_end`
             FROM `hotel_surcharge`
             WHERE `surcharge_id` = '$surcharge_id'
               AND `hotel_id` = '$hotel_id'           
           ");
        }
        
        foreach ($getdate as $key => $a) {

            if (
                    ( ($data['date_start'] >= $a['date_start']) && ($data['date_start'] <= $a['date_end']) )
                    ||
                    ( ($data['date_end'] >= $a['date_start']) && ($data['date_end'] <= $a['date_end']) )
            ) {
                self::setJSON(array(
                    alert => error('Khoảng ngày áp dụng phụ phí đã tồn tại')
                ));
            }
        }        

        return $data;
    }

    public function editAction() {
        $id = getInt('ID');
        $hotel_id = get_hotel_id();

        if (!isPost()) {
            $post = self::$Model->fetchRow("            
            SELECT `a`.*
            FROM `hotel_surcharge` as `a`
            WHERE `a`.`ID`='$id' AND `a`.`hotel_id`='$hotel_id'");

            self::getOptions();

            self::set(array(
                post => $post
            ));
        } else {
            loadClass('ZData');
            $f = new ZData();
            $f->addFields(self::fields());

            $data = self::checkData($f->getData(), $id);

            if (!is_array($data)) {
                self::setJSON(array(
                    alert => error($data)
                ));
            }

            if (isset($data['days'])) {
                $data['days'] = implode(',', $data['days']);
            }

            self::$Model->update('hotel_surcharge', $data, "`ID`='$id'");
            self::$Model->update('hotel_surcharge', array(
                hotel_id => $hotel_id
                    ), "`ID`='$id'");

//            $room_surcharge_types = array();
//            foreach ($room_types as $key => $value) {
//                $room_surcharge_types[$key]['room_type_id'] = $value;
//                $room_surcharge_types[$key]['surcharge_id'] = $id;
//            }
//
//            self::$Model->delete('room_surcharge_types', "`surcharge_id`='$id'");
//            self::$Model->insertMany('room_surcharge_types', $room_surcharge_types);

            self::setJSON(array(
                redirect => "#Hotel/Surcharge/View?ID=$id",
                close => true
            ));
        }
    }

    public function deleteAction() {
        $ids = getInt('ID', array(), true);
        if (count($ids) > 0) {
            $cond = implode(',', $ids);
            self::$Model->delete('hotel_surcharge', "`ID` IN ($cond)");            
        }
        self::setJSON(array(
            redirect => "#Hotel/Surcharge"
        ));
    }

}

?>
