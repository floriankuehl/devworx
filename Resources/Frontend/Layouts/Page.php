<?php 
  header("Content-Type: text/html;charset=utf-8"); 
  header("Content-Script-Type: text/javascript;charset=utf-8"); 
  header("Content-Style-Type: text/css;charset=utf-8");
?>
<!DOCTYPE {doctype}>
<html lang="{lang}">
  <head>
    <meta charset="{charset}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    {head.meta}
    {head.metaHttpEquiv}
    <title>{head.title}</title>
    {head.styles}
    {head.scripts}
    {head.content}
  </head>
  <body>
    <devworx-app>
      <?php 
        if( \Devworx\Frontend::isActiveLogin() ){
          echo \Devworx\View::Partial('Navigation',[
            'controller' => $controller,
            'action' => $action
          ]);
        } 
      ?>
      <main class="px-5 pb-5">
        <devworx-view class="border-2 border-dark rounded p-3 bg-light">
          {body.content}
        </devworx-view>
      </main>
      <?php echo \Devworx\View::Partial('Footer'); ?>
    </devworx-app>
    {body.styles}
    {body.scripts}
  </body>
</html>
