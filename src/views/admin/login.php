<?php
/** @var string $title */
?>
<div class="card login">
  <h1>Login Admin</h1>
  <form method="post" action="<?= url(admin_login_path()) ?>">
    <?= csrf_field() ?>
    <label>Username<input name="username" required autofocus autocomplete="username"></label>
    <label>Password<input type="password" name="password" required autocomplete="current-password"></label>
    <button class="btn">Masuk</button>
  </form>
</div>

