<?php

namespace NpAgbShippingMethod;

class FiltersFunctions{

    // Example
    /*
    public static function example():void{
        
        add_filter('filter_name', 'NpAgbShippingMethod\filter_handler_function_name', 1, 1000);
    }
    */

    public static function add_agb_shipping_methods():void{

        add_filter( 'woocommerce_shipping_methods', 'NpAgbShippingMethod\add_agb_shipping_methods' );
    }

    //-----------------------------------------------------------------------
}

?>