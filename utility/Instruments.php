<?php

namespace NpAgbShippingMethod;

class Instruments{

    public static function get_all_filters(){

        global $wpdb;

        $table = $wpdb->prefix.'woocommerce_attribute_taxonomies';

        $sql = '
        SELECT 
            `attribute_id`, `attribute_name`
        FROM `'.$table.'` 
        ';

        $results = $wpdb->get_results($sql);
        
        if(!$results) return false;

        return $results;
    }

    //-----------------------------------------------------------------------

    public static function get_concrete_attr_filter_by_id($attribute_id){

        global $wpdb;

        $table = $wpdb->prefix.'woocommerce_attribute_taxonomies';

        $sql = '
        SELECT 
            `attribute_id` AS `id`, 
            `attribute_label` AS `label`, 
            `attribute_name` AS `slug`
        FROM `'.$table.'` 
        WHERE
            `attribute_id` = '.$attribute_id.' 
        ';

        $results = $wpdb->get_row($sql);
        
        if(!$results) return false;

        return $results;
    }

    //-----------------------------------------------------------------------

    public static function get_all_filters_for_list(){

        global $wpdb;

        $table = 'wp_agb_products_filters';

        $sql = '
        SELECT 
            `id`, `const_id`, `filter_category_id`, `from_wc_table_attr_taxonomy_id`, `name`
        FROM `'.$table.'` 
        ';

        $results = $wpdb->get_results($sql);
        
        if(!$results) return false;

        return $results;
    }

    //-----------------------------------------------------------------------

    public static function filters_output_on_site(){
        
        // Esli nahodimsya na stranice magazina ili na stranice kategorii magazina
        if(!is_shop() && !is_product_category()) return '';

        $filters = Instruments::get_all_filters_for_list();

        if(!is_array($filters) || count($filters) < 1) return '';

        $session_filters = get_filters_from_session_agb();

        $is_price_filter = 0;

        $lang = false;
        if(function_exists('pll_default_language')){
                    
            if(is_admin()){

                // In admin only default lang
                $lang = pll_default_language();
            }else{

                $lang = pll_current_language();
            }
        }

        // Opredelit identifikator kategorii gde dolzhen but filter
        // i esli aktivna eta kategoriya to vuvesti filter
        $category_id = Instruments::category_id_compact_get();
        $lang_param = ($lang) ? 'site_lang="'.$lang.'"' : 'site_lang=""';

        echo '<div 
        class="filter-category-div" 
        category_id="'.$category_id.'" 
        '.$lang_param.' 
        style="display:none;"
        ></div>';

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

        Files_Include_Functions::include_html_file_css('filter-styles');

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

            if($price){

                echo 
                '<div class="filter-out-block-agb">'.
                    '<div slug_attrs_group="price" class="filter-block-name-agb">'.
                        '<span>'.
                        TranslatorCenter::run($fltr->name).
                        '</span>'.
                    '</div>'.

                    '<div class="bottom-line-price-agb">'.
                        '<div class="price-part-agb">'.
                            '<b>'.
                            TranslatorCenter::run('Price:').
                            '</b>'.

                            '<span class="first-currency">'.
                            TranslatorCenter::run('from').
                            '</span>'.
                            '<span old_price_min="'.$price->min_price.'" class="first-price">'.
                            $min_price.
                            '</span>'.

                            '<span class="second-currency">'.
                            TranslatorCenter::run('to').
                            '</span>'.
                            '<span old_price_max="'.$price->max_price.'" class="second-price">'.
                            $max_price.
                            '</span>'.
                        '</div>'.

                        '<div class="btn-part-agb">'.
                            '<div class="fltr-btn-agb">'.
                            TranslatorCenter::run('Filter').
                            '</div>'.
                        '</div>'.
                    '</div>'.

                    '<div class="filter-line-block-agb">'.
                        '<div class="min-element-agb">'.
                        '</div>'.
                        '<div class="max-element-agb">'.
                        '</div>'.
                        '<div class="center-line-agb">'.
                        '</div>'.
                    '</div>'.
                '</div>';
            }
        }


        foreach($filters as $key => $fltr){

            if(
                $fltr->from_wc_table_attr_taxonomy_id != 0 && 
                ($category_id == $fltr->filter_category_id || 
                $fltr->filter_category_id == 0 /*show on all categories*/)
            ){

                $tax_filter_name = wc_attribute_taxonomy_name_by_id( 
                    (int) $fltr->from_wc_table_attr_taxonomy_id 
                );
                $in_sess_ftrs = ''; $filter_values2 = '';
                if(isset($session_filters['filter'])){

                    $name_for_session = preg_replace('#pa_#', '', $tax_filter_name);
                    $in_sess_ftrs = $session_filters['filter'][$name_for_session];

                    if($in_sess_ftrs){

                        $filter_values2 = Instruments::get_elemets_of_filter_by_id(
                            $tax_filter_name, $in_sess_ftrs
                        );
                    }
                }                
                $filter_values = Instruments::get_elemets_of_filter_by_id(
                    $tax_filter_name, ''
                );

                //--------------------------------------
                // If active multilangual get right translate
                if($lang){
                    
                    $filter_values = Instruments::terms_with_right_lang($lang, $filter_values);
                    if($filter_values2 && is_array($filter_values2) && count($filter_values2) > 0){

                        $filter_values2 = Instruments::terms_with_right_lang($lang, $filter_values2);
                    }
                }
                //--------------------------------------

                if(!is_array($filter_values) || count($filter_values) < 1) continue;
                //--------------------------------------

                $fltr_attr_list = '<ul class="filter-attr-list-agb">';
                foreach($filter_values as $key => $attr){
                    
                    $amount = Instruments::get_products_amount_by_term_id($category_id, $attr->term_id);
                    if($amount > 0){

                        $attr_class = '';
                        if(isset($filter_values2[$key]) /*for selected elements*/){

                            if(isset($session_filters['amount'])) $amount = $session_filters['amount'];

                            if(isset($session_filters['filter'][$name_for_session])){

                                $from_session_filters = 
                                Instruments::get_elemets_of_filter_by_id(
                                    'pa_'.$name_for_session, $session_filters['filter'][$name_for_session]
                                );

                                $from_session_filters = 
                                Instruments::terms_with_right_lang(
                                    $lang, 
                                    $from_session_filters
                                );

                                foreach($from_session_filters as $kkk => $vvv){

                                    if($vvv->term_id == $attr->term_id) {

                                        $attr_class = 'class="active"';
                                    }
                                }
                            }
                        }
                        
                        $fltr_attr_list .= 
                        '<li '.$attr_class.' term_slug="'.$attr->slug.'" term_id="'.$attr->term_id.'">'.
                            '<span class="filter-checkbox-agb"></span>'.

                            '<span>'.
                            $attr->name.
                            '</span>'.

                            '<span>'.
                            $amount.
                            '</span>'.
                        '</li>';
                    
                    }
                }
                $fltr_attr_list .= '</ul>';

                $att_grp = Instruments::get_concrete_attr_filter_by_id(
                    $fltr->from_wc_table_attr_taxonomy_id
                );

                
                if( preg_match('#filter-checkbox-agb#i', $fltr_attr_list) ){

                    echo 
                    '<div class="filter-out-block-agb">'.
                        '<div slug_attrs_group="'.$att_grp->slug.'" class="filter-block-name-agb">'.
                            '<span>'.
                            TranslatorCenter::run($fltr->name).
                            '</span>'.
                        '</div>'.

                        $fltr_attr_list.
                    '</div>';

                }
            }
        }

        echo 
        '<div class="addition-btn-filter-agb">'.
            '<div class="btn-part-agb">'.
                '<div class="fltr-btn-agb-2 clean-price">'.
                TranslatorCenter::run('Clean price').
                '</div>'.
            '</div>'.

            '<div class="btn-part-agb">'.
                '<div class="fltr-btn-agb-2 apply-filter">'.
                TranslatorCenter::run('Apply').
                '</div>'.
            '</div>'.

            '<div class="btn-part-agb">'.
                '<div class="fltr-btn-agb-2 clean-all">'.
                TranslatorCenter::run('Clean filter').
                '</div>'.
            '</div>'.
        '</div>';

        echo '
        <script>
        window.trslt_btn_addition_loading = "'.TranslatorCenter::run('Load more').'";
        </script>';

        Files_Include_Functions::include_html_file_js('commonAdminFunc');
        Files_Include_Functions::include_html_file_js('common-functions-for-filter');
        Files_Include_Functions::include_html_file_js('fast-add-to-cart-and-attrs-marks-refresh');
        Files_Include_Functions::include_html_file_js('fast-buy-refresh-action');
        Files_Include_Functions::include_html_file_js('wish-list-btn-refresh');
        Files_Include_Functions::include_html_file_js('price-filter');
        Files_Include_Functions::include_html_file_js('order-element-on-shop');
        Files_Include_Functions::include_html_file_js('limit_results_per_page');
        Files_Include_Functions::include_html_file_js('additional_loading');
        Files_Include_Functions::include_html_file_js('on-filters-elements-click-select');
        Files_Include_Functions::include_html_file_js('clean-filters-js');

        // Test addition product block output
        //ProductsFunctions::after_title_addition_block_agb(282);
    }

    //-----------------------------------------------------------------------

    public static function get_elemets_of_filter_by_id($tax_filter_name, $session_filters = ''){

        if(!$tax_filter_name) return false;

        global $wpdb;
    
        $t1 = $wpdb->prefix.'terms';
        $t2 = $wpdb->prefix.'term_taxonomy';

        if($session_filters){

            $filters_ids = '';
            foreach($session_filters as $key => $val){

                $filters_ids .= $val.',';
            }
            $filters_ids = preg_replace('#,$#', '', $filters_ids);

            $part_sql = 'AND `'.$t1.'`.`term_id` IN ('.$filters_ids.')';
        }else $part_sql = '';
        //------------------------
    
        $sql = '
        SELECT 
            `'.$t1.'`.`term_id` AS `term_id`, 
            `'.$t1.'`.`name` AS `name`, 
            `'.$t1.'`.`slug` AS `slug` 
        FROM `'.$t1.'` 
        LEFT JOIN `'.$t2.'`
        ON 
            `'.$t1.'`.`term_id` = `'.$t2.'`.`term_id` 
        WHERE
            `'.$t2.'`.`taxonomy` = "'.$tax_filter_name.'" 
            '.$part_sql.'
        ';
    
        $result = $wpdb->get_results($sql);
        if(!$result) return false;
    
        return $result;
    }

    //-----------------------------------------------------------------------

    // Zdes mozhno delat vuborky bez ycheta cenu
    public static function get_max_min_products_price($product_catg = 0){

        global $wpdb;
    
        $p = $wpdb->prefix.'posts';
        $p_m = $wpdb->prefix.'postmeta';

        // Select max min price with category
        if($product_catg){

            $catgs = 
            Instruments::get_products_ids_by_one_or_more_categories($product_catg);
            //---------------------------------------------
            
            $str_ids = '';
            foreach($catgs as $key => $product){
                
                $str_ids .= $product->product_id.',';
            }
            $str_ids = preg_replace('#[\s]*,[\s]*$#', '', $str_ids);
            //---------------------------------------------

            $sql = '
            SELECT 
                MAX(`price`) AS `max_price`,
                MIN(`price`) AS `min_price`
            FROM 
                (
                    SELECT
                        CAST(`'.$p_m.'`.`meta_value` AS SIGNED) AS `price`
                    FROM `'.$p_m.'` 
                    LEFT JOIN `'.$p.'`
                    ON 
                        `'.$p.'`.`ID` = `'.$p_m.'`.`post_id`
                    WHERE 
                        `'.$p.'`.`ID` IN ('.$str_ids.') AND 
                        `'.$p.'`.`post_status` = "publish" AND 
                        (
                            (`'.$p_m.'`.`meta_key` = "_price") OR 
                            (`'.$p_m.'`.`meta_key` = "_sale_price") 
                        ) AND 
                        `'.$p_m.'`.`meta_value` != ""
                ) AS all_types_prices_of_products
            ';
        }else{

            // CAST(`'.$t2.'`.`meta_value` AS SIGNED) - preobrazovanie strokovogo v chislovoj
            $sql = '
            SELECT 
                MAX(CAST(`'.$p_m.'`.`meta_value` AS SIGNED)) AS `max_price`,
                MIN(CAST(`'.$p_m.'`.`meta_value` AS SIGNED)) AS `min_price`
            FROM `'.$p.'` 
            LEFT JOIN `'.$p_m.'`
            ON 
                `'.$p.'`.`ID` = `'.$p_m.'`.`post_id` 
            WHERE
                (`'.$p.'`.`post_type` = "product") AND 
                `'.$p.'`.`post_status` = "publish" AND 
                (
                    (`'.$p_m.'`.`meta_key` = "_price") OR 
                    (`'.$p_m.'`.`meta_key` = "_sale_price")
                ) AND 
                `'.$p_m.'`.`meta_value` != ""
            ';
        }
        
        $result = $wpdb->get_row($sql);
        if(!$result) return false;

        return $result;
    }

    //-----------------------------------------------------------------------
    /*
    $ids_filter_elements = array(
        'color' => array(1,2...ids),
        'material' => array(1,2...ids)
        ...
    );
    */
    public static function get_products_for_filter(
        $product_catg = 0, $min_price = 0, $max_price = 0, 
        $ids_filter_elements = '', $limit = '', $order = '', $page = '',
        $without_product_select_only_amount = false
    ){

        $lang = false;
        if(function_exists('pll_default_language')){

            $lang = pll_current_language();
        }

        // For multilangual category select
        if(
            $lang && $product_catg > 0
        ){

            // Detected category of enother langs
            $langTermRelation = Instruments::getTermLangRelation($product_catg, $lang);
            //---------------------------------------------

            $product_catg = $langTermRelation[$lang];
            //---------------------------------------------

            $all_product_ids = array();

            $all_product_ids['catg'] = 
            Instruments::get_products_ids_by_one_or_more_categories($product_catg, $lang);
            //---------------------------------------------            
            
            if(is_array($ids_filter_elements) && count($ids_filter_elements) > 0) {

                $all_product_ids = 
                Instruments::ids_filter_elements_cicle_generate($ids_filter_elements, $all_product_ids, $lang);
            }
            //---------------------------------------------
            
            $str_ids = 
            Instruments::filtered_products_ids_by_selected_attrs_for_filter($all_product_ids);
            if(!$str_ids) return 'no products for this filters';
            //---------------------------------------------
            
            if(!$without_product_select_only_amount){

                $result = Instruments::main_select_products_by_ids(
                    $str_ids, $min_price, $max_price, $limit, $order, $page
                );
            }else{

                $result = Instruments::main_amount_products_by_ids(
                    $str_ids, $min_price, $max_price, $limit, $order, $page
                );

                return $result;
            }
            
            if($result === true) return false;
            if(!$result) return array();
        }
        // Without lang by category select
        elseif(
            $product_catg > 0
        ){

            $all_product_ids = array();

            $all_product_ids['catg'] = 
            Instruments::get_products_ids_by_one_or_more_categories($product_catg);
            //---------------------------------------------            
            
            if(is_array($ids_filter_elements) && count($ids_filter_elements) > 0) {

                $all_product_ids = 
                Instruments::ids_filter_elements_cicle_generate($ids_filter_elements, $all_product_ids, $lang);
            }
            //---------------------------------------------
            
            $str_ids = 
            Instruments::filtered_products_ids_by_selected_attrs_for_filter($all_product_ids);
            if(!$str_ids) return 'no products for this filters';
            //---------------------------------------------
            
            if(!$without_product_select_only_amount){

                $result = Instruments::main_select_products_by_ids(
                    $str_ids, $min_price, $max_price, $limit, $order, $page
                );
            }else{

                $result = Instruments::main_amount_products_by_ids(
                    $str_ids, $min_price, $max_price, $limit, $order, $page
                );
                
                return $result;
            }

            if($result === true) return false;
            if(!$result) return array();
        }
        // Without category but with filters
        elseif(
            !$product_catg && 
            is_array($ids_filter_elements) && count($ids_filter_elements) > 0
        ){

            $excluded_products = true;

            $all_product_ids = array();

            $all_product_ids = 
            Instruments::ids_filter_elements_cicle_generate($ids_filter_elements, $all_product_ids, $lang, $excluded_products);
            //---------------------------------------------
            
            $str_ids = 
            Instruments::filtered_products_ids_by_selected_attrs_for_filter($all_product_ids);
            if(!$str_ids) return 'no products for this filters';
            //---------------------------------------------
            
            if(!$without_product_select_only_amount){

                $result = Instruments::main_select_products_by_ids(
                    $str_ids, $min_price, $max_price, $limit, $order, $page
                );
            }else{

                $result = Instruments::main_amount_products_by_ids(
                    $str_ids, $min_price, $max_price, $limit, $order, $page
                );
                
                return $result;
            }

            if($result === true) return false;
            if(!$result) return array();

            // Esli est lang to vubiraem tolko tovaru etoj lang
            if($lang){

                $result = Instruments::products_ids_by_correct_lang($result, $lang);
                if($result === true) return false;
                if(!$result) return array();
            }
        }
        // without category, without filters
        elseif(
            !$product_catg &&
            (!is_array($ids_filter_elements) || count($ids_filter_elements) < 1)
        ){

            if($lang){

                // Esli est yazuk to rabotaem bez limitov potomy chto snachala nado vubrat vse tovaru,
                // otobrat ids tovarov nyzhnogo yazuka i 
                // potom novum zaprosom vuborki tovarov s bd sdelat limit

                // Select all products ids by all langs without limit
                $result = Instruments::main_select_products_without_ids(
                    $min_price, $max_price, /*$limit = */'', $order, /*$page = */0, $lang
                );

                if($result === false) return false;
                if($result == 'error_ex_p_terms') return 'error_ex_p_terms';
                if(!$result) return array();

                // Select all products ids of correct lang
                $result = Instruments::products_ids_by_correct_lang_as_product_id_field($result, $lang, /*$id_field_name = */'id');

                $str_ids = Instruments::convertToStrIdsFromIdsArray($result);

                if($without_product_select_only_amount){
                    
                    $result = Instruments::main_amount_products_by_ids(
                        $str_ids, $min_price, $max_price, $limit, $order, $page
                    );
                    
                    return $result;
                }

                $result = Instruments::main_select_products_by_ids(
                    $str_ids, $min_price, $max_price, $limit, $order, $page
                );
            }else{

                if(!$without_product_select_only_amount){

                    $result = Instruments::main_select_products_without_ids(
                        $min_price, $max_price, $limit, $order, $page, /*$lang=*/''
                    );
                }else{
    
                    $result = Instruments::main_amount_products_without_ids(
                        $min_price, $max_price, $limit, $order, $page, /*$lang=*/''
                    );
                    
                    return $result;
                }
            }

            if($result === false) return false;
            if($result == 'error_ex_p_terms') return 'error_ex_p_terms';
            if(!$result) return array();
        }

        set_amount_products_result_in_session(count($result));
        return $result;
    }

    //-----------------------------------------------------------------------

    public static function ajax_products_adaptation_get(){

        $data = array(
            'status' => 'success',
            'amount' => 0,
            'common_amount_no_limit' => 0,
            'amount_only_one' => 0,
            'successes' => array(
                'message' => TranslatorCenter::run('Success. Filter price products selected.'),
            ),
            'errors' => array(
                'message' => TranslatorCenter::run('Error. Price filter.'),
                'no_results' => TranslatorCenter::run('Nothing found.'),
            ),
            'btn_addition_loading' => TranslatorCenter::run('Load more'),
            'if_click_add_filter' => array(),
            'do_filter_clear' => 0,
            'all_filters_for_del' => array(),
            'pagin_html' => '',
            'poln_kolichestvo' => '',
            'user_login_status' => (int) is_user_logged_in(),
        );
        //-------------------------------


        $request_uri = '';
        if(isset($_POST['request_uri']) && $_POST['request_uri']){

            $request_uri = $_POST['request_uri'];
            $request_uri = preg_replace('#page/[0-9]+/?$#', '', $request_uri);
            $request_uri = preg_replace('#/$#', '', $request_uri);
            $request_uri = preg_replace('#^/#', '', $request_uri);
        }
        //-------------------------------

        $query_string = '';
        if(isset($_POST['query_string']) && $_POST['query_string']){

            $query_string = urldecode($_POST['query_string']);
            $query_string = preg_replace('#^(\?|\&)#', '', $query_string);
            $query_string = '?'.$query_string;
        }
        //-------------------------------

        $hash_string = '';
        if(isset($_POST['hash_string']) && $_POST['hash_string']){

            $hash_string = $_POST['hash_string'];
            $hash_string = preg_replace('!^#!', '', $hash_string);
            $hash_string = '#'.$hash_string;
        }
        //-------------------------------

        

        if(
            !isset($_POST['category_id_agb']) || 
            !is_numeric($_POST['category_id_agb'])
        ){

            $category_id_agb = 0;
        }else{

            $category_id_agb = $_POST['category_id_agb'];
        }
        //-------------------------------

        if(
            isset($_POST['clear_filters_agb']) || 
            isset($_POST['clean_all_filters_agb']) 
        ){
            
            foreach($_SESSION['products_filters_agb']['filter'] as $attrs_grp => $ids_arr){

                foreach($ids_arr as $att_key => $one_id){
                    
                    $a_m_m = Instruments::get_products_amount_by_term_id($category_id_agb, $one_id);
                    $data['all_filters_for_del'][$one_id] = array(
                        'id' => $one_id, 
                        'amount' => ($a_m_m) ? $a_m_m : 0,
                    );
                }
            }

            if(isset($_SESSION['products_filters_agb']['filter']))
            $_SESSION['products_filters_agb']['filter'] = array();

            if(isset($_SESSION['products_filters_agb']['price']))
            $_SESSION['products_filters_agb']['price'] = array();
        }
        //-------------------------------

        $lang = false;
        if(function_exists('pll_default_language')){

            $lang = pll_current_language();
        }
        //-------------------------------

        if(
            isset($_POST['one_filter_agb']) && 
            $_POST['one_filter_agb'] && 
            is_numeric($_POST['one_filter_agb']))
        {

            $short = &$_SESSION['products_filters_agb']['filter'];
            foreach($short as $k1 => $filters_set){

                foreach($filters_set as $k2 => $fid){

                    $all_fids = array();
                    // If is some of this functions
                    if(
                        function_exists('pll_default_language') || 
                        function_exists('pll_current_language')
                    ) {
                        //----------------------------------------------
                        // Find all filters ids with lang if multilang is working
                        //----------------------------------------------

                        //$curLang = pll_current_language();
                        $lang_term_relation = Instruments::getTermLangRelation($fid);

                        if($lang_term_relation && count($lang_term_relation) > 0){

                            foreach($lang_term_relation as $kl => $lfid){

                                $all_fids[] = $lfid;
                            }
                        }else{

                            $all_fids[] = $fid;
                        }
                    }else{

                        $all_fids[] = $fid;
                    }
                    
                    
                    foreach($all_fids as $akey => $fid){

                        if($_POST['one_filter_agb'] == $fid){

                            // Get correct amount of one filter what was deactivated
                            $results = '';
                            if(isset($_POST['slug_attrs_group']) && $_POST['slug_attrs_group']){
    
                                $results = Instruments::get_products_for_filter(
                                    $category_id_agb, 
                                    /*$min_price_agb = */0, /*$max_price_agb = */0, 
                                    /*$filters = */array(
                                        $_POST['slug_attrs_group'] => array($_POST['one_filter_agb']),
                                    ),
                                    /*$limit = */false,
                                    /*$order = */false,
                                    /*$page = */0,
                                );
                            }
    
                            $data['amount_only_one'] = (is_array($results)) ? count($results) : 0;
    
                            $data['if_click_add_filter']['term_id'] = $_POST['one_filter_agb'];
                            unset($short[$k1][$k2]);
                        }
                    }
                }                
            }
        }
        //-------------------------------

        $session_filters = get_filters_from_session_agb();

        if(
            !isset($_POST['min_price_agb']) || !isset($_POST['max_price_agb']) || 
            !is_numeric($_POST['min_price_agb']) || !is_numeric($_POST['max_price_agb']) || 
            isset($_POST['clean_price_agb']) || 
            isset($_POST['clean_all_filters_agb'])
        ){
            if(
                isset($session_filters['price']) && 
                is_numeric($session_filters['price']['min']) &&
                is_numeric($session_filters['price']['max']) && 
                !isset($_POST['clean_price_agb']) && 
                !isset($_POST['clean_all_filters_agb'])
            ){

                $min_price_agb = $session_filters['price']['min'];
                $max_price_agb = $session_filters['price']['max'];
            }else{

                $min_price_agb = 0; $max_price_agb = 0;
            }
        }else{

            $min_price_agb = $_POST['min_price_agb']; $max_price_agb = $_POST['max_price_agb'];
        }
        //-------------------------------

        if(
            isset($_POST['order_by_name']) && $_POST['order_by_name'] != '' && 
            isset($_POST['order_by_method']) && $_POST['order_by_method'] != ''
        ){

            $order = array(
                'order_name' => $_POST['order_by_name'],
                'order_method' => $_POST['order_by_method'],
            );
        }else{

            if(
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
            }else{

                $order = '';
            }
        }
        //-------------------------------

        if(
            isset($_POST['limit_per_page']) && $_POST['limit_per_page']
        ){

            $limit = $_POST['limit_per_page'];
        }else{

            if(
                isset($session_filters['limit']) && 
                $session_filters['limit']
            ){

                $limit = $session_filters['limit'];
            }else{

                $limit = 12;
            }
        }
        //-------------------------------

        if(
            isset($_POST['page_num_agb']) && is_numeric($_POST['page_num_agb']) && 
            !isset($_POST['clean_all_filters_agb'])
        ){

            $page = $_POST['page_num_agb'];
        }else{

            if(
                isset($session_filters['page']) && 
                $session_filters['page'] && 
                !isset($_POST['clean_all_filters_agb'])
            ){

                $page = $session_filters['page'];
            }else{

                $page = 0;
            }
        }
        //-------------------------------

        if(
            !isset($_POST['slug_attrs_group']) || !isset($_POST['attr_term_id']) || 
            !$_POST['slug_attrs_group'] || !is_numeric($_POST['attr_term_id']) || 
            isset($_POST['clean_all_filters_agb'])
        ){
            
            if(
                isset($session_filters['filter']) && 
                is_array($session_filters['filter']) && 
                count($session_filters['filter']) > 0 &&
                !isset($_POST['clean_all_filters_agb'])
            ){
    
                $ids_filter_elements = $session_filters['filter'];
            }else{
    
                $ids_filter_elements = array();
            }
        }else{

            if(
                isset($session_filters['filter']) && 
                is_array($session_filters['filter'])
            ){
    
                $ids_filter_elements = $session_filters['filter'];
                if(
                    isset($ids_filter_elements[$_POST['slug_attrs_group']]) && 
                    is_array($ids_filter_elements[$_POST['slug_attrs_group']])
                ){
                    $nofind = true;

                    if($lang){

                        $langRelation = Instruments::getTermLangRelation($_POST['attr_term_id'], $lang);
                        foreach($ids_filter_elements[$_POST['slug_attrs_group']] as $ee => $vv){

                            // Ishchem vse sovpadeniya po vsem yazukovum versiyam
                            foreach($langRelation as $lang_key => $L_term_id){

                                if($vv == $L_term_id){

                                    // Esli est takoj element to ydalit ego
                                    // potopmy kak povtornoe nazhatie na vubrannuj filter v filtre 
                                    // to delaem ydalenie filtra
                                    unset($ids_filter_elements[$_POST['slug_attrs_group']][$ee]);
                                    $nofind = false; 
                                    break;
                                }

                                //break; - zablokirovano tak kak pri etom mozhna edalyat neskolko povtoryajyshchihsya elementov filtra
                            }
                        }
                    }else{

                        foreach($ids_filter_elements[$_POST['slug_attrs_group']] as $ee => $vv){

                            if($vv == $_POST['attr_term_id']){

                                // Esli est takoj element to ydalit ego
                                // potopmy kak povtornoe nazhatie na vubrannuj filter v filtre 
                                // to delaem ydalenie filtra
                                unset($ids_filter_elements[$_POST['slug_attrs_group']][$ee]);
                                $nofind = false; 
                                //break; - zablokirovano tak kak pri etom mozhna edalyat neskolko povtoryajyshchihsya elementov filtra
                            }
                        }
                    }

                    // Esli net v massive etogo elementa to dobavit v massiv
                    if($nofind){

                        $data['do_filter_clear'] = 0;
                        $ids_filter_elements[$_POST['slug_attrs_group']][] = (int) $_POST['attr_term_id'];
                    }else{
                        // Esli naiden filter to otpravit status bul ydalen

                        $data['do_filter_clear'] = 1;
                    }
                }else{

                    $ids_filter_elements = array();
                    $ids_filter_elements[$_POST['slug_attrs_group']] = array();
                    $ids_filter_elements[$_POST['slug_attrs_group']][] = $_POST['attr_term_id'];
                }

                // For return to js
                $att_fltr = Instruments::get_term_by_id_simple($_POST['attr_term_id']);
                $data['if_click_add_filter']['slug'] = $_POST['slug_attrs_group'];
                $data['if_click_add_filter']['term_id'] = $_POST['attr_term_id'];
                $data['if_click_add_filter']['name'] = $att_fltr->name;

                // Get correct amount of one filter what was activated
                $results = Instruments::get_products_for_filter(
                    $category_id_agb, 
                    $min_price_agb, $max_price_agb, 
                    /*$filters = */array(
                        $_POST['slug_attrs_group'] => array($_POST['attr_term_id']),
                    ),
                    /*$limit = */false,
                    /*$order = */false,
                    /*$page = */0,
                );

                $data['amount_only_one'] = (is_array($results)) ? count($results) : 0;
            }else{
    
                $ids_filter_elements = array();
            }
        }
        //-------------------------------

        $price = array(
            'min' => $min_price_agb,
            'max' => $max_price_agb,
        );

        // Set to common filters
        set_filters_in_session_agb($ids_filter_elements, $price, $limit, $order, $page, $category_id_agb);
        //-------------------------------

        // Re-get filters elements 
        $session_filters = get_filters_from_session_agb();
        //-------------------------------

        
        //-------------------------------
        $filters_cash_file_name = Instruments::cache_initialize_work( $session_filters, $lang );
        $filters_cash_file_name__ammmm = $filters_cash_file_name.'_ammmm';
        $filters_cash_file_name__pagin = $filters_cash_file_name.'_pagin';
        //-------------------------------



        $results = true;
        // START. TEPER ZAGRYZHAT TOVARU TOLKO POSLE NAZHATIYA NA KNOPKY PRIMENIT VSE FILTRU
        if(
            isset($_POST['all_filters_apply']) || 
            isset($_POST['addition_loading_agb']) || 
            isset($_POST['clean_all_filters_agb']) || 
            isset($_POST['clear_filters_agb']) || 
            isset($_POST['one_filter_agb']) || 
            isset($_POST['order_by_name'])
        ){

            $cash_file_status_agb = Instruments::cash_file_status($filters_cash_file_name);            

            clearstatcache();
            if( $cash_file_status_agb == 'cash_file_no_exists' ){

                // Get products correct ammount without limit
                $ammmm = Instruments::get_products_for_filter(
                    $category_id_agb, 
                    $min_price_agb, $max_price_agb, 
                    $session_filters['filter'],
                    /*$limit = */false,
                    $order,
                    $page,
                    /*$without_product_select_only_amount = */true
                );        

                $data['common_amount_no_limit'] = $ammmm;
                //-------------------------------

                $results = Instruments::get_products_for_filter(
                    $category_id_agb, 
                    $min_price_agb, $max_price_agb, 
                    $session_filters['filter'],
                    $limit,
                    $order,
                    $page
                );
                //-------------------------------

                //-------------------------------
                //-------------------------------
                $pages_amount = ceil( $ammmm / $limit );
                wc_set_loop_prop( 'total_pages',  $pages_amount);
                wc_set_loop_prop( 'current_page', $page+1 );
        
                
                global $base_my_agb, $total_my_agb, $total_results_agb, $per_page_limit_agb;
        
                $base_my_agb = 
                reroutingOnRightLink_plg().
                $_SERVER['SERVER_NAME'].
                '/'.$request_uri.'/page/%#%/'.$query_string.$hash_string;

                $total_my_agb = $pages_amount;
        
                ob_start();
                get_template_part( 'woocommerce/loop/pagination' );
                $pagination_htnl = ob_get_clean();
                $data['pagin_html'] = $pagination_htnl;

                $per_page_limit_agb = $limit;
                $total_results_agb = $data['common_amount_no_limit'];

                ob_start();
                get_template_part( 'woocommerce/loop/result-count' );
                $poln_kolichestvo_html = ob_get_clean();
                $data['poln_kolichestvo'] = $poln_kolichestvo_html;
                //-------------------------------
                //-------------------------------


                if($results === 'no products for this filters'){

                    Instruments::write_in_cash_file(
                        $filters_cash_file_name, 
                        $filters_cash_file_name__ammmm, 
                        $filters_cash_file_name__pagin, 
                        array(), $data['pagin_html'], 0
                    );

                    $data['status'] = 'error';
                    $data['errors']['message'] = TranslatorCenter::run('No results found by this query.');
                    echo ControlCenter::jsonEncode($data);
                    die();
                }

                if($results == 'error_ex_p_terms'){

                    Instruments::write_in_cash_file(
                        $filters_cash_file_name, 
                        $filters_cash_file_name__ammmm, 
                        $filters_cash_file_name__pagin, 
                        array(), $data['pagin_html'], 0
                    );

                    $data['status'] = 'error';
                    $data['errors']['message'] = TranslatorCenter::run('In excluded products.');
                    echo ControlCenter::jsonEncode($data);
                    die();
                }
                
                if($results === false){
            
                    Instruments::write_in_cash_file(
                        $filters_cash_file_name, 
                        $filters_cash_file_name__ammmm, 
                        $filters_cash_file_name__pagin, 
                        array(), $data['pagin_html'], 0
                    );

                    $data['status'] = 'error';
                    echo ControlCenter::jsonEncode($data);
                    die();
                }
        
                $amount = count($results);
                if($amount < 1){

                    Instruments::write_in_cash_file(
                        $filters_cash_file_name, 
                        $filters_cash_file_name__ammmm, 
                        $filters_cash_file_name__pagin, 
                        array(), $data['pagin_html'], 0
                    );
        
                    $data['status'] = 'error';
                    $data['errors']['message'] = TranslatorCenter::run('No results found by this query.');
                    echo ControlCenter::jsonEncode($data);
                    die();
                }
                
                set_amount_products_result_in_session($amount);
                $data['amount'] = $amount;
        
                $results = Instruments::all_products_addition_data_get($results);
                //-------------------------------

                Instruments::write_in_cash_file(
                    $filters_cash_file_name, 
                    $filters_cash_file_name__ammmm, 
                    $filters_cash_file_name__pagin, 
                    $results, $data['pagin_html'], $ammmm
                );

            }else{

                clearstatcache();
                if( is_file(PATH_CURRENT_PLUGIN_PF_AGB.'/cache/'.$filters_cash_file_name) ){

                    clearstatcache();
                    $results = file_get_contents(PATH_CURRENT_PLUGIN_PF_AGB.'/cache/'.$filters_cash_file_name);
                    $results = unserialize($results);

                    $amount = count($results);
                    set_amount_products_result_in_session($amount);
                    $data['amount'] = $amount;
                }

                clearstatcache();
                if( is_file(PATH_CURRENT_PLUGIN_PF_AGB.'/cache/'.$filters_cash_file_name__ammmm) ){

                    clearstatcache();
                    $ammmm = (int) trim(file_get_contents(PATH_CURRENT_PLUGIN_PF_AGB.'/cache/'.$filters_cash_file_name__ammmm));

                    $pages_amount = ceil( $ammmm / $limit );

                    $data['common_amount_no_limit'] = $ammmm;
                }else{

                    $pages_amount = 1;
                    wc_set_loop_prop( 'total_pages',  $pages_amount);
                    wc_set_loop_prop( 'current_page', $page+1 );

                    $data['common_amount_no_limit'] = 0;
                }

                global $base_my_agb, $total_my_agb, $total_results_agb, $per_page_limit_agb;

                $base_my_agb = 
                reroutingOnRightLink_plg().
                $_SERVER['SERVER_NAME'].
                '/'.$request_uri.'/page/%#%/'.$query_string.$hash_string;

                $total_my_agb = $pages_amount;

                clearstatcache();
                if( is_file(PATH_CURRENT_PLUGIN_PF_AGB.'/cache/'.$filters_cash_file_name__pagin) ){

                    clearstatcache();
                    $data['pagin_html'] = file_get_contents(PATH_CURRENT_PLUGIN_PF_AGB.'/cache/'.$filters_cash_file_name__pagin);
                }else{

                    wc_set_loop_prop( 'total_pages',  $pages_amount);
                    wc_set_loop_prop( 'current_page', $page+1 );

                    ob_start();
                    get_template_part( 'woocommerce/loop/pagination' );
                    $pagination_htnl = ob_get_clean();
                    $data['pagin_html'] = $pagination_htnl;
                }

                $per_page_limit_agb = $limit;
                $total_results_agb = $data['common_amount_no_limit'];

                ob_start();
                get_template_part( 'woocommerce/loop/result-count' );
                $poln_kolichestvo_html = ob_get_clean();
                $data['poln_kolichestvo'] = $poln_kolichestvo_html;
            }
        }
        // END. TEPER ZAGRYZHAT TOVARU TOLKO POSLE NAZHATIYA NA KNOPKY PRIMENIT VSE FILTRU



        if($results === false){
    
            $data['status'] = 'error';
            echo ControlCenter::jsonEncode($data);
            die();
        }

        $data['products'] = $results;
        echo ControlCenter::jsonEncode($data);
        die();
    }

    //-----------------------------------------------------------------------


    public static function write_in_cash_file(
        $filters_cash_file_name, 
        $filters_cash_file_name__ammmm, 
        $filters_cash_file_name__pagin, 
        $results, $pagin_html, $ammmm
    ){

        $str_results = serialize($results);
        clearstatcache();
        file_put_contents(PATH_CURRENT_PLUGIN_PF_AGB.'/cache/'.$filters_cash_file_name, $str_results, LOCK_EX);

        clearstatcache();
        file_put_contents(PATH_CURRENT_PLUGIN_PF_AGB.'/cache/'.$filters_cash_file_name__ammmm, $ammmm, LOCK_EX);

        clearstatcache();
        file_put_contents(PATH_CURRENT_PLUGIN_PF_AGB.'/cache/'.$filters_cash_file_name__pagin, $pagin_html, LOCK_EX);
    }

    //-----------------------------------------------------------------------

    public static function cache_initialize_work($session_filters, $lang){

        $filters_cash_file_name = '';
        if($lang){

            $filters_cash_file_name .= $lang;
        }
        // Create file of filters cash of products set
        if( ! $session_filters['filter'] || count($session_filters['filter']) < 1 ){

            $filters_cash_file_name .= 'f';
        }else{

            foreach($session_filters['filter'] as $fckey => $fcval){

                foreach($fcval as $fcval_2){

                    $filters_cash_file_name .= $fcval_2;
                }
            }
        }

        if( ! $session_filters['category_id_agb'] ){

            $filters_cash_file_name .= 'c';
        }else{

            $filters_cash_file_name .= $session_filters['category_id_agb'];
        }

        if( ! $session_filters['price'] ){

            $filters_cash_file_name .= 'p';
        }else{

            $filters_cash_file_name .= 
            $session_filters['price']['min'].
            $session_filters['price']['max'];
        }

        if( ! $session_filters['limit'] ){

            $filters_cash_file_name .= 'L';
        }else{

            $filters_cash_file_name .= $session_filters['limit'];
        }

        if( ! $session_filters['page'] ){

            $filters_cash_file_name .= 'pg';
        }else{

            $filters_cash_file_name .= $session_filters['page'];
        }

        if( ! $session_filters['order_by'] || count($session_filters['order_by']) < 1 ){

            $filters_cash_file_name .= 'ob';
        }else{

            foreach($session_filters['order_by'] as $fckey => $fcval){

                $filters_cash_file_name .= $fcval;
            }
        }
        
        // Pri nazhatii na element sortirovki imenno v etot moment ne idet novaya podgryzka
        // i dlya etogo nyzhno novoe imya faila kesha, inache posle nazhatiya na 
        // knopky "zagryzit eshche" bydet vubran fail iz kesha, tak kak on sovpadet po imeni 
        // i tak kak bula prosto sortirovka ranee, to vugryzka podgryzki bydet ne vernoj.
        if(isset($_POST['order_elem_click_now'])){

            $filters_cash_file_name .= 's';
        }

        // Tolko v moment nazhatiya na elemnt atributa filtra dlya togo chto i verhnee,
        // chto bu ne bulo sovpadeniya imeni kesh faila.
        if(isset($_POST['one_on_filter_elem_click'])){

            $filters_cash_file_name .= 'o';
        }

        // V moment nazhatiya na knopky "primenit" nizhnyy
        if(isset($_POST['all_filters_apply'])){

            $filters_cash_file_name .= 'a';
        }
        

        clearstatcache();
        if( ! is_dir(PATH_CURRENT_PLUGIN_PF_AGB.'/cache') ) mkdir(PATH_CURRENT_PLUGIN_PF_AGB.'/cache');

        $time_cash_file_name_agb = PATH_CURRENT_PLUGIN_PF_AGB.'/cache/time';
        $ttime = time();
        clearstatcache();
        if( 
            ! is_file($time_cash_file_name_agb) 
        ){

            clearstatcache();
            file_put_contents($time_cash_file_name_agb, $ttime, LOCK_EX);
        }elseif(
            ($ttime - file_get_contents($time_cash_file_name_agb)) > (24 * 60 * 60 * 7)
        ){

            clearstatcache();

            $data_array = glob(PATH_CURRENT_PLUGIN_PF_AGB.'/cache/*', GLOB_BRACE);
            $i = 0;
            foreach($data_array as $e){
                
                clearstatcache();
                unlink($e);
            }

            file_put_contents($time_cash_file_name_agb, $ttime, LOCK_EX);
        }

        return $filters_cash_file_name;
    }

    //-----------------------------------------------------------------------

    public static function cash_file_status($filters_cash_file_name){

        $cash_file_status_agb = 'cash_file_no_exists';
        clearstatcache();
        if( ! is_file(PATH_CURRENT_PLUGIN_PF_AGB.'/cache/'.$filters_cash_file_name) ){

            $cash_file_status_agb = 'cash_file_no_exists';
        }elseif( is_file(PATH_CURRENT_PLUGIN_PF_AGB.'/cache/'.$filters_cash_file_name) ){

            clearstatcache();
            $cash_data = file_get_contents(PATH_CURRENT_PLUGIN_PF_AGB.'/cache/'.$filters_cash_file_name);
            $cash_data = unserialize($cash_data);

            if( is_array($cash_data) ){

                $cash_file_status_agb = 'cash_file_exists_and_is_array';
            }else{

                clearstatcache();
                unlink(PATH_CURRENT_PLUGIN_PF_AGB.'/cache/'.$filters_cash_file_name);
                $cash_file_status_agb = 'cash_file_no_exists';
            }
        }

        return $cash_file_status_agb;
    }

    //-----------------------------------------------------------------------

    public static function get_products_ids_by_one_or_more_categories($product_catg, $lang = ''){

        global $wpdb;

        $t_r = $wpdb->prefix.'term_relationships';
        $t_t = $wpdb->prefix.'term_taxonomy';
        $t = $wpdb->prefix.'terms';

        // Vubiraem term kotoruj otvechaet za otobrazhenie tovara v kataloge
        // on ne imeet privyazki k yazuky
        $sql = '
        SELECT 
            `term_id`
        FROM `'.$t.'`
        WHERE
            `name` = "exclude-from-catalog"
        ';

        $term_id = $wpdb->get_row($sql);
        if(!$term_id) return false;

        $term_id = $term_id->term_id;
        //---------------------------------------------

        // Vubiraem otklychennue iz pokaza v kataloge tovaru
        $sql = '
        SELECT 
            `'.$t_r.'`.`object_id` AS `product_id`
        FROM `'.$t_r.'`
        WHERE
            `'.$t_r.'`.`term_taxonomy_id` IN (
                SELECT `term_taxonomy_id` AS `tt_id` 
                FROM `'.$t_t.'` `t_t2`, `'.$t.'` `t2` 
                WHERE 
                    `t2`.`term_id` = `t_t2`.`term_id` AND 
                    `t2`.`term_id` IN ('.$term_id.')
            )
        ';
        
        $excluded_ids = $wpdb->get_results($sql);
        // Teper vubiraem polnostiy vse ex tovaru po vsem yazukam, esli odnomy zadan 
        // exclude to i dtygoj tovar drygogo yazuka tozhe dolzhen imet isklychenie s pokaza
        $excluded_ids = Instruments::products_ids_of_all_langs($excluded_ids, $lang);

        if($excluded_ids === false) return false;
        //---------------------------------------------

        $str_ex_ids = '';
        foreach($excluded_ids as $key => $product){
            
            $str_ex_ids .= $product->product_id.',';
        }
        //$str_ex_ids = preg_replace('#[\s]*,[\s]*$#', '', $str_ex_ids);
        $str_ex_ids = ','.$str_ex_ids;
        //---------------------------------------------
        unset($excluded_ids);

        $sql = '
        SELECT 
            `'.$t_r.'`.`object_id` AS `product_id`
        FROM `'.$t_r.'`
        WHERE
            `'.$t_r.'`.`term_taxonomy_id` IN (
                SELECT `term_taxonomy_id` AS `tt_id` 
                FROM `'.$t_t.'` `t_t2`, `'.$t.'` `t2` 
                WHERE 
                    `t2`.`term_id` = `t_t2`.`term_id` AND 
                    `t2`.`term_id` IN ('.$product_catg.')
            )
        ';
        
        $pids = $wpdb->get_results($sql);
        if(!$pids) return false;
        //---------------------------------------------

        foreach($pids as $key => $product){
            
            if(preg_match('#,'.$product->product_id.',#', $str_ex_ids)){

                unset($pids[$key]);
            }
        }
        //---------------------------------------------

        return $pids;
    }

    //-----------------------------------------------------------------------

    /*
    Select by AND or OR oparator for filter
    Example param 
    $all_terms_ids = 
    'AND (term_id = 1 OR term_id = 2 OR ...)
    AND (term_id = 100 OR term_id = 200 OR ...)';
    ... etc.
    */
    public static function get_products_ids_by_terms_ids($all_terms_ids){

        global $wpdb;

        $t_r = $wpdb->prefix.'term_relationships';
        $t_t = $wpdb->prefix.'term_taxonomy';
        $t = $wpdb->prefix.'terms';

        $sql = '
        SELECT 
            `'.$t_r.'`.`object_id` AS `product_id`
        FROM `'.$t_r.'`
        WHERE
            `'.$t_r.'`.`term_taxonomy_id` IN (
                SELECT `term_taxonomy_id` AS `tt_id` 
                FROM `'.$t_t.'` `t_t2`, `'.$t.'` `t2`
                WHERE 
                    `t2`.`term_id` = `t_t2`.`term_id`  
                    '.$all_terms_ids.'
            )
        ';

        $pids = $wpdb->get_results($sql);
        if($pids === false) return false;
        if(!$pids) return array();

        return $pids;
    }

    //-----------------------------------------------------------------------

    public static function get_products_amount_by_term_id($cat_id = 0, $attr_id){

        global $wpdb;

        $t_r = $wpdb->prefix.'term_relationships';
        $t_t = $wpdb->prefix.'term_taxonomy';
        $t = $wpdb->prefix.'terms';

        $cat_ids = false;
        if($cat_id){

            $sql = '
            SELECT 
                `'.$t_r.'`.`object_id` AS `product_id`
            FROM `'.$t_r.'`
            WHERE
                `'.$t_r.'`.`term_taxonomy_id` IN (
                    SELECT `term_taxonomy_id` AS `tt_id` 
                    FROM `'.$t_t.'` `t_t2`, `'.$t.'` `t2`
                    WHERE 
                        `t2`.`term_id` = `t_t2`.`term_id` AND 
                        `t2`.`term_id` = '.$cat_id.'
                )
            ';

            $cat_ids = $wpdb->get_results($sql);
        }
        //------------------

        $sql = '
        SELECT 
            `'.$t_r.'`.`object_id` AS `product_id`
        FROM `'.$t_r.'`
        WHERE
            `'.$t_r.'`.`term_taxonomy_id` IN (
                SELECT `term_taxonomy_id` AS `tt_id` 
                FROM `'.$t_t.'` `t_t2`, `'.$t.'` `t2`
                WHERE 
                    `t2`.`term_id` = `t_t2`.`term_id` AND 
                    `t2`.`term_id` = '.$attr_id.'
            )
        ';

        $attr_ids = $wpdb->get_results($sql);
        //------------------

        if($cat_id){

            if(!$cat_ids || !$attr_ids) return 0;

            $matched_ids = array();
            foreach($cat_ids as $key => $val){

                foreach($attr_ids as $key2 => $val2){

                    if($val->product_id == $val2->product_id) {
                        
                        $matched_ids[] = $val->product_id;
                        break;
                    }
                }
            }

            return count($matched_ids);
        }else{

            if(!$attr_ids) return 0;
            else
            return count($attr_ids);
        }
    }

    //-----------------------------------------------------------------------

    public static function ids_filter_elements_cicle_generate(
        $ids_filter_elements, $all_product_ids, $lang = false, $excluded_products = false
    ){

        global $wpdb;

        $all_terms_ids = '';
        foreach($ids_filter_elements as $key => $type_filter){
                    
            $all_terms_ids .= ' AND (';
            foreach($type_filter as $key2 => $filter_val){

                $all_terms_ids .= '`t2`.`term_id` = '.$filter_val.' OR ';
            }
            $all_terms_ids = preg_replace('#[\s]*OR[\s]*$#', ')', $all_terms_ids);
            //------

            $all_product_ids[$key] = 
            Instruments::get_products_ids_by_terms_ids($all_terms_ids);

            if($lang){

                $all_product_ids[$key] = 
                Instruments::products_ids_by_correct_lang_as_product_id_field(
                    $all_product_ids[$key], $lang
                );
            }

            $all_terms_ids = '';
        }


        if($excluded_products === true){
            
            $t_r = $wpdb->prefix.'term_relationships';
            $t_t = $wpdb->prefix.'term_taxonomy';
            $t = $wpdb->prefix.'terms';

            // Vubiraem term kotoruj otvechaet za otobrazhenie tovara v kataloge
            // on ne imeet privyazki k yazuky
            $sql = '
            SELECT 
                `term_id`
            FROM `'.$t.'`
            WHERE
                `name` = "exclude-from-catalog"
            ';

            $term_id = $wpdb->get_row($sql);
            if(!$term_id) return false;

            $term_id = $term_id->term_id;
            //---------------------------------------------

            // Vubiraem otklychennue iz pokaza v kataloge tovaru
            $sql = '
            SELECT 
                `'.$t_r.'`.`object_id` AS `product_id`
            FROM `'.$t_r.'`
            WHERE
                `'.$t_r.'`.`term_taxonomy_id` IN (
                    SELECT `term_taxonomy_id` AS `tt_id` 
                    FROM `'.$t_t.'` `t_t2`, `'.$t.'` `t2` 
                    WHERE 
                        `t2`.`term_id` = `t_t2`.`term_id` AND 
                        `t2`.`term_id` IN ('.$term_id.')
                )
            ';
            
            $excluded_ids = $wpdb->get_results($sql);
            // Teper vubiraem polnostiy vse ex tovaru po vsem yazukam, esli odnomy zadan 
            // exclude to i dtygoj tovar drygogo yazuka tozhe dolzhen imet isklychenie s pokaza
            $excluded_ids = Instruments::products_ids_of_all_langs($excluded_ids, $lang);
            if( ! is_array($excluded_ids) || $excluded_ids === false ) {
            
                return false;
            }
            // Esli net rezyltatov to nichto ne isklycheno i dalee vuvestu vse tovaru
            elseif( is_array($excluded_ids) && count($excluded_ids) > 0 ){

                $str_ex_ids = '';
                foreach($excluded_ids as $key => $product){
                    
                    $str_ex_ids .= $product->product_id.',';
                }
                //$str_ex_ids = preg_replace('#[\s]*,[\s]*$#', '', $str_ex_ids);
                $str_ex_ids = ','.$str_ex_ids;
                //---------------------------------------------
                unset($excluded_ids);
                //---------------------------------------------

                foreach($all_product_ids as $fkey => $products_ids){

                    foreach($products_ids as $key => $product){
                
                        if(preg_match('#,'.$product->product_id.',#', $str_ex_ids)){
            
                            unset($all_product_ids[$fkey][$key]);
                        }
                    }
                }
                //---------------------------------------------
            }
        }

        return $all_product_ids;
    }

    //-----------------------------------------------------------------------

    public static function filtered_products_ids_by_selected_attrs_for_filter($all_product_ids){

        // Ishchem kakoj massiv filtra tovarov samuj bolshij i zapominaem
        $countElems = 0;
        $save_filter_key = '';
        foreach($all_product_ids as $key => $terms_attrs){

            $count = count($terms_attrs);
            if($count > $countElems) {
                
                $countElems = $count;
                $save_filter_key = $key;
            }
        }
        //------------------------------

        // Otbiraem sovpavshie ids
        $str_ids = ''; $pre_save = array(); $now_save = array();
        foreach($all_product_ids as $key => $pids_arr){

            // Skip filter key of more counted, for no repeate in cicle match
            if($key == $save_filter_key && count($all_product_ids) > 1) continue;

            //------------------------------
            // Change places of arrays of ids by which has more count of elements
            $ccc = count($pre_save);
            if(count($pids_arr) > $ccc && $ccc > 0){

                $move = $pids_arr;
                $pids_arr = $pre_save;
                $pre_save = $move;
            }
            //------------------------------

            // Filtering for to delete ids of no matched by selected filters
            foreach($pids_arr as $k => $terms_attrs){

                if(!$pre_save) $pre_save = $all_product_ids[$save_filter_key];
                foreach($pre_save as $key2 => $terms_attrs2){
                    
                    if($terms_attrs->product_id == $terms_attrs2->product_id){
                        
                        $now_save[] = $terms_attrs;
                        break;
                    }
                }
            }
            $pre_save = $now_save;
            $now_save = array();
        }

        //--------------------------------
        $str_ids = '';
        foreach($pre_save as $key => $product){
            
            $str_ids .= $product->product_id.',';
        }
        $str_ids = preg_replace('#[\s]*,[\s]*$#', '', $str_ids);
        //--------------------------------

        return $str_ids;
    }

    //-----------------------------------------------------------------------

    public static function determine_order_method($order){

        if($order['order_name'] == 'price-desc') $order['order_name'] = 'price';
        elseif($order['order_name'] == 'popularity') $order['order_name'] = 'total_sales';
        elseif($order['order_name'] == 'date') $order['order_name'] = 'post_date';

        return $order;
    }

    //-----------------------------------------------------------------------

    public static function set_order_in_compact_function_agb($order){

        $order = Instruments::determine_order_method($order);

        if($order['order_name'] == 'price' && $order['order_method'] == 'ASC'){

            $order_by = 'ORDER BY `min_price` ASC';
            $order_by .= ', `id` '.$order['order_method'].' ';
        }elseif($order['order_name'] == 'price' && $order['order_method'] == 'DESC'){

            $order_by = 'ORDER BY `max_price` DESC';
            $order_by .= ', `id` '.$order['order_method'].' ';
        }elseif($order['order_name'] == 'total_sales'){

            $order_by = 'ORDER BY `'.$order['order_name'].'` '.$order['order_method'];
            $order_by .= ', `id` '.$order['order_method'].' ';
        }elseif($order['order_name'] == 'post_date'){

            $order_by = 'ORDER BY `'.$order['order_name'].'` '.$order['order_method'];
            $order_by .= ', `id` '.$order['order_method'].' ';
        }elseif($order['order_name'] == 'rating'){

            $order_by = 
            'ORDER BY `'.$order['order_name'].'` '.$order['order_method'].', '.
            '`rating_count` '.$order['order_method'];
            $order_by .= ', `id` '.$order['order_method'].' ';
        }elseif($order['order_name'] == 'menu_order'){

            /*
            $order_by = 
            'ORDER BY `'.$order['order_name'].'` '.$order['order_method'].', '.
            '`title` '.$order['order_method'];
            */

            // Esli "menu_order" to poka vmesto "menu_order" stavim "post_date"
            $order['order_name'] = 'post_date';
            //---------------------------------

            $order_by = 'ORDER BY `'.$order['order_name'].'` '.$order['order_method'];
            $order_by .= ', `id` '.$order['order_method'].' ';
        }else{

            //$order_by = 'ORDER BY `menu_order` ASC, `title` ASC';

            // Poka vmesto "menu_order" stavim "post_date"
            $order_by = 'ORDER BY `post_date` DESC, `id` DESC';
        }

        return $order_by;
    }

    //-----------------------------------------------------------------------

    public static function main_select_products_by_ids(
        $str_ids, $min_price = 0, $max_price = 0, $limit = '', $order = '', $page = ''
    ){

        global $wpdb;
    
        $p = $wpdb->prefix.'posts';
        $p_m = $wpdb->prefix.'postmeta';
        $wc_pml = $wpdb->prefix.'wc_product_meta_lookup';

        // Eto sinhronizirovano s WC v faile "plugins/woocommerce/includes/class-wc-query.php" 
        // v fynkcie "get_catalog_ordering_args"
        if($order){

            $order_by = Instruments::set_order_in_compact_function_agb($order);
        }else{

            //$order_by = 'ORDER BY `menu_order` ASC, `title` ASC';

            // Poka vmesto "menu_order" stavim "post_date"
            $order_by = 'ORDER BY `post_date` DESC, `id` DESC';
        }

        $page = ($page) ? $page : 0;

        // Dolzhen but pystum esli vuborka ne cherez ajax 
        // chto bu limit rabotal cherez wp_query pravilno
        $limit_sql = '';
        if($limit){

            $limit_sql = 'LIMIT '.($page*$limit).', '.$limit;
        }

        if($min_price == 0 && $max_price == 0){

            $where = '';
        }else{

            // Eto sinhronizirovano s WC v faile "plugins/woocommerce/includes/class-wc-query.php" 
            // v funlcie "price_filter_post_clauses"
            $where = 'WHERE NOT ("'.$max_price.'" < min_price OR "'.$min_price.'" > max_price )';
        }

        $sql = '
        SELECT 
            `id`, `title`, `min_price`, `max_price`, `rating`, `rating_count`, `total_sales`, `menu_order`, `post_date`, `post_type`
        FROM 
            (
                SELECT
                `'.$p.'`.`ID` AS `id`,
                `'.$p.'`.`post_title` AS `title`,
                `'.$p.'`.`menu_order` AS `menu_order`,
                `'.$p.'`.`post_date` AS `post_date`,
                `'.$p.'`.`post_type` AS `post_type`,
                `'.$wc_pml.'`.`min_price` AS `min_price`,
                `'.$wc_pml.'`.`max_price` AS `max_price`,
                `'.$wc_pml.'`.`average_rating` AS `rating`,
                `'.$wc_pml.'`.`rating_count` AS `rating_count`,
                `'.$wc_pml.'`.`total_sales` AS `total_sales`
                FROM `'.$p.'` 
                INNER JOIN `'.$wc_pml.'`
                ON 
                    `'.$p.'`.`ID` = `'.$wc_pml.'`.`product_id`
                WHERE 
                    `'.$p.'`.`ID` IN ('.$str_ids.') AND 
                    `'.$p.'`.`post_status` = "publish" 
            ) AS all_types_prices_of_products
        '.$where.'
        GROUP BY `id`, `post_type`
        '.$order_by.'
        '.$limit_sql.'
        ';

        $result = $wpdb->get_results($sql);
        if($result === false) return false;
        if(!$result) array();

        return $result;
    }

    //-----------------------------------------------------------------------

    public static function main_amount_products_by_ids(
        $str_ids, $min_price = 0, $max_price = 0, $limit = '', $order = '', $page = ''
    ){

        global $wpdb;
    
        $p = $wpdb->prefix.'posts';
        $p_m = $wpdb->prefix.'postmeta';
        $wc_pml = $wpdb->prefix.'wc_product_meta_lookup';

        // Eto sinhronizirovano s WC v faile "plugins/woocommerce/includes/class-wc-query.php" 
        // v fynkcie "get_catalog_ordering_args"
        if($order){

            $order_by = Instruments::set_order_in_compact_function_agb($order);
        }else{

            //$order_by = 'ORDER BY `menu_order` ASC, `title` ASC';

            // Poka vmesto "menu_order" stavim "post_date"
            $order_by = 'ORDER BY `post_date` DESC, `id` DESC';
        }

        $page = ($page) ? $page : 0;

        // Dolzhen but pystum esli vuborka ne cherez ajax 
        // chto bu limit rabotal cherez wp_query pravilno
        $limit_sql = '';
        if($limit){

            $limit_sql = 'LIMIT '.($page*$limit).', '.$limit;
        }

        if($min_price == 0 && $max_price == 0){

            $where = '';
        }else{

            // Eto sinhronizirovano s WC v faile "plugins/woocommerce/includes/class-wc-query.php" 
            // v funlcie "price_filter_post_clauses"
            $where = 'WHERE NOT ("'.$max_price.'" < min_price OR "'.$min_price.'" > max_price )';
        }

        $sql = '
        SELECT 
            COUNT(`id`) AS `amount`
        FROM 
            (
                SELECT
                `'.$p.'`.`ID` AS `id`,
                `'.$p.'`.`post_title` AS `title`,
                `'.$p.'`.`menu_order` AS `menu_order`,
                `'.$p.'`.`post_date` AS `post_date`,
                `'.$p.'`.`post_type` AS `post_type`,
                `'.$wc_pml.'`.`min_price` AS `min_price`,
                `'.$wc_pml.'`.`max_price` AS `max_price`,
                `'.$wc_pml.'`.`average_rating` AS `rating`,
                `'.$wc_pml.'`.`rating_count` AS `rating_count`,
                `'.$wc_pml.'`.`total_sales` AS `total_sales`
                FROM `'.$p.'` 
                INNER JOIN `'.$wc_pml.'`
                ON 
                    `'.$p.'`.`ID` = `'.$wc_pml.'`.`product_id`
                WHERE 
                    `'.$p.'`.`ID` IN ('.$str_ids.') AND 
                    `'.$p.'`.`post_status` = "publish" 
            ) AS all_types_prices_of_products
        '.$where.'
        '.$order_by.'
        '.$limit_sql.'
        ';

        $result = $wpdb->get_row($sql);
        if(!$result) return 0;
        if(!property_exists($result, 'amount')) return 0;

        return $result->amount;
    }

    //-----------------------------------------------------------------------

    public static function main_select_products_without_ids(
        $min_price = 0, $max_price = 0, $limit = '', $order = '', $page = '', $lang = ''
    ){

        global $wpdb;
    
        $p = $wpdb->prefix.'posts';
        $p_m = $wpdb->prefix.'postmeta';
        $wc_pml = $wpdb->prefix.'wc_product_meta_lookup';

        // Eto sinhronizirovano s WC v faile "plugins/woocommerce/includes/class-wc-query.php" 
        // v fynkcie "get_catalog_ordering_args"
        if($order){

            $order_by = Instruments::set_order_in_compact_function_agb($order);
        }else{

            //$order_by = 'ORDER BY `menu_order` ASC, `title` ASC';

            // Poka vmesto "menu_order" stavim "post_date"
            $order_by = 'ORDER BY `post_date` DESC, `id` DESC';
        }

        $page = ($page) ? $page : 0;

        // Dolzhen but pystum esli vuborka ne cherez ajax 
        // chto bu limit rabotal cherez wp_query pravilno
        $limit_sql = '';
        if($limit){

            $limit_sql = 'LIMIT '.($page*$limit).', '.$limit;
        }

        if($min_price == 0 && $max_price == 0){

            $where = '';
        }else{

            // Eto sinhronizirovano s WC v faile "plugins/woocommerce/includes/class-wc-query.php" 
            // v funlcie "price_filter_post_clauses"
            $where = 'WHERE NOT ("'.$max_price.'" < min_price OR "'.$min_price.'" > max_price )';
        }

        // CAST(`'.$t2.'`.`meta_value` AS SIGNED) - preobrazovanie strokovogo v chislovoj
        $sql = '
        SELECT 
            `id`, `title`, `min_price`, `max_price`, `rating`, `rating_count`, `total_sales`, `menu_order`, `post_date`, `post_type`
        FROM 
            (
                SELECT
                `'.$p.'`.`ID` AS `id`,
                `'.$p.'`.`post_title` AS `title`,
                `'.$p.'`.`menu_order` AS `menu_order`,
                `'.$p.'`.`post_date` AS `post_date`,
                `'.$p.'`.`post_type` AS `post_type`,
                `'.$wc_pml.'`.`min_price` AS `min_price`,
                `'.$wc_pml.'`.`max_price` AS `max_price`,
                `'.$wc_pml.'`.`average_rating` AS `rating`,
                `'.$wc_pml.'`.`rating_count` AS `rating_count`,
                `'.$wc_pml.'`.`total_sales` AS `total_sales`
                FROM `'.$p.'` 
                INNER JOIN `'.$wc_pml.'`
                ON 
                    `'.$p.'`.`ID` = `'.$wc_pml.'`.`product_id`
                WHERE 
                    `'.$p.'`.`post_type` = "product" AND 
                    `'.$p.'`.`post_status` = "publish" 
            ) AS all_types_prices_of_products
        '.$where.'
        GROUP BY `id`, `post_type`
        '.$order_by.'
        '.$limit_sql.'
        ';

        $result = $wpdb->get_results($sql);
        if($result === false) return false;
        if(!$result) array();
        //---------------------------------------------

        //---------------------------------------------
        // Polychit isklychennue ids tovarov iz pokaza v kataloge
        //---------------------------------------------

        $t_r = $wpdb->prefix.'term_relationships';
        $t_t = $wpdb->prefix.'term_taxonomy';
        $t = $wpdb->prefix.'terms';

        // Vubiraem term kotoruj otvechaet za otobrazhenie tovara v kataloge
        // on ne imeet privyazki k yazuky
        $sql = '
        SELECT 
            `term_id`
        FROM `'.$t.'`
        WHERE
            `name` = "exclude-from-catalog"
        ';

        $term_id = $wpdb->get_row($sql);
        if(!$term_id) return false;

        $term_id = $term_id->term_id;
        //---------------------------------------------

        // Vubiraem otklychennue iz pokaza v kataloge tovaru
        $sql = '
        SELECT 
            `'.$t_r.'`.`object_id` AS `product_id`
        FROM `'.$t_r.'`
        WHERE
            `'.$t_r.'`.`term_taxonomy_id` IN (
                SELECT `term_taxonomy_id` AS `tt_id` 
                FROM `'.$t_t.'` `t_t2`, `'.$t.'` `t2` 
                WHERE 
                    `t2`.`term_id` = `t_t2`.`term_id` AND 
                    `t2`.`term_id` IN ('.$term_id.')
            )
        ';
        
        $excluded_ids = $wpdb->get_results($sql);
        // Teper vubiraem polnostiy vse ex tovaru po vsem yazukam, esli odnomy zadan 
        // exclude to i dtygoj tovar drygogo yazuka tozhe dolzhen imet isklychenie s pokaza
        $excluded_ids = Instruments::products_ids_of_all_langs($excluded_ids, $lang);
        if( ! is_array($excluded_ids) || $excluded_ids === false ) {
            
            return 'error_ex_p_terms';
        }
        // Esli net rezyltatov to nichto ne isklycheno i dalee vuvestu vse tovaru
        elseif( is_array($excluded_ids) && count($excluded_ids) > 0 ){

            $str_ex_ids = '';
            foreach($excluded_ids as $key => $product){
                
                $str_ex_ids .= $product->product_id.',';
            }
            //$str_ex_ids = preg_replace('#[\s]*,[\s]*$#', '', $str_ex_ids);
            $str_ex_ids = ','.$str_ex_ids;
            //---------------------------------------------
            unset($excluded_ids);
            //---------------------------------------------

            foreach($result as $key => $product){
                
                if(preg_match('#,'.$product->id.',#', $str_ex_ids)){

                    unset($result[$key]);
                }
            }
            //---------------------------------------------
        }
        
        return $result;
    }

    //-----------------------------------------------------------------------

    public static function main_amount_products_without_ids(
        $min_price = 0, $max_price = 0, $limit = '', $order = '', $page = '', $lang = ''
    ){

        global $wpdb;
    
        $p = $wpdb->prefix.'posts';
        $p_m = $wpdb->prefix.'postmeta';
        $wc_pml = $wpdb->prefix.'wc_product_meta_lookup';

        $page = ($page) ? $page : 0;

        // Dolzhen but pystum esli vuborka ne cherez ajax 
        // chto bu limit rabotal cherez wp_query pravilno
        $limit_sql = '';
        if($limit){

            $limit_sql = 'LIMIT '.($page*$limit).', '.$limit;
        }

        if($min_price == 0 && $max_price == 0){

            $where = '';
        }else{

            // Eto sinhronizirovano s WC v faile "plugins/woocommerce/includes/class-wc-query.php" 
            // v funlcie "price_filter_post_clauses"
            $where = 'WHERE NOT ("'.$max_price.'" < min_price OR "'.$min_price.'" > max_price )';
        }

        // CAST(`'.$t2.'`.`meta_value` AS SIGNED) - preobrazovanie strokovogo v chislovoj
        $sql = '
        SELECT 
            `id`, `title`, `min_price`, `max_price`, `rating`, `rating_count`, `total_sales`, `menu_order`, `post_date`, `post_type`
        FROM 
            (
                SELECT
                `'.$p.'`.`ID` AS `id`,
                `'.$p.'`.`post_title` AS `title`,
                `'.$p.'`.`menu_order` AS `menu_order`,
                `'.$p.'`.`post_date` AS `post_date`,
                `'.$p.'`.`post_type` AS `post_type`,
                `'.$wc_pml.'`.`min_price` AS `min_price`,
                `'.$wc_pml.'`.`max_price` AS `max_price`,
                `'.$wc_pml.'`.`average_rating` AS `rating`,
                `'.$wc_pml.'`.`rating_count` AS `rating_count`,
                `'.$wc_pml.'`.`total_sales` AS `total_sales`
                FROM `'.$p.'` 
                INNER JOIN `'.$wc_pml.'`
                ON 
                    `'.$p.'`.`ID` = `'.$wc_pml.'`.`product_id`
                WHERE 
                    `'.$p.'`.`post_type` = "product" AND 
                    `'.$p.'`.`post_status` = "publish" 
            ) AS all_types_prices_of_products
        '.$where.'
        GROUP BY `id`, `post_type`
        '.$limit_sql.'
        ';

        $result = $wpdb->get_results($sql);
        if($result === false) return 0;
        if(!property_exists($result[0], 'id')) return 0;

        
        //---------------------------------------------
        // Polychit isklychennue ids tovarov iz pokaza v kataloge
        //---------------------------------------------

        $t_r = $wpdb->prefix.'term_relationships';
        $t_t = $wpdb->prefix.'term_taxonomy';
        $t = $wpdb->prefix.'terms';

        // Vubiraem term kotoruj otvechaet za otobrazhenie tovara v kataloge
        // on ne imeet privyazki k yazuky
        $sql = '
        SELECT 
            `term_id`
        FROM `'.$t.'`
        WHERE
            `name` = "exclude-from-catalog"
        ';

        $term_id = $wpdb->get_row($sql);
        if(!$term_id) return false;

        $term_id = $term_id->term_id;
        //---------------------------------------------

        // Vubiraem otklychennue iz pokaza v kataloge tovaru
        $sql = '
        SELECT 
            `'.$t_r.'`.`object_id` AS `product_id`
        FROM `'.$t_r.'`
        WHERE
            `'.$t_r.'`.`term_taxonomy_id` IN (
                SELECT `term_taxonomy_id` AS `tt_id` 
                FROM `'.$t_t.'` `t_t2`, `'.$t.'` `t2` 
                WHERE 
                    `t2`.`term_id` = `t_t2`.`term_id` AND 
                    `t2`.`term_id` IN ('.$term_id.')
            )
        ';
        
        $excluded_ids = $wpdb->get_results($sql);
        // Teper vubiraem polnostiy vse ex tovaru po vsem yazukam, esli odnomy zadan 
        // exclude to i dtygoj tovar drygogo yazuka tozhe dolzhen imet isklychenie s pokaza
        $excluded_ids = Instruments::products_ids_of_all_langs($excluded_ids, $lang);
        if( ! is_array($excluded_ids) || $excluded_ids === false ) {
            
            return 0;
        }
        // Esli net rezyltatov to nichto ne isklycheno i dalee vuvestu vse tovaru
        elseif( is_array($excluded_ids) && count($excluded_ids) > 0 ){

            $str_ex_ids = '';
            foreach($excluded_ids as $key => $product){
                
                $str_ex_ids .= $product->product_id.',';
            }
            //$str_ex_ids = preg_replace('#[\s]*,[\s]*$#', '', $str_ex_ids);
            $str_ex_ids = ','.$str_ex_ids;
            //---------------------------------------------
            unset($excluded_ids);
            //---------------------------------------------

            foreach($result as $key => $product){
                
                // Dve zapyatue dlya chetkogo opredeleniya nomera potomy kak v bolshyjy cifry mozhet 
                // popost chast korotkogo nomera id
                if(preg_match('#,'.$product->id.',#', $str_ex_ids)){

                    unset($result[$key]);
                }
            }
            //---------------------------------------------
        }

        return count($result);
    }

    //-----------------------------------------------------------------------

    public static function products_ids_by_correct_lang($products_ids, $lang){

        // Esli net mnogoyazuchnosti to vernyt obuchnuj massiv tovarov
        if(!$lang) return $products_ids;

        global $wpdb;

        $t_r = $wpdb->prefix.'term_relationships';
        $t_t = $wpdb->prefix.'term_taxonomy';

        // Vubiraem po odnomy potomy, chto sohranyaetsa ishodnaya sortirovka tovarov
        // i ne nyzhno potom delat sortirovky massiva
        $byLangProducts = array();
        $alreadySaved = array();
        foreach($products_ids as $key => $product){

            $sql = '
            SELECT 
                `'.$t_t.'`.`description` AS `description`
            FROM `'.$t_r.'`, `'.$t_t.'`
            WHERE
                `'.$t_r.'`.`object_id` = '.$product->id.' AND
                `'.$t_r.'`.`term_taxonomy_id` = `'.$t_t.'`.`term_taxonomy_id` AND
                `'.$t_t.'`.`taxonomy` = "post_translations"
            ';

            $result = $wpdb->get_row($sql);
            if(!$result) {

                if(isset($alreadySaved[$product->id])) continue;

                $alreadySaved[$product->id] = 1;
                
                $byLangProducts[] = (object) array(
                    'id' => $product->id,
                    'min_price' => $product->min_price,
                    'post_type' => $product->post_type,
                );

                continue;
            }

            $productLangs = unserialize($result->description);
            // Continue if not product by this lang
            if(!isset($productLangs[$lang])) continue;
            // Esli bul dobavlen to bolshe ne dobavlyaem
            if(isset($alreadySaved[$productLangs[$lang]])) continue;

            $alreadySaved[$productLangs[$lang]] = 1;
            
            $byLangProducts[] = (object) array(
                'id' => $productLangs[$lang],
                'min_price' => $product->min_price,
                'post_type' => $product->post_type,
            );
        }


        return $byLangProducts;
    }

    //-----------------------------------------------------------------------

    public static function products_ids_of_all_langs($products_ids, $lang){

        // Esli net mnogoyazuchnosti to vernyt obuchnuj massiv tovarov
        if(!$lang) return $products_ids;

        global $wpdb;

        $t_r = $wpdb->prefix.'term_relationships';
        $t_t = $wpdb->prefix.'term_taxonomy';

        // Vubiraem po odnomy potomy, chto sohranyaetsa ishodnaya sortirovka tovarov
        // i ne nyzhno potom delat sortirovky massiva
        $byLangProducts = array();
        $alreadySaved = array();
        foreach($products_ids as $key => $product){

            $sql = '
            SELECT 
                `'.$t_t.'`.`description` AS `description`
            FROM `'.$t_r.'`, `'.$t_t.'`
            WHERE
                `'.$t_r.'`.`object_id` = '.$product->product_id.' AND
                `'.$t_r.'`.`term_taxonomy_id` = `'.$t_t.'`.`term_taxonomy_id` AND
                `'.$t_t.'`.`taxonomy` = "post_translations"
            ';

            $result = $wpdb->get_row($sql);
            if(!$result) {

                if(isset($alreadySaved[$product->product_id])) continue;

                $alreadySaved[$product->product_id] = 1;
                
                $byLangProducts[] = (object) array(
                    'product_id' => $product->product_id,
                );

                continue;
            }

            $productLangs = unserialize($result->description);
            // nahodim vse id tovara po vsem yazukam
            foreach($productLangs as $kl_lang => $vl_pid){

                // Esli bul dobavlen to bolshe ne dobavlyaem
                if(isset($alreadySaved[$vl_pid])) continue;

                $alreadySaved[$vl_pid] = 1;
                
                $byLangProducts[] = (object) array(
                    'product_id' => $vl_pid,
                );
            }
        }


        return $byLangProducts;
    }

    //-----------------------------------------------------------------------

    // Return no as id field but as product_id format
    public static function products_ids_by_correct_lang_as_product_id_field($products_ids, $lang, $id_field_name = 'product_id'){

        // Esli net mnogoyazuchnosti to vernyt obuchnuj massiv tovarov
        if(!$lang) return $products_ids;

        global $wpdb;

        $t_r = $wpdb->prefix.'term_relationships';
        $t_t = $wpdb->prefix.'term_taxonomy';

        // Vubiraem po odnomy potomy, chto sohranyaetsa ishodnaya sortirovka tovarov
        // i ne nyzhno potom delat sortirovky massiva
        $byLangProducts = array();
        $alreadySaved = array();
        foreach($products_ids as $key => $product){

            $product_id = $product->{$id_field_name};

            $sql = '
            SELECT 
                `'.$t_t.'`.`description` AS `description`
            FROM `'.$t_r.'`, `'.$t_t.'`
            WHERE
                `'.$t_r.'`.`object_id` = '.$product_id.' AND
                `'.$t_r.'`.`term_taxonomy_id` = `'.$t_t.'`.`term_taxonomy_id` AND
                `'.$t_t.'`.`taxonomy` = "post_translations"
            ';

            $result = $wpdb->get_row($sql);
            if(!$result) {
                
                if(isset($alreadySaved[$product_id])) continue;
                $alreadySaved[$product_id] = 1;

                $byLangProducts[] = (object) array(
                    'product_id' => $product_id,
                );

                continue;
            }

            $productLangs = unserialize($result->description);
            // Continue if not product by this lang
            if(!isset($productLangs[$lang])) continue;
            // Esli bul dobavlen to bolshe ne dobavlyaem
            if(isset($alreadySaved[$productLangs[$lang]])) continue;

            $alreadySaved[$productLangs[$lang]] = 1;
            
            $byLangProducts[] = (object) array(
                'product_id' => $productLangs[$lang],
            );
        }

        return $byLangProducts;
    }

    //-----------------------------------------------------------------------

    public static function convertToStrIdsFromIdsArray($products_arr){

        $str_ids = '';
        foreach($products_arr as $key => $product){
            
            $str_ids .= $product->product_id.',';
        }
        $str_ids = preg_replace('#[\s]*,[\s]*$#', '', $str_ids);
        //--------------------------------

        return $str_ids;
    }

    //-----------------------------------------------------------------------

    public static function get_addition_data_for_product($product_id){

        $data = array(
            'image_html' => '',
            'onsale_html' => '', 
            'addition_block_html' => '',
            'price_html' => '',
            'add_to_cart_btn_html' => '',
        );

        $product = wc_get_product($product_id);

        if(!$product) return $data;

        $percent = ProductsFunctions::get_onsale_percentage_agb($product);
        $percent = '-'.$percent.'%';

        $product_url = get_permalink( $product_id );

        $data['url'] = $product_url;
        
        // Ety fynkcijy mozhno najti y woocommerce v 
        // "plugins/woocommerce/includes/wc-template-functions.php"
        // v fynkciji vuvoda kartinki tivara "woocommerce_get_product_thumbnail"
        $data['image_html'] = 
        '<div class="loop-prod-image-out-agb">'.
        $product->get_image( 
            /*$image_size = */'woocommerce_thumbnail', 
            /*$attr = */array(), 
            /*$placeholder = */true 
        ).
        '</div>';

         if ( $product->is_on_sale() ) {

            $data['onsale_html'] = 
            '<span class="onsale">' . $percent /*esc_html__( 'Sale!', LANG_THEME_KEY_AGB )*/ . '</span>';
        }

        $data['quick_view_btn_html'] = ProductsFunctions::quick_view_button_agb( $product_id );

        $data['addition_block_html'] = ProductsFunctions::after_title_addition_block_agb($product_id);

        $data['title_html'] = ProductsFunctions::shop_loop_product_title($product_id);

        $pdata = productDataForCartAgb($product_id);

        $price_html = $product->get_price_html();
        $data['price_html'] = 
        '<span class="price">'.$price_html.'</span>'.
        '
        <div class="fast-buy-amount-product-elem-agb">
            <div class="fast-buy-minus">-</div>
            <div class="fast-buy-amount-field">
                <input type="text" 
                class="fast_buy_amount_input_elem_agb" 
                name="fast_buy_amount_input_elem_agb" value="1">
            </div>
            <div class="fast-buy-plus">+</div>
        </div>
        '.
        '<div class="over-btns-buy-cart-agb">'.
        '<div 
        agb_product_type="'.$pdata['type'].'"
        agb_available="available"
        agb_product_sku="'.$pdata['sku'].'"
        agb_product_id="'.$pdata['pid'].'"
        agb_product_price="'.$pdata['price'].'"
        class="buy-now-btn-agb">'.__('Buy now', LANG_THEME_KEY_AGB).'</div>'.
        '<div 
        agb_product_type="'.$pdata['type'].'" 
        agb_product_sku="'.$pdata['sku'].'" 
        agb_product_id="'.$pdata['pid'].'" 
        agb_product_price="'.$pdata['price'].'"
        agb_product_name="'.$pdata['name'].'"
        class="add-to-cart-now-agb '.$pdata['in_cart'].'"></div>'.
        '</div>';

        $data['add_to_cart_btn_html'] = 
        '<div class="loop-button-wrap button-layout2">'.
        apply_filters(
            'woocommerce_loop_add_to_cart_link',
            sprintf(
                '<a href="%s" data-quantity="%s" class="%s" %s>%s</a>',
                esc_url( $product->add_to_cart_url() ),
                1, /*esc_attr( isset( $args['quantity'] ) ? $args['quantity'] : 1 ),*/
                'button product_type_variable add_to_cart_button', /*esc_attr( isset( $args['class'] ) ? $args['class'] : 'button' ),*/
                '', /*isset( $args['attributes'] ) ? wc_implode_html_attributes( $args['attributes'] ) : '',*/
                esc_html( $product->add_to_cart_text() )
            ),
            $product,
            /*$args = */array()
        ).
        '</div>';
        
        return $data;
    }

    //-----------------------------------------------------------------------

    public static function all_products_addition_data_get($products){

        foreach($products as $key => $product){

            $products[$key]->addition = Instruments::get_addition_data_for_product($product->id);
        }

        return $products;
    }

    //-----------------------------------------------------------------------

    public static function get_all_categories(){

        global $wpdb;

        $t1 = $wpdb->prefix.'term_taxonomy';
        $t2 = $wpdb->prefix.'terms';

        $sql = '
        SELECT 
            `'.$t2.'`.`term_id` AS `term_id`, 
            `'.$t2.'`.`name` AS `name`,
            `'.$t2.'`.`slug` AS `slug`
        FROM `'.$t1.'` 
        LEFT JOIN `'.$t2.'`
        ON 
            `'.$t1.'`.`term_id` = `'.$t2.'`.`term_id`
        WHERE 
            `'.$t1.'`.`taxonomy` = "product_cat"
        ';

        $result = $wpdb->get_results($sql);

        if(!$result) return false;
        
        //--------------------------------------
        // If active multilangual get right translate
        if(function_exists('pll_default_language')){
            
            if(is_admin()){

                // In admin only default lang
                $lang = pll_default_language();
            }else{

                $lang = pll_current_language();
            }

            $result = Instruments::terms_with_right_lang($lang, $result);
        }
        //--------------------------------------

        return $result;
    }

    //-----------------------------------------------------------------------

    public static function filters_list_output(&$filters_data){

        if(!$filters_data) return '';
        if(
            !is_array($filters_data) || 
            count($filters_data) < 1) return '';

        echo '<ul class="list-of-some-elements-agb">';

        foreach($filters_data as $key => $val){
            
            if($val->filter_category_id){

                $catg = get_term_by('id', $val->filter_category_id, 'product_cat');
                $catg = $catg->name.':'.$catg->term_id;
            }else{

                $catg = TranslatorCenter::run('Without catg').':'.$val->filter_category_id;
            }

            echo '
            <li const_id="'.$val->const_id.'">
                <div class="has-group-elem">
                    <div class="inputs-blocks">
                        <div class="element-identifier-agb">
                            '.TranslatorCenter::run('Filter identifier:').' '.$val->const_id.'
                            '.$catg.'
                        </div>

                        <div class="input-wrap-elem" style="width:400px;">
                            <div class="description-on-input">
                            '.TranslatorCenter::run('Filter name:').'
                            </div>

                            <input 
                            style="width:100%;" 
                            type="text" 
                            value="'.$val->name.'"
                            class="filter_existing_name"
                            >
                        </div>
                    </div>

                    <div class="group-control">
                        <a 
                        filter_const_id="'.$val->const_id.'"
                        class="link-for-rename-agb" 
                        href="#"
                        >
                            '.TranslatorCenter::run('rename').'
                        </a>

                        <a 
                        filter_const_id="'.$val->const_id.'"
                        class="delete-filter-link-agb" 
                        href="#"
                        >
                            '.TranslatorCenter::run('delete').'
                        </a>
                    </div>
                </div>
            </li>
            ';
        }

        echo '</ul>';
    }

    //-----------------------------------------------------------------------

    public static function delete_element_data_agb($filter_const_id){

        global $wpdb;

        $table = 'wp_agb_products_filters';

        $results = $wpdb->delete( $table, array( 'const_id' => $filter_const_id ) );

        if($results === false) return false;
        //---------------------

        return true;
    }

    //-----------------------------------------------------------------------

    public static function update_element_data_agb($filter_const_id, $rename_name){

        global $wpdb;

        $table = 'wp_agb_products_filters';

        $results = $wpdb->update( 
            $table, 
            array( 'name' => $rename_name,), 
            array( 'const_id' => $filter_const_id ), 
            array( '%s', ), 
            array( '%d', ) 
        );
        
        if($results === false) return false;

        return true;
    }

    //-----------------------------------------------------------------------

    public static function getTermLangRelation($term_id = 0, $lang = false){

        if($term_id < 1) return false;
    
        global $wpdb;
    
        $t1 = $wpdb->prefix.'term_relationships';
        $t2 = $wpdb->prefix.'term_taxonomy';
    
        $sql = '
        SELECT `taxonomy`, `description` 
        FROM `'.$t1.'` 
        LEFT JOIN `'.$t2.'`
        ON 
            `'.$t1.'`.`term_taxonomy_id` = `'.$t2.'`.`term_taxonomy_id` 
        WHERE
            `'.$t1.'`.`object_id` = '.$term_id.' AND 
            `'.$t2.'`.`taxonomy` = "term_translations"
        ';
    
        $result = $wpdb->get_row($sql);
        if(!$result && $lang) {

            return array($lang => $term_id);
        }elseif(!$result && !$lang) return array('uk' => $term_id);
    
        $termLangs = unserialize($result->description);
    
        return $termLangs;
    }

    //-----------------------------------------------------------------------

    public static function get_term_by_id_simple($term_id = 0){

        if($term_id < 1) return false;
    
        global $wpdb;
    
        $t1 = $wpdb->prefix.'terms';
    
        $sql = '
        SELECT `term_id`, `name`, `slug` 
        FROM `'.$t1.'` 
        WHERE
            `'.$t1.'`.`term_id` = '.$term_id.' 
        ';
    
        $result = $wpdb->get_row($sql);
        if(!$result) return false;
    
        return $result;
    }

    //-----------------------------------------------------------------------

    public static function get_terms_by_ids($str_terms_ids){

        global $wpdb;
    
        $t1 = $wpdb->prefix.'terms';
    
        $sql = '
        SELECT 
             `term_id`, `name`, `slug` 
        FROM `'.$t1.'` 
        WHERE
            `'.$t1.'`.`term_id` IN ('.$str_terms_ids.')  
        ';
    
        $result = $wpdb->get_results($sql);
        if($result === false) return false;
        if(!$result) return array();
    
        return $result;
    }

    //-----------------------------------------------------------------------

    // Make category as translated
    public static function terms_with_right_lang($terms_lang, &$terms_data){

        $new_terms_arr = array();
        $no_finded_any_elements = true;
        $save_current_langs_ids = '';
        foreach($terms_data as $key => $term){

            $term_langs_arr = Instruments::getTermLangRelation($term->term_id, $terms_lang);

            if(!$term_langs_arr){

                if(isset($new_terms_arr[$term->term_id])) continue;
                
                $save_current_langs_ids .= $term->term_id.',';

                $no_finded_any_elements = false;
                $new_terms_arr[$term->term_id] = $term;

                continue;
            }

            // Esli yzhe bul dobavlen to perehodim k sledyjyshchemy
            // Kod dolzhen but imenno v etom meste a ne vushe
            if(isset($new_terms_arr[$term_langs_arr[$terms_lang]])) continue;
            if($term->term_id != $term_langs_arr[$terms_lang]) {

                $save_current_langs_ids .= $term_langs_arr[$terms_lang].',';
                continue;
            }
            $no_finded_any_elements = false;
            $new_terms_arr[$term_langs_arr[$terms_lang]] = $term;
        }

        if(!$terms_data) $no_finded_any_elements = false;

        // Esli buli ids tolko odnogo yazuka to vuborka perevodov ne bula vubrana
        // i potomy nyzhno sdelat pryamyjy vuborky
        if(
            is_array($terms_data) && count($terms_data) > 0 && $no_finded_any_elements
        ){

            $save_current_langs_ids = preg_replace('#,$#', '', $save_current_langs_ids);
            $new_terms_arr = Instruments::get_terms_by_ids($save_current_langs_ids);
            
            $new_terms_arr_2 = array();
            foreach($new_terms_arr as $key => $term){

                $new_terms_arr_2[$term->term_id] = $term;
            }

            if(count($new_terms_arr_2) > 0) $new_terms_arr = $new_terms_arr_2;
        }

        return $new_terms_arr;
    }

    //-----------------------------------------------------------------------

    public static function category_id_compact_get(){

        if(is_shop()){

            $category_id = 0;
        }else{

            $category = get_queried_object(); // objekt stranicu, gde mozhno polychit kategorijy
            $category_id = $category->term_id;
        }

        return $category_id;
    }

    //-----------------------------------------------------------------------

    public static function genNextId_agb($table_name, $id_name = 'id', $noPrefix = false){
    
        global $wpdb;
        if(
            !preg_match('#^'.$wpdb->prefix.'#', $table_name) && 
            !$noPrefix
        ){
    
            $table_name = $wpdb->prefix.$table_name;
        }
    
        $data = $wpdb->get_row('SELECT MAX(`'.$id_name.'`) AS `max_id` FROM `'.$table_name.'`');
        
        if(!is_numeric($data->max_id)) return 1;		
        return ($data->max_id + 1);
    }

    //-----------------------------------------------------------------------
}

?>