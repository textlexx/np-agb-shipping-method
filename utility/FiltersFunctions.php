<?php

namespace NpAgbShippingMethod;

class FiltersFunctions{

    // Ystanovka sortirovki tovarov kataloga po ymolchanijy po "date" vmesto "menu_order"
    public static function default_catalog_order(){
        
        add_filter('woocommerce_default_catalog_orderby', 'NpAgbShippingMethod\filter_def_order_agb', 1, 1000);
    }

    // Menyaem nazvanie elementa po ymolchanijy
    public static function list_all_catalog_orderby(){
        
        add_filter('woocommerce_catalog_orderby', 'NpAgbShippingMethod\all_catalog_orderby_agb', 1, 1001);
    }
}

// Ystanovka sortirovki tovarov kataloga po ymolchanijy po "date" vmesto "menu_order"
function filter_def_order_agb($order){

    $order = 'date';

    return $order;
}

// Menyaem nazvanie elementa po ymolchanijy
function all_catalog_orderby_agb($order_by){

    $order_by['menu_order'] = TranslatorCenter::run('Sort by setted');

    return $order_by;
}

?>