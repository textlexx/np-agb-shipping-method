<?php

namespace NpAgbShippingMethod;

class ExtFunctions{

    public static function getPostOrProductLangRelation(int $post_id = 0):bool|array|object{

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

}