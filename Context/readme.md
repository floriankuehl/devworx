<h1>The Context folder</h1>
<p>This folder contains every context for Devworx. The framework is located in the folder Devworx and serves a context for Login, Profile and Logout.</p>

<h2>Api Context</h2>
<p>The Api context can communicate with Controllers on JSON basis without rendering. The login hash must be provided in the headers.</p>

<h2>Development</h2>
<p>The Development context allows modification of the database and creation boilerplate codes.</p>

<h2>Devworx</h2>
<p>The Devworx context contains all system classes and serves as a codebase and starting point for every app task.</p>

<h2>Documentation</h2>
<p>The Documentation context contains a automatically generated documentation of Devworx by doxygen and allows file routing to a private folder.</p>

<h2>Frontend</h2>
<p>The Frontend context contains all project-related files that differ from the system contexts.</p>

<p>Context folder structure:</p>
<ul>
  <li>Classes
    <ul>
      <li>Controller</li>
      <li>Repository</li>
      <li>Models</li>
      <li>ViewHelper</li>
    </ul>
  </li>
  <li>Configuration</li>
  <li>Resources
    <ul>
      <li>
        Private
        <ul>
          <li>Layouts</li>
          <li>Templates</li>
          <li>Partials</li>
        </ul>
      </li>
      <li>
        Public
        <ul>
          <li>Scripts</li>
          <li>Styles</li>
          <li>Fonts</li>
          <li>Images</li>
        </ul>
      </li>
    </ul>
  </li>
</ul>
