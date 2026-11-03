<h1><?= e($title) ?></h1>

<form class="card form" method="post" action="<?= url('admin/sponsors/batch') ?>" enctype="multipart/form-data">
  <?= csrf_field() ?>

  <div class="rounded-xl border border-blue-500/30 bg-blue-950/30 p-4 text-xs text-blue-300 space-y-1.5 mb-2">
    <div class="font-bold flex items-center gap-1.5 text-blue-200">
      <span>💡</span>
      <span>Upload Banyak Logo Sekaligus</span>
    </div>
    <p>Anda dapat memilih beberapa file logo sekaligus dari komputer Anda (gunakan tombol <strong>Ctrl</strong> atau <strong>Shift</strong> saat memilih file, misal 15 logo sekaligus).</p>
    <p>Nama sponsor akan <strong>dibuat secara otomatis dari nama file gambar</strong> (contoh: file <code>bank-mandiri.png</code> akan otomatis menjadi sponsor <code>Bank Mandiri</code>). Anda dapat mengedit nama dan statusnya kapan saja.</p>
  </div>

  <label class="block mt-4">
    <span class="block font-bold mb-1">Pilih File Logo Sponsor (Bisa Pilih Banyak) *</span>
    <input type="file" name="logos[]" multiple accept="image/png,image/jpeg" required class="block w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-600 file:text-white hover:file:bg-blue-500 file:cursor-pointer cursor-pointer">
  </label>
  <p class="muted small-t">Format: JPG atau PNG, maksimal 2 MB per file.</p>

  <div class="mt-6 flex items-center gap-3">
    <button class="btn">⚡ Upload Semua Sponsor</button> 
    <a class="btn ghost" href="<?= url('admin/sponsors') ?>">Batal</a>
  </div>
</form>

