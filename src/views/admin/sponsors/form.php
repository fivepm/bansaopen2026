<?php $edit = (bool)$sponsor; $action = $edit ? "admin/sponsors/{$sponsor['id']}/update" : 'admin/sponsors'; ?>
<h1><?= e($title) ?></h1>
<form class="card form" method="post" action="<?= url($action) ?>" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <label>Nama Sponsor *<input name="name" required maxlength="120" value="<?= old('name', $sponsor['name'] ?? '') ?>"></label>
  <?php if ($edit): ?>
    <div><div class="small-t muted">Logo saat ini</div><img src="<?= e(asset($sponsor['logo_path'])) ?>" alt="" class="h-20 rounded border border-slate-200 bg-slate-50 p-2 object-contain"></div>
  <?php endif; ?>
  <label>Logo Sponsor <?= $edit ? '(kosongkan jika tidak diganti)' : '*' ?>
    <input type="file" name="logo" accept="image/png,image/jpeg" <?= $edit ? '' : 'required' ?>></label>
  <p class="muted small-t">JPG/PNG, maks 2 MB.</p>
  <label class="!flex items-center gap-2 !font-normal">
    <input type="checkbox" name="is_active" value="1" class="!w-auto" <?= (!$edit || $sponsor['is_active']) ? 'checked' : '' ?>> Tampilkan di halaman publik
  </label>
  <div><button class="btn">Simpan</button> <a class="btn ghost" href="<?= url('admin/sponsors') ?>">Batal</a></div>
</form>

