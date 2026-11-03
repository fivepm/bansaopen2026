<h1>Pengaturan Turnamen</h1>
<div class="card max-w-2xl space-y-4">
  <h2 class="text-lg font-semibold">Logo Bansa Open</h2>
  <p class="muted">Logo tampil di navbar dan hero halaman utama. Format JPG/PNG, maks 2 MB. Disarankan PNG transparan.</p>
  <div class="flex h-40 items-center justify-center rounded-xl border-2 border-dashed border-slate-300 bg-slate-50">
    <?php if ($logo): ?><img src="<?= e(asset($logo)) ?>" alt="Logo" class="max-h-32 max-w-full object-contain">
    <?php else: ?><span class="muted">Belum ada logo</span><?php endif; ?>
  </div>
  <form method="post" action="<?= url('admin/settings/logo') ?>" enctype="multipart/form-data" class="grid gap-3">
    <?= csrf_field() ?>
    <label>Pilih file logo<input type="file" name="logo" accept="image/png,image/jpeg" required></label>
    <div><button class="btn"><?= $logo ? 'Perbarui Logo' : 'Unggah Logo' ?></button></div>
  </form>
  <?php if ($logo): ?>
  <form method="post" action="<?= url('admin/settings/logo/delete') ?>" onsubmit="return confirm('Hapus logo?')">
    <?= csrf_field() ?><button class="btn small danger">Hapus Logo</button>
  </form>
  <?php endif; ?>
</div>

