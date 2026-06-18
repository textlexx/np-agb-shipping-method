<?php

namespace NpAgbShippingMethod;

class ControlCenter{
	
	//-----------------------------------------------------------------------

	public static function killWpMagicQuotes(mixed &$val):bool{
		
		if($val){

			$val = preg_replace('#[\\\\]+#', '\\', $val);
			$val = preg_replace('#\\\\\'#', '\'', $val);
			$val = preg_replace('#\\\\"#', '"', $val);

			return $val;
		}

		return '';
	}
	
	//-----------------------------------------------------------------------
	
	// True check on empty value
	public static function empty(mixed &$val):bool{
		
		if(preg_match('#^[\t\r\n ]*$#', $val)){

			return true;
		}

		return false;
	}
	
	//-----------------------------------------------------------------------

	public static function login(mixed &$val):bool{
		
		if(preg_match('#^[0-9a-zA-Z_\.\-\*]{1,50}$#', $val)){

			return true;
		}

		return false;
	}
	
	//-----------------------------------------------------------------------

	public static function email(mixed &$val):bool{
		
		if(preg_match('#^[0-9a-zA-Z\.\-\*_]{1,50}@[0-9a-zA-Z\.\-_]{1,100}\.[a-zA-Z]{2,5}$#', $val)){

			return true;
		}

		return false;
	}
	
	//-----------------------------------------------------------------------

	public static function password(mixed &$val):bool{
		
		if(preg_match('#^[^ \t\r\nЙйЦцУуКкЕеНнГгШшЩщЗзХхЇїФфІіВвАаПпРрОоЛлДдЖжЄєЭэЯяЧчСсМмИиТтЬьБбЮюЁёЫыЪъ]{1,50}$#', $val)){

			return true;
		}

		return false;
	}
	
	//-----------------------------------------------------------------------

	public static function jsonEncode(mixed &$val):string{

		return json_encode($val, JSON_HEX_TAG && JSON_HEX_AMP && JSON_HEX_APOS && JSON_HEX_QUOT);
	}

	//-----------------------------------------------------------------------

	public static function is_opt_amount_symbols_right(mixed &$val, $maxLength = 100){

		if(mb_strlen($val) <= $maxLength) return true;

		return false;
	}

	//-----------------------------------------------------------------------

	public static function isOnlyEngSymbs(mixed &$val){
		
		if(preg_match('#^[0-9a-zA-Z_]+$#', $val)){

			return true;
		}

		return false;
	}
	
	//-----------------------------------------------------------------------
}