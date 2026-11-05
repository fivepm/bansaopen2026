<?php
/**
 * Halaman Khusus Cabang Olahraga dengan Sistem Tab (JavaScript murni)
 * Tab 1: Live Score
 * Tab 2: Bracket Kiri-Kanan (Two-Sided) / Klasemen
 * Tab 3: Detail Tim (dengan pemecahan nama pemain per baris)
 * Tab 4: Hasil Pertandingan
 */
$liveCount = count($liveMatches);
$finishedCount = count($finishedMatches);
$teamCount = count($teams);
$isGroup = ($system === 'group');
$hasMultipleCategories = (count($categories) > 1);
?>

<!-- Header Cabor Banner -->
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
          <?= \App\Models\Sports::emoji($sport) ?>
        </span>
        <div>
          <h1 class="text-2xl sm:text-4xl font-black tracking-tight text-white">
            Turnamen <?= e(sport_label($sport)) ?>
          </h1>
          <p class="text-xs sm:text-sm text-slate-400">
            Format: <span class="font-semibold text-blue-400"><?= e(\App\Models\Sports::SYSTEMS[$system] ?? 'Sistem Gugur') ?></span>
          </p>
          <?php if (!empty($kategori) || !empty($date_range) || !empty($jam_pertandingan) || !empty($venue)): ?>
            <div class="mt-2.5 flex flex-wrap items-center gap-2 text-xs">
              <?php if (!empty($kategori)): ?>
                <span class="inline-flex items-center gap-1.5 rounded-lg border border-indigo-500/30 bg-indigo-950/40 px-2.5 py-1 font-bold text-indigo-300">
                  <i class="bi bi-tags-fill"></i>
                  <span><?= e($kategori) ?></span>
                </span>
              <?php endif; ?>
              <?php if (!empty($date_range)): ?>
                <span class="inline-flex items-center gap-1.5 rounded-lg border border-amber-500/30 bg-amber-950/40 px-2.5 py-1 font-bold text-amber-300">
                  <i class="bi bi-calendar-event-fill"></i>
                  <span><?= e($date_range) ?></span>
                </span>
              <?php endif; ?>
              <?php if (!empty($jam_pertandingan)): ?>
                <span class="inline-flex items-center gap-1.5 rounded-lg border border-cyan-500/30 bg-cyan-950/40 px-2.5 py-1 font-bold text-cyan-300">
                  <i class="bi bi-clock-fill"></i>
                  <span><?= e($jam_pertandingan) ?></span>
                </span>
              <?php endif; ?>
              <?php if (!empty($venue)): ?>
                <span class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-500/30 bg-emerald-950/40 px-2.5 py-1 font-bold text-emerald-300">
                  <i class="bi bi-geo-alt-fill"></i>
                  <span><?= e($venue) ?></span>
                </span>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Quick Stat Badges -->
    <div class="flex flex-wrap items-center gap-2.5">
      <div class="flex items-center gap-2 rounded-xl border border-slate-800 bg-slate-900/90 px-3.5 py-2 text-xs font-medium text-slate-300">
        <span class="text-slate-500">👥</span>
        <span><b><?= $teamCount ?></b> Tim Terdaftar</span>
      </div>

      <div class="flex items-center gap-2 rounded-xl border <?= $liveCount > 0 ? 'border-red-500/40 bg-red-950/40 text-red-300' : 'border-slate-800 bg-slate-900/90 text-slate-300' ?> px-3.5 py-2 text-xs font-medium">
        <?php if ($liveCount > 0): ?>
          <span class="relative flex h-2 w-2">
            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-red-400 opacity-75"></span>
            <span class="relative inline-flex h-2 w-2 rounded-full bg-red-500"></span>
          </span>
          <span id="stat-live-count" class="font-bold"><b><?= $liveCount ?></b> Sedang Main</span>
        <?php else: ?>
          <span class="text-slate-500">⚪</span>
          <span id="stat-live-count">0 Sedang Main</span>
        <?php endif; ?>
      </div>

      <div class="flex items-center gap-2 rounded-xl border border-slate-800 bg-slate-900/90 px-3.5 py-2 text-xs font-medium text-slate-300">
        <span class="text-emerald-400">✓</span>
        <span><b id="stat-finished-count"><?= $finishedCount ?></b> Selesai</span>
      </div>
    </div>
  </div>
</section>

<!-- Navigasi Sistem Tab (JavaScript murni tanpa reload) -->
<nav class="mt-8 flex gap-2 overflow-x-auto rounded-2xl border border-slate-800/90 bg-slate-900/80 p-1.5 shadow-lg backdrop-blur-md" aria-label="Tab Turnamen">
  <!-- Tab 1: Live Score -->
  <button type="button" id="btn-tab-live" onclick="switchTab('live')" class="tab-btn flex shrink-0 items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition-all">
    <span class="relative flex h-2.5 w-2.5">
      <?php if ($liveCount > 0): ?>
        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-red-400 opacity-75"></span>
        <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-red-500"></span>
      <?php else: ?>
        <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-slate-500"></span>
      <?php endif; ?>
    </span>
    <span>Live Score</span>
    <span id="tab-live-count" class="rounded-full bg-red-500 px-2 py-0.2 text-[11px] font-bold text-white <?= $liveCount > 0 ? '' : 'hidden' ?>"><?= $liveCount ?></span>
  </button>

  <!-- Tab Baru: Jadwal & Lapangan (Chronological Order of Play) -->
  <button type="button" id="btn-tab-jadwal" onclick="switchTab('jadwal')" class="tab-btn flex shrink-0 items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition-all">
    <span>📅</span>
    <span>Jadwal &amp; Lapangan</span>
    <span id="tab-jadwal-count" class="rounded-full bg-slate-800 px-2 py-0.2 text-[11px] font-bold text-slate-300"><?= count($scheduleMatches ?? []) ?></span>
  </button>

  <!-- Tab 2: Bracket / Klasemen (Fase Grup) -->
  <button type="button" id="btn-tab-bracket" onclick="switchTab('bracket')" class="tab-btn flex shrink-0 items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition-all">
    <span><?= $isGroup ? '📊' : '🌳' ?></span>
    <span><?= $isGroup ? 'Klasemen &amp; Jadwal' : 'Bagan Pertandingan' ?></span>
  </button>

  <?php if ($isGroup): ?>
    <!-- Tab 3 (Khusus Sistem Grup / Futsal): Playoff & Final -->
    <button type="button" id="btn-tab-playoff" onclick="switchTab('playoff')" class="tab-btn flex shrink-0 items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition-all">
      <span>🏆</span>
      <span>Playoff &amp; Final</span>
    </button>
  <?php endif; ?>

  <!-- Tab Detail Tim -->
  <button type="button" id="btn-tab-teams" onclick="switchTab('teams')" class="tab-btn flex shrink-0 items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition-all">
    <span>👥</span>
    <span>Detail Tim</span>
    <span class="rounded-full bg-slate-800 px-2 py-0.2 text-[11px] font-bold text-slate-300"><?= $teamCount ?></span>
  </button>

  <!-- Tab Hasil Pertandingan -->
  <button type="button" id="btn-tab-hasil" onclick="switchTab('hasil')" class="tab-btn flex shrink-0 items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition-all">
    <span>📋</span>
    <span>Hasil Pertandingan</span>
    <span id="tab-hasil-count" class="rounded-full bg-slate-800 px-2 py-0.2 text-[11px] font-bold text-slate-300"><?= $finishedCount ?></span>
  </button>
</nav>

<!-- ========================================== -->
<!-- KONTEN TAB 1: LIVE SCORE (BWF TV STYLE)     -->
<!-- ========================================== -->
<section id="panel-live" class="tab-panel mt-6">
  <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <div>
      <h2 class="text-xl sm:text-2xl font-black text-white flex items-center gap-2">
        <span class="h-3 w-3 rounded-full bg-red-500 animate-ping"></span>
        Papan Skor Siaran Langsung (Live Broadcast)
      </h2>
      <p class="text-xs sm:text-sm text-slate-400">Pembaruan otomatis real-time dari arena pertandingan.</p>
    </div>
    <div class="flex items-center gap-2">
      <!-- Tombol Mode Layar Penuh (Full Screen) -->
      <button type="button"
              id="btn-toggle-fullscreen"
              onclick="toggleFullScreenLive()"
              class="inline-flex items-center gap-1.5 rounded-xl border border-blue-500/40 bg-blue-950/40 px-3 py-1.5 text-xs font-bold text-blue-300 hover:bg-blue-900/60 hover:text-white transition shadow-md active:scale-95"
              title="Tampilkan Papan Skor Layar Penuh (Semua Lapangan)">
        <span id="fs-icon" class="text-sm">⛶</span>
        <span id="fs-label" class="hidden sm:inline">Layar Penuh</span>
      </button>

      <span class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-500/40 bg-emerald-950/40 px-3 py-1.5 text-xs font-bold text-emerald-400">
        <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
        Live Score
      </span>
      <?php if ($liveCount > 0): ?>
        <span id="header-live-badge" class="rounded-xl border border-red-500/30 bg-red-950/40 px-3 py-1.5 text-xs font-semibold text-red-400">
          🔴 <?= $liveCount ?> Pertandingan Berlangsung
        </span>
      <?php endif; ?>
    </div>
  </div>

  <!-- Floating Exit Fullscreen Button (Hanya Muncul saat Mode Layar Penuh Aktif) -->
  <div id="fullscreen-exit-bar" class="hidden fixed top-4 right-4 z-50">
    <button type="button"
            onclick="toggleFullScreenLive()"
            class="flex items-center gap-2 rounded-xl bg-slate-900/95 border border-slate-700 px-4 py-2 text-xs font-bold text-slate-100 hover:bg-slate-800 hover:text-white shadow-2xl backdrop-blur-md transition-all active:scale-95">
      <span>🗗</span>
      <span>Keluar Layar Penuh</span>
      <kbd class="rounded bg-slate-800 px-1.5 py-0.5 text-[10px] text-slate-400 font-mono">ESC</kbd>
    </button>
  </div>

  <div id="live-content-container">
    <?php include dirname(__DIR__) . '/partials/live_matches_content.php'; ?>
  </div>
</section>

<!-- ========================================== -->
<!-- KONTEN TAB: JADWAL & LAPANGAN              -->
<!-- ========================================== -->
<section id="panel-jadwal" class="tab-panel hidden mt-6">
  <div id="schedule-content-container">
    <?php include dirname(__DIR__) . '/partials/schedule_matches_content.php'; ?>
  </div>
</section>

<!-- ========================================== -->
<!-- KONTEN TAB 2: BRACKET / KLASEMEN (FASE GRUP) -->
<!-- ========================================== -->
<section id="panel-bracket" class="tab-panel hidden mt-6">
  <?php if ($isGroup): ?>
    <!-- Konten Klasemen & Jadwal Khusus Pertandingan Grup (Real-Time Auto-Refresh) -->
    <div id="group-standings-container">
      <?php include dirname(__DIR__) . '/partials/group_standings_content.php'; ?>
    </div>

  <?php else: ?>
    <!-- Mode Sistem Gugur (Bracket Kiri-Kanan / Grand Slam Style) -->
    <div class="mb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-xl sm:text-2xl font-black text-white">Bagan Pertandingan (Two-Sided Bracket)</h2>
        <p class="text-xs sm:text-sm text-slate-400">Tata letak bagan dua sisi (Blok Kiri &amp; Kanan) yang bertemu di Grand Final &amp; Perebutan Juara 3.</p>
      </div>

      <!-- Sub-Tab / Filter Kategori untuk Badminton (Ganda Putra vs Ganda Putri) -->
      <?php if ($hasMultipleCategories): ?>
        <div class="flex items-center gap-1.5 rounded-2xl border border-slate-800 bg-slate-900/90 p-1.5 shadow-md">
          <?php foreach ($categories as $ck => $cl): ?>
            <button type="button"
                    id="btn-subcat-<?= $ck ?>"
                    onclick="switchBracketCategory('<?= $ck ?>')"
                    class="subcat-btn rounded-xl px-4 py-2 text-xs font-bold transition-all">
              <?= $ck === 'ganda_putra' ? '🏸 ' : ($ck === 'ganda_putri' ? '🏸 ' : '') ?><?= e($cl) ?>
            </button>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Container Bagan per Kategori (Beralih dengan JS tanpa reload) -->
    <?php foreach ($categories as $ck => $cl): ?>
      <?php $bracket = $brackets[$ck] ?? ['totalRounds' => 0, 'left' => [], 'right' => [], 'final' => null, 'thirdPlace' => null]; ?>
      <div id="bracket-cat-<?= $ck ?>" class="bracket-cat-panel hidden">
        <?php if ($hasMultipleCategories): ?>
          <div class="mb-3 flex items-center gap-2">
            <span class="h-2 w-2 rounded-full bg-blue-500"></span>
            <span class="text-xs font-extrabold uppercase tracking-widest text-blue-400">Bagan Kategori: <?= e($cl) ?></span>
          </div>
        <?php endif; ?>
        <div id="bracket-cat-content-<?= $ck ?>">
          <?php include dirname(__DIR__) . '/partials/two_sided_bracket.php'; ?>
        </div>
      </div>
    <?php endforeach; ?>

  <?php endif; ?>
</section>

<?php if ($isGroup): ?>
<!-- ========================================== -->
<!-- KONTEN TAB KHUSUS: PLAYOFF & FINAL         -->
<!-- ========================================== -->
<section id="panel-playoff" class="tab-panel hidden mt-6">
  <div id="playoffs-container">
    <?php include dirname(__DIR__) . '/partials/playoff_content.php'; ?>
  </div>
</section>
<?php endif; ?>

<!-- ========================================== -->
<!-- KONTEN TAB 3: DETAIL TIM                   -->
<!-- ========================================== -->
<section id="panel-teams" class="tab-panel hidden mt-6">
  <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <div>
      <h2 class="text-xl sm:text-2xl font-black text-white">Daftar Tim &amp; Anggota Pemain</h2>
      <p class="text-xs sm:text-sm text-slate-400">Daftar lengkap seluruh tim terdaftar beserta rincian nama para pemain.</p>
    </div>

    <!-- Filter Kategori Tim (Badminton: Ganda Putra / Putri) -->
    <?php if ($hasMultipleCategories): ?>
      <div class="flex items-center gap-1.5 rounded-2xl border border-slate-800 bg-slate-900/90 p-1.5 shadow-md">
        <button type="button" onclick="filterTeamCategory('all')" id="btn-teamcat-all" class="teamcat-btn rounded-xl px-3.5 py-1.5 text-xs font-bold transition-all bg-blue-600 text-white">
          Semua (<?= $teamCount ?>)
        </button>
        <?php foreach ($categories as $ck => $cl): ?>
          <button type="button" onclick="filterTeamCategory('<?= $ck ?>')" id="btn-teamcat-<?= $ck ?>" class="teamcat-btn rounded-xl px-3.5 py-1.5 text-xs font-bold text-slate-400 hover:text-white transition-all">
            <?= e($cl) ?>
          </button>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <span class="rounded-xl border border-slate-800 bg-slate-900 px-3 py-1.5 text-xs font-semibold text-slate-300">
        Total: <?= $teamCount ?> Tim
      </span>
    <?php endif; ?>
  </div>

  <?php if (empty($teams)): ?>
    <div class="rounded-2xl border border-dashed border-slate-800 bg-slate-900/40 p-10 text-center text-slate-500">
      Belum ada tim yang terdaftar pada cabang olahraga ini.
    </div>
  <?php else: ?>
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
      <?php foreach ($teams as $idx => $team): ?>
        <?php
          $members = parse_team_members($team['members']);
          $teamCat = $team['category'] ?? 'umum';
        ?>
        <div class="team-card flex flex-col justify-between rounded-2xl border border-slate-800 bg-slate-900/90 p-5 shadow-xl shadow-black/20 transition hover:border-slate-700" data-category="<?= e($teamCat) ?>">
          <div>
            <div class="flex items-start justify-between gap-3">
              <div>
                <div class="flex items-center gap-2">
                  <span class="text-xs font-bold text-blue-400">Tim #<?= $idx + 1 ?></span>
                  <?php if ($hasMultipleCategories && !empty($categories[$teamCat])): ?>
                    <span class="rounded-md bg-slate-800 px-2 py-0.5 text-[11px] font-semibold text-slate-300">
                      <?= e($categories[$teamCat]) ?>
                    </span>
                  <?php endif; ?>
                </div>
                <h3 class="text-lg font-black text-white mt-1">
                  <?= e($team['name']) ?>
                </h3>
              </div>
              <?php if (!empty($team['institution'])): ?>
                <span class="shrink-0 rounded-lg border border-slate-700 bg-slate-800/80 px-2.5 py-1 text-xs font-semibold text-slate-300">
                  <?= e($team['institution']) ?>
                </span>
              <?php endif; ?>
            </div>

            <?php if (!empty($team['contact'])): ?>
              <div class="mt-2 text-xs text-slate-400 flex items-center gap-1.5">
                <span>📞</span>
                <span><?= e($team['contact']) ?></span>
              </div>
            <?php endif; ?>

            <!-- Format Menampilkan Anggota Tim: Baris Baru (Bukan Koma) -->
            <div class="mt-4 pt-3 border-t border-slate-800/80">
              <div class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">
                Daftar Pemain / Anggota:
              </div>

              <?php if (!empty($members)): ?>
                <ul class="space-y-1.5">
                  <?php foreach ($members as $pIndex => $player): ?>
                    <li class="flex items-center gap-2.5 rounded-xl border border-slate-800/70 bg-slate-950/70 px-3 py-2 text-xs sm:text-sm text-slate-200">
                      <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-blue-600/20 text-[11px] font-bold text-blue-400">
                        <?= $pIndex + 1 ?>
                      </span>
                      <span class="font-medium"><?= e($player) ?></span>
                    </li>
                  <?php endforeach; ?>
                </ul>
              <?php else: ?>
                <p class="text-xs italic text-slate-500">Belum ada daftar anggota pemain.</p>
              <?php endif; ?>
            </div>
          </div>

          <div class="mt-4 pt-3 border-t border-slate-800/60 text-right text-[11px] text-slate-500">
            Terdaftar: <?= date('d M Y', strtotime($team['created_at'])) ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<!-- ========================================== -->
<!-- KONTEN TAB 4: HASIL PERTANDINGAN           -->
<!-- ========================================== -->
<section id="panel-hasil" class="tab-panel hidden mt-6">
  <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <div>
      <h2 class="text-xl sm:text-2xl font-black text-white">Hasil Pertandingan Selesai</h2>
      <p class="text-xs sm:text-sm text-slate-400">Riwayat pertandingan yang telah selesai dengan rincian perolehan poin tiap set secara berjejer.</p>
    </div>
    <span class="rounded-xl border border-slate-800 bg-slate-900 px-3 py-1.5 text-xs font-semibold text-slate-300">
      Total: <b id="results-count"><?= $finishedCount ?></b> Pertandingan Selesai
    </span>
  </div>

  <div id="finished-content-container">
    <?php include dirname(__DIR__) . '/partials/finished_matches_content.php'; ?>
  </div>
</section>

<!-- ========================================== -->
<!-- JAVASCRIPT SISTEM TAB & SEAMLESS POLLING   -->
<!-- ========================================== -->
<script>
// Variabel kategori bracket aktif global
window.currentBracketCat = '<?= \App\Models\Sports::defaultCategory($sport) ?>';

// 1. Switch Tab Utama (Live, Jadwal, Bracket, Playoff, Teams, Hasil)
function switchTab(tabId) {
  const tabs = ['live', 'jadwal', 'bracket', 'playoff', 'teams', 'hasil'];
  if (!tabs.includes(tabId)) {
    tabId = 'live';
  }

  // Sembunyikan semua panel tab utama
  document.querySelectorAll('.tab-panel').forEach(el => el.classList.add('hidden'));

  // Reset semua tombol tab ke state inactive
  document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.classList.remove('bg-blue-600', 'text-white', 'shadow-lg', 'shadow-blue-600/30');
    btn.classList.add('text-slate-400', 'hover:text-slate-200', 'hover:bg-slate-800/60');
  });

  // Tampilkan panel target
  const targetPanel = document.getElementById('panel-' + tabId);
  if (targetPanel) {
    targetPanel.classList.remove('hidden');
  }

  // Jika tab bracket dibuka, render ulang garis penghubung SVG agar presisi
  if (tabId === 'bracket' && typeof window.drawAllBracketLines === 'function') {
    requestAnimationFrame(() => window.drawAllBracketLines());
  }

  // Berikan highlight pada tombol aktif
  const targetBtn = document.getElementById('btn-tab-' + tabId);
  if (targetBtn) {
    targetBtn.classList.remove('text-slate-400', 'hover:text-slate-200', 'hover:bg-slate-800/60');
    targetBtn.classList.add('bg-blue-600', 'text-white', 'shadow-lg', 'shadow-blue-600/30');
  }

  // Update hash URL tanpa reload
  if (history.replaceState) {
    history.replaceState(null, '', '#' + tabId);
  }
}

// 2. Switch Sub-Tab Kategori Bracket (Khusus Badminton: Ganda Putra vs Ganda Putri)
function switchBracketCategory(catKey) {
  window.currentBracketCat = catKey;
  document.querySelectorAll('.bracket-cat-panel').forEach(p => p.classList.add('hidden'));
  document.querySelectorAll('.subcat-btn').forEach(b => {
    b.classList.remove('bg-blue-600', 'text-white', 'shadow-md', 'shadow-blue-600/30');
    b.classList.add('text-slate-400', 'hover:text-white');
  });

  const targetPanel = document.getElementById('bracket-cat-' + catKey);
  if (targetPanel) {
    targetPanel.classList.remove('hidden');
  }

  const targetBtn = document.getElementById('btn-subcat-' + catKey);
  if (targetBtn) {
    targetBtn.classList.add('bg-blue-600', 'text-white', 'shadow-md', 'shadow-blue-600/30');
    targetBtn.classList.remove('text-slate-400', 'hover:text-white');
  }

  // Render ulang garis bracket untuk kategori yang baru ditampilkan
  if (typeof window.drawAllBracketLines === 'function') {
    requestAnimationFrame(() => window.drawAllBracketLines());
  }
}

// 3. Filter Kategori pada Tab Detail Tim
function filterTeamCategory(catKey) {
  document.querySelectorAll('.teamcat-btn').forEach(b => {
    b.classList.remove('bg-blue-600', 'text-white');
    b.classList.add('text-slate-400', 'hover:text-white');
  });

  const activeBtn = document.getElementById('btn-teamcat-' + catKey);
  if (activeBtn) {
    activeBtn.classList.add('bg-blue-600', 'text-white');
    activeBtn.classList.remove('text-slate-400', 'hover:text-white');
  }

  document.querySelectorAll('.team-card').forEach(card => {
    if (catKey === 'all' || card.dataset.category === catKey) {
      card.classList.remove('hidden');
    } else {
      card.classList.add('hidden');
    }
  });
}

// ==========================================
// SEAMLESS REAL-TIME POLLING (SETINTERVAL 1s)
// ==========================================
let isPolling = false;
let lastStatusHash = null;
let lastLiveScoreHash = null;
let lastBracketHash = null;
let lastStandingsHash = null;
let lastScheduleHash = null;

async function pollLiveScores() {
  if (isPolling) return;
  isPolling = true;

  try {
    const res = await fetch('<?= url($sport . '/live-data') ?>');
    if (!res.ok) return;
    const data = await res.json();
    if (!data.ok) return;

    // 1. Update Badges & Counter
    updateLiveBadges(data.live_count, data.finished_count, data.schedule_count);

    // 2. Perpindahan Status Pertandingan (Live <-> Selesai / Belum Mulai)
    // Jika match_status berubah, re-render DOM komponen Live dan Hasil secara seamless
    if (lastStatusHash !== null && lastStatusHash !== data.status_hash) {
      const liveCont = document.getElementById('live-content-container');
      if (liveCont && data.live_html !== undefined) {
        liveCont.innerHTML = data.live_html;
        tickPublicFutsalClocks();
        updateFullscreenUI();
      }

      const finCont = document.getElementById('finished-content-container');
      if (finCont && data.finished_html !== undefined) {
        finCont.innerHTML = data.finished_html;
      }
    }
    lastStatusHash = data.status_hash;

    // 2b. Simpan hash live score terkini (skor & highlights diupdate secara granular tanpa re-render kontainer)
    lastLiveScoreHash = data.live_score_hash;

    // 3. Sinkronisasi Bagan Bracket jika status atau pertandingan berubah
    if (data.brackets_html && lastBracketHash !== null && lastBracketHash !== data.bracket_hash) {
      for (const [catKey, bHtml] of Object.entries(data.brackets_html)) {
        const bCont = document.getElementById('bracket-cat-content-' + catKey);
        if (bCont) {
          const scrollEl = bCont.querySelector('.bracket-scroll-container');
          const prevScrollLeft = scrollEl ? scrollEl.scrollLeft : 0;

          bCont.innerHTML = bHtml;

          const newScrollEl = bCont.querySelector('.bracket-scroll-container');
          if (newScrollEl && prevScrollLeft) {
            newScrollEl.scrollLeft = prevScrollLeft;
          }
        }
      }
      if (typeof window.drawAllBracketLines === 'function') {
        requestAnimationFrame(() => window.drawAllBracketLines());
      }
    }
    lastBracketHash = data.bracket_hash;

    // 3b. Sinkronisasi Klasemen & Jadwal Grup serta Tab Playoff secara Real-Time
    if (data.standings_html && lastStandingsHash !== null && lastStandingsHash !== data.standings_hash) {
      const standingsCont = document.getElementById('group-standings-container');
      if (standingsCont) {
        standingsCont.innerHTML = data.standings_html;
      }
      const playoffsCont = document.getElementById('playoffs-container');
      if (playoffsCont && data.playoffs_html) {
        playoffsCont.innerHTML = data.playoffs_html;
      }
    }
    lastStandingsHash = data.standings_hash;

    // 3c. Sinkronisasi Tab Jadwal & Lapangan secara Real-Time
    if (data.schedule_html && lastScheduleHash !== null && lastScheduleHash !== data.schedule_hash) {
      const schedCont = document.getElementById('schedule-content-container');
      if (schedCont) {
        schedCont.innerHTML = data.schedule_html;
        if (typeof applyScheduleFilters === 'function') {
          applyScheduleFilters();
        }
      }
    }
    lastScheduleHash = data.schedule_hash;

    // 4. Update Nilai Skor & Active Set dengan micro-flash jika ada pertandingan live
    if (data.matches && data.matches.length > 0) {
      data.matches.forEach(m => {
        // Fallback jika card pertandingan belum ada di DOM (misal baru mulai live)
        if (!document.getElementById(`live-card-${m.id}`)) {
          const liveCont = document.getElementById('live-content-container');
          if (liveCont && data.live_html) {
            liveCont.innerHTML = data.live_html;
            tickPublicFutsalClocks();
            updateFullscreenUI();
          }
        }
        updateMatchInDOM(m);

        // Khusus Futsal: sinkronisasi timeline highlights secara granular tanpa re-render kontainer (zero flicker)
        if (m.sport_type === 'futsal') {
          const mHl = (data.highlights && data.highlights[m.id]) ? data.highlights[m.id] : [];
          syncHighlightsInDOM(m.id, mHl, m.team1_id);
        }
      });
    }

  } catch (e) {
    // Silent fail in background without interrupting UI
    console.debug('Polling live-data:', e);
  } finally {
    isPolling = false;
  }
}

function updateLiveBadges(liveCount, finishedCount, scheduleCount) {
  // Update badge jumlah live di tab button
  const badgeEl = document.getElementById('tab-live-count');
  if (badgeEl) {
    if (liveCount > 0) {
      badgeEl.textContent = liveCount;
      badgeEl.classList.remove('hidden');
    } else {
      badgeEl.classList.add('hidden');
    }
  }

  // Update stat badge di hero header
  const statLiveEl = document.getElementById('stat-live-count');
  if (statLiveEl) {
    statLiveEl.innerHTML = (liveCount > 0)
      ? `<b>${liveCount}</b> Sedang Main`
      : '0 Sedang Main';
  }

  const headerBadge = document.getElementById('header-live-badge');
  if (headerBadge) {
    if (liveCount > 0) {
      headerBadge.textContent = `🔴 ${liveCount} Pertandingan Berlangsung`;
      headerBadge.classList.remove('hidden');
    } else {
      headerBadge.classList.add('hidden');
    }
  }

  const statFinEl = document.getElementById('stat-finished-count');
  if (statFinEl) {
    statFinEl.textContent = finishedCount;
  }

  const tabHasilCount = document.getElementById('tab-hasil-count');
  if (tabHasilCount) {
    tabHasilCount.textContent = finishedCount;
  }

  const tabJadwalCount = document.getElementById('tab-jadwal-count');
  if (tabJadwalCount && scheduleCount !== undefined) {
    tabJadwalCount.textContent = scheduleCount;
  }
}

function updateMatchInDOM(m) {
  const isFutsal = (m.sport_type === 'futsal');
  const isRubber = (m.scoring_system === 'rubber_set');
  const currSet = parseInt(m.current_set || 0);
  const isSetFin = Boolean(parseInt(m.is_set_finished || 0));

  // 1. Update Nilai Set 1 Tim A & B
  updateValueWithFlash(`live-set1-a-${m.id}`, m.set1_a || 0);
  updateValueWithFlash(`live-set1-b-${m.id}`, m.set1_b || 0);

  // 2. Update Nilai Set 2 Tim A & B (dengan carry-over jika Poin Lanjut)
  const s2a = (!isFutsal && !isRubber && parseInt(m.set2_a) === 0 && parseInt(m.set1_a) > 0) ? m.set1_a : (m.set2_a || 0);
  const s2b = (!isFutsal && !isRubber && parseInt(m.set2_b) === 0 && parseInt(m.set1_b) > 0) ? m.set1_b : (m.set2_b || 0);
  updateValueWithFlash(`live-set2-a-${m.id}`, s2a);
  updateValueWithFlash(`live-set2-b-${m.id}`, s2b);

  // 3. Update Nilai Set 3 Tim A & B (jika Rubber)
  if (!isFutsal && isRubber) {
    updateValueWithFlash(`live-set3-a-${m.id}`, m.set3_a || 0);
    updateValueWithFlash(`live-set3-b-${m.id}`, m.set3_b || 0);
  }

  // 4. Update Summary Total Score
  updateValueWithFlash(`live-score1-${m.id}`, m.score1);
  updateValueWithFlash(`live-score2-${m.id}`, m.score2);

  const isHalfTime = isFutsal && (currSet === 1 && isSetFin);
  const isFullTime = isFutsal && (currSet === 2 && isSetFin);

  // 5. Update Timer Futsal Clock data attributes & controls
  if (isFutsal) {
    const clock = document.getElementById(`public-clock-${m.id}`);
    if (clock) {
      if (m.futsal_half_duration !== undefined) clock.dataset.halfDuration = m.futsal_half_duration;
      if (m.futsal_elapsed_seconds !== undefined) clock.dataset.elapsed = m.futsal_elapsed_seconds;
      if (m.futsal_timer_status !== undefined) clock.dataset.status = m.futsal_timer_status;
      if (m.futsal_timer_updated_at !== undefined) clock.dataset.updatedAt = m.futsal_timer_updated_at;

      const halfDur = parseInt(m.futsal_half_duration || clock.dataset.halfDuration || 5, 10);
      const maxSec = halfDur * 120;

      // Jika match full time: waktu otomatis menjadi batas waktu maksimal yang ditentukan admin
      if (isFullTime) {
        clock.dataset.elapsed = maxSec;
        clock.dataset.status = 'stopped';
        clock.textContent = String(Math.floor(maxSec / 60)).padStart(2, '0') + ':' + String(maxSec % 60).padStart(2, '0');
      } else if (isHalfTime) {
        const htSec = halfDur * 60;
        clock.dataset.elapsed = htSec;
        clock.dataset.status = 'paused';
        clock.textContent = String(Math.floor(htSec / 60)).padStart(2, '0') + ':' + String(htSec % 60).padStart(2, '0');
      } else if (m.futsal_timer_status !== 'running') {
        const sec = parseInt(m.futsal_elapsed_seconds || 0, 10);
        clock.textContent = String(Math.floor(sec / 60)).padStart(2, '0') + ':' + String(sec % 60).padStart(2, '0');
      }
    }
    const babakEl = document.getElementById(`public-babak-${m.id}`);
    const babakTextEl = document.getElementById(`public-babak-text-${m.id}`);
    if (babakTextEl) {
      babakTextEl.textContent = isHalfTime ? 'HALF TIME' : (isFullTime ? 'FULL TIME' : ((currSet === 2) ? 'BABAK 2' : 'BABAK 1'));
    }
    if (babakEl) {
      babakEl.className = 'inline-flex items-center gap-1.5 rounded-full px-3 py-0.5 text-[11px] sm:text-xs font-black uppercase tracking-wider ' +
        (isHalfTime ? 'bg-amber-500/20 border border-amber-500/50 text-amber-300 shadow-sm shadow-amber-500/20' : (isFullTime ? 'bg-cyan-500/20 border border-cyan-500/50 text-cyan-300 shadow-sm shadow-cyan-500/20' : 'bg-slate-800/90 border border-slate-700 text-emerald-400 shadow-sm'));
    }
    const dotEl = document.getElementById(`public-dot-${m.id}`);
    if (dotEl) {
      if (!isHalfTime && !isFullTime && m.futsal_timer_status === 'running') {
        dotEl.className = 'h-2 w-2 rounded-full bg-emerald-400 animate-ping';
      } else if (isHalfTime) {
        dotEl.className = 'h-2 w-2 rounded-full bg-amber-400';
      } else if (isFullTime) {
        dotEl.className = 'h-2 w-2 rounded-full bg-cyan-400';
      } else {
        dotEl.className = 'h-2 w-2 rounded-full bg-emerald-400';
      }
    }
  }

  // 6. Update Active Set Highlight
  for (let s = 1; s <= (isRubber ? 3 : 2); s++) {
    const elA = document.getElementById(`live-set${s}-a-${m.id}`);
    const elB = document.getElementById(`live-set${s}-b-${m.id}`);

    const isActive = (s === currSet && !isSetFin);

    const activeClass = 'font-mono text-3xl sm:text-4xl font-black text-amber-300 bg-amber-950/60 border-2 border-amber-500/60 rounded-xl px-3 py-1.5 shadow-lg shadow-amber-500/20 ring-2 ring-amber-400/30';
    const inactiveClass = 'font-mono text-xl sm:text-2xl font-bold text-slate-300 bg-slate-950/80 rounded-xl px-3 py-1.5 border border-slate-800';

    if (elA) elA.className = 'inline-block transition-all ' + (isActive ? activeClass : inactiveClass);
    if (elB) elB.className = 'inline-block transition-all ' + (isActive ? activeClass : inactiveClass);
  }

  // 7. Update Chyron Header
  const chyronEl = document.getElementById(`live-chyron-${m.id}`);
  if (chyronEl) {
    let html = '';
    if (!isFutsal) {
      html += `
        <span class="rounded-xl border border-blue-500/30 bg-blue-950/40 px-2.5 py-1 text-[11px] font-bold text-blue-300 hidden sm:inline-block">
          ${isRubber ? 'Rubber Set' : 'Poin Lanjut'}
        </span>
      `;
    }

    if (isHalfTime) {
      html += `
        <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-500/60 bg-amber-500/20 px-3.5 py-1 text-xs font-black text-amber-300 shadow-md shadow-amber-500/20">
          ⏸️ HALF TIME (ISTIRAHAT)
        </span>
      `;
    } else if (currSet > 0 && !isSetFin) {
      const label = isFutsal ? `BABAK ${currSet} BERLANGSUNG` : ((currSet === 3) ? 'SET 3 (RUBBER GAME) BERLANGSUNG' : `SET ${currSet} BERLANGSUNG`);
      html += `
        <span class="inline-flex items-center gap-1.5 rounded-full border border-red-500/60 bg-red-500/20 px-3.5 py-1 text-xs font-black text-red-300 animate-pulse shadow-md shadow-red-500/20">
          <span class="h-2 w-2 rounded-full bg-red-400 animate-ping"></span>
          ${label}
        </span>
      `;
    } else if (currSet > 0 && isSetFin) {
      html += `
        <span class="inline-flex items-center gap-1.5 rounded-full border border-cyan-500/60 bg-cyan-500/20 px-3.5 py-1 text-xs font-black text-cyan-300">
          ✓ ${isFutsal ? (currSet === 2 ? 'FULL TIME' : `BABAK ${currSet} SELESAI`) : `SET ${currSet} SELESAI`}
        </span>
      `;
    } else {
      html += `
        <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-500/60 bg-amber-500/20 px-3.5 py-1 text-xs font-bold text-amber-300">
          <span class="h-2 w-2 rounded-full bg-amber-400 animate-ping"></span>
          LIVE SCORE
        </span>
      `;
    }
    chyronEl.innerHTML = html;
  }
}

function updateValueWithFlash(elementId, newValue) {
  const el = document.getElementById(elementId);
  if (!el) return;
  const currentVal = el.textContent.trim();
  const nextVal = String(newValue);

  if (currentVal !== nextVal) {
    el.textContent = nextVal;
    // Animasi flash / pulse TV broadcast sesaat (tanpa flicker)
    el.classList.add('scale-110', 'text-amber-400');
    setTimeout(() => {
      el.classList.remove('scale-110', 'text-amber-400');
    }, 400);
  }
}

/**
 * Konfigurasi badge event highlight futsal Football Manager style
 */
const HL_EVENTS = {
  'goal':               { label: 'Gol',                 emoji: '⚽',  badge: 'bg-emerald-500/20 text-emerald-400 border-emerald-500/40' },
  'yellow_card':        { label: 'Kartu Kuning',        emoji: '🟨',  badge: 'bg-amber-500/20 text-amber-300 border-amber-500/40' },
  'double_yellow_card': { label: 'Kartu Kuning Kedua',  emoji: '🟨🟥', badge: 'bg-orange-500/20 text-orange-300 border-orange-500/40' },
  'red_card':           { label: 'Kartu Merah',         emoji: '🟥',  badge: 'bg-rose-500/20 text-rose-400 border-rose-500/40' },
  'substitution':       { label: 'Pertukaran Pemain',   emoji: '🔄',  badge: 'bg-sky-500/20 text-sky-400 border-sky-500/40' },
  'half_time':          { label: 'Half Time',           emoji: '⏸️',  badge: 'bg-amber-500/20 text-amber-300 border-amber-500/50' },
  'full_time':          { label: 'Full Time',           emoji: '🏁',  badge: 'bg-cyan-500/20 text-cyan-300 border-cyan-500/50' }
};

function escapeHtml(str) {
  if (str === null || str === undefined) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function createHighlightItemElement(h, team1Id) {
  if (h.event_type === 'half_time' || h.event_type === 'full_time') {
    const isHt = (h.event_type === 'half_time');
    const div = document.createElement('div');
    div.id = `fm-event-item-${h.id}`;
    div.dataset.hlId = String(h.id);
    div.className = 'flex items-center justify-center my-3 transition-all duration-300 fm-event-animate';
    div.innerHTML = `
      <div class="inline-flex items-center gap-2 rounded-full border ${isHt ? 'border-amber-500/60 bg-amber-950/80 text-amber-300 shadow-amber-500/20' : 'border-cyan-500/60 bg-cyan-950/80 text-cyan-300 shadow-cyan-500/20'} px-4 py-1 text-xs font-black shadow-lg">
        <span class="text-sm">${isHt ? '⏸️' : '🏁'}</span>
        <span>${isHt ? 'HALF TIME' : 'FULL TIME'}</span>
        <span class="font-mono text-[11px] opacity-80">(${escapeHtml(h.minute || '1')}')</span>
      </div>
    `;
    return div;
  }

  const isTeamA = (parseInt(h.team_id, 10) === parseInt(team1Id, 10));
  const evt = HL_EVENTS[h.event_type] || HL_EVENTS['goal'];

  let playerContent = '';
  if (h.event_type === 'substitution' && h.player_out && h.player_in) {
    playerContent = `
      <div class="text-slate-300 text-[11px] font-semibold leading-tight">
        <span class="text-rose-300 font-bold">${escapeHtml(h.player_out)}</span> ➔ <span class="text-emerald-300 font-bold">${escapeHtml(h.player_in)}</span>
      </div>
    `;
  } else {
    playerContent = `
      <div class="font-black text-white truncate max-w-[130px] sm:max-w-[200px] leading-tight">
        ${escapeHtml(h.player_name || '')}
      </div>
    `;
  }

  const badgeBody = `
    <div class="min-w-0">
      ${playerContent}
      <div class="text-[9px] uppercase tracking-wider opacity-80 font-bold mt-0.5">
        ${escapeHtml(evt.label)}
      </div>
    </div>
  `;

  const leftCol = isTeamA
    ? `<div class="justify-self-end text-right min-w-0 max-w-full">
        <div class="inline-flex items-center gap-2 rounded-xl border ${evt.badge} px-3 py-1.5 text-xs shadow-md shadow-black/40 backdrop-blur-sm transition hover:scale-[1.02]">
          ${badgeBody}
          <span class="text-base shrink-0">${evt.emoji}</span>
        </div>
      </div>`
    : `<div></div>`;

  const rightCol = !isTeamA
    ? `<div class="justify-self-start text-left min-w-0 max-w-full">
        <div class="inline-flex items-center gap-2 rounded-xl border ${evt.badge} px-3 py-1.5 text-xs shadow-md shadow-black/40 backdrop-blur-sm transition hover:scale-[1.02]">
          <span class="text-base shrink-0">${evt.emoji}</span>
          ${badgeBody}
        </div>
      </div>`
    : `<div></div>`;

  const div = document.createElement('div');
  div.id = `fm-event-item-${h.id}`;
  div.dataset.hlId = String(h.id);
  div.className = 'grid grid-cols-[1fr_auto_1fr] items-center gap-2 sm:gap-4 transition-all duration-300 fm-event-animate';
  div.innerHTML = `
    ${leftCol}
    <div class="flex flex-col items-center justify-center shrink-0">
      <span class="flex h-7 w-7 items-center justify-center rounded-full bg-slate-900 border-2 border-emerald-500/70 font-mono text-[10px] font-black text-emerald-300 shadow-lg shadow-black/90 ring-2 ring-emerald-500/20">
        ${escapeHtml(h.minute || '1')}'
      </span>
    </div>
    ${rightCol}
  `;
  return div;
}

/**
 * Sinkronisasi daftar highlight di DOM secara granular tanpa kedipan / re-render
 */
function syncHighlightsInDOM(mid, hlList, team1Id) {
  const listEl = document.getElementById(`live-timeline-list-${mid}`);
  const emptyEl = document.getElementById(`live-timeline-empty-${mid}`);
  if (!listEl) return;

  const items = Array.isArray(hlList) ? hlList : [];
  if (emptyEl) {
    if (items.length === 0) {
      emptyEl.classList.remove('hidden');
    } else {
      emptyEl.classList.add('hidden');
    }
  }

  const t1Id = parseInt(team1Id || listEl.dataset.team1Id || 0, 10);
  const incomingMap = new Map();
  items.forEach(h => incomingMap.set(String(h.id), h));

  // 1. Hapus elemen DOM yang sudah tidak ada di data incoming (misal di-cancel wasit)
  const existingDomItems = Array.from(listEl.querySelectorAll('[data-hl-id]'));
  existingDomItems.forEach(el => {
    const hid = el.dataset.hlId;
    if (!incomingMap.has(hid)) {
      el.remove();
    }
  });

  // 2. Sinkronisasi urutan dan tambahkan item baru tanpa menyentuh elemen lama (zero flicker)
  items.forEach((h, idx) => {
    const hid = String(h.id);
    let el = document.getElementById(`fm-event-item-${hid}`);
    if (!el) {
      // Elemen baru masuk dengan animasi halus
      el = createHighlightItemElement(h, t1Id);
    }
    const currentChild = listEl.children[idx];
    if (currentChild !== el) {
      listEl.insertBefore(el, currentChild || null);
    }
  });
}


/**
 * Ticker real-time untuk timer futsal publik (berdetik setiap detik secara presisi)
 */
function tickPublicFutsalClocks() {
  document.querySelectorAll('.public-futsal-clock').forEach(clock => {
    const status = clock.dataset.status || 'stopped';
    const elapsed = parseInt(clock.dataset.elapsed || '0', 10);
    const halfDur = parseInt(clock.dataset.halfDuration || '5', 10);
    const maxSec = halfDur * 120;
    let currentSec = elapsed;

    if (status === 'running') {
      const updatedAtStr = clock.dataset.updatedAt;
      if (updatedAtStr) {
        const updatedTime = new Date(updatedAtStr.replace(' ', 'T')).getTime();
        const now = Date.now();
        const diff = Math.floor((now - updatedTime) / 1000);
        if (!isNaN(diff) && diff >= 0) {
          currentSec = Math.min(maxSec, elapsed + diff);
        }
      }
    }

    const mins = Math.floor(currentSec / 60);
    const secs = currentSec % 60;
    clock.textContent = String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
  });
}

// Jalankan polling dan ticker clock
setInterval(pollLiveScores, 1000);
setInterval(tickPublicFutsalClocks, 1000);
setTimeout(tickPublicFutsalClocks, 100);

/**
 * Fitur Fullscreen Live Score (Mendukung Semua Cabor: Futsal, Badminton, Tenis Meja)
 */
function toggleFullScreenLive() {
  const panel = document.getElementById('panel-live');
  if (!panel) return;
  const isFs = Boolean(document.fullscreenElement || document.webkitFullscreenElement);
  if (!isFs) {
    if (panel.requestFullscreen) {
      panel.requestFullscreen();
    } else if (panel.webkitRequestFullscreen) {
      panel.webkitRequestFullscreen();
    } else if (panel.msRequestFullscreen) {
      panel.msRequestFullscreen();
    }
  } else {
    if (document.exitFullscreen) {
      document.exitFullscreen();
    } else if (document.webkitExitFullscreen) {
      document.webkitExitFullscreen();
    }
  }
}

function toggleFullScreenCard(cardId) {
  const card = document.getElementById(cardId);
  if (!card) return;
  const currentFs = document.fullscreenElement || document.webkitFullscreenElement;
  if (currentFs === card) {
    if (document.exitFullscreen) {
      document.exitFullscreen();
    } else if (document.webkitExitFullscreen) {
      document.webkitExitFullscreen();
    }
  } else {
    if (card.requestFullscreen) {
      card.requestFullscreen();
    } else if (card.webkitRequestFullscreen) {
      card.webkitRequestFullscreen();
    } else if (card.msRequestFullscreen) {
      card.msRequestFullscreen();
    }
  }
}

function updateFullscreenUI() {
  const currentFs = document.fullscreenElement || document.webkitFullscreenElement;
  const isFs = Boolean(currentFs);
  const fsIcon = document.getElementById('fs-icon');
  const fsLabel = document.getElementById('fs-label');
  const exitBar = document.getElementById('fullscreen-exit-bar');

  if (exitBar) {
    if (isFs && currentFs && currentFs.id === 'panel-live') {
      exitBar.classList.remove('hidden');
    } else {
      exitBar.classList.add('hidden');
    }
  }

  if (fsIcon && fsLabel) {
    if (isFs && currentFs && currentFs.id === 'panel-live') {
      fsIcon.textContent = '🗗';
      fsLabel.textContent = 'Keluar Layar Penuh';
    } else {
      fsIcon.textContent = '⛶';
      fsLabel.textContent = 'Layar Penuh';
    }
  }

  document.querySelectorAll('.card-fs-btn').forEach(btn => {
    const targetId = btn.dataset.cardId;
    if (isFs && currentFs && currentFs.id === targetId) {
      btn.innerHTML = '<span class="text-xs">🗗</span>';
      btn.title = 'Keluar Layar Penuh (ESC)';
      btn.classList.add('bg-blue-600', 'text-white', 'border-blue-400');
      btn.classList.remove('bg-slate-800/60', 'text-slate-300');
    } else {
      btn.innerHTML = '<span class="text-xs">⛶</span>';
      btn.title = 'Layar Penuh Pertandingan Ini';
      btn.classList.remove('bg-blue-600', 'text-white', 'border-blue-400');
      btn.classList.add('bg-slate-800/60', 'text-slate-300');
    }
  });
}

document.addEventListener('fullscreenchange', updateFullscreenUI);
document.addEventListener('webkitfullscreenchange', updateFullscreenUI);

// Shortcut keyboard 'F' untuk toggle fullscreen saat di tab Live
document.addEventListener('keydown', (e) => {
  if ((e.key === 'f' || e.key === 'F') && !e.ctrlKey && !e.metaKey && !e.altKey) {
    const activePanel = document.querySelector('.tab-panel:not(.hidden)');
    const isLiveActive = activePanel && activePanel.id === 'panel-live';
    const tag = (document.activeElement && document.activeElement.tagName) ? document.activeElement.tagName.toLowerCase() : '';
    if (isLiveActive && tag !== 'input' && tag !== 'textarea' && tag !== 'select') {
      e.preventDefault();
      toggleFullScreenLive();
    }
  }
});

// Inisialisasi saat DOM selesai dimuat
document.addEventListener('DOMContentLoaded', () => {
  // Inisialisasi sub-tab kategori bracket pertama
  <?php $defaultCat = \App\Models\Sports::defaultCategory($sport); ?>
  switchBracketCategory('<?= $defaultCat ?>');

  // Inisialisasi tab utama berdasarkan hash URL atau status live
  let hash = window.location.hash.replace('#', '');
  if (hash === 'klasemen') hash = 'bracket';

  if (['live', 'jadwal', 'bracket', 'playoff', 'teams', 'hasil'].includes(hash)) {
    switchTab(hash);
  } else {
    <?php if ($liveCount > 0): ?>
      switchTab('live');
    <?php else: ?>
      switchTab('bracket');
    <?php endif; ?>
  }
});
</script>
