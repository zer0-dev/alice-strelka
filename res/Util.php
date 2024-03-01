<?php
class Util{
	public static function mb_ucfirst($string, $encoding) {
		$firstChar = mb_substr($string, 0, 1, $encoding);
		$then = mb_substr($string, 1, null, $encoding);
		return mb_strtoupper($firstChar, $encoding) . $then;
	}
	
	public function declension($balance){
		$rub = $balance[0];
		$kop = $balance[1];
		if(($rub % 10 == 1) && ($rub % 100 != 11)){
			$rubTxt = 'рубль';
		} elseif((($rub % 10 == 2) && ($rub % 100 != 12)) || (($rub % 10 == 3) && ($rub % 100 != 13)) || (($rub % 10 == 4) && ($rub % 100 != 14))){
			$rubTxt = 'рубля';
		} else{
			$rubTxt = 'рублей';
		}
		if(($kop % 10 == 1) && ($kop % 100 != 11)){
			$koptxt = 'копейка';
		} elseif((($kop % 10 == 2) && ($kop % 100 != 12)) || (($kop % 10 == 3) && ($kop % 100 != 13)) || (($kop % 10 == 4) && ($kop % 100 != 14))){
			$koptxt = 'копейки';
		} else{
			$koptxt = 'копеек';
		}
		return $rub.' '.$rubTxt.' '.$kop.' '.$koptxt;
	}
}
?>