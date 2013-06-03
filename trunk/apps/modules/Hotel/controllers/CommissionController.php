<?php

class HotelCommissionController extends Zone_Action {

    protected function fields() {
        $data = array(
            partner_id => array(
                type => 'INT',
                no_empty => true,
                label => 'Đối tác'
            ),
            percent => array(
                type => 'INT',
                no_empty => true,
                label => 'Hoa hồng'
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
            desc => array(
                type => 'CHAR',
                label => 'Mô tả'
            )
        );
        return $data;
    }

    public function indexAction() {

        $hotel_id = get_hotel_id();
        $user_id = get_user_id();

        loadClass('ZList');
        $list = new ZList();

        $list->setVar(array(
        ));
        $list->setPageLink('#Hotel/Commission');
        $list->setSqlCount("
            SELECT COUNT(*)    
            FROM `commission` as `a`
              LEFT JOIN `partners` as `b`
                ON `a`.`partner_id` = `b`.`ID`
              LEFT JOIN `hotels` as `c`
                ON `a`.`hotel_id` = `c`.`ID`                
        ");
        $list->setSqlSelect("
            SELECT 
              `a`.*,               
              `b`.`title` as `partner_name`,
              `c`.`title` as `hotel_name`
            FROM `commission` as `a`
              LEFT JOIN `partners` as `b`
                ON `a`.`partner_id` = `b`.`ID`
              LEFT JOIN `hotels` as `c`
                ON `a`.`hotel_id` = `c`.`ID`            
        ");

        $list->setWhere("`a`.`hotel_id` = '$hotel_id'");
        
        $list->addFieldEqual(array(
            '`a`.`partner_id`' => 'partner_id'
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
        self::getOptions();

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



            self::$Model->insert('commission', $data);
            $last_id = self::$Model->lastId();
            self::$Model->update('commission', array(
                hotel_id => $hotel_id
                    ), "`ID`='$last_id'");

            self::setJSON(array(
                redirect => '#Hotel/Commission'
            ));
        }
    }

    public function deleteAction() {
        $ids = getInt('ID', array(), true);
        if (count($ids) > 0) {
            $cond = implode(',', $ids);
            self::$Model->delete('commission', "`ID` IN ($cond)");
        }
        self::setJSON(array(
            reload => true,
        ));
    }

    public function editAction() {
        $id = getInt('ID');
        self::getOptions();

        self::set(array(
            post => self::$Model->fetchRow("SELECT * FROM `commission` WHERE `ID` = '$id'")
        ));

        if (isPost()) {
            loadClass('ZData');
            $f = new ZData();
            $f->addFields(self::fields());
            $data = self::checkData($f->getData(), $id);

            if (!is_array($data)) {
                self::setJSON(array(
                    alert => error($data)
                ));
            }
            
            self::$Model->update('commission', $data, "`ID`='$id'");
            self::setJSON(array(
                redirect => "#Hotel/Commission"
            ));
        }
    }

    private function getOptions() {
        self::set(array(
            partners => self::$Model->fetchAll("SELECT * FROM `partners`")
        ));
    }

    private function checkData($data, $edited_id = null) {

        $hotel_id = get_hotel_id();

        if (!is_array($data)) {
            return $data;
        }

        if ($data['percent'] > 100) {
            self::setJSON(array(
                alert => error('Hoa hồng không lớn hơn 100%')
            ));
        }

        if ($data['date_start'] > $data['date_end']) {
            self::setJSON(array(
                alert => error('Ngày bắt đầu không lớn hơn ngày kết thúc')
            ));
        }

        $partner_id = $data['partner_id'];

        if (self::getActionName() == 'editAction') {
            $getdate = self::$Model->fetchAll("
            SELECT `date_start`, `date_end`
            FROM `commission`
            WHERE `hotel_id` = '$hotel_id'
                AND `partner_id` = '$partner_id'
                AND `ID` <> '$edited_id'
            ");
        } else {
            $getdate = self::$Model->fetchAll("
            SELECT `date_start`, `date_end`
            FROM `commission`
            WHERE `hotel_id` = '$hotel_id'
                AND `partner_id` = '$partner_id'
            ");
        }
        foreach ($getdate as $key => $a) {

            if (
                    ( ($data['date_start'] >= $a['date_start']) && ($data['date_start'] <= $a['date_end']) )
                    ||
                    ( ($data['date_end'] >= $a['date_start']) && ($data['date_end'] <= $a['date_end']) )
            ) {
                self::setJSON(array(
                    alert => error('Khoảng ngày áp dụng hoa hồng đã tồn tại')
                ));
            }
        }

        return $data;
    }

}
