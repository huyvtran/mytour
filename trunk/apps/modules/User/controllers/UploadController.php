<?php
class UserUploadController extends Zone_Action {
	public function init() {
		$configs = self::$Model->fetchRow("SELECT * FROM `configs` LIMIT 1");

		$user_id = get_user_id();
		$user = self::$Model->fetchRow("SELECT * FROM `users` WHERE `ID`='$user_id'");

		self::set(array(
			configs => $configs,
			user => $user
		));
	}

	public function indexAction() {
	}

	public function listAction() {
		self::removeLayout();
		$name = get('name');
		self::setContent(self::getDepartments(0, 0, '', $name));
	}

	protected function getDepartments($id = 0, $depth = 0, $selected = '', $name = 'users', $attr = '') {
		//Lay phong ban con
		$sub_departments = self::$Model->fetchAll("SELECT `a`.*,IF(`b`.`ID`,`b`.`ID`,0) as `parentID`
				FROM `departments` as `a`
				LEFT JOIN `departments` as `b` ON
				`a`.`parent_id`=`b`.`ID` HAVING `parentID`='$id'");

		$staffs = self::$Model->fetchAll("SELECT *,IF( '$selected' LIKE CONCAT('%,',`ID`,'%') , ' checked', '' ) as `checked`  FROM `users` WHERE `department_id`='$id'");

		if (count($staffs) == 0 && count($sub_departments) == 0) {
			return '';
		}

		//class
		$class = $depth == 0 ? "tree-list tree-root" : "tree-list";

		$list = "<div class='tree-list-outer'>
			<div class=\"$class\">";
		foreach ($sub_departments as $k => $sub) {
			$subs = self::getDepartments($sub['ID'], $depth + 1, $selected, $name, $attr);

			$cl = $subs != "" ? "" : " tree-link-empty";
			$cls = $subs != "" ? "" : " tree-folder-empty";

			//show child in depth
			$has_sub = self::$Model->fetchOne("SELECT `ID` FROM `departments` WHERE `parent_id`='{$sub['ID']}'");
			$is_close = $has_sub ? "" : " tree-section-close";

			$list .= "<div class='tree-folder-outer'>
				<a class='tree-node' onclick='tree_collapse(this)'></a>
				<div class='tree-folder'>
					<a class='tree-title'><input type='checkbox' class='tree-check' onclick='task_check_all(this)'/>{$sub['title']}</a>
					$subs
				</div></div>";
		}

		foreach ($staffs as $k => $staff) {
			$class = $staff['gender'] == 1 ? 'male' : 'female';
			$list .= "<a class='tree-item-outer'>
				<span class='tree-item' title='{$staff['username']}'><span class='tree-item-icon'></span>
				<input type='checkbox' $attr name='{$name}' value='{$staff['ID']}'{$staff['checked']}' x-label='{$staff['username']}' class='tree-check'/><span class='$class'>{$staff['name']}</span></span>
				</a>";
		}

		$list .= "</div></div>";
		return $list;
	}
}
