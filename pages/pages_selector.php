<?php

namespace NpAgbShippingMethod;

Files_Include_Functions::include_html_file_css('style');


if(isset($_GET['page'])){

    if($_GET['page'] == 'base_page_np_agb_ship_met'){

        require_once(SubMenuCenter::getAbsolutePagePath('base_page'));
            
        //Files_Include_Functions::include_html_file_js('example');
    }
    /*else if($_GET['page'] == 'example_page_2'){
    
        require_once(SubMenuCenter::getAbsolutePagePath('example_page_2'));

        //Files_Include_Functions::include_html_file_js('example');
    }
    */
}

?>