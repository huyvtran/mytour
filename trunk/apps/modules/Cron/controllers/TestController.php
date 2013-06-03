<?php

class CronTestController extends Zone_Action {
	public function indexAction() {
		$client = new SoapClient("http://www.mymobile.com.vn/SMSAPIWS/SMSAgentWS.asmx?op=SendMT");
		$strWeather = $client->GetWeather(array('City' => 'Murfreesboro'))->GetWeatherResult;
		echo $strWeather."\nDone";

	}
}
