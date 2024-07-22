<h1>Devworx</h1>

<strong>PHP Framework for Rapid Prototyping</strong>
<p>This structure facilitates other developers to quickly get into the project and efficiently create prototypes.</p>
<p>No 3rd Party PHP Libraries are used.</p>
<p>The software can be customized and extended as desired, as it is intended to save developers time.</p>

<h2>Roadmap</h2>
<p>The solution will be expanded and supplemented with functionality over time. Here are some ideas:</p>
<ul>
  <li>L10n Integration</li>
  <li>Better Renderer</li>
  <li>XML Utilities</li>
  <li>Dynamic alias-based query building</li>
  <li>Editors for Models, Menus and Users</li>
</ul>
<p>Over time, more repositories will be released that are based on Devworx.</p>

<h3>Want to participate?</h3>
<p>Feel free to submit changes and be part of the project. I will revise the code and create branches for upcoming changes.</p>

<h2>Architecture</h2>

<h3>Context-Based MVC Solution</h3>
<p>The solution can be controlled in the frontend and API context via controllers and actions. The <code>LoginHash</code> can be provided by Cookie or via request header.</p>

<h3>Credential Hashing</h3>
<p>The <code>LoginHash</code> consists of the login name and password. Neither the login name nor the password are stored in plaintext in the database. The hash is directly compared with the value in the database.</p>

<h3>Database</h3>
<p>The <code>Database</code> class serves as a database interface and is accessible via <code>global $DB</code>.</p>
<p>Database entries in Devworx have a basic structure that allows for easy data handling and mapping to any <code>AbstractModel</code>.</p>
<ul>
  <li><b>uid</b> <span>PK int (Unique ID of the row)</span></li>
  <li><b>cruser</b> <span>int (UserID of the creation user)</span></li>
  <li><b>created</b> <span>timestamp (Creation timestamp)</span></li>
  <li><b>updated</b> <span>timestamp (Timestamp of last update)</span></li>
  <li><b>hidden</b> <span>tinyint (Hidden-Flag)</span></li>
  <li><b>deleted</b> <span>tinyint (Deleted-Flag)</span></li>
</ul>

<h3>Repository</h3>
<p>The <code>Repository</code> class enables caching of schemas. System fields such as <code>hidden</code> and <code>deleted</code> are automatically added.</p>

<h3>Data Handling</h3>
<p>The results of database queries are generally represented with associative arrays. Classes like <code>ArrayWalker</code> allow enriching relational data such as MySQL results to enable multidimensional results and additional data. Models can also be used to modify result handling.</p>

<h3>Templating</h3>
<p>The solution uses the principle of layout, template, and partial. The layout provides the outer frame that is the same for all results and renders the corresponding template of the requested action. Partials are small code snippets that can be used in templates and layouts, as well as in the actions, to generate output.</p>

<h3>Rendering</h3>
<p>The rendering is done via <code>AbstractRenderer</code>. The <code>FluidRenderer</code> enables string templating with placeholders. The <code>JSON-Renderer</code> allows direct output in JSON. ViewHelpers are (yet) not available.</p>

<h3>Resources</h3>
<p>A distinction is made between private and public resources. Private resources are, for example, layouts, templates, and partials. Public are all styles, images, icons, and scripts.</p>

<h3>Styling</h3>
<p><code>Bootstrap 5.3</code> and <code>Material Icons from Google</code> are used here.</p>
<h3>Custom HTML Elements</h3>
<p>The solution contains its own HTML elements to handle lists and formats. These are imported via JavaScript module.</p>

<h3>Caches</h3>
<p>The solution features automatic file caching of MySQL database schemas. This allows type usage without database queries.</p>
<h2>Advantages</h2>

<h3>Modularity</h3>
<p>The class structure makes it easy to find and change specific functions.</p>

<h3>Reusability</h3>
<p>Through inheritance and utility classes, many parts of the code can be reused, which shortens development time.</p>

<h3>Clarity</h3>
<p>The division into different classes and files helps to keep the code clear.</p>

<h3>Extensibility</h3>
<p>New functions can be easily implemented by adding new classes or inheriting existing classes.</p>

<h3>Error Handling</h3>
<p>Utility classes such as <code>DebugUtility</code> and <code>ErrorUtility</code> make implementing debugging and error handling logic easier.</p>

<h3>Security Features</h3>
<p><code>AuthUtility</code> provides basic security functions that can be quickly integrated.</p>
