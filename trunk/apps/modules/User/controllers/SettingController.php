<?php
class UserSettingController extends Zone_Action {
	protected function fields($default = NULL) {
		$data = array(
			page => array(
				type => 'ENUM',
				value => array(10, 20, 30, 40, 50, 100),
				label => 'Kích thước trang'
			),
			notice_voice => array(
				type => 'ENUM',
				value => array('yahoo', 'beep', 'ring', 'silent'),
				label => 'Âm thanh thông báo'
			),
			/*shortkey => array(
			 type 		  => 'ENUM',
			 value		  => array('yes','no'),
			 label	  => 'Phím tắt',
			 default_value => 'no'
			 ),*/
			bg_accept => array(
				type => 'ENUM',
				value => array('yes', 'no'),
				label => 'Chấp nhận dùng hình nền',
				default_value => 'no'
			),
			bg_pos => array(
				type => 'ENUM',
				value => array('top left', 'top right', 'top center', 'bottom left', 'bottom right', 'bottom center', 'center center'),
				label => 'Vị trí hình nền',
				default_value => 'no'
			),
			bg_repeat => array(
				type => 'ENUM',
				value => array('no-repeat', 'repeat', 'repeat-x', 'repeat-y'),
				label => 'Lặp hình nền',
				default_value => 'no-repeat'
			)
		);
		return $data;
	}

	public function indexAction() {
		self::removeLayout();
		$user_id = get_user_id();
		loadClass('ZData');

		$settings = self::$Model->fetchOne("SELECT `settings` FROM `users` WHERE `ID`='$user_id'");
		$settings = get_query_configs($settings);
		self::set('settings', $settings);

		if (isPost()) {
			$f = new ZData();
			$f->addField(self::fields());
			$data = $f->getData();

			if (!is_array($data)) {
				self::setJSON(array(
					message => error($data)
				));
			}

			$data['bg_img'] = $settings['bg_img'];

			$t = self::$Model->update('users', array(
				settings => to_query_configs($data)
			), "`ID`='$user_id'");

			self::setJSON(array(
				callback => "(function(){
						window.location.reload();
				})()"
			));
		}
	}

	//upload bg
	public function bgAction() {
		self::removeLayout();
		$user_id = get_user_id();
		$settings = self::$Model->fetchOne("SELECT `settings`
			FROM `users` WHERE `ID`='$user_id'");

		$settings = get_query_configs($settings);

		$fields = array(
			bg_img => array(
				type => 'IMAGE',
				path => 'files/images/',
				max_size => 1024,
				return_name => true
			)
		);

		loadClass('ZData');
		$f = new ZData();
		$f->addField($fields);
		if (isPost()) {
			$data = $f->getData();
			if (!is_array($data)) {
				self::setJSON(array(
					alert => $data
				));
			}

			if ($settings['bg_img']) {
				@unlink("files/images/{$settings['bg_img']}");
			}

			$settings['bg_img'] = $data['bg_img'];

			self::$Model->update('users', array(
				settings => to_query_configs($settings)
			), "`ID`='$user_id'");

			self::setJSON(array(
				close => 'no',
				callback => "(function(){
					var link =baseURL+'/files/images/{$data['bg_img']}';
					$('#userbg')
						.htm('<a href=\"'+link+'\" target=\"_blank\"><img style=\"max-width:200px;max-height:200px\" src=\"'+link+'\"/></a>')
				})()"
			));
		}
	}
}
