<?php

namespace spaceProductsFilters;

class ActionCenter{

    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------

    /*
    public static function add_some_action_function(){

        add_action('name_of_some_action', 'spaceProductsFilters\some_function', 1);
    }
    */

    public static function startAddLoadImage_wp_scripts(){

        add_action('admin_enqueue_scripts', 'spaceProductsFilters\startAddLoadImage_wp_scripts', 9999);
    }

    public static function rename_and_delete_element(){

        add_action(
            'wp_ajax_rename_element_agb', 
            'spaceProductsFilters\rename_element_agb'
        );

        add_action(
            'wp_ajax_delete_element_agb', 
            'spaceProductsFilters\delete_element_agb'
        );
    }

    //-----------------------------------------------------------------------

    public static function clean_cache_agb(){

        add_action(
            'wp_ajax_clean_cache_agb', 
            'spaceProductsFilters\clean_cache_agb'
        );
    }

    //-----------------------------------------------------------------------

    public static function ajax_products_adaptation_get(){

        add_action(
            'wp_ajax_ajax_products_adaptation_get', 
            'spaceProductsFilters\ajax_products_adaptation_get'
        );

        add_action(
            'wp_ajax_nopriv_ajax_products_adaptation_get', 
            'spaceProductsFilters\ajax_products_adaptation_get'
        );
    }

    //-----------------------------------------------------------------------

    public static function create_tables(){

        add_action(
            'init', 
            'spaceProductsFilters\create_db_tables_agb'
        );
    }

    //-----------------------------------------------------------------------

    public static function set_custom_filters_agb(){

        add_action( 'pre_get_posts', 'spaceProductsFilters\set_custom_filters_agb', 1000 );
    }

    //-----------------------------------------------------------------------

    public static function add_in_theme_footer_loader_html(){

        add_action('wp_footer', 'spaceProductsFilters\footer_product_filter_plugin_loader_html', 1);
    }

    //-----------------------------------------------------------------------

    public static function translateCommonJs_agb(){

        add_action('wp_head', 'spaceProductsFilters\translateCommonJs_agb', 1);
    }

    //-----------------------------------------------------------------------
}