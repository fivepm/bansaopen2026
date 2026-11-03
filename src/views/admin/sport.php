<div class="card max-w-2xl space-y-4">
  <h2 class="text-lg font-semibold">Sistem Turnamen</h2>
  <p class="muted">Pilih sistem untuk <?= e(sport_label($sport)) ?>. Data bracket dan data grup disimpan terpisah, jadi berganti sistem tidak menghapus data.</p>
  <form method="post" action="<?= url("admin/$sport/system") ?>" class="grid !my-0 !grid-cols-1 gap-3">
    <?= csrf_field() ?>
    <?php foreach (\App\Models\Sports::SYSTEMS as $key => $label): ?>
      <label class="!flex cursor-pointer items-start gap-3 rounded-xl border p-4 <?= $system === $key ? 'border-pri bg-blue-50' : 'border-slate-200' ?> !font-normal">
        <input type="radio" name="system" value="<?= $key ?>" class="!mt-1 !w-auto" <?= $system === $key ? 'checked' : '' ?>>
        <span><b><?= e($label) ?></b><br><span class="muted text-sm"><?= $key === 'knockout' ? 'Kalah langsung tersingkir. Pemenang maju otomatis hingga final.' : 'Tim dibagi ke grup, semua bertemu semua. Klasemen dihitung otomatis dari skor.' ?></span></span>
      </label>
    <?php endforeach; ?>
    <div><button class="btn">Simpan Sistem</button></div>
  </form>
</div>
<div class="grid">
  <a class="card sport" href="<?= url("admin/$sport/teams") ?>"><h2 class="text-lg font-semibold">Kelola Tim</h2></a>
  <a class="card sport" href="<?= url("admin/$sport/matches") ?>"><h2 class="text-lg font-semibold"><?= $system === 'group' ? 'Pertandingan & Skor' : 'Bracket & Skor' ?></h2></a>
  <?php if ($system === 'group'): ?><a class="card sport" href="<?= url("admin/$sport/groups") ?>"><h2 class="text-lg font-semibold">Grup & Klasemen</h2></a><?php endif; ?>
</div>

