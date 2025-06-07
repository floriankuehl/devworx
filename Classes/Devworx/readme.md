<h2>Devworx</h2>
<p>This folder contains all Framework-related files like:</p>
<ul>
  <li><code>AbstractController</code> handles controller action and <code>View</code> logic</li>
  <li><code>AbstractModel</code> provides a basic data structure for <code>Models</code></li>
  <li><code>AbstractViewHelper</code> provides a basic logik for <code>ViewHelpers</code> (exp)</li>
  <li><code>ConfigManager</code> handles JSON configuration by files</li>
  <li><code>Database</code> handles <code>MySQLi</code></li>
  <li><code>Frontend</code> handles MVC processing, <code>Configurations</code>, <code>Controllers</code>, <code>Views</code>, <code>Sessions</code> and <code>Cookies</code></li>
  <li><code>Html</code> handles PHP HTML generation</li>
  <li><code>Repository</code> handles <code>Database</code> and <code>Models</code></li>
  <li><code>Request</code> handles access to request related variables</li>
  <li><code>View</code> handles rendering via <code>Renderer</code>, provides static functions for <code>Layout</code>, <code>Template</code> and <code>Partial</code></li>
</ul>

<h2>Renderer</h2>
<p>This folder contains all Renderers like:</p>
<ul>
  <li><code>AbstractRenderer</code> handles rendering</li> 
  <li><code>ConfigRenderer</code> handles Configuration driven frontend HTML rendering</li>
  <li><code>FluidRenderer</code> handles template based rendering with placeholder syntax like {item.value}</li>
  <li><code>JsonRenderer</code> handles template based JSON rendering from actions</li>
</ul>

<h2>Utility</h2>
<p>This folder contains all utility classes like:</p>
<ul>
  <li><code>ArrayUtility</code> handles <code>Arrays</code></li>
  <li><code>AuthUtility</code> handles <code>Authentication</code></li>
  <li><code>BuildUtility</code> handles code generation for <code>Models</code>, <code>Controllers</code> and <code>Repositories</code></li>
  <li><code>CookieUtility</code> handles the <code>System Cookie</code></li>
  <li><code>DebugUtility</code> handles better <code>Debug</code> messages</li>
  <li><code>EmailUtility</code> handles <code>E-Mail</code> links</li>
  <li><code>FileUtility</code> handles <code>Files</code>, provides upload, download and show mechanics</li>
  <li><code>FlashMessageUtility</code> handles <code>Flash Messages</code></li>
  <li><code>GeneralUtility</code> handles <code>Redirects</code> and <code>Instanciating</code>, provides functions for accessing _SESSION, _COOKIE, _GET, _POST and _REQUEST</li>
  <li><code>ModelUtility</code> handles <code>Model</code> mapping</li>
  <li><code>OPCacheUtility</code> handles <code>PHP opcache</code> (exp)</li>
  <li><code>PageUtility</code> handles <code>Page</code> modification using <code>Frontend</code> configuration, e.g. changing the page title in an action</li>
  <li><code>SessionUtility</code> handles <code>Session</code> variable access </li>
  <li><code>StringUtility</code> handles <code>String</code> cleaning, formatting and regex checks</li>
</ul>

<h2>Walkers</h2>
<p>This folder contains classes for array extending, like extending MySQL results into multidimensional arrays.</p>
<ul>
  <li><code>AbstractSubsetWalker</code> provides data from a sublist of items 1:n</li>
  <li><code>AbstractWalker</code> provides data for extending arrays item by item</li>
  <li><code>Walkers</code> provides functions for applying sets of Walkers</li>
</ul>
