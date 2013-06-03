<?php

class MessageIndexController extends Zone_Action {

    const MAX_FILTER_FROM = 30;

    private $folders = null;
    private $setting = null;

    private function fields() {
        return array(
            user_id => get_user_id(),
            date => new Model_Expr('NOW()'),
            date_created => new Model_Expr('NOW()'),
            subject => array(
                type => 'CHAR',
                max_length => 255,
                label => 'Tiêu đề'
            ),
            body => array(
                type => 'HTML',
                max_length => 65000,
                label => 'Nội dung'
            )
        );
    }

    private function getSendUsers($rq) {
        $ids = getInt($rq, array(), 1);
        if (count($ids) == 0) {
            return array();
        }
        $ids = implode(',', $ids);
        $users = self::$Model->fetchAll("SELECT
                `a`.*
           FROM `users` as `a`
                WHERE `a`.`is_deleted`='no'
                    AND `a`.`ID` IN ($ids)");
        return $users;
    }

    private function getQuote($data) {
        return strip_tags($data);
    }

    private function getFolders() {
        if (!is_null($this->folders))
            return $this->folders;
        $user_id = get_user_id();
        $folders = self::$Model->fetchAll("SELECT
                    `a`.*,
                    SUM(IF(`b`.`is_read`='0',1,0)) as `unread`
                FROM `user_message_folders` as `a`
                LEFT JOIN `user_message_folder_maps` as `c`
                    ON `c`.`folder_id`=`a`.`ID`
                LEFT JOIN `user_message_informs` as `b`
                    ON `c`.`message_id`=`b`.`root_id`
                    AND `b`.`user_id`='$user_id'
                    AND `b`.`is_read`='0'
                    AND `b`.`is_deleted`='0'
                WHERE `a`.`user_id`='$user_id'
                GROUP BY `a`.`ID`");
        $this->folders = $folders;
        return $folders;
    }

    private function getSetting() {
        if (!is_null($this->setting))
            return $this->setting;
        $user_id = get_user_id();
        $setting = self::$Model->fetchRow("SELECT *
                FROM `user_message_settings`
                    WHERE `user_id`='$user_id'");

        if (!$setting) {
            self::$Model->insert('user_message_settings', array(
                user_id => $user_id
            ));
        }

        $this->setting = $setting;
        return $setting;
    }

    protected function init() {
        //auto filter
        $user_id = get_user_id();
        $folders = $this->getFolders();

        $total = self::$Model->fetchRow("SELECT
               SUM(IF(`is_deleted`='0',1,0)) as `unread`,
               SUM(IF(`is_deleted`='0' AND `type`='app',1,0)) as `notice`
            FROM `user_message_informs`
                WHERE
                    `user_id`='$user_id'
                    AND `is_deleted`='0'
                    AND `is_read`='0'
                GROUP BY `user_id`");

        self::set(array(
            folders => $folders,
            unread => $total['unread'],
            unreadNotice => $total['notice']
        ));

        if ((int) $total['unread'] > 0) {
            $this->autofilter();
        }
    }

    /*
     * Inbox
     * Find all messages which exists a sub message is mapped to user
     */

    public function indexAction() {
        $user_id = get_user_id();
        $setting = $this->getSetting();

        loadClass('ZList');
        $list = new ZList();

        $list->setPageLink('#Message');
        $list->setPageRange(30);
        $list->setSqlCount("SELECT
               COUNT(*)
            FROM `user_message_informs` as `a`
            LEFT JOIN `users` as `b` ON `b`.`ID`=`a`.`last_id`");

        $list->setSqlSelect("SELECT
                IF(`a`.`type`='app','Office',`b`.`fullname`) as `from`,
                `a`.`last_date` as `date`,
                `a`.`last_subject` as `subject`,
                `a`.`last_body` as `body`,
                `a`.`root_id` as `ID`,
                `a`.`is_read` as `is_read`,
                `a`.`num` as `num`,
                `a`.`has_file`
            FROM `user_message_informs` as `a`
            LEFT JOIN `users` as `b`
                ON `b`.`ID`=`a`.`last_id`");
        $list->setPageRange(max((int) $setting['page_range'], 25));
        $list->setWhere("`a`.`user_id`='$user_id'");
        $list->setWhere("`a`.`is_deleted`='0'");

        if (get('s', '')) {
            $s = get('s', '');
            $list->setVar(array('s' => $s));
            $list->setWhere(" EXISTS( SELECT *
                FROM `user_messages` as `s`
                    LEFT JOIN `users` as `n`
                        ON `s`.`user_id`=`n`.`ID`
                WHERE
                    ( `s`.`root_id`=`a`.`root_id` OR `s`.`ID`=`a`.`root_id` )
                     AND (
                        `s`.`subject` LIKE '%$s%'
                            OR `s`.`body` LIKE '%$s%'
                            OR `n`.`fullname` LIKE '%$s%'
                            OR `n`.`username` LIKE '%$s%'
                    )
                LIMIT 1 ) ");
        }

        //find in a certain folder
        $parent_id = getInt('parent_id', 0);
        $is_inbox = true;
        if ($parent_id) {
            $folder = self::$Model->fetchRow("SELECT *
                FROM `user_message_folders`
                    WHERE `ID`='$parent_id' AND `user_id`='$user_id'");
            if ($folder) {
                $list->setWhere("EXISTS( SELECT *
                    FROM `user_message_folder_maps` as `c`
                        WHERE
                            `c`.`message_id`=`a`.`root_id`
                            AND `c`.`folder_id`='$parent_id' LIMIT 1 )");
                $list->setVar(array('parent_id' => $parent_id));
                self::set('page_title', $folder['title']);
                self::set('tab_active', $folder['ID']);
                $is_inbox = false;
            }
        }

        $type = get('type');
        if ($type == 'app') {
            $is_inbox = false;
            $list->setWhere("`a`.`type`='app'");
            $list->setVar(array('type' => 'app'));
            self::set('page_title', 'Tin thông báo');
            self::set('tab_active', 'app');

            //sort for notice list
            if ($setting['sort_notice'] == 'read') {
                $list->setOrder("`a`.`is_read`, `a`.`last_date` DESC");
            } else {
                $list->setOrder("`a`.`last_date` DESC");
            }
        }

        if ($is_inbox) {
            $list->setWhere("`a`.`is_receiver`='1'");
            if ($setting['quick_display'] == 1) {
                $list->setWhere("NOT EXISTS( SELECT
                        `p`.`message_id`
                    FROM `user_message_folder_maps` as `p`
                        INNER JOIN `user_message_folders` as `q`
                            ON `p`.`folder_id`=`q`.`ID` AND `q`.`user_id`='$user_id'
                    WHERE `p`.`message_id`=`a`.`root_id`)");
            }
        }

        if ($type != 'app') {
            if ($setting['sort_inbox'] == 'read') {
                $list->setOrder("`a`.`is_read`, `a`.`last_date` DESC");
            } else {
                $list->setOrder("`a`.`last_date` DESC");
            }
        }

        self::set($list->run());
        self::set(array(
            folders => $this->getFolders()
        ));
    }

    public function trashAction() {
        $user_id = get_user_id();
        loadClass('ZList');
        $list = new ZList();

        $list->setPageLink('#Message/Index/Trash');
        $list->setPageRange(30);
        $list->setSqlCount("SELECT
               COUNT(*)
            FROM `user_message_informs` as `a`
            LEFT JOIN `users` as `b` ON `b`.`ID`=`a`.`last_id`");

        $list->setSqlSelect("SELECT
                IF(`a`.`type`='app','Office',`b`.`fullname`) as `from`,
                `a`.`last_date` as `date`,
                `a`.`last_subject` as `subject`,
                `a`.`last_body` as `body`,
                `a`.`root_id` as `ID`,
                `a`.`is_read` as `is_read`,
                `a`.`num` as `num`,
                `a`.`has_file`
            FROM `user_message_informs` as `a`
            LEFT JOIN `users` as `b` ON `b`.`ID`=`a`.`last_id`");

        $setting = $this->getSetting();
        $list->setPageRange(max((int) $setting['page_range'], 25));

        $list->setWhere("`a`.`user_id`='$user_id'");
        $list->setWhere("`a`.`is_deleted`='1'");
        $list->setOrder("`a`.`last_date` DESC");

        if (get('s', '')) {
            $s = get('s', '');
            $list->setVar(array('s' => $s));
            $list->setWhere(" EXISTS( SELECT *
                FROM `user_messages` as `s`
                WHERE
                    ( `s`.`root_id`=`a`.`root_id` OR `s`.`ID`=`a`.`root_id` )
                     AND ( `s`.`subject` LIKE '%$s%' OR `s`.`body` LIKE '%$s%' )
                    LIMIT 1) ");
        }

        self::set($list->run());
        self::set(array(
            folders => $this->getFolders()
        ));
    }

    public function outboxAction() {

        $user_id = get_user_id();
        loadClass('ZList');
        $list = new ZList();

        $list->setPageLink('#Message/Index/Outbox');
        $list->setPageRange(30);
        $list->setSqlCount("SELECT
               COUNT(*)
            FROM `user_message_informs` as `a`
            LEFT JOIN `users` as `b` ON `b`.`ID`=`a`.`last_id`");

        $list->setSqlSelect("SELECT
                IF(`a`.`type`='app','Office',`b`.`fullname`) as `from`,
                `a`.`last_date` as `date`,
                `a`.`last_subject` as `subject`,
                `a`.`last_body` as `body`,
                `a`.`root_id` as `ID`,
                `a`.`is_read` as `is_read`,
                `a`.`num` as `num`,
                `a`.`has_file`
            FROM `user_message_informs` as `a`
            INNER JOIN `user_messages` as `m`
                ON  `m`.`ID`=`a`.`root_id`
            LEFT JOIN `users` as `b`
                ON `b`.`ID`=`a`.`last_id`");

        $setting = $this->getSetting();
        $list->setPageRange(max((int) $setting['page_range'], 25));

        $list->setWhere("`a`.`user_id`='$user_id'");
        $list->setWhere("`a`.`is_deleted`='0'");
        $list->setWhere("`a`.`is_sender`='1'");

        if ($setting['sort_outbox'] == 'date') {
            $list->setOrder("`a`.`last_date` DESC, `m`.`date` DESC");
        } else {
            $list->setOrder("`m`.`date` DESC");
        }

        if (get('s', '')) {
            $s = get('s', '');
            $list->setVar(array('s' => $s));
            $list->setWhere(" EXISTS( SELECT *
                FROM `user_messages` as `s`
                WHERE
                    ( `s`.`root_id`=`a`.`root_id` OR `s`.`ID`=`a`.`root_id` )
                     AND ( `s`.`subject` LIKE '%$s%' OR `s`.`body` LIKE '%$s%' )
                    LIMIT 1) ");
        }
        self::set($list->run());
        self::set(array(
            folders => $this->getFolders()
        ));
    }

    public function addAction() {
        $user_id = get_user_id();
        $settings = self::$Model->fetchRow("SELECT *
            FROM `user_message_settings` WHERE `user_id`='$user_id'");
        self::set(array(
            settings => $settings
        ));

        if (isPost()) {
            loadClass('ZData');
            $f = new ZData();
            $f->addfields(self::fields());
            $data = $f->getData();

            if (!is_array($data)) {
                self::setJSON(array(
                    message => error($data)
                ));
            }

            $send_users = self::getSendUsers('to');

            if (count($send_users) == 0) {
                self::setJSON(array(
                    alert => error('Chưa có người nhận')
                ));
            }

            $data['is_draft'] = '0';
            $bool = self::$Model->insert('user_messages', $data);
            if (!$bool) {
                self::setJSON(array(
                    alert => error('Không thể gửi tin nhắn')
                ));
            }

            $message_id = self::$Model->lastId();

            //insert files
            $file_ids = getInt('files', array(), 1);
            $files = get_file_upload($file_ids, 'message');
            $ids = array();
            $file_messages = array();

            foreach ($files as $file) {
                $f = array();
                foreach (array('filename', 'name', 'size', 'type') as $k) {
                    $f[$k] = $file[$k];
                }
                $f['message_id'] = $message_id;
                $f['root_id'] = $message_id;
                $file_messages[] = $f;
                $ids[] = $file['ID'];
            }

            $has_file = 0;
            if (count($ids) > 0) {
                remove_file_upload($ids);
                $has_file = 1;
            }

            self::$Model->insertMany('user_message_files', $file_messages);

            $informs = array();
            $maps = array();
            $last_date = date('Y-m-d H:i:s');
            $last_subject = $this->getQuote($data['subject']);
            $last_body = $this->getQuote($data['body']);

            $not_found_me = true;

            foreach ($send_users as $u) {
                $maps[] = array(
                    user_id => $u['ID'],
                    message_id => $message_id,
                    root_id => $message_id,
                    is_read => 0
                );

                $inform = array(
                    user_id => $u['ID'],
                    root_id => $message_id,
                    last_message_id => $message_id,
                    is_receiver => 1,
                    is_read => 0,
                    last_id => $user_id,
                    last_date => $last_date,
                    last_subject => $last_subject,
                    last_body => $last_body,
                    num => 1,
                    is_sender => ($u['ID'] == $user_id ? 1 : 0),
                    has_file => $has_file
                );

                if ($u['ID'] == $user_id) {
                    $not_found_me = false;
                }
                $informs[] = $inform;
            }

            if ($not_found_me) {
                $informs[] = array(
                    user_id => $user_id,
                    root_id => $message_id,
                    last_message_id => $message_id,
                    is_receiver => 0,
                    is_read => 1,
                    last_id => $user_id,
                    last_date => $last_date,
                    last_subject => $last_subject,
                    last_body => $last_body,
                    num => 1,
                    is_sender => 1,
                    has_file => $has_file
                );
            }

            self::$Model->insertMany('user_message_maps', $maps);
            self::$Model->insertMany('user_message_informs', $informs);

            self::setJSON(array(
                alert => 'Thư của bạn đã được gửi đi',
                redirect => '#Message/Index/Outbox'
            ));
        }
    }

    /*
     * @desc
     * If user get a message with reply mode
     * they can view all message from root message
     * but the users which get it in cc or bbc mode
     * then get a new root message
     */

    public function replyAction() {
        $user_id = get_user_id();
        //only accept reply for a message had been sent to current user
        $post_id = getInt('message_id');

        //find type of map to this message
        $post = self::$Model->fetchRow("SELECT
                `a`.*,
                `u`.`fullname` as `from`,
                `u`.`username` as `from_username`,
                IFNULL(`a`.`root_id`,`a`.`ID`) as `root_id`,
                `b`.`user_id` as `reply_id`
            FROM `user_messages` as `a`
                LEFT JOIN `user_message_maps` as `b`
                        ON `a`.`ID`=`b`.`message_id`
                    AND `b`.`user_id`='$user_id'
                LEFT JOIN `users` as `u`
                        ON `a`.`user_id`=`u`.`ID`
                WHERE `a`.`ID`='$post_id' LIMIT 1");

        if (!$post || ( $post['user_id'] != $user_id && $post['reply_id'] != $user_id )) {
            self::setJSON(array(
                alert => error('Tin nhắn đã bị xóa hoặc không tồn tại')
            ));
        }

        self::set(array(
            post => $post
        ));

        $root_id = $post['root_id'];

        //for check this message is my own
        $rootPost = self::$Model->fetchRow("SELECT *
            FROM `user_messages` as `a`
                WHERE `a`.`ID`='$root_id'
                    AND `a`.`user_id` = '$user_id'");

        if (!$rootPost) {
            if ($post['user_id'] != $user_id && $post['reply_id'] != $user_id) {
                //user can reply if there is a message reply follow
                $rePost = self::$Model->fetchRow("SELECT *
                    FROM `user_messages` as `a`
                        INNER JOIN `user_message_maps` as `b`
                            ON `a`.`ID`=`b`.`message_id` AND `b`.`user_id`='$user_id'
                    WHERE `a`.`root_id`='$root_id' AND `a`.`date` >= '{$post['date']}'");

                if (!$rePost) {
                    self::setJSON(array(
                        alert => error('Bạn không được phép trả lời tin nhắn này')
                    ));
                }
            }
        }

        $mode = get('mode');
        if (!isPost()) {
            $to_users = array(get_user($post['user_id']));
            if ($mode == 'all') {
                $to_users = array_merge($to_users, self::$Model->fetchAll("SELECT `b`.*
                    FROM `user_message_maps` as `a`
                        INNER JOIN `users` as `b`
                            ON `a`.`user_id`=`b`.`ID`
                        WHERE
                            `a`.`message_id`='$post_id'
                            AND `a`.`role`='1'
                            AND `b`.`is_deleted`='no'
                            AND `b`.`ID`<>'$user_id'"));
            }

            $setting = self::$Model->fetchRow("SELECT *
                FROM `user_message_settings` WHERE `user_id`='$user_id'");

            self::set(array(
                setting => $setting,
                message => $post,
                to_users => $to_users
            ));
        }

        if (isPost()) {
            loadClass('ZData');
            $f = new ZData();
            $f->addfields(self::fields());
            $data = $f->getData();

            if (!is_array($data)) {
                self::setJSON(array(
                    message => error($data)
                ));
            }

            $send_users = self::getSendUsers('to');

            if (count($send_users) == 0) {
                self::setJSON(array(
                    alert => error('Chưa có người nhận')
                ));
            }

            $data['is_draft'] = '0';
            $data['root_id'] = $root_id;
            $bool = self::$Model->insert('user_messages', $data);

            if (!$bool) {
                self::setJSON(array(
                    alert => error('Không thể gửi được tin nhắn')
                ));
            }

            $message_id = self::$Model->lastId();

            //insert files
            $file_ids = getInt('files', array(), 1);
            $files = get_file_upload($file_ids, 'message');
            $ids = array();
            $file_messages = array();

            foreach ($files as $file) {
                $f = array();
                foreach (array('filename', 'name', 'size', 'type') as $k) {
                    $f[$k] = $file[$k];
                }
                $f['message_id'] = $message_id;
                $f['root_id'] = $root_id;
                $file_messages[] = $f;
                $ids[] = $file['ID'];
            }

            $has_file = 0;
            if (count($ids) > 0) {
                remove_file_upload($ids);
                $has_file = 1;
            }

            self::$Model->insertMany('user_message_files', $file_messages);

            $maps = array();
            $map_to_views = array();
            $informs = array();
            $last_date = date('Y-m-d H:i:s');
            $last_subject = $this->getQuote($data['subject']);
            $last_body = $this->getQuote($data['body']);

            self::$Model->exec("INSERT INTO
                `user_message_informs`
                    (   `user_id`,`root_id`,
                        `last_id`,`is_read`,
                        `last_date`,`last_subject`,
                        `last_body`,`is_deleted`,
                        `is_sender`,`has_file`,
                        `last_message_id`)
                VALUES (
                    '{$user_id}','{$root_id}',
                    '{$user_id}','1',
                    '$last_date','$last_subject',
                    '$last_body',
                    '0','1',
                    '$has_file','$message_id')
                    ON DUPLICATE KEY
                UPDATE
                    `is_sender`='1',
                    `is_read`='1',
                    `last_id`='$user_id',
                    `last_date`='$last_date',
                    `last_subject`='$last_subject',
                    `last_body`='$last_body',
                    `is_deleted`='0',
                    `has_file`='$has_file',
                    `last_message_id`='$message_id'");

            $message_can_views = self::$Model->fetchAll("SELECT `message_id`
                FROM `user_message_maps` WHERE `user_id`='$user_id' AND `root_id`='$root_id'");

            $uids = array();
            foreach ($send_users as $u) {
                $uids[] = $u['ID'];
                $maps[] = array(
                    user_id => $u['ID'],
                    message_id => $message_id,
                    root_id => $root_id,
                    is_read => 0
                );

                $informs[] = "(
                        '{$u['ID']}',
                        '{$root_id}',
                        '{$user_id}',
                        '0',
                        '$last_date',
                        '$last_subject',
                        '$last_body',
                        '0',
                        '1',
                        '$has_file',
                        '$message_id')";

                foreach ($message_can_views as $a) {
                    $map_to_views[] = array(
                        user_id => $u['ID'],
                        message_id => $a['message_id'],
                        root_id => $root_id,
                        is_read => 0,
                        role => 0
                    );
                }
            }

            self::$Model->insertMany('user_message_maps', $maps);

            //update for can view
            self::$Model->insertMany('user_message_maps', $message_can_views);

            $inform_values = implode(',', $informs);
            ///die(var_dump($inform_values));

            self::$Model->exec("INSERT INTO
                `user_message_informs`
                    (   `user_id`,
                        `root_id`,
                        `last_id`,
                        `is_read`,
                        `last_date`,
                        `last_subject`,
                        `last_body`,
                        `is_deleted`,
                        `is_receiver`,
                        `has_file`,
                        `last_message_id`
                    )
                VALUES $inform_values
                    ON DUPLICATE KEY
                UPDATE
                    `is_read`='0',
                    `is_receiver`='1',
                    `last_id`='$user_id',
                    `last_date`='$last_date',
                    `last_subject`='$last_subject',
                    `last_body`='$last_body',
                    `is_deleted`='0',
                    `has_file`='$has_file',
                    `last_message_id`='$message_id'");

            //udpate again num reply for every user
            $uids[] = $user_id;
            $uids = implode(',', $uids);

            $num_can_view_of_sender = self::$Model->fetchOne("SELECT
                COUNT(*)
                    FROM `user_messages` as `a`
                    LEFT JOIN
                        `user_message_maps` as `b`
                        ON
                            `b`.`message_id`=`a`.`ID` AND `b`.`user_id`='$user_id'
                    WHERE
                             `a`.`date` <= '{$post['date']}'
                         AND
                            (`a`.`root_id`='$root_id' OR `a`.`ID`='$root_id')
                         AND
                            (`a`.`user_id`='$user_id' OR `b`.`user_id`='$user_id')");

            self::$Model->exec("UPDATE
                    `user_message_informs`
                SET
                    `num`= GREATEST(`num`+1,$num_can_view_of_sender)
                WHERE
                    `root_id`='$root_id'
                    AND `user_id` IN ($uids)");

            self::setJSON(array(
                redirect => '#Message/Index/Outbox'
            ));
        }
    }

    public function viewAction() {
        $post_id = getInt('ID');
        $user_id = get_user_id();

        //find message
        $post = self::$Model->fetchRow("SELECT
                `a`.*,
                `b`.`user_id` as `reply_id`,
                `b`.`is_deleted`,
                `b`.`last_message_id`
            FROM `user_messages` as `a`
                INNER JOIN `user_message_informs` as `b`
                    ON `b`.`user_id`='$user_id' AND `b`.`root_id`=`a`.`ID`
            WHERE `a`.`is_draft`='0'
                AND `a`.`ID`='$post_id'
                AND `a`.`root_id` IS NULL ");

        //user only accept view this message if it is his or a reply
        if (!$post || ( $post['user_id'] != $user_id && $post['reply_id'] != $user_id )) {
            self::setJSON(array(
                alert => error('Tin nhắn không tồn tại hoặc đã bị xóa')
            ));
        }

        self::set('root', $post);
        $root_id = $post['root_id'] ? $post['root_id'] : $post['ID'];

        loadClass('ZList');
        $list = new ZList();
        //$list->setPageRange(5);
        $list->setLimit(false);
        $list->setOrder("`a`.`date`");
        $list->setSqlCount("SELECT
            COUNT(*) FROM `user_messages` as `a`
            LEFT JOIN `user_message_maps` as `b`
                ON `a`.`ID`=`b`.`message_id`
                    AND `b`.`user_id`='$user_id'");

        $list->setSqlSelect("SELECT
                    `a`.*,
                    IF(`a`.`user_id`='0','Office',`u`.`fullname`) as `from_name`,
                    `u`.`ID` as `from_id`,
                    `u`.`photo` as `from_photo`,
                    IFNULL(`b`.`is_read`,1) as `is_read`,
                    (SELECT COUNT(*)
                        FROM `user_message_maps` as `z`
                            WHERE `z`.`message_id`=`a`.`ID` AND `z`.`role`='1') as `to_list`
                FROM `user_messages` as `a`
                LEFT JOIN `user_message_maps` as `b`
                    ON `a`.`ID`=`b`.`message_id`  AND `b`.`user_id`='$user_id'
                LEFT JOIN `users` as `u`
                    ON `a`.`user_id`=`u`.`ID`");

        //message is mine or reply to me
        $list->setWhere("`a`.`is_draft`='0'");
        $list->setWhere("(`a`.`root_id`='$root_id' OR `a`.`ID`='$root_id')");
        $list->setWhere("(`b`.`message_id` IS NOT NULL OR `a`.`user_id`='$user_id')");
        //$list->setWhere("`a`.`ID` <= '{$post['last_message_id']}'");
        $list->setOrder("`a`.`date` DESC");

        self::set(array(
            root_id => $root_id
        ));

        self::set($list->run());

        //map files
        $posts = self::get('posts');
        foreach ($posts as $k => $a) {
            $posts[$k]['files'] = self::$Model->fetchAll("SELECT *
                FROM `user_message_files` WHERE `message_id`='{$a['ID']}'");
            //$posts[$k]['short_from'] = self::$Model->fetchAll("SELECT *
            // FROM `user_message_files` WHERE `message_id`='{$a['ID']}' LIMIT 1");
        }
        self::set('posts', $posts);

        //find all member
        $members = self::$Model->fetchAll("SELECT
                `b`.*
            FROM `user_message_informs` as `a`
                INNER JOIN `users` as `b`
                    ON `b`.`ID`=`a`.`user_id`
                        AND `a`.`root_id`='$root_id'");

        self::set('members', $members);
        self::set('folders', $this->getFolders());

        $inFolders = self::$Model->fetchAll("SELECT `a`.*
            FROM `user_message_folders` as `a`
                INNER JOIN `user_message_folder_maps` as `b`
                    ON `a`.`ID`=`b`.`folder_id` AND `b`.`message_id`='$root_id'");

        self::set('inFolders', $inFolders);

        //update read
        self::$Model->update('user_message_maps', array(
            is_read => 1
                ), "`user_id`='$user_id' AND `root_id`='$root_id'");

        self::$Model->update('user_message_informs', array(
            is_read => 1
                ), "`user_id`='$user_id' AND `root_id`='$root_id'");
    }

    /**
     * Load all receivers
     */
    public function quicktoAction() {
        $post_id = getInt('ID', 0);
        $user_id = get_user_id();

        self::removeLayout();

        //find message
        $post = self::$Model->fetchRow("SELECT
                `a`.*,
                `b`.`user_id` as `reply_id`
            FROM `user_messages` as `a`
                LEFT JOIN `user_message_maps` as `b`
                    ON `b`.`user_id`='$user_id' AND `b`.`message_id`=`a`.`ID`
            WHERE `a`.`is_draft`='0' AND `a`.`ID`='$post_id'");

        //user only accept view this message if it is his or a reply
        if (!$post) {
            self::setContent(error('Tin nhắn không tồn tại hoặc đã bị xóa'));
        }

        $users = self::$Model->fetchAll("SELECT `u`.*
            FROM `user_message_maps` as `a`
            INNER JOIN `users` as `u`
                ON `u`.`ID`=`a`.`user_id`
                    AND `a`.`message_id`='$post_id'
                    AND `role`='1'");
        self::set(array(
            post => $post,
            users => $users
        ));
    }

    //remove a message map to user
    public function deleteAction() {
        $user_id = get_user_id();
        $ids = getInt('ID', array(), 2);
        $mode = get('mode');
        foreach ($ids as $post_id) {
            if ($mode == 'complete') {
                $message = self::$Model->fetchRow("SELECT
                        * FROM `user_messages`
                        WHERE `ID`='$post_id'
                            AND `is_draft`='0'
                            AND `root_id` IS NULL");

                if (!$message)
                    continue;
                self::$Model->update('user_message_informs', array(
                    is_deleted => 2
                        ), "`root_id`='$post_id' AND `user_id`='$user_id'");

                $test = self::$Model->fetchRow("SELECT *
                    FROM `user_message_informs`
                    WHERE `root_id`='$post_id' AND `is_deleted`<>'2'");

                //if empty then remove all
                if (!$test) {
                    //remove files
                    self::$Model->delete('user_messages', "`ID`='$post_id' OR `root_id`='$post_id'");
                    self::$Model->delete('user_message_folder_maps', "`message_id`='$post_id'");
                    self::$Model->delete('user_message_informs', "`root_id`='$post_id'");
                    self::$Model->exec("DELETE `a`
                        FROM `user_message_maps` as `a`
                            INNER JOIN `user_messages` as `b`
                                ON `a`.`message_id`=`b`.`ID`
                            WHERE `b`.`root_id`='$post_id'");

                    $files = self::$Model->fetchAll("SELECT *
                        FROM `user_message_files`
                            WHERE `root_id`='$post_id'");

                    foreach ($files as $f) {
                        @unlink("files/message/{$f['name']}");
                    }
                    self::$Model->delete('user_message_files', "`root_id`='$post_id'");
                }
            } else {
                //remove map
                self::$Model->update('user_message_informs', array(
                    is_deleted => 1
                        ), "`root_id`='$post_id' AND `user_id`='$user_id'");
            }
        }

        $redirect_url = get('go');
        if ($redirect_url) {
            self::setJSON(array(
                redirect => '#' . $redirect_url
            ));
        } else {
            self::setJSON(array(
                reload => true
            ));
        }
    }

//    public function fixAction() {
//        $posts = self::$Model->fetchAll("SELECT *
//            FROM `user_messages` WHERE `user_id`='0'");
//        foreach ( $posts as $post ) {
//            $post_id = $post['ID'];
//            self::$Model->delete('user_messages', "`ID`='$post_id' OR `root_id`='$post_id'");
//            self::$Model->delete('user_message_folder_maps', "`message_id`='$post_id'");
//            self::$Model->delete('user_message_informs', "`root_id`='$post_id'");
//            self::$Model->exec("DELETE `a`
//                FROM `user_message_maps` as `a`
//                    INNER JOIN `user_messages` as `b`
//                        ON `a`.`message_id`=`b`.`ID`
//                    WHERE `b`.`root_id`='$post_id'");
//
//            $files = self::$Model->fetchAll("SELECT *
//                FROM `user_message_files`
//                    WHERE `root_id`='$post_id'");
//
//            foreach ( $files as $f ) {
//                @unlink("files/message/{$f['name']}");
//            }
//            self::$Model->delete('user_message_files', "`root_id`='$post_id'");
//            echo "<h1>{$post['subject']}</h1>";
//            echo $post['body'];
//        }
//        die('aaaa');
//    }

    /**
     * Move message had deleted go back
     */
    public function revertAction() {
        $user_id = get_user_id();
        $ids = getInt('ID', array(), 2);
        if (count($ids) > 0) {
            $ids = implode(',', $ids);
            self::$Model->update('user_message_informs', array(
                is_deleted => 0
                    ), "`root_id` IN ($ids)
                        AND `user_id`='$user_id'
                        AND `is_deleted`='1'");
        }

        $redirect_url = get('go');
        if ($redirect_url) {
            self::setJSON(array(
                redirect => '#' . $redirect_url
            ));
        } else {
            self::setJSON(array(
                reload => true
            ));
        }
    }

    /**
     * Mark messages to be unread or read
     */
    public function markAction() {
        $user_id = get_user_id();
        $ids = getInt('ID', array(), 2);

        if (count($ids) > 0) {
            $ids = implode(',', $ids);
            self::$Model->update('user_message_informs', array(
                is_read => get('type') == 'unread' ? 0 : 1
                    ), "`root_id` IN ($ids)
                        AND `user_id`='$user_id'");
        }

        self::setJSON(array(
            reload => true
        ));
    }

    /*
     * Move message to new folder
     */

    public function moveAction() {
        $user_id = get_user_id();
        $fids = getInt('folder_ids', array(), 2);
        $mids = getInt('ID', array(), 2);

        if (count($fids) > 0 && count($mids) > 0) {
            $fids = implode(',', $fids);
            $mids = implode(',', $mids);

            $posts = self::$Model->fetchAll("SELECT *
                FROM `user_message_informs`
                    WHERE `user_id`='$user_id'
                        AND `root_id` IN($mids)");

            $folders = self::$Model->fetchAll("SELECT *
                FROM `user_message_folders`
                    WHERE `user_id`='$user_id'
                        AND `ID` IN($fids)");

            if (count($posts) > 0 && count($folders) > 0) {
                $mids = array();
                $fids = array();

                foreach ($posts as $a) {
                    $mids[] = $a['root_id'];
                }

                foreach ($folders as $a) {
                    $fids[] = $a['ID'];
                }

                $maps = array();
                foreach ($mids as $m) {
                    foreach ($fids as $f) {
                        $maps[] = array(
                            type => 1,
                            message_id => $m,
                            folder_id => $f
                        );
                    }
                }

                $mids = implode(',', $mids);
                $fids = implode(',', $fids);

                //remove old mapping
                self::$Model->delete("user_message_folder_maps", "`message_id` IN($mids) OR `folder_id` NOT IN($fids)");

                self::$Model->insertMany('user_message_folder_maps', $maps);
            }
        }

        self::setJSON(array(
            reload => true
        ));
    }

    /**
     * Tear a parent map
     */
    public function tearAction() {
        $user_id = get_user_id();
        $fid = getInt('folder_id', 0);
        $mid = getInt('ID', 0);
        $post = self::$Model->fetchRow("SELECT *
                FROM `user_message_informs`
                    WHERE `user_id`='$user_id'
                        AND `root_id`='$mid'");
        if ($post) {
            self::$Model->delete("user_message_folder_maps", "`message_id`='$mid' AND `folder_id`='$fid'");
        }
        self::setJSON(array(
            reload => true
        ));
    }

    /*
     * Add message to more folder
     */

    public function appendAction() {
        $user_id = get_user_id();
        $fids = getInt('folder_ids', array(), 2);
        $mids = getInt('ID', array(), 2);

        if (count($fids) > 0 && count($mids) > 0) {
            $fids = implode(',', $fids);
            $mids = implode(',', $mids);

            $posts = self::$Model->fetchAll("SELECT *
                FROM `user_message_informs`
                    WHERE `user_id`='$user_id'
                        AND `root_id` IN($mids)");

            $folders = self::$Model->fetchAll("SELECT *
                FROM `user_message_folders`
                    WHERE `user_id`='$user_id'
                        AND `ID` IN($fids)");

            if (count($posts) > 0 && count($folders) > 0) {
                $mids = array();
                $fids = array();

                foreach ($posts as $a) {
                    $mids[] = $a['root_id'];
                }

                foreach ($folders as $a) {
                    $fids[] = $a['ID'];
                }

                $maps = array();
                foreach ($mids as $m) {
                    foreach ($fids as $f) {
                        $maps[] = array(
                            type => 1,
                            message_id => $m,
                            folder_id => $f
                        );
                    }
                }

                $mids = implode(',', $mids);
                $fids = implode(',', $fids);

                self::$Model->insertMany('user_message_folder_maps', $maps);
            }
        }

        self::setJSON(array(
            reload => true
        ));
    }

    /**
     * Config for signature
     */
    public function settingAction() {
        $user_id = get_user_id();
        if (isPost()) {
            loadClass('ZData');
            $f = new ZData();
            $f->addField(array(
                page_range => array(
                    type => 'ENUM',
                    value => array(25, 50, 75, 100),
                    no_empty => true,
                    label => 'Số tin nhắn trên mỗi trang'
                ),
                sort_inbox => array(
                    type => 'CHAR'
                ),
                sort_outbox => array(
                    type => 'CHAR'
                ),
                sort_notice => array(
                    type => 'CHAR'
                ),
                quick_display => array(
                    type => 'INT',
                    label => 'Hiển thị tối ưu'
                ),
                use_signature => array(
                    type => 'INT',
                    label => 'Sử dụng chữ ký'
                ),
                signature => array(
                    type => 'CHAR'
                )
            ));

            $data = $f->getData();
            self::$Model->exec("INSERT INTO `user_message_settings`
               (    `user_id`,
                    `use_signature`,
                    `signature`,
                    `sort_inbox`,
                    `sort_outbox`,
                    `sort_notice`,
                    `page_range`,
                    `quick_display`)
               VALUES('$user_id',
                        '{$data['use_signature']}',
                        '{$data['signature']}',
                        '{$data['sort_inbox']}',
                        '{$data['sort_outbox']}',
                        '{$data['sort_notice']}',
                        '{$data['page_range']}',
                        '{$data['quick_display']}'
                )
                ON DUPLICATE KEY
               UPDATE
                    `use_signature`='{$data['use_signature']}',
                    `signature`='{$data['signature']}',
                    `sort_inbox`='{$data['sort_inbox']}',
                    `sort_outbox`='{$data['sort_outbox']}',
                    `sort_notice`='{$data['sort_notice']}',
                    `page_range`='{$data['page_range']}',
                    `quick_display`='{$data['quick_display']}'
               ");
        }

        $post = self::$Model->fetchRow("SELECT *
                FROM `user_message_settings`
                    WHERE `user_id`='$user_id'");

        $folders = $this->getFolders();
        self::set(array(
            post => $post,
            folders => $folders
        ));
    }

    public function fileAction() {
        $file_id = getInt('ID', 0);
        $user_id = get_user_id();
        self::removeLayout();

        $file = self::$Model->fetchRow("SELECT *
                FROM `user_message_files`
                    WHERE `ID`='$file_id' LIMIT 1");

        $file_path = "files/message/{$file['name']}";
        if (!$file || !file_exists($file_path)) {
            self::setError(error('File đã bị xóa hoặc không tồn tại'));
        }

        $message_id = $file['message_id'];
        //find message
        $post = self::$Model->fetchRow("SELECT
                `a`.*,
                `b`.`user_id` as `reply_id`
            FROM `user_messages` as `a`
                LEFT JOIN `user_message_maps` as `b`
                    ON `b`.`user_id`='$user_id' AND `b`.`message_id`=`a`.`ID`
            WHERE `a`.`is_draft`='0' AND `a`.`ID`='$message_id'");

        //user only accept view this message if it is his or a reply
        if (!$post) {
            self::setError(error('Bạn không được phép xem file này'));
        }

        show_file($file, 'message');
    }

    private function fieldFolder() {
        return array(
            user_id => get_user_id(),
            title => array(
                type => 'CHAR',
                max_length => 255,
                no_empty => true,
                label => 'Tên'
            ),
            text_filter => array(
                type => 'CHAR',
                max_length => 255
            ),
            logic => array(
                type => 'ENUM',
                value => array(0, 1),
                default_value => 1
            )
        );
    }

    public function addfolderAction() {
        self::removeLayout();
        self::addJSON(array(
            frame_title => 'Thêm thư mục tin nhắn'
        ));
        if (isPost()) {
            loadClass('ZData');
            $f = new ZData();
            $f->addField($this->fieldFolder());
            $data = $f->getData();
            if (!is_array($data)) {
                self::setJSON(array(
                    message => error($data)
                ));
            }

            $froms = getInt('from', array(), 1);
            if (count($froms) > self::MAX_FILTER_FROM) {
                self::setJSON(array(
                    message => error('Chỉ được chọn không quá ' + self::MAX_FILTER_FROM + ' người gửu')
                ));
            }

            $data['from'] = implode(',', $froms);

            self::$Model->insert('user_message_folders', $data);
            self::setJSON(array(
                close => true,
                reload => true
            ));
        }
    }

    public function editfolderAction() {
        self::removeLayout();
        $user_id = get_user_id();
        $post_id = getInt('ID');

        $post = self::$Model->fetchRow("SELECT *
            FROM `user_message_folders` WHERE `ID`='$post_id' AND `user_id`='$user_id'");
        self::set(array(
            post => $post
        ));

        $uids = array();
        $users = array();
        foreach (explode(',', $post['from']) as $i) {
            if (is_numeric($i)) {
                $uids[] = $i;
            }
        }
        if (count($uids) > 0) {
            $users = get_user($uids);
        }
        self::set('users', $users);

        self::addJSON(array(
            frame_title => 'Sửa thư mục tin nhắn'
        ));

        if (isPost()) {
            loadClass('ZData');
            $f = new ZData();
            $f->addField($this->fieldFolder());
            $data = $f->getData();
            if (!is_array($data)) {
                self::setJSON(array(
                    message => error($data)
                ));
            }

            $froms = getInt('from', array(), 1);
            if (count($froms) > self::MAX_FILTER_FROM) {
                self::setJSON(array(
                    message => error('Chỉ được chọn không quá ' + self::MAX_FILTER_FROM + ' người gửu')
                ));
            }

            $data['from'] = implode(',', $froms);

            self::$Model->update('user_message_folders', $data, "`ID`='$post_id' AND `user_id`='$user_id'");
            self::setJSON(array(
                close => true,
                reload => true
            ));
        }
    }

    public function deletefolderAction() {
        self::removeLayout();
        $user_id = get_user_id();
        $post_id = getInt('ID');
        $bool = self::$Model->delete('user_message_folders', "`ID`='$post_id' AND `user_id`='$user_id'");
        if ($bool)
            self::$Model->delete('user_message_folder_maps', "`folder_id`='$post_id'");
        self::setJSON(array(
            reload => true
        ));
    }

    /**
     * Auto filter new message to folder
     * I update max up to 100 records before load message
     *
     * @return null
     */
    private function autofilter() {
        $user_id = get_user_id();
        $folders = $this->getFolders();
        $setting = $this->getSetting();
        $last_id = (int) $setting['last_id'];

        $case = array();
        foreach ($folders as $f) {
            $sub_case = array();
            if (!empty($f['text_filter'])) {
                $s = explode(',', $f['text_filter']);
                $s = array_slice(array_unique($s), 0, 15);
                $a = array();
                foreach ($s as $p) {
                    $a[] = "( `m`.`subject` LIKE '%$p%' OR `m`.`body` LIKE '%$p%' )";
                }
                if (count($a) > 0) {
                    $a = implode(' OR ', $a);
                    $sub_case[] = "($a)";
                }
            }

            if (!empty($f['from'])) {
                $s = $f['from'];
                $sub_case[] = "( ',$s,' LIKE CONCAT('%,',`m`.`user_id`,',%') )";
            }

            if (count($sub_case) == 0)
                continue;
            $sub_case = implode($f['logic'] == '1' ? ' AND ' : ' OR ', $sub_case);
            $sub_case = " EXISTS(SELECT *
                FROM `user_messages` as `m`
                WHERE IFNULL(`m`.`root_id`,`m`.`ID`)=`a`.`root_id` AND ( $sub_case ) LIMIT 1) ";
            $case[] = " IF($sub_case,1,0) as `f{$f['ID']}` ";
        }

        if (count($case) == 0)
            return;

        $case = implode(',', $case);
        $posts = self::$Model->fetchAll("SELECT
                    `a`.*,
                    `a`.`last_message_id`,
                    `a`.`root_id`,
                    $case
                FROM `user_message_informs` as `a`
                    WHERE
                        `a`.`user_id`='$user_id'
                        AND `a`.`is_read`='0'
                        AND `a`.`is_receiver`='1'
                        AND `a`.`last_message_id` > '$last_id'
                    ORDER BY `a`.`last_message_id`
                        LIMIT 100");

        if (count($posts) > 0) {
            $maps = array();
            foreach ($posts as $a) {
                foreach ($folders as $f) {
                    if ($a["f{$f['ID']}"] == 1)
                        $maps[] = array(
                            message_id => $a['root_id'],
                            folder_id => $f['ID'],
                            type => 0
                        );
                }
            }

            self::$Model->insertMany('user_message_folder_maps', $maps);
            self::$Model->update('user_message_settings', array(
                last_id => (int) ($posts[count($posts) - 1]['last_message_id'])
            ));
        }
    }

}