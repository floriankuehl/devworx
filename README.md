<h1>Devworx RP</h1>

<strong>PHP framework for rapid prototyping</strong>
<p>This structure facilitates other developers to quickly get into the project and efficiently create prototypes.</p>
<p>No 3rd Party PHP Libraries are used.</p>
<p>The software can be customized and extended as desired, as it is intended to save time for developers.</p>

<h2>Inspiration</h2>
<p>As a developer, I wrote the same codes over and over for kickstarting standalone webapps. I repeated the same time-consuming steps for every prototype project. Sure, I could have used bigger frameworks like Typo3 (big inspiration for me), because of future compatibility and flexibility, but for small prototypes, i wanted to have my own reusable framework. Without the pain of complex version updates. Devworx is my personal framework, that was shaped by experience through many years of development.</p>

<h2>Architecture</h2>

<h3>Context-Based MVC Solution</h3>
<p>The solution can be controlled in the frontend and API context via controllers and actions. The <code>LoginHash</code> can be provided by Cookie or via request header.</p>

<h3>Database</h3>
<p>The <code>Database</code> class serves as a MySQLi database interface and is accessible via <code>global $DB</code>. It contains functions like <code>query, statement, get, add, put and remove.</code></p>
<p>Database entries in Devworx have a basic structure that allows for easy data handling and mapping to any <code>AbstractModel</code>.</p>
<ul>
  <li><b>uid</b> <span>PK int (Unique ID of the row)</span></li>
  <li><b>cruser</b> <span>int (UserID of the creation user)</span></li>
  <li><b>created</b> <span>timestamp (Creation timestamp)</span></li>
  <li><b>updated</b> <span>timestamp (Timestamp of last update)</span></li>
  <li><b>hidden</b> <span>tinyint (Hidden-Flag)</span></li>
  <li><b>deleted</b> <span>timestamp (Deletion timestamp)</span></li>
</ul>

<h4>Repository</h4>
<p>The <code>Repository</code> class enables caching of schemas and easy access to specific database tables in controller context. It contains functions like <code>findBy, findOneBy, findAll, put, add, remove and delete</code>. System fields such as <code>hidden</code> and <code>deleted</code> are automatically added to the queries. Results can be mapped to Model-classes automatically.</p>

<h4>Extending results</h4>
<p>The results of database queries are generally represented with associative arrays. Classes like <code>ArrayWalker</code> allow enriching relational data such as MySQL results to enable multidimensional results and additional data. Models can also be used to modify result handling.</p>

<h3>API Context</h3>
<p>Devworx comes with the idea to use itself in an API style context, based on JSON data. The login hash can be provided via HTTP header to access the full capacity. The public resources contain a JavaScript implementation for easy access.</p>

<h3>Access security</h3>
<p>The codes like classes and private resources are located in the root folder of the solution, but the frontend files are located in the <code>Public</code> folder, aswell as the JavaScript classes for all custom HTML elements and the API integration. The domain for the projects should point to the <code>Public</code> folder.</p>

<h4>Security Features</h4>
<p><code>AuthUtility</code> provides basic security functions that can be quickly integrated.</p>

<h4>Credential Hashing</h4>
<p>The <code>LoginHash</code> consists of the login name and password. The passwords are NOT stored in the database, only the hashes. The hash is used directly to retrieve user information from the database, also as the API key for JSON access.</p>

<h3>Rendering</h3>
<p>The rendering is done via <code>AbstractRenderer</code>. The <code>FluidRenderer</code> enables string templating with placeholders. The <code>JSONRenderer</code> allows direct output in JSON. ViewHelpers are (yet) not available.</p>

<h3>Templating</h3>
<p>The solution uses the principle of layout, template, and partial. The layout provides the outer frame that is the same for all results and renders the corresponding template of the requested action. Partials are small code snippets that can be used in templates and layouts, as well as in the actions, to generate output.</p>

<h3>Resources</h3>
<p>A distinction is made between private and public resources. Private resources are, for example, layouts, templates, and partials. Public resources are all styles, images, icons, and scripts. All private resources are located in the <code>root/Resources</code> folder. The public resources are located in the <code>root/Public/Resources</code> folder.</p>

<h3>Configuration</h3>
<p>The solution can be configured via JSON files, that are stored in the Configuration folder. These files are used to configurate the system itself, aswell as as the frontend page. See <code>Resources/Layouts/Page.php</code> and <code>Configuration/Frontend.json</code></p>

<h3>Styling</h3>
<p><code>Bootstrap 5.3</code> and <code>Material Icons from Google</code> are used by standard. But it is easy to change the framework's styling via the configuration files.</p>

<h3>Custom HTML Elements</h3>
<p>The solution contains its own HTML elements to handle lists and formats. These are imported via JavaScript module. See Public/Resources/Scripts.</p>

<h3>JavaScript Addons</h3>
<p>The solution contains addons for dialogs, toggle logic, confirming, formatting and a example for providing serverside rendered templates or partials.</p>

<h3>Caches</h3>
<p>The solution features automatic file caching of MySQL database schemas. This allows type usage without database queries.</p>

<h2>Advantages</h2>

<h3>Modularity</h3>
<p>The class structure makes it easy to find and change specific functions.</p>

<h3>Reusability</h3>
<p>Through inheritance and utility classes, many parts of the code can be reused, which shortens development time.</p>

<h3>Clarity</h3>
<p>The division into different classes and files helps to keep the code clear. Every class and every function has documentation blocks.</p>

<h3>Extensibility</h3>
<p>New functions can be easily implemented by adding new classes or inheriting existing classes. The classes are loaded automatically via use-statement. Namespace and folder structure are matching for easy location of the files.</p>

<h3>Error Handling</h3>
<p>Utility classes such as <code>DebugUtility</code> make implementing debugging and error handling logic easier.</p>

<h2>Roadmap</h2>
<p>The solution will be expanded and supplemented with functionality over time. Here are some ideas:</p>
<ul>
  <li>L10n Integration</li>
  <li>Better Renderer</li>
  <li>XML and JSON Utilities</li>
  <li>Dynamic alias-based query building</li>
  <li>Editors for models, menus and users</li>
  <li>User groups and permission management</li>
  <li>PSR integration</li>
</ul>
<p>Over time, more repositories will be released that are based on Devworx.</p>
