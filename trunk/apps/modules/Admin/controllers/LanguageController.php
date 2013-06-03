<?php
class AdminLanguageController extends Zone_Action {
	private function getLanguages() {
		$langs = array();
		$path = ROOT.'/apps/languages';
		$d = opendir($path);
		while ($f = readdir($d)) {
			if ($f == "." || $f == "..")
				continue;
			if (is_file("$path/$f"))
				continue;

			$langs[] = $f;
		}
		closedir($d);
		return $langs;
	}

	private function getModules($lang) {
		$langs = array();
		$path = ROOT."/apps/languages/$lang";
		$d = opendir($path);
		while ($f = readdir($d)) {
			if ($f == "." || $f == "..")
				continue;
			if (!is_file("$path/$f"))
				continue;

			$langs[] = $f;
		}
		closedir($d);
		return $langs;
	}

	private function getTerms($lang, $module) {
		$terms = (array) (@include ROOT."/apps/languages/$lang/$module.php" );
		ksort($terms);
		return $terms;
	}

	public function indexAction() {
		if (get('hl', null) && get('m', null)) {
			if (isPost()) {

				$hl = get('hl', null);
				$m = get('m', null);
				$file = ROOT."/apps/languages/$hl/{$m}.php";
				if (!file_exists($file)) {
					self::setJSON(array(
						alert => 'File ngôn ngữ không tồn tại'
					));
				}

				$terms = self::getTerms($hl, $m);
				$lang_terms = get('lang_term', array(),1);
				$lang_values = get('lang_value', array(),1);
				$lang_deletes = get('lang_delete', array(),1);

				foreach ($lang_terms as $k1 => $v1) {
					$terms[$v1] = $lang_values[$k1];
				}

                foreach ($lang_deletes as $v1) {
					unset($terms[$v1]);
				}

				arsort($terms);

				file_put_contents($file, "<?php\n return ".var_export($terms, true).";");

				self::setJSON(array(
					redirect => "#Admin/Language?hl=$hl&m=$m"
				));
			}

			self::set(array(
				terms => self::getTerms(get('hl'), get('m'))
			));
		}

		if (get('hl', null)) {
			self::set(array(
				modules => self::getModules(get('hl'))
			));
		}

		self::set(array(
			langs => self::getLanguages()
		));
	}
}
?>