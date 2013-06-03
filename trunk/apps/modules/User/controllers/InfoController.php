<?php
class UserInfoController extends Zone_Action {
	public function indexAction() {
		self::removeLayout();
		$user_id = get('ID', 0);

		$user = self::$Model->fetchRow("SELECT * FROM `users` WHERE `ID`='$user_id' AND `is_deleted`='no'");

		if (!$user) {
			self::setContent(error("Thành viên không tồn tại hoặc đã bị xóa"));
		}
		self::set('user', $user);

		$tasks = self::$Model->fetchAll("SELECT `a`.*
			FROM  `tasks` as `a`
			LEFT JOIN `tasks_users` as `c`
				ON `c`.`user_id`='$user_id'
				AND `c`.`task_id`=`a`.`ID`
			WHERE
				`a`.`is_draft`='no'
				AND `a`.`status`<>'complete'
				AND `c`.`is_implement`='yes'
			ORDER BY `a`.`date_start` DESC LIMIT 0,10");

		self::set('tasks', $tasks);
	}
}
