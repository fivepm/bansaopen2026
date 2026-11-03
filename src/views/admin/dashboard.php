<h1>Dashboard</h1>
<div class="grid">
  <?php foreach ($sports as $slug => $s): ?>
    <a class="card sport !text-left" href="<?= url("admin/$slug") ?>">
      <div class="flex items-center gap-3"><span class="text-4xl"><?= $s['emoji'] ?></span><h2 class="text-lg font-semibold"><?= e($s['label']) ?></h2></div>
      <p class="muted small-t mt-2"><?= e(\App\Models\Sports::SYSTEMS[$s['system']]) ?></p>
      <div class="mt-3 grid !my-0 !grid-cols-3 !gap-2 text-center">
        <div><b class="block text-2xl text-pri"><?= $s['teams'] ?></b><span class="text-xs text-slate-500">Tim</span></div>
        <div><b class="block text-2xl text-pri"><?= $s['live'] ?></b><span class="text-xs text-slate-500">Live</span></div>
        <div><b class="block text-2xl text-pri"><?= $s['finished'] ?></b><span class="text-xs text-slate-500">Selesai</span></div>
      </div>
    </a>
  <?php endforeach; ?>
</div>
<div class="grid">
  <a class="card sport" href="<?= url('admin/sponsors') ?>"><h2 class="text-lg font-semibold">Sponsor</h2></a>
  <a class="card sport" href="<?= url('admin/settings') ?>"><h2 class="text-lg font-semibold">Pengaturan &amp; Logo</h2></a>
</div>
