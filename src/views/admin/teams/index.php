<div class="row-between">
  <div>
    <h2 class="text-xl font-bold">Tim / Peserta <?= e(sport_label($sport)) ?></h2>
    <?php if (count($categories) > 1): ?>
      <div class="mt-2 flex gap-2">
        <a href="<?= url("admin/$sport/teams") ?>" class="badge <?= empty($catFilter) ? 'finished !font-bold' : '' ?>">Semua Kategori</a>
        <?php foreach ($categories as $ck => $cl): ?>
          <a href="<?= url("admin/$sport/teams?cat=$ck") ?>" class="badge <?= ($catFilter === $ck) ? 'finished !font-bold' : '' ?>"><?= e($cl) ?></a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
  <a class="btn" href="<?= url("admin/$sport/teams/create" . ($catFilter ? "?cat=$catFilter" : '')) ?>">+ Tambah Tim</a>
</div>

<div class="card mt-4 overflow-x-auto">
<table>
  <thead>
    <tr>
      <?php if (count($categories) > 1): ?><th>Kategori</th><?php endif; ?>
      <th>Nama Tim</th>
      <th>Anggota</th>
      <th>Instansi</th>
      <th>Kontak</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($teams as $t): ?>
    <tr>
      <?php if (count($categories) > 1): ?>
        <td><span class="badge"><?= e(category_label($t['category'] ?? 'umum')) ?></span></td>
      <?php endif; ?>
      <td><b><?= e($t['name']) ?></b></td>
      <td>
        <?php
          $mList = parse_team_members($t['members'] ?? null);
        ?>
        <?php if (!empty($mList)): ?>
          <div class="space-y-1 text-xs text-slate-300 min-w-[140px]">
            <?php foreach ($mList as $idx => $mName): ?>
              <div class="flex items-center gap-1.5 leading-snug">
                <span class="text-[10px] text-slate-500 font-mono"><?= $idx + 1 ?>.</span>
                <span class="font-medium text-slate-200"><?= e($mName) ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <span class="text-slate-500 italic text-xs">-</span>
        <?php endif; ?>
      </td>
      <td><?= e($t['institution']) ?></td>
      <td><?= e($t['contact']) ?></td>
      <td class="actions">
        <a class="btn small" href="<?= url("admin/$sport/teams/{$t['id']}/edit") ?>">Edit</a>
        <form class="inline" method="post" action="<?= url("admin/$sport/teams/{$t['id']}/delete") ?>" onsubmit="return confirm('Hapus tim ini?')"><?= csrf_field() ?><button class="btn small danger">Hapus</button></form>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$teams): ?>
    <tr><td colspan="<?= count($categories) > 1 ? 6 : 5 ?>" class="empty">Belum ada tim terdaftar.</td></tr>
  <?php endif; ?>
  </tbody>
</table>
</div>
