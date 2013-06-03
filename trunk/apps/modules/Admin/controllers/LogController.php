<?php

class AdminLogController extends Zone_Action {

    public function indexAction() {

        $user_id = get_user_id();
        $vars = array();
        $order_by = " ORDER BY `date` DESC";

        $current_page = max(getInt('p', 1), 1);
        $vars['p'] = $current_page;
        $range_page = max(10, (int) self::getConfig('user.page', 20));
        $limit = "LIMIT " . ($current_page - 1) * $range_page . ",$range_page";

        $field_orders = array(
            '`a`.`user_id`' => 'user_id',
            '`a`.`username`' => 'username',
            '`a`.`browser`' => 'browser',
            '`a`.`ip`' => 'ip'
        );

        $field_groups = array(
            '`a`.`user_id`' => 'user_id',
            '`a`.`username`' => 'username',
            '`a`.`date`' => 'date'
        );

        $field_texts = array();

        foreach ( $field_orders as $f => $r ) {
            if ( get('order_by') == $r ) {
                $order_type = (get('order_type') == 'desc' ? 'desc' : 'asc');
                $order_by = "ORDER BY $f $order_type";
                $vars['order_by'] = $r;
                $vars['order_type'] = $order_type;
                break;
            }
        }

        $where = array("`a`.`user_id`<>'0'");

        $word = get('s', '');
        if ( $word != '' ) {
            $a = array();
            foreach ( $field_texts as $k => $f ) {
                $a[] = app_search_sql_text($k, $word);
            }
            $vars['s'] = $_REQUEST['s'];
            $where[] = '(' . implode(' OR ', $a) . ')';
        }

        foreach ( $field_texts as $k => $f ) {
            $word = get($f, '');
            if ( $word == '' )
                continue;
            $where[] = app_search_sql_text($k, $word);
            $vars[$f] = $_REQUEST[$f];
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
                $where[] = app_search_sql_group($k, $s);
                $vars[$f] = $vr;
            }
        }

        $where = implode(' AND ', $where);
        if ( $where != '' )
            $where = ' WHERE ' . $where;

        $a = $vars;
        unset($a['p']);
        if ( self::getAction() == 'search' ) {
            return $a;
        }

        $total = self::$Model->fetchOne("SELECT COUNT(*) FROM `logs` as `a` $where");
        $posts = self::$Model->fetchAll("SELECT `a`.*
			FROM `logs` as `a` $where $order_by $limit");

        $page = page_ajax('#Admin/Log?' . to_query_configs($a, false) . '&p=', $total, $range_page, $current_page);

        self::set(array(
            page => $page,
            total => $total,
            vars => $vars,
            posts => $posts
        ));

    }

    public function searchAction() {
        if ( !isPost() ) {
            self::getOptions();
        } else {
            $vars = self::indexAction();
            if ( count($vars) == 0 ) {
                self::setJSON(array(
                    alert => 'Chưa có thông số tìm kiếm'
                ));
            } else {
                self::setJSON(array(
                    redirect => '#Admin/Log?' . to_query_configs($vars, false)
                ));
            }
        }

    }

        public function deleteAction() {
				
		self::$Model->delete('logs', " `date`<='DATE_SUB(NOW(),30)' ");
	
		self::setJSON(array(
			redirect => '#'.get('url', 'Admin/Log')
		));
	}
}

?>