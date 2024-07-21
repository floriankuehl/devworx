<?php

namespace Devworx\Walkers;

class Walkers {
    
  public static function Start(array $extenders, array &$list){
    foreach( $extenders as $i => $extender )
      $extender->Start($list);
  }
  
  public static function End(array $extenders, array &$list){
    foreach( $extenders as $i => $extender )
      $extender->End($list);
  }
  
  public static function Apply(array $extenders, array &$list){
    foreach( $extenders as $j => $extender ){
      $extender->Walk($list);
    }
  }
  
  public static function ApplySub(array $extenders,array &$list,string $key){
    foreach( $list as $i => $row ){
      if( array_key_exists($key,$row) ){
        $sub = $row[$key];
        self::Apply($extenders,$sub);
        $list[$i][$key] = $sub;
      }
    }
  }
  
}