<?php

namespace NpAgbShippingMethod;

class ActionCenter{

    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------

    /*
    public static function add_some_action_function():void{

        add_action('name_of_some_action', 'NpAgbShippingMethod\some_function', 1);
    }
    */

    public static function create_tables():void{

        add_action(
            'init', 
            'NpAgbShippingMethod\create_db_tables'
        );
    }

    //-----------------------------------------------------------------------

    public static function trsltCommonJs():void{

        add_action('wp_head', 'NpAgbShippingMethod\trsltCommonJs', 1);
    }

    //-----------------------------------------------------------------------

    public static function init_my_custom_shipping_method():void{

        add_action( 'plugins_loaded', 'NpAgbShippingMethod\init_my_custom_shipping_method' );
    }

    //-----------------------------------------------------------------------

    public static function add_my_custom_shipping_method():void{

        add_action( 'woocommerce_shipping_methods', 'NpAgbShippingMethod\add_my_custom_shipping_method' );
    }

    //-----------------------------------------------------------------------
}