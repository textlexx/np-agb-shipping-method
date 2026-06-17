<?php

namespace spaceProductsFilters;

function startAddLoadImage_wp_scripts(){

    wp_enqueue_media(); 
}

function rename_element_agb(){

    $data = array(
        'status' => 'success',
        'successes' => array(
            'message' => TranslatorCenter::run('Success. Filter was renamed.'),
        ),
        'errors' => array(
            'message' => TranslatorCenter::run('Error. Rename was failure.'),
        ),
    );

    if(
        isset($_POST['filter_const_id']) &&
        isset($_POST['rename_name']) &&
        $_POST['filter_const_id'] > 0 &&
        $_POST['rename_name']
    ){

        $_POST['rename_name'] = ControlCenter::killWpMagicQuotes($_POST['rename_name']);

        $data['filter_const_id'] = $_POST['filter_const_id'];
        $data['rename_name'] = $_POST['rename_name'];

        if(!$res = Instruments::update_element_data_agb(
            $_POST['filter_const_id'], $_POST['rename_name']
        )){

            $data['status'] = 'error';
            echo ControlCenter::jsonEncode($data);
            die();
        }
    }else{

        $data['status'] = 'error';
    }

    echo ControlCenter::jsonEncode($data);
    die();
}

function delete_element_agb(){

    $data = array(
        'status' => 'success',
        'successes' => array(
            'delete' => TranslatorCenter::run('Success. Filter was deleted.'),
        ),
        'errors' => array(
            'delete' => TranslatorCenter::run('Error. Delete was failure.'),
        ),
    );

    if(isset($_POST['const_id']) && $_POST['const_id'] > 0){

        $data['const_id'] = $_POST['const_id'];

        if(!$res = Instruments::delete_element_data_agb($_POST['const_id'])){

            $data['status'] = 'error';
            echo ControlCenter::jsonEncode($data);
            die();
        }
    }else{

        $data['status'] = 'error';
    }

    echo ControlCenter::jsonEncode($data);
    die();
}

function clean_cache_agb(){

    $data = array(
        'status' => 'success',
        'successes' => array(
            'delete' => TranslatorCenter::run('Success. Cache was cleaned.'),
        ),
        'errors' => array(
            'delete' => TranslatorCenter::run('Error. Clean was failure.'),
        ),
    );

    if(isset($_POST['cache1'])){

        $cache1Dir = $_SERVER['DOCUMENT_ROOT'].'/cache_agb';

        clearstatcache();
        if(!is_dir($cache1Dir)){

            $data['status'] = 'error';
            $data['errors']['delete'] = TranslatorCenter::run('Error. Cache dir not exists.');
            echo ControlCenter::jsonEncode($data);
            die();
        }

        $files = glob($cache1Dir.'/*', GLOB_BRACE);

        foreach($files as $val) unlink($val);
    }
    elseif(isset($_POST['cache2'])){

        $cache2Dir = $_SERVER['DOCUMENT_ROOT'].'/wp-content/plugins/agb-products-filters/cache';

        clearstatcache();
        if(!is_dir($cache2Dir)){

            $data['status'] = 'error';
            $data['errors']['delete'] = TranslatorCenter::run('Error. Cache dir not exists.');
            echo ControlCenter::jsonEncode($data);
            die();
        }

        $files = glob($cache2Dir.'/*', GLOB_BRACE);

        foreach($files as $val) unlink($val);
    }
    else{

        $data['status'] = 'error';
    }

    echo ControlCenter::jsonEncode($data);
    die();
}

function create_db_tables_agb(){

    $sqlFile = 
    $_SERVER['DOCUMENT_ROOT'].
    '/wp-content/plugins/'.PLUGIN_DIR_NAME_PF_AGB.
    '/sql_tables/wp_agb_products_filters.sql';

    if(!file_exists($sqlFile)) return false;

    $sql = file_get_contents($sqlFile);

    if(!$sql) return false;

    global $wpdb;

    $result = $wpdb->query($sql);

    if(!$result) return false;

    return true;
}


function get_max_min_products_price_agb(){

    $data = array(
        'status' => 'success',
        'errors' => array(
            'delete' => TranslatorCenter::run('Error. Max min price was failure.'),
        ),
    );

    $price = Instruments::get_max_min_products_price();

    if(!property_exists($price, 'max_price')) {
        
        $data['status'] = 'error';
        echo ControlCenter::jsonEncode($data);
        die();
    }

    $data['price'] = $price;

    echo ControlCenter::jsonEncode($data);
    die();
}


function ajax_products_adaptation_get(){

    Instruments::ajax_products_adaptation_get();
}


function set_custom_filters_agb($query){
	
	if ( ! is_admin() && (is_shop() || is_product_category()) && $query->is_main_query() ) {

        $category_id = Instruments::category_id_compact_get();

        //----------------------
        set_filters_in_session_agb(
            /*$filters = */'', /*$price_filter = */'', /*$limit = */'', 
            /*$order_by = */'', /*$page = */'', $category_id
        );
        //----------------------

        $filters = Instruments::get_all_filters_for_list();

        $is_price_filter = 0;


        foreach($filters as $key => $fltr){

            if(
                ($fltr->from_wc_table_attr_taxonomy_id == 0 /*0 - is price*/ &&
                $category_id == $fltr->filter_category_id) ||
                // Esli net cenevogo filtra dlya konkretnoj kategorii,
                // to esli est cenovoj filtr dlya vsego magazina ispolzyem ego
                ($fltr->from_wc_table_attr_taxonomy_id == 0 /*0 - is price*/ &&
                $fltr->filter_category_id == 0 /*0 -kategoriya oboznachaet ves magazin toest vse kategorii*/)
            ){

                $is_price_filter = 1;
                break;
            }
        }

        $session_filters = get_filters_from_session_agb();

        $price = false;
        if($is_price_filter == 1){

            $price = Instruments::get_max_min_products_price($category_id);

            if(
                !isset($session_filters['price']) || 
                !is_numeric($session_filters['price']['min']) ||
                !is_numeric($session_filters['price']['max'])
            ){

                $min_price = $price->min_price;
                $max_price = $price->max_price;
            }else{

                $min_price = $session_filters['price']['min'];
                $max_price = $session_filters['price']['max'];
            }
        }

        if(isset($_GET['per_page']) && $_GET['per_page'] > 0){

            $limit = $_GET['per_page'];
            set_limit_products_result_in_session($limit);
        }elseif(!isset($session_filters['limit']) || !$session_filters['limit']){

            $limit = 12;
        }else{

            $limit = $session_filters['limit'];
        }

        if(!$price){

            $products = Instruments::get_products_for_filter(
                $category_id, 0, 0, $session_filters['filter'], 
                /*$limit = */'', /*$order = */'', /*$page = */''
            );
        }else{

            $products = Instruments::get_products_for_filter(
                $category_id, $min_price, $max_price, $session_filters['filter'], 
                /*$limit = */'', /*$order = */'', /*$page = */''
            );
        }

        /*if(
            isset($session_filters['order_by']) && 
            isset($session_filters['order_by']['order_name']) &&
            isset($session_filters['order_by']['order_method']) && 
            $session_filters['order_by']['order_name'] && 
            $session_filters['order_by']['order_method']
        ){

            $order = array(
                'order_name' => $session_filters['order_by']['order_name'],
                'order_method' => $session_filters['order_by']['order_method'],
            );
        }else*/if(isset($_GET['orderby']) && $_GET['orderby']){

            if($_GET['orderby'] == 'popularity'){

                $order = array(
                    'order_name' => 'popularity',
                    'order_method' => 'DESC',
                );
            }elseif($_GET['orderby'] == 'rating'){

                $order = array(
                    'order_name' => 'rating',
                    'order_method' => 'DESC',
                );
            }elseif($_GET['orderby'] == 'date'){

                $order = array(
                    'order_name' => 'date',
                    'order_method' => 'DESC',
                );
            }elseif($_GET['orderby'] == 'price-desc'){

                $order = array(
                    'order_name' => 'price-desc',
                    'order_method' => 'DESC',
                );
            }elseif($_GET['orderby'] == 'price'){

                $order = array(
                    'order_name' => 'price',
                    'order_method' => 'ASC',
                );
            }else{

                $order = array(
                    'order_name' => 'date',
                    'order_method' => 'DESC',
                );
            }
        }else{

            $order = array(
                'order_name' => 'date',
                'order_method' => 'DESC',
            );
        }

        set_order_products_result_in_session($order);

        $ids = array();
        foreach($products as $product) $ids[] = $product->id;
        // For no results if no results by some filters combination
        if(!$ids) $ids[] = 0;

        $paged = get_query_var( 'paged' );

        set_page_products_result_in_session($paged);

        // Po reitengy
        //$query->set('meta_key', '_wc_average_rating');
        // Dlya pravilnoj sortirovki po cene nyzhno ne "meta_value" a "meta_value_num"
        // I takzhe eto kasaetsa vseh chislovuh znachenij v pole "meta_value" tablicu "postmeta"
        // ystanovka tolka esli est v filtre sortirovka inache ispolzovat standart
        //if($order) $query->set('orderby', 'meta_value_num');

        // Po cene
        // Dlya pravilnoj sortirovki nyzhno ispolzovat "_price" tak kak zdes ykazana realnaya dejstvyjyshchaia cena
        /*
        if($order && isset($order['order_name']) && $order['order_name']){

            if($order['order_name'] == 'menu_order'){

                // Esli sortirovka po osnovnoj tablice posts to "meta_key" ne nyzhno
                $query->set('orderby', 'menu_order');
            }elseif($order['order_name'] == 'popularity'){

                $query->set('meta_key', 'total_sales');
            }elseif($order['order_name'] == 'rating'){

                $query->set('meta_key', '_wc_average_rating');
            }elseif($order['order_name'] == 'date'){

                // Esli sortirovka po osnovnoj tablice posts to "meta_key" ne nyzhno
                $query->set('orderby', 'post_date');
            }else{

                $query->set('meta_key', '_price');
            }
        }
        */

        if( 
            ! isset($_SESSION['products_filters_agb']['order_by']['order_name']) || 
            ! isset($_SESSION['products_filters_agb']['order_by']['order_method']) || 
            $_SESSION['products_filters_agb']['order_by']['order_name'] == 'date'
        ){

            $query->set('orderby', 'post_date');
            $query->set('order', 'DESC');
        }
        

        $query->set('posts_per_page', $limit);
        $query->set('paged', $paged);
        $query->set('post__in', $ids);
	}
}


function get_filters_from_session_agb(){

    if(isset($_SESSION['products_filters_agb']) && count($_SESSION['products_filters_agb']) < 1) return false;

    return $_SESSION['products_filters_agb'];
}

function set_filters_in_session_agb(
    $filters = '', $price_filter = '', $limit = '', $order_by = '', $page = '', $category_id_agb = ''
){

    if(!isset($_SESSION['products_filters_agb'])) $_SESSION['products_filters_agb'] = array();
    if(!isset($_SESSION['products_filters_agb']['filter'])) $_SESSION['products_filters_agb']['filter'] = array();


    if(
        isset($_SESSION['products_filters_agb']['category_id_agb'])
    ){

        if(
            is_numeric($category_id_agb) && 
            $_SESSION['products_filters_agb']['category_id_agb'] != $category_id_agb 
        ){

            $_SESSION['products_filters_agb']['filter'] = array();
            $_SESSION['products_filters_agb']['price'] = array();
            $_SESSION['products_filters_agb']['category_id_agb'] = $category_id_agb;
        }
    }else{

        if($category_id_agb && is_numeric($category_id_agb)){

            $_SESSION['products_filters_agb']['category_id_agb'] = $category_id_agb;
        }else{
    
            $_SESSION['products_filters_agb']['category_id_agb'] = 0;
        }
    }
    

    if($filters){

        foreach($filters as $key => $array_filters_ids){

            if(!isset($_SESSION['products_filters_agb']['filter'][$key]))
            $_SESSION['products_filters_agb']['filter'][$key] = array();

            if(!$array_filters_ids)
            unset($_SESSION['products_filters_agb']['filter'][$key]);
            else
            $_SESSION['products_filters_agb']['filter'][$key] = $array_filters_ids;
        }
    }
    //------------------------------------------

    //$_SESSION['products_filters_agb']['filter']['color'] = array();

    //$_SESSION['products_filters_agb']['filter']['material'] = array();

    //$_SESSION['products_filters_agb']['filter']['color'][] = 31;
    //$_SESSION['products_filters_agb']['filter']['color'][] = 98;
    //$_SESSION['products_filters_agb']['filter']['color'][] = 99;
    //$_SESSION['products_filters_agb']['filter']['color'][] = 196;
    //$_SESSION['products_filters_agb']['filter']['material'][] = 216;
    //$_SESSION['products_filters_agb']['filter']['material'][] = 228; /*no results on 31 color*/
    
    //------------------------------------------
    //------------------------------------------
    

    if(!isset($_SESSION['products_filters_agb']['price']))
    $_SESSION['products_filters_agb']['price'] = array();

    if($price_filter){

        if(
            !$price_filter['max']
        ){

            $_SESSION['products_filters_agb']['price'] = array();
        }else{

            if(!$price_filter['min']) $price_filter['min'] = 0;
            if(!$price_filter['max']) $price_filter['max'] = 0;

            $_SESSION['products_filters_agb']['price']['min'] = $price_filter['min'];
            $_SESSION['products_filters_agb']['price']['max'] = $price_filter['max'];
        }
    }else{

        if(isset($_SESSION['products_filters_agb']['price']['min'])){

            if(!is_numeric($_SESSION['products_filters_agb']['price']['min'])) 
            $_SESSION['products_filters_agb']['price'] = array();
        } else $_SESSION['products_filters_agb']['price'] = array();

        if(isset($_SESSION['products_filters_agb']['price']['max'])){

            if(!is_numeric($_SESSION['products_filters_agb']['price']['max'])) 
            $_SESSION['products_filters_agb']['price'] = array();
        } else $_SESSION['products_filters_agb']['price'] = array();
    }
    //------------------------------------------

    if(!isset($_SESSION['products_filters_agb']['limit']))
    $_SESSION['products_filters_agb']['limit'] = '';

    if($limit){

        $_SESSION['products_filters_agb']['limit'] = $limit;
    }else{

        $_SESSION['products_filters_agb']['limit'] = 12;
    }
    //------------------------------------------

    if(!isset($_SESSION['products_filters_agb']['page']))
    $_SESSION['products_filters_agb']['page'] = '';

    if($page === 0 || $page > 0){

        $_SESSION['products_filters_agb']['page'] = $page;
    }
    //------------------------------------------

    if(!isset($_SESSION['products_filters_agb']['order_by']))
    $_SESSION['products_filters_agb']['order_by'] = array();

    if($order_by && $order_by['order_name'] && $order_by['order_method']){

        /* Example
        $order_by = array(
            'order_name' => 'price',
            'order_method' => 'ASC',
        );*/
        $_SESSION['products_filters_agb']['order_by'] = $order_by;
    }else{

        if( 
            ! isset($_SESSION['products_filters_agb']['order_by']['order_name']) || 
            ! isset($_SESSION['products_filters_agb']['order_by']['order_method'])
        ){

            $_SESSION['products_filters_agb']['order_by'] = array(
                'order_name' => 'date',
                'order_method' => 'DESC',
            );
        }
    }
    //------------------------------------------
}

function set_amount_products_result_in_session($amount = 0){

    if(!isset($_SESSION['products_filters_agb'])) $_SESSION['products_filters_agb'] = array();

    if($amount) $_SESSION['products_filters_agb']['amount'] = $amount;
    else 
    $_SESSION['products_filters_agb']['amount'] = 0;
}

function set_limit_products_result_in_session($limit = 0){

    if(!isset($_SESSION['products_filters_agb'])) $_SESSION['products_filters_agb'] = array();

    if($limit) $_SESSION['products_filters_agb']['limit'] = $limit;
    else 
    $_SESSION['products_filters_agb']['limit'] = '';
}

function set_order_products_result_in_session($order = 0){

    if(!isset($_SESSION['products_filters_agb'])) $_SESSION['products_filters_agb'] = array();

    if(is_array($order) && isset($order['order_name']) && isset($order['order_method'])) 
    $_SESSION['products_filters_agb']['order_by'] = $order;
    else 
    $_SESSION['products_filters_agb']['order_by'] = array();
}

function set_page_products_result_in_session($page){

    if(!isset($_SESSION['products_filters_agb'])) $_SESSION['products_filters_agb'] = array();

    if(is_numeric($page)) $_SESSION['products_filters_agb']['page'] = $page;
    else 
    $_SESSION['products_filters_agb']['page'] = '';
}

function footer_product_filter_plugin_loader_html(){

    Files_Include_Functions::include_template_php('footer-loader');
}

function translateCommonJs_agb(){

    echo '
    <script>
    window.translate_for_js_fltr_plugin = {
        "no_product_attr_selected" : '.
        '"'.TranslatorCenter::run('Error, not all attrs was selected').'",'.
        '"no_match_in_all_attrs_product_variation_id" : '.
        '"'.TranslatorCenter::run('Error, some attrs selected has no match between itself.').'",'.
        '"fast_buy_no_attr" : '.
        '"'.TranslatorCenter::run('Error, not all attrs was selected or not all attrs load.').'",'.
        '"fast_buy_fill_params" : '.
        '"'.TranslatorCenter::run('Error, you must select all parameters of product.').'",'.
        '"fast_buy_fill_phone_mail" : '.
        '"'.TranslatorCenter::run('Error, you must fill phone, name and email right.').'",'.
        '"to_see_cart" : '.
        '"'.TranslatorCenter::run('To see cart').'",'.
        '"success_fast_buy" : '.
        '"'.TranslatorCenter::run('Thank you for order, we contact with you in several time.').'",'.
        '"subscribe_fill_form" : '.
        '"'.TranslatorCenter::run('Error, you must fill all fields of form.').'",'.
        '"success_subscribe" : '.
        '"'.TranslatorCenter::run('Thank you for subscribe, and now you can get news from site.').'",'.
        '"clean_filters" : '.
        '"'.TranslatorCenter::run('Clean filters').'"
    }
    </script>
    ';
}



function reroutingOnRightLink_plg(){

    $protocol = (strtolower(substr($_SERVER["SERVER_PROTOCOL"], 0, 5)) == 'https') ? 'https' : 'http';

    if($_SERVER["SERVER_PORT"] == 443)
        $protocol = 'https';
    elseif (isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1')))
        $protocol = 'https';
    elseif (
        !empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || 
        !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on'
    )
        $protocol = 'https';
    elseif (!empty($_SERVER["REQUEST_SCHEME"]) && $_SERVER["REQUEST_SCHEME"] == 'https')
        $protocol = 'https';
        
    return $protocol.'://';
}

?>