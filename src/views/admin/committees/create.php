<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
  <div>
    <div class="flex items-center gap-2 mb-1">
      <a href="<?= url('admin/committees') ?>" class="text-xs font-semibold text-slate-400 hover:text-white transition">
        ← Kembali ke Daftar Panitia
      </a>
    </div>
    <h1 class="text-2xl font-black text-white">Tambah Anggota Panitia</h1>
    <p class="text-xs sm:text-sm text-slate-400">Masukkan nama anggota panitia dan tentukan jabatan inti atau divisi yang diemban.</p>
  </div>
</div>

<form method="post" action="<?= url('admin/committees') ?>" enctype="multipart/form-data" class="space-y-6">
  <?= csrf_field() ?>

  <!-- Informasi Pokok -->
  <div class="rounded-3xl border border-slate-800 bg-slate-900/90 p-6 sm:p-7 shadow-xl shadow-black/20 space-y-5">
    <h2 class="text-base font-extrabold text-white border-b border-slate-800/80 pb-3">Identitas Panitia</h2>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
      <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">
          Nama Lengkap <span class="text-rose-400">*</span>
        </label>
        <input type="text"
               name="name"
               required
               placeholder="Contoh: Budi Prasetyo"
               class="mt-1.5 w-full rounded-xl border border-slate-700 bg-slate-950/80 py-2.5 px-3.5 text-xs sm:text-sm text-white placeholder-slate-500 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
        <span class="mt-1 block text-[11px] text-slate-500">Nama lengkap anggota panitia yang akan ditampilkan di web.</span>
      </div>

      <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">
          Nomor Kontak (HP / WhatsApp - Opsional)
        </label>
        <input type="text"
               name="phone"
               placeholder="Contoh: 081234567890"
               class="mt-1.5 w-full rounded-xl border border-slate-700 bg-slate-950/80 py-2.5 px-3.5 text-xs sm:text-sm text-white placeholder-slate-500 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
        <span class="mt-1 block text-[11px] text-slate-500">Digunakan untuk koordinasi internal panitia.</span>
      </div>
    </div>

    <!-- Foto Profil Panitia (Upload & Crop) -->
    <div class="pt-3 border-t border-slate-800/80">
      <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">
        Foto Profil Panitia (Opsional)
      </label>
      <div class="flex items-center gap-4">
        <div id="photo-preview-box" class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full border-2 border-slate-700 bg-slate-950 overflow-hidden shadow-inner text-slate-600">
          <i class="bi bi-person-fill text-3xl"></i>
        </div>
        <div class="flex-1 space-y-1.5">
          <input type="file"
                 id="committee-photo-input"
                 name="photo"
                 accept="image/jpeg,image/png,image/jpg"
                 onchange="handlePhotoSelectWithCrop(this)"
                 class="block w-full text-xs text-slate-400 file:mr-3 file:rounded-xl file:border-0 file:bg-blue-600 file:px-3.5 file:py-2 file:text-xs file:font-bold file:text-white hover:file:bg-blue-500 cursor-pointer">
          <input type="hidden" name="cropped_photo" id="cropped-photo-data">
          <span class="block text-[11px] text-slate-500">
            Format: JPG, JPEG, atau PNG (Maks 2 MB). Setelah memilih foto, jendela <strong>Crop Foto (1:1)</strong> akan otomatis terbuka agar foto pas dengan lingkaran profil.
          </span>
        </div>
      </div>
    </div>
  </div>

  <!-- Pilihan Jabatan (Radio Button untuk Inti/BPH & Checkbox untuk Divisi) -->
  <div class="rounded-3xl border border-slate-800 bg-slate-900/90 p-6 sm:p-7 shadow-xl shadow-black/20 space-y-6">
    <div class="border-b border-slate-800/80 pb-3">
      <h2 class="text-base font-extrabold text-white">Struktur &amp; Jabatan Panitia</h2>
      <p class="text-xs text-slate-400">Pilih salah satu jabatan inti (Radio Button) atau pilih opsi "Anggota Divisi / Lainnya" untuk membuka pemilihan checkbox divisi operasional.</p>
    </div>

    <!-- 1. Jabatan Inti / BPH (Radio Button) -->
    <div class="rounded-2xl border border-amber-500/30 bg-amber-950/10 p-5">
      <div class="flex items-center justify-between mb-3 border-b border-slate-800/80 pb-2.5">
        <div class="text-xs font-black uppercase tracking-wider text-amber-400 flex items-center gap-1.5">
          <span>👑</span>
          <span>Jabatan Inti &amp; BPH (Radio Button)</span>
        </div>
        <span class="rounded-full bg-amber-500/10 border border-amber-500/30 px-2.5 py-0.5 text-[10px] font-bold text-amber-300">
          Pilih Salah Satu
        </span>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
        <?php foreach ($coreRoles as $role): ?>
          <label class="flex items-center gap-3 rounded-xl border border-slate-800/80 bg-slate-900/80 p-3 hover:border-amber-500/50 hover:bg-slate-800/60 cursor-pointer transition select-none">
            <input type="radio"
                   name="core_role"
                   value="<?= (int)$role['id'] ?>"
                   onchange="handleRoleSelectionChange()"
                   class="h-4 w-4 border-slate-700 bg-slate-950 text-amber-500 focus:ring-amber-500">
            <span class="text-xs font-bold text-slate-200">
              <?= e($role['name']) ?>
            </span>
          </label>
        <?php endforeach; ?>

        <!-- Opsi Pembuka Checkbox Divisi -->
        <label class="flex items-center gap-3 rounded-xl border-2 border-dashed border-blue-500/50 bg-blue-950/20 p-3 hover:border-blue-400 hover:bg-blue-900/30 cursor-pointer transition select-none">
          <input type="radio"
                 name="core_role"
                 value="other"
                 checked
                 onchange="handleRoleSelectionChange()"
                 class="h-4 w-4 border-slate-700 bg-slate-950 text-blue-500 focus:ring-blue-500">
          <span class="text-xs font-black text-blue-300 flex items-center gap-1.5">
            <span>✨</span>
            <span>Anggota Divisi / Lainnya</span>
          </span>
        </label>
      </div>
    </div>

    <!-- 2. Jabatan Operasional & PJ Cabor (Checkbox Multi-Select) -->
    <div>
      <div class="flex items-center justify-between mb-3">
        <div>
          <h3 class="text-sm font-extrabold text-white flex items-center gap-2">
            <span>📌</span>
            <span>Jabatan Operasional Divisi &amp; PJ Cabor (Checkbox)</span>
          </h3>
          <p class="text-xs text-slate-400">Pilih satu atau lebih jabatan. Otomatis terkunci jika jabatan inti dipilih.</p>
        </div>
        <span id="division-status-badge" class="rounded-full bg-blue-600/15 border border-blue-500/30 px-3 py-1 text-[11px] font-bold text-blue-400 transition-all">
          Multi-Select Aktif
        </span>
      </div>

      <div id="division-checkboxes-container" class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3 transition-opacity duration-300">
        <?php foreach ($divisionGrouped as $divName => $roles): ?>
          <?php $isCabor = ($divName === 'Penanggung Jawab Cabor'); ?>
          <div class="rounded-2xl border <?= $isCabor ? 'border-indigo-500/30 bg-indigo-950/10' : 'border-slate-800/90 bg-slate-950/40' ?> p-4">
            <div class="text-xs font-black uppercase tracking-wider <?= $isCabor ? 'text-indigo-400' : 'text-slate-300' ?> mb-3 flex items-center gap-1.5 border-b border-slate-800/80 pb-2">
              <span><?= $isCabor ? '🏆' : '📌' ?></span>
              <span><?= e($divName) ?></span>
            </div>

            <div class="space-y-2">
              <?php foreach ($roles as $role): ?>
                <label class="flex items-start gap-2.5 rounded-xl border border-slate-800/60 bg-slate-900/60 p-2.5 hover:border-slate-700 hover:bg-slate-800/40 cursor-pointer transition select-none">
                  <input type="checkbox"
                         name="role_ids[]"
                         value="<?= (int)$role['id'] ?>"
                         class="division-role-checkbox mt-0.5 h-4 w-4 rounded border-slate-700 bg-slate-950 text-blue-600 focus:ring-blue-500 focus:ring-offset-slate-900">
                  <span class="text-xs font-medium text-slate-200">
                    <?= e($role['name']) ?>
                  </span>
                </label>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Aksi Tombol -->
  <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
    <a href="<?= url('admin/committees') ?>" class="rounded-xl border border-slate-700 bg-slate-800 px-5 py-2.5 text-xs font-semibold text-slate-300 hover:bg-slate-700 hover:text-white transition">
      Batal
    </a>
    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-6 py-2.5 text-xs font-bold text-white shadow-lg shadow-blue-600/30 hover:bg-blue-500 transition">
      <span>💾</span>
      <span>Simpan Anggota Panitia</span>
    </button>
  </div>
</form>

<!-- Modal & Skrip Pemotong Foto (Cropper) -->
<?php include dirname(__DIR__, 2) . '/partials/crop_modal.php'; ?>

<script>
function handleRoleSelectionChange() {
  const selectedRadio = document.querySelector('input[name="core_role"]:checked');
  const divisionContainer = document.getElementById('division-checkboxes-container');
  const checkboxes = document.querySelectorAll('.division-role-checkbox');
  const badge = document.getElementById('division-status-badge');

  if (!selectedRadio) return;

  if (selectedRadio.value === 'other') {
    // Aktifkan kembali semua checkbox divisi
    divisionContainer.classList.remove('opacity-40', 'pointer-events-none');
    divisionContainer.classList.add('opacity-100');
    checkboxes.forEach(cb => {
      cb.disabled = false;
    });
    if (badge) {
      badge.textContent = 'Multi-Select Aktif';
      badge.className = 'rounded-full bg-blue-600/15 border border-blue-500/30 px-3 py-1 text-[11px] font-bold text-blue-400 transition-all';
    }
  } else {
    // Radio button Penanggung Jawab / BPH terpilih:
    // Otomatis uncheck semua checkbox dan di-disable
    checkboxes.forEach(cb => {
      cb.checked = false;
      cb.disabled = true;
    });
    divisionContainer.classList.remove('opacity-100');
    divisionContainer.classList.add('opacity-40', 'pointer-events-none');
    if (badge) {
      badge.textContent = 'Terkunci (Jabatan Inti Dipilih)';
      badge.className = 'rounded-full bg-slate-800 border border-slate-700 px-3 py-1 text-[11px] font-bold text-slate-400 transition-all';
    }
  }
}

document.addEventListener('DOMContentLoaded', handleRoleSelectionChange);
</script>
