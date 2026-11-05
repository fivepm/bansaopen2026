<?php
/** Tab navigasi admin per cabor. Butuh $sport. */
$sys = \App\Models\Sports::system($sport);
$path = trim((string)parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$tabs = ['' => 'Ringkasan', 'teams' => 'Tim', 'matches' => $sys === 'group' ? 'Pertandingan & Skor' : 'Bracket & Skor']
    + ($sys === 'group' ? ['groups' => 'Grup & Klasemen', 'group-draw' => '🎰 Undian Grup'] : [])
    + (\App\Controllers\Admin\DrawController::supports($sport) ? ['draw' => '🎰 Undian Tim'] : []);
?>
<div class="mb-4 flex items-center gap-3">
  <span class="text-3xl"><?= \App\Models\Sports::emoji($sport) ?></span>
  <h1 class="!m-0"><?= e(sport_label($sport)) ?></h1>
  <span class="badge"><?= e(\App\Models\Sports::SYSTEMS[$sys]) ?></span>
</div>
<div class="tabs">
  <?php foreach ($tabs as $slug => $label):
      $href = "admin/$sport" . ($slug ? "/$slug" : '');
      $active = $slug === '' ? str_ends_with($path, "admin/$sport") : str_contains($path, "admin/$sport/$slug"); ?>
    <a href="<?= url($href) ?>" class="<?= $active ? 'active' : '' ?>"><?= e($label) ?></a>
  <?php endforeach; ?>
</div>

