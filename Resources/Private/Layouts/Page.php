<?php 
  header("Content-Type: text/html;charset=utf-8"); 
  header("Content-Script-Type: text/javascript;charset=utf-8"); 
  header("Content-Style-Type: text/css;charset=utf-8");
?>
<!DOCTYPE {doctype}>
<html lang="{lang}">
  <head>
    <meta charset="{charset}">
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
      <main class="px-5">
        {body.content}
      </main>
    </devworx-app>
    {body.styles}
    {body.scripts}
  </body>
</html>
