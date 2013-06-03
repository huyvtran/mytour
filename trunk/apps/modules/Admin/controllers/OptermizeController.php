<?php
class AdminOptermizeController extends Zone_Action {
	public function indexAction() {
	}

	/*Remove trash from user deleted*/
	public function userAction() {
		self::removeLayout();
		$users = self::$Model->fetchAll("SELECT * FROM `users` WHERE `is_deleted`='yes'");
		foreach ($users as $a) {
			self::$Model->delete('notices', "`user_id`='{$a['ID']}'");
			self::$Model->delete('calendars', "`created_by_id`='{$a['ID']}' AND `type`='user'");
			if ($a['photo']) {
				@unlink("files/photo/{$a['photo']}");
			}
		}
	}

	/*Remove old notice*/
	public function noticeAction() {
		self::removeLayout();
		self::$Model->delete('notices', "CURDATE() > DATE_ADD(DATE(`date`),INTERVAL 1 MONTH)");
	}

	/*Optermize table*/
	public function optermizeAction() {
		self::removeLayout();
		$tables = self::$Model->exec('SHOW TABLES');
		$tables = mysql_fetch_assoc($tables);

		foreach ($tables as $tb) {
			self::$Model->exec("OPTIMIZE TABLE '$tb'");
		}
	}

}
?>