<?php

namespace Devworx\Utility;

use \Api\Utility\ApiUtility;

class AuthUtility {
  
  public static function isUserHash(string $hash): bool {
    global $DB;
    if( StringUtility::isMd5($hash) ){
      $result = $DB->query("SELECT COUNT(*) FROM user WHERE login='{$hash}' LIMIT 1;",true,MYSQLI_NUM);
      return (bool) $result[0];
    }
    return false;
  }
  
  public static function getUserByHash(?string $hash): ?array{
    if( is_null($hash) || empty($hash) )
      return null;
    global $DB;
    $hash = $DB->escape($hash);
    return $DB->query("SELECT * FROM user WHERE login='{$hash}' LIMIT 1;",true,MYSQLI_ASSOC);
  }
  
  public static function setLastLogin(?string $hash): bool {
    if( is_null($hash) || empty($hash) )
      return false;
    global $DB;
    $hash = $DB->escape($hash);
    return (bool)$DB->query("UPDATE user SET lastLogin=CURRENT_TIMESTAMP WHERE login='{$hash}' LIMIT 1;",true,MYSQLI_NUM);
  }
  
  public static function getStoredHash(): ?string {
    return \Devworx\Frontend::isApiContext() ? 
      ApiUtility::getKey() : 
      CookieUtility::get();
  }
  
  public static function getCurrentUser(): ?array {
    $hash = self::getStoredHash();
    return is_null($hash) ? $hash : self::getUserByHash($hash);
  }
  
  public static function cookie(): bool {
    $hash = CookieUtility::get();
    return is_string($hash) && !empty($hash);
  }
    
  public static function post():bool {
    $result = $_SERVER['REQUEST_METHOD'] === 'POST';
    if( $result ){
      $result = ArrayUtility::hasValue($_POST,'username') && 
        ArrayUtility::hasValue($_POST,'password');
      if( $result ){
        $payload = implode("|",[
          ArrayUtility::key($_POST,'username'),
          ArrayUtility::key($_POST,'password')
        ]);
        $payload = md5( $payload );
        $result = self::isUserHash( $payload ) && 
          CookieUtility::set( $payload ) && 
          self::setLastLogin( $payload );
      }
    }
    return $result;
  }
  
  public static function lock(){
    CookieUtility::unset();
    if( !empty(session_id()) )
      session_destroy();
  }
}


?>