<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
  <div>
    <div class="flex items-center gap-2 mb-1">
      <a href="<?= url('admin/contacts') ?>" class="text-xs font-semibold text-slate-400 hover:text-white transition">
        ← Kembali ke Daftar Kontak
      </a>
    </div>
    <h1 class="text-2xl font-black text-white">Tambah Akun Kontak Baru</h1>
    <p class="text-xs sm:text-sm text-slate-400">Tambahkan akun media sosial atau nomor kontak baru turnamen Bansa Open 2026.</p>
  </div>
</div>

<form method="post" action="<?= url('admin/contacts') ?>" class="space-y-6">
  <?= csrf_field() ?>

  <div class="rounded-3xl border border-slate-800 bg-slate-900/90 p-6 sm:p-7 shadow-xl shadow-black/20 space-y-5">
    <h2 class="text-base font-extrabold text-white border-b border-slate-800/80 pb-3">Informasi Akun</h2>

    <!-- 1. Pilihan Platform -->
    <div>
      <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">
        Pilih Platform / Aplikasi <span class="text-rose-400">*</span>
      </label>
      <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <?php foreach ($platforms as $key => $p): ?>
          <label class="flex items-center gap-3 rounded-2xl border border-slate-800 bg-slate-950/60 p-3 hover:border-slate-700 hover:bg-slate-800/50 cursor-pointer transition select-none">
            <input type="radio"
                   name="platform"
                   value="<?= $key ?>"
                   <?= $key === 'whatsapp' ? 'checked' : '' ?>
                   class="h-4 w-4 border-slate-700 bg-slate-950 text-blue-600 focus:ring-blue-500">
            <span class="flex items-center gap-2 text-xs font-bold text-white">
              <i class="bi <?= $p['icon'] ?> <?= $p['color'] ?> text-base"></i>
              <span><?= e($p['label']) ?></span>
            </span>
          </label>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
      <!-- 2. Nama Akun / Nomor (Field Baru yang Ditampilkan di Footer) -->
      <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">
          Nama Akun / Nomor <span class="text-rose-400">*</span>
        </label>
        <input type="text"
               name="account_name"
               required
               placeholder="Contoh: Admin 1 (0812-3456-7890) / @bansaopen"
               class="mt-1.5 w-full rounded-xl border border-slate-700 bg-slate-950/80 py-2.5 px-3.5 text-xs sm:text-sm text-white placeholder-slate-500 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
        <span class="mt-1 block text-[11px] text-slate-400">
          💡 Teks ini yang akan tampil tepat di samping ikon pada footer halaman publik.
        </span>
      </div>

      <!-- 3. Tautan / URL / Nomor Tujuan -->
      <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">
          Tautan / URL / Nomor Tujuan <span class="text-rose-400">*</span>
        </label>
        <input type="text"
               name="url"
               required
               placeholder="Contoh: 08123456789 atau https://wa.me/628123456789"
               class="mt-1.5 w-full rounded-xl border border-slate-700 bg-slate-950/80 py-2.5 px-3.5 text-xs sm:text-sm text-white placeholder-slate-500 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
        <span class="mt-1 block text-[11px] text-slate-400">
          Tautan tujuan saat diklik. Untuk WA bisa nomor HP saja atau URL <code class="text-emerald-400">wa.me/...</code>.
        </span>
      </div>
    </div>

    <!-- 4. Urutan Tampilan -->
    <div class="max-w-xs">
      <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">
        Urutan Tampilan
      </label>
      <input type="number"
             name="sort_order"
             value="0"
             min="0"
             class="mt-1.5 w-full rounded-xl border border-slate-700 bg-slate-950/80 py-2 px-3 text-xs sm:text-sm text-white placeholder-slate-500 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
      <span class="mt-1 block text-[11px] text-slate-500">Angka lebih kecil tampil lebih dulu (0, 1, 2, ...).</span>
    </div>
  </div>

  <!-- Aksi Tombol -->
  <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
    <a href="<?= url('admin/contacts') ?>" class="rounded-xl border border-slate-700 bg-slate-800 px-5 py-2.5 text-xs font-semibold text-slate-300 hover:bg-slate-700 hover:text-white transition">
      Batal
    </a>
    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-6 py-2.5 text-xs font-bold text-white shadow-lg shadow-blue-600/30 hover:bg-blue-500 transition">
      <span>💾</span>
      <span>Simpan Akun Kontak</span>
    </button>
  </div>
</form>

