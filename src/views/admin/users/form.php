<?php
$edit = (bool)$user;
$action = $edit ? "admin/users/{$user['id']}/update" : 'admin/users';
$currentRole = $user['role'] ?? 'operator';
?>
<h2 class="mb-4 text-xl font-bold text-white"><?= e($title) ?></h2>
<form class="card form" method="post" action="<?= url($action) ?>">
  <?= csrf_field() ?>

  <label>Nama Lengkap Petugas *
    <input name="name" required maxlength="100" placeholder="mis. Budi Santoso (Wasit)" value="<?= old('name', $user['name'] ?? '') ?>">
  </label>

  <label>Username Login *
    <input name="username" required maxlength="50" placeholder="mis. wasit1" value="<?= old('username', $user['username'] ?? '') ?>" <?= $edit ? 'readonly class="opacity-70 cursor-not-allowed"' : '' ?>>
  </label>

  <label>Password <?= $edit ? '(Kosongkan jika tidak ingin mengubah)' : '*' ?>
    <input type="password" name="password" maxlength="100" placeholder="Minimal 4 karakter" <?= $edit ? '' : 'required' ?>>
  </label>

  <label>Peran Pengguna (Role) *
    <select name="role" required>
      <option value="operator" <?= $currentRole === 'operator' ? 'selected' : '' ?>>
        Operator Skor / Wasit (Hanya akses Live Panel)
      </option>
      <option value="superadmin" <?= $currentRole === 'superadmin' ? 'selected' : '' ?>>
        Super Admin (Akses Penuh Seluruh Sistem)
      </option>
    </select>
  </label>

  <div class="rounded-xl border border-slate-800 bg-slate-950 p-3 text-xs text-slate-400 space-y-1">
    <p><b>Catatan Peran:</b></p>
    <p>• <b>Operator Skor:</b> Hanya dapat mengoperasikan Live Panel untuk memencet tombol + dan - skor pada pertandingan live dan menyelesaikan pertandingan.</p>
    <p>• <b>Super Admin:</b> Dapat mengatur logo, sponsor, menambah/mengedit tim, membuat dan mereset bagan pertandingan, serta mengelola pengguna.</p>
  </div>

  <div class="pt-2">
    <button class="btn">Simpan Pengguna</button>
    <a class="btn ghost" href="<?= url('admin/users') ?>">Batal</a>
  </div>
</form>

