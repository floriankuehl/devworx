<!DOCTYPE {doctype}>
<html lang="{lang}">
  <head>
    <meta charset="{charset}">
    {head.metaHttpEquiv}
    {head.meta}
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
      <main>
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
