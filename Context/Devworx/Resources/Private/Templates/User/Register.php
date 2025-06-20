<?php
  //namespace Devworx;
  use \Devworx\Html;
  use \Devworx\Models\User;
  
  $user = isset($user) ? $user : new User();
?>
<form class="p-2" method="POST" action="?controller=Login&action=register">
  <div class="d-flex flex-row mb-2">
    <label for="user_name" class="col-2">Username</label>
    <?php echo Html::detectInput($user,'name',['required'=>'required','maxlength'=>64]); ?>
  </div>
  <div class="d-flex flex-row mb-2">
    <label for="user_email" class="col-2">E-Mail</label>
    <?php echo Html::detectInput($user,'email',['type'=>'email','required'=>null,'maxlength'=>64]); ?>
  </div>
  <div class="d-flex flex-row mb-2">
    <label for="user_phone" class="col-2">Telefon</label>
    <?php echo Html::detectInput($user,'tel',['type'=>'tel','required'=>null,'maxlength'=>64]); ?>
  </div>
  <hr>
  <div class="d-flex flex-row mb-2">
    <label for="user_salutation" class="col-2">Anrede</label>
    <?php echo Html::detectInput($user,'salutation',['maxlength'=>10]); ?>
  </div>
  <div class="d-flex flex-row mb-2">
    <label for="user_firstName" class="col-2">Vorname</label>
    <?php echo Html::detectInput($user,'firstName',['required'=>null,'maxlength'=>64]); ?>
  </div>
  <div class="d-flex flex-row mb-2">
    <label for="user_lastName" class="col-2">Nachname</label>
    <?php echo Html::detectInput($user,'lastName',['required'=>null,'maxlength'=>64]); ?>
  </div>
  <hr>
  <div class="d-flex flex-row mb-2">
    <label for="user_address" class="col-2">Straße</label>
    <?php echo Html::detectInput($user,'address',['required'=>null,'maxlength'=>64]); ?>
  </div>
  <div class="d-flex flex-row mb-2">
    <label for="user_address2" class="col-2">Hausnummer</label>
    <?php echo Html::detectInput($user,'address2',['required'=>null,'maxlength'=>64,'size'=>2]); ?>
  </div>
  <div class="d-flex flex-row mb-2">
    <label for="user_zip" class="col-2">PLZ</label>
    <?php echo Html::detectInput($user,'zip',['required'=>null,'maxlength'=>6,'size'=>6]); ?>
  </div>
  <div class="d-flex flex-row mb-2">
    <label for="user_city" class="col-2">Stadt</label>
    <?php echo Html::detectInput($user,'city',['required'=>null,'maxlength'=>64]); ?>
  </div>
  <div class="d-flex flex-row mb-2">
    <label for="user_country" class="col-2">Land</label>
    <?php echo Html::detectInput(
      $user,
      'country',
      ['required'=>null],
      [
        '' => 'Bitte auswählen',
        'DE'=>'Deutschland',
        'AT' => 'Österreich',
        'UK'=>'United Kingdom',
        'US' => 'United States',
      ]
    ); ?>
  </div>
  <div class="d-flex flex-row mb-2">
    <label for="user_dsgvo" class="col-2">Datenschutz</label>
    <input id="user_dsgvo" type="checkbox" value="1" name="user[dsgvo]" required>
  </div>
  <div>
    <input type="submit" value="Registrieren" class="btn btn-primary">
  </div>
</form>