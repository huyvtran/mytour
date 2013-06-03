<?php
class AdminSmsController extends Zone_Action {
	public function indexAction() {

		$user_id = get_user_id();
		$vars = array();
		$order_by = " ORDER BY `ID` DESC";

		$current_page = max(getInt('p', 1), 1);
		$vars['p'] = $current_page;
		$range_page = max(10, (int) self::getConfig('user.page', 20));
		$limit = "LIMIT ".($current_page - 1) * $range_page.",$range_page";

		$field_orders = array(
			'`a`.`ID`' => 'ID'
		);

		$field_groups = array(
			'`a`.`mode`' => 'mode'
		);

		$field_texts = array();

		foreach ($field_orders as $f => $r) {
			if (get('order_by') == $r) {
				$order_type = (get('order_type') == 'desc' ? 'desc' : 'asc');
				$order_by = "ORDER BY $f $order_type";
				$vars['order_by'] = $r;
				$vars['order_type'] = $order_type;
				break;
			}
		}

		$where = array();

		$word = get('s', '');
		if ($word != '') {
			$a = array();
			foreach ($field_texts as $k => $f) {
				$a[] = app_search_sql_text($k, $word);
			}
			$vars['s'] = $_REQUEST['s'];
			$where[] = '('.implode(' OR ', $a).')';
		}

		foreach ($field_texts as $k => $f) {
			$word = get($f, '');
			if ($word == '')
				continue;
			$where[] = app_search_sql_text($k, $word);
			$vars[$f] = $_REQUEST[$f];
		}

		foreach ($field_groups as $k => $f) {
			if (is_array($_REQUEST[$f])) {
				$s = implode(',', get($f, array(), true));
				$vr = implode(',', $_REQUEST[$f]);
			} else {
				$s = get($f, '');
				$vr = $_REQUEST[$f];
			}
			if ($s != '') {
				$where[] = app_search_sql_group($k, $s);
				$vars[$f] = $vr;
			}
		}

		$where = implode(' AND ', $where);
		if ($where != '')
			$where = ' WHERE '.$where;

		$a = $vars;
		unset($a['p']);
		if (self::getAction() == 'search') {
			return $a;
		}

		$total = self::$Model->fetchOne("SELECT COUNT(*) FROM `sms` as `a` $where");
		$posts = self::$Model->fetchAll("SELECT
				`a`.*,
				`b`.`username` as `username`,
				`b`.`is_deleted` as `is_deleted`	
			FROM `sms` as `a`
			LEFT JOIN `users` as `b`
				ON `a`.`user_id`=`b`.`ID`
				$where $order_by $limit");

		$page = page_ajax('#Admin/Sms?'.to_query_configs($a, false).'&p=', $total, $range_page, $current_page);

		self::set(array(
			page => $page,
			total => $total,
			vars => $vars,
			posts => $posts
		));
	}
}
?>