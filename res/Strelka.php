<?php
class Strelka{
	public static function checkCard($card){
		$ch = curl_init('https://strelkacard.ru/api/cards/status/?cardnum='.$card.'&cardtypeid=3ae427a1-0f17-4524-acb1-a3f50090a8f3');
		curl_setopt($ch,CURLOPT_HTTPHEADER,['Referer: https://strelkacard.ru/']);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
		$check = json_decode(curl_exec($ch), true);
	if($check['cardblocked'] || !empty($check['__all__']) || $check['rightconfirmrequired']) return false;
		return true;
	}
	
	public static function getBalance($card){
		$ch = curl_init('https://strelkacard.ru/api/cards/status/?cardnum='.$card->getNumber().'&cardtypeid=3ae427a1-0f17-4524-acb1-a3f50090a8f3');
		curl_setopt($ch,CURLOPT_HTTPHEADER,['Referer: https://strelkacard.ru/']);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
		$check = json_decode(curl_exec($ch), true);
		if($check['cardblocked'] || !empty($check['__all__']) || $check['rightconfirmrequired']) return false;
		return [floor($check['balance']/100), $check['balance']%100];
	}
}
?>