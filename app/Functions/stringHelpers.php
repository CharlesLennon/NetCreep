<?php

function multiExplode(string $data, $delimiters = []) : array
{
    if(!$data){return [];}
    $delimiters = $delimiters ?: [","," ", PHP_EOL, "|", "/", "\\"];
    $makeReady = str_replace($delimiters, $delimiters[0], $data);
    return explode($delimiters[0], $makeReady);
}

function getLastValue(string $data, $delimiters = []) : string
{
    $exploded = multiExplode($data, $delimiters);
    return end($exploded);
}

function escapeQuotes(string $text) : string
{
    //e.g. " hello "world "" -> " hello \"world \""
    $text = str_replace(['&#039;', '&quot;'], ["'", '"'], $text);
    $text = str_replace('"', '\\"', $text);
    $text = str_replace("'", "\\'", $text);
    return $text;
}