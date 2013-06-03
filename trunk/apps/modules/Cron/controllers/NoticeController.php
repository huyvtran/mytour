<?php

class CronNoticeController extends Zone_Action {

    public function indexAction() {
        die(' ');
        set_time_limit(0);

        //write_log(date('H:i:s d/m/Y')."\n","log.txt");
        $user_id = get_user_id();
        $time = time();
        $date = date('Y-m-d');
        $date1 = date('Y-m-d', strtotime('-1 day', $date));
        $date2 = date('Y-m-d', strtotime('+1 day', $date));

        $posts = self::$Model->fetchAll("SELECT *
			FROM `calendars`
			WHERE
				`is_draft`='no'
				AND ( `repeat_alert`='yes' OR `repeat_email`='yes' )
				AND `time_start` IS NOT NULL
			AND (
					( `repeat_type`='none'
						AND CAST(CONCAT(`date_start`,' ',`time_start`) as DATETIME )
							>= NOW() )
					OR (
						`repeat_type`<>'none'
						AND ( `repeat_date_end` IS NULL
						OR `repeat_date_end` >= '$date2' )
					)
				)");

        self::removeLayout();

        foreach ( $posts as $post ) {
            $ins = 60 * ((int) $post['repeat_time_number']) * ((int) $post['repeat_time_unit']);

            $stone = 0;
            if ( $post['repeat_type'] == 'none' ) {
                $stone = strtotime($post['date_start'] . ' ' . $post['time_start']);
            } else
            if ( $post['repeat_type'] == 'year' ) {
                $t = strtotime($post['date_start']);
                $stone = strtotime(date('Y') . '-' . date('m-d', $t) . ' ' . $post['time_start']);
            } else
            if ( $post['repeat_type'] == 'week' ) {
                $t = strtotime($post['date_start']);
                //$stone = strtotime(date('Y') . '-' . date('m-d',$t).' '.$post['time_start']);
                continue;
            } else
            if ( $post['repeat_type'] == 'day' ) {
                //
                continue;
            }

            if ( time() + $ins >= $stone ) {
                if ( $post['date_checked'] == date('Y-m-d H:i:s', $stone - $ins) ) {
                    continue;
                }

                //	echo date('Y-m-d H:i:s',$stone - $ins);
                self::$Model->update('calendars', array(
                    date_checked => date('Y-m-d H:i:s', $stone - $ins)
                        ), "ID='{$post['ID']}'");

                if ( $post['repeat_alert'] == 'yes' ) {
                    if ( $post['type'] == 'user' ) {
                        if ( self::$Model->fetchRow("SELECT `ID` FROM `users` WHERE `ID`='{$post['created_by_id']}' AND `is_deleted`='no'") ) {
                            self::$Model->insert('notices', array(
                                user_id => $post['created_by_id'],
                                created_by_id => $post['created_by_id'],
                                content => '',
                                title => 'Nhắc sự kiện: ' . $post['title'],
                                date => mysql_date(),
                                url => '#Calendar/Index/View?ID=' . $post['ID']
                            ));
                        }

                        $email = self::$Model->fetchOne("SELECT `email`
							FROM `users`
							WHERE
								`is_deleted`='no'
								AND `ID`='{$post['created_by_id']}'");
                        @mail($email, "Office Event:" + $post['title'], "Your event will coming soon.
							{$post['content']}", "From:deliver@office.com/r/nReply-to:$email");
                    }$users = self::$Model
                            ->fetchAll("SELECT `a`.`ID`,`a`.`email`
							FROM `users` as `a`
							LEFT JOIN `calendars_users` as `b`
							ON `a`.`ID`=`b`.`user_id`
								AND `b`.`calendar_id`='{$post['ID']}
						'
							WHERE
								`b`.`calendar_id`='{$post['ID']}'
								AND `b`.`is_guest`='yes'
								AND `b`.`status`<>'no'
								AND `a`.`is_deleted`='no'");
                    foreach ( $users as $u ) {
                        self::$Model->insert('notices', array(
                            user_id => $u['ID'],
                            created_by_id => $post['created_by_id'],
                            content => '',
                            title => 'Nhắc sự kiện: ' . $post['title'],
                            date => mysql_date(),
                            url => $post['type'] == 'user' ? '#Calendar/Invite/View?ID=' . $post['ID'] : '#Calendar/Company/View?ID=' . $post['ID']
                        ));
                        $email = $u['email'];
                        @mail($email, "Office Event:" + $post['title'], "Your event will coming soon.
							{$post['content']}", "From:deliver@office.com/r/nReply-to:$email");
                    }
                }
            }
        }

    }

}