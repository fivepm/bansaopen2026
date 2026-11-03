<div class="row-between">
  <div>
    <h2 class="text-xl font-bold text-white">Manajemen Pengguna &amp; Operator Wasit</h2>
    <p class="text-xs text-slate-400 mt-1">Kelola akun Super Admin dan Operator Skor (Wasit) yang bertugas mengupdate live score.</p>
  </div>
  <a class="btn" href="<?= url('admin/users/create') ?>">+ Tambah Operator / Admin</a>
</div>

<div class="card mt-5 overflow-x-auto">
<table>
  <thead>
    <tr>
      <th>Nama Lengkap</th>
      <th>Username</th>
      <th>Peran (Role)</th>
      <th>Hak Akses</th>
      <th>Terdaftar</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($users as $u): ?>
    <?php $isSuper = ($u['role'] === 'superadmin'); ?>
    <tr>
      <td><b><?= e($u['name']) ?></b></td>
      <td><span class="font-mono text-xs text-slate-300"><?= e($u['username']) ?></span></td>
      <td>
        <span class="badge <?= $isSuper ? 'finished !font-bold' : '!bg-blue-950/60 !text-blue-300 !border-blue-500/40 font-semibold' ?>">
          <?= $isSuper ? '👑 Super Admin' : '⚡ Operator Skor' ?>
        </span>
      </td>
      <td class="text-xs text-slate-400">
        <?= $isSuper ? 'Akses Penuh Seluruh Sistem' : 'Hanya Live Panel Skor' ?>
      </td>
      <td class="text-xs text-slate-500"><?= date('d M Y, H:i', strtotime($u['created_at'])) ?></td>
      <td class="actions">
        <a class="btn small" href="<?= url("admin/users/{$u['id']}/edit") ?>">Edit</a>
        <?php if ((int)$u['id'] !== (int)(\App\Core\Auth::user()['id'] ?? 0)): ?>
          <form class="inline" method="post" action="<?= url("admin/users/{$u['id']}/delete") ?>" onsubmit="return confirm('Hapus akun pengguna ini?')">
            <?= csrf_field() ?>
            <button class="btn small danger">Hapus</button>
          </form>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>

