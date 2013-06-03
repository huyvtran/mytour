<?php

class UserSelectController extends Zone_Action {

    public function init() {
        $configs = self::$Model->fetchRow("SELECT * FROM `configs` LIMIT 1");
        $user_id = get_user_id();
        $user = self::$Model->fetchRow("SELECT `a`.*,`b`.`name`
                FROM `users` as `a`
                LEFT JOIN `personnels` as `b`
                 ON `a`.`personnel_id`=`b`.`ID`
                WHERE `a`.`ID`='$user_id'");

        self::set(array(
            configs => $configs,
            user => $user
        ));

    }

    public function multicheckAction() {
        self::removeLayout();
        $selected = array();
        foreach ( explode(get('str_user', ' '), ',') as $id ) {
            if ( (int) $id != 0 ) {
                $selected[] = (int) $id;
            }
        }
        $selected = implode(',', $selected);

        $name = get('name','users');
        $is_checkbox = get('is_checkbox', 1);
        $cache_prefix = "departments";
        $cache_file = "tree_select";

        $tree = getCache($cache_prefix, $cache_file);
        if ( !$tree ) {
            $tree = $this->getTreeCheckbox(0, 0);
            setCache($cache_prefix, $cache_file, $tree);
        }

        $script = '(function(elem){ $(elem.parentNode.parentNode).find("input[type=checkbox]").set("checked", elem.checked ).each(function(){if( !this.disabled ){ if(this.name == elem.name ) this.checked = elem.checked   }}) })';
        $check_select = "<input type='checkbox' class='tree-check' onclick='$script(this)'/>";

        $tree = str_ireplace('{INPUT_NAME}', $name, $tree);
        $tree = str_ireplace('{INPUT_TYPE}', $is_checkbox == 1 ? 'checkbox' : 'radio', $tree);
        $tree = str_ireplace('{CHECK_SELECT}', $is_checkbox == 1 ? $check_select : '', $tree);

        self::setJSON(array(
            content => $tree
        ));

    }

    protected function getTreeCheckbox( $id = 0, $depth = 0 ) {
        $sub_departments = self::$Model->fetchAll("SELECT
					`a`.*,
					IF(`b`.`ID` IS NOT NULL,`b`.`ID`,0) as `parentID`
				FROM `departments` as `a`
				LEFT JOIN `departments` as `b`
					ON `a`.`parent_id`=`b`.`ID`
				WHERE IF(`b`.`ID` IS NOT NULL,`b`.`ID`,0)='$id' ORDER BY `ord`");

        $staffs = self::$Model->fetchAll("SELECT
			`a`.*,
			`b`.`name` as `name`
			FROM `users` as `a`
			LEFT JOIN `personnels` as `b`
                ON `a`.`personnel_id`=`b`.`ID`
			WHERE
				`a`.`department_id`='$id'
				AND `a`.`is_deleted`='no'");

        if ( count($staffs) == 0 && count($sub_departments) == 0 ) {
            return '';
        }

        //class
        $class = $depth == 0 ? 'tree-list tree-root' : 'tree-list';
        $type = '{INPUT_TYPE}';
        $name = '{INPUT_NAME}';

        $list = $depth > 1 ? "<div class='tree-list-outer' style='display:none'><div class='$class'>"
            : "<div class='tree-list-outer'><div class='$class'>";

        foreach ( $sub_departments as $k => $sub ) {
            $subs = self::getTreeCheckbox($sub['ID'], $depth + 1);
            if ( $depth < 1 ) {
                $list .= "<div class='tree-folder-outer'>
                    <a class='tree-node' onclick='tree_collapse(this)'></a>
                    <div class='tree-folder'>
                        <a class='tree-title'>{CHECK_SELECT}
                        {$sub['title']}</a>$subs
                    </div></div>";
            } else {
                $list .= "<div class='tree-folder-outer'>
                    <a class='tree-node tree-open' onclick='tree_collapse(this)'></a>
                    <div class='tree-folder'>
                        <a class='tree-title'>{CHECK_SELECT}{$sub['title']}</a>
                        $subs
                    </div></div>";
            }
        }

        foreach ( $staffs as $k => $staff ) {
            $class = '';
            $list .= "<a class='tree-item-outer'>
				<span class='tree-item' title='{$staff['fullname']}'><span class='tree-item-icon'></span>
				<input type='$type' name='{$name}' value='{$staff['ID']}'{$staff['checked']}' x-label='{$staff['fullname']}' x-title='{$staff['fullname']}' class='tree-check'/>
				<span class='chaticon-{$staff['ID']}'><span class='offline'></span></span>
				<span class='$class'>{$staff['fullname']}</span></span>
				</a>";
        }

        $list .= "</div></div>";
        return $list;

    }

    public function suggestAction() {
        self::removeLayout();
        $s = get('s', '');
        if ( $s == '' ) {
            self::setJSON(array(content => array()));
        }
        self::setJSON(array(
            content => self::$Model->fetchAll("SELECT
					`a`.`username`,
					`a`.`ID`,
                    `a`.`fullname`,
					`b`.`name`,
					IFNULL(`c`.`title`,'') as `department_title`
				FROM `users` as `a`
                 LEFT JOIN `personnels` as `b`
                 ON `a`.`personnel_id`=`b`.`ID`
				LEFT JOIN `departments` as `c`
					ON `a`.`department_id`=`c`.`ID`
				WHERE
					`a`.`is_deleted`='no'
					AND `a`.`fullname` LIKE '$s%' LIMIT 10")
        ));

    }

}
