<?php

namespace spaceProductsFilters;

class SubMenuCenter{

    //-----------------------------------------------------------------------

    public static function getAbsolutePagePath($pageFileName){

        return realpath(__DIR__.'/../pages/'.$pageFileName.'.php');
    }

    //-----------------------------------------------------------------------

    public static function standartPage(){
        
        if(!ProductsFiltersAgb::standartAccessMessageForPages()) return false;

        echo TranslatorCenter::run('Change parameter of the function which adding submenus.');
    }

    //-----------------------------------------------------------------------
}

?>