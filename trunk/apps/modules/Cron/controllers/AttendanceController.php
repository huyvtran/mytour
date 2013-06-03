<?php class CronAttendanceController extends Zone_Action {
    public function indexAction() {
        $settings = self::$Model->fetchRow("SELECT * FROM `personnel_settings`");
        $rules = self::$Model->fetchAll("SELECT * FROM `personnel_lates` ORDER BY `time` DESC");
        $time_working = $settings['work_time_start'];

        $case = "CASE";
        foreach ( $rules as $rule ) {
            $case .= "\n WHEN
                    `b`.`time_start` > '{$rule['time']}'
                    AND CONCAT(',','{$rule['days']}',',') LIKE CONCAT('%,',DATE_FORMAT(`b`.`date`,'%w'),',%')
                THEN {$rule['money']}";
        }
        $case .="\n ELSE 0 \nEND";

        $date = getDateRq('date', date('d/m/Y',strtotime('yesterday')));

        $date_format = change_date_format($date);
        $users = self::$Model->fetchAll("SELECT
                `u`.`username`,
                `u`.`ID`,
                `a`.`name` as `fullname`,
                `a`.`code` as `code`,
                `b`.`time_start` as `time_start`,
                $case as `money`
            FROM `personnels` as `a`
            LEFT JOIN `users` as `u`
                ON `u`.`personnel_id`=`a`.`ID`
            LEFT JOIN `personnel_attendance_checkdates` as `b`
                ON `a`.`code`=`b`.`personnel_code`
            WHERE `a`.`job_status`<>'STOP_WORKING'
                AND `b`.`date`='$date_format'
                AND `b`.`time_start` > '$time_working'
                AND `u`.`ID` IS NOT NULL");

        $subject = "Thông báo phạt đi muộn ngày $date";
        $body = "Thông báo phạt đi muộn ngày $date:<br/>
            Họ tên: {fullname}<br/>
            Giờ đến: {time}<br/>
            Tiền phạt: {money}<br/>";

        foreach ( $users as $a ) {
            if ( $a['money'] == 0 )
                continue;
            $bd = str_ireplace(array('{fullname}', '{time}', '{money}'), array($a['fullname'], $a['time_start'], show_money($a['money'],'&#8363;')), $body);
            //$bd .= "<br/><i style='color:red'>Thông báo này thay cho thông báo phạt bạn nhận được chiều qua</i>";
            send_message($subject, $bd, array($a));
        }

        echo "<meta charset='utf8'/>";
        $k = 0;
        foreach ( $users as $a ) {
            if ( $a['money'] == 0 )
                continue;
            $k++;
            echo "$k  #ID: " . $a['ID'] . "-#Code: " . $a['code'] . "-#Name: " . $a['fullname'] . "-#Penalty: " . $a['money'] . "<br/>";
        }
        exit;
    }
}
