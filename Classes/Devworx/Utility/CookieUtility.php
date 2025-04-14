<?php

namespace Devworx\Utility;

class CookieUtility {
  
  const 
    COOKIE = 'devworx',
    EXPIRES = 3600 * 24;
  
  static function getProgramFolder(){
    $tokens = explode('/',$_SERVER['SCRIPT_NAME']);
    array_pop($tokens);
    return implode('/',$tokens);
  }
  
  static function has(): bool {
    return array_key_exists(self::COOKIE,$_COOKIE);
  }
  
  static function set(string $value): bool {
    return empty($value) ? false : setcookie(
      self::COOKIE,
      $value,
      [
        'expires' => time() + self::EXPIRES,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'samesite' => 'strict',
        'secure' => ( array_key_exists('HTTPS',$_SERVER) && ( $_SERVER['HTTPS'] == 'on' ) ),
      ]
    );
  }
  
  static function refresh(): bool {
    return self::set( self::get() );
  }
  
  static function get(): ?string {
    return self::has() ? $_COOKIE[self::COOKIE] : null;
  }
  
  static function unset(): bool {
    return setcookie(
      self::COOKIE,
      "",
      [
        'expires' => 0,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'samesite' => 'strict',
        'secure' => ( array_key_exists('HTTPS',$_SERVER) && ( $_SERVER['HTTPS'] == 'on' ) ),
      ]
    );
  }
}

?>