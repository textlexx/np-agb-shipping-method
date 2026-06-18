<?php

namespace NpAgbShippingMethod;

class SubMenuCenter{

    //-----------------------------------------------------------------------

    public static function getAbsolutePagePath(string $pageFileName):string{

        return realpath(__DIR__.'/../pages/'.$pageFileName.'.php');
    }

    //-----------------------------------------------------------------------

    public static function standartPage():?bool{
        
        if(!NPShippingMethod::standartAccessMessageForPages()) return false;

        echo TranslatorCenter::run('Change parameter of the function which adding submenus.');
        return null;
    }

    //-----------------------------------------------------------------------
}

?>