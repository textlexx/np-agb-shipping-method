<?php

namespace NpAgbShippingMethod;
//-----------------------------------------------------------------
//-----------------------------------------------------------------

/*
Plugin Name: Nova Poshta AGB Shipping Method
Plugin URI: https://33da.top
Description: Nova Poshta AGB Shipping Method
Version: 1.0
Author: Aleksandr Borisovich Gaidash
Author URI: https://33da.top
*/

//-----------------------------------------------------------------
//-----------------------------------------------------------------

define('PLUGIN_DIR_NAME_NP_S_MT', 'np-agb-shipping-method');
define('PLUGIN_URL_DIR_NP_S_MT', plugins_url());
define('URL_DIR_CURRENT_PLG_NP_S_MT', PLUGIN_URL_DIR_NP_S_MT.'/'.PLUGIN_DIR_NAME_NP_S_MT);
define('PATH_CURRENT_PLG_NP_S_MT', $_SERVER['DOCUMENT_ROOT'].'/wp-content/plugins/'.PLUGIN_DIR_NAME_NP_S_MT);

// Included common classes
require_once ('requireClasses.php');

//-----------------------------------------------------------------
//-----------------------------------------------------------------

class NPShippingMethod {

    // Path relatively this plugin dir
	public static $base_page = 'pages/pages_selector.php';
	// Default function add plugin in menu
	public static $default_menu = array(__CLASS__, 'plugin_in_menu');
	// Default function including plugin page
	public static $default_page = array(__CLASS__, 'show_page');

    // After plugin activated started show it in menu
	public static function run():void{
		
		// Add plugin in admin panel menu
        add_action('admin_menu', NPShippingMethod::$default_menu);
        
        // Include actions
		ActionCenter::trsltCommonJs();
		ActionCenter::create_tables();
		ActionCenter::init_my_custom_shipping_method();
		ActionCenter::add_my_custom_shipping_method();
        //----------------------------

		// Include filters
		//FiltersFunctions::example();
		//----------------------------

		// admin_notices - action add opportunity show errors and successes messages
		add_action('admin_notices', function(){
			
			// If user do not has access
			if(!current_user_can('manage_options')) {

				add_settings_error( 
					'', '', 
					TranslatorCenter::run('You do not have access to this functions.'), 
					'error' 
				);

				return false;
			}

			// After access allowed, if need this access check
			//SetOptions::example();
			//----------------------------
		});
	}
	
    //-----------------------------------------------------------------------

    // Add plugin link in menu
	public static function plugin_in_menu():void{

		add_menu_page(
			'Nova Poshta Shipping Method', 
			'Nova Poshta ShipMet', 
			'manage_options', // user capability to plugin in menu 
			'base_page_np_agb_ship_met',
			NPShippingMethod::$default_page
		);
	}
	
	//-----------------------------------------------------------------------

	// Add submenu in main menu of plugin
	public static function addSubMenus(
		string $parentSlugName = 'standart_parent_menu_slug', 
		string $pageTitle = 'Standart page title', 
		string $menuTitle = 'Menu title', 
		string $capability = 'manage_options',
		string $menuSlug = 'standart_sub_menu_slug',
		array  $function = array('SubMenuCenter', 'standartPage')
	):void{

		add_submenu_page(
			$parentSlugName, 
			$pageTitle, 
			$menuTitle, 
			$capability, 
			$menuSlug, 
			$function
		);
	}

	//-----------------------------------------------------------------------
    
	// Require separate plugin page
	public static function show_page(): ?bool{
		
		if(!NPShippingMethod::standartAccessMessageForPages()) return false;
		
		require_once (NPShippingMethod::$base_page);
		return null;
	}
	
	//-----------------------------------------------------------------------

	public static function standartAccessMessageForPages():bool{

		echo '<h1>'.get_admin_page_title().'</h1>';
		
		settings_errors();
		
		// If user do not has access
		if(!current_user_can('manage_options')) {

			add_settings_error( '', '', TranslatorCenter::run('You do not have access to this page.'), 'error' );
			settings_errors();
			
			return false;
		}

		return true;
	}

	//-----------------------------------------------------------------------
}

//-----------------------------------------------------------------
//-----------------------------------------------------------------

NPShippingMethod::run();

//-----------------------------------------------------------------
//-----------------------------------------------------------------

?>