<!DOCTYPE {doctype}>
<html lang="{lang}">
  <head>
    <meta charset="{charset}">
    {head.metaHttpEquiv}
    {head.meta}
    <title>{head.title}</title>
    {head.styles}
	{head.modules}
    {head.scripts}
    {head.content}
    <link rel="icon" type="image/x-icon" href="{head.favicon}">
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
        <devworx-view>{body.content}</devworx-view>
      </main>
      <?php echo \Devworx\View::Partial('Footer'); ?>
    </devworx-app>
    {body.styles}
	{body.modules}
    {body.scripts}
  </body>
</html>
