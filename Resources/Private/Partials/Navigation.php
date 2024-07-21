<?php namespace Devworx; ?>
<nav class="navbar navbar-expand-md navbar-light bg-light position-fixed w-100">
  <ul class="navbar-nav flex-grow-1">
    <li class="nav-item">
      <a class="nav-link" href="<?php echo Frontend::getUrl('project','list'); ?>">Projekte</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="<?php echo Frontend::getUrl('contract','list'); ?>">Verträge</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="<?php echo Frontend::getUrl('domain','list'); ?>">Domains</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="<?php echo Frontend::getUrl('workload','index'); ?>">Auslastung</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="<?php echo Frontend::getUrl('login','logout'); ?>">Logout</a>
    </li>
  </ul>
  <form method="POST" action="<?php echo Frontend::getUrl('cache','flush'); ?>" class="d-flex flex-row px-3">
    <select name="cache">
      <option value="all" selected>Alle Caches</option>
      <?php
        foreach( \Frontend\Controller\CacheController::CACHES as $i => $cache ){
          echo "<option value=\"{$cache}\">" . ucfirst($cache) . " Cache</option>";
        }
      ?>
    </select>
    <button type="submit" class="btn btn-outline">Leeren</button>
  </form>
</nav>