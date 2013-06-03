<?php

class ChatIndexController extends Zone_Action {

    public function dataAction() {
        self::removeLayout();
        $user_id = get_user_id();

        if (isPost()) {
            loadClass('ZData');
            $f = new ZData();
            $f->addField(array(
                from_id => array(
                    type => 'NONE',
                    default_value => $user_id
                ),
                to_id => array(
                    type => 'INT'
                ),
                message => array(
                    type => 'TEXT',
                    no_empty => true,
                    min_length => 1
                ),
                date => array(
                    type => 'NONE',
                    default_value => new Model_Expr('NOW()')
                )
            ));

            $data = $f->getData();
            /* 			if( !is_array($data)){
              self::setJSON(array(
              alert	=> $data
              ));
              }
             */
            self::$Model->insert('chats', $data);
        }

        self::$Model->update('users', array(
            date_active => new Model_Expr('NOW()')
                ), "`ID`='$user_id'");

        $data = array();
        $data['status'] = '200';

        $user = self::$Model->fetchRow("SELECT
				`title` as `status`,
				`ID`,
				`username`,
				`photo`,
				`date_active`
			FROM `users`
			WHERE `ID`='$user_id'");

        $data['me'] = $user;

        $users = self::$Model->fetchAll("SELECT
				`ID`,
				`username`,
				`photo`,
				`date_active`,
				TIMESTAMPDIFF(SECOND,IF(`date_active`,`date_active`,'1970-01-01'),NOW()) as `active`,
				`title` as `status`
			FROM `users`
				WHERE `is_deleted`='no' AND `ID`<>'$user_id'");

        $messages = self::$Model->fetchAll("SELECT `a`.*
				,`b`.`username` as `from_name`
				,`b`.`photo` as `from_photo`
				,TIMESTAMPDIFF(SECOND,IF(`b`.`date_active`,`b`.`date_active`,'1970-01-01'),NOW()) as `from_active`
				,`c`.`username` as `to_name`
			FROM `chats` as `a`
			LEFT JOIN `users` as `b`
				ON `b`.`ID`=`a`.`from_id`
			LEFT JOIN `users` as `c`
				ON `c`.`ID`=`a`.`to_id`
			WHERE `a`.`to_id`='$user_id' AND `a`.`to_read`='no'
			ORDER BY `a`.`date`");

        self::$Model->update("chats", array(
            from_read => 'yes'
                ), "`from_id`='$user_id' AND `from_read`='no'");

        self::$Model->update("chats", array(
            to_read => 'yes'
                ), "`to_id`='$user_id' AND `to_read`='no'");

        //self::$Model->delete("chats","from_read='yes' AND to_read='yes'");

        $data['users'] = $users;
        $data['messages'] = $messages;

        self::setContent(json_encode($data));
    }

    public function changestatusAction() {
        self::removeLayout();

        if (isPost()) {
            return true;
        }

        loadClass('ZData');
        $f = new ZData();
        $f->addField(array(
            title => array(
                type => 'TEXT'
            )
        ));

        $data = $f->getData();
        if (!is_array($data)) {
            return false;
        }

        $data['title'] = short_title($data['title'], 100);
        self::$Model->update('users', $data, "`ID`='" . get_user_id() . "'");

        self::$Model->insert('notices', array(
            user_id => 0,
            date => new Model_Expr('NOW()'),
            created_by_id => $user['ID'],
            title => 'Thông báo từ hệ thống',
            content => $data['title'],
            url => '#User/Info?ID=' . $user['ID']
        ));

        self::setContent('');
    }

}
