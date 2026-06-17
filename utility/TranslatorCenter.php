<?php

namespace spaceProductsFilters;

class TranslatorCenter{

    public static $dirOfLang = 'languages';
    public static $oneCicleWorkPHPScript_agb = 0;

    public static function run($original){

        if(!is_dir(__DIR__.'/../'.TranslatorCenter::$dirOfLang)){

            if(!mkdir(__DIR__.'/../'.TranslatorCenter::$dirOfLang)) return $original;
        }

        $locale = get_locale();
        $dictionary = '';
        $emptyFile = true;
        $files = array();
        $files = glob(__DIR__.'/../'.TranslatorCenter::$dirOfLang.'/*'.$locale.'*.agb', GLOB_BRACE);

        //------------------------------------
        $original2 = preg_quote($original);
        //------------------------------------
        $original2 = preg_replace('#[\t\r\n ]+#', ' ', $original2);

        foreach($files as $e){

            if(!is_file($e)) continue;

            $dictionary = file_get_contents($e);
            // Esli ne pystoj fail to ystanovit false
            if(!ControlCenter::empty($dictionary)) $emptyFile = false;
            else $emptyFile = true;
            //--------------------------------------------------
            // sozdanie imeni faila optimizirovanogo perevoda dlya reg vurazhenij 
            // chtobu na kazhduj perevod ne nagryzhat script pri poiske v ishodnom 
            // faile perevoda ".agb"
            $optimizeFile = preg_replace('#\.agb#', '.optimize', $e);
            //--------------------------------------------------
            
            // esli cukl scripta tolko zapystilsa to sozdat fail optimizacii perevoda
            if(!TranslatorCenter::$oneCicleWorkPHPScript_agb && !$emptyFile) {

                TranslatorCenter::$oneCicleWorkPHPScript_agb = 1;

                $dictionary2 = preg_replace('#[\t\r\n ]+#', ' ', $dictionary);

                $fp = fopen($optimizeFile, "w+");
                flock($fp, LOCK_EX);
                fwrite($fp, $dictionary2);
                flock($fp, LOCK_UN);
                fclose($fp);
            }else{

                // esli cukl scripta yzhe v rabote i eshche ne zakonchilsa to ispolzovat 
                // ranee sozdannuj fail optimizacii perevodov chto bu ne nagryzhat zamenamu probelov
                // kotoroje delaet reg vurazhenie s bolshim kolichestvom strok a yzhe polychit gotovuj 
                // format dlya poiska iz faila optimizacii
                if(is_file($optimizeFile)){

                    $dictionary2 = file_get_contents($optimizeFile);
                }else{

                    $dictionary2 = preg_replace('#[\t\r\n ]+#', ' ', $dictionary);
                }
            }

            if(preg_match("#(\[[\t\r\n ]*key[\t\r\n ]*\][\t\r\n ]*=[\t\r\n ]*\[".$original2."\])([\t\r\n ]*\[[\t\r\n ]*val[\t\r\n ]*\][\t\r\n ]*=[\t\r\n ]*\[([^\]]*)\][\t\r\n ]*\[[\t\r\n ]*end[\t\r\n ]*\])#m", $dictionary2, $finded)){
                
                $original = (!preg_match('#^[\t\r\n ]*$#m', $finded[3])) ? $finded[3] : $original;
                $dictionary = '';
                $dictionary2 = '';
                break;
            }
        }

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
            
            if(!is_file($trsl_file)){

                file_put_contents($trsl_file, $new_string);
            }else{

                $new_string = file_get_contents($trsl_file).$new_string;
                file_put_contents($trsl_file, $new_string, LOCK_EX);
            }

            $new_string = preg_replace('#[\t\r\n ]+#', ' ', $new_string);

            $fp = fopen($optimizeFile, "w+");
            flock($fp, LOCK_EX);
            fwrite($fp, $new_string);
            flock($fp, LOCK_UN);
            fclose($fp);
        }

        return $original;
    }
}