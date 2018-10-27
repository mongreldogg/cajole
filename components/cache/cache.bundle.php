<?php

/**
 * Zend OPCache wrapper for Cajole Framework
 */

namespace Bundle;

class Cache {

    public static function Warmup(){

        $log = "";

        $used = opcache_reset();
        if(!$used) $log .= "Zend OPCache is not used!\r\n";
        else $log .= "Successful Zend OPCache reset!\r\n";

        opcache_compile_file('core/core.php');

        $files = glob('config/{{*/}*,*}*config.php', GLOB_BRACE);
        if(is_array($files))
        foreach(array_reverse($files) as $file) {
            $log .= "Compiling: $file:\r\n";
            $result = opcache_compile_file($file);
            if($result) 
            $log .= "DONE\r\n\r\n"; 
            else $log .= "FAIL\r\n\r\n";
        }
        
        $files = glob('bundles/{{*/}*,*}*.bundle.php', GLOB_BRACE);
        foreach(array_reverse($files) as $file) {
            $log .= "Compiling: $file:\r\n";
            $result = opcache_compile_file($file);
            if($result) 
                $log .= "DONE\r\n\r\n"; 
            else $log .= "FAIL\r\n\r\n";
        }
        
        $files = glob('events/{{*/}*,*}*.event.php', GLOB_BRACE);
        foreach($files as $file) {
            $log .= "Compiling: $file:\r\n";
            $result = opcache_compile_file($file);
            if($result) 
                $log .= "DONE\r\n\r\n"; 
            else $log .= "FAIL\r\n\r\n";
        }
        
        $files = glob(BASE_DIR.'/routes/{{*/}*,*}*.php', GLOB_BRACE);
        foreach($files as $file) {
            $log .= "Compiling: $file:\r\n";
            $result = opcache_compile_file($file);
            if($result) 
                $log .= "DONE\r\n\r\n"; 
            else $log .= "FAIL\r\n\r\n";
        }

        return $log;

    }

    public static function Reset(){
        $used = opcache_reset();
        if(!$used) return "Zend OPCache is not used!\r\n";
    }

}