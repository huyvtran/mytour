<?php
class UserSmsController extends Zone_Action {
	protected function indexAction() {
		$user_id = get_user_id();

		$s = get('s', '');
		if ($s == '')
			die('[]');

		$posts = self::$Model->fetchAll(" SELECT
				`a`.`mobile` as `ID`,
				CONCAT(`a`.`mobile`,' (',`a`.`title`,')') as `title`
			FROM  `contacts` as `a`
			WHERE
				`a`.`is_draft`='no'
				AND `a`.`created_by_id`='$user_id'
				AND `a`.`mobile` IS NOT NULL 				
				AND `a`.`mobile`<>'' 				
				AND (`a`.`mobile` LIKE '$s%' OR `a`.`title` LIKE '%$s%') LIMIT 10");
		die(json_encode($posts));
	}
}
