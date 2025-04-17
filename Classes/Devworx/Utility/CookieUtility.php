<?php

namespace Devworx\Utility;

class CookieUtility {
  
  const 
    COOKIE = 'devworx',
    EXPIRES = 3600 * 24;
  
  /**
   * Gets the folder of the current script
   *
   * @return string
   */
  static function getProgramFolder(): string {
    $tokens = explode('/',$_SERVER['SCRIPT_NAME']);
    array_pop($tokens);
    return implode('/',$tokens);
  }
  
  /**
   * Checks if the devworx cookie is set
   *
   * @return bool
   */
  static function has(): bool {
    return array_key_exists(self::COOKIE,$_COOKIE);
  }
  
  /**
   * Sets the devworx cookie value
   *
   * @param string $value The cookie value to set (user hash)
   * @return bool
   */
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
  
  /**
   * Refreshs the devworx cookie
   *
   * @return bool
   */
  static function refresh(): bool {
    return self::set( self::get() );
  }
  
  /**
   * Gets the devworx cookie value
   *
   * @return string|null
   */
  static function get(): ?string {
    return self::has() ? $_COOKIE[self::COOKIE] : null;
  }
  
  /**
   * Unsets the devworx cookie by altering the expire date
   *
   * @return bool
   */
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
