<?php
class UserConfigController extends Zone_Action {
	protected function fields($default = NULL) {
		$data = array(
			title => array(
				type => 'CHAR',
				label => 'Status'
			),
			address => array(
				type => 'CHAR',
				label => 'Địa chỉ'
			),
			birthday => array(
				type => 'DATE',
				fix_value => change_date_format,
				default_value => NULL,
				label => 'Sinh nhật'
			),
			profiles => array(
				type => 'CHAR',
				label => 'Giới thiệu'
			),
			university => array(
				type => 'CHAR',
				label => 'university'
			),
			fav => array(
				type => 'CHAR',
				label => 'Sở thích'
			),
			sport => array(
				type => 'CHAR',
				label => 'sport'
			),
			music => array(
				type => 'CHAR',
				label => 'Âm nhạc'
			),
			film => array(
				type => 'CHAR',
				label => 'Phim ảnh'
			),
			city => array(
				type => 'CHAR',
				label => 'Thành phố/thị xã'
			),
			web => array(
				type => 'CHAR',
				label => 'Trang web'
			),
			email => array(
				type => 'CHAR',
				label => 'Email'
			),
			yahoo => array(
				type => 'CHAR',
				label => 'Yahoo'
			),
			phone => array(
				type => 'CHAR',
				label => 'Điện thoại',
				min_length => 6,
				max_length => 20
			),
            yahoo => array(
				type => 'CHAR',
				label => 'Yahoo'
			),
            row_id => array(
                type => 'INT',
                no_empty => true,
                label => translate('personnel.lunch.field.row')
            ),
            level_id => array(
                type => 'INT',
                no_empty => true,
                label => translate('personnel.lunch.field.level')
            ),
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
		self::removeLayout();
		$user_id = get_user_id();
		loadClass('ZData');

		$user = self::$Model->fetchRow("SELECT * FROM `users` WHERE `ID`='$user_id'");
		self::set("user", $user);

		if (isPost()) {
			$f = new ZData();
			$f->addField(self::fields());
			$data = $f->getData();

			if (!is_array($data)) {
				self::setJSON(array(
					alert => error($data)
				));
			}

			$t = self::$Model->update('users', $data, "`ID`='$user_id'");

                        self::$Model->update('personnel_lunchs',array(
                            level_id=>$data['level_id'],
                            row_id=>$data['row_id'],
                        ),"`created_by_id`=$user_id");
			if ($t) {
				self::$Model->insert('notices', array(
					user_id => 0,
					date => new Model_Expr('NOW()'),
					created_by_id => $user['ID'],
					title => 'Thông báo từ hệ thống',
					content => get_user_link($user).' vừa cập nhập thông tin cá nhân',
					url => '#User/Info?ID='.$user['ID']
				));
			}
			self::setJSON(array(
				close => true
			));
		}
	}
}
