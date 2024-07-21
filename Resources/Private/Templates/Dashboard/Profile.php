<?php
  //namespace Devworx;
  use \Devworx\Html;
  use \Frontend\Models\User;
  
  $user = isset($user) ? $user : new User();
?>
<form 
  method="POST" 
  action="?controller=dashboard&action=profile"
  enctype="multipart/form-data"
  class="p-2 bg-light border rounded shadow col-12 col-md-8 col-lg-6"
>
  <div class="d-flex flex-column flex-md-row mb-2">
    <label for="user_name" class="col-12 col-md-3">Name</label>
    <input 
      id="user_name"
      type="text" 
      name="user[name]" 
      disabled 
      value="{user.name}" 
    >
  </div>
  <div class="d-flex flex-column flex-md-row mb-2">
    <label for="user_email" class="col-12 col-md-3">E-Mail</label>
    <input 
      id="user_email"
      type="email" 
      name="user[email]" 
      required
      value="{user.email}"
    >
  </div>
  <div class="d-flex flex-column flex-md-row mb-2">
    <label for="user_phone" class="col-12 col-md-3">Telefon</label>
    <input 
      id="user_tel"
      type="tel" 
      name="user[tel]" 
      required
      value="{user.tel}"
    >
  </div>
  <hr>
  <div class="d-flex flex-column flex-md-row mb-2">
    <label for="user_salutation" class="col-12 col-md-3">Anrede</label>
    <select id="user_salutation" name="user[salutation]">
      <option value=""<?php echo $user['salutation'] == '' ? ' selected' : ''; ?>></option>
      <option value="Herr"<?php echo $user['salutation'] == 'Herr' ? ' selected' : ''; ?>>Herr</option>
      <option value="Frau"<?php echo $user['salutation'] == 'Frau' ? ' selected' : ''; ?>>Frau</option>
    </select>
  </div>
  <div class="d-flex flex-column flex-md-row mb-2">
    <label for="user_firstName" class="col-12 col-md-3">Vorname</label>
    <input 
      id="user_firstName"
      type="text" 
      name="user[firstName]" 
      value="{user.firstName}" 
    >
  </div>
  <div class="d-flex flex-column flex-md-row mb-2">
    <label for="user_lastName" class="col-12 col-md-3">Nachname</label>
    <input 
      id="user_lastName"
      type="text" 
      name="user[lastName]" 
      value="{user.lastName}" 
    >
  </div>
  <hr>
  <div class="d-flex flex-column flex-md-row mb-2">
    <label for="user_address" class="col-12 col-md-3">Straße</label>
    <input 
      id="user_address"
      type="text" 
      name="user[address]" 
      value="{user.address}" 
    >
  </div>
  <div class="d-flex flex-column flex-md-row mb-2">
    <label for="user_address2" class="col-12 col-md-3">Haus-Nr.</label>
    <input 
      id="user_address2"
      type="text" 
      size="4"
      maxlength="6"
      name="user[address2]" 
      value="{user.address2}" 
    >
  </div>
  <div class="d-flex flex-column flex-md-row mb-2">
    <label for="user_zip" class="col-12 col-md-3">PLZ</label>
    <input 
      id="user_zip"
      type="text" 
      size="8"
      maxlength="6"
      name="user[zip]" 
      value="{user.zip}" 
    >
  </div>
  <div class="d-flex flex-column flex-md-row mb-2">
    <label for="user_city" class="col-12 col-md-3">Stadt</label>
    <input 
      id="user_city"
      type="text" 
      name="user[city]" 
      value="{user.city}" 
    >
  </div>
  <div class="d-flex flex-column flex-md-row mb-2">
    <label for="user_country" class="col-12 col-md-3">Land</label>
    <select id="user_country" name="user[country]">
      <option value=""<?php echo $user['country'] == '' ? ' selected' : ''; ?>>-</option>
      <option value="DE"<?php echo $user['country'] == 'DE' ? ' selected' : ''; ?>>Deutschland</option>
      <option value="AT"<?php echo $user['country'] == 'AT' ? ' selected' : ''; ?>>Österreich</option>
      <option value="CH"<?php echo $user['country'] == 'CH' ? ' selected' : ''; ?>>Schweiz</option>
      <option value="UK"<?php echo $user['country'] == 'UK' ? ' selected' : ''; ?>>United Kingdom</option>
      <option value="US"<?php echo $user['country'] == 'US' ? ' selected' : ''; ?>>United States</option>
    </select>
  </div>
  <div class="d-flex flex-row mb-2">
    <label for="user_css" class="col-12 col-md-3">CSS</label>
    <textarea id="user_css" name="user[css]" class="col-12 col-md-9">{user.css}</textarea>
  </div>
  <div class="d-flex flex-row mb-2">
    <label for="user_image" class="col-3 col-md-2">Profilbild</label>
    <input id="user_image" name="profileImage" type="file" accept="image/png, image/jpeg">
  </div>
  <?php 
    $profileImage = "./Resources/Public/Profiles/" . $user['login'] . '.jpg';
    if( file_exists( $profileImage ) ): 
  ?>
  <div class="d-flex flex-row">
    <img class="d-flex fit-contain" src="<?php echo $profileImage; ?>" alt="Profilbild von {user.name}" width="100" height="100">
  </div>
  <div class="d-flex flex-row mb-2">
    <input id="user_removeImage" type="checkbox" name="removeProfileImage" value="1"> Profilbild entfernen
  </div>
  <?php endIf; ?>
  <div>
    <input type="submit" value="Speichern" class="btn btn-primary">
  </div>
</form>
