<!-- Header Banner Kepanitiaan -->
<section class="relative overflow-hidden rounded-3xl border border-slate-800 bg-gradient-to-br from-slate-900 via-indigo-950/40 to-slate-950 p-6 sm:p-8 shadow-2xl shadow-black/40">
  <div class="pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full bg-blue-600/15 blur-3xl"></div>

  <div class="relative z-10 flex flex-col justify-between gap-6 md:flex-row md:items-center">
    <div class="space-y-2">
      <div class="flex items-center gap-2">
        <a href="<?= url('/') ?>" class="inline-flex items-center text-xs font-semibold text-slate-400 hover:text-white transition">
          ← Kembali ke Beranda
        </a>
      </div>

      <div class="flex flex-wrap items-center gap-3">
        <span class="flex h-12 w-12 sm:h-14 sm:w-14 items-center justify-center rounded-2xl bg-slate-800/90 text-3xl shadow-inner">
          👥
        </span>
        <div>
          <h1 class="text-2xl sm:text-4xl font-black tracking-tight text-white">
            Susunan Kepanitiaan
          </h1>
          <p class="text-xs sm:text-sm text-slate-400">
            Struktur organisasi, panitia inti, koordinator, dan anggota divisi turnamen <?= e($_ENV['APP_NAME'] ?? 'Bansa Open 2026') ?>.
          </p>
        </div>
      </div>
    </div>

    <!-- Total Badge -->
    <div class="flex flex-wrap items-center gap-2.5">
      <span class="inline-flex items-center gap-2 rounded-2xl border border-blue-500/30 bg-blue-950/40 px-4 py-2 text-xs font-bold text-blue-300 shadow-inner">
        <span>🏆</span>
        <span><?= (int)$totalCommittees ?> Panitia Terdaftar</span>
      </span>
    </div>
  </div>
</section>

<?php if (empty($coreCommittees) && empty($divisionCommittees)): ?>
  <div class="mt-8 rounded-3xl border border-slate-800 bg-slate-900/60 p-12 text-center text-slate-500">
    <span class="block text-4xl mb-3">👥</span>
    <h3 class="text-base font-bold text-slate-400">Susunan Kepanitiaan Belum Ditetapkan</h3>
    <p class="text-xs text-slate-500 mt-1">Data susunan panitia sedang dalam proses finalisasi oleh panitia turnamen.</p>
  </div>
<?php else: ?>

  <!-- 1. Bagian Panitia Inti (Penanggung Jawab & BPH) -->
  <?php if (!empty($coreCommittees)): ?>
    <section class="mt-10">
      <div class="mb-5 flex items-center justify-between">
        <div>
          <div class="inline-flex items-center gap-1.5 rounded-full border border-amber-500/30 bg-amber-950/30 px-3 py-1 text-xs font-bold text-amber-400 mb-2">
            <span>👑</span>
            <span>BPH &amp; Penanggung Jawab</span>
          </div>
          <h2 class="text-xl sm:text-2xl font-black text-white">Panitia Inti</h2>
          <p class="text-xs sm:text-sm text-slate-400">Pimpinan dan pengurus inti penyelenggara turnamen.</p>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4.5">
        <?php foreach ($coreCommittees as $c): ?>
          <div class="group relative flex flex-col items-center text-center justify-between rounded-2xl border border-amber-500/30 bg-gradient-to-b from-amber-950/20 via-slate-900/90 to-slate-950 p-5 shadow-xl shadow-black/20 hover:border-amber-500/60 hover:-translate-y-1.5 transition-all">
            <div class="w-full flex flex-col items-center">
              <!-- Avatar Lingkaran Panitia Inti -->
              <div class="relative mb-3.5">
                <div class="h-20 w-20 rounded-full p-1 bg-gradient-to-tr from-amber-500/30 to-amber-400/10 border border-amber-500/40 shadow-lg shadow-black/30 group-hover:scale-105 transition-transform duration-300">
                  <?php if (!empty($c['photo'])): ?>
                    <img src="<?= asset($c['photo']) ?>"
                         alt="<?= e($c['name']) ?>"
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
                <?= e($c['name']) ?>
              </h3>
              <p class="mt-1 text-xs font-bold text-amber-400">
                <?= e($c['role_title']) ?>
              </p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

  <!-- 2. Bagian Divisi Operasional & Penanggung Jawab Cabor (Card Desain Seragam) -->
  <?php if (!empty($divisionCommittees)): ?>
    <section class="mt-12 space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <div class="inline-flex items-center gap-1.5 rounded-full border border-blue-500/30 bg-blue-950/30 px-3 py-1 text-xs font-bold text-blue-400 mb-2">
            <span>📌</span>
            <span>Operasional &amp; Cabor</span>
          </div>
          <h2 class="text-xl sm:text-2xl font-black text-white">Divisi &amp; Penanggung Jawab Cabor</h2>
          <p class="text-xs sm:text-sm text-slate-400">Koordinator, anggota divisi pelaksana, dan penanggung jawab cabang olahraga.</p>
        </div>
      </div>

      <!-- Grid Card Seragam dengan Desain Panitia Inti -->
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4.5">
        <?php foreach ($divisionCommittees as $d): ?>
          <?php
            $priority = (int)($d['priority'] ?? 2);
            $badgeIcon = '👤';
            $badgeClass = 'bg-slate-800 border-slate-700 text-slate-400';
            $priorityLabel = 'Anggota Divisi';
            $cardBorderHover = 'hover:border-blue-500/50 hover:shadow-blue-500/10';

            if ($priority === 1) {
                $badgeIcon = '⭐';
                $badgeClass = 'bg-blue-500/20 border-blue-400/50 text-blue-300';
                $priorityLabel = 'Koordinator';
                $cardBorderHover = 'hover:border-blue-500/60 hover:shadow-blue-500/15';
            } elseif ($priority === 3) {
                $badgeIcon = '🏆';
                $badgeClass = 'bg-emerald-500/20 border-emerald-400/50 text-emerald-300';
                $priorityLabel = 'Penanggung Jawab Cabor';
                $cardBorderHover = 'hover:border-emerald-500/50 hover:shadow-emerald-500/10';
            }
          ?>
          <div class="group relative flex flex-col items-center text-center justify-between rounded-2xl border border-slate-800 bg-slate-900/90 p-5 shadow-xl shadow-black/20 transition-all duration-300 hover:-translate-y-1.5 <?= $cardBorderHover ?> hover:shadow-2xl">
            <div class="w-full flex flex-col items-center">
              <!-- Avatar Lingkaran Divisi / PJ Cabor -->
              <div class="relative mb-3.5">
                <div class="h-20 w-20 rounded-full p-1 bg-gradient-to-tr from-blue-500/30 to-indigo-500/10 border border-blue-500/40 shadow-lg shadow-black/30 group-hover:scale-105 transition-transform duration-300">
                  <?php if (!empty($d['photo'])): ?>
                    <img src="<?= asset($d['photo']) ?>"
                         alt="<?= e($d['name']) ?>"
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
                <span class="absolute -bottom-1 -right-1 flex h-6 w-6 items-center justify-center rounded-full <?= $badgeClass ?> border text-[11px] shadow" title="<?= e($priorityLabel) ?>">
                  <?= $badgeIcon ?>
                </span>
              </div>

              <!-- Nama Panitia -->
              <h3 class="text-sm font-black text-white group-hover:text-blue-300 transition-colors line-clamp-2">
                <?= e($d['name']) ?>
              </h3>

              <!-- Jabatan Gabungan (Jika Merangkap, misal: Divisi Acara & PJ Cabor Futsal) -->
              <p class="mt-1 text-xs font-bold text-blue-400 line-clamp-2">
                <?= e($d['role_title']) ?>
              </p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

<?php endif; ?>

<!-- Footer Kontak Sosial Media -->
<?php include dirname(__DIR__) . '/partials/contact_socials.php'; ?>

