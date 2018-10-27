<?php

use Core\CajoleException;
use Core\Template;
use Core\Request;
use Core\Response;

CajoleException::TraceProcessor(function($trace){
    
    $trace = strip_tags($trace);
    $parts = preg_split('/\#/', $trace);
    unset($parts[0]);
    $base = str_replace("\\", "\\\\", BASE_DIR);
    $base = str_replace("/", "\\/", $base);
    $out = [];
    foreach($parts as $part){
        $filename = '';
        preg_match('/'.$base.'.*\.php\(\d+\)?/', $part, $filename);
        $filename = $filename[0];
        $line = 0;
        preg_match('/\((\d+?)\)/', $filename, $line);
        $line = $line[1];
        $filename = str_replace('('.$line.')', '', $filename);
        $out[] = "<div class=\"row_hdr\">#$part</div>";
        if((int)$line && file_exists($filename)){
            $handle = @fopen($filename, 'r');
            $startline = $line - 3;
            $endline = $line + 3;
            if($line < 3) {
                $startline = 1;
                $endline = 4;
            }
            for($i = 0; $i < $startline; $i++){
                fgets($handle);
            }
            $lines = [];
            for($i = $startline; $i <= $endline; $i++){
                $lines[$i] = fgets($handle);
            }
            fclose($handle);
            foreach($lines as $idx=>$ln){
                $id = $idx + 1;
                $ln = str_replace("\t", '&nbsp;&nbsp;', $ln);
                if($id != $line)
                    $out[] = "<div class=\"row\">$id: $ln</div>";
                else 
                    $out[] = "<div class=\"row highlight\">$id: $ln</div>";
            }
        } else $out[] = "<div class=\"row\">(couldnt read a file)</div>";
    }
    
    return implode("", $out);
});

CajoleException::Renderer(function($ex){
    $proto = 'http';
    if(Request::IsSafe()) $proto = 'https';
    Template::SetGlobal('base_href', $proto.'://'.Request::Domain().ROOT_DIR);
    $trace = CajoleException::RenderStackTrace($ex);
    $trace = str_replace(BASE_DIR, '', $trace);
    $trace = preg_replace('/^\#/', '<br/>#', $trace);
    header('HTTP/1.1 500 Internal Server Error');
    Response::HTML(Template::Render(Template::Get('profiler'), [
        'message' => $ex->getMessage(),
        'stack_trace' => $trace
    ]));
});