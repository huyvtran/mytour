<?php
class StickyIndexController extends Zone_Action {
	public function indexAction() {
		$user_id = get_user_id();
		$posts = self::$Model->fetchAll("SELECT *,CAST(`date` as DATE) as `date` FROM `stickies` WHERE `user_id`='$user_id'");
		return self::setContent(json_encode($posts));
	}

	public function addAction() {
		if (isPost()) {
			$data = array(
				user_id => get_user_id()
			);
			self::$Model
			->insert("stickies", $data);
		}
		self::setContent(self::$Model->lastId());
		self::removeLayout();
	}

	public function editAction() {
		if (isPost()) {
			$data = array(
				content => get('content'),
				date => date('Y-m-d H:i:s'),
				width => get('mW'),
				height => get('mH'),
				x => get('dx'),
				y => get('dy'),
				user_id => get_user_id()
			);
			self::$Model
			->update("stickies", $data, "`ID`='".get('ID', 0)."' AND `user_id`='".get_user_id()."'");
		}
		self::setContent('');
		self::removeLayout();
	}

	public function deleteAction() {
		self::$Model->delete("stickies", "`ID`='".get('ID', 0)."' AND `user_id`='".get_user_id()."'");
	}
}
