<?php

function bladeCompile($html, array $args = [])
{
    if($html == "") { return ""; }
    $bladeName = str_replace([' ','.'],'', microtime()) . '_' . md5($html) . '_' . md5(json_encode($args));
    $bladeFileName = resource_path("views/runtime/{$bladeName}.blade.php");  
    
    $f = fopen($bladeFileName, 'x');
    fwrite($f, $html);
    fclose($f);
    
    $compiled = view("runtime.{$bladeName}", $args)->render();
    unlink($bladeFileName);
    return $compiled;
   
}