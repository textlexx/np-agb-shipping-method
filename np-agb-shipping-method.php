<?php

namespace NpAgbShippingMethod;

session_start();

define('LANG_THEME_KEY_AGB', 'agb-33da');

//-----------------------------------------------------------------
//-----------------------------------------------------------------

/*
Plugin Name: Products filters AGB System
Plugin URI: http://creation.zt.ua
Description: Products filters system for filtering products on site shop page of woocommerce.
Version: 1.0
Author: Aleksandr Borisovich Gaidash
Author URI: http://creation.zt.ua
*/

//-----------------------------------------------------------------
//-----------------------------------------------------------------

define('PLUGIN_DIR_NAME_PF_AGB', 'agb-products-filters');
define('PLUGIN_URL_DIR_PF_AGB', plugins_url());
define('DIR_CURRENT_PLUGIN_PF_AGB', PLUGIN_URL_DIR_PF_AGB.'/'.PLUGIN_DIR_NAME_PF_AGB);
define('PATH_CURRENT_PLUGIN_PF_AGB', $_SERVER['DOCUMENT_ROOT'].'/wp-content/plugins/'.PLUGIN_DIR_NAME_PF_AGB);

// Included common classes
require_once ('requireClasses.php');

//-----------------------------------------------------------------
//-----------------------------------------------------------------

//----------------------
set_filters_in_session_agb();
//----------------------

class ProductsFiltersAgb {

    // Path relatively this plugin dir
	public static $base_page = 'pages/pages_selector.php';
	// Default function add plugin in menu
	public static $default_menu = array(__CLASS__, 'plugin_in_menu');
	// Default function including plugin page
	public static $default_page = array(__CLASS__, 'show_page');

    // After plugin activated started show it in menu
	public static function run(){
		
		// Add plugin in admin panel menu
        add_action('admin_menu', ProductsFiltersAgb::$default_menu);
        
        // Include actions
		ActionCenter::translateCommonJs_agb();
		ActionCenter::create_tables();
		ActionCenter::startAddLoadImage_wp_scripts();
        ActionCenter::rename_and_delete_element();
		ActionCenter::clean_cache_agb();
		ActionCenter::set_custom_filters_agb();
		ActionCenter::ajax_products_adaptation_get();
		ActionCenter::add_in_theme_footer_loader_html();
        //----------------------------

		// Include filters
		FiltersFunctions::list_all_catalog_orderby();
		//----------------------------

		// admin_notices - action add opportunity show errors and successes messages
		add_action('admin_notices', function(){
			
			// If user do not has access
			if(!current_user_can('manage_options')) {

				add_settings_error( '', '', TranslatorCenter::run('You do not have access to this functions.'), 'error' );			
				return false;
			}

			SetOptions::add_new_products_filter_agb();
			//----------------------------
		});
	}
	
    //-----------------------------------------------------------------------

    // Add plugin link in menu
	public static function plugin_in_menu(){

		add_menu_page(
			'Products filters AGB System', 
			'Products filters AGB', 
			'manage_options', // user capability to plugin in menu 
			'products_filters_plugin_base_page_agb',
			ProductsFiltersAgb::$default_page
		);

		add_menu_page(
			TranslatorCenter::run('Clean Cache AGB'), 
			TranslatorCenter::run('Clean Cache AGB'), 
			'manage_options', // user capability to plugin in menu 
			'clean_cache_products_filters_plugin_base_page_agb',
			ProductsFiltersAgb::$default_page
		);
	}
	
	//-----------------------------------------------------------------------

	// Add submenu in main menu of plugin
	public static function addSubMenus(
		$parentSlugName = 'standart_parent_menu_slug', 
		$pageTitle = 'Standart page title', 
		$menuTitle = 'Menu title', 
		$capability = 'manage_options',
		$menuSlug = 'standart_sub_menu_slug',
		$function = array('SubMenuCenter', 'standartPage')
	){

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
	public static function show_page(){
		
		if(!ProductsFiltersAgb::standartAccessMessageForPages()) return false;
		
		require_once (ProductsFiltersAgb::$base_page);
	}
	
	//-----------------------------------------------------------------------

	public static function standartAccessMessageForPages(){

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

ProductsFiltersAgb::run();

//-----------------------------------------------------------------
//-----------------------------------------------------------------

?>