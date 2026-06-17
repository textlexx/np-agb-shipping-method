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

    public static function create_tables(){

        add_action(
            'init', 
            'NpAgbShippingMethod\create_db_tables'
        );
    }

    //-----------------------------------------------------------------------

    public static function trsltCommonJs(){

        add_action('wp_head', 'NpAgbShippingMethod\trsltCommonJs', 1);
    }

    //-----------------------------------------------------------------------
}