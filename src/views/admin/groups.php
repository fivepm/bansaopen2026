<?php $groupOptions = fn($cur) => implode('', array_map(
    fn($g) => '<option value="' . (int)$g['id'] . '" ' . ((int)$cur === (int)$g['id'] ? 'selected' : '') . '>' . e($g['name']) . '</option>', $groups)); ?>

<div class="card space-y-3">
  <h2 class="text-lg font-semibold">1. Buat Grup</h2>
  <form method="post" action="<?= url("admin/$sport/groups/create") ?>" class="row !mb-0" onsubmit="return confirm('Membuat grup baru menghapus grup, pembagian tim, dan jadwal grup yang ada. Lanjut?')">
    <?= csrf_field() ?>
    <label>Jumlah grup (Grup A, B, …)<input type="number" name="count" min="1" max="16" value="<?= max(2, count($groups)) ?>"></label>
    <button class="btn"><?= $groups ? 'Buat Ulang Grup' : 'Buat Grup' ?></button>
    <?php if ($groups): ?><button class="btn danger" formaction="<?= url("admin/$sport/groups/reset") ?>">Hapus Semua Grup</button><?php endif; ?>
  </form>
</div>

<?php if ($groups): ?>
<div class="card mt-4 space-y-3">
  <div class="row-between">
    <h2 class="text-lg font-semibold">2. Bagi Tim ke Grup</h2>
    <form method="post" action="<?= url("admin/$sport/groups/auto") ?>" onsubmit="return confirm('Bagi acak semua tim? Jadwal grup yang ada akan dihapus.')"><?= csrf_field() ?><button class="btn small ghost">🎲 Bagi Otomatis (acak)</button></form>
  </div>
  <?php if (!$teams): ?>
    <p class="muted">Belum ada tim. Tambahkan dulu di tab Tim.</p>
  <?php else: ?>
  <form method="post" action="<?= url("admin/$sport/groups/assign") ?>" onsubmit="return confirm('Menyimpan pembagian grup akan menghapus jadwal grup yang ada. Lanjut?')">
    <?= csrf_field() ?>
    <div class="grid !my-0 !grid-cols-1 sm:!grid-cols-2 lg:!grid-cols-3">
      <?php foreach ($teams as $t): ?>
        <label><?= e($t['name']) ?>
          <select name="group[<?= (int)$t['id'] ?>]"><option value="0">— Tanpa grup —</option><?= $groupOptions($assignments[(int)$t['id']] ?? 0) ?></select>
        </label>
      <?php endforeach; ?>
    </div>
    <div class="mt-4"><button class="btn">Simpan Pembagian</button></div>
  </form>
  <?php endif; ?>
</div>

<div class="card mt-4 space-y-3">
  <h2 class="text-lg font-semibold">3. Generate Jadwal (Round Robin)</h2>
  <p class="muted">Setiap tim di satu grup bertemu semua tim lain satu kali. <?= $hasFixtures ? 'Jadwal sudah ada — generate ulang akan menghapus skor grup.' : '' ?></p>
  <form method="post" action="<?= url("admin/$sport/groups/fixtures") ?>" <?= $hasFixtures ? 'onsubmit="return confirm(\'Jadwal & skor grup akan diganti. Lanjut?\')"' : '' ?>>
    <?= csrf_field() ?><button class="btn"><?= $hasFixtures ? 'Generate Ulang Jadwal' : 'Generate Jadwal' ?></button>
    <?php if ($hasFixtures): ?><a class="btn ghost" href="<?= url("admin/$sport/matches") ?>">Input skor →</a><?php endif; ?>
  </form>
</div>

<h2 class="mt-8 text-xl font-bold">Klasemen</h2>
<div class="grid !grid-cols-1 lg:!grid-cols-2">
  <?php foreach ($standings as $s): $g = $s['group']; $rows = $s['rows']; include dirname(__DIR__) . '/partials/standings_table.php'; endforeach; ?>
</div>
<?php endif; ?>

