<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
  <div>
    <h1 class="text-2xl font-black text-white">Pengaturan Jadwal &amp; Lokasi</h1>
    <p class="text-xs sm:text-sm text-slate-400">Atur kategori turnamen, rentang tanggal pelaksanaan, jam pertandingan, dan lokasi lapangan yang ditampilkan pada card cabor halaman utama publik.</p>
  </div>
</div>

<form method="post" action="<?= url('admin/schedules') ?>" class="space-y-6">
  <?= csrf_field() ?>

  <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <?php foreach ($schedules as $slug => $s): ?>
      <div class="flex flex-col justify-between rounded-3xl border border-slate-800 bg-slate-900/90 p-5 sm:p-6 shadow-xl shadow-black/20">
        <div>
          <!-- Header Cabor -->
          <div class="flex items-center gap-3 border-b border-slate-800/80 pb-4">
            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-800 text-2xl shadow-inner">
              <?= $s['emoji'] ?>
            </span>
            <div>
              <h3 class="text-base font-extrabold text-white"><?= e($s['label']) ?></h3>
              <span class="text-xs text-slate-400">Format: <?= e(\App\Models\Sports::SYSTEMS[\App\Models\Sports::system($slug)] ?? 'Turnamen') ?></span>
            </div>
          </div>

          <!-- Form Fields -->
          <div class="mt-5 space-y-4">
            <!-- 1. Kategori Pertandingan -->
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">
                Kategori Pertandingan
              </label>
              <div class="relative mt-1.5">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-indigo-400">
                  <i class="bi bi-tags-fill"></i>
                </span>
                <input type="text"
                       name="kategori[<?= $slug ?>]"
                       value="<?= e($s['kategori'] ?? '') ?>"
                       placeholder="Contoh: Ganda Putra &amp; Putri, Umum, U-19"
                       class="w-full rounded-xl border border-slate-700 bg-slate-950/80 py-2.5 pl-9 pr-3 text-xs sm:text-sm text-white placeholder-slate-500 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
              </div>
              <span class="mt-1 block text-[11px] text-slate-500">Contoh: "Ganda Putra &amp; Putri", "Umum", "U-19"</span>
            </div>

            <!-- 2. Rentang Tanggal Turnamen -->
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">
                Rentang Tanggal Turnamen
              </label>
              <div class="relative mt-1.5">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-amber-400">
                  <i class="bi bi-calendar-event-fill"></i>
                </span>
                <input type="text"
                       name="dates[<?= $slug ?>]"
                       value="<?= e($s['dates'] ?? '') ?>"
                       placeholder="Contoh: 12 - 16 Agustus 2026"
                       class="w-full rounded-xl border border-slate-700 bg-slate-950/80 py-2.5 pl-9 pr-3 text-xs sm:text-sm text-white placeholder-slate-500 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
              </div>
              <span class="mt-1 block text-[11px] text-slate-500">Contoh: "15 - 18 Agustus 2026" atau "Sabtu, 22 Juni 2026"</span>
            </div>

            <!-- 3. Jam Pertandingan -->
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">
                Jam Pertandingan
              </label>
              <div class="relative mt-1.5">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-cyan-400">
                  <i class="bi bi-clock-fill"></i>
                </span>
                <input type="text"
                       name="jam[<?= $slug ?>]"
                       value="<?= e($s['jam_pertandingan'] ?? '') ?>"
                       placeholder="Contoh: 08:00 WIB - Selesai"
                       class="w-full rounded-xl border border-slate-700 bg-slate-950/80 py-2.5 pl-9 pr-3 text-xs sm:text-sm text-white placeholder-slate-500 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500">
              </div>
              <span class="mt-1 block text-[11px] text-slate-500">Contoh: "08:00 WIB - Selesai" atau "19:00 - 23:00 WIB"</span>
            </div>

            <!-- 4. Nama Tempat / Lokasi Lapangan -->
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">
                Nama Tempat / Lokasi Lapangan
              </label>
              <div class="relative mt-1.5">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-emerald-400">
                  <i class="bi bi-geo-alt-fill"></i>
                </span>
                <input type="text"
                       name="venues[<?= $slug ?>]"
                       value="<?= e($s['venue'] ?? '') ?>"
                       placeholder="Contoh: GOR Bansa Indoor Arena"
                       class="w-full rounded-xl border border-slate-700 bg-slate-950/80 py-2.5 pl-9 pr-3 text-xs sm:text-sm text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
              </div>
              <span class="mt-1 block text-[11px] text-slate-500">Contoh: "GOR Bansa Indoor Arena" / "Lapangan Futsal Bansa"</span>
            </div>

            <!-- 5. Catatan Tambahan (Opsional) -->
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">
                Catatan Tambahan (Opsional)
              </label>
              <textarea name="notes[<?= $slug ?>]"
                        rows="2"
                        placeholder="Contoh: Pendaftaran ditutup H-3 sebelum turnamen dimulai."
                        class="mt-1.5 w-full rounded-xl border border-slate-700 bg-slate-950/80 p-2.5 text-xs text-white placeholder-slate-500 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"><?= e($s['notes'] ?? '') ?></textarea>
            </div>
          </div>
        </div>

        <!-- Preview Card Publik Mini -->
        <div class="mt-5 rounded-2xl border border-slate-800/80 bg-slate-950/50 p-3.5 text-xs text-slate-400">
          <div class="text-[10px] uppercase font-bold text-slate-500 mb-2 flex items-center justify-between">
            <span>Pratinjau Card Publik:</span>
            <span class="rounded bg-slate-800 px-1.5 py-0.5 text-[9px] text-slate-300">Info Card</span>
          </div>
          <div class="flex flex-col gap-1.5">
            <div class="flex items-center gap-2 text-indigo-300 font-semibold truncate">
              <i class="bi bi-tags-fill text-indigo-400"></i>
              <span class="truncate"><?= !empty($s['kategori']) ? e($s['kategori']) : '<span class="italic text-slate-500 font-normal">(Kategori belum diisi)</span>' ?></span>
            </div>
            <div class="flex items-center gap-2 text-amber-300 font-semibold truncate">
              <i class="bi bi-calendar-event-fill text-amber-400"></i>
              <span class="truncate"><?= !empty($s['dates']) ? e($s['dates']) : '<span class="italic text-slate-500 font-normal">(Tanggal belum diisi)</span>' ?></span>
            </div>
            <div class="flex items-center gap-2 text-cyan-300 font-semibold truncate">
              <i class="bi bi-clock-fill text-cyan-400"></i>
              <span class="truncate"><?= !empty($s['jam_pertandingan']) ? e($s['jam_pertandingan']) : '<span class="italic text-slate-500 font-normal">(Jam belum diisi)</span>' ?></span>
            </div>
            <div class="flex items-center gap-2 text-emerald-300 font-semibold truncate">
              <i class="bi bi-geo-alt-fill text-emerald-400"></i>
              <span class="truncate"><?= !empty($s['venue']) ? e($s['venue']) : '<span class="italic text-slate-500 font-normal">(Lokasi belum diisi)</span>' ?></span>
            </div>
          </div>
        </div>

      </div>
    <?php endforeach; ?>
  </div>

  <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
    <a href="<?= url('admin') ?>" class="rounded-xl border border-slate-700 bg-slate-800 px-5 py-2.5 text-xs font-semibold text-slate-300 hover:bg-slate-700 hover:text-white transition">
      Batal
    </a>
    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-6 py-2.5 text-xs font-bold text-white shadow-lg shadow-blue-600/30 hover:bg-blue-500 transition">
      <span>💾</span>
      <span>Simpan Semua Pengaturan Jadwal, Kategori, &amp; Lokasi</span>
    </button>
  </div>
</form>

