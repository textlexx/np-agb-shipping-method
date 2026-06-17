<?php

namespace NpAgbShippingMethod;

class Instruments{

    public static function getTermLangRelation(int $term_id = 0, bool|string $lang = false){

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

    public static function get_term_by_id_simple(int $term_id = 0){

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

    public static function get_terms_by_ids(string $str_terms_ids){

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
    public static function terms_with_right_lang(bool|string $terms_lang, array $terms_data){

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

            if(isset($new_terms_arr[$term_langs_arr[$terms_lang]])) continue;
            if($term->term_id != $term_langs_arr[$terms_lang]) {

                $save_current_langs_ids .= $term_langs_arr[$terms_lang].',';
                continue;
            }
            $no_finded_any_elements = false;
            $new_terms_arr[$term_langs_arr[$terms_lang]] = $term;
        }

        if(!$terms_data) $no_finded_any_elements = false;

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

    public static function genNextId(string $table_name, string $id_name = 'id', bool $noPrefix = false){
    
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