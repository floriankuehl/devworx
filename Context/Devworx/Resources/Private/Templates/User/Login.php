<?php echo \Devworx\Utility\FlashMessageUtility::handle(); ?>
<section class="d-flex flex-column h-100 justify-content-center align-items-center">
  <form 
    method="POST" 
    action="?controller=user&action=login" 
    class="d-flex flex-column p-3 border border-dark"
  >
    <div class="d-flex flex-row flex-wrap mb-2">
      <label for="login_username" class="col-12 col-md-4">Username</label>
      <input id="login_username" required type="text" name="username" class="col-12 col-md-8">
    </div>
    <div class="d-flex flex-row flex-wrap mb-2">
      <label for="login_password" class="col-12 col-md-4">Passwort</label>
      <input id="login_password" required type="password" name="password" class="col-12 col-md-8">
    </div>
    <div class="d-flex flex-row flex-wrap justify-content-center">
      <input type="submit" value="Einloggen" class="btn btn-primary">
    </div>
  </form>
</section>