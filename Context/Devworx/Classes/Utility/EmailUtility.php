<?php

namespace Devworx\Utility;

class EmailUtility {
  
  /**
   * Creates a mailto url for frontend use
   * 
   * @param string $receiver The receiver of the mail
   * @param string $subject The subject of the mail
   * @param string $body The body of the mail
   * @return string
   */
  public static function mailtoUrl(string $receiver,string $subject='',string $body=''): string {
    $subject = rawurlencode($subject);
    $body = rawurlencode($body);
    return "mailto:{$receiver}?subject={$subject}&body={$body}";
  }
  
}

?>
