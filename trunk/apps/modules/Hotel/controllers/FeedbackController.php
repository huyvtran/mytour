<?php

class HotelFeedbackController extends Zone_Action {

    protected function fields() {
        $data = array(
            comment => array(
                type => 'CHAR',
                no_empty => true,
                label => 'Phàn hồi'
            )
        );
        return $data;
    }

    public function indexAction() {
        $hotel_id = get_hotel_id();
        $date_from = change_date_format(getDateRq('date_from', date('d/m/Y')));
        $date_from .= date(' H:i:s');
        $date_to = change_date_format(getDateRq('date_to'));
        $date_to .= date(' H:i:s');
        $export = get('export');


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

        $list->setVar(array(
            date_from => getDateRq('date_from', date('d/m/Y')),
            date_to => getDateRq('date_to')
        ));
        $list->setPageLink('#Hotel/Feedback');
        $list->setSqlCount("SELECT COUNT(*)             
            FROM `comments` as `a`
            LEFT JOIN `hotels` as `b`
            ON `a`.`hotel_id` = `b`.`ID`
            LEFT JOIN `customers` as `c`
            ON `a`.`customer_id` = `c`.`ID`");

        $list->setSqlSelect("SELECT
            `a`.*,
            `b`.`title` as `hotel_name`,
            `c`.`fullname` as `fullname`
            FROM `comments` as `a`
            LEFT JOIN `hotels` as `b`
            ON `a`.`hotel_id` = `b`.`ID`
            LEFT JOIN `customers` as `c`
            ON `a`.`customer_id` = `c`.`ID`");

        $list->setOrder('`a`.`time` DESC');

        $list->setWhere("`a`.`hotel_id` = '{$hotel_id}' ");
        if (empty($export)) {
            $list->setWhere("`a`.`customer_id` <> 0 ");
        }
        $list->setWhere("`a`.`time` BETWEEN '$date_from' AND '$date_to' ");

        $list->addFieldOrder(array(
            '`hotel_name`' => 'hotel_name',
            '`fullname`' => 'fullname',
            '`a`.`comment`' => 'comment',
            '`a`.`time`' => 'time'
        ));

        $list->addFieldText(array(
            '`a`.`comment`' => 's',
            'fullname' => 's'
        ));

        $list->addFieldEqual(array(
            '`a`.`status`' => 'status',
            '`a`.`customer_id`' => 'customer_id'
        ));

        $list->run();

        self::set(array(
            posts => $list->getPosts(),
            page => $list->getPage(),
            vars => $list->getVars(),
            date_from => getDateRq('date_from', date('d/m/Y')),
            date_to => getDateRq('date_to')
        ));

        self::export();
    }

    private function export() {
        $export = get('export');
        if (!in_array($export, array('Excel2003', 'Excel2007')))
            return;

        self::removeLayout();        
        $posts = self::get('posts');


        loadOS('PHPExcel');
        $writer = new PHPExcel();
        $writer->setActiveSheetIndex(0);
        $worksheet = $writer->getActiveSheet();
        $worksheet->setTitle('Chi tiết');
        $row = 2;
        $col = 0;
        $worksheet
                ->setCellValueByColumnAndRow($col++, $row, 'TT')
                ->setCellValueByColumnAndRow($col++, $row, 'Khách sạn')
                ->setCellValueByColumnAndRow($col++, $row, 'Khách hàng')
                ->setCellValueByColumnAndRow($col++, $row, 'Feedback')
                ->setCellValueByColumnAndRow($col++, $row, 'Thời gian')
                ->setCellValueByColumnAndRow($col++, $row, 'Trạng thái')
                ->setCellValueByColumnAndRow($col++, $row, 'Trả lời');

        $row++;
        foreach ($posts as $k => $a) {
            //save
            if ($a['customer_id'] <> 0) {
                $reply = '';
                if ($a['status'] == 1) {
                    foreach ($posts as $n => $b) {
                        if ($b['root'] == $a['root'] && $b['customer_id'] == 0 && $b['user_id'] <> 0) {
                            $reply = $b['comment'];
                            unset($b[$n]);
                        }
                    }
                }

                $col = 0;
                $worksheet
                        ->setCellValueByColumnAndRow($col++, $row, $k + 1);

                $worksheet
                        ->setCellValueByColumnAndRow($col++, $row, $a['hotel_name']);
                $worksheet
                        ->setCellValueByColumnAndRow($col++, $row, $a['fullname']);
                $worksheet
                        ->setCellValueByColumnAndRow($col++, $row, $a['comment']);
                $worksheet
                        ->setCellValueByColumnAndRow($col++, $row, date('d/m/Y H:i:s', strtotime($a['time'])));
                $worksheet
                        ->setCellValueByColumnAndRow($col++, $row, ($a['status'] == 0) ? 'Chưa trả lời' : 'Đã trả lời');
                $worksheet
                        ->setCellValueByColumnAndRow($col++, $row, $reply);
                $row++;
            }
        }

        $writer->setActiveSheetIndex(0);

// Redirect output to a client’s web browser (Excel2007)
        if ($export == 'Excel2007') {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="Bang_tong_hop_feedback.xlsx"');
            header('Cache-Control: max-age=0');

            $objWriter = PHPExcel_IOFactory::createWriter($writer, 'Excel2007');
            $objWriter->save('php://output');
        } else if ($export == 'Excel2003') {
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="Bang_tong_hop_feedback.xls"');
            header('Cache-Control: max-age=0');

            $objWriter = PHPExcel_IOFactory::createWriter($writer, 'Excel5');
            $objWriter->save('php://output');
        }
        exit;
    }

    public function editAction() {
        self::removeLayout();
        $id = getInt('ID');
        $hotel_id = get_hotel_id();
        $user_id = get_user_id();

        if (!isPost()) {
            $posts = self::$Model->fetchAll("
                SELECT `a`.*, `b`.`fullname` as `fullname`
                FROM `comments` as `a`
                LEFT JOIN `customers` as `b`
                    ON `a`.`customer_id` = `b`.`ID`
                WHERE `a`.`root`='$id'
                    AND `a`.`hotel_id`='$hotel_id'
                    ");

            self::set(array(
                posts => $posts
            ));
        } else {
            $id = getInt('ID');
            $reply = get('reply');
            $reply_id = getInt('reply_id');

            loadClass('ZData');
            $f = new ZData();
            $f->addFields(self::fields());
            $data = $f->getData();

            if (!is_array($data)) {
                self::setJSON(array(
                    message => error($data)
                ));
            }

            self::$Model->update('comments', $data, "`ID`='$id'");

            // If $reply_id is empty
            if (empty($reply_id)) {
                if (!empty($reply)) {

                    // If $reply is not null, insert a new row to comments table
                    self::$Model->insert('comments', array(
                        hotel_id => $hotel_id,
                        customer_id => 0,
                        time => new Model_Expr('NOW()'),
                        comment => $reply,
                        user_id => $user_id,
                        status => 1,
                        root => $id
                    ));

                    // update row has ID = $id to change status field to 1  
                    self::$Model->update('comments', array(
                        status => 1
                            ), "`ID`='$id'");
                }
                // else do nothing
            } else {
                // else
                if (!empty($reply)) {
                    // If $reply is not null, update row that has ID = $reply_id
                    self::$Model->update('comments', array(
                        comment => $reply
                            ), "`ID`='$reply_id'");
                } else {

                    // else delete row that has to has ID = $reply_id                                           
                    self::$Model->delete('comments', "`ID`='$reply_id'");

                    // update row that has ID = $id
                    self::$Model->update('comments', array(
                        status => 0
                            ), "`ID`='$id'");
                }
            }

            self::setJSON(array(
                reload => true,
                close => true
            ));
        }
    }

    public function deleteAction() {
        $ids = getInt('ID', array(), 2);
        $hotel_id = get_hotel_id();

        if (count($ids) > 0) {
            $cond = implode(',', $ids);
            self::$Model->delete('comments', "`root` IN ($cond)");
        }
        self::setJSON(array(
            reload => true
        ));
    }

}
