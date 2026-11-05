<?php $logo = \App\Models\Setting::logoUrl(); ?>

<!-- Hero Section -->
<section class="relative overflow-hidden rounded-3xl border border-slate-800 bg-gradient-to-br from-slate-900 via-indigo-950/40 to-slate-950 p-6 sm:p-12 shadow-2xl shadow-black/40">
  <div class="pointer-events-none absolute -right-20 -top-20 h-72 w-72 rounded-full bg-blue-600/15 blur-3xl"></div>
  <div class="pointer-events-none absolute -left-20 -bottom-20 h-72 w-72 rounded-full bg-indigo-600/10 blur-3xl"></div>

  <div class="relative z-10 flex flex-col-reverse items-center justify-between gap-8 md:flex-row">
    <div class="flex-1 space-y-4 text-center md:text-left">
      <!-- <div class="inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3.5 py-1 text-xs font-semibold text-emerald-400 shadow-sm backdrop-blur-sm">
        <span class="relative flex h-2 w-2">
          <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
          <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
        </span>
        Turnamen Sedang Berlangsung
      </div> -->

      <h1 class="text-3xl font-black tracking-tight text-white sm:text-5xl lg:text-6xl">
        <?= e($_ENV['APP_NAME'] ?? 'BANSA OPEN 2026') ?>
      </h1>

      <p class="max-w-xl text-base text-slate-300 sm:text-lg">
        Platform resmi pemantauan turnamen olahraga Bansa Open. Pantau bagan pertandingan, update live score langsung dari lapangan, klasemen grup, dan hasil lengkap secara real-time.
      </p>

      <!-- Tombol Aksi: Tonton Live di atas Pilih Cabang Olahraga -->
      <?php
        $hasLive = !empty($overview['live']) && (int)$overview['live'] > 0;
        $liveSportSlug = null;
        $liveSportName = '';
        if (!empty($sports)) {
            foreach ($sports as $slug => $s) {
                if (!empty($s['live']) && $s['live'] > 0) {
                    $liveSportSlug = $slug;
                    $liveSportName = $s['name'] ?? ucfirst($slug);
                    break;
                }
            }
        }
        $liveUrl = $liveSportSlug ? url($liveSportSlug . '/live') : url('#cabor');
      ?>

      <div class="pt-3 flex flex-col items-center md:items-start gap-3.5">
        <!-- 1. Tombol Tonton Live -->
        <div class="flex flex-col items-center md:items-start">
          <?php if ($hasLive): ?>
            <a href="<?= e($liveUrl) ?>" 
               class="group inline-flex items-center gap-2.5 rounded-xl bg-gradient-to-r from-red-600 via-rose-600 to-red-600 hover:from-red-500 hover:to-rose-500 px-6 py-3.5 text-sm sm:text-base font-extrabold text-white shadow-xl shadow-red-600/40 hover:shadow-red-500/60 hover:scale-[1.02] active:scale-95 transition-all duration-200">
              <span class="relative flex h-3 w-3">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-white opacity-80"></span>
                <span class="relative inline-flex h-3 w-3 rounded-full bg-white"></span>
              </span>
              <span>Tonton Live</span>
              <span class="rounded-full bg-black/30 px-2.5 py-0.5 text-xs font-bold text-white tracking-wide">
                <?= (int)$overview['live'] ?> Match
              </span>
              <span class="transition-transform group-hover:translate-x-1">→</span>
            </a>
            <span class="mt-1.5 text-xs font-semibold text-rose-400 flex items-center gap-1.5">
              <span class="h-1.5 w-1.5 rounded-full bg-rose-400 animate-pulse"></span>
              Pertandingan <?= e($liveSportName) ?> sedang berlangsung
            </span>
          <?php else: ?>
            <button type="button" 
                    disabled 
                    class="inline-flex items-center gap-2.5 rounded-xl border border-slate-700/80 bg-slate-800/80 px-6 py-3.5 text-sm sm:text-base font-bold text-slate-400 shadow-inner cursor-not-allowed select-none opacity-80">
              <span class="h-2.5 w-2.5 rounded-full bg-slate-500"></span>
              <span>Tonton Live</span>
            </button>
            <span class="mt-1.5 text-xs text-slate-400 italic">
              (belum ada pertandingan yang berlangsung)
            </span>
          <?php endif; ?>
        </div>

        <!-- 2. Tombol Pilih Cabang Olahraga -->
        <div>
          <a href="#cabor" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 hover:bg-blue-500 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-blue-600/30 hover:shadow-blue-500/40 transition hover:scale-[1.01] active:scale-95">
            <span>🏆</span>
            <span>Pilih Cabang Olahraga</span>
            <span>↓</span>
          </a>
        </div>
      </div>
    </div>

    <?php if ($logo): ?>
      <div class="flex shrink-0 items-center justify-center">
        <img src="<?= e($logo) ?>" alt="Logo Bansa Open" class="max-h-48 sm:max-h-60 w-auto object-contain drop-shadow-[0_15px_30px_rgba(37,99,235,0.25)]">
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- Ringkasan Statistik -->
<section class="mt-8 grid grid-cols-2 gap-4 sm:grid-cols-4">
  <div class="rounded-2xl border border-slate-800/90 bg-slate-900/80 p-4 sm:p-5 shadow-lg backdrop-blur-sm">
    <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Cabang Olahraga</div>
    <div class="mt-2 text-2xl font-black text-white sm:text-3xl"><?= count($sports) ?> Cabor</div>
    <div class="mt-1 text-xs text-slate-500">Badminton, Futsal, Pingpong</div>
  </div>

  <div class="rounded-2xl border border-slate-800/90 bg-slate-900/80 p-4 sm:p-5 shadow-lg backdrop-blur-sm">
    <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Tim</div>
    <div class="mt-2 text-2xl font-black text-blue-400 sm:text-3xl"><?= (int)($overview['teams'] ?? 0) ?> Tim</div>
    <div class="mt-1 text-xs text-slate-500">Terdaftar &amp; bertanding</div>
  </div>

  <div class="rounded-2xl border border-slate-800/90 bg-slate-900/80 p-4 sm:p-5 shadow-lg backdrop-blur-sm <?= !empty($overview['live']) ? 'ring-1 ring-red-500/40' : '' ?>">
    <div class="flex items-center justify-between">
      <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Sedang Main</span>
      <?php if (!empty($overview['live'])): ?>
        <span class="flex h-2 w-2 rounded-full bg-red-500 animate-ping"></span>
      <?php endif; ?>
    </div>
    <div class="mt-2 text-2xl font-black text-red-400 sm:text-3xl"><?= (int)($overview['live'] ?? 0) ?> Match</div>
    <div class="mt-1 text-xs text-slate-500">Pertandingan live saat ini</div>
  </div>

  <div class="rounded-2xl border border-slate-800/90 bg-slate-900/80 p-4 sm:p-5 shadow-lg backdrop-blur-sm">
    <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Selesai</div>
    <div class="mt-2 text-2xl font-black text-emerald-400 sm:text-3xl"><?= (int)($overview['finished'] ?? 0) ?> Match</div>
    <div class="mt-1 text-xs text-slate-500">Riwayat skor akhir</div>
  </div>
</section>

<!-- Daftar Cabang Olahraga -->
<section id="cabor" class="mt-12">
  <div class="flex items-center justify-between mb-6">
    <div>
      <h2 class="text-xl sm:text-2xl font-black tracking-tight text-white">Cabang Olahraga</h2>
      <p class="text-xs sm:text-sm text-slate-400">Pilih cabang olahraga untuk memantau live score, bagan, klasemen, dan anggota tim.</p>
    </div>
  </div>

  <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
    <?php foreach ($sports as $slug => $s): ?>
      <?php
        $isActive = (bool)$s['active'];
        $status = $s['status'] ?? ($isActive ? 'ready' : 'not_started');
      ?>
      <div class="group relative flex flex-col justify-between overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/90 p-6 shadow-xl shadow-black/20 transition-all duration-300 <?= $isActive ? 'hover:-translate-y-1.5 hover:border-blue-500/50 hover:shadow-2xl hover:shadow-blue-500/10' : 'opacity-85' ?>">
        
        <div>
          <!-- Header Cabor: Emoji & Judul -->
          <div class="flex items-start gap-4">
            <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-slate-800/90 text-3xl shadow-inner group-hover:scale-105 transition-transform">
              <?= $s['emoji'] ?>
            </span>
            <div class="min-w-0 flex-1">
              <h3 class="text-xl font-black text-white group-hover:text-blue-400 transition-colors truncate">
                <?= e($s['label']) ?>
              </h3>
              <p class="mt-0.5 text-xs text-slate-400">
                Format: <span class="font-semibold text-slate-300"><?= e(\App\Models\Sports::SYSTEMS[$s['system']] ?? 'Sistem Gugur') ?></span>
              </p>
            </div>
          </div>

          <!-- Informasi Kategori, Tanggal, Jam Pertandingan, dan Lokasi (Secara Berurutan) -->
          <div class="mt-4 space-y-2 rounded-2xl border border-slate-800/80 bg-slate-950/60 p-3.5 text-xs">
            <!-- 1. Kategori Pertandingan -->
            <div class="flex items-center gap-2.5 text-indigo-300 font-medium truncate">
              <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-md bg-indigo-500/10 text-indigo-400">
                <i class="bi bi-tags-fill text-xs"></i>
              </span>
              <span class="truncate"><?= !empty($s['kategori']) ? e($s['kategori']) : '<span class="italic text-slate-500">Kategori belum ditentukan</span>' ?></span>
            </div>

            <!-- 2. Tanggal Pelaksanaan -->
            <div class="flex items-center gap-2.5 text-amber-300 font-medium truncate">
              <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-md bg-amber-500/10 text-amber-400">
                <i class="bi bi-calendar-event-fill text-xs"></i>
              </span>
              <span class="truncate"><?= !empty($s['date_range']) ? e($s['date_range']) : '<span class="italic text-slate-500">Jadwal belum ditentukan</span>' ?></span>
            </div>

            <!-- 3. Jam Pertandingan -->
            <div class="flex items-center gap-2.5 text-cyan-300 font-medium truncate">
              <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-md bg-cyan-500/10 text-cyan-400">
                <i class="bi bi-clock-fill text-xs"></i>
              </span>
              <span class="truncate"><?= !empty($s['jam_pertandingan']) ? e($s['jam_pertandingan']) : '<span class="italic text-slate-500">Waktu belum ditentukan</span>' ?></span>
            </div>

            <!-- 4. Lokasi Lapangan -->
            <div class="flex items-center gap-2.5 text-emerald-300 font-medium truncate">
              <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-md bg-emerald-500/10 text-emerald-400">
                <i class="bi bi-geo-alt-fill text-xs"></i>
              </span>
              <span class="truncate"><?= !empty($s['venue']) ? e($s['venue']) : '<span class="italic text-slate-500">Lokasi belum ditentukan</span>' ?></span>
            </div>
          </div>

          <!-- Badge Status (Diletakkan Tepat di Bawah Urutan Informasi Kategori, Tanggal, Jam, Lokasi) -->
          <div class="mt-3.5 flex items-center justify-between">
            <?php if ($status === 'not_started'): ?>
              <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-700 bg-slate-800/80 px-3 py-1 text-xs font-semibold text-slate-400">
                <i class="bi bi-hourglass-split text-[11px]"></i>
                Belum Dimulai
              </span>
            <?php elseif ($status === 'live'): ?>
              <span class="inline-flex items-center gap-1.5 rounded-full border border-red-500/40 bg-red-500/15 px-3 py-1 text-xs font-bold text-red-400 animate-pulse">
                <span class="h-1.5 w-1.5 rounded-full bg-red-400 animate-ping"></span>
                Sedang Berlangsung (<?= (int)$s['live'] ?> Live)
              </span>
            <?php elseif ($status === 'finished'): ?>
              <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-500/30 bg-emerald-500/15 px-3 py-1 text-xs font-semibold text-emerald-400">
                <i class="bi bi-check-circle-fill text-[11px]"></i>
                Selesai
              </span>
            <?php else: ?>
              <span class="inline-flex items-center gap-1.5 rounded-full border border-blue-500/30 bg-blue-500/15 px-3 py-1 text-xs font-semibold text-blue-400">
                <span class="h-1.5 w-1.5 rounded-full bg-blue-400"></span>
                Siap Bertanding
              </span>
            <?php endif; ?>

            <?php if ($isActive): ?>
              <span class="text-[11px] font-medium text-slate-400">
                <?= (int)$s['teams'] ?> Tim
              </span>
            <?php endif; ?>
          </div>

          <?php if ($isActive): ?>
            <div class="mt-3 flex flex-wrap items-center gap-2 text-xs text-slate-400">
              <span class="rounded-lg bg-slate-800/80 px-2.5 py-1 font-medium text-slate-300"><?= (int)$s['teams'] ?> Tim</span>
              <?php if (!empty($s['live'])): ?>
                <span class="rounded-lg bg-red-950/80 border border-red-500/40 px-2.5 py-1 font-semibold text-red-400 animate-pulse">🔴 <?= (int)$s['live'] ?> Live</span>
              <?php endif; ?>
              <?php if (!empty($s['matches'])): ?>
                <span class="rounded-lg bg-slate-800/80 px-2.5 py-1 font-medium text-slate-300"><?= (int)$s['finished'] ?> Selesai</span>
              <?php else: ?>
                <span class="rounded-lg bg-blue-950/60 border border-blue-500/30 px-2.5 py-1 font-medium text-blue-300">Tim Terdaftar</span>
              <?php endif; ?>
            </div>
          <?php else: ?>
            <p class="mt-3 text-xs italic text-slate-500">
              Data tim dan bagan pertandingan cabang ini belum diatur oleh panitia.
            </p>
          <?php endif; ?>
        </div>

        <div class="mt-6 pt-4 border-t border-slate-800/80">
          <?php if ($isActive): ?>
            <a href="<?= url($slug) ?>" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600/20 border border-blue-500/30 px-4 py-2.5 text-sm font-bold text-blue-400 transition-all hover:bg-blue-600 hover:text-white">
              Buka Halaman <?= e($s['label']) ?> →
            </a>
          <?php else: ?>
            <button type="button" disabled class="inline-flex w-full cursor-not-allowed items-center justify-center rounded-xl border border-slate-800 bg-slate-800/40 px-4 py-2.5 text-xs font-semibold text-slate-500">
              Belum Dimulai
            </button>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- Section Panitia Inti (Di bawah Cabang Olahraga & Di Atas Sponsor) -->
<?php if (!empty($coreCommittees)): ?>
  <section id="panitia-inti" class="mt-14">
    <div class="flex items-center justify-between mb-6">
      <div>
        <div class="inline-flex items-center gap-1.5 rounded-full border border-amber-500/30 bg-amber-950/30 px-3 py-1 text-xs font-bold text-amber-400 mb-2">
          <span>👑</span>
          <span>BPH &amp; Penanggung Jawab</span>
        </div>
        <h2 class="text-xl sm:text-2xl font-black tracking-tight text-white">Panitia Inti Turnamen</h2>
        <p class="text-xs sm:text-sm text-slate-400">Pimpinan dan pengurus inti penyelenggara turnamen <?= e($_ENV['APP_NAME'] ?? 'Bansa Open 2026') ?>.</p>
      </div>
      <a href="<?= url('kepanitiaan') ?>" class="hidden sm:inline-flex items-center gap-1.5 text-xs font-bold text-blue-400 hover:text-blue-300 transition">
        <span>Lihat Semua Panitia</span>
        <span>→</span>
      </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
      <?php foreach ($coreCommittees as $member): ?>
        <div class="group relative flex flex-col items-center text-center justify-between rounded-2xl border border-slate-800 bg-slate-900/90 p-5 shadow-xl shadow-black/20 transition-all duration-300 hover:-translate-y-1.5 hover:border-amber-500/50 hover:shadow-2xl hover:shadow-amber-500/10">
          <div class="w-full flex flex-col items-center">
            <!-- Foto Profil Avatar Lingkaran di atas Nama dan Jabatan -->
            <div class="relative mb-3.5">
              <div class="h-20 w-20 rounded-full p-1 bg-gradient-to-tr from-amber-500/30 to-amber-400/10 border border-amber-500/40 shadow-lg shadow-black/30 group-hover:scale-105 transition-transform duration-300">
                <?php if (!empty($member['photo'])): ?>
                  <img src="<?= asset($member['photo']) ?>"
                       alt="<?= e($member['name']) ?>"
                       class="h-full w-full rounded-full object-cover">
                <?php else: ?>
                  <!-- Placeholder Siluet Bayangan Orang -->
                  <div class="h-full w-full rounded-full bg-slate-800 flex items-center justify-center text-slate-500 shadow-inner">
                    <svg class="h-10 w-10 text-slate-500" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                    </svg>
                  </div>
                <?php endif; ?>
              </div>
              <span class="absolute -bottom-1 -right-1 flex h-6 w-6 items-center justify-center rounded-full bg-amber-500/20 border border-amber-400/50 text-[11px] shadow">
                👑
              </span>
            </div>

            <h3 class="text-sm font-black text-white group-hover:text-amber-300 transition-colors line-clamp-2">
              <?= e($member['name']) ?>
            </h3>
            <p class="mt-1 text-xs font-bold text-amber-400">
              <?= e($member['role_title']) ?>
            </p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="mt-4 text-center sm:hidden">
      <a href="<?= url('kepanitiaan') ?>" class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-400 hover:text-blue-300 transition">
        Lihat Seluruh Susunan Panitia →
      </a>
    </div>
  </section>
<?php endif; ?>

<!-- Sponsor Kami Ticker -->
<?php include dirname(__DIR__) . '/partials/sponsor_ticker.php'; ?>

<!-- Informasi Kontak & Sosial Media Resmi (Tepat di bawah animasi sponsor) -->
<?php include dirname(__DIR__) . '/partials/contact_socials.php'; ?>
