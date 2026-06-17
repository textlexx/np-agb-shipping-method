<?php

namespace NpAgbShippingMethod;

class ActionCenter{

    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------

    /*
    public static function add_some_action_function(){

        add_action('name_of_some_action', 'NpAgbShippingMethod\some_function', 1);
    }
    */

    public static function startAddLoadImage_wp_scripts(){

        add_action('admin_enqueue_scripts', 'NpAgbShippingMethod\startAddLoadImage_wp_scripts', 9999);
    }

    public static function rename_and_delete_element(){

        add_action(
            'wp_ajax_rename_element_agb', 
            'NpAgbShippingMethod\rename_element_agb'
        );

        add_action(
            'wp_ajax_delete_element_agb', 
            'NpAgbShippingMethod\delete_element_agb'
        );
    }

    //-----------------------------------------------------------------------

    public static function clean_cache_agb(){

        add_action(
            'wp_ajax_clean_cache_agb', 
            'NpAgbShippingMethod\clean_cache_agb'
        );
    }

    //-----------------------------------------------------------------------

    public static function ajax_products_adaptation_get(){

        add_action(
            'wp_ajax_ajax_products_adaptation_get', 
            'NpAgbShippingMethod\ajax_products_adaptation_get'
        );

        add_action(
            'wp_ajax_nopriv_ajax_products_adaptation_get', 
            'NpAgbShippingMethod\ajax_products_adaptation_get'
        );
    }

    //-----------------------------------------------------------------------

    public static function create_tables(){

        add_action(
            'init', 
            'NpAgbShippingMethod\create_db_tables_agb'
        );
    }

    //-----------------------------------------------------------------------

    public static function set_custom_filters_agb(){

        add_action( 'pre_get_posts', 'NpAgbShippingMethod\set_custom_filters_agb', 1000 );
    }

    //-----------------------------------------------------------------------

    public static function add_in_theme_footer_loader_html(){

        add_action('wp_footer', 'NpAgbShippingMethod\footer_product_filter_plugin_loader_html', 1);
    }

    //-----------------------------------------------------------------------

    public static function translateCommonJs_agb(){

        add_action('wp_head', 'NpAgbShippingMethod\translateCommonJs_agb', 1);
    }

    //-----------------------------------------------------------------------
}