<?php class CronSmsController extends Zone_Action {
	public function indexAction() {
		@set_time_limit(0);
		self::removeLayout();

		loadClass('ZSms');

		$configs = self::$Model->fetchRow('configs', "SELECT * FROM `configs` LIMIT 1");

		$msgs = self::$Model->fetchAll("SELECT * FROM `sms`");

		$sms = new ZSms($configs['sms_url'], $configs, 'sms_');
		$ck = $sms->sendData(array(
			dest => "841662917081", //$msg['address'],
			msgbody => "Chao ban toi la eoffice", $msg['content'],
			msgtype => "Text",
			reqtime => str_replace(' ', '', date('Y m d H i s', time())) //$msg['date_send']
		));

		die(var_dump($ck));

		foreach ($msgs as $msg) {
			$sms = new ZSms($configs['sms_url'], $configs, 'sms_');
			$ck = $sms->sendData(array(
				dest => $msg['address'],
				msgbody => $msg['content'],
				msgtype => "Text",
				reqtime => str_replace(' ', '', date('Y m d H i s', time())) //$msg['date_send']
			));

			$result = $ck->SendMTResult;
			self::$Model->update('sms', array(
				status => $result == 202 ? 1 : 2,
				result_send => $result
			), "`ID`='{$msg['ID']}'");
		}
		self::setContent('FINISH');
	}
}
