<?php

namespace NpAgbShippingMethod;

class TranslatorCenter{

    public static $dirOfLang = 'languages';
    public static $oneCicleWorkPHPScript = 0;

    public static function run(string $original):string{

        if(!is_dir(__DIR__.'/../'.TranslatorCenter::$dirOfLang)){

            if(!mkdir(__DIR__.'/../'.TranslatorCenter::$dirOfLang)) return $original;
        }

        $locale = get_locale();
        $dictionary = '';
        $emptyFile = true;
        $files = array();
        $files = glob(__DIR__.'/../'.TranslatorCenter::$dirOfLang.'/*'.$locale.'*.agb', GLOB_BRACE);
        $optimizeFile = '';

        //------------------------------------
        $original2 = preg_quote($original);
        //------------------------------------
        $original2 = preg_replace('#[\t\r\n ]+#', ' ', $original2);

        foreach($files as $e){

            clearstatcache();
            if(!is_file($e)) continue;

            $dictionary = file_get_contents($e);
            // If not empty file, set false
            $emptyFile = (!ControlCenter::empty($dictionary)) ? false : true;
            //--------------------------------------------------            
            // Optimized file
            $optimizeFile = preg_replace('#\.agb#', '.optimize', $e);
            //--------------------------------------------------
            
            // Creat optimization file only if in first passage of cicle
            // by remeber in property "TranslatorCenter::$oneCicleWorkPHPScript"
            if(!TranslatorCenter::$oneCicleWorkPHPScript && !$emptyFile) {

                TranslatorCenter::$oneCicleWorkPHPScript = 1;

                $dictionary2 = preg_replace('#[\t\r\n ]+#', ' ', $dictionary);

                $fp = fopen($optimizeFile, "w+");
                flock($fp, LOCK_EX);
                fwrite($fp, $dictionary2);
                flock($fp, LOCK_UN);
                fclose($fp);
            }else{

                // Use optimized file instead double work
                if(is_file($optimizeFile)){

                    $dictionary2 = file_get_contents($optimizeFile);
                }else{

                    $dictionary2 = preg_replace('#[\t\r\n ]+#', ' ', $dictionary);
                }
            }

            // For search matches
            if(preg_match("#(\[[\t\r\n ]*key[\t\r\n ]*\][\t\r\n ]*=[\t\r\n ]*\[".$original2."\])([\t\r\n ]*\[[\t\r\n ]*val[\t\r\n ]*\][\t\r\n ]*=[\t\r\n ]*\[([^\]]*)\][\t\r\n ]*\[[\t\r\n ]*end[\t\r\n ]*\])#m", $dictionary2, $finded)){
                
                $original = (!preg_match('#^[\t\r\n ]*$#m', $finded[3])) ? $finded[3] : $original;
                $dictionary = '';
                $dictionary2 = '';
                break;
            }
        }

        clearstatcache();
        $trsl_file = realpath(__DIR__.'/../'.TranslatorCenter::$dirOfLang);
        $trsl_file = $trsl_file.'/'.$locale.'.agb';

        // If not exists string translate, add it.
        $amountF = count($files);
        if(
            $dictionary != '' || 
            !($amountF > 0) || 
            ($emptyFile && $amountF > 0)
        ){

            $new_string = 
            "\r\n".
            "[key]=[".$original."]\r\n".
            "[val]=[]\r\n".
            "[end]\r\n\r\n";
            
            clearstatcache();
            if(!is_file($trsl_file)){

                file_put_contents($trsl_file, $new_string);
            }else{

                $new_string = file_get_contents($trsl_file).$new_string;
                file_put_contents($trsl_file, $new_string, LOCK_EX);
            }

            clearstatcache();
            if( is_file($optimizeFile) ){

                $new_string = preg_replace('#[\t\r\n ]+#', ' ', $new_string);

                clearstatcache();
                $fp = fopen($optimizeFile, "w+");
                flock($fp, LOCK_EX);
                fwrite($fp, $new_string);
                flock($fp, LOCK_UN);
                fclose($fp);
            }
        }

        return $original;
    }
}