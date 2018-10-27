<?php

namespace Core;

class CajoleException extends \Exception {

    public function __construct($message){
        parent::__construct($message);
        self::$renderers = [];
    }

    private static $renderers;

    public static function ExecuteProfiler($ex){
        if(is_array(self::$renderers)){
            foreach(self::$renderers as $renderer){
                if(is_callable($renderer)) $renderer($ex);
            }
        } else throw $ex;
    }

    public static function Renderer($proc){
        if(is_callable($proc)) self::$renderers[] = $proc;
    }

    private static $traceProcs;

    public static function TraceProcessor($proc, $trace = null){
        if(is_callable($proc)) self::$traceProcs[] = $proc;
    }

    public static function RenderStackTrace($ex){
        $trace = $ex->getTraceAsString();
        $trace = "#00 ".$ex->getFile()."(".$ex->getLine().")\n".$trace;
        foreach(self::$traceProcs as $proc){
            if(is_callable($proc)) $trace = $proc($trace);
        }
        return $trace;
    }

}