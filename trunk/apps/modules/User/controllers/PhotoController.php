<?php
class UserPhotoController extends Zone_Action {
	public function indexAction() {
		self::removeLayout();
		$user_id = get_user_id();
		$user = self::$Model->fetchRow("SELECT `photo` FROM `users` WHERE `ID`='$user_id'");
		self::set('photo', $user['photo']);

		$fields = array(
			photo => array(
				type => 'IMAGE',
				path => 'files/photo/',
				resize => array(95, 95),
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

			self::$Model->update("users", $data, "`ID`='$user_id'");
			if ($user['photo']) {
				@unlink("files/photo/{$user['photo']}");
			}
			self::setJSON(array(
				callback => "(function(){
					$('#uphoto')
						.each(function(){
							this.src = baseURL+'/files/photo/{$data['photo']}?'+(new Date()).getTime();
						})
				})()"
			));
		} else {
			die('200');
		}
	}

	/* public function thumbAction(){
	 self::removeLayout();
	
	 $user_id = get('user_id',0);
	
	 $w = getInt("w",30);
	 $h = getInt("h",30);
	
	 $user = self::$Model->fetchRow("SELECT `photo` FROM `users` WHERE `ID`='$user_id'");
	
	 $file_path = "files/photo/{$user['photo']}";
	
	 if( !$user || !file_exists( $file_path ) ){
	 $file_path = "files/photo/noavatar.gif";
	 }
	
	 loadClass('PHPThumb');
	 $thumb = PhpThumbFactory::create($file_path);
	 $thumb->resize($w,$h);
	 $thumb->show();
	 }	 */

	public function thumbAction() {
		self::removeLayout();

		$w = getInt("w", 30);
		$h = getInt("h", 30);
		$file_path = get("file");

		loadClass('PHPThumb');
		$thumb = PhpThumbFactory::create($file_path);
		$thumb->resize($w, $h);
		$thumb->show();
	}
}
