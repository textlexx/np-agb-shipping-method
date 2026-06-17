<?php

namespace NpAgbShippingMethod;

Files_Include_Functions::include_html_file_css('style');


if(isset($_GET['page'])){

    if($_GET['page'] == 'products_filters_plugin_base_page_agb'){

        require_once(SubMenuCenter::getAbsolutePagePath('base_page'));
            
        Files_Include_Functions::include_html_file_js('commonAdminFunc');
        Files_Include_Functions::include_html_file_js('select_image');
        Files_Include_Functions::include_html_file_js('select_image_2');
        Files_Include_Functions::include_html_file_js('rename_delete_groups');
        Files_Include_Functions::include_html_file_js('hide-show-blocks-js');
    }else if($_GET['page'] == 'clean_cache_products_filters_plugin_base_page_agb'){
    
        require_once(SubMenuCenter::getAbsolutePagePath('clean_cache_btn'));

        Files_Include_Functions::include_html_file_js('commonAdminFunc');
        Files_Include_Functions::include_html_file_js('clean_caches');
    }
}

?>