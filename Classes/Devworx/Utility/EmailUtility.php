<?php

namespace Devworx\Utility;

class EmailUtility {
  
  
  public static function mailtoUrl(string $receiver,string $subject='',string $body=''){
    $subject = rawurlencode($subject);
    $body = rawurlencode($body);
    return "mailto:{$receiver}?subject={$subject}&body={$body}";
  }
  
}

?>