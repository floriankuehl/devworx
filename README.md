<h1>Devworx Framework</h1>

<strong>PHP framework for rapid prototyping</strong>
<p>This structure facilitates other developers to quickly get into the project and efficiently create prototypes.</p>
<p>No 3rd Party PHP Libraries are used. Doxygen is integrated for automatic HTML documentation.</p>
<p>The software can be customized and extended as desired, as it is intended to save time for developers.</p>

<h2>Inspiration</h2>
<p>As a developer, I wrote the same codes over and over for kickstarting standalone webapps. I repeated the same time-consuming steps for every prototype project. Sure, I could have used bigger frameworks like Typo3 (big inspiration for me), because of future compatibility and flexibility, but for small prototypes, i wanted to have my own reusable framework. Without the pain of complex version updates. Devworx is my personal framework, that was shaped by experience through many years of development.</p>

<h2>Architecture</h2>

<h3>Context-Based MVC Solution</h3>
<p>The solution can be controlled in the frontend and API context via controllers and actions. The <code>LoginHash</code> can be provided by Cookie or via request header.</p>
<p>Devworx comes with the idea to use itself in different contexts, based on JSON configuration files. The available contexts are globally defined in the <code>devworx.php</code> and are used by the <code>\Devworx\Frontend</code> class.</p>
<p>The currently available contexts are <code>Devworx, Frontend, Api, Development and Documentation</code>.</p>
<p>See <code>./Public/.htaccess</code> and <code>Devworx\Cache\HtaccessCache</code> to learn, how the context is determined by URI.</p>

<h3>Configuration</h3>
<p>The solution can be configured via JSON files, based on the provided context, that are stored in the Configuration folder. These files are used to configurate the system itself, as well as as the frontend page.</p>
<p>See <code>Context/Devworx/Resources/Private/Layouts/Page.php</code> and <code>Context/Devworx/Configuration/Context.json</code></p>

<h2>Contexts</h2>

<h3>Devworx Context</h3>
<p>This context is used for internal mechanics like Login, Registering, Password Handling and Cache Management. This is basically the user interaction context</p>

<h3>Frontend Context</h3>
<p>This context is used for the "normal" processing of the user frontend, like a Dashboard or the customized routines of your project.</p>
<p>For standard rendering, the <code>Cascade\Renderer\CascadeRenderer</code> is used.</p>

<h3>API Context</h3>
<p>This context is used for <code>JSON</code> based interaction, with controllers. To use the api context, you can either request <code>./api/</code> or provide the <code>X-Devworx-Context</code> header with the login hash of the user as the value.</p>
<p>As long as the cookie or the header is set, the api can be easily accessed with <code>JavaScript fetch API</code>.</p>
<p>For standard rendering, the <code>Devworx\Renderer\JSONRenderer</code> is used.</p>

<h3>Documentation Context</h3>
<p>This context is used for automated documentation with <code>doxygen</code> and can be accessed by requesting <code>./documentation/</code>.</p>
<p>To ensure doxygen is working, check the <code>Context/Documentation/Configuration/Context.json</code> configuration and <code>Context/Documentation/Configuration/Doxygen.txt</code></p>
<p>If you want to regenerate the documentation, see <code>DoxygenUtility</code> or flush the <code>Documentation cache</code> via the action <code>CacheController::flush</code>.</p>
<p>Internally, the <code>Documentation</code> controller routes the HTML files of the Doxygen Documentation to the frontend via the action <code>Documentation::show</code>.</p>
<p>No renderer is used, because the files are routed directly.</p>

<h3>Development Context</h3>
<p>This context is used for extending or maintaining the project. This includes managing models, performance tracking, file statistics and backups. This context is not finished yet.</p>

<h2>Rendering with Devworx Cascade</h2>
<p>Devworx comes with a powerful rendering engine named <code>Cascade</code>, that allows the user to perform logic and math inside the template, without switching to PHP.</p>
<p>Cascade parses a template character-wise with lookahead to identify nodes for the <code>Abstract Syntax Tree</code>. The code is interpreted into Nodes that can be compiled and evaluated.</p>
<ul>
  <li>Parser
    <ul>
      <li>Character based Lexer mechanic</li>
      <li>Look-ahead</li>
    </ul>
  </li>
  <li>
    Interpreter
    <ul>
      <li>Node based AST</li>
      <li>HTMLNode
        <ul>
          <li>HTMLViewHelpers</li>
        </ul>
      </li>
      <li>Datatypes
        <li>
          <li>Identifier <code>{a}</code></li>
          <li>Constants <code>{true} {false} {null}</code></li>
          <li>Number <code>{1.23}</code></li>
          <li>String <code>{'hello'}</code></li>
          <li>Object <code>{foo:'hello', bar:user.name, baz:false, bim:3.1415}</code></li>
          <li>Array <code>{0:'hello', 1:user.name, 2:false, 3:3.1415}</code></li>
        </li>
      </li>
      <li>
        Context interaction
        <ul>
          <li>Namespace aliasing <code>{namespaces.d = Devworx\ViewHelper}</code></li>
          <li>VariableAccess <code>{user.name}</code></li>
          <li>Assignment <code>{user.role = 'master'}</code></li>
        </ul>
      </li>
      <li>
        Operations
        <ul>
          <li>Unary <code>{a++} {!a} {a ?? b}</code></li>
          <li>Binary <code>{a + b} {a ** b} {a > b} {a !| b}</code></li>
          <li>Ternary <code>{a ? b : c}</code></li>  
          <li>Variable comparison <code>{user.role == 'master' ? 'CHIEF' : 'WORKER'  }</code></li>
          <li>Array/Object merging <code>{foo:'bar'} + {custom:'value'}</code></li>
          <li>Custom operators <code>{a !| b} {a !& b} {a !^ b} //nor, nand, xnor</code></li>
        </ul>
      </li>
      <li>
        Functions
        <ul>
          <li>Named and unnamed arguments</li>
          <li>Regulated PHP System Functions <code>{sqrt()}, {sin(2.31)}, {abs(-4.2)}</code></li>
          <li>Function aliases <code>{tolower('ABC')} //strtolower</code></li>
          <li>ViewHelpers <code>{d:format.currency(value:3.1415,decimals:2)}</code></li>
        </ul>
      </li>
      <li>Result-Piping <code>{user.age * 3.1415 -> d:format.currency(symbol:'YEN') -> tolower()}</code></li>
    </ul>
  </li>
  <li>Compiler constructs a precompiled concatenated array of strings inside a function call for caching</li>
  <li>Evaluator executes the logic of each Node and evaluates the results</li>
  <li>Caching works with a custom file cache, hashed by controller, action, context and variables</li>
</ul>

<h2>Classes</h2>
<p>All the utility classes, as well as some core classes, have static functions for easy reuse in different codes. The <code>Devworx\Frontend</code> class handles the whole architecture statically.</p>
<p>Controllers, Requests, Repositories, Models, Renderers and Views work by instancing.</p>
<p>Classes are loaded automatically by namespace. e.g.: <code>Devworx\Frontend</code> loads <code>Context/Devworx/Classes/Frontend.php</code>.</p>

<h3>Error Handling</h3>
<p>Utility classes such as <code>DebugUtility</code> make implementing debugging and error handling logic easier.</p>
<p>Exceptions are caught by an own Exception-Handler.</p>

<h2>Database</h2>
<p>The <code>Database</code> class serves as a PDO database interface and is accessible by calling it statically. It contains basic functions for database interaction.</p>
<p>Database entries (aka Models) in Devworx have a basic structure that allows for easy data handling and mapping to any <code>AbstractModel</code>.</p>
<ul>
  <li><b>uid</b> <span>PK int (Unique ID of the row)</span></li>
  <li><b>cruser</b> <span>int (UserID of the creation user)</span></li>
  <li><b>created</b> <span>timestamp (Creation timestamp)</span></li>
  <li><b>updated</b> <span>timestamp (Timestamp of last update)</span></li>
  <li><b>hidden</b> <span>tinyint (Hidden-Flag)</span></li>
  <li><b>deleted</b> <span>timestamp (Deletion timestamp)</span></li>
</ul>

<h3>Repository</h3>
<p>The <code>Repository</code> class enables caching of schemas and easy access to specific database tables in controller context. It contains functions like <code>findBy, findOneBy, findAll, put, add, remove and delete</code>. System fields such as <code>hidden</code> and <code>deleted</code> are automatically added to the queries. Results can be mapped to Model-classes automatically.</p>

<h3>Extending results</h3>
<p>The results of database queries are generally represented with associative arrays. Classes like <code>ArrayWalker</code> allow enriching relational data such as MySQL results to enable multidimensional results and additional data. Models can also be used to modify result handling.</p>

<h3>Access security</h3>
<p>The codes like classes and private resources are located in the root folder of the solution, but the frontend files are located in the <code>Public</code> folder, aswell as the JavaScript classes for all custom HTML elements and the API integration. The domain for the projects should point to the <code>./Public</code> folder.</p>

<h4>Security Features</h4>
<p><code>AuthUtility</code> provides basic security functions that can be quickly integrated.</p>

<h4>Credential Hashing</h4>
<p>The <code>LoginHash</code> consists of the login name and password. The passwords are NOT stored in the database, only the hashes. The hash is used directly to retrieve user information from the database, also as the API key for JSON access.</p>

<h2>Rendering</h2>
<p>The rendering is done via <code>AbstractRenderer</code>. Devworx contains multiple types of renderers.</p>
<ul>
  <li>The <code>CascadeRenderer</code> enables Cascade Syntax in templates</li>
  <li>The <code>FluidRenderer</code> enables very simple string templating with placeholders.</li>
  <li>The <code>JSONRenderer</code> allows direct output in JSON</li>
</ul>

<h3>Templating</h3>
<p>The solution uses the principle of layout, template, and partial. The layout provides the outer frame that is the same for all results and renders the corresponding template of the requested action. Partials are small code snippets that can be used in templates and layouts, as well as in the actions, to generate output.</p>
<p>See <code>Devworx\View</code> class for further clearance.</p>

<h3>Resources</h3>
<p>A distinction is made between private and public resources. Private resources are, for example, layouts, templates, and partials. Public resources are all styles, images, icons, and scripts. All private resources are located in the <code>Context/{Context}/Resources/Private</code> folder. The public resources are located in the <code>Context/{Context}/Resources/Public</code> folder. All root paths can be configured in the <code>Context/{Context}/Configuration/Context.json</code> files.</p>

<h3>Styling</h3>
<p><code>Bootstrap 5.3</code> and <code>Material Icons from Google</code> are used by standard. But it is easy to change the framework's styling via the configuration files.</p>

<h3>Custom HTML Elements</h3>
<p>The solution contains its own HTML elements to handle lists and formats. These are imported via JavaScript module. See <code>Context/Devworx/Resources/Public/Scripts</code>.</p>
<p>To ensure easy implementation, the custom elements inherit from <code>AutoRegistering(HtmlElement)</code>, that provide the tag name and the base tag of the new element. All custom elements are loaded by module in <code>/resources/Devworx/Script/Module.js</code>.</p>
<p>The solution contains javascript addons for dialogs, toggle logic, confirming, formatting and a example for providing serverside rendered templates or partials.</p>

<h2>Caches</h2>

<h3>CachesCache</h3>
<p>The <code>Devworx\Cache\CachesCache</code> can collect different caches inside the context by reading <code>Devworx/Configuration/Caches.json</code> and optionally merged with <code>{Context}/Configuration/Caches.json</code>.</p>

<h3>ClassCache</h3>
<p>The <code>Devworx\Cache\ClassCache</code> can collect namespace information for the <code>Devworx\Autoloader</code> by suggesting the basic folder-to-namespace structure.</p>

<h3>ConfigurationCache</h3>
<p>The <code>Devworx\Cache\ConfigurationCache</code> can collect a configuration array from <code>Context/Devworx/Configuration/Context.json</code> and merges with <code>Context/{Context}/Configuration/Context.json</code></p>

<h3>HtaccessCache</h3>
<p>The <code>Devworx\Cache\HtaccessCache</code> can collect htaccess parts from the framework <code>Context/Devworx/Configuration/Global.htaccess</code> and from the contexts <code>Context/{Context}/Configuration/Context.htaccess</code>. Finally writes to Public/.htaccess</p>

<h3>Repository cache</h3>
<p>The <code>Devworx\Repository</code> features automatic file caching of MySQL database schemas. This allows type usage without database queries.</p>

<h3>Models cache</h3>
<p>All models can be refreshed by analysing the MySQL table structure, checking getters and setters and rewriting the model files. use with care!</p>

<h3>Documentation cache</h3>
<p>The code is documented with PHP doc-blocks, and the developer can use doxygen for building a html help structure for the complete code structure. Just provide a path to the doxygen binarys in the <code>Context/Documentation/Configuration/Context.json</code>.</p>
