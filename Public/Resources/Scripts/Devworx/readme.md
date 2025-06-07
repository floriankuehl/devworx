<h2>Devworx Scripts</h2>

<p>This folder contains all Devworx JavaScript modules like:</p>
<ul>
  <li><code>Api</code> handles API access via <code>fetch</code></li>
  <li><code>CustomElement</code> provides an exported constructor for custom HTML elements.</li>
  <li><code>ElementUtility</code> provides helpful functions for instanciating and registering elements.</li>
  <li><code>Elements</code> provides exports for all element classes in the folder Elements</li>
  <li><code>Format.js</code> provides a formatting class for numbers and dates</li>
  <li><code>Module</code> provides the full loading of the Devworx module</li>
  <li><code>ProjectElements</code> provides a file for adding project based elements</li>
  <li><code>ViewHelpers</code> provides exports for all element classes in the folder ViewHelpers</li>
</ul>

<h2>General</h2>
<p>All Custom Elements and static classes are exported by default, so you can choose an import name for them. The base class for every devworx custom element contains coordination helper, informations about the base class and the namespace for the HTML Tag, aswell as static registering and instanciation. See CustomElement.js</p>

<h2>Module</h2>
<p>To use the Devworx scripts accordingly, you need to include your setup.js via <code>&lt;script type="module" src="Resources/Scripts/setup.js"&gt;&lt;/script&gt;</code></p>
<p>To import the complete module in your setup.js, you can use <code>import * as Devworx from './Devworx/Module.js'</code>. All custom elements will be installed by importing.</p>
<p>You can also do it inline like:</p>
<code>&lt;script type="module" defer&gt;
    import * as Devworx from './Resources/Scripts/Devworx/Module.js'
    console.log( Devworx )
&lt;/script&gt;</code>

<h2>Custom Elements</h2>
<p>To build a custom element, use the following pattern:</p>
<code>import CustomElement from './Resources/Scripts/Devworx/CustomElement.js'
export default class Foo extends CustomElement(HTMLElement){
  static namespace = 'devworx'
  constructor(){
    super()
    //shadow attach or element creation
  }
  init(){
    super.init()
    //connectedCallback
    return this
  }
}</code>

<p>Dont forget to use ElementUtility to register your element.</p>
