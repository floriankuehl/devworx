<h1>Devworx</h1>

<strong>PHP Framework für schnelles Prototyping</strong>

<p>Diese Struktur erleichtert es anderen Entwicklern, sich schnell in das Projekt einzuarbeiten und Prototypen effizient zu erstellen.</p>
<p>Es werden keine 3rd Party PHP Libraries benutzt.</p>
<p>Die Software kann nach Wunsch angepasst und erweitert werden, da sie eine Zeitersparnis für Entwickler ermöglichen soll.</p>

<h2>Roadmap</h2>
<p>Die Lösung wird im Lauf der Zeit erweitert und durch Funktionalität ergänzt.</p>
<p>Im Laufe der Zeit werden weitere Repositories veröffentlicht, die Devworx als Basis haben.</p>

<h2>Architektur</h2>
<h3>Kontext-basisierte MVC Lösung</h3>
<p>Die Lösung kann im Frontend- und API-Kontext über Controller und Actions angesteuert werden.</p>

<h3>Credential Hashing</h3>
<p>Der LoginHash besteht aus dem Login-Namen und dem Passwort. Weder Login-Name noch Passwort werden im Klartext in der Datenbank gespeichert. Der Abgleich des Hashs erfolgt direkt mit dem Wert in der Datenbank.</p>

<h3>Datenbank</h3>
<p>Datenbank-Einträge in Devworx haben ein Grundraster, welches ein einfaches Datenhandling ermöglicht.</p>
<ul>
  <li><b>uid</b> <span>PK int (Unique ID of the row)</span></li>
  <li><b>cruser</b> <span>int (UserID of the creation user)</span></li>
  <li><b>created</b> <span>timestamp (Creation timestamp)</span></li>
  <li><b>updated</b> <span>timestamp (Timestamp of last update)</span></li>
  <li><b>hidden</b> <span>tinyint (Hidden-Flag)</span></li>
  <li><b>deleted</b> <span>tinyint (Deleted-Flag)</span></li>
</ul>

<h3>Repository</h3>
<p>Die <code>Repository-Klasse</code> ermöglicht ein direktes Datenbankinterface mit Caching der Schemen. Die Systemfelder wie <code>hidden</code> und <code>deleted</code> werden automatisch hinzugefügt.</p>

<h3>Daten-Behandlung</h3>
<p>Die Ergebnisse der Datenbankabfragen werden grundsätzlich mit assoziativen Arrays dargestellt. Klassen wie die ArrayWalker ermöglichen das Anreichern von relationalen Daten wie MySQL-Ergebnisse um mehrdimensionale Ergebnisse und Zusatzdaten zu ermöglichen. Es können zusätzlich auch Models genutzt werden um die Ergebnisbehandlung zu modifizieren.</p>

<h3>Templating</h3>
<p>Die Lösung bedient sich dem Prinzip von Layout, Template und Partial. Das Layout gibt den äußeren Rahmen vor, der für alle Ergebnisse gleiche ist und rendert das entsprechende Template der angefragten Action. Partials sind kleine Codesnippets, die in Templates und Layouts, sowie in den Actions benutzt werden können um Ausgaben zu erzeugen.</p>

<h3>Rendering</h3>
<p>Das Rendering erfolgt über AbstractRenderer. Der FluidRenderer ermöglicht ein String-Templating mit Platzhaltern. Der JSON-Renderer ermöglicht direkte Ausgabe in JSON. Es stehen (noch) keine ViewHelper zur Verfügung.</p>

<h3>Resourcen</h3>
<p>Hier wird zwischen Private- und Public Resources unterschieden. Die Private Resources sind z.B. Layouts, Templates und Partials. Public sind alle Styles, Bilder, Icons und Scripts.</p>

<h3>Styling</h3>
<p>Hier ist Bootstrap 5.3 im Einsatz, sowie die Material Icons von Google.</p>

<h3>Eigene HTML Elemente</h3>
<p>Die Lösung enthält eigene HTML-Elemente um Listen und Formate zu behandeln. Diese werden per Modul importiert.</p>

<h3>Caches</h3>
<p>Die Lösung verfügt über automatisches File-Caching von MySQL-Datenbankschemen. Dies ermöglicht die Typen-Verwendung ohne Datenbankabfrage.</p>

<h2>Vorteile</h2>
<h3>Modularität</h3>
<p>Die Klassenstruktur ermöglicht es, spezifische Funktionen leicht zu finden und zu ändern.</p>

<h3>Wiederverwendbarkeit</h3>
<p>Durch Vererbung und Utility-Klassen können viele Teile des Codes wiederverwendet werden, was die Entwicklungszeit verkürzt.</p>
  
<h3>Klarheit</h3>
<p>Die Aufteilung in verschiedene Klassen und Dateien hilft dabei, den Code übersichtlich zu halten.</p>

<h3>Erweiterbarkeit</h3>
<p>Neue Funktionen können durch Hinzufügen neuer Klassen oder durch Vererbung bestehender Klassen leicht implementiert werden.</p>

<h3>Fehlerbehandlung</h3>
<p>Utility-Klassen wie DebugUtility und ErrorUtility erleichtern die Implementierung von Debugging- und Fehlerbehandlungslogik.</p>

<h3>Sicherheitsfunktionen</h3>
<p>AuthUtility bietet grundlegende Sicherheitsfunktionen, die schnell integriert werden können.</p>
