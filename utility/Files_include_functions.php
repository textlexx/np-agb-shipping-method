<?php

namespace NpAgbShippingMethod;


class Files_Include_Functions{

    public static function include_html_file_css($file_name){

        $f_path = realpath(__DIR__.'/../pages/styles_files/'.$file_name.'.html');

        if(!is_file($f_path)) return '';

        $content = file_get_contents($f_path);

        echo $content;
    }

    public static function include_html_file_js($file_name){

        $f_path = realpath(__DIR__.'/../pages/js_files/'.$file_name.'.html');

        if(!is_file($f_path)) return '';

        $content = file_get_contents($f_path);

        echo $content;
    }

    public static function include_template_php($file_name){

        $f_path = realpath(__DIR__.'/../template_parts/'.$file_name.'.php');

        if(!is_file($f_path)) return '';

        require_once($f_path);
    }
}

?>