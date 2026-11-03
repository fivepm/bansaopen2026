<?php
$sel = fn($v, $cur) => (string)$v === (string)$cur ? 'selected' : '';
$isGroup = $match['stage'] === 'group';
$sys = $match['scoring_system'] ?? 'rubber_set';
?>
<h2 class="mb-3 text-xl font-bold">Atur Pertandingan — <?= e(match_stage_label($match, $total)) ?> #<?= (int)$match['position'] ?></h2>
<form class="card form" method="post" action="<?= url("admin/$sport/matches/{$match['id']}/update") ?>">
  <?= csrf_field() ?>

  <!-- Pilihan Sistem Perhitungan Poin -->
  <div class="rounded-xl border border-slate-800 bg-slate-950 p-4">
    <div class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Sistem Perhitungan Poin Pertandingan</div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <label class="!flex cursor-pointer items-start gap-2.5 rounded-xl border p-3 <?= $sys === 'rubber_set' ? 'border-blue-500 bg-blue-950/20' : 'border-slate-800' ?> !font-normal">
        <input type="radio" name="scoring_system" value="rubber_set" class="!w-auto !mt-1" <?= $sys === 'rubber_set' ? 'checked' : '' ?>>
        <div>
          <span class="font-bold text-white text-sm">Rubber Set (Best of 3)</span>
          <p class="text-xs text-slate-400 mt-0.5">Mencari pemenang 2 set. Jika 1-1, lanjut ke set ke-3 sebagai penentu.</p>
        </div>
      </label>

      <label class="!flex cursor-pointer items-start gap-2.5 rounded-xl border p-3 <?= $sys === 'poin_lanjut' ? 'border-blue-500 bg-blue-950/20' : 'border-slate-800' ?> !font-normal">
        <input type="radio" name="scoring_system" value="poin_lanjut" class="!w-auto !mt-1" <?= $sys === 'poin_lanjut' ? 'checked' : '' ?>>
        <div>
          <span class="font-bold text-white text-sm">Poin Lanjut (Akumulasi 2 Set)</span>
          <p class="text-xs text-slate-400 mt-0.5">Maksimal 2 set. Pemenang ditentukan dari akumulasi total poin kedua set.</p>
        </div>
      </label>
    </div>
  </div>

  <div class="two">
    <?php if ($isGroup): ?>
      <label>Tim 1<input value="<?= e($match['team1_name']) ?>" disabled></label>
      <label>Tim 2<input value="<?= e($match['team2_name']) ?>" disabled></label>
    <?php else: ?>
      <label>Tim 1
        <select name="team1_id"><option value="">— TBD —</option>
          <?php foreach ($teams as $t): ?><option value="<?= $t['id'] ?>" <?= $sel($t['id'], $match['team1_id']) ?>><?= e($t['name']) ?></option><?php endforeach; ?>
        </select></label>
      <label>Tim 2
        <select name="team2_id"><option value="">— TBD —</option>
          <?php foreach ($teams as $t): ?><option value="<?= $t['id'] ?>" <?= $sel($t['id'], $match['team2_id']) ?>><?= e($t['name']) ?></option><?php endforeach; ?>
        </select></label>
    <?php endif; ?>
  </div>

  <!-- Rincian Skor per Set -->
  <div class="rounded-xl border border-slate-800 bg-slate-950 p-4 space-y-3">
    <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Rincian Skor per Set</div>
    <div class="grid grid-cols-3 gap-3">
      <div>
        <span class="block text-xs font-semibold text-center text-slate-400 mb-1">Set 1</span>
        <div class="flex items-center gap-1">
          <input type="number" min="0" name="set1_a" value="<?= (int)($match['set1_a'] ?? 0) ?>" title="Set 1 Tim 1" class="text-center">
          <span class="text-slate-600">-</span>
          <input type="number" min="0" name="set1_b" value="<?= (int)($match['set1_b'] ?? 0) ?>" title="Set 1 Tim 2" class="text-center">
        </div>
      </div>
      <div>
        <span class="block text-xs font-semibold text-center text-slate-400 mb-1">Set 2</span>
        <div class="flex items-center gap-1">
          <input type="number" min="0" name="set2_a" value="<?= (int)($match['set2_a'] ?? 0) ?>" title="Set 2 Tim 1" class="text-center">
          <span class="text-slate-600">-</span>
          <input type="number" min="0" name="set2_b" value="<?= (int)($match['set2_b'] ?? 0) ?>" title="Set 2 Tim 2" class="text-center">
        </div>
      </div>
      <div>
        <span class="block text-xs font-semibold text-center text-slate-400 mb-1">Set 3 (Rubber)</span>
        <div class="flex items-center gap-1">
          <input type="number" min="0" name="set3_a" value="<?= (int)($match['set3_a'] ?? 0) ?>" title="Set 3 Tim 1" class="text-center">
          <span class="text-slate-600">-</span>
          <input type="number" min="0" name="set3_b" value="<?= (int)($match['set3_b'] ?? 0) ?>" title="Set 3 Tim 2" class="text-center">
        </div>
      </div>
    </div>
  </div>

  <div class="two">
    <label>Skor Utama Tim 1 (Set / Total Poin)<input type="number" min="0" name="score1" value="<?= (int)$match['score1'] ?>"></label>
    <label>Skor Utama Tim 2 (Set / Total Poin)<input type="number" min="0" name="score2" value="<?= (int)$match['score2'] ?>"></label>
  </div>

  <div class="two">
    <label>Jadwal<input type="datetime-local" name="scheduled_at" value="<?= $match['scheduled_at'] ? e(date('Y-m-d\TH:i', strtotime($match['scheduled_at']))) : '' ?>"></label>
    <label>Lapangan<input name="court" maxlength="30" placeholder="Court 1" value="<?= e($match['court']) ?>"></label>
  </div>

  <label>Status Pertandingan
    <select name="status">
      <?php foreach (['upcoming', 'live', 'finished'] as $s): ?><option value="<?= $s ?>" <?= $sel($s, $match['status']) ?>><?= e(status_label($s)) ?></option><?php endforeach; ?>
    </select>
  </label>

  <p class="muted text-xs">
    <?= $isGroup
      ? 'Saat status “Selesai”, hasil otomatis masuk ke klasemen grup.'
      : 'Saat status “Selesai”, pemenang otomatis maju ke babak berikutnya di bagan bracket.' ?>
  </p>

  <div class="pt-2"><button class="btn">Simpan Pengaturan</button> <a class="btn ghost" href="<?= url("admin/$sport/matches") ?>">Batal</a></div>
</form>
