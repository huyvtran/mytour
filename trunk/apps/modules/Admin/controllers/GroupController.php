<?php

class AdminGroupController extends Zone_Action {

    public function fields() {
        $roles = array();

        foreach ( self::getConfig('modules.roles') as $a ) {
            foreach ( (array) $a[1] as $i ) {
                $roles[] = $i;
            }
        }

        $data = array(
            title => array(
                type => 'CHAR',
                label => 'Tên phòng',
                no_empty => true
            ),
            roles => array(
                type => 'LIST',
                value => $roles,
                label => 'Quyền'
            ),
            date_updated => array(
                type => 'NONE',
                default_value => new Model_Expr('NOW()')
            ),
            updated_by_id => array(
                type => 'NONE',
                default_value => get_user_id()
            ),
            max_size_upload => array(
                type => 'INT',
                label => 'Thuộc nhóm',
                default_value => 0
            )
        );
        return $data;

    }

    public function indexAction() {
        $vars = array();
        $where = array();
        $order_by = " ORDER BY `a`.`ID` DESC";

        if ( isset($_REQUEST['p']) ) {
            $vars['p'] = $current_page;
        }

        $field_order = array('title', 'username', 'date_created');
        foreach ( $field_order as $f ) {
            if ( get('order_by') == $f ) {
                $order_by = "ORDER BY $f " . ( get('order_type') == 'desc' ? 'desc' : 'asc');
                $vars['order_by'] = $f;
                if ( get('order_type') == 'desc' ) {
                    $vars['order_by'] = 'desc';
                }
                break;
            }
        }

        $where = count($where) > 0 ? ' WHERE ' . implode(' AND ', $where) : '';

        $posts = self::$Model->fetchAll("SELECT
                `a`.*,
                `b`.`ID` as `created_by_id`,
                `b`.`username` as `created_by_name`,
                `b`.`is_deleted` as `created_is_deleted`,
                `c`.`ID` as `updated_by_id`,
                `c`.`username` as `updated_by_name`,
                `c`.`is_deleted` as `updated_is_deleted`,
                (SeLECT COUNT(*) FROM `users` as `u` WHERE `u`.`group_id`=`a`.`ID`) as `number_member`
			FROM `groups` as `a`
                LEFT JOIN `users` as `b`
                    ON `b`.`ID`=`a`.`created_by_id`
                LEFT JOIN `users` as `c`
                    ON `c`.`ID`=`a`.`updated_by_id`
			$where $order_by");

        self::set(array(
            vars => $vars,
            posts => $posts
        ));

    }

    public function addAction() {
        if ( isPost() ) {
            loadClass('ZData');
            $f = new ZData();
            $f->addField(self::fields());
            $data = $f->getData();

            if ( !is_array($data) ) {
                self::setJSON(array(
                    message => error($data)
                ));
            }

            $data['date_created'] = new Model_Expr('NOW()');
            $data['created_by_id'] = get_user_id();

            self::$Model->insert('groups', $data);

            self::setJSON(array(
                redirect => "#Admin/Group"
            ));
        }

    }

    public function editAction() {

        $group_id = getInt('ID', 0);
        $post = self::$Model->fetchRow("SELECT * FROM `groups` WHERE `ID`='$group_id'");
        if ( !$post ) {
            self::setJSON(array(
                alert => error(translate('default.add.post_not_found'))
            ));
        }

        self::set('post', $post);

        if ( isPost() ) {
            loadClass('ZData');
            $f = new ZData();
            $f->addField(self::fields($post));
            $data = $f->getData();

            if ( !is_array($data) ) {
                self::setJSON(array(
                    message => error($data)
                ));
            }

            self::$Model->update('groups', $data, " `ID`='$group_id'");

            self::setJSON(array(
                redirect => '#Admin/Group'
            ));
        }

    }

    public function deleteAction() {
        $post_id = getInt('ID', 0);
        if ( self::$Model->fetchRow("SELECT * FROM `users`
			WHERE `is_deleted`='no' AND `group_id`='$post_id'") ) {
            self::setJSON(array(
                alert => error(translate('default.group.can_not_delete_group_include_member'))
            ));
        }

        self::$Model->delete('groups', "`ID`='$post_id'");

        self::setJSON(array(
            redirect => '#Admin/Group'
        ));

    }

}

?>