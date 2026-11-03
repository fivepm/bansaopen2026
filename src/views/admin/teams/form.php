<?php
$edit = (bool)$team;
$action = $edit ? "admin/$sport/teams/{$team['id']}/update" : "admin/$sport/teams";
$currentCat = $team['category'] ?? $_GET['cat'] ?? \App\Models\Sports::defaultCategory($sport);
?>
<h2 class="mb-3 text-xl font-bold"><?= e($title) ?></h2>
<form class="card form" method="post" action="<?= url($action) ?>">
  <?= csrf_field() ?>

  <?php if (count($categories) > 1): ?>
    <label>Kategori Pertandingan *
      <select name="category" required>
        <?php foreach ($categories as $ck => $cl): ?>
          <option value="<?= $ck ?>" <?= $currentCat === $ck ? 'selected' : '' ?>><?= e($cl) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
  <?php else: ?>
    <input type="hidden" name="category" value="<?= e($currentCat) ?>">
  <?php endif; ?>

  <label>Nama Tim / Peserta *<input name="name" required maxlength="120" value="<?= old('name', $team['name'] ?? '') ?>"></label>
  <label>Anggota (pisahkan koma atau baris baru)<input name="members" maxlength="255" value="<?= old('members', $team['members'] ?? '') ?>"></label>
  <label>Instansi / Asal<input name="institution" maxlength="120" value="<?= old('institution', $team['institution'] ?? '') ?>"></label>
  <label>Kontak<input name="contact" maxlength="50" value="<?= old('contact', $team['contact'] ?? '') ?>"></label>
  <div><button class="btn">Simpan</button> <a class="btn ghost" href="<?= url("admin/$sport/teams") ?>">Batal</a></div>
</form>
