<?php
class UserPasswordController extends Zone_Action {
	public function indexAction() {
		self::removeLayout();
		$user_id = get_user_id();
		$oldpass = self::$Model->fetchOne("SELECT `password`
			FROM `users` WHERE `ID`='$user_id'");
		$fields = array(
			password => array(
				type => 'PASSWORD',
				label => 'Mật khẩu',
				no_empty => true,
				old_value => $oldpass,
				min_length => 6,
				max_length => 100
			)
		);

		loadClass('ZData');
		$f = new ZData();
		$f->addField($fields);
		if (isPost()) {
			$data = $f->getData();
			if (!is_array($data)) {
				self::setJSON(array(
					message => error($data)
				));
			}
			self::$Model->update("users", $data, "`ID`='$user_id'");
			self::setJSON(array(
				callback => "(function(){
					location.href='".baseUrl()."/Logout';
				})()"
			));
		}
	}
}
