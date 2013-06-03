<?php

class AdminExportController extends Zone_Action {

    private function fields() {
        $roles = array();

        foreach ( self::getConfig('modules.roles') as $a ) {
            foreach ( (array) $a[1] as $i ) {
                $roles[] = $i;
            }
        }
        $data = array(
            username => array(
                type => 'CHAR',
                no_empty => true,
                min_length => 4,
                max_length => 30,
                label => translate('admin.user.field.username'),
            ),
            password => array(
                type => 'PASSWORD',
                no_empty => true,
                min_length => 4,
                max_length => 32,
                label => translate('admin.user.field.password'),
            ),
            email => array(
                type => 'EMAIL',
                no_empty => true,
                label => translate('admin.user.field.email'),
            ),
            phone => array(
                type => 'PHONE',
                no_empty => true,
                label => translate('admin.user.field.phone'),
            ),
            fullname => array(
                type => 'CHAR',
                no_empty => true,
                label => translate('admin.user.field.name'),
            ),
            group_id => array(
                type => 'INT',
                label => translate('admin.user.field.group')
            ),
            department_id => array(
                type => 'INT',
                label => translate('admin.user.field.department_id')
            ),
            personnel_id => array(
                type => 'INT',
                label => translate('admin.user.field.personnel')
            ),
            inherit_roles => array(
                type => 'ENUM',
                value => array('yes', 'no'),
                default_value => 'yes'
            ),
            roles => array(
                type => 'LIST',
                value => $roles,
                label => translate('admin.user.field.role')
            )
        );
        return $data;

    }

    public function indexAction() {
        $vars = array();
        $order_by = " ORDER BY `a`.`is_admin`,`a`.`ID` DESC";

        $current_page = max(getInt('p', 1), 1);
        $range_page = max(getInt('rp', 50), 50);

        if ( isset($_REQUEST['p']) ) {
            $vars['p'] = $current_page;
        }

        if ( isset($_REQUEST['rp']) ) {
            $vars['rp'] = $range_page;
        }

        $field_groups = array(
            '`a`.`group_id`' => 'group_id',
            '`a`.`department_id`' => 'department_id'
        );

        $field_texts = array(
		'`a`.`username`' => 'username',
	);

       $where = array("is_deleted='no'");
        $word = get('s', '');
        if ( $word != '' ) {
            $a = array();
            foreach ( $field_texts as $k => $f ) {
                $a[] = app_search_sql_text($k, $word);
            }
            $vars['s'] = $_REQUEST['s'];
            $where[] = '(' . implode(' OR ', $a) . ')';
        }


        foreach ($field_texts as $k => $f) {
			$word = get($f, '');
			if ($word == '')
				continue;
			$where = app_search_sql_text($k, $word);
			$vars[$f] = $_REQUEST[$f];
		}


        $limit = "LIMIT " . ($current_page - 1) * $range_page . ",$range_page";
        $field_order = array('username', 'name', 'date_created', 'group_id');
        foreach ( $field_order as $f ) {
            if ( get('order_by') == $f ) {
                $order_by = "ORDER BY $f " . (get('order_type') == 'desc' ? 'desc' : 'asc');
                $vars['order_by'] = $f;
                if ( get('order_type') == 'desc' ) {
                    $vars['order_by'] = 'desc';
                }
                break;
            }
        }
        foreach ( $field_groups as $k => $f ) {
            if ( is_array($_REQUEST[$f]) ) {
                $s = implode(',', get($f, array(), true));
                $vr = implode(',', $_REQUEST[$f]);
            } else {
                $s = get($f, '');
                $vr = $_REQUEST[$f];
            }
            if ( $s != '' ) {
                $z = app_search_sql_group($k, $s);
                if ( $z )
                    $where[] = $z;
                $vars[$f] = $vr;
            }
        }
        $where = implode(' AND ', $where);
        $total = self::$Model->fetchOne("SELECT COUNT(*) FROM `users` as `a` WHERE $where");
        $posts=self::$Model->fetchAll("
			SELECT `a`.*,`b`.`title` as `group_title`,`c`.`name` as `personnel_name`,`c`.`id` as `personnel_id`,
					`d`.`title` as `department_title`
			FROM `users` as `a`
			LEFT JOIN `groups` as `b`
				ON `b`.`ID`=`a`.`group_id`
			LEFT JOIN `personnels` as `c`
				ON `c`.`ID`=`a`.`personnel_id`
			LEFT JOIN `departments` as `d`
				ON `d`.`ID`=`a`.`department_id`
			WHERE $where $order_by $limit ");

        $a = $vars;
        unset($a['p']);

        if (self::getAction() == 'search') {
                return $a;

        }
        $page = page_ajax('#Admin/User?' . to_query_configs($a, false) . '&p=', $total, $range_page, $current_page);

        self::set(array(
            page => $page,
            total => $total,
            vars => $vars,
            users => $posts
        ));

    }

    public function deletedAction() {
        $vars = array();
        $order_by = " ORDER BY `a`.`is_admin`,`a`.`ID` DESC";

        $current_page = max(getInt('p', 1), 1);
        $range_page = max(getInt('rp', 50), 50);

        if ( isset($_REQUEST['p']) ) {
            $vars['p'] = $current_page;
        }

        if ( isset($_REQUEST['rp']) ) {
            $vars['rp'] = $range_page;
        }

        $limit = "LIMIT " . ($current_page - 1) * $range_page . ",$range_page";
        $field_order = array('username', 'name', 'date_created', 'group_id');
        foreach ( $field_order as $f ) {
            if ( get('order_by') == $f ) {
                $order_by = "ORDER BY $f " . (get('order_type') == 'desc' ? 'desc' : 'asc');
                $vars['order_by'] = $f;
                if ( get('order_type') == 'desc' ) {
                    $vars['order_by'] = 'desc';
                }
                break;
            }
        }

        $total = self::$Model->fetchOne("SELECT COUNT(*) FROM `users` as `a` WHERE `is_deleted`='yes'");
        $posts = self::$Model->fetchAll("
			SELECT `a`.*,`b`.`title` as `group_title`,`c`.`name` as `personnel_name`,`c`.`id` as `personnel_id`,
					`d`.`title` as `department_title`
			FROM `users` as `a`
			LEFT JOIN `groups` as `b`
				ON `b`.`ID`=`a`.`group_id`
			LEFT JOIN `personnels` as `c`
				ON `c`.`ID`=`a`.`personnel_id`
			LEFT JOIN `departments` as `d`
				ON `d`.`ID`=`a`.`department_id`
			WHERE `is_deleted`='yes' $order_by $limit ");

        $a = $vars;
        unset($a['p']);

        $page = page_ajax('#Admin/User/Deleted?' . to_query_configs($a, false) . '&p=', $total, $range_page, $current_page);

        self::set(array(
            page => $page,
            total => $total,
            vars => $vars,
            users => $posts
        ));

    }

    private function checkData( $data ) {
        if ( !is_array($data) )
            return $data;
        if ( preg_match("#[^a-z0-9]#ui", $data['username']) ) {
            return translate('admin.user.username_must_contain_normal_character');
        }
        return $data;

    }

    public function addAction() {
        $personnels = self::$Model->fetchAll("SELECT * FROM `personnels` WHERE `is_draft`='no' ORDER BY `name`");
        $groups = self::$Model->fetchAll("SELECT * FROM `groups` ORDER BY `title`");
        $departments = self::$Model->fetchAll("SELECT * FROM `departments` ORDER BY `parent_id`");

        self::set(array(
            personnels => $personnels,
            groups => $groups,
            departments => $departments
        ));

        if ( isPost() ) {
            loadClass('ZData');
            $f = new ZData();
            $f->addField(self::fields());
            $data = self::checkData($f->getData());

            if ( !is_array($data) ) {
                self::setJSON(array(
                    message => error($data)
                ));
            }

            $data['date_created'] = new Model_Expr('NOW()');

            if ( get('inherit_roles') != 'no' ) {
                $f->removeField('roles');
                $data['roles'] = '';
            }


            if ( self::$Model->fetchRow("SELECT *
				FROM `users`
					WHERE UPPER(`username`)=UPPER('{$data['username']}')
						AND `is_deleted`='no'") ) {
                self::setJSON(array(
                    message => error(translate('admin.user.username_exists'))
                ));
            }

            self::$Model->insert('users', $data);

            self::setJSON(array(
                redirect => '#Admin/User'
            ));
        }

    }

    public function editAction() {
        $user_id = getInt('ID', 0);
        $user = self::$Model->fetchRow("SELECT * FROM `users`
                WHERE `is_deleted`='no' AND `ID`='$user_id'");

        if ( !$user ) {
            self::setJSON(array(
                error(translate('admin.user.user_not_exists'))
            ));
        }

        $personnels = self::$Model->fetchAll("SELECT * FROM `personnels`
                WHERE `is_draft`='no' ORDER BY `name`");
        $groups = self::$Model->fetchAll("SELECT * FROM `groups` ORDER BY `title`");
        $departments = self::$Model->fetchAll("SELECT * FROM `departments` ORDER BY `parent_id`");

        self::set(array(
            post => $user,
            personnels => $personnels,
            groups => $groups,
            departments => $departments
        ));

        if ( isPost() ) {
            loadClass('ZData');
            $f = new ZData();
            $f->addField(self::fields($user));

            if ( !isset($_POST['changepass']) ) {
                $f->removeField('password');
            }

            $data = self::checkData($f->getData());

            if ( !is_array($data) ) {
                self::setJSON(array(
                    message => error($data)
                ));
            }

            if ( get('inherit_roles') != 'no' ) {
                $f->removeField('roles');
                $data['roles'] = '';
            }
            if ( self::$Model->fetchRow("SELECT *
				FROM `users`
					WHERE UPPER(`username`)=UPPER('{$data['username']}')
						AND `is_deleted`='no' AND `ID`<>'$user_id'") ) {
                self::setJSON(array(
                    message => error(translate('admin.user.username_exists'))
                ));
            }

            self::$Model->update('users', $data, "`ID`='$user_id'");
            self::setJSON(array(
                redirect => "#Admin/User"
            ));
        }

    }

    public function deleteAction() {
        $ids = getInt('ID', array(), true);
        //fix for single
        $id = getInt('ID', 0);
        if ( $id ) {
            $ids = array($id);
        }

        if ( count($ids) > 0 ) {
            $cond = implode(',', $ids);
            $imgs = self::$Model->fetchAll("SELECT `photo`
                  FROM `users`
                    WHERE `is_deleted`='no'
                        AND `ID` IN($cond)
                        AND `is_admin`='no'
                        AND `photo` IS NOT NULL");
            foreach ( $imgs as $img ) {
                @unlink('files/photo/' . $img['photo']);
            }
            self::$Model->update('users', array(
                is_deleted => 'yes'
                    ), "`ID` IN($cond) AND `is_admin`='no'");
        }

        self::setJSON(array(
            redirect => '#Admin/User'
        ));

    }

    /**
     * Creat list of users from personnel data
     * 28/06/2012
     */
    public function personnelAction() {
        $content = get('sms_content');
        if ( !isPost() ) {
            self::set('sms_content', getCache("sms", "fast_create_user",""));
            return;
        }

        //$range = 2; //max(1, getInt('range', 0));

        if ( !empty($content) ) {
            setCache("sms", "fast_create_user", $content);
        }

        $posts = self::$Model->fetchAll("SELECT `a`.* FROM `personnels` as `a`
            WHERE `a`.`is_draft`='no' AND `a`.`job_status`<>'STOP_WORKING' AND
                NOT EXISTS( SELECT `b`.`ID` FROM `users` as `b` WHERE `b`.`is_deleted`='no' AND `b`.`personnel_id`=`a`.`ID` ) LIMIT 10");

        foreach ( $posts as $post ) {
            $names = preg_split("/\s+/i", $post['name']);

            if ( count($names) == 0 )
                continue;

            $username = strtolower(create_alias(array_pop($names)));

            foreach ( $names as $a ) {
                $username .= strtolower(mb_substr(create_alias($a), 0, 1));
            }

            //chekc username exist
            $i = 0;
            while ($i >= 0) {
                $u = $username . ($i == 0 ? "" : $i );
                $user = self::$Model->fetchRow("SELECT * FROM `users` WHERE `username`='$u'");
                if ( !$user ) {
                    $username = $u;
                    break;
                }
                $i++;
            }

            $password = get('passwd',rand_string(8));

            self::$Model->insert("users", array(
                created_by_id => get_user_id(),
                // updated_by_id => get_user_id(),
                date_created => new Model_Expr('NOW()'),
                //date_updated => new Model_Expr('NOW()'),
                is_deleted => 'no',
                username => $username,
                password => md5($password),
                personnel_id => $post['ID'],
                personnel_id => $post['ID'],
                email    => $post['email'],
                phone    => $post['mobile'],
                fullname    => $post['name'],
                group_id => getInt('group_id')
            ));

            if ( get('send_sms') && !empty($content) && !empty($post['mobile']) ) {
                $sms = preg_replace("/\{username\}/is", $username, $content);
                $sms = preg_replace("/\{staff_name\}/is", $post['name'], $sms);
                $sms = preg_replace("/\{staff_code\}/is", $post['code'], $sms);
                $sms = preg_replace("/\{password}/is", $password, $sms);
                $sms = preg_replace("/\{url}/is", baseUrl(true), $sms);
                sendSMS(array(
                    address => $post['mobile'],
                    content => $sms
                ));
            }
        }

        $remain = self::$Model->fetchOne("SELECT COUNT(`a`.`ID`)
            FROM `personnels` as `a`
            WHERE `a`.`is_draft`='no' AND `a`.`job_status`<>'STOP_WORKING' AND
                EXISTS(SELECT * FROM `users` as `b` WHERE `b`.`personnel_id`=`a`.`ID` )");

        $total = self::$Model->fetchOne("SELECT COUNT(*) FROM `personnels` WHERE `is_draft`='no' AND `job_status`<>'STOP_WORKING'");

        self::setJSON(array(
            progress => floor($remain * 100 / $total)
        ));

    }

    private function createMenuDepartment( $id = 0, $depth = 0, $items = null ) {

        if ( !$items ) {
            $items = self::$Model->fetchAll("SELECT
					`a`.*,
                    IF(`b`.`ID`,`b`.`ID`,0) as `parentID`
                    FROM `departments` as `a`
                    	LEFT JOIN `departments` as `b`
					ON `a`.`parent_id`=`b`.`ID` ORDER BY `ord`");
        }
        $sub_departments = array();
        foreach ( $items as $k => $a ) {
            if ( $a['parentID'] != $id ) {
                continue;
            }
            // unset($items[$k]);
            $sub_departments[] = $a;
        }

        //if ( $count == 0 && count($sub_departments) == 0 ) {
        //   return '';
        // }
        //class
        $class = $depth == 0 ? "tree-list tree-root" : "tree-list";

        $count = self::$Model->fetchOne("SELECT COUNT(*)
                        FROM `personnels`
                            WHERE `department_id`='$id' AND `is_draft`='no'");

        $list = "<div class='tree-list-outer'>
			<div class=\"$class\">";
        foreach ( $sub_departments as $k => $sub ) {
            $subs = self::createMenuDepartment($sub['ID'], $depth + 1, $items);
            //if ( is_array($subs) ) {
            $count += (int) $subs['count'];
            $count1 = $subs['count'];
            $subs = $subs['html'];
            // }

            $list .= "<div class='tree-folder-outer'>
				<a class='tree-node' onclick='tree_collapse(this)'></a>
				<div class='tree-folder'>
					<a class='tree-title department-icon' href='#Contact/Personnel?department_id={$sub['ID']}'>
					{$sub['title']} <span style='color:#333;font-weight:normal'>[$count1]</span></a>
					$subs
				</div></div>";
        }
        $list .= "</div></div>";

        if ( $depth > 0 )
            return array(
                count => $count,
                html => $list
            );
        return $list;

    }



}