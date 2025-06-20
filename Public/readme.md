<h2>The Public folder</h2>
<p>The domain of the system should have this folder as the document root.</p>

<ul>
  <li><code>resources</code> contains symlinks to the public resources of the contexts. The symlinks are created and maintained by the <code>SymlinkCache</code>. To access Devworx scripts use <code>/resources/devworx/Scripts/</code></li>
  <li><code>.htaccess</code> configures the server environment, prevents listing and listens to contextual routes. The htaccess is controlled by the <code>HtaccessCache</code></li>  
  <li><code>index.php</code> initializes the framework and displays whatever the Frontend processes, also dumps a performance log in the public folder</li>
</ul>
