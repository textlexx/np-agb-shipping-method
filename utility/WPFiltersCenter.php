<?php

namespace spaceProductsFilters;

class WPFiltersCenter{
	
	//-----------------------------------------------------------------------
	/* Example
	public static function addFilter_off_login_page_for_admin_panel(){
		
		// Return default value if not exists option
		$return_default = 0;
		
		if(get_option('regLogAgb_off_standart_login_adm', $return_default) != 1) return false;
		if(!preg_match('#^/wp-admin#', $_SERVER['REQUEST_URI'])) return false;
		
		add_filter('login_url', RegLogAgbInit::$root_page, 10);
	}
	*/
	//-----------------------------------------------------------------------
}

