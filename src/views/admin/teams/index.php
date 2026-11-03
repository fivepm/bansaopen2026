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
      <td><?= e($t['members']) ?></td>
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
