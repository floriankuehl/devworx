<?php

namespace Devworx\Utility;

use \Api\Utility\ApiUtility;

class AuthUtility {
  
  /**
   * Creates a user hash from a username and a password
   *
   * @param string $userName The username
   * @param string $password The password
   * @return string
   */
  public static function createUserHash(string $userName, string $password): string {
	  return md5("{$userName}|{$password}");
  }
  
  /**
   * Checks if a given string is MD5 and checks if a user with this login exists
   *
   * @param string $hash The login hash
   * @return bool
   */
  public static function isUserHash(string $hash): bool {
    global $DB;
    if( StringUtility::isMd5($hash) ){
      $result = $DB->query("SELECT COUNT(*) FROM user WHERE login='{$hash}' LIMIT 1;",true,MYSQLI_NUM);
      return (bool) $result[0];
    }
    return false;
  }
  
  /**
   * Retrieves a user by its login hash
   *
   * @param string $hash The user login hash
   * @return array|null
   */
  public static function getUserByHash(?string $hash): ?array{
    if( is_null($hash) || empty($hash) )
      return null;
    global $DB;
    $hash = $DB->escape($hash);
    return $DB->query("SELECT * FROM user WHERE login='{$hash}' LIMIT 1;",true,MYSQLI_ASSOC);
  }
  
  /**
   * Sets the last login timestamp of a user by hash
   *
   * @param string $hash The user login hash
   * @return bool
   */
  public static function setLastLogin(?string $hash): bool {
    if( is_null($hash) || empty($hash) )
      return false;
    global $DB;
    $hash = $DB->escape($hash);
    return (bool)$DB->query("UPDATE user SET lastLogin=CURRENT_TIMESTAMP WHERE login='{$hash}' LIMIT 1;",true,MYSQLI_NUM);
  }
  
  /**
   * Retrieves the current user hash by the given context
   *
   * @return string|null
   */
  public static function getStoredHash(): ?string {
    return \Devworx\Frontend::isApiContext() ? 
      ApiUtility::getKey() : 
      CookieUtility::get();
  }
  
  /**
   * Retrieves the current user by the stored hash
   *
   * @return array|null
   */
  public static function getCurrentUser(): ?array {
    $hash = self::getStoredHash();
    return is_null($hash) ? $hash : self::getUserByHash($hash);
  }
  
  /**
   * Checks if a devworx cookie exists
   *
   * @return bool
   */
  public static function cookie(): bool {
    $hash = CookieUtility::get();
    return is_string($hash) && !empty($hash);
  }
  
  /**
   * Performs a login based on POST data
   *
   * @return bool
   */
  public static function post():bool {
    $result = $_SERVER['REQUEST_METHOD'] === 'POST';
    if( $result ){
      $result = ArrayUtility::hasValue($_POST,'username') && 
        ArrayUtility::hasValue($_POST,'password');
      if( $result ){
        $payload = self::createUserHash($_POST['username'], $_POST['password']);
        $result = self::isUserHash( $payload ) && 
          CookieUtility::set( $payload ) && 
          self::setLastLogin( $payload );
      }
    }
    return $result;
  }
  
  /**
   * Performs a logout by destroying the session and the cookie
   *
   * @return void
   */
  public static function lock(): void {
	SessionUtility::stop();
	CookieUtility::unset();
  }
}


?>
