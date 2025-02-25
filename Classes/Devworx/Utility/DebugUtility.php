<?php

namespace Devworx\Utility;

class DebugUtility {
  
  const STYLE = 'Resources/Public/Styles/debug.css';
  
  static function var_dump(
    $var,
    string $title='',
    string $method='',
    int $line=0
  ){
    ob_start();
    var_dump($var);
    $result = ob_get_clean();
    return "<article about=\"{$method}\" class=\"debugger\">".
      ( empty($title) ? "" : "<header>{$title}</header>" ).
      ( empty($method) ? "" : "<small>{$method} on line {$line}</small>" ).
      "<pre>{$result}</pre>".
    "</article>";
  }
  
  static function exception(\Throwable $e){
    $type = $e::class;
    $title = $e->getMessage();
    
    ob_start();
    foreach( $e->getTrace() as $i=>$row ){
      
      $args = array_key_exists('args',$row) ?
        json_encode($row['args'],JSON_PRETTY_PRINT) :
        '';

      echo "<div data-type=\"tracerow\">".
        "<div>".
          "<span data-type=\"index\">".$i."</span>". 
          ( array_key_exists('class',$row) ? "<span data-type=\"class\">".$row['class']."</span>" : '' ). 
          ( array_key_exists('type',$row) ? "<span data-type=\"type\">".$row['type'] ."</span>" : '' ). 
          ( array_key_exists('function',$row) ? "<span data-type=\"function\">".$row['function'] ."</span>" : '' ).
          ( array_key_exists('line',$row) ? "<span data-type=\"line\">".$row['line'] ."</span>" : '' ). 
        "</div>".
        ( empty($args) ? $args : "<pre>{$args}</pre>" ).
      "</div>";
    }
    $content = ob_get_clean();
    
    echo "<style>@import url('" . self::STYLE ."');</style>".
      "<article about=\"{$type}\" class=\"debugger\">".
        ( empty($title) ? "" : "<header>{$type}: {$title}</header>" ).
        ( "<small>".$e->getFile()." on line ".$e->getLine()."</small>" ).
        "<div data-type=\"trace\">{$content}</div>".
      "</article>";
  }
  
}