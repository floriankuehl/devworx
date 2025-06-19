<?php $dialog = $dialog ?? false; ?>
<a 
  class="btn mi-btn d-flex align-items-center" 
  href="{href}" 
  title="{title}" 
  rel="{rel}"
  target="{target}"
  <?php if( is_string($dialog) ) echo 'data-dialog="'.$dialog.'"'; ?>
>
  <span class="mi mi-outline">{icon}</span>
  <small>{label}</small>
</a>