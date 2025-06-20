<div class="fm-list d-flex flex-column">
<?php 
  foreach( $messages as $i => $message ){
    echo \Devworx\View::Partial('FlashMessages/Show',$message)->render();
  }  
?>
</div>