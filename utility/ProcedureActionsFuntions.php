<?php

namespace NpAgbShippingMethod;


function create_db_tables(string $tabName):bool{

    global $wpdb;

    $sqlFile = 
    PATH_CURRENT_PLG_NP_S_MT.
    '/sql_tables/'.$wpdb->prefix.$tabName.'.sql';

    clearstatcache();
    if(!file_exists($sqlFile)) return false;

    $sql = file_get_contents($sqlFile);

    if(!$sql) return false;

    $result = $wpdb->query($sql);

    if(!$result) return false;

    return true;
}

function trsltCommonJs():void{

    echo '
    <script>
    window.translate_for_js_fltr_plugin = {
        "trslt_key_1" : '.
        '"'.TranslatorCenter::run('Some translated text 1.').'",'.
        '"trslt_key_2" : '.
        '"'.TranslatorCenter::run('Some translated text 2.').'"
    }
    </script>
    ';
}



function rightWebProtocol():string{

    $protocol = (strtolower(substr($_SERVER["SERVER_PROTOCOL"], 0, 5)) == 'https') ? 'https' : 'http';

    if($_SERVER["SERVER_PORT"] == 443)
        $protocol = 'https';
    elseif (isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1')))
        $protocol = 'https';
    elseif (
        !empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || 
        !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on'
    )
        $protocol = 'https';
    elseif (!empty($_SERVER["REQUEST_SCHEME"]) && $_SERVER["REQUEST_SCHEME"] == 'https')
        $protocol = 'https';
        
    return $protocol.'://';
}

?>