<?php

define('BASE_DIR', __DIR__);
define('EXECUTION_START_TIME', microtime(true));

require_once "core/core.php";

use Core\CajoleCore;
use Core\CajoleException;

try {
    $files = glob('config/{{*/}*,*}*config.php', GLOB_BRACE);
    if(is_array($files))
    foreach(array_reverse($files) as $file) {include $file;}

    $files = glob('bundles/{{*/}*,*}*.bundle.php', GLOB_BRACE);
    if(is_array($files))
    foreach(array_reverse($files) as $file) {include $file;}

    $files = glob('events/{{*/}*,*}*.event.php', GLOB_BRACE);
    if(is_array($files))
    foreach($files as $file) {@include $file;}

    $files = glob('routes/{{*/}*,*}*.php', GLOB_BRACE);
    if(is_array($files))
    foreach($files as $file) {include $file;}
    @$core = new CajoleCore();
} catch(Throwable $th){
    CajoleException::ExecuteProfiler($th);
} catch(Exception $ex){
    CajoleException::ExecuteProfiler($ex);
} catch(Error $err){
    CajoleException::ExecuteProfiler($err);
}
