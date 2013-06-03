<?php
class AdminNewsController extends Zone_Action {
	public function fields() {
		$data = array(
			title => array(
				type => 'CHAR',
				label => 'Tiêu đề',
				no_empty => true
			),
			ord => array(
				type => 'INT',
				default_value => new Model_Expr("LAST_INSERT_ID()+1")
			),
			date_updated => array(
				type => 'NONE',
				default_value => new Model_Expr("NOW()")
			),
			updated_by_id => array(
				type => 'NONE',
				default_value => get_user_id()
			)
		);
		return $data;
	}

	public function indexAction() {
		$posts = self::$Model->fetchAll("SELECT `a`.*,
				`b`.`username` as `created_by_name`,
				`b`.`is_deleted` as `created_is_deleted`,
				`c`.`username` as `updated_by_name`,
				`c`.`is_deleted` as `updated_is_deleted`
			FROM `news_cats` as `a`
			LEFT JOIN `users` as `b`
				ON `a`.`created_by_id`=`b`.`ID`
			LEFT JOIN `users` as `c`
				ON `a`.`updated_by_id`=`c`.`ID`
			ORDER BY `a`.`ord`");
		self::set('posts', $posts);
	}

	public function addAction() {
		self::removeLayout();
		if (isPost()) {
			loadClass('ZData');
			$form = new ZData();
			$form->addField(self::fields());
			$form->addField(array(
				date_created => array(
					type => 'NONE',
					default_value => mysql_date()
				),
				created_by_id => array(
					type => 'NONE',
					default_value => get_user_id()
				)
			));

			$data = $form->getData();
			if (!is_array($data)) {
				self::setJSON(array(
					message => error($data)
				));
			}

			self::$Model->insert("news_cats", $data);

			self::setJSON(array(
				redirect => "#Admin/News"
			));
		}
	}

	public function editAction() {
		self::removeLayout();
		$post_id = getInt('ID', 0);
		$post = self::$Model->fetchRow("SELECT * FROM `news_cats` WHERE `ID`='$post_id'");

		if (!$post) {
			self::setJSON(array(
				content => error('Dữ liệu đã bị xóa hoặc không tồn tại.')
			));
		}

		self::set('post', $post);
		if (isPost()) {
			loadClass('ZData');
			$form = new ZData();
			$form->addField(self::fields($post));
			$data = $form->getData();

			if (!is_array($data)) {
				self::setJSON(array(
					message => error($data)
				));
			}

			self::$Model->update("news_cats", $data, "`ID`='$post_id'");
			self::setJSON(array(
				redirect => "#Admin/News"
			));
		}
	}

	public function deleteAction() {
		$post_id = getInt("ID", 0);

		self::$Model->delete("news_cats", " `ID`='$post_id' ");
		self::setJSON(array(
			redirect => "#Admin/News"
		));
	}
}
?>