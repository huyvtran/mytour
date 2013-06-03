<?php
class UserFaqController extends Zone_Action {
	public function indexAction() {
		$posts = self::$Model
		->fetchAll("SELECT * FROM `faqs` WHERE `is_menu`='yes' ORDER BY `ord`");

		self::set(array(
			posts => $posts
		));
		self::removeLayout();
	}

	public function viewAction() {
		$post_id = get('ID');
		$posts = self::$Model
		->fetchAll("SELECT * FROM `faqs`
				WHERE `is_menu`='no'
					AND `parent_id`='$post_id'
				ORDER BY `ord`");
		self::set(array(
			posts => $posts
		));
		self::removeLayout();
	}

	public function searchAction() {
		$s = get('faq_s');
		$posts = self::$Model
		->fetchAll("SELECT * FROM `faqs`
				WHERE `is_menu`='no'
					AND ( `title` LIKE '%$s%' OR `content` LIKE '%$s%' )
				ORDER BY IF(`title` LIKE '%$s%',0,1)");
		self::set(array(
			posts => $posts
		));

		self::removeLayout();
		self::setView('FaqView');
	}
}
?>