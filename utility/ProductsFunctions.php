<?php

namespace NpAgbShippingMethod;

class ProductsFunctions{

    public static function after_title_addition_block_agb($product_id){

        $available = false;
        $li_list = array();
        $firstElem = true; $defElem = true;
        $remeberVariaIdForClassFirstElem = false;
        $allAttrsList = '';
        $classFirstE = '';
        $rememberFirstVariaIdForDefAttr = false;
        $defAttrsAvailable = true;
        $error = '';
    
        // Get correct product for current page lang
        if($product_id = ProductsFunctions::getCartProductWithLangCondition($product_id)){
            
            $product = wc_get_product($product_id);
        }

        $price = 
        ($product->sale_price > 0 && 
        $product->sale_price < $product->price) ? $product->sale_price : $product->price;
    
        $pType = $product->get_type();
    
        $inCart = '';
        if(ProductsFunctions::is_product_in_cart_agb_with_lang_condition($product_id, $product, $pType)) $inCart = "added";
    
        $fastAmountElem = '';
        /*
        $fastAmountElem = '
        <div class="fast-buy-amount-product-elem-agb">
            <div class="fast-buy-minus">-</div>
            <div class="fast-buy-amount-field">
                <input type="text" 
                class="fast_buy_amount_input_elem_agb" 
                name="fast_buy_amount_input_elem_agb" value="1">
            </div>
            <div class="fast-buy-plus">+</div>
        </div>
        ';
        */
    
        if ($pType == 'variable') {
    
            $available_variations = $product->get_available_variations();
            $attrs = $product->get_variation_attributes();
            $defAttrs = $product->get_default_attributes();
    
            // If can set default attributes check
            if(is_array($defAttrs) && count($defAttrs) > 0){
                
                foreach($defAttrs as $defKey => $defVal){
    
                    foreach ( $available_variations as $key => $value ){
    
                        if(
                            isset($value['attributes']['attribute_'.$defKey]) &&
                            ($value['attributes']['attribute_'.$defKey] == $defVal ||
                            $value['attributes']['attribute_'.$defKey] == '')
                        ){
    
                            if(!$rememberFirstVariaIdForDefAttr) $rememberFirstVariaIdForDefAttr = $value['variation_id'];
                            if(
                                $rememberFirstVariaIdForDefAttr !== $value['variation_id'] &&
                                $value['attributes']['attribute_'.$defKey] !== ''
                            ) {
                                $defAttrsAvailable = false; break;
                            }
                        }
                    }
    
                    if(!$defAttrsAvailable) break;
                }
            }
            //--------------------------
    
            foreach($attrs as $k => $v){
    
                $firstElem = true; $defElem = true;
                if(!isset($li_list[$k])) $li_list[$k] = '';
    
                foreach($v as $att => $attVal){
    
                    $attr_available_variations = '';
                    foreach ( $available_variations as $key => $value ){
    
                        if(
                            isset($value['attributes']['attribute_'.$k]) &&
                            ($value['attributes']['attribute_'.$k] == $attVal ||
                            $value['attributes']['attribute_'.$k] == '')
                        ){
    
                            $attr_available_variations .= $value['variation_id'].',';
                            //-----------------agb start
                            // First elem class mark must be only on same variation id attributes
                            if(!$remeberVariaIdForClassFirstElem) {
    
                                $remeberVariaIdForClassFirstElem = $value['variation_id'];
                            }
                            if(is_array($defAttrs) && count($defAttrs) > 0 && $defAttrsAvailable){
                                
                                // default attrs if use
                                if(
                                    $defElem &&
                                    $rememberFirstVariaIdForDefAttr == $value['variation_id']
                                ) $classFirstE = 'class="active"';
                            }else{
    
                                // simple first elem if not use default attrs
                                if(
                                    $firstElem && 
                                    $remeberVariaIdForClassFirstElem == $value['variation_id']
                                ) $classFirstE = 'class="active"';
                            }
                            //-----------------agb end
                        }
                    }
    
                    if($attr_available_variations !== '') {
    
                        $attr_available_variations = preg_replace('#[\s]*,[\s]*$#', '', $attr_available_variations);
                    }else{
    
                        // If not variable for product attr stop work and show error
                        $available = false; break;
                    }
    
                    //$termLangRelation = getTermLangRelationBySlug($attVal, $k);
                    $term = get_term_by('slug', $attVal, $k);
    
                    $attr_slug_name = 'attr_slug_name="'.$attVal.'"';
                    $vari_ids = 'attr_available_variations="'.$attr_available_variations.'"';
                    $li_list[$k] .= '
                    <li 
                    '.$classFirstE.'
                    '.$attr_slug_name.'
                    '.$vari_ids.'>
                        <span>'.$term->name.'</span>
                    </li>
                    ';
    
                    $available = true;
                    $firstElem = false;
                    $classFirstE = '';
                }
                
                $defElem = false;
                if(!$available) break;
            }
    
            
    
            if($available){
    
                foreach($li_list as $listKey => $listVal){
    
                    $allAttrsList .= '
                    <span>'.
                    TranslatorCenter::run(ProductsFunctions::woocommerce_get_pa_label_agb($listKey)).
                    ':</span>
                    <div class="sizes-of-product-and-other-agb">
                        <ul product_attr_slug="'.$listKey.'">
                        '.$listVal.'
                        </ul>
                    </div>
                    ';
                }

                $quickViewBtn = '
                <div 
                class="quick-view-pava-btn-agb agb33da-quick-view"
                data-product-id="'.$product_id.'" 
                data-nonce="'.esc_attr( wp_create_nonce( 'agb33da-qview-nonce' ) ).'"
                >
                </div>
                ';
    
                $korzinaBtn = '
                <div class="in-korzina-add-btn-agb '.$inCart.'" agb_product_type="variable" 
                agb_product_sku="'.$product->sku.'" agb_product_id="'.$product_id.'"
                agb_product_price="'.$price.'" agb_product_name="'.$product->name.'">
                    <span>'.__('Add to cart', LANG_THEME_KEY_AGB).'</span>
                </div>
                ';

                $wishListBtn = '
                <div class="wish-list-btn-agb" agb_product_id="'.$product_id.'">
                    <span>'.__('Add to wish list', LANG_THEME_KEY_AGB).'</span>
                </div>
                ';
    
                $fastBuyBtn = '
                <div class="in-list-fast-buy-btn-agb" agb_product_type="variable" agb_available="available" 
                agb_product_sku="'.$product->sku.'" agb_product_id="'.$product_id.'"
                agb_product_price="'.$price.'">
                    <span>'.__('Fast buy', LANG_THEME_KEY_AGB).'</span>
                </div>
                ';
            }else{

                $quickViewBtn = '';
    
                $korzinaBtn = '';

                $wishListBtn = '';
    
                $fastBuyBtn = '';
    
                $error = 
                '<div class="sizes-of-product-and-other-agb">'.
                '<span class="err-txt">'.
                __('Error. Product not available. Contact with support.', LANG_THEME_KEY_AGB).
                '</span>'.
                '</div>';
            }

            if(!is_user_logged_in()) $wishListBtn = '';
    
            return '
            <div class="outer-wrap-addition-data-agb">
                '.$allAttrsList.'
                '.$error.'
                '.$fastAmountElem.'
                <div class="btns-place-fast-agb">
                <div class="btns-place-fast-agb-inner">
                    '.$fastBuyBtn.$quickViewBtn.$wishListBtn.$korzinaBtn.'
                </div>
                </div>
            </div>
            ';
        }elseif($pType == 'simple'){

            $quickViewBtn = '
            <div 
            class="quick-view-pava-btn-agb agb33da-quick-view"
            data-product-id="'.$product_id.'" 
            data-nonce="'.esc_attr( wp_create_nonce( 'agb33da-qview-nonce' ) ).'"
            >
            </div>
            ';
    
            $korzinaBtn = '
            <div class="in-korzina-add-btn-agb '.$inCart.'" agb_product_type="simple" 
            agb_product_sku="'.$product->sku.'" agb_product_id="'.$product_id.'"
            agb_product_price="'.$price.'" agb_product_name="'.$product->name.'">
                <span>'.__('Add to cart', LANG_THEME_KEY_AGB).'</span>
            </div>
            ';

            $wishListBtn = '
            <div class="wish-list-btn-agb" agb_product_id="'.$product_id.'">
                <span>'.__('Add to wish list', LANG_THEME_KEY_AGB).'</span>
            </div>
            ';
    
            $fastBuyBtn = '
            <div class="in-list-fast-buy-btn-agb" agb_product_type="simple" agb_available="available" 
            agb_product_sku="'.$product->sku.'" agb_product_id="'.$product_id.'"
            agb_product_price="'.$price.'">
                <span>'.__('Fast buy', LANG_THEME_KEY_AGB).'</span>
            </div>
            ';

            if(!is_user_logged_in()) $wishListBtn = '';
    
            return '
            <div class="outer-wrap-addition-data-agb">
                '.$error.'
                '.$fastAmountElem.'
                <div class="btns-place-fast-agb">
                <div class="btns-place-fast-agb-inner">
                    '.$fastBuyBtn.$quickViewBtn.$wishListBtn.$korzinaBtn.'
                </div>
                </div>
            </div>
            ';
        }elseif($pType == 'variation'){
            
            $attrs = $product->get_attributes();
            $attrsHtml = '';
            foreach($attrs as $taxonomy => $attr_slug){
                
                $term = get_term_by('slug', $attr_slug, $taxonomy);
                
                $label = TranslatorCenter::run(ProductsFunctions::woocommerce_get_pa_label_agb($taxonomy));
                $attrsHtml .= 
                '<div class="attr-variation-product-block">'.
                '<span class="first">'.$label.':</span>'.
                '<span class="second">'.$term->name.'</span>'.
                '</div>';
            }

            $quickViewBtn = '
            <div 
            class="quick-view-pava-btn-agb agb33da-quick-view"
            data-product-id="'.$product_id.'" 
            data-nonce="'.esc_attr( wp_create_nonce( 'agb33da-qview-nonce' ) ).'"
            >
            </div>
            ';
    
            $korzinaBtn = '
            <div class="in-korzina-add-btn-agb '.$inCart.'" agb_product_type="simple" 
            agb_product_sku="'.$product->sku.'" agb_product_id="'.$product_id.'"
            agb_product_price="'.$price.'" agb_product_name="'.$product->name.'">
                <span>'.__('Add to cart', LANG_THEME_KEY_AGB).'</span>
            </div>
            ';

            $wishListBtn = '
            <div class="wish-list-btn-agb" agb_product_id="'.$product_id.'">
                <span>'.__('Add to wish list', LANG_THEME_KEY_AGB).'</span>
            </div>
            ';
    
            $fastBuyBtn = '
            <div class="in-list-fast-buy-btn-agb" agb_product_type="simple" agb_available="available" 
            agb_product_sku="'.$product->sku.'" agb_product_id="'.$product_id.'"
            agb_product_price="'.$price.'">
                <span>'.__('Fast buy', LANG_THEME_KEY_AGB).'</span>
            </div>
            ';

            if(!is_user_logged_in()) $wishListBtn = '';
    
            return '
            <div class="outer-wrap-addition-data-agb">
                '.$error.'
                '.$attrsHtml.'
                '.$fastAmountElem.'
                <div class="btns-place-fast-agb">
                <div class="btns-place-fast-agb-inner">
                    '.$fastBuyBtn.$quickViewBtn.$wishListBtn.$korzinaBtn.'
                </div>
                </div>
            </div>
            ';
        }
    }

    //------------------------------------------------

    public static function getCartProductWithLangCondition($product_id = 0){

        if($product_id < 1) return false;
        if(
            !function_exists('pll_default_language') || 
            !function_exists('pll_current_language')
        ) return $product_id;

        $defLang = pll_default_language();
        $curLang = pll_current_language();

        $curProductLang = pll_get_post_language($product_id);
        
        if(!$curProductLang) return $product_id;
        // Ostanovit esli i tekyshchij yazuk stranicu i yazuk tovara ravnu "$defLang"
        if($defLang == $curLang && $defLang == $curProductLang) return $product_id;
        //---------------

        if(!$productLangs = ProductsFunctions::getPostOrProductLangRelation($product_id)) return $product_id;

        $correctLangProductId = $productLangs[$curLang];

        return $correctLangProductId;
    }

    //------------------------------------------------

    public static function getPostOrProductLangRelation($post_id = 0){

        if($post_id < 1) return false;
    
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
            `'.$t1.'`.`object_id` = '.$post_id.' AND 
            `'.$t2.'`.`taxonomy` = "post_translations"
        ';
    
        $result = $wpdb->get_row($sql);
        if(!$result) return false;
    
        $postLangs = unserialize($result->description);
    
        return $postLangs;
    }

    //------------------------------------------------

    public static function is_product_in_cart_agb_with_lang_condition($productId, &$productObj = 0, $pType = 'simple') {

        if(!$productObj) return false;
    
        global $productsIdsInCart;
    
        if(isset($productsIdsInCart[$productId])) return true;
    
        if($langRelation = ProductsFunctions::getPostOrProductLangRelation($productId)) {
    
            //-----------------------
            // Poisk vseh drygih yazukovuh versij etogo tovara i proverka ne v korzine li oni
            $result = ProductsFunctions::searchInForeach_for_is_product_in_cart($productId, $langRelation);
            if($result) return true;
            //-----------------------
        }
        
        // Esli ranee ne bulo naideno to polychit dochernie tovaru etogo tovara 
        // i opredelit net li kakogo-to iz nih v korzine
        if($pType == 'variable'){
    
            $variations_ids = $productObj->get_children();
            foreach($variations_ids as $key => $pid){
    
                // Poisk tekyshchej variacii
                if(isset($productsIdsInCart[$pid])) return true;
    
                // Poisk variacij drygih yazukov tekyshchej variacii
                if($langRelation = ProductsFunctions::getPostOrProductLangRelation($pid)) {
    
                    //-----------------------
                    // Poisk vseh drygih yazukovuh versij etogo tovara i proverka ne v korzine li oni
                    $result = ProductsFunctions::searchInForeach_for_is_product_in_cart($pid, $langRelation);
                    if($result) return true;
                    //-----------------------
                }
            }
        }elseif($pType == 'variation'){
    
            $productId = $productObj->get_parent_id();
            if(isset($productsIdsInCart[$productId])) return true;
    
            if($langRelation = ProductsFunctions::getPostOrProductLangRelation($productId)) {
    
                //-----------------------
                // Poisk vseh drygih yazukovuh versij etogo tovara i proverka ne v korzine li oni
                $result = ProductsFunctions::searchInForeach_for_is_product_in_cart($productId, $langRelation);
                if($result) return true;
                //-----------------------
            }
        }
    
        return false;
    }

    //------------------------------------------------

    public static function searchInForeach_for_is_product_in_cart($productId, $langRelation){

        global $productsIdsInCart;

        if(
            !function_exists('pll_default_language') || 
            !function_exists('pll_current_language')
        ){

            if(isset($productsIdsInCart[$productId])) return true;
        }else{

            $langOfCurProduct = pll_get_post_language($productId);
            // Poisk vseh drygih yazukovuh versij etogo tovara i proverka ne v korzine li oni
            foreach($langRelation as $lang => $pid){
        
                if($lang != $langOfCurProduct){
        
                    if(isset($productsIdsInCart[$pid])) return true;
                }
            }
        }
    
        return false;
    }

    //------------------------------------------------

    public static function woocommerce_get_pa_label_agb($value){

        if(!preg_match('#^pa_#', $value)) return $value;
        $value = preg_replace('#^pa_#', '', $value);
        $value = trim($value);
    
        global $wpdb;
    
        $table = $wpdb->prefix.'woocommerce_attribute_taxonomies';
        $sql = '
        SELECT `attribute_label` FROM `'.$table.'` WHERE `attribute_name` = "'.$value.'"
        ';
    
        $data = $wpdb->get_row($sql);
        if(!$data) return $value;
    
        return $data->attribute_label;
    }

    //------------------------------------------------

    public static function quick_view_button_agb( $product_id ) {

        $quick_view_layout = get_theme_mod( 'shop_product_quickview_layout', 'layout1' ); 
        if( 'layout1' == $quick_view_layout ) {
            return '';
        } 
        
        ob_start();
        ?>
    
        <span  
        class="button 
        agb33da-quick-view-show-on-hover 
        agb33da-quick-view agb33da-quick-view-<?php echo esc_attr( $quick_view_layout ); ?>" 
        aria-label="<?php /* translators: %s: quick view product title */ 
        echo esc_attr( sprintf( __( 'Quick view the %s product', LANG_THEME_KEY_AGB ), 
        get_the_title( $product_id ) ) ); ?>" 
        data-product-id="<?php echo absint( $product_id ); ?>" 
        data-nonce="<?php echo esc_attr( wp_create_nonce( 'agb33da-qview-nonce' ) ); ?>">
            <?php esc_html_e( 'Quick View', LANG_THEME_KEY_AGB); ?>
        </span>
    
        <?php
        $output = ob_get_clean();
        return $output;
    }

    //------------------------------------------------

    public static function shop_loop_product_title($product_id) {
        
        $title = get_the_title($product_id);

        return 
        '<h2 class="woocommerce-loop-product__title">
        <a class="agb33da-wc-loop-product__title" 
        href="'. 
        esc_url( 
            get_the_permalink( 
                $product_id
            ) 
        ) .'">'. 
        $title.
        '</a></h2>';
    }

    //------------------------------------------------

    public static function get_onsale_percentage_agb($product){

        $sale_price = $product->get_sale_price();
        $regular_price = $product->get_regular_price();
        $percentage = 0;
    
        if ( $sale_price < $regular_price ) {
            $percentage = round( ( ( (float) $regular_price - (float) $sale_price ) / (float) $regular_price ) * 100 );
        }
    
        return $percentage;
    }
}