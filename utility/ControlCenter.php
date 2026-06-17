<?php

namespace NpAgbShippingMethod;

class ControlCenter{
	
	//-----------------------------------------------------------------------

	public static function killWpMagicQuotes(&$val){
		
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
	public static function empty(&$val){
		
		if(preg_match('#^[\t\r\n ]*$#', $val)){

			return true;
		}

		return false;
	}
	
	//-----------------------------------------------------------------------

	public static function login(&$val){
		
		if(preg_match('#^[0-9a-zA-Z_\.\-\*]{1,50}$#', $val)){

			return true;
		}

		return false;
	}
	
	//-----------------------------------------------------------------------

	public static function email(&$val){
		
		if(preg_match('#^[0-9a-zA-Z\.\-\*_]{1,50}@[0-9a-zA-Z\.\-_]{1,100}\.[a-zA-Z]{2,5}$#', $val)){

			return true;
		}

		return false;
	}
	
	//-----------------------------------------------------------------------

	public static function password(&$val){
		
		if(preg_match('#^[^ \t\r\nЙйЦцУуКкЕеНнГгШшЩщЗзХхЇїФфІіВвАаПпРрОоЛлДдЖжЄєЭэЯяЧчСсМмИиТтЬьБбЮюЁёЫыЪъ]{1,50}$#', $val)){

			return true;
		}

		return false;
	}
	
	//-----------------------------------------------------------------------

	public static function jsonEncode($value){

		return json_encode($value, JSON_HEX_TAG && JSON_HEX_AMP && JSON_HEX_APOS && JSON_HEX_QUOT);
	}

	//-----------------------------------------------------------------------

	public static function is_opt_amount_symbols_right($value, $maxLength = 100){

		if(mb_strlen($value) <= $maxLength) return true;

		return false;
	}

	//-----------------------------------------------------------------------

	public static function isOnlyEngSymbs(&$val){
		
		if(preg_match('#^[0-9a-zA-Z_]+$#', $val)){

			return true;
		}

		return false;
	}
	
	//-----------------------------------------------------------------------
}