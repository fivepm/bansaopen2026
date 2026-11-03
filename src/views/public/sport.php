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

  <!-- Tab 2: Bracket / Klasemen -->
  <button type="button" id="btn-tab-bracket" onclick="switchTab('bracket')" class="tab-btn flex shrink-0 items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition-all">
    <span><?= $isGroup ? '📊' : '🌳' ?></span>
    <span><?= $isGroup ? 'Klasemen &amp; Jadwal' : 'Bagan Pertandingan' ?></span>
  </button>

  <!-- Tab 3: Detail Tim -->
  <button type="button" id="btn-tab-teams" onclick="switchTab('teams')" class="tab-btn flex shrink-0 items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition-all">
    <span>👥</span>
    <span>Detail Tim</span>
    <span class="rounded-full bg-slate-800 px-2 py-0.2 text-[11px] font-bold text-slate-300"><?= $teamCount ?></span>
  </button>

  <!-- Tab 4: Hasil Pertandingan -->
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
      <p class="text-xs sm:text-sm text-slate-400">Pembaruan otomatis tiap 1 detik langsung dari meja wasit arena pertandingan.</p>
    </div>
    <div class="flex items-center gap-2">
      <span class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-500/40 bg-emerald-950/40 px-3 py-1.5 text-xs font-bold text-emerald-400">
        <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
        Live Polling 1s
      </span>
      <?php if ($liveCount > 0): ?>
        <span id="header-live-badge" class="rounded-xl border border-red-500/30 bg-red-950/40 px-3 py-1.5 text-xs font-semibold text-red-400">
          🔴 <?= $liveCount ?> Pertandingan Berlangsung
        </span>
      <?php endif; ?>
    </div>
  </div>

  <div id="live-content-container">
    <?php include dirname(__DIR__) . '/partials/live_matches_content.php'; ?>
  </div>
</section>

<!-- ========================================== -->
<!-- KONTEN TAB 2: BRACKET / KLASEMEN           -->
<!-- ========================================== -->
<section id="panel-bracket" class="tab-panel hidden mt-6">
  <?php if ($isGroup): ?>
    <!-- Mode Sistem Grup (Liga) -->
    <div class="mb-5">
      <h2 class="text-xl sm:text-2xl font-black text-white">Klasemen &amp; Jadwal Fase Grup</h2>
      <p class="text-xs sm:text-sm text-slate-400">Tabel klasemen diperbarui otomatis berdasarkan akumulasi hasil skor pertandingan.</p>
    </div>

    <?php if (empty($standings)): ?>
      <div class="rounded-2xl border border-dashed border-slate-800 bg-slate-900/40 p-10 text-center text-slate-500">
        Grup turnamen belum dibuat oleh panitia.
      </div>
    <?php else: ?>
      <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <?php foreach ($standings as $gid => $s): $g = $s['group']; $rows = $s['rows']; ?>
          <div class="space-y-4">
            <?php include dirname(__DIR__) . '/partials/standings_table.php'; ?>

            <details class="group rounded-2xl border border-slate-800 bg-slate-900/70 p-4 shadow-md transition-all">
              <summary class="flex cursor-pointer items-center justify-between font-bold text-sm text-slate-200 hover:text-white">
                <span>📅 Jadwal &amp; Skor Pertandingan <?= e($g['name']) ?></span>
                <span class="text-xs text-blue-400 group-open:rotate-180 transition-transform">▼</span>
              </summary>
              <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                <?php foreach ($groupMatches[$gid] ?? [] as $m): $total = 0; ?>
                  <div>
                    <div class="mb-1 text-xs text-slate-400">Hari ke-<?= (int)$m['round'] ?></div>
                    <?php include dirname(__DIR__) . '/partials/match_card.php'; ?>
                  </div>
                <?php endforeach; ?>
                <?php if (empty($groupMatches[$gid])): ?>
                  <p class="text-xs italic text-slate-500">Jadwal pertandingan grup ini belum dibuat.</p>
                <?php endif; ?>
              </div>
            </details>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

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
// 1. Switch Tab Utama (Live, Bracket, Teams, Hasil)
function switchTab(tabId) {
  const tabs = ['live', 'bracket', 'teams', 'hasil'];
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
let lastBracketHash = null;

async function pollLiveScores() {
  if (isPolling) return;
  isPolling = true;

  try {
    const res = await fetch('<?= url($sport . '/live-data') ?>');
    if (!res.ok) return;
    const data = await res.json();
    if (!data.ok) return;

    // 1. Update Badges & Counter
    updateLiveBadges(data.live_count, data.finished_count);

    // 2. Perpindahan Status Pertandingan (Live <-> Selesai / Belum Mulai)
    // Jika match_status berubah, re-render DOM komponen Live dan Hasil secara seamless
    if (lastStatusHash !== null && lastStatusHash !== data.status_hash) {
      const liveCont = document.getElementById('live-content-container');
      if (liveCont && data.live_html !== undefined) {
        liveCont.innerHTML = data.live_html;
      }

      const finCont = document.getElementById('finished-content-container');
      if (finCont && data.finished_html !== undefined) {
        finCont.innerHTML = data.finished_html;
      }
    }
    lastStatusHash = data.status_hash;

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
    }
    lastBracketHash = data.bracket_hash;

    // 4. Update Nilai Skor & Active Set dengan micro-flash jika ada pertandingan live
    if (data.matches && data.matches.length > 0) {
      data.matches.forEach(m => {
        // Fallback jika elemen belum ada di DOM
        if (!document.getElementById(`live-set1-a-${m.id}`)) {
          const liveCont = document.getElementById('live-content-container');
          if (liveCont && data.live_html) {
            liveCont.innerHTML = data.live_html;
          }
        }
        updateMatchInDOM(m);
      });
    }

  } catch (e) {
    // Silent fail in background without interrupting UI
    console.debug('Polling live-data:', e);
  } finally {
    isPolling = false;
  }
}

function updateLiveBadges(liveCount, finishedCount) {
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
}

function updateMatchInDOM(m) {
  const isRubber = (m.scoring_system === 'rubber_set');
  const currSet = parseInt(m.current_set || 0);
  const isSetFin = Boolean(parseInt(m.is_set_finished || 0));

  // 1. Update Nilai Set 1 Tim A & B
  updateValueWithFlash(`live-set1-a-${m.id}`, m.set1_a || 0);
  updateValueWithFlash(`live-set1-b-${m.id}`, m.set1_b || 0);

  // 2. Update Nilai Set 2 Tim A & B (dengan carry-over jika Poin Lanjut)
  const s2a = (!isRubber && parseInt(m.set2_a) === 0 && parseInt(m.set1_a) > 0) ? m.set1_a : (m.set2_a || 0);
  const s2b = (!isRubber && parseInt(m.set2_b) === 0 && parseInt(m.set1_b) > 0) ? m.set1_b : (m.set2_b || 0);
  updateValueWithFlash(`live-set2-a-${m.id}`, s2a);
  updateValueWithFlash(`live-set2-b-${m.id}`, s2b);

  // 3. Update Nilai Set 3 Tim A & B (jika Rubber)
  if (isRubber) {
    updateValueWithFlash(`live-set3-a-${m.id}`, m.set3_a || 0);
    updateValueWithFlash(`live-set3-b-${m.id}`, m.set3_b || 0);
  }

  // 4. Update Summary Total Score
  updateValueWithFlash(`live-score1-${m.id}`, m.score1);
  updateValueWithFlash(`live-score2-${m.id}`, m.score2);

  // 5. Update Active Set Highlight (Pindahkan ukuran font besar & highlight ke set yang sedang aktif)
  for (let s = 1; s <= (isRubber ? 3 : 2); s++) {
    const elA = document.getElementById(`live-set${s}-a-${m.id}`);
    const elB = document.getElementById(`live-set${s}-b-${m.id}`);

    const isActive = (s === currSet && !isSetFin);

    const activeClass = 'font-mono text-3xl sm:text-4xl font-black text-amber-300 bg-amber-950/60 border-2 border-amber-500/60 rounded-xl px-3 py-1.5 shadow-lg shadow-amber-500/20 ring-2 ring-amber-400/30';
    const inactiveClass = 'font-mono text-xl sm:text-2xl font-bold text-slate-300 bg-slate-950/80 rounded-xl px-3 py-1.5 border border-slate-800';

    if (elA) elA.className = 'inline-block transition-all ' + (isActive ? activeClass : inactiveClass);
    if (elB) elB.className = 'inline-block transition-all ' + (isActive ? activeClass : inactiveClass);
  }

  // 6. Update Chyron Header
  const chyronEl = document.getElementById(`live-chyron-${m.id}`);
  if (chyronEl) {
    let html = `
      <span class="rounded-xl border border-blue-500/30 bg-blue-950/40 px-2.5 py-1 text-[11px] font-bold text-blue-300 hidden sm:inline-block">
        ${isRubber ? 'Rubber Set' : 'Poin Lanjut'}
      </span>
    `;

    if (currSet > 0 && !isSetFin) {
      const label = (currSet === 3) ? 'SET 3 (RUBBER GAME) BERLANGSUNG' : `SET ${currSet} BERLANGSUNG`;
      html += `
        <span class="inline-flex items-center gap-1.5 rounded-full border border-red-500/60 bg-red-500/20 px-3.5 py-1 text-xs font-black text-red-300 animate-pulse shadow-md shadow-red-500/20">
          <span class="h-2 w-2 rounded-full bg-red-400 animate-ping"></span>
          ${label}
        </span>
      `;
    } else if (currSet > 0 && isSetFin) {
      html += `
        <span class="inline-flex items-center gap-1.5 rounded-full border border-cyan-500/60 bg-cyan-500/20 px-3.5 py-1 text-xs font-black text-cyan-300">
          ⏸️ SET ${currSet} SELESAI
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

// Jalankan polling setiap 1000ms (1 detik)
setInterval(pollLiveScores, 1000);

// Inisialisasi saat DOM selesai dimuat
document.addEventListener('DOMContentLoaded', () => {
  // Inisialisasi sub-tab kategori bracket pertama
  <?php $defaultCat = \App\Models\Sports::defaultCategory($sport); ?>
  switchBracketCategory('<?= $defaultCat ?>');

  // Inisialisasi tab utama berdasarkan hash URL atau status live
  let hash = window.location.hash.replace('#', '');
  if (hash === 'klasemen') hash = 'bracket';

  if (['live', 'bracket', 'teams', 'hasil'].includes(hash)) {
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
