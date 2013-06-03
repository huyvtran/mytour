<?php
class AdminFaqController extends Zone_Action {
	public function fields($default = NULL) {
		$data = array(
			title => array(
				type => 'CHAR',
				label => 'Tiêu đề',
				no_empty => true
			),
			is_menu => array(
				type => 'ENUM',
				label => 'Hiện ở menu',
				value => array('yes', 'no'),
				default_value => 'no'
			),
			ord => array(
				type => 'INT',
				label => 'Thứ tự',
				default_value => 1
			),
			parent_id => array(
				type => 'INT',
				label => 'Mục lớn',
			),
			content => array(
				type => 'HTML',
				label => 'Trả lời'
			)
		);
		if (is_array($default)) {
			foreach ($data as $k => $config) {
				if ($config['type'] != 'NONE' && isset($default[$k])) {
					$data[$k]['default_value'] = $default[$k];
				}
			}
		}
		return $data;
	}

	public function indexAction() {
		$posts = self::$Model
		->fetchAll("SELECT `a`.* FROM `faqs` as `a` ORDER BY `ord`");

		self::set('posts', $posts);
	}

	public function addAction() {
		$posts = self::$Model
		->fetchAll("SELECT * FROM `faqs` WHERE `is_menu`='yes' ORDER BY `ord`");
		self::set('posts', $posts);

		if (isPost()) {
			loadClass('ZData');
			$form = new ZData();
			$form->addField(self::fields());
			$data = $form->getData();

			if (!is_array($data)) {
				self::setJSON(array(
					message => error($data)
				));
			}

			self::$Model->insert('faqs', $data);

			self::setJSON(array(
				redirect => '#Admin/Faq'
			));
		}
	}

	public function editAction() {
		$posts = self::$Model
		->fetchAll("SELECT * FROM `faqs` WHERE `is_menu`='yes' ORDER BY `ord`");
		self::set('posts', $posts);

		$post_id = getInt('ID', 0);
		$post = self::$Model->fetchRow("SELECT * FROM `faqs` WHERE `ID`='$post_id'");

		if (!$post) {
			self::setJSON(array(
				content => error('Câu hỏi đã bị xóa hoặc không tôn tại')
			));
		}

		self::set('post', $post);

		if (isPost()) {
			loadClass('ZData');
			$form = new ZData();
			$form->addField(self::fields($post));
			$data = $form->getData();

			$parent_id = get('parent_id');
			//if( self::$Model->fetchRow("SELECT * FROM `faqs`
			//	WHERE `ID`='$parent_id' AND `ID`<>'$post_id'") ){
			//	self::setJSON(array(
			//		message => error('Phòng ban trực thuộc không hợp lệ')
			//	));
			//}

			if (!is_array($data)) {
				self::setJSON(array(
					message => error($data)
				));
			}

			self::$Model->update('faqs', $data, "`ID`='$post_id'");
			self::setJSON(array(
				redirect => '#Admin/Faq/'
			));
		}
	}

	public function deleteAction() {
		$post_id = getInt('ID', 0);
		$post = self::$Model->fetchRow("SELECT * FROM `faqs` WHERE `ID`='$post_id'");
		if (!$post) {
			self::setJSON(array(
				alert => 'Câu hỏi đã bị xóa hoặc không tồn tại'
			));
		}

		self::$Model->update('faqs', array(
			parent_id => $post['parent_id']
		), "`parent_id`='$post_id'");

		self::$Model->delete('faqs', " `ID`='$post_id' ");
		self::setJSON(array(
			redirect => '#Admin/Faq'
		));
	}
}
?>