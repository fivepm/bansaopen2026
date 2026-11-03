<div class="row-between"><h1>Sponsor</h1><a class="btn" href="<?= url('admin/sponsors/create') ?>">+ Tambah Sponsor</a></div>
<div class="card overflow-x-auto">
<table>
  <thead><tr><th>Logo</th><th>Nama</th><th>Status</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($sponsors as $s): ?>
    <tr>
      <td><img src="<?= e(asset($s['logo_path'])) ?>" alt="" class="h-12 w-24 rounded bg-slate-50 object-contain"></td>
      <td><b><?= e($s['name']) ?></b></td>
      <td>
        <form method="post" action="<?= url("admin/sponsors/{$s['id']}/toggle") ?>"><?= csrf_field() ?>
          <button class="badge cursor-pointer <?= $s['is_active'] ? 'finished' : '' ?>" title="Klik untuk mengubah"><?= $s['is_active'] ? '● Aktif' : '○ Nonaktif' ?></button>
        </form>
      </td>
      <td class="actions">
        <a class="btn small" href="<?= url("admin/sponsors/{$s['id']}/edit") ?>">Edit</a>
        <form class="inline" method="post" action="<?= url("admin/sponsors/{$s['id']}/delete") ?>" onsubmit="return confirm('Hapus sponsor ini?')"><?= csrf_field() ?><button class="btn small danger">Hapus</button></form>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$sponsors): ?><tr><td colspan="4" class="empty">Belum ada sponsor.</td></tr><?php endif; ?>
  </tbody>
</table>
</div>

