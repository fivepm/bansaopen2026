<?php
/** Tab navigasi publik per cabor. Butuh $sport. */
$sys = \App\Models\Sports::system($sport);
$path = trim((string)parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$tabs = ['' => 'Ringkasan'] + ($sys === 'group' ? ['klasemen' => 'Klasemen & Jadwal'] : ['bracket' => 'Bagan']) + ['live' => 'Live', 'hasil' => 'Hasil'];
?>
<div class="mb-4 flex items-center gap-3">
  <span class="text-3xl"><?= \App\Models\Sports::emoji($sport) ?></span>
  <h1 class="!m-0"><?= e(sport_label($sport)) ?></h1>
</div>
<div class="tabs">
  <?php foreach ($tabs as $slug => $label):
      $active = str_ends_with($path, '/' . $sport . ($slug ? "/$slug" : '')) || $path === $sport . ($slug ? "/$slug" : ''); ?>
    <a href="<?= url($sport . ($slug ? "/$slug" : '')) ?>" class="<?= $active ? 'active' : '' ?>"><?= e($label) ?></a>
  <?php endforeach; ?>
</div>

