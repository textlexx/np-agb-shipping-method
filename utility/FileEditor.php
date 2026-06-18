<?php

namespace NpAgbShippingMethod;

class FileEditor{

    //-----------------------------------------------------------------------

    public static function save(string $fileParh, string $data):bool{

        clearstatcache();
        $fileParh = realpath($_SERVER['DOCUMENT_ROOT'].'/'.$fileParh);
        if(!is_file($fileParh)) return false;
        if(file_put_contents($fileParh, stripslashes($data)) > 0)  return true;
        return false;
    }

    //-----------------------------------------------------------------------
}

?>