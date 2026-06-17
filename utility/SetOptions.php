<?php

namespace NpAgbShippingMethod;

class SetOptions{
	
	//-----------------------------------------------------------------------
	/*
	public static function exampleSetOption(){
		
		if(!isset($_REQUEST['param_name1']) || $_REQUEST['param_name2'] == 'on') return false;
		
		$option_name = '';
		$er_id_div_code = '';
		$er_type = 'error';
		
		// Add option if not exists or update if exists
		if(update_option('name_of_update_option', 0)){
			
			$er_type = 'updated';
			$er_message = TranslatorCenter::run('Success. name_of_update_option option was saved.');
			add_settings_error( $option_name, $er_id_div_code, $er_message, $er_type );

			return true;
		}
		
		return false;
	}
	*/
	//-----------------------------------------------------------------------

	public static function add_new_products_filter_agb(){
		
		if( 
			!isset($_POST['agb_product_filter_type']) || 
			!isset($_POST['agb_product_filter_category']) || 
			!isset($_POST['agb_product_filter_name']) 
		) return false;
				
		$option_name = '';
		$er_id_div_code = '';
		$er_type = 'error';

		if(
			ControlCenter::empty($_POST['agb_product_filter_type']) || 
			ControlCenter::empty($_POST['agb_product_filter_category'])
		) {
			
			$er_message = 
			TranslatorCenter::run('Error. Field of form must be fill.');
			add_settings_error( $option_name, $er_id_div_code, $er_message, $er_type );

			return false;
		}
		//----------------------

		// If isset filter name
		if(!ControlCenter::empty($_POST['agb_product_filter_name'])){

			if(!ControlCenter::is_opt_amount_symbols_right($_POST['agb_product_filter_name'], 250)){
			
				$er_message = 
				TranslatorCenter::run('Error. This filter NAME has wrong symbols count, must be lower then 250.');
				add_settings_error( $option_name, $er_id_div_code, $er_message, $er_type );
	
				return false;
			}
		}
		//----------------------

		// dlya "wc_attribute_taxonomy_name_by_id" fynkcii obyazatelno nyzhno peredavat tip (INT) 
		// inache ne bydet rabotat
		if(
			!wc_attribute_taxonomy_name_by_id( (int) $_POST['agb_product_filter_type'] ) && 
			$_POST['agb_product_filter_type'] != 0 /*0 - is price*/
		){

			$er_message = TranslatorCenter::run('Error. Can not add. Filter attribute not exists.');
			add_settings_error( $option_name, $er_id_div_code, $er_message, $er_type );

			return false;
		}
		//----------------------
		
		// Add filter
			
		global $wpdb;

		$table = 'wp_agb_products_filters';

		$id = Instruments::genNextId($table);
		$const_id = Instruments::genNextId($table, 'const_id');

		$product_filter_name = '';
		if(
			ControlCenter::empty($_POST['agb_product_filter_name']) && 
			$_POST['agb_product_filter_type'] != 0 /*0 - is price*/
		){

			$sql = '
			SELECT `attribute_name`
			FROM `'.$wpdb->prefix.'woocommerce_attribute_taxonomies` 
			WHERE `attribute_id` = "'.$_POST['agb_product_filter_type'].'"';

			$res = $wpdb->get_row($sql);

			if(!$res) {
				
				$er_message = TranslatorCenter::run('Error. With db. Get attribute id.');
				add_settings_error( $option_name, $er_id_div_code, $er_message, $er_type );
				return false;
			}

			if(!property_exists($res, 'attribute_name')) {
				
				$er_message = TranslatorCenter::run('Error. With db. Get attribute id (2).');
				add_settings_error( $option_name, $er_id_div_code, $er_message, $er_type );
				return false;
			}

			$product_filter_name = $res->attribute_name;
		}elseif($_POST['agb_product_filter_type'] == 0 /*0 - is price*/){

			$product_filter_name = 'price';
		}else{

			$product_filter_name = $_POST['agb_product_filter_name'];
		}


		$res = $wpdb->insert(
			$table,
			array(
				'id' => $id,
				'const_id' => $const_id,
				'filter_category_id' => $_POST['agb_product_filter_category'],
				'from_wc_table_attr_taxonomy_id' => $_POST['agb_product_filter_type'],
				'name' => $product_filter_name,
			),
			array('%d', '%d', '%d', '%d', '%s',)
		);

		if(!$res) {

			$er_message = TranslatorCenter::run('Error. Insert filter in db.');
			add_settings_error( $option_name, $er_id_div_code, $er_message, $er_type );
			return false;
		}
		//----------------------

		$er_type = 'updated';
		$er_message = TranslatorCenter::run('Success. Products filter was added.');
		add_settings_error( $option_name, $er_id_div_code, $er_message, $er_type );

		return true;
	}

	//-----------------------------------------------------------------------

}

