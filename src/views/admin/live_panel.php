<?php
/**
 * Halaman Khusus Live Panel / Operator Skor Wasit
 * Antarmuka interaktif dengan tombol + dan - per set via Fetch API
 */
$isWasitRole = \App\Core\Auth::isOperator();

// Kumpulkan data wasit dari daftar pertandingan live yang sedang berlangsung
$allRefMains = [];
$allRefLine1 = [];
$allRefLine2 = [];
foreach ($liveMatches as $lm) {
    if (!empty($lm['referee_main'])) $allRefMains[] = $lm['referee_main'];
    if (!empty($lm['referee_line1'])) $allRefLine1[] = $lm['referee_line1'];
    if (!empty($lm['referee_line2'])) $allRefLine2[] = $lm['referee_line2'];
}
$uniqueRefMains = array_values(array_unique($allRefMains));
$uniqueRefLines = array_values(array_unique(array_merge($allRefLine1, $allRefLine2)));

// Nama petugas wasit: jika role wasit dan admin sudah mengatur wasit utama di match,
// gunakan nama wasit utama dari data pertandingan tersebut.
if ($isWasitRole && !empty($uniqueRefMains)) {
    $petugasWasitName = implode(' / ', $uniqueRefMains);
} else {
    $petugasWasitName = \App\Core\Auth::user()['name'] ?? 'Wasit';
}
?>
<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
  <div>
    <div class="flex items-center gap-2">
      <span class="flex h-3 w-3 rounded-full bg-red-500 animate-ping"></span>
      <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">Live Panel / Operator Skor</h1>
    </div>
    <p class="text-xs sm:text-sm text-slate-400 mt-1">
      Papan kendali wasit untuk menambah/mengurangi poin skor langsung ke database secara real-time.
    </p>
  </div>

  <div class="flex flex-wrap items-center gap-2">
    <div class="flex flex-wrap items-center gap-2 rounded-2xl border border-slate-700/80 bg-slate-800/90 px-3.5 py-1.5 text-xs text-slate-300 shadow-sm">
      <div class="flex items-center gap-1.5">
        <span class="text-amber-400 text-sm">👨‍⚖️</span>
        <span class="text-slate-400">Petugas Wasit:</span>
        <b class="text-white font-bold" id="header-petugas-wasit"><?= e($petugasWasitName) ?></b>
        <span class="ml-1 text-[11px] text-slate-400">(<?= $isWasitRole ? 'Role: Wasit' : e(\App\Core\Auth::roleLabel()) ?>)</span>
      </div>

      <div class="flex items-center gap-1.5 border-l border-slate-700 pl-2 text-slate-400 <?= empty($uniqueRefLines) ? 'hidden' : '' ?>" id="header-wasit-garis-wrap">
        <span class="text-blue-400">🚩 Garis:</span>
        <b class="text-slate-200 font-semibold" id="header-wasit-garis-text"><?= e(implode(', ', $uniqueRefLines)) ?></b>
      </div>
    </div>
  </div>
</div>

<!-- Filter Cabang Olahraga -->
<div class="mb-6 flex flex-wrap items-center gap-2">
  <span class="text-xs font-bold uppercase tracking-wider text-slate-400 mr-1">Filter Cabor:</span>
  <a href="<?= url('admin/live-panel') ?>" class="badge <?= empty($sportFilter) ? 'finished !font-bold' : '' ?>">
    Semua Cabor
  </a>
  <?php foreach ($sports as $slug => $s): ?>
    <a href="<?= url("admin/live-panel?sport=$slug") ?>" class="badge <?= ($sportFilter === $slug) ? 'finished !font-bold' : '' ?>">
      <?= $s['emoji'] ?> <?= e($s['label']) ?>
    </a>
  <?php endforeach; ?>
</div>

<?php if (empty($liveMatches)): ?>
  <div class="flex flex-col items-center justify-center rounded-3xl border border-dashed border-slate-800 bg-slate-900/50 p-12 text-center shadow-inner">
    <span class="text-5xl">⚡</span>
    <h3 class="mt-4 text-lg font-bold text-white">Tidak Ada Pertandingan yang Berstatus LIVE</h3>
    <p class="mt-1.5 max-w-md text-xs sm:text-sm text-slate-400">
      Saat ini belum ada pertandingan yang sedang berjalan di lapangan.
      <?php if (\App\Core\Auth::isSuperAdmin()): ?>
        Masuk ke menu cabor (Bracket / Pertandingan) lalu tekan tombol <b>"Set as Live"</b> untuk memulai pertandingan.
      <?php else: ?>
        Silakan hubungi Super Admin untuk menyalakan status Live pada pertandingan yang akan dimulai.
      <?php endif; ?>
    </p>
    <?php if (\App\Core\Auth::isSuperAdmin()): ?>
      <div class="mt-6 flex gap-3">
        <a href="<?= url('admin/badminton/matches') ?>" class="btn small">Buka Bracket Badminton</a>
      </div>
    <?php endif; ?>
  </div>
<?php else: ?>
  <div class="space-y-6">
    <?php foreach ($liveMatches as $m): ?>
      <?php
        $scoringSystem = $m['scoring_system'] ?? 'rubber_set';
        $isRubber = ($scoringSystem === 'rubber_set');
        $isFutsal = (($m['sport_type'] ?? '') === 'futsal');
        $term = $isFutsal ? 'Babak' : 'Set';
        $matchHighlights = $highlights[$m['id']] ?? [];
        $t1Members = parse_team_members($m['team1_members'] ?? null);
        $t2Members = parse_team_members($m['team2_members'] ?? null);
      ?>
      <div id="match-card-<?= $m['id'] ?>"
           data-match-id="<?= (int)$m['id'] ?>"
           data-sport-type="<?= e($m['sport_type'] ?? '') ?>"
           data-team1-id="<?= (int)$m['team1_id'] ?>"
           data-team1-name="<?= e($m['team1_name'] ?? 'Tim 1') ?>"
           data-team1-members='<?= json_encode($t1Members, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>'
           data-team2-id="<?= (int)$m['team2_id'] ?>"
           data-team2-name="<?= e($m['team2_name'] ?? 'Tim 2') ?>"
           data-team2-members='<?= json_encode($t2Members, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>'
           data-highlights='<?= json_encode($matchHighlights, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>'
           data-futsal-half-duration="<?= (int)($m['futsal_half_duration'] ?? 5) ?>"
           data-futsal-elapsed-seconds="<?= (int)($m['futsal_elapsed_seconds'] ?? 0) ?>"
           data-futsal-timer-status="<?= e($m['futsal_timer_status'] ?? 'stopped') ?>"
           data-current-set="<?= (int)($m['current_set'] ?? 0) ?>"
           data-is-set-finished="<?= (int)($m['is_set_finished'] ?? 0) ?>"
           class="live-match-card relative rounded-3xl border-2 border-red-500/60 bg-gradient-to-b from-slate-900 via-slate-900 to-slate-950 p-6 shadow-2xl shadow-red-500/10 transition-all">
        
        <!-- STEALTH UNLOCK HOTSPOT DI SUDUT KANAN ATAS (TERSEMBUNYI SAAT TERKUNCI) -->
        <div id="stealth-hotspot-<?= $m['id'] ?>"
             onclick="handleStealthHotspotClick(<?= $m['id'] ?>)"
             title="Area Buka Kunci"
             class="referee-stealth-hotspot hidden absolute top-0 right-0 w-24 h-24 z-[100001] cursor-pointer group flex items-start justify-end p-2.5 select-none"
             aria-label="Unlock Area">
          <!-- Ikon gembok samar (hanya terlihat jika di-hover kursor) -->
          <span class="opacity-0 group-hover:opacity-40 text-slate-500 text-xs transition duration-200">🔒</span>
        </div>

        <!-- FLOATING UNLOCK DIALOG (MUNCUL HANYA JIKA HOTSPOT KANAN ATAS DIKLIK) -->
        <div id="unlock-panel-<?= $m['id'] ?>"
             class="hidden fixed top-3 right-3 z-[100002] w-80 max-w-[calc(100vw-1.5rem)] rounded-2xl border border-amber-500/50 bg-slate-900/95 p-4 shadow-2xl shadow-black/90 backdrop-blur-xl text-slate-100">
          <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-500/20 text-xl text-amber-400">
              🔒
            </div>
            <div class="flex-1">
              <h4 class="text-sm font-black text-white">Mode Wasit Terkunci</h4>
              <p class="mt-1 text-xs text-slate-400 leading-relaxed">
                Layar dikunci agar wasit tetap fokus pada pertandingan ini dan tidak sengaja keluar.
              </p>
            </div>
          </div>
          <div class="mt-4 flex items-center justify-end gap-2 border-t border-slate-800 pt-3">
            <button type="button"
                    onclick="hideUnlockPanel(<?= $m['id'] ?>)"
                    class="rounded-xl border border-slate-700 bg-slate-800/80 px-3 py-1.5 text-xs font-semibold text-slate-300 hover:bg-slate-700 hover:text-white transition">
              Tetap Kunci
            </button>
            <button type="button"
                    onclick="exitRefereeFullscreen(<?= $m['id'] ?>)"
                    class="rounded-xl bg-rose-600 hover:bg-rose-500 px-3.5 py-1.5 text-xs font-bold text-white shadow-lg shadow-rose-600/30 transition flex items-center gap-1.5 active:scale-95">
              🔓 Buka Kunci &amp; Keluar
            </button>
          </div>
          <div class="mt-2 text-[10px] text-slate-500 text-right">
            Otomatis tersembunyi kembali dalam <span id="unlock-timer-<?= $m['id'] ?>">6</span>s
          </div>
        </div>

        <!-- OVERLAY WAJIB LANDSCAPE UNTUK HP -->
        <div id="landscape-guard-<?= $m['id'] ?>"
             class="referee-landscape-guard hidden fixed inset-0 z-[100000] bg-[#070a12]/98 backdrop-blur-lg flex-col items-center justify-center p-6 text-center text-white">
          <div class="relative mb-5">
            <div class="w-20 h-20 rounded-2xl bg-blue-600/20 border border-blue-500/40 flex items-center justify-center text-4xl shadow-xl shadow-blue-500/20 animate-pulse">
              📱
            </div>
            <div class="absolute -top-2 -right-2 w-7 h-7 rounded-full bg-amber-500 flex items-center justify-center text-xs font-black text-black">
              ↻
            </div>
          </div>
          <h3 class="text-xl sm:text-2xl font-black text-white tracking-tight">Putar HP ke Mode Landscape</h3>
          <p class="mt-2 text-xs sm:text-sm text-slate-300 max-w-sm leading-relaxed">
            Untuk kenyamanan wasit dalam mencatat skor, tampilan Mode Fokus diwajibkan dalam posisi horizontal (landscape).
          </p>
          <div class="mt-4 flex items-center gap-2 text-xs text-amber-300 bg-amber-500/10 border border-amber-500/30 rounded-xl px-3.5 py-2">
            <span>💡</span> Pastikan fitur <b>Auto-Rotate / Rotasi Otomatis</b> pada HP Anda aktif.
          </div>
          <button type="button"
                  onclick="dismissLandscapeGuard(<?= $m['id'] ?>)"
                  class="mt-6 text-xs text-slate-400 hover:text-white underline">
            Tetap lanjutkan dalam mode portrait
          </button>
        </div>

        <!-- Header Kartu Live Match -->
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-800 pb-4">
          <div class="flex items-center gap-3">
            <span class="text-2xl"><?= \App\Models\Sports::emoji($m['sport_type']) ?></span>
            <div>
              <div class="text-sm font-black text-white flex items-center gap-2">
                <span><?= e(sport_label($m['sport_type'])) ?></span>
                <?php if (!empty($m['category']) && $m['category'] !== 'umum'): ?>
                  <span class="rounded bg-slate-800 px-2 py-0.5 text-xs text-blue-400">
                    <?= e(category_label($m['category'])) ?>
                  </span>
                <?php endif; ?>
              </div>
              <div class="text-xs text-slate-400 mt-0.5">
                <?= e(match_stage_label($m, 0)) ?>
                <?= $m['court'] ? ' · ' . e($m['court']) : '' ?>
              </div>
            </div>
          </div>

          <div class="flex flex-wrap items-center gap-2.5">
            <!-- Tombol Fullscreen / Mode Fokus Wasit -->
            <button type="button"
                    onclick="enterRefereeFullscreen(<?= $m['id'] ?>)"
                    id="btn-fullscreen-trigger-<?= $m['id'] ?>"
                    title="Buka Mode Fokus Wasit (Layar Penuh)"
                    class="btn-enter-fullscreen flex items-center gap-1.5 rounded-xl border border-indigo-500/40 bg-indigo-600/20 hover:bg-indigo-600 text-indigo-300 hover:text-white px-3 py-1.5 text-xs font-bold transition shadow-sm active:scale-95">
              <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
              </svg>
              <span>Mode Fokus (Fullscreen)</span>
            </button>

            <!-- Badge Mode Fokus Aktif (Hanya terlihat saat Fullscreen) -->
            <span class="fullscreen-referee-badge hidden items-center gap-1.5 rounded-full border border-emerald-500/40 bg-emerald-500/15 px-3 py-1 text-xs font-bold text-emerald-400">
              <span class="h-2 w-2 rounded-full bg-emerald-400 animate-ping"></span>
              MODE FOKUS WASIT (🔒 TERKUNCI)
            </span>

            <!-- Badge Petugas Wasit (Terlihat saat Fullscreen) -->
            <span class="fullscreen-referee-badge hidden items-center gap-2 rounded-full border border-amber-500/40 bg-amber-500/15 px-3.5 py-1 text-xs font-bold text-amber-300">
              <span>👨‍⚖️ Wasit:</span>
              <span class="text-white font-black" id="fs-ref-main-<?= $m['id'] ?>">
                <?= e(!empty($m['referee_main']) ? $m['referee_main'] : (\App\Core\Auth::user()['name'] ?? 'Wasit')) ?>
              </span>
              <?php if (!empty($m['referee_line1']) || !empty($m['referee_line2'])): ?>
                <span class="text-slate-400 font-normal">| 🚩 Garis:</span>
                <span class="text-blue-300 font-semibold" id="fs-ref-lines-<?= $m['id'] ?>">
                  <?= e(implode(', ', array_filter([$m['referee_line1'] ?? '', $m['referee_line2'] ?? '']))) ?>
                </span>
              <?php endif; ?>
            </span>

            <!-- Badge Sistem Scoring / Format Pertandingan -->
            <?php if ($isFutsal): ?>
              <?php
                $isPlayoff = (($m['stage'] ?? '') === 'knockout' || ($m['stage'] ?? '') === 'playoff' || in_array($m['bracket_side'] ?? '', ['final', 'third_place'], true));
              ?>
              <span class="rounded-xl border <?= $isPlayoff ? 'border-amber-500/40 bg-amber-950/40 text-amber-300' : 'border-emerald-500/30 bg-emerald-950/40 text-emerald-400' ?> px-3 py-1 text-xs font-bold">
                <?= $isPlayoff ? '🏆 Playoff' : '⚽ Grup' ?>
              </span>
            <?php else: ?>
              <span class="rounded-xl border border-blue-500/30 bg-blue-950/40 px-3 py-1 text-xs font-bold text-blue-400">
                <?= $isRubber ? '🏆 Rubber Set (Best of 3)' : '📈 Poin Lanjut (Akumulasi 2 Set)' ?>
              </span>
            <?php endif; ?>

            <span class="inline-flex items-center gap-1.5 rounded-full border border-red-500/40 bg-red-500/20 px-3 py-1 text-xs font-black text-red-400">
              <span class="h-2 w-2 rounded-full bg-red-400 animate-ping"></span>
              LIVE
            </span>
          </div>
        </div>

        <!-- Banner Informasi Perangkat Wasit yang Bertugas (Wasit Utama 1 orang & Wasit Garis 2 orang) -->
        <div class="mt-4 flex flex-wrap items-center justify-between gap-2.5 rounded-2xl border border-slate-800 bg-slate-950/80 p-3 sm:px-4 text-xs shadow-inner" id="match-referee-box-<?= $m['id'] ?>">
          <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
            <span class="inline-flex items-center gap-1.5 font-bold uppercase tracking-wider text-[11px] text-amber-400 shrink-0">
              <span>👨‍⚖️</span>
              <span>Perangkat Wasit:</span>
            </span>

            <!-- Wasit Utama -->
            <span class="inline-flex items-center gap-1.5 rounded-xl border border-amber-500/40 bg-amber-500/10 px-3 py-1 text-xs font-semibold text-amber-300">
              <span class="opacity-80">Wasit Utama:</span>
              <b class="text-white font-black" id="card-ref-main-<?= $m['id'] ?>">
                <?= e(!empty($m['referee_main']) ? $m['referee_main'] : 'Belum diatur panitia') ?>
              </b>
            </span>

            <!-- Wasit Garis 1 -->
            <span class="inline-flex items-center gap-1.5 rounded-xl border border-blue-500/30 bg-blue-500/10 px-2.5 py-1 text-xs font-semibold text-blue-300">
              <span class="opacity-80">🚩 Wasit Garis 1:</span>
              <b class="text-white font-bold" id="card-ref-line1-<?= $m['id'] ?>">
                <?= e(!empty($m['referee_line1']) ? $m['referee_line1'] : '-') ?>
              </b>
            </span>

            <!-- Wasit Garis 2 -->
            <span class="inline-flex items-center gap-1.5 rounded-xl border border-indigo-500/30 bg-indigo-500/10 px-2.5 py-1 text-xs font-semibold text-indigo-300">
              <span class="opacity-80">🚩 Wasit Garis 2:</span>
              <b class="text-white font-bold" id="card-ref-line2-<?= $m['id'] ?>">
                <?= e(!empty($m['referee_line2']) ? $m['referee_line2'] : '-') ?>
              </b>
            </span>
          </div>

          <?php if (\App\Core\Auth::isSuperAdmin()): ?>
            <button type="button"
                    onclick="openLiveRefereeModal(<?= (int)$m['id'] ?>, '<?= e(addslashes($m['sport_type'])) ?>', '<?= e(addslashes($m['team1_name'] ?? 'Tim 1')) ?>', '<?= e(addslashes($m['team2_name'] ?? 'Tim 2')) ?>', '<?= e(addslashes($m['referee_main'] ?? '')) ?>', '<?= e(addslashes($m['referee_line1'] ?? '')) ?>', '<?= e(addslashes($m['referee_line2'] ?? '')) ?>')"
                    class="rounded-xl border border-amber-500/30 bg-amber-500/10 hover:bg-amber-500/20 text-amber-300 px-3 py-1 text-xs font-bold transition flex items-center gap-1 shadow-sm">
              <span>✏️</span>
              <span>Atur Wasit</span>
            </button>
          <?php endif; ?>
        </div>

        <!-- Scoreboard Ringkasan Utama -->
        <div class="my-6 grid grid-cols-[1fr_auto_1fr] items-center gap-4 text-center">
          <!-- Tim 1 -->
          <div class="space-y-1">
            <span class="text-base sm:text-xl font-black text-white line-clamp-2">
              <?= e($m['team1_name'] ?? 'Tim 1') ?>
            </span>
            <?php if (in_array(($m['sport_type'] ?? ''), ['badminton', 'pingpong'], true) && !empty($t1Members)): ?>
              <div class="text-[11px] sm:text-xs font-medium text-slate-400 leading-snug"><?= e(implode(', ', $t1Members)) ?></div>
            <?php endif; ?>
            <div class="text-xs text-slate-400">
              <?= $isFutsal ? 'Total Gol' : ($isRubber ? 'Sets Won' : 'Total Poin') ?>:
              <span id="summary-score1-<?= $m['id'] ?>" class="font-mono text-2xl sm:text-3xl font-black text-blue-400">
                <?= (int)$m['score1'] ?>
              </span>
            </div>
          </div>

          <!-- VS Badge -->
          <div class="flex flex-col items-center justify-center px-2">
            <span class="rounded-xl border border-slate-800 bg-slate-950 px-3 py-1 text-xs font-black text-slate-500">
              VS
            </span>
          </div>

          <!-- Tim 2 -->
          <div class="space-y-1">
            <span class="text-base sm:text-xl font-black text-white line-clamp-2">
              <?= e($m['team2_name'] ?? 'Tim 2') ?>
            </span>
            <?php if (in_array(($m['sport_type'] ?? ''), ['badminton', 'pingpong'], true) && !empty($t2Members)): ?>
              <div class="text-[11px] sm:text-xs font-medium text-slate-400 leading-snug"><?= e(implode(', ', $t2Members)) ?></div>
            <?php endif; ?>
            <div class="text-xs text-slate-400">
              <?= $isFutsal ? 'Total Gol' : ($isRubber ? 'Sets Won' : 'Total Poin') ?>:
              <span id="summary-score2-<?= $m['id'] ?>" class="font-mono text-2xl sm:text-3xl font-black text-indigo-400">
                <?= (int)$m['score2'] ?>
              </span>
            </div>
          </div>
        </div>



        <?php
          $currentSet = (int)($m['current_set'] ?? 0);
          $isSetFinished = (bool)($m['is_set_finished'] ?? false);
          $isHalfTime = ($isFutsal && $currentSet === 1 && $isSetFinished);

          // Kelayakan tombol Mulai Set / Babak:
          $canStartSet1 = ($currentSet === 0 || $currentSet === 1);
          $canStartSet2 = (($currentSet === 1 && $isSetFinished) || $currentSet === 2);
          $setsA = ((int)$m['set1_a'] > (int)$m['set1_b'] ? 1 : 0) + ((int)$m['set2_a'] > (int)$m['set2_b'] ? 1 : 0);
          $setsB = ((int)$m['set1_b'] > (int)$m['set1_a'] ? 1 : 0) + ((int)$m['set2_b'] > (int)$m['set2_a'] ? 1 : 0);
          $canStartSet3 = !$isFutsal && $isRubber && (($currentSet === 2 && $isSetFinished && $setsA === 1 && $setsB === 1) || $currentSet === 3);
          $halfDur = (int)($m['futsal_half_duration'] ?? 5);
          $halfDurFormatted = sprintf('%02d:00', $halfDur);
        ?>

        <?php if ($isFutsal): ?>
          <!-- ======================================================== -->
          <!-- KONTROL PERTANDINGAN & TIMER TERPADU (KHUSUS FUTSAL)     -->
          <!-- ======================================================== -->
          <div id="futsal-control-card-<?= $m['id'] ?>" class="mb-5 rounded-2xl border-2 border-emerald-500/40 bg-slate-950/95 p-5 shadow-2xl shadow-emerald-950/30">
            
            <!-- Baris Atas: Display Stopwatch Besar, Status Babak, & Waktu Tersimpan -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-800/80 pb-4">
              
              <!-- Sisi Kiri: Stopwatch & Status Babak -->
              <div class="flex items-center gap-3.5">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-emerald-500/15 border border-emerald-500/40 text-3xl text-emerald-400 shadow-inner">
                  ⏱️
                </div>
                <div>
                  <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Timer Pertandingan Futsal</span>
                    <span id="badge-active-set-<?= $m['id'] ?>">
                      <?php if ($isHalfTime): ?>
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-500/50 bg-amber-500/20 px-2.5 py-0.5 text-[10px] font-black text-amber-300">
                          ⏸️ HALF TIME (ISTIRAHAT)
                        </span>
                      <?php elseif ($currentSet > 0 && !$isSetFinished): ?>
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-500/50 bg-emerald-500/20 px-2.5 py-0.5 text-[10px] font-black text-emerald-300">
                          <span class="h-2 w-2 rounded-full bg-emerald-400 animate-ping"></span>
                          BABAK <?= $currentSet ?> SEDANG BERLANGSUNG
                        </span>
                      <?php elseif ($currentSet === 2 && $isSetFinished): ?>
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-cyan-500/50 bg-cyan-500/20 px-2.5 py-0.5 text-[10px] font-black text-cyan-300">
                          ✓ WAKTU NORMAL SELESAI
                        </span>
                      <?php else: ?>
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-500/50 bg-amber-500/20 px-2.5 py-0.5 text-[10px] font-bold text-amber-300">
                          ⚠️ BELUM DIMULAI · SIAP MULAI BABAK 1
                        </span>
                      <?php endif; ?>
                    </span>
                  </div>
                  
                  <div class="flex items-center gap-3 mt-1">
                    <span id="timer-display-<?= $m['id'] ?>" class="font-mono text-3xl sm:text-5xl font-black text-emerald-400 tracking-wider drop-shadow-md">
                      00:00
                    </span>
                    <span id="timer-status-badge-<?= $m['id'] ?>" class="rounded-full bg-slate-800 px-3 py-1 text-xs font-black text-slate-400">
                      BERHENTI
                    </span>
                  </div>

                  <div id="timer-target-info-<?= $m['id'] ?>" class="text-[11px] text-slate-400 font-semibold mt-1">
                    <?php if ($currentSet === 0): ?>
                      Target Babak 1: 00:00 - <?= $halfDurFormatted ?> (Durasi Babak: <?= $halfDur ?> Menit)
                    <?php elseif ($currentSet === 1 && !$isSetFinished): ?>
                      Target Babak 1: 00:00 - <?= $halfDurFormatted ?>
                    <?php elseif ($isHalfTime): ?>
                      <span class="text-amber-300 font-bold">⏸️ Half Time! Babak 1 Selesai (<?= $halfDurFormatted ?>). Klik "Mulai Babak 2" untuk melanjutkan.</span>
                    <?php elseif ($currentSet === 2 && !$isSetFinished): ?>
                      Target Babak 2: <?= $halfDurFormatted ?> - <?= sprintf('%02d:00', $halfDur * 2) ?>
                    <?php else: ?>
                      <span class="text-emerald-400 font-bold">✓ Waktu Normal Selesai! Pertandingan Siap Diselesaikan.</span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>

              <!-- Sisi Kanan: Tampilan Durasi Pertandingan Ditentukan Admin -->
              <div class="flex flex-col items-start md:items-end justify-center">
                <div id="saved-duration-badge-<?= $m['id'] ?>" class="rounded-2xl border border-amber-500/30 bg-amber-500/10 px-4 py-2 text-left md:text-right shadow-sm">
                  <span class="text-[10px] font-bold uppercase tracking-wider text-amber-400/90 block">Durasi Babak (Admin):</span>
                  <span class="font-mono text-base sm:text-lg font-black text-white">
                    <b id="saved-duration-text-<?= $m['id'] ?>" class="text-amber-300"><?= $halfDur ?> Menit</b>
                    <span class="text-xs font-semibold text-slate-400"> (<?= $halfDurFormatted ?>/babak)</span>
                  </span>
                </div>
              </div>
            </div>

            <!-- Baris Bawah: Tombol Mulai Babak (Timer Otomatis Berjalan), Jeda, Selesai, dan Reset -->
            <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
              <div class="flex flex-wrap items-center gap-2">
                
                <!-- Tombol Mulai Babak 1 (Timer Langsung Berjalan Otomatis) -->
                <button type="button"
                        onclick="startSet(<?= $m['id'] ?>, 1)"
                        id="btn-set-<?= $m['id'] ?>-1"
                        class="btn-flow-set flex items-center gap-1.5 rounded-xl px-4 py-2 text-xs transition active:scale-95 <?= ($currentSet === 1) ? 'bg-emerald-600 border border-emerald-500 text-white font-black shadow-lg shadow-emerald-600/30 ring-2 ring-emerald-400/40' : 'bg-emerald-700 hover:bg-emerald-600 border border-emerald-600 text-white font-bold shadow-md' ?>"
                        <?= (!$canStartSet1) ? 'disabled="disabled" title="Babak 1 sudah selesai"' : '' ?>>
                  ▶ Mulai Babak 1
                </button>

                <!-- Tombol Mulai Babak 2 (Timer Langsung Melanjutkan Otomatis dari Babak 1) -->
                <button type="button"
                        onclick="startSet(<?= $m['id'] ?>, 2)"
                        id="btn-set-<?= $m['id'] ?>-2"
                        class="btn-flow-set flex items-center gap-1.5 rounded-xl px-4 py-2 text-xs transition active:scale-95 <?= ($currentSet === 2) ? 'bg-emerald-600 border border-emerald-500 text-white font-black shadow-lg shadow-emerald-600/30 ring-2 ring-emerald-400/40' : (($canStartSet2 && $currentSet !== 2) ? 'bg-blue-600 border border-blue-500 text-white font-black animate-pulse shadow-md shadow-blue-600/30' : 'bg-slate-800/90 border border-slate-700 text-slate-300 font-bold hover:bg-slate-700 hover:text-white') ?> <?= (!$canStartSet2) ? 'opacity-30 cursor-not-allowed pointer-events-none' : '' ?>"
                        <?= (!$canStartSet2) ? 'disabled="disabled" title="Selesaikan Babak 1 terlebih dahulu"' : '' ?>>
                  ▶ Mulai Babak 2
                </button>

                <!-- Tombol Jeda / Lanjutkan Timer Babak yang Aktif -->
                <button type="button"
                        id="btn-timer-toggle-<?= $m['id'] ?>"
                        onclick="toggleTimer(<?= $m['id'] ?>)"
                        class="flex items-center gap-1.5 rounded-xl border border-amber-500/40 bg-amber-500/10 hover:bg-amber-500/20 text-amber-300 font-bold px-3 py-2 text-xs active:scale-95 transition"
                        title="Jeda sementara atau lanjutkan timer babak yang sedang berjalan">
                  ⏸ Jeda Timer
                </button>

                <!-- Tombol Selesai Babak (Hanya jika babak sedang berjalan) -->
                <div id="wrapper-btn-finish-top-<?= $m['id'] ?>">
                  <?php if ($currentSet > 0 && !$isSetFinished): ?>
                    <button type="button"
                            onclick="finishSet(<?= $m['id'] ?>, <?= $currentSet ?>)"
                            id="btn-top-finish-<?= $m['id'] ?>"
                            class="flex items-center gap-1.5 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-black px-3.5 py-2 text-xs shadow-lg shadow-cyan-600/30 active:scale-95 transition">
                      ✓ Selesai Babak <?= $currentSet ?>
                    </button>
                  <?php elseif ($currentSet > 0 && $isSetFinished): ?>
                    <button type="button"
                            onclick="reopenSet(<?= $m['id'] ?>)"
                            id="btn-top-reopen-<?= $m['id'] ?>"
                            class="flex items-center gap-1.5 rounded-xl border border-slate-700 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold px-3.5 py-2 text-xs active:scale-95 transition">
                      ↺ Koreksi Babak <?= $currentSet ?>
                    </button>
                  <?php endif; ?>
                </div>

                <!-- Tombol Reset Babak -->
                <button type="button"
                        onclick="resetTimer(<?= $m['id'] ?>)"
                        class="flex items-center gap-1.5 rounded-xl border border-slate-700 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold px-3 py-2 text-xs active:scale-95 transition"
                        title="Reset timer babak ini (Babak 1 kembali ke 00:00, Babak 2 kembali ke batas akhir Babak 1)">
                  ↺ Reset Babak
                </button>

              </div>
            </div>

            <!-- Petunjuk Ringkas Wasit -->
            <p id="helper-msg-<?= $m['id'] ?>" class="text-[11px] text-slate-400 mt-3 pt-3 border-t border-slate-800/80">
              <?php if ($currentSet === 0): ?>
                <span class="text-amber-300 font-semibold">Instruksi:</span> Tekan tombol <b>"▶ Mulai Babak 1"</b> untuk memulai pertandingan. Timer akan otomatis berjalan sesuai durasi yang telah ditentukan admin (<?= $halfDur ?> menit per babak).
              <?php elseif ($currentSet === 1 && !$isSetFinished): ?>
                <span class="text-emerald-400 font-semibold">Babak 1 Berjalan:</span> Timer sedang aktif. Catat gol, kartu, atau pergantian pemain. Tekan <b>"✓ Selesai Babak 1"</b> saat turun minum.
              <?php elseif ($isHalfTime): ?>
                <span class="text-cyan-300 font-semibold">Half Time (Istirahat):</span> Babak 1 telah selesai. Tekan <b>"▶ Mulai Babak 2"</b> untuk melanjutkan babak kedua.
              <?php elseif ($currentSet === 2 && !$isSetFinished): ?>
                <span class="text-emerald-400 font-semibold">Babak 2 Berjalan:</span> Waktu melanjutkan dari batas akhir babak 1. Tekan <b>"✓ Selesai Babak 2"</b> saat peluit panjang berbunyi.
              <?php elseif ($currentSet === 2 && $isSetFinished): ?>
                <span class="text-emerald-400 font-semibold">Waktu Normal Selesai:</span> Seluruh babak telah selesai! Silakan periksa skor dan tekan tombol <b>"✓ Selesai Pertandingan"</b> di bawah.
              <?php endif; ?>
            </p>
          </div>
        <?php else: ?>
          <!-- ALUR PERTANDINGAN KHUSUS BADMINTON & PINGPONG (SET 1, 2, 3) -->
          <div class="mb-5 rounded-2xl border border-slate-800 bg-slate-950/80 p-4 shadow-inner">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
              <div class="flex items-center gap-2.5">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400"><?= $term ?> Aktif:</span>
                <span id="badge-active-set-<?= $m['id'] ?>">
                  <?php if ($currentSet > 0 && !$isSetFinished): ?>
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-500/40 bg-emerald-500/20 px-3 py-1 text-xs font-black text-emerald-400">
                      <span class="h-2 w-2 rounded-full bg-emerald-400 animate-ping"></span>
                      SET <?= $currentSet ?><?= ($currentSet === 3) ? ' (RUBBER)' : '' ?> SEDANG AKTIF
                    </span>
                  <?php elseif ($currentSet > 0 && $isSetFinished): ?>
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-cyan-500/40 bg-cyan-500/20 px-3 py-1 text-xs font-black text-cyan-300">
                      ✓ SET <?= $currentSet ?> TELAH SELESAI
                    </span>
                  <?php else: ?>
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-500/40 bg-amber-500/20 px-3 py-1 text-xs font-bold text-amber-300">
                      ⚠️ Belum Ada Set Aktif · Pilih Set di Samping
                    </span>
                  <?php endif; ?>
                </span>
              </div>

            <!-- Tombol Pemilihan Set/Babak & Selesai -->
            <div class="flex flex-wrap items-center gap-2">
              <!-- Tombol Selesai (Hanya saat ada yang sedang berjalan) -->
              <div id="wrapper-btn-finish-top-<?= $m['id'] ?>">
                <?php if ($currentSet > 0 && !$isSetFinished): ?>
                  <button type="button"
                          onclick="finishSet(<?= $m['id'] ?>, <?= $currentSet ?>)"
                          id="btn-top-finish-<?= $m['id'] ?>"
                          class="flex items-center gap-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-black px-3.5 py-1.5 text-xs shadow-lg shadow-emerald-600/30 active:scale-95 transition">
                    ✓ Selesai <?= $term ?> <?= $currentSet ?>
                  </button>
                <?php elseif ($currentSet > 0 && $isSetFinished): ?>
                  <button type="button"
                          onclick="reopenSet(<?= $m['id'] ?>)"
                          id="btn-top-reopen-<?= $m['id'] ?>"
                          class="flex items-center gap-1.5 rounded-xl border border-slate-700 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold px-3 py-1.5 text-xs active:scale-95 transition">
                    ↺ Koreksi <?= $term ?> <?= $currentSet ?>
                  </button>
                <?php endif; ?>
              </div>

              <!-- Tombol Mulai 1 -->
              <button type="button"
                      onclick="startSet(<?= $m['id'] ?>, 1)"
                      id="btn-set-<?= $m['id'] ?>-1"
                      class="btn-flow-set flex items-center gap-1.5 rounded-xl px-3.5 py-1.5 text-xs transition active:scale-95 <?= ($currentSet === 1) ? 'bg-emerald-600 border border-emerald-500 text-white font-black shadow-lg shadow-emerald-600/30 ring-2 ring-emerald-400/40' : 'bg-slate-800/90 border border-slate-700 text-slate-300 font-bold hover:bg-slate-700 hover:text-white' ?>"
                      <?= (!$canStartSet1) ? 'disabled="disabled" title="' . $term . ' 1 sudah selesai"' : '' ?>>
                ▶ Mulai <?= $term ?> 1
              </button>

              <!-- Tombol Mulai 2 (Terkunci sebelum 1 Selesai) -->
              <button type="button"
                      onclick="startSet(<?= $m['id'] ?>, 2)"
                      id="btn-set-<?= $m['id'] ?>-2"
                      class="btn-flow-set flex items-center gap-1.5 rounded-xl px-3.5 py-1.5 text-xs transition active:scale-95 <?= ($currentSet === 2) ? 'bg-emerald-600 border border-emerald-500 text-white font-black shadow-lg shadow-emerald-600/30 ring-2 ring-emerald-400/40' : (($canStartSet2 && $currentSet !== 2) ? 'bg-blue-600 border border-blue-500 text-white font-black animate-pulse shadow-md shadow-blue-600/30' : 'bg-slate-800/90 border border-slate-700 text-slate-300 font-bold hover:bg-slate-700 hover:text-white') ?> <?= (!$canStartSet2) ? 'opacity-30 cursor-not-allowed pointer-events-none' : '' ?>"
                      <?= (!$canStartSet2) ? 'disabled="disabled" title="Selesaikan ' . $term . ' 1 terlebih dahulu"' : '' ?>>
                ▶ Mulai <?= $term ?> 2
              </button>

              <!-- Tombol Mulai Set 3 (Hanya jika Rubber Set untuk badminton/pingpong) -->
              <?php if (!$isFutsal && $isRubber): ?>
                <button type="button"
                        onclick="startSet(<?= $m['id'] ?>, 3)"
                        id="btn-set-<?= $m['id'] ?>-3"
                        class="btn-flow-set flex items-center gap-1.5 rounded-xl px-3.5 py-1.5 text-xs transition active:scale-95 <?= ($currentSet === 3) ? 'bg-emerald-600 border border-emerald-500 text-white font-black shadow-lg shadow-emerald-600/30 ring-2 ring-emerald-400/40' : (($canStartSet3 && $currentSet !== 3) ? 'bg-amber-600 border border-amber-500 text-white font-black animate-pulse shadow-md shadow-amber-600/30' : 'bg-slate-800/90 border border-slate-700 text-slate-300 font-bold hover:bg-slate-700 hover:text-white') ?> <?= (!$canStartSet3) ? 'opacity-30 cursor-not-allowed pointer-events-none' : '' ?>"
                        <?= (!$canStartSet3) ? 'disabled="disabled" title="Terkunci sebelum Set 2 selesai atau skor seri 1-1"' : '' ?>>
                  ▶ Mulai Set 3
                </button>
              <?php endif; ?>
            </div>
          </div>

          <p id="helper-msg-<?= $m['id'] ?>" class="text-[11px] text-slate-400 mt-2.5">
            <?php if ($currentSet === 0): ?>
              <span class="text-amber-300 font-semibold">Instruksi:</span> Tekan tombol <b>"Mulai Set 1"</b> untuk memulai pertandingan.
            <?php elseif ($currentSet === 1 && !$isSetFinished): ?>
              <span class="text-emerald-400 font-semibold">Set 1 Aktif:</span> Tombol 'Mulai Set 2' terkunci. Wasit wajib menekan tombol <b>"✓ Selesai Set 1"</b> setelah Set 1 berakhir agar bisa memilih Set 2.
            <?php elseif ($currentSet === 1 && $isSetFinished): ?>
              <span class="text-cyan-300 font-semibold">Set 1 Selesai:</span> Tombol <b>"▶ Mulai Set 2"</b> kini terbuka! Klik tombol tersebut untuk memulai Set 2.
            <?php elseif ($currentSet === 2 && !$isSetFinished): ?>
              <span class="text-emerald-400 font-semibold">Set 2 Aktif:</span> Sedang dimainkan <?= !$isRubber ? '(akumulasi lanjutan)' : '' ?>. Tekan tombol <b>"✓ Selesai Set 2"</b> jika set ini berakhir.
            <?php elseif ($currentSet === 2 && $isSetFinished): ?>
              <?php if ($isRubber && $setsA === 1 && $setsB === 1): ?>
                <span class="text-amber-300 font-semibold">Seri 1-1:</span> Set 2 selesai! Tombol <b>"▶ Mulai Set 3"</b> kini terbuka untuk babak penentuan.
              <?php else: ?>
                <span class="text-emerald-400 font-semibold">Pertandingan Selesai:</span> Pemenang sudah ditentukan! Silakan tekan tombol <b>"✓ Selesai Pertandingan"</b> di bawah.
              <?php endif; ?>
            <?php elseif ($currentSet === 3 && !$isSetFinished): ?>
              <span class="text-emerald-400 font-semibold">Set 3 Aktif:</span> Babak Rubber Game dimainkan. Tekan tombol <b>"✓ Selesai Set 3"</b> setelah set penentuan berakhir.
            <?php elseif ($currentSet === 3 && $isSetFinished): ?>
              <span class="text-emerald-400 font-semibold">Set 3 Selesai:</span> Silakan tekan tombol <b>"✓ Selesai Pertandingan"</b> untuk memajukan pemenang di bracket.
            <?php endif; ?>
          </p>
        </div>
      <?php endif; ?>

        <?php if (!$isFutsal): ?>
        <!-- Interactive Point Controls (Babak 1, Babak 2, opsional Set 3 untuk Badminton/Pingpong) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 <?= $isRubber ? 'lg:grid-cols-3' : '' ?> gap-4 pt-2">
          
          <!-- BABAK / SET 1 -->
          <?php
            $isSet1Active = ($currentSet === 1);
            $isSet1Editable = ($isSet1Active && !$isSetFinished);
          ?>
          <div id="box-set-<?= $m['id'] ?>-1" class="rounded-2xl p-4 transition-all duration-300 flex flex-col justify-between <?= $isSet1Active ? ($isSetFinished ? 'border-2 border-cyan-500/80 bg-slate-900/90 shadow-xl' : 'border-2 border-emerald-500/90 bg-slate-900/90 shadow-xl shadow-emerald-500/10 ring-2 ring-emerald-500/20') : 'border border-slate-800 bg-slate-950/40 opacity-60' ?>">
            <div>
              <div class="mb-3 text-center text-xs font-black uppercase tracking-wider text-slate-400 border-b border-slate-800/80 pb-2 flex items-center justify-between">
                <span><?= strtoupper($term) ?> 1</span>
                <span id="badge-set-<?= $m['id'] ?>-1">
                  <?php if ($isSet1Active && !$isSetFinished): ?>
                    <span class="rounded-full bg-emerald-950/90 border border-emerald-500/50 px-2.5 py-0.5 text-[10px] font-black text-emerald-300 flex items-center gap-1">
                      <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-ping"></span> AKTIF DIMAINKAN
                    </span>
                  <?php elseif ($currentSet >= 1 && ($isSetFinished || $currentSet > 1)): ?>
                    <span class="rounded-full bg-cyan-950/80 border border-cyan-500/50 px-2.5 py-0.5 text-[10px] font-bold text-cyan-300">
                      ✓ SELESAI (TERKUNCI)
                    </span>
                  <?php else: ?>
                    <span class="rounded-full bg-slate-900 border border-slate-800 px-2.5 py-0.5 text-[10px] font-bold text-slate-500">
                      🔒 TERKUNCI
                    </span>
                  <?php endif; ?>
                </span>
              </div>

              <?php if ($isFutsal): ?>
                <!-- Skor Futsal Babak 1 (Otomatis dari Highlight Gol) -->
                <div class="space-y-2.5 mb-3">
                  <div class="flex items-center justify-between gap-2 rounded-xl border border-slate-800 bg-slate-950/80 p-2.5">
                    <span class="text-xs font-bold text-slate-300 truncate max-w-[120px]" title="<?= e($m['team1_name'] ?? 'Tim 1') ?>">
                      <?= e($m['team1_name'] ?? 'Tim 1') ?>
                    </span>
                    <span id="val-<?= $m['id'] ?>-1-a" class="font-mono text-xl font-black text-emerald-400">
                      <?= (int)$m['set1_a'] ?> Gol
                    </span>
                  </div>
                  <div class="flex items-center justify-between gap-2 rounded-xl border border-slate-800 bg-slate-950/80 p-2.5">
                    <span class="text-xs font-bold text-slate-300 truncate max-w-[120px]" title="<?= e($m['team2_name'] ?? 'Tim 2') ?>">
                      <?= e($m['team2_name'] ?? 'Tim 2') ?>
                    </span>
                    <span id="val-<?= $m['id'] ?>-1-b" class="font-mono text-xl font-black text-emerald-400">
                      <?= (int)$m['set1_b'] ?> Gol
                    </span>
                  </div>
                  <div class="rounded-lg bg-emerald-950/40 border border-emerald-500/20 px-2 py-1 text-[10px] text-emerald-400 font-medium text-center">
                    ⚽ Skor bertambah otomatis saat wasit mencatat "Gol" di bawah
                  </div>
                </div>
              <?php else: ?>
                <!-- Tim 1 Set 1 (Badminton / Pingpong) -->
                <div class="flex items-center justify-between gap-2 mb-3">
                  <span class="text-xs font-bold text-slate-300 truncate max-w-[100px] sm:max-w-[120px]" title="<?= e($m['team1_name'] ?? 'Tim 1') ?>">
                    <?= e($m['team1_name'] ?? 'Tim 1') ?>
                  </span>
                  <div class="flex items-center gap-2">
                    <button type="button" <?= !$isSet1Editable ? 'disabled="disabled"' : '' ?> onclick="updatePoint(<?= $m['id'] ?>, 1, 'a', -1)" class="score-btn btn-score-<?= $m['id'] ?>-1 flex h-9 w-9 items-center justify-center rounded-xl border border-slate-700 bg-slate-800 text-base font-bold text-slate-200 hover:bg-slate-700 active:scale-95 transition <?= !$isSet1Editable ? 'opacity-30 cursor-not-allowed pointer-events-none' : '' ?>">
                      −
                    </button>
                    <span id="val-<?= $m['id'] ?>-1-a" class="font-mono text-2xl font-black text-white min-w-[36px] text-center">
                      <?= (int)$m['set1_a'] ?>
                    </span>
                    <button type="button" <?= !$isSet1Editable ? 'disabled="disabled"' : '' ?> onclick="updatePoint(<?= $m['id'] ?>, 1, 'a', 1)" class="score-btn btn-score-<?= $m['id'] ?>-1 flex h-9 w-9 items-center justify-center rounded-xl bg-blue-600 text-base font-bold text-white hover:bg-blue-500 active:scale-95 transition shadow-md shadow-blue-600/30 <?= !$isSet1Editable ? 'opacity-30 cursor-not-allowed pointer-events-none' : '' ?>">
                      +
                    </button>
                  </div>
                </div>

                <!-- Tim 2 Set 1 (Badminton / Pingpong) -->
                <div class="flex items-center justify-between gap-2">
                  <span class="text-xs font-bold text-slate-300 truncate max-w-[100px] sm:max-w-[120px]" title="<?= e($m['team2_name'] ?? 'Tim 2') ?>">
                    <?= e($m['team2_name'] ?? 'Tim 2') ?>
                  </span>
                  <div class="flex items-center gap-2">
                    <button type="button" <?= !$isSet1Editable ? 'disabled="disabled"' : '' ?> onclick="updatePoint(<?= $m['id'] ?>, 1, 'b', -1)" class="score-btn btn-score-<?= $m['id'] ?>-1 flex h-9 w-9 items-center justify-center rounded-xl border border-slate-700 bg-slate-800 text-base font-bold text-slate-200 hover:bg-slate-700 active:scale-95 transition <?= !$isSet1Editable ? 'opacity-30 cursor-not-allowed pointer-events-none' : '' ?>">
                      −
                    </button>
                    <span id="val-<?= $m['id'] ?>-1-b" class="font-mono text-2xl font-black text-white min-w-[36px] text-center">
                      <?= (int)$m['set1_b'] ?>
                    </span>
                    <button type="button" <?= !$isSet1Editable ? 'disabled="disabled"' : '' ?> onclick="updatePoint(<?= $m['id'] ?>, 1, 'b', 1)" class="score-btn btn-score-<?= $m['id'] ?>-1 flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-600 text-base font-bold text-white hover:bg-indigo-500 active:scale-95 transition shadow-md shadow-indigo-600/30 <?= !$isSet1Editable ? 'opacity-30 cursor-not-allowed pointer-events-none' : '' ?>">
                      +
                    </button>
                  </div>
                </div>
              <?php endif; ?>
            </div>

            <!-- Footer Box 1: Tombol Selesai / Koreksi -->
            <div id="footer-set-<?= $m['id'] ?>-1" class="mt-4 pt-3 border-t border-slate-800/80 flex items-center justify-between gap-2">
              <?php if ($currentSet === 1 && !$isSetFinished): ?>
                <span class="text-[11px] text-slate-400">Poin sedang dicatat</span>
                <button type="button"
                        onclick="finishSet(<?= $m['id'] ?>, 1)"
                        class="flex items-center gap-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-3 py-1.5 text-xs shadow-md shadow-emerald-600/30 active:scale-95 transition">
                  ✓ Selesai <?= $term ?> 1
                </button>
              <?php elseif ($currentSet === 1 && $isSetFinished): ?>
                <span class="text-[11px] text-cyan-300 font-bold">✓ <?= $term ?> 1 Telah Selesai</span>
                <button type="button"
                        onclick="reopenSet(<?= $m['id'] ?>)"
                        class="text-xs text-slate-400 hover:text-white underline">
                  ↺ Koreksi Skor
                </button>
              <?php elseif ($currentSet > 1): ?>
                <span class="text-[11px] text-slate-400 font-medium">✓ <?= $term ?> 1 Selesai</span>
                <span class="text-[10px] text-slate-500">Terkunci</span>
              <?php else: ?>
                <span class="text-[11px] text-slate-500 font-medium">Belum Dimulai</span>
                <span class="text-[10px] text-slate-600">Terkunci</span>
              <?php endif; ?>
            </div>
          </div>

          <!-- BABAK / SET 2 -->
          <?php
            $isSet2Active = ($currentSet === 2);
            $isSet2Editable = ($isSet2Active && !$isSetFinished);
            $set2AVal = (!$isFutsal && !$isRubber && (int)$m['set2_a'] === 0 && (int)$m['set1_a'] > 0) ? (int)$m['set1_a'] : (int)$m['set2_a'];
            $set2BVal = (!$isFutsal && !$isRubber && (int)$m['set2_b'] === 0 && (int)$m['set1_b'] > 0) ? (int)$m['set1_b'] : (int)$m['set2_b'];
          ?>
          <div id="box-set-<?= $m['id'] ?>-2" class="rounded-2xl p-4 transition-all duration-300 flex flex-col justify-between <?= $isSet2Active ? ($isSetFinished ? 'border-2 border-cyan-500/80 bg-slate-900/90 shadow-xl' : 'border-2 border-emerald-500/90 bg-slate-900/90 shadow-xl shadow-emerald-500/10 ring-2 ring-emerald-500/20') : 'border border-slate-800 bg-slate-950/40 opacity-60' ?>">
            <div>
              <div class="mb-3 text-center text-xs font-black uppercase tracking-wider text-slate-400 border-b border-slate-800/80 pb-2 flex items-center justify-between gap-1 flex-wrap">
                <div class="flex items-center gap-1.5">
                  <span><?= strtoupper($term) ?> 2</span>
                  <?php if (!$isFutsal && !$isRubber): ?>
                    <span class="rounded bg-amber-950/70 border border-amber-500/40 px-1.5 py-0.5 text-[9px] font-bold text-amber-300 normal-case">
                      Carry-Over
                    </span>
                  <?php endif; ?>
                </div>
                <span id="badge-set-<?= $m['id'] ?>-2">
                  <?php if ($isSet2Active && !$isSetFinished): ?>
                    <span class="rounded-full bg-emerald-950/90 border border-emerald-500/50 px-2.5 py-0.5 text-[10px] font-black text-emerald-300 flex items-center gap-1">
                      <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-ping"></span> AKTIF DIMAINKAN
                    </span>
                  <?php elseif ($currentSet >= 2 && ($isSetFinished || $currentSet > 2)): ?>
                    <span class="rounded-full bg-cyan-950/80 border border-cyan-500/50 px-2.5 py-0.5 text-[10px] font-bold text-cyan-300">
                      ✓ SELESAI (TERKUNCI)
                    </span>
                  <?php else: ?>
                    <span class="rounded-full bg-slate-900 border border-slate-800 px-2.5 py-0.5 text-[10px] font-bold text-slate-500">
                      🔒 TERKUNCI
                    </span>
                  <?php endif; ?>
                </span>
              </div>

              <?php if ($isFutsal): ?>
                <!-- Skor Futsal Babak 2 (Otomatis dari Highlight Gol) -->
                <div class="space-y-2.5 mb-3">
                  <div class="flex items-center justify-between gap-2 rounded-xl border border-slate-800 bg-slate-950/80 p-2.5">
                    <span class="text-xs font-bold text-slate-300 truncate max-w-[120px]" title="<?= e($m['team1_name'] ?? 'Tim 1') ?>">
                      <?= e($m['team1_name'] ?? 'Tim 1') ?>
                    </span>
                    <span id="val-<?= $m['id'] ?>-2-a" class="font-mono text-xl font-black text-emerald-400">
                      <?= (int)$m['set2_a'] ?> Gol
                    </span>
                  </div>
                  <div class="flex items-center justify-between gap-2 rounded-xl border border-slate-800 bg-slate-950/80 p-2.5">
                    <span class="text-xs font-bold text-slate-300 truncate max-w-[120px]" title="<?= e($m['team2_name'] ?? 'Tim 2') ?>">
                      <?= e($m['team2_name'] ?? 'Tim 2') ?>
                    </span>
                    <span id="val-<?= $m['id'] ?>-2-b" class="font-mono text-xl font-black text-emerald-400">
                      <?= (int)$m['set2_b'] ?> Gol
                    </span>
                  </div>
                  <div class="rounded-lg bg-emerald-950/40 border border-emerald-500/20 px-2 py-1 text-[10px] text-emerald-400 font-medium text-center">
                    ⚽ Skor bertambah otomatis saat wasit mencatat "Gol" di bawah
                  </div>
                </div>
              <?php else: ?>
                <!-- Tim 1 Set 2 (Badminton / Pingpong) -->
                <div class="flex items-center justify-between gap-2 mb-3">
                  <span class="text-xs font-bold text-slate-300 truncate max-w-[100px] sm:max-w-[120px]" title="<?= e($m['team1_name'] ?? 'Tim 1') ?>">
                    <?= e($m['team1_name'] ?? 'Tim 1') ?>
                  </span>
                  <div class="flex items-center gap-2">
                    <button type="button" <?= !$isSet2Editable ? 'disabled="disabled"' : '' ?> onclick="updatePoint(<?= $m['id'] ?>, 2, 'a', -1)" class="score-btn btn-score-<?= $m['id'] ?>-2 flex h-9 w-9 items-center justify-center rounded-xl border border-slate-700 bg-slate-800 text-base font-bold text-slate-200 hover:bg-slate-700 active:scale-95 transition <?= !$isSet2Editable ? 'opacity-30 cursor-not-allowed pointer-events-none' : '' ?>">
                      −
                    </button>
                    <span id="val-<?= $m['id'] ?>-2-a" class="font-mono text-2xl font-black text-white min-w-[36px] text-center">
                      <?= $set2AVal ?>
                    </span>
                    <button type="button" <?= !$isSet2Editable ? 'disabled="disabled"' : '' ?> onclick="updatePoint(<?= $m['id'] ?>, 2, 'a', 1)" class="score-btn btn-score-<?= $m['id'] ?>-2 flex h-9 w-9 items-center justify-center rounded-xl bg-blue-600 text-base font-bold text-white hover:bg-blue-500 active:scale-95 transition shadow-md shadow-blue-600/30 <?= !$isSet2Editable ? 'opacity-30 cursor-not-allowed pointer-events-none' : '' ?>">
                      +
                    </button>
                  </div>
                </div>

                <!-- Tim 2 Set 2 (Badminton / Pingpong) -->
                <div class="flex items-center justify-between gap-2">
                  <span class="text-xs font-bold text-slate-300 truncate max-w-[100px] sm:max-w-[120px]" title="<?= e($m['team2_name'] ?? 'Tim 2') ?>">
                    <?= e($m['team2_name'] ?? 'Tim 2') ?>
                  </span>
                  <div class="flex items-center gap-2">
                    <button type="button" <?= !$isSet2Editable ? 'disabled="disabled"' : '' ?> onclick="updatePoint(<?= $m['id'] ?>, 2, 'b', -1)" class="score-btn btn-score-<?= $m['id'] ?>-2 flex h-9 w-9 items-center justify-center rounded-xl border border-slate-700 bg-slate-800 text-base font-bold text-slate-200 hover:bg-slate-700 active:scale-95 transition <?= !$isSet2Editable ? 'opacity-30 cursor-not-allowed pointer-events-none' : '' ?>">
                      −
                    </button>
                    <span id="val-<?= $m['id'] ?>-2-b" class="font-mono text-2xl font-black text-white min-w-[36px] text-center">
                      <?= $set2BVal ?>
                    </span>
                    <button type="button" <?= !$isSet2Editable ? 'disabled="disabled"' : '' ?> onclick="updatePoint(<?= $m['id'] ?>, 2, 'b', 1)" class="score-btn btn-score-<?= $m['id'] ?>-2 flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-600 text-base font-bold text-white hover:bg-indigo-500 active:scale-95 transition shadow-md shadow-indigo-600/30 <?= !$isSet2Editable ? 'opacity-30 cursor-not-allowed pointer-events-none' : '' ?>">
                      +
                    </button>
                  </div>
                </div>
              <?php endif; ?>
            </div>

            <!-- Footer Box 2: Tombol Selesai / Koreksi -->
            <div id="footer-set-<?= $m['id'] ?>-2" class="mt-4 pt-3 border-t border-slate-800/80 flex items-center justify-between gap-2">
              <?php if ($currentSet === 2 && !$isSetFinished): ?>
                <span class="text-[11px] text-slate-400">Poin sedang dicatat</span>
                <button type="button"
                        onclick="finishSet(<?= $m['id'] ?>, 2)"
                        class="flex items-center gap-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-3 py-1.5 text-xs shadow-md shadow-emerald-600/30 active:scale-95 transition">
                  ✓ Selesai <?= $term ?> 2
                </button>
              <?php elseif ($currentSet === 2 && $isSetFinished): ?>
                <span class="text-[11px] text-cyan-300 font-bold">✓ <?= $term ?> 2 Telah Selesai</span>
                <button type="button"
                        onclick="reopenSet(<?= $m['id'] ?>)"
                        class="text-xs text-slate-400 hover:text-white underline">
                  ↺ Koreksi Skor
                </button>
              <?php elseif ($currentSet > 2): ?>
                <span class="text-[11px] text-slate-400 font-medium">✓ <?= $term ?> 2 Selesai</span>
                <span class="text-[10px] text-slate-500">Terkunci</span>
              <?php else: ?>
                <span class="text-[11px] text-slate-500 font-medium">Belum Dimulai</span>
                <span class="text-[10px] text-slate-600">Terkunci</span>
              <?php endif; ?>
            </div>
          </div>

          <!-- SET 3 (Hanya untuk Badminton/Pingpong Rubber Set) -->
          <?php if (!$isFutsal && $isRubber): ?>
            <?php
              $isSet3Active = ($currentSet === 3);
              $isSet3Editable = ($isSet3Active && !$isSetFinished);
            ?>
            <div id="box-set-<?= $m['id'] ?>-3" class="rounded-2xl p-4 transition-all duration-300 flex flex-col justify-between <?= $isSet3Active ? ($isSetFinished ? 'border-2 border-cyan-500/80 bg-slate-900/90 shadow-xl' : 'border-2 border-emerald-500/90 bg-slate-900/90 shadow-xl shadow-emerald-500/10 ring-2 ring-emerald-500/20') : 'border border-slate-800 bg-slate-950/40 opacity-60' ?>">
              <div>
                <div class="mb-3 text-center text-xs font-black uppercase tracking-wider text-amber-400 border-b border-slate-800/80 pb-2 flex items-center justify-between">
                  <span>SET 3 (RUBBER)</span>
                  <span id="badge-set-<?= $m['id'] ?>-3">
                    <?php if ($isSet3Active && !$isSetFinished): ?>
                      <span class="rounded-full bg-emerald-950/90 border border-emerald-500/50 px-2.5 py-0.5 text-[10px] font-black text-emerald-300 flex items-center gap-1">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-ping"></span> AKTIF DIMAINKAN
                      </span>
                    <?php elseif ($currentSet === 3 && $isSetFinished): ?>
                      <span class="rounded-full bg-cyan-950/80 border border-cyan-500/50 px-2.5 py-0.5 text-[10px] font-bold text-cyan-300">
                        ✓ SELESAI (TERKUNCI)
                      </span>
                    <?php else: ?>
                      <span class="rounded-full bg-slate-900 border border-slate-800 px-2.5 py-0.5 text-[10px] font-bold text-slate-500">
                        🔒 TERKUNCI
                      </span>
                    <?php endif; ?>
                  </span>
                </div>

                <!-- Tim 1 Set 3 -->
                <div class="flex items-center justify-between gap-2 mb-3">
                  <span class="text-xs font-bold text-slate-300 truncate max-w-[100px] sm:max-w-[120px]" title="<?= e($m['team1_name'] ?? 'Tim 1') ?>">
                    <?= e($m['team1_name'] ?? 'Tim 1') ?>
                  </span>
                  <div class="flex items-center gap-2">
                    <button type="button" <?= !$isSet3Editable ? 'disabled="disabled"' : '' ?> onclick="updatePoint(<?= $m['id'] ?>, 3, 'a', -1)" class="score-btn btn-score-<?= $m['id'] ?>-3 flex h-9 w-9 items-center justify-center rounded-xl border border-slate-700 bg-slate-800 text-base font-bold text-slate-200 hover:bg-slate-700 active:scale-95 transition <?= !$isSet3Editable ? 'opacity-30 cursor-not-allowed pointer-events-none' : '' ?>">
                      −
                    </button>
                    <span id="val-<?= $m['id'] ?>-3-a" class="font-mono text-2xl font-black text-white min-w-[36px] text-center">
                      <?= (int)$m['set3_a'] ?>
                    </span>
                    <button type="button" <?= !$isSet3Editable ? 'disabled="disabled"' : '' ?> onclick="updatePoint(<?= $m['id'] ?>, 3, 'a', 1)" class="score-btn btn-score-<?= $m['id'] ?>-3 flex h-9 w-9 items-center justify-center rounded-xl bg-blue-600 text-base font-bold text-white hover:bg-blue-500 active:scale-95 transition shadow-md shadow-blue-600/30 <?= !$isSet3Editable ? 'opacity-30 cursor-not-allowed pointer-events-none' : '' ?>">
                      +
                    </button>
                  </div>
                </div>

                <!-- Tim 2 Set 3 -->
                <div class="flex items-center justify-between gap-2">
                  <span class="text-xs font-bold text-slate-300 truncate max-w-[100px] sm:max-w-[120px]" title="<?= e($m['team2_name'] ?? 'Tim 2') ?>">
                    <?= e($m['team2_name'] ?? 'Tim 2') ?>
                  </span>
                  <div class="flex items-center gap-2">
                    <button type="button" <?= !$isSet3Editable ? 'disabled="disabled"' : '' ?> onclick="updatePoint(<?= $m['id'] ?>, 3, 'b', -1)" class="score-btn btn-score-<?= $m['id'] ?>-3 flex h-9 w-9 items-center justify-center rounded-xl border border-slate-700 bg-slate-800 text-base font-bold text-slate-200 hover:bg-slate-700 active:scale-95 transition <?= !$isSet3Editable ? 'opacity-30 cursor-not-allowed pointer-events-none' : '' ?>">
                      −
                    </button>
                    <span id="val-<?= $m['id'] ?>-3-b" class="font-mono text-2xl font-black text-white min-w-[36px] text-center">
                      <?= (int)$m['set3_b'] ?>
                    </span>
                    <button type="button" <?= !$isSet3Editable ? 'disabled="disabled"' : '' ?> onclick="updatePoint(<?= $m['id'] ?>, 3, 'b', 1)" class="score-btn btn-score-<?= $m['id'] ?>-3 flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-600 text-base font-bold text-white hover:bg-indigo-500 active:scale-95 transition shadow-md shadow-indigo-600/30 <?= !$isSet3Editable ? 'opacity-30 cursor-not-allowed pointer-events-none' : '' ?>">
                      +
                    </button>
                  </div>
                </div>
              </div>

              <!-- Footer Box Set 3: Tombol Selesai Set 3 / Koreksi -->
              <div id="footer-set-<?= $m['id'] ?>-3" class="mt-4 pt-3 border-t border-slate-800/80 flex items-center justify-between gap-2">
                <?php if ($currentSet === 3 && !$isSetFinished): ?>
                  <span class="text-[11px] text-slate-400">Poin sedang dicatat</span>
                  <button type="button"
                          onclick="finishSet(<?= $m['id'] ?>, 3)"
                          class="flex items-center gap-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-3 py-1.5 text-xs shadow-md shadow-emerald-600/30 active:scale-95 transition">
                    ✓ Selesai Set 3
                  </button>
                <?php elseif ($currentSet === 3 && $isSetFinished): ?>
                  <span class="text-[11px] text-cyan-300 font-bold">✓ Set 3 Telah Selesai</span>
                  <button type="button"
                          onclick="reopenSet(<?= $m['id'] ?>)"
                          class="text-xs text-slate-400 hover:text-white underline">
                    ↺ Koreksi Skor
                  </button>
                <?php else: ?>
                  <span class="text-[11px] text-slate-500 font-medium">Belum Dimulai</span>
                  <span class="text-[10px] text-slate-600">Terkunci</span>
                <?php endif; ?>
              </div>
            </div>
          <?php endif; ?>

        </div>
        <?php endif; ?>

        <?php if ($isFutsal): ?>
          <!-- ======================================================== -->
          <!-- SEKSI MATCH HIGHLIGHTS & CATATAN WASIT (FUTSAL)          -->
          <!-- ======================================================== -->
          <div class="mt-6 rounded-2xl border border-slate-800 bg-slate-950/90 p-4 sm:p-5 shadow-inner">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-800/80 pb-3">
              <div class="flex items-center gap-2">
                <span class="text-base sm:text-lg">⚡</span>
                <h4 class="text-xs sm:text-sm font-black text-white uppercase tracking-wider">
                  Match Highlights &amp; Catatan Wasit
                </h4>
              </div>
              <!-- Tombol Aksi Cepat Highlight -->
              <div class="flex flex-wrap items-center gap-2">
                <button type="button"
                        onclick="openHighlightForm(<?= $m['id'] ?>, 'goal')"
                        class="flex items-center gap-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-3 py-1.5 text-xs shadow-md shadow-emerald-600/30 active:scale-95 transition">
                  ⚽ + Gol
                </button>
                <button type="button"
                        onclick="openHighlightForm(<?= $m['id'] ?>, 'yellow_card')"
                        class="flex items-center gap-1.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-black font-bold px-3 py-1.5 text-xs shadow-md shadow-amber-500/30 active:scale-95 transition">
                  🟨 + Kartu Kuning
                </button>
                <button type="button"
                        onclick="openHighlightForm(<?= $m['id'] ?>, 'double_yellow_card')"
                        class="flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-amber-500 to-rose-600 hover:from-amber-400 hover:to-rose-500 text-white font-bold px-3 py-1.5 text-xs shadow-md shadow-amber-600/30 active:scale-95 transition"
                        title="Kartu Kuning Kedua">
                  🟨🟥 + 2x Kuning
                </button>
                <button type="button"
                        onclick="openHighlightForm(<?= $m['id'] ?>, 'red_card')"
                        class="flex items-center gap-1.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold px-3 py-1.5 text-xs shadow-md shadow-rose-600/30 active:scale-95 transition">
                  🟥 + Kartu Merah
                </button>
                <button type="button"
                        onclick="openHighlightForm(<?= $m['id'] ?>, 'substitution')"
                        class="flex items-center gap-1.5 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold px-3 py-1.5 text-xs shadow-md shadow-sky-600/30 active:scale-95 transition">
                  🔄 + Pertukaran Pemain
                </button>
              </div>
            </div>

            <!-- Form Tambah Highlight (Collapsible) -->
            <div id="hl-form-container-<?= $m['id'] ?>" class="hidden mt-4 rounded-xl border border-blue-500/40 bg-blue-950/20 p-4">
              <div class="flex items-center justify-between mb-3 border-b border-blue-500/20 pb-2">
                <span class="text-xs font-bold text-blue-300 flex items-center gap-2">
                  <span id="hl-form-icon-<?= $m['id'] ?>">⚽</span>
                  <span id="hl-form-title-<?= $m['id'] ?>">Catat Kejadian Pertandingan</span>
                  <span class="rounded-full bg-blue-500/20 border border-blue-400/30 px-2.5 py-0.5 text-[10px] font-bold text-blue-200">
                    ⏱️ Menit ke-<b id="hl-timer-auto-hint-<?= $m['id'] ?>">1'</b> (Otomatis dari Stopwatch)
                  </span>
                </span>
                <button type="button" onclick="closeHighlightForm(<?= $m['id'] ?>)" class="text-xs text-slate-400 hover:text-white transition">
                  ✕ Batal
                </button>
              </div>

              <!-- Input Hidden untuk Menit Kejadian yang Otomatis dari Timer -->
              <input type="hidden" id="hl-minute-<?= $m['id'] ?>" value="1">

              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs items-end">
                <div>
                  <label class="block text-[11px] font-bold text-slate-400 mb-1">Pilih Tim</label>
                  <select id="hl-team-<?= $m['id'] ?>" onchange="updatePlayerDropdown(<?= $m['id'] ?>)" class="w-full rounded-xl border border-slate-700 bg-slate-800 px-3 py-2 font-bold text-white focus:outline-none focus:border-blue-500">
                    <option value="<?= (int)$m['team1_id'] ?>"><?= e($m['team1_name'] ?? 'Tim 1') ?></option>
                    <option value="<?= (int)$m['team2_id'] ?>"><?= e($m['team2_name'] ?? 'Tim 2') ?></option>
                  </select>
                </div>
                <div>
                  <label class="block text-[11px] font-bold text-slate-400 mb-1">Jenis Kejadian</label>
                  <select id="hl-event-<?= $m['id'] ?>" onchange="onEventTypeChange(<?= $m['id'] ?>)" class="w-full rounded-xl border border-slate-700 bg-slate-800 px-3 py-2 font-bold text-white focus:outline-none focus:border-blue-500">
                    <option value="goal">⚽ Gol</option>
                    <option value="yellow_card">🟨 Kartu Kuning</option>
                    <option value="double_yellow_card">🟨🟥 Kartu Kuning Kedua (2x Kuning)</option>
                    <option value="red_card">🟥 Kartu Merah</option>
                    <option value="substitution">🔄 Pertukaran Pemain</option>
                  </select>
                </div>

                <!-- Input Pemain Tunggal (Gol / Kartu) -->
                <div id="hl-single-player-container-<?= $m['id'] ?>">
                  <label class="block text-[11px] font-bold text-slate-400 mb-1">Nama Pemain</label>
                  <select id="hl-player-select-<?= $m['id'] ?>" onchange="onPlayerSelectChange(<?= $m['id'] ?>)" class="w-full rounded-xl border border-slate-700 bg-slate-800 px-3 py-2 font-semibold text-white focus:outline-none focus:border-blue-500">
                    <!-- Dinamis via JS -->
                  </select>
                  <input type="text" id="hl-player-manual-<?= $m['id'] ?>" placeholder="Ketik nama pemain..." class="hidden mt-1.5 w-full rounded-xl border border-slate-700 bg-slate-800 px-3 py-1.5 text-xs text-white focus:outline-none focus:border-blue-500">
                </div>

                <!-- Input Pertukaran Pemain (Keluar & Masuk) -->
                <div id="hl-sub-container-<?= $m['id'] ?>" class="hidden sm:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-2">
                  <div>
                    <label class="block text-[11px] font-bold text-rose-300 mb-1">Pemain Keluar (Out)</label>
                    <select id="hl-player-out-select-<?= $m['id'] ?>" onchange="onSubPlayerSelectChange(<?= $m['id'] ?>, 'out')" class="w-full rounded-xl border border-slate-700 bg-slate-800 px-3 py-2 font-semibold text-white focus:outline-none focus:border-rose-500">
                      <!-- Dinamis via JS -->
                    </select>
                    <input type="text" id="hl-player-out-manual-<?= $m['id'] ?>" placeholder="Nama pemain keluar..." class="hidden mt-1.5 w-full rounded-xl border border-slate-700 bg-slate-800 px-3 py-1.5 text-xs text-white focus:outline-none focus:border-rose-500">
                  </div>
                  <div>
                    <label class="block text-[11px] font-bold text-emerald-300 mb-1">Pemain Masuk (In)</label>
                    <select id="hl-player-in-select-<?= $m['id'] ?>" onchange="onSubPlayerSelectChange(<?= $m['id'] ?>, 'in')" class="w-full rounded-xl border border-slate-700 bg-slate-800 px-3 py-2 font-semibold text-white focus:outline-none focus:border-emerald-500">
                      <!-- Dinamis via JS -->
                    </select>
                    <input type="text" id="hl-player-in-manual-<?= $m['id'] ?>" placeholder="Nama pemain masuk..." class="hidden mt-1.5 w-full rounded-xl border border-slate-700 bg-slate-800 px-3 py-1.5 text-xs text-white focus:outline-none focus:border-emerald-500">
                  </div>
                </div>

                <!-- Tombol Submit Simpan Highlight -->
                <div id="hl-submit-wrapper-<?= $m['id'] ?>">
                  <button type="button"
                          id="btn-hl-submit-<?= $m['id'] ?>"
                          onclick="submitHighlight(<?= $m['id'] ?>)"
                          class="w-full rounded-xl bg-blue-600 hover:bg-blue-500 py-2.5 px-4 text-xs font-bold text-white shadow-md shadow-blue-600/30 active:scale-95 transition flex items-center justify-center gap-1.5">
                    <span>✓</span>
                    <span>Simpan Kejadian</span>
                  </button>
                </div>
              </div>
            </div>

            <!-- Timeline Daftar Highlights -->
            <div class="mt-4">
              <div id="highlight-list-<?= $m['id'] ?>" class="space-y-2">
                <!-- Di-render oleh JavaScript -->
              </div>
            </div>
          </div>
        <?php endif; ?>

        <!-- Tombol Aksi Pertandingan -->
        <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-slate-800 pt-4">
          <div class="flex items-center gap-2 text-xs">
            <span class="text-slate-400">Ringkasan:</span>
            <span id="preview-<?= $m['id'] ?>" class="font-bold text-amber-400">
              <?php if ($isFutsal): ?>
                Babak 1 (<?= (int)$m['set1_a'] ?>-<?= (int)$m['set1_b'] ?>) ➔ Babak 2 (<?= (int)$m['set2_a'] ?>-<?= (int)$m['set2_b'] ?>) · Total Gol: <?= (int)$m['score1'] ?>-<?= (int)$m['score2'] ?>
              <?php elseif ($isRubber): ?>
                Set 1 (<?= (int)$m['set1_a'] ?>-<?= (int)$m['set1_b'] ?>), Set 2 (<?= (int)$m['set2_a'] ?>-<?= (int)$m['set2_b'] ?>), Set 3 (<?= (int)$m['set3_a'] ?>-<?= (int)$m['set3_b'] ?>)
              <?php else: ?>
                Set 1 (Tengah: <?= (int)$m['set1_a'] ?>-<?= (int)$m['set1_b'] ?>) ➔ Set 2 (Akumulasi Akhir: <?= $set2AVal ?>-<?= $set2BVal ?>)
              <?php endif; ?>
            </span>
          </div>

          <div class="flex items-center gap-2">
            <!-- Form Batalkan Status Live -->
            <form method="post" action="<?= url("admin/live-panel/{$m['id']}/reset-status") ?>" onsubmit="return confirm('Kembalikan status pertandingan ini ke Belum Mulai?')">
              <?= csrf_field() ?>
              <button type="submit" class="btn small ghost text-xs text-slate-400 hover:text-white">
                Batalkan Live
              </button>
            </form>

            <!-- Form Selesaikan Pertandingan -->
            <form method="post" action="<?= url("admin/live-panel/{$m['id']}/finish") ?>" onsubmit="return confirm('Pastikan skor seluruh set sudah benar. Selesaikan pertandingan ini sekarang?')">
              <?= csrf_field() ?>
              <button type="submit" id="btn-finish-match-<?= $m['id'] ?>" class="btn small !bg-emerald-600 hover:!bg-emerald-500 text-white font-bold shadow-lg shadow-emerald-600/30">
                ✓ Selesai Pertandingan
              </button>
            </form>
          </div>
        </div>

      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<!-- ================= MODAL ATUR WASIT (LIVE PANEL) ================= -->
<div id="liveRefereeModal" class="fixed inset-0 z-[100010] flex items-center justify-center bg-black/80 backdrop-blur-sm p-4 hidden overflow-y-auto">
  <div class="relative w-full max-w-md rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-2xl transition-all my-8 max-h-[90vh] overflow-y-auto">
    <div class="flex items-center justify-between border-b border-slate-800 pb-3 mb-4">
      <div>
        <h3 class="text-base font-black text-white flex items-center gap-2">
          <span>👔</span>
          <span>Atur Perangkat Wasit</span>
        </h3>
        <p id="liveRefModalSubtitle" class="text-xs text-amber-400 font-semibold mt-0.5">Pertandingan #...</p>
      </div>
      <button type="button" onclick="closeLiveRefereeModal()" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-800 hover:text-white transition">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>

    <form id="liveRefereeModalForm" onsubmit="handleLiveRefereeSubmit(event)">
      <input type="hidden" id="liveRefMatchId" value="">
      <input type="hidden" id="liveRefSport" value="">

      <div class="space-y-4 text-xs">
        <div>
          <label class="mb-1 block font-bold uppercase tracking-wider text-slate-300 flex items-center justify-between">
            <span>👨‍⚖️ Wasit Utama (1 Orang)</span>
            <span class="text-[10px] text-amber-400 font-normal">Pemimpin Pertandingan</span>
          </label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-500">👨‍⚖️</span>
            <input type="text" name="referee_main" id="liveModalRefMain" list="liveRefereesDatalist" placeholder="Nama Wasit Utama..." maxlength="100" class="w-full rounded-xl border border-slate-700 bg-slate-950 pl-9 pr-3 py-2 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
          </div>
        </div>

        <div>
          <label class="mb-1 block font-bold uppercase tracking-wider text-slate-300 flex items-center justify-between">
            <span>🚩 Wasit Garis 1</span>
            <span class="text-[10px] text-blue-400 font-normal">Line Referee 1</span>
          </label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-500">🚩</span>
            <input type="text" name="referee_line1" id="liveModalRefLine1" list="liveRefereesDatalist" placeholder="Nama Wasit Garis 1..." maxlength="100" class="w-full rounded-xl border border-slate-700 bg-slate-950 pl-9 pr-3 py-2 text-sm text-slate-100 focus:border-blue-500 focus:outline-none">
          </div>
        </div>

        <div>
          <label class="mb-1 block font-bold uppercase tracking-wider text-slate-300 flex items-center justify-between">
            <span>🚩 Wasit Garis 2</span>
            <span class="text-[10px] text-blue-400 font-normal">Line Referee 2</span>
          </label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-500">🚩</span>
            <input type="text" name="referee_line2" id="liveModalRefLine2" list="liveRefereesDatalist" placeholder="Nama Wasit Garis 2..." maxlength="100" class="w-full rounded-xl border border-slate-700 bg-slate-950 pl-9 pr-3 py-2 text-sm text-slate-100 focus:border-blue-500 focus:outline-none">
          </div>
        </div>
      </div>

      <div class="mt-6 flex items-center justify-end gap-2.5 pt-3 border-t border-slate-800">
        <button type="button" onclick="closeLiveRefereeModal()" class="btn ghost small !py-2 !px-4">Batal</button>
        <button type="submit" id="liveRefSubmitBtn" class="btn small !py-2 !px-4 !bg-amber-600 hover:!bg-amber-500 font-bold text-white shadow-md shadow-amber-600/30">Simpan Perangkat Wasit</button>
      </div>
    </form>
  </div>
</div>

<datalist id="liveRefereesDatalist">
  <?php foreach (($refereesList ?? []) as $rName): ?>
    <option value="<?= e($rName) ?>"></option>
  <?php endforeach; ?>
</datalist>

<!-- ========================================== -->
<!-- JAVASCRIPT FETCH API UNTUK LIVE PANEL      -->
<!-- ========================================== -->
<script>
/**
 * Memilih dan mengaktifkan set yang sedang dimainkan (Mulai Set 1, 2, atau 3).
 */
async function startSet(matchId, setNum) {
  const btn = document.getElementById(`btn-set-${matchId}-${setNum}`);
  if (btn) btn.disabled = true;

  try {
    const formData = new URLSearchParams();
    formData.append('set', setNum);
    formData.append('_token', document.querySelector('meta[name="csrf"]').content);

    const res = await fetch('<?= url('admin/live-panel') ?>/' + matchId + '/start-set', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-CSRF-Token': document.querySelector('meta[name="csrf"]').content,
      },
      body: formData.toString()
    });

    const data = await res.json();
    if (!data.ok) {
      alert(data.error || 'Gagal mengubah set aktif.');
      return;
    }

    // Refresh status alur pertandingan di DOM
    applyMatchState(matchId, {
      currentSet: data.current_set,
      isSetFinished: false,
      scoringSystem: data.scoring_system,
      set1_a: data.set1_a,
      set1_b: data.set1_b,
      set2_a: data.set2_a,
      set2_b: data.set2_b,
      set3_a: data.set3_a,
      set3_b: data.set3_b,
      score1: data.score1,
      score2: data.score2,
      winnerPreview: data.winner_preview
    });

    const card = document.getElementById(`match-card-${matchId}`);
    if (card) {
      card.dataset.currentSet = data.current_set;
      card.dataset.isSetFinished = '0';
    }

    // Khusus cabor Futsal: Mulai Babak otomatis menjalankan stopwatch!
    const isFutsal = (card && card.dataset.sportType === 'futsal');
    if (isFutsal) {
      if (futsalTimers[matchId]) {
        futsalTimers[matchId].half = parseInt(data.current_set, 10) || setNum;
        if (typeof data.futsal_half_duration === 'number') {
          futsalTimers[matchId].halfDuration = data.futsal_half_duration;
        }
        if (typeof data.futsal_elapsed_seconds === 'number') {
          futsalTimers[matchId].elapsed = data.futsal_elapsed_seconds;
        } else if (setNum === 2) {
          futsalTimers[matchId].elapsed = futsalTimers[matchId].halfDuration * 60;
        }
      }
      startTimerInterval(matchId);
      showRefereeToast(`▶ Babak ${setNum} dimulai! Stopwatch berjalan otomatis.`);
    }

  } catch (err) {
    console.error(err);
    alert('Terjadi kesalahan koneksi saat mengaktifkan set.');
  } finally {
    if (btn) btn.disabled = false;
  }
}

/**
 * Menandai set/babak aktif telah selesai.
 * Mengunci skor dan MEMBUKA tombol untuk memilih babak/set berikutnya!
 */
async function finishSet(matchId, setNum) {
  const card = document.getElementById(`match-card-${matchId}`);
  const isFutsal = (card && card.dataset.sportType === 'futsal');
  const term = isFutsal ? 'Babak' : 'Set';

  if (!confirm(`Selesaikan ${term} ${setNum} sekarang? Anda dapat memilih ${term.toLowerCase()} berikutnya setelah ini.`)) {
    return;
  }

  try {
    const formData = new URLSearchParams();
    formData.append('set', setNum);
    formData.append('_token', document.querySelector('meta[name="csrf"]').content);

    const res = await fetch('<?= url('admin/live-panel') ?>/' + matchId + '/finish-set', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-CSRF-Token': document.querySelector('meta[name="csrf"]').content,
      },
      body: formData.toString()
    });

    const data = await res.json();
    if (!data.ok) {
      alert(data.error || `Gagal menyelesaikan ${term.toLowerCase()}.`);
      return;
    }

    // Update state di DOM
    applyMatchState(matchId, {
      currentSet: data.current_set,
      isSetFinished: true,
      scoringSystem: data.scoring_system,
      nextSet: data.next_set,
      canFinishMatch: data.can_finish_match,
      set1_a: data.set1_a,
      set1_b: data.set1_b,
      set2_a: data.set2_a,
      set2_b: data.set2_b,
      set3_a: data.set3_a,
      set3_b: data.set3_b,
      score1: data.score1,
      score2: data.score2,
      winnerPreview: data.winner_preview
    });

    if (card) {
      card.dataset.currentSet = data.current_set;
      card.dataset.isSetFinished = '1';
    }

    // Khusus Futsal: jeda timer saat babak berakhir
    if (isFutsal) {
      pauseTimer(matchId);
      if (setNum === 1) {
        // Otomatis ubah waktu menjadi waktu akhir Babak 1 yang telah ditentukan admin
        const t = futsalTimers[matchId];
        if (t) {
          const half1End = t.halfDuration * 60;
          t.elapsed = half1End;
          t.half = 1;
          updateTimerDisplay(matchId);
          await syncTimerToBackend(matchId);
        }
        showRefereeToast('⏸️ Babak 1 Selesai! Waktu babak pertama otomatis disesuaikan ke batas waktu akhir. Pertandingan kini HALF TIME.');
      } else {
        // Otomatis ubah waktu menjadi waktu maksimal Babak 2 yang telah ditentukan admin
        const t = futsalTimers[matchId];
        if (t) {
          const maxTime = t.halfDuration * 120;
          t.elapsed = maxTime;
          t.half = 2;
          updateTimerDisplay(matchId);
          await syncTimerToBackend(matchId);
        }
        showRefereeToast('✓ Babak 2 Selesai! Waktu babak kedua otomatis disesuaikan ke batas maksimal. Pertandingan siap diselesaikan.');
      }
    }

    if (data.highlights) {
      renderHighlights(matchId, data.highlights);
      if (card) card.dataset.highlights = JSON.stringify(data.highlights);
    }

  } catch (err) {
    console.error(err);
    alert(`Terjadi kesalahan koneksi saat menyelesaikan ${term.toLowerCase()}.`);
  }
}

/**
 * Membuka kembali set/babak aktif untuk mengoreksi skor.
 */
async function reopenSet(matchId) {
  try {
    const formData = new URLSearchParams();
    formData.append('_token', document.querySelector('meta[name="csrf"]').content);

    const res = await fetch('<?= url('admin/live-panel') ?>/' + matchId + '/reopen-set', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-CSRF-Token': document.querySelector('meta[name="csrf"]').content,
      },
      body: formData.toString()
    });

    const data = await res.json();
    if (!data.ok) {
      alert(data.error || 'Gagal membuka kembali set/babak.');
      return;
    }

    // Ubah status isSetFinished menjadi false pada DOM
    const curr = data.current_set;
    applyMatchState(matchId, {
      currentSet: curr,
      isSetFinished: false
    });

    const card = document.getElementById(`match-card-${matchId}`);
    if (card) {
      card.dataset.currentSet = curr;
      card.dataset.isSetFinished = '0';
    }

    const isFutsal = (card && card.dataset.sportType === 'futsal');
    if (isFutsal) {
      if (futsalTimers[matchId]) {
        futsalTimers[matchId].half = parseInt(curr, 10) || 1;
      }
      startTimerInterval(matchId);
      showRefereeToast(`↺ Babak ${curr} dibuka kembali untuk koreksi. Timer dilanjutkan.`);
    }

    if (data.highlights) {
      renderHighlights(matchId, data.highlights);
      if (card) card.dataset.highlights = JSON.stringify(data.highlights);
    }

  } catch (err) {
    console.error(err);
    alert('Terjadi kesalahan koneksi saat membuka kembali set/babak.');
  }
}

/**
 * Sinkronisasi visual status match ke DOM
 */
function applyMatchState(matchId, state) {
  const currentSet = state.currentSet;
  const isFinished = state.isSetFinished;

  const card = document.getElementById(`match-card-${matchId}`);
  const isFutsal = (card && card.dataset.sportType === 'futsal');
  const term = isFutsal ? 'Babak' : 'Set';
  const termUpper = term.toUpperCase();

  // 1. Update Top Active Set Badge
  const badgeActive = document.getElementById(`badge-active-set-${matchId}`);
  if (badgeActive) {
    if (currentSet > 0 && !isFinished) {
      badgeActive.innerHTML = `
        <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-500/40 bg-emerald-500/20 px-3 py-1 text-xs font-black text-emerald-400">
          <span class="h-2 w-2 rounded-full bg-emerald-400 animate-ping"></span>
          ${termUpper} ${currentSet}${(!isFutsal && currentSet === 3) ? ' (RUBBER)' : ''} SEDANG AKTIF
        </span>
      `;
    } else if (currentSet > 0 && isFinished) {
      if (isFutsal && currentSet === 1) {
        badgeActive.innerHTML = `
          <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-500/40 bg-amber-500/20 px-3 py-1 text-xs font-black text-amber-300">
            ⏸️ HALF TIME (ISTIRAHAT BABAK 1)
          </span>
        `;
      } else {
        badgeActive.innerHTML = `
          <span class="inline-flex items-center gap-1.5 rounded-full border border-cyan-500/40 bg-cyan-500/20 px-3 py-1 text-xs font-black text-cyan-300">
            ✓ ${termUpper} ${currentSet} TELAH SELESAI
          </span>
        `;
      }
    }
  }

  // 2. Update Top Finish / Reopen Button Wrapper
  const wrapperTop = document.getElementById(`wrapper-btn-finish-top-${matchId}`);
  if (wrapperTop) {
    if (currentSet > 0 && !isFinished) {
      wrapperTop.innerHTML = `
        <button type="button"
                onclick="finishSet(${matchId}, ${currentSet})"
                id="btn-top-finish-${matchId}"
                class="flex items-center gap-1.5 rounded-xl ${isFutsal ? 'bg-cyan-600 hover:bg-cyan-500' : 'bg-emerald-600 hover:bg-emerald-500'} text-white font-black px-3.5 py-1.5 text-xs shadow-lg shadow-cyan-600/30 active:scale-95 transition">
          ✓ Selesai ${term} ${currentSet}
        </button>
      `;
    } else if (currentSet > 0 && isFinished) {
      wrapperTop.innerHTML = `
        <button type="button"
                onclick="reopenSet(${matchId})"
                id="btn-top-reopen-${matchId}"
                class="flex items-center gap-1.5 rounded-xl border border-slate-700 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold px-3.5 py-1.5 text-xs active:scale-95 transition">
          ↺ Koreksi ${term} ${currentSet}
        </button>
      `;
    } else {
      wrapperTop.innerHTML = '';
    }
  }

  // 3. Update Tombol Flow Set / Babak
  const b1 = document.getElementById(`btn-set-${matchId}-1`);
  const b2 = document.getElementById(`btn-set-${matchId}-2`);
  const b3 = document.getElementById(`btn-set-${matchId}-3`);

  // Logika kelayakan tombol:
  const canStartSet2 = (currentSet === 1 && isFinished) || currentSet === 2;
  const canStartSet3 = !isFutsal && (state.nextSet === 3 || currentSet === 3 || (currentSet === 2 && isFinished && state.scoringSystem === 'rubber_set'));

  if (b1) {
    if (currentSet === 1 && !isFinished) {
      b1.disabled = false;
      b1.className = 'btn-flow-set flex items-center gap-1.5 rounded-xl px-3.5 py-1.5 text-xs transition active:scale-95 bg-emerald-600 border border-emerald-500 text-white font-black shadow-lg shadow-emerald-600/30 ring-2 ring-emerald-400/40';
    } else if (currentSet >= 1 && (isFinished || currentSet > 1)) {
      b1.disabled = true;
      b1.className = 'btn-flow-set flex items-center gap-1.5 rounded-xl px-3.5 py-1.5 text-xs transition bg-slate-800/90 border border-slate-700 text-slate-400 font-bold opacity-60 cursor-not-allowed';
    } else {
      b1.disabled = false;
      b1.className = 'btn-flow-set flex items-center gap-1.5 rounded-xl px-3.5 py-1.5 text-xs transition active:scale-95 bg-emerald-700 hover:bg-emerald-600 border border-emerald-600 text-white font-bold shadow-md';
    }
  }

  if (b2) {
    if (currentSet === 2 && !isFinished) {
      b2.disabled = false;
      b2.className = 'btn-flow-set flex items-center gap-1.5 rounded-xl px-3.5 py-1.5 text-xs transition active:scale-95 bg-emerald-600 border border-emerald-500 text-white font-black shadow-lg shadow-emerald-600/30 ring-2 ring-emerald-400/40';
    } else if (currentSet === 2 && isFinished) {
      b2.disabled = true;
      b2.className = 'btn-flow-set flex items-center gap-1.5 rounded-xl px-3.5 py-1.5 text-xs transition bg-slate-800/90 border border-slate-700 text-slate-400 font-bold opacity-60 cursor-not-allowed';
    } else if (canStartSet2) {
      b2.disabled = false;
      b2.className = 'btn-flow-set flex items-center gap-1.5 rounded-xl px-3.5 py-1.5 text-xs transition active:scale-95 bg-blue-600 border border-blue-500 text-white font-black animate-pulse shadow-lg shadow-blue-600/40 ring-2 ring-blue-400/50';
    } else {
      b2.disabled = true;
      b2.className = 'btn-flow-set flex items-center gap-1.5 rounded-xl px-3.5 py-1.5 text-xs transition active:scale-95 bg-slate-800/90 border border-slate-700 text-slate-300 font-bold opacity-30 cursor-not-allowed pointer-events-none';
    }
  }

  if (b3) {
    if (currentSet === 3) {
      b3.disabled = false;
      b3.className = 'btn-flow-set flex items-center gap-1.5 rounded-xl px-3.5 py-1.5 text-xs transition active:scale-95 bg-emerald-600 border border-emerald-500 text-white font-black shadow-lg shadow-emerald-600/30 ring-2 ring-emerald-400/40';
    } else if (canStartSet3) {
      b3.disabled = false;
      b3.className = 'btn-flow-set flex items-center gap-1.5 rounded-xl px-3.5 py-1.5 text-xs transition active:scale-95 bg-amber-600 border border-amber-500 text-white font-black animate-pulse shadow-lg shadow-amber-600/40 ring-2 ring-amber-400/50';
    } else {
      b3.disabled = true;
      b3.className = 'btn-flow-set flex items-center gap-1.5 rounded-xl px-3.5 py-1.5 text-xs transition active:scale-95 bg-slate-800/90 border border-slate-700 text-slate-300 font-bold opacity-30 cursor-not-allowed pointer-events-none';
    }
  }

  // 4. Update Helper Text
  const helperMsg = document.getElementById(`helper-msg-${matchId}`);
  if (helperMsg) {
    if (isFutsal) {
      if (currentSet === 1 && !isFinished) {
        helperMsg.innerHTML = '<span class="text-emerald-400 font-semibold">Babak 1 Aktif:</span> Jalankan stopwatch dan catat gol/kartu. Tekan tombol <b>"✓ Selesai Babak 1"</b> saat turun minum.';
      } else if (currentSet === 1 && isFinished) {
        helperMsg.innerHTML = '<span class="text-cyan-300 font-semibold">Babak 1 Selesai:</span> Tombol <b>"▶ Mulai Babak 2"</b> kini terbuka! Klik tombol tersebut untuk melanjutkan ke Babak 2.';
      } else if (currentSet === 2 && !isFinished) {
        helperMsg.innerHTML = '<span class="text-emerald-400 font-semibold">Babak 2 Aktif:</span> Babak kedua sedang dimainkan. Tekan tombol <b>"✓ Selesai Babak 2"</b> setelah peluit panjang berbunyi.';
      } else if (currentSet === 2 && isFinished) {
        helperMsg.innerHTML = '<span class="text-emerald-400 font-semibold">Waktu Normal Selesai:</span> Seluruh babak telah berakhir! Silakan periksa skor dan tekan tombol <b>"✓ Selesai Pertandingan"</b> di bawah.';
      }
    } else {
      if (currentSet === 1 && !isFinished) {
        helperMsg.innerHTML = '<span class="text-emerald-400 font-semibold">Set 1 Aktif:</span> Tombol "Mulai Set 2" dikunci. Wasit wajib menekan tombol <b>"✓ Selesai Set 1"</b> setelah Set 1 berakhir agar tombol Set 2 terbuka.';
      } else if (currentSet === 1 && isFinished) {
        helperMsg.innerHTML = '<span class="text-cyan-300 font-semibold">Set 1 Selesai:</span> Tombol <b>"▶ Mulai Set 2"</b> kini terbuka! Silakan klik untuk melanjutkan ke Set 2.';
      } else if (currentSet === 2 && !isFinished) {
        helperMsg.innerHTML = '<span class="text-emerald-400 font-semibold">Set 2 Aktif:</span> Sedang dimainkan ' + (!state.scoringSystem.includes('rubber') ? '(akumulasi lanjutan)' : '') + '. Tekan tombol <b>"✓ Selesai Set 2"</b> jika set ini berakhir.';
      } else if (currentSet === 2 && isFinished) {
        if (state.nextSet === 3) {
          helperMsg.innerHTML = '<span class="text-amber-300 font-semibold">Seri 1-1:</span> Set 2 selesai! Tombol <b>"▶ Mulai Set 3"</b> kini terbuka untuk babak penentuan.';
        } else {
          helperMsg.innerHTML = '<span class="text-emerald-400 font-semibold">Pertandingan Selesai:</span> Pemenang sudah ditentukan! Silakan tekan tombol <b>"✓ Selesai Pertandingan"</b> di bawah.';
        }
      } else if (currentSet === 3 && !isFinished) {
        helperMsg.innerHTML = '<span class="text-emerald-400 font-semibold">Set 3 Aktif:</span> Babak Rubber Game dimainkan. Tekan tombol <b>"✓ Selesai Set 3"</b> setelah pertandingan selesai.';
      } else if (currentSet === 3 && isFinished) {
        helperMsg.innerHTML = '<span class="text-emerald-400 font-semibold">Set 3 Selesai:</span> Silakan tekan tombol <b>"✓ Selesai Pertandingan"</b> untuk memajukan tim di bracket.';
      }
    }
  }

  // 5. Update Kotak Set / Babak (Boxes), Badges, Tombol Skor, dan Footer Box
  const maxBoxes = isFutsal ? 2 : 3;
  for (let s = 1; s <= maxBoxes; s++) {
    const box = document.getElementById(`box-set-${matchId}-${s}`);
    const badge = document.getElementById(`badge-set-${matchId}-${s}`);
    const scoreBtns = document.querySelectorAll(`.btn-score-${matchId}-${s}`);
    const footer = document.getElementById(`footer-set-${matchId}-${s}`);

    const isThisActive = (s === currentSet);
    const isThisEditable = (isThisActive && !isFinished);

    if (box) {
      if (isThisActive) {
        box.className = isFinished
          ? 'rounded-2xl p-4 transition-all duration-300 flex flex-col justify-between border-2 border-cyan-500/80 bg-slate-900/90 shadow-xl'
          : 'rounded-2xl p-4 transition-all duration-300 flex flex-col justify-between border-2 border-emerald-500/90 bg-slate-900/90 shadow-xl shadow-emerald-500/10 ring-2 ring-emerald-500/20';
      } else {
        box.className = 'rounded-2xl p-4 transition-all duration-300 flex flex-col justify-between border border-slate-800 bg-slate-950/40 opacity-60';
      }
    }

    if (badge) {
      if (isThisActive && !isFinished) {
        badge.innerHTML = `
          <span class="rounded-full bg-emerald-950/90 border border-emerald-500/50 px-2.5 py-0.5 text-[10px] font-black text-emerald-300 flex items-center gap-1">
            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-ping"></span> AKTIF DIMAINKAN
          </span>
        `;
      } else if (s <= currentSet && (isFinished || s < currentSet)) {
        badge.innerHTML = `
          <span class="rounded-full bg-cyan-950/80 border border-cyan-500/50 px-2.5 py-0.5 text-[10px] font-bold text-cyan-300">
            ✓ SELESAI (TERKUNCI)
          </span>
        `;
      } else {
        badge.innerHTML = `
          <span class="rounded-full bg-slate-900 border border-slate-800 px-2.5 py-0.5 text-[10px] font-bold text-slate-500">
            🔒 TERKUNCI
          </span>
        `;
      }
    }

    // Toggle tombol skor + / -
    scoreBtns.forEach(sb => {
      if (isThisEditable) {
        sb.disabled = false;
        sb.classList.remove('opacity-30', 'cursor-not-allowed', 'pointer-events-none');
      } else {
        sb.disabled = true;
        sb.classList.add('opacity-30', 'cursor-not-allowed', 'pointer-events-none');
      }
    });

    // Update Footer Box
    if (footer) {
      if (isThisActive && !isFinished) {
        footer.innerHTML = `
          <span class="text-[11px] text-slate-400">Poin sedang dicatat</span>
          <button type="button"
                  onclick="finishSet(${matchId}, ${s})"
                  class="flex items-center gap-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-3 py-1.5 text-xs shadow-md shadow-emerald-600/30 active:scale-95 transition">
            ✓ Selesai ${term} ${s}
          </button>
        `;
      } else if (isThisActive && isFinished) {
        footer.innerHTML = `
          <span class="text-[11px] text-cyan-300 font-bold">✓ ${term} ${s} Telah Selesai</span>
          <button type="button"
                  onclick="reopenSet(${matchId})"
                  class="text-xs text-slate-400 hover:text-white underline">
            ↺ Koreksi Skor
          </button>
        `;
      } else if (s < currentSet) {
        footer.innerHTML = `
          <span class="text-[11px] text-slate-400 font-medium">✓ ${term} ${s} Selesai</span>
          <span class="text-[10px] text-slate-500">Terkunci</span>
        `;
      } else {
        footer.innerHTML = `
          <span class="text-[11px] text-slate-500 font-medium">Belum Dimulai</span>
          <span class="text-[10px] text-slate-600">Terkunci</span>
        `;
      }
    }
  }

  // 6. Update Set 2 Carry-Over Values jika relevan
  if (state.set2_a !== undefined) {
    const v2a = document.getElementById(`val-${matchId}-2-a`);
    if (v2a) v2a.textContent = state.set2_a;
  }
  if (state.set2_b !== undefined) {
    const v2b = document.getElementById(`val-${matchId}-2-b`);
    if (v2b) v2b.textContent = state.set2_b;
  }

  // 7. Update Summary Score
  if (state.score1 !== undefined) {
    const s1 = document.getElementById(`summary-score1-${matchId}`);
    if (s1) s1.textContent = state.score1;
  }
  if (state.score2 !== undefined) {
    const s2 = document.getElementById(`summary-score2-${matchId}`);
    if (s2) s2.textContent = state.score2;
  }

  // 8. Update Preview Ringkasan
  const prevEl = document.getElementById(`preview-${matchId}`);
  if (prevEl) {
    if (isFutsal && state.set1_a !== undefined) {
      prevEl.textContent = `Babak 1 (${state.set1_a}-${state.set1_b}) ➔ Babak 2 (${state.set2_a}-${state.set2_b}) · Total Gol: ${state.score1}-${state.score2}` +
        (state.winnerPreview ? ` · [${state.winnerPreview}]` : '');
    } else if (state.scoringSystem === 'poin_lanjut') {
      prevEl.textContent = `Set 1 (Tengah: ${state.set1_a}-${state.set1_b}) ➔ Set 2 (Akumulasi Akhir: ${state.set2_a}-${state.set2_b})` +
        (state.winnerPreview ? ` · [${state.winnerPreview}]` : '');
    } else if (state.set1_a !== undefined) {
      prevEl.textContent = `Set 1 (${state.set1_a}-${state.set1_b}), Set 2 (${state.set2_a}-${state.set2_b}), Set 3 (${state.set3_a}-${state.set3_b})` +
        (state.winnerPreview ? ` · [${state.winnerPreview}]` : '');
    }
  }

  // 9. Highlight tombol Selesai Pertandingan jika match siap diselesaikan
  const btnFinishMatch = document.getElementById(`btn-finish-match-${matchId}`);
  if (btnFinishMatch && state.canFinishMatch) {
    btnFinishMatch.classList.add('ring-4', 'ring-emerald-400', 'animate-pulse');
  }
}

/**
 * Menambah / mengurangi skor per set via Fetch API.
 */
async function updatePoint(matchId, setNum, teamSide, delta) {
  const btn = event.currentTarget;
  btn.disabled = true;

  try {
    const formData = new URLSearchParams();
    formData.append('set', setNum);
    formData.append('team', teamSide);
    formData.append('delta', delta);
    formData.append('_token', document.querySelector('meta[name="csrf"]').content);

    const res = await fetch('<?= url('admin/live-panel') ?>/' + matchId + '/point', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-CSRF-Token': document.querySelector('meta[name="csrf"]').content,
      },
      body: formData.toString()
    });

    const data = await res.json();
    if (!data.ok) {
      alert(data.error || 'Gagal memperbarui skor.');
      return;
    }

    // Update elemen skor per set di DOM
    const valEl = document.getElementById(`val-${matchId}-${setNum}-${teamSide}`);
    if (valEl) {
      valEl.textContent = data.value;
      valEl.classList.add('text-amber-400', 'scale-110');
      setTimeout(() => valEl.classList.remove('text-amber-400', 'scale-110'), 300);
    }

    // Jika sistem Poin Lanjut dan mengupdate Set 1, sinkronkan juga nilai awal Set 2 di layar
    if (data.scoring_system === 'poin_lanjut' && setNum === 1) {
      const v2 = document.getElementById(`val-${matchId}-2-${teamSide}`);
      if (v2) {
        v2.textContent = (teamSide === 'a') ? data.set2_a : data.set2_b;
      }
    }

    // Update ringkasan score1 & score2
    const s1 = document.getElementById(`summary-score1-${matchId}`);
    const s2 = document.getElementById(`summary-score2-${matchId}`);
    if (s1) s1.textContent = data.score1;
    if (s2) s2.textContent = data.score2;

    // Update preview status
    const prevEl = document.getElementById(`preview-${matchId}`);
    if (prevEl) {
      const card = document.getElementById('match-card-' + matchId);
      const isFutsal = (card && card.dataset.sportType === 'futsal');
      if (isFutsal) {
        prevEl.textContent = `Babak 1 (${data.set1_a}-${data.set1_b}) ➔ Babak 2 (${data.set2_a}-${data.set2_b}) · Total Gol: ${data.score1}-${data.score2}` +
          (data.winner_preview ? ` · [${data.winner_preview}]` : '');
      } else if (data.scoring_system === 'poin_lanjut') {
        prevEl.textContent = `Set 1 (Tengah: ${data.set1_a}-${data.set1_b}) ➔ Set 2 (Akumulasi Akhir: ${data.set2_a}-${data.set2_b})` +
          (data.winner_preview ? ` · [${data.winner_preview}]` : '');
      } else {
        prevEl.textContent = `Set 1 (${data.set1_a}-${data.set1_b}), Set 2 (${data.set2_a}-${data.set2_b}), Set 3 (${data.set3_a}-${data.set3_b})` +
          (data.winner_preview ? ` · [${data.winner_preview}]` : '');
      }
    }

  } catch (err) {
    console.error(err);
    alert('Terjadi kesalahan koneksi saat mengirim skor.');
  } finally {
    btn.disabled = false;
  }
}

// ==========================================
// STOPWATCH TIMER FUTSAL BERKELANJUTAN (CONTINUOUS)
// ==========================================
const futsalTimers = {};

function formatTime(totalSeconds) {
  const m = Math.floor(totalSeconds / 60);
  const s = totalSeconds % 60;
  return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
}

function initFutsalTimers() {
  document.querySelectorAll('.live-match-card[data-sport-type="futsal"]').forEach(card => {
    const mid = parseInt(card.dataset.matchId, 10);
    const dbHalfDur = parseInt(card.dataset.futsalHalfDuration, 10) || 5;
    const dbElapsed = parseInt(card.dataset.futsalElapsedSeconds, 10) || 0;
    const cardCurrSet = parseInt(card.dataset.currentSet, 10) || 0;
    let saved = null;
    try {
      saved = JSON.parse(localStorage.getItem('futsal_timer_' + mid));
    } catch (e) {}

    // Durasi babak selalu mengacu ke waktu yang ditentukan oleh admin di pengaturan match
    const halfDur = dbHalfDur;
    const initialElapsed = (saved && typeof saved.elapsed === 'number') ? saved.elapsed : dbElapsed;
    const initialHalf = (cardCurrSet >= 2) ? 2 : ((saved && saved.half) ? parseInt(saved.half, 10) : 1);

    futsalTimers[mid] = {
      halfDuration: Math.max(1, Math.min(60, halfDur)),
      elapsed: Math.max(0, initialElapsed),
      isRunning: false,
      interval: null,
      half: inArray(initialHalf, [1, 2]) ? initialHalf : 1,
    };

    const durInput = document.getElementById('futsal-duration-' + mid);
    if (durInput) durInput.value = futsalTimers[mid].halfDuration;

    const halfSelect = document.getElementById('timer-half-select-' + mid);
    if (halfSelect) halfSelect.value = futsalTimers[mid].half;

    // Jika timer sedang aktif sebelum reload halaman, tambahkan selisih waktu
    if (saved && saved.isRunning && saved.lastTick) {
      const diffSec = Math.floor((Date.now() - saved.lastTick) / 1000);
      futsalTimers[mid].elapsed += Math.max(0, diffSec);
      startTimerInterval(mid);
    } else {
      updateTimerDisplay(mid);
    }
  });
}

function inArray(needle, haystack) {
  return haystack.indexOf(needle) !== -1;
}

function updateTimerDisplay(mid) {
  const t = futsalTimers[mid];
  if (!t) return;

  const disp = document.getElementById('timer-display-' + mid);
  if (disp) disp.textContent = formatTime(t.elapsed);

  // Status Babak / Half Time Badge
  const halfBadge = document.getElementById('timer-half-badge-' + mid);
  if (halfBadge) {
    const card = document.getElementById('match-card-' + mid);
    const currSet = card ? parseInt(card.dataset.currentSet || '0', 10) : 0;
    const isSetFin = card ? (card.dataset.isSetFinished === '1' || card.dataset.isSetFinished === 'true') : false;
    if (currSet === 1 && isSetFin) {
      halfBadge.textContent = '⏸️ HALF TIME';
      halfBadge.className = 'font-mono text-xs font-black text-amber-300 bg-amber-950/80 border border-amber-500/40 px-2.5 py-0.5 rounded-md';
    } else {
      halfBadge.textContent = 'BABAK ' + t.half;
      halfBadge.className = 'font-mono text-xs font-black text-emerald-300 bg-emerald-950/80 border border-emerald-500/40 px-2.5 py-0.5 rounded-md';
    }
  }

  const halfSelect = document.getElementById('timer-half-select-' + mid);
  if (halfSelect && parseInt(halfSelect.value, 10) !== t.half) {
    halfSelect.value = t.half;
  }

  const badge = document.getElementById('timer-status-badge-' + mid);
  if (badge) {
    if (t.isRunning) {
      badge.className = 'rounded-full bg-emerald-500/20 border border-emerald-500/40 px-2.5 py-0.5 text-[10px] font-black text-emerald-400 animate-pulse';
      badge.textContent = 'BERJALAN';
    } else {
      badge.className = 'rounded-full bg-slate-800 px-2.5 py-0.5 text-[10px] font-bold text-slate-400';
      badge.textContent = 'JEDA';
    }
  }

  // Tombol Jeda / Lanjutkan Timer
  const btnToggle = document.getElementById('btn-timer-toggle-' + mid) || document.getElementById('btn-timer-start-' + mid);
  if (btnToggle) {
    if (t.isRunning) {
      btnToggle.className = 'flex items-center gap-1.5 rounded-xl border border-amber-500/50 bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 font-black px-3 py-2 text-xs shadow-md shadow-amber-500/20 active:scale-95 transition';
      btnToggle.innerHTML = '⏸ Jeda Timer';
    } else {
      btnToggle.className = 'flex items-center gap-1.5 rounded-xl border border-emerald-500/50 bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 font-black px-3 py-2 text-xs shadow-md shadow-emerald-500/20 active:scale-95 transition';
      btnToggle.innerHTML = '▶ Lanjutkan Timer';
    }
  }

  // Tampilkan Waktu Babak Tersimpan Sebelum & Selama Babak Berlangsung
  const savedTxt = document.getElementById('saved-duration-text-' + mid);
  if (savedTxt) {
    savedTxt.textContent = formatTime(t.halfDuration * 60);
    const parentSpan = savedTxt.parentElement;
    if (parentSpan) {
      const hint = parentSpan.querySelector('span');
      if (hint) hint.textContent = ` (${t.halfDuration} Menit/Babak)`;
    }
  }

  const targetInfo = document.getElementById('timer-target-info-' + mid);
  if (targetInfo) {
    const half1End = t.halfDuration * 60;
    const half2End = t.halfDuration * 120;
    const card = document.getElementById('match-card-' + mid);
    const currSet = card ? parseInt(card.dataset.currentSet || '0', 10) : 0;
    const isSetFin = card ? (card.dataset.isSetFinished === '1' || card.dataset.isSetFinished === 'true') : false;

    if (t.half === 1) {
      if (currSet === 1 && isSetFin) {
        targetInfo.innerHTML = `<span class="text-amber-400 font-bold">⏸️ HALF TIME (Istirahat Babak 1 Selesai: ${formatTime(half1End)})</span> · Tekan <b>"▶ Mulai Babak 2"</b> untuk melanjutkan!`;
      } else if (t.elapsed >= half1End) {
        targetInfo.innerHTML = `<span class="text-emerald-400 font-bold">✓ Waktu Babak 1 Habis (${formatTime(half1End)})</span> · Tekan "✓ Selesai Babak 1" untuk turun minum`;
      } else {
        targetInfo.textContent = `Target Babak 1: 00:00 - ${formatTime(half1End)}`;
      }
    } else {
      if (currSet === 2 && isSetFin) {
        targetInfo.innerHTML = `<span class="text-emerald-400 font-bold">🏆 Pertandingan Selesai!</span> · Tekan tombol "✓ Selesai Pertandingan" di bawah`;
      } else if (t.elapsed >= half2End) {
        targetInfo.innerHTML = `<span class="text-amber-400 font-bold">🏆 Waktu Normal Habis (${formatTime(half2End)})</span> · Tekan "✓ Selesai Babak 2"`;
      } else {
        targetInfo.textContent = `Target Babak 2: ${formatTime(half1End)} - ${formatTime(half2End)}`;
      }
    }
  }

  // Simpan state timer ke localStorage
  try {
    localStorage.setItem('futsal_timer_' + mid, JSON.stringify({
      halfDuration: t.halfDuration,
      elapsed: t.elapsed,
      isRunning: t.isRunning,
      lastTick: Date.now(),
      half: t.half
    }));
  } catch (e) {}
}

function toggleTimer(mid) {
  const t = futsalTimers[mid];
  if (!t) return;
  if (t.isRunning) {
    pauseTimer(mid);
  } else {
    startTimerInterval(mid);
  }
}

function startTimerInterval(mid) {
  const t = futsalTimers[mid];
  if (!t) return;
  if (t.interval) clearInterval(t.interval);

  const half1End = t.halfDuration * 60;
  const half2End = t.halfDuration * 120;

  // Jika di Babak 2 dan waktu masih di bawah batas Babak 1, mulai dari batas Babak 1
  if (t.half === 2 && t.elapsed < half1End) {
    t.elapsed = half1End;
  }

  t.isRunning = true;
  t.interval = setInterval(() => {
    t.elapsed++;

    // Notifikasi batas waktu tercapai (Timer TIDAK berhenti otomatis, wasit yang berhak menghentikan)
    if (t.half === 1 && t.elapsed === half1End) {
      showRefereeToast(`⏱️ Waktu normal Babak 1 selesai (${formatTime(half1End)})! Waktu tambahan berjalan...`);
    }

    if (t.half === 2 && t.elapsed === half2End) {
      showRefereeToast(`🏆 Waktu normal Babak 2 selesai (${formatTime(half2End)})! Waktu tambahan berjalan...`);
    }

    // Sinkronisasi berkala ke database setiap 5 detik
    if (t.elapsed % 5 === 0) {
      syncTimerToBackend(mid);
    }

    updateTimerDisplay(mid);
  }, 1000);

  updateTimerDisplay(mid);
  syncTimerToBackend(mid);
}

function pauseTimer(mid) {
  const t = futsalTimers[mid];
  if (!t) return;
  t.isRunning = false;
  if (t.interval) clearInterval(t.interval);
  t.interval = null;
  updateTimerDisplay(mid);
  syncTimerToBackend(mid);
}

function resetTimer(mid) {
  const t = futsalTimers[mid];
  if (!t) return;

  const half1End = t.halfDuration * 60;
  if (t.half === 1) {
    if (confirm('Reset waktu ke awal Babak 1 (00:00)?')) {
      pauseTimer(mid);
      t.elapsed = 0;
      updateTimerDisplay(mid);
      syncTimerToBackend(mid);
    }
  } else {
    if (confirm(`Reset waktu ke awal Babak 2 (${formatTime(half1End)})?`)) {
      pauseTimer(mid);
      t.elapsed = half1End;
      updateTimerDisplay(mid);
      syncTimerToBackend(mid);
    }
  }
}

function changeTimerHalf(mid) {
  const t = futsalTimers[mid];
  const sel = document.getElementById('timer-half-select-' + mid);
  if (!t || !sel) return;

  const newHalf = parseInt(sel.value, 10);
  t.half = newHalf;
  const half1End = t.halfDuration * 60;

  // Reset Babak Kedua: timer murni dimulai dari menit batas babak 1 dan tidak membawa waktu tambahan dari babak 1
  if (newHalf === 2) {
    t.elapsed = half1End;
  } else if (newHalf === 1) {
    t.elapsed = 0;
  }

  pauseTimer(mid);
  updateTimerDisplay(mid);
  syncTimerToBackend(mid);
  showRefereeToast(`Babak diatur ke Babak ${newHalf}. Waktu: ${formatTime(t.elapsed)}`);
}

function changeFutsalDuration(mid, val) {
  const t = futsalTimers[mid];
  if (!t) return;
  const newDur = Math.max(1, Math.min(60, parseInt(val, 10) || 5));
  t.halfDuration = newDur;

  const half1End = newDur * 60;
  if (t.half === 2 && t.elapsed < half1End) {
    t.elapsed = half1End;
  }

  updateTimerDisplay(mid);
  syncTimerToBackend(mid);
  showRefereeToast(`Durasi babak diatur ke ${newDur} menit.`);
}

async function saveFutsalDuration(mid) {
  const durInput = document.getElementById('futsal-duration-' + mid);
  if (!durInput) return;
  const newDur = Math.max(1, Math.min(60, parseInt(durInput.value, 10) || 5));
  durInput.value = newDur;

  const t = futsalTimers[mid];
  if (t) {
    t.halfDuration = newDur;
    const half1End = newDur * 60;
    if (t.half === 2 && t.elapsed < half1End) {
      t.elapsed = half1End;
    }
  }

  const savedTxt = document.getElementById('saved-duration-text-' + mid);
  if (savedTxt) {
    savedTxt.textContent = formatTime(newDur * 60);
    const parentSpan = savedTxt.parentElement;
    if (parentSpan) {
      const hint = parentSpan.querySelector('span');
      if (hint) hint.textContent = ` (${newDur} Menit/Babak)`;
    }
  }

  updateTimerDisplay(mid);
  await syncTimerToBackend(mid);
  showRefereeToast(`💾 Waktu babak tersimpan: ${newDur} menit (${formatTime(newDur * 60)}/babak)`);
}

async function syncTimerToBackend(mid) {
  const t = futsalTimers[mid];
  if (!t) return;
  try {
    const formData = new URLSearchParams();
    formData.append('half_duration', t.halfDuration);
    formData.append('elapsed_seconds', t.elapsed);
    formData.append('timer_status', t.isRunning ? 'running' : 'paused');
    formData.append('_token', document.querySelector('meta[name="csrf"]').content);

    await fetch('<?= url('admin/live-panel') ?>/' + mid + '/timer', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-CSRF-Token': document.querySelector('meta[name="csrf"]').content,
      },
      body: formData.toString()
    });
  } catch (err) {
    console.error('Failed to sync timer:', err);
  }
}

function getTimerMinute(mid) {
  const t = futsalTimers[mid];
  if (!t || t.elapsed <= 0) return '1';
  const rawMin = Math.max(1, Math.floor(t.elapsed / 60) + 1);
  const d = t.halfDuration || 5;
  const half = t.half || 1;

  if (half === 1) {
    if (rawMin > d) {
      const extra = rawMin - d;
      return `${d}+${extra}`;
    }
    return String(rawMin);
  } else {
    // Babak 2: batas normal adalah 2 * d (contoh: jika durasi 5 menit, batasnya menit 10)
    const limit = d * 2;
    if (rawMin > limit) {
      const extra = rawMin - limit;
      return `${limit}+${extra}`;
    }
    return String(rawMin);
  }
}

// ==========================================
// MATCH HIGHLIGHTS & PERTUKARAN PEMAIN
// ==========================================
function renderHighlights(mid, list) {
  const container = document.getElementById('highlight-list-' + mid);
  if (!container) return;

  if (!list || list.length === 0) {
    container.innerHTML = '<div class="rounded-xl border border-dashed border-slate-800 bg-slate-900/40 p-4 text-center text-xs text-slate-500">Belum ada catatan gol, kartu, atau pergantian pemain untuk pertandingan ini.</div>';
    return;
  }

  const icons = {
    goal:               { emoji: '⚽',   label: 'Gol',                 badge: 'bg-emerald-500/20 text-emerald-400 border-emerald-500/40' },
    yellow_card:        { emoji: '🟨',   label: 'Kartu Kuning',        badge: 'bg-amber-500/20 text-amber-300 border-amber-500/40' },
    double_yellow_card: { emoji: '🟨🟥', label: 'Kartu Kuning Kedua',  badge: 'bg-orange-500/20 text-orange-300 border-orange-500/40' },
    red_card:           { emoji: '🟥',   label: 'Kartu Merah',         badge: 'bg-rose-500/20 text-rose-400 border-rose-500/40' },
    substitution:       { emoji: '🔄',   label: 'Pertukaran Pemain',   badge: 'bg-sky-500/20 text-sky-400 border-sky-500/40' },
    half_time:          { emoji: '⏸️',   label: 'Half Time',           badge: 'bg-amber-500/20 text-amber-300 border-amber-500/50 font-bold' },
    full_time:          { emoji: '🏁',   label: 'Full Time',           badge: 'bg-cyan-500/20 text-cyan-300 border-cyan-500/50 font-bold' },
  };

  container.innerHTML = list.map(h => {
    const info = icons[h.event_type] || icons.goal;

    if (h.event_type === 'half_time' || h.event_type === 'full_time') {
      const isHt = (h.event_type === 'half_time');
      return `
        <div class="flex items-center justify-between gap-3 rounded-xl border ${isHt ? 'border-amber-500/40 bg-amber-950/40 text-amber-300' : 'border-cyan-500/40 bg-cyan-950/40 text-cyan-300'} px-3.5 py-2.5 text-xs font-bold shadow-sm">
          <div class="flex items-center gap-2.5">
            <span class="font-mono text-amber-400 font-bold">${h.minute}'</span>
            <span class="inline-flex items-center gap-1.5 rounded-md border ${info.badge} px-2.5 py-0.5 text-[10px] font-black uppercase">
              <span>${info.emoji}</span>
              <span>${info.label}</span>
            </span>
            <span class="text-white">${isHt ? 'Turun Minum (Babak 1 Selesai)' : 'Peluit Panjang (Pertandingan Selesai)'}</span>
          </div>
          <button type="button"
                  onclick="deleteHighlight(${mid}, ${h.id})"
                  class="shrink-0 inline-flex items-center gap-1 rounded-lg border border-rose-500/30 bg-rose-950/40 hover:bg-rose-900/60 text-rose-300 hover:text-rose-100 px-2.5 py-1 text-[11px] font-bold transition shadow-sm active:scale-95"
                  title="Hapus penanda ini">
            <span>✕</span>
            <span>Batal</span>
          </button>
        </div>
      `;
    }

    let detailHtml = `<span class="font-bold text-white truncate">${escapeHtml(h.player_name)}</span>`;

    if (h.event_type === 'substitution') {
      if (h.player_out && h.player_in) {
        detailHtml = `
          <span class="text-slate-300 truncate text-xs">
            Keluar: <b class="text-rose-300">${escapeHtml(h.player_out)}</b> ➔ Masuk: <b class="text-emerald-300">${escapeHtml(h.player_in)}</b>
          </span>
        `;
      }
    }

    return `
      <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-800 bg-slate-900/80 px-3.5 py-2.5 text-xs hover:border-slate-700 transition">
        <div class="flex items-center gap-2.5 min-w-0 flex-wrap sm:flex-nowrap">
          <span class="font-mono font-bold text-amber-400 min-w-[28px]">${h.minute}'</span>
          <span class="inline-flex items-center gap-1 rounded-md border ${info.badge} px-2 py-0.5 text-[10px] font-black">
            <span>${info.emoji}</span>
            <span>${info.label}</span>
          </span>
          ${detailHtml}
          <span class="text-slate-400 truncate text-[11px]">(${escapeHtml(h.team_name || '')})</span>
        </div>
        <button type="button"
                onclick="deleteHighlight(${mid}, ${h.id})"
                class="shrink-0 inline-flex items-center gap-1 rounded-lg border border-rose-500/30 bg-rose-950/40 hover:bg-rose-900/60 text-rose-300 hover:text-rose-100 px-2.5 py-1 text-[11px] font-bold transition shadow-sm active:scale-95"
                title="Batalkan / Hapus catatan kejadian ini (jika gol, skor otomatis berkurang)">
          <span>✕</span>
          <span>Batal</span>
        </button>
      </div>
    `;
  }).join('');
}

function onEventTypeChange(mid) {
  const eventSelect = document.getElementById('hl-event-' + mid);
  if (!eventSelect) return;
  const evt = eventSelect.value;

  const singleContainer = document.getElementById('hl-single-player-container-' + mid);
  const subContainer = document.getElementById('hl-sub-container-' + mid);
  const submitWrapper = document.getElementById('hl-submit-wrapper-' + mid);
  const title = document.getElementById('hl-form-title-' + mid);
  const icon = document.getElementById('hl-form-icon-' + mid);

  const titles = {
    goal: 'Catat Gol Baru',
    yellow_card: 'Catat Kartu Kuning',
    double_yellow_card: 'Catat Kartu Kuning Kedua',
    red_card: 'Catat Kartu Merah',
    substitution: 'Catat Pertukaran Pemain'
  };
  const emojis = { goal: '⚽', yellow_card: '🟨', double_yellow_card: '🟨🟥', red_card: '🟥', substitution: '🔄' };

  if (title) title.textContent = titles[evt] || 'Catat Kejadian';
  if (icon) icon.textContent = emojis[evt] || '⚡';

  if (evt === 'substitution') {
    if (singleContainer) singleContainer.classList.add('hidden');
    if (subContainer) subContainer.classList.remove('hidden');
    if (submitWrapper) submitWrapper.className = 'sm:col-span-2 lg:col-span-4 pt-1';
    updateSubPlayerDropdowns(mid);
  } else {
    if (singleContainer) singleContainer.classList.remove('hidden');
    if (subContainer) subContainer.classList.add('hidden');
    if (submitWrapper) submitWrapper.className = '';
    updatePlayerDropdown(mid);
  }
}

function openHighlightForm(mid, eventType) {
  const form = document.getElementById('hl-form-container-' + mid);
  if (!form) return;
  form.classList.remove('hidden');

  const eventSelect = document.getElementById('hl-event-' + mid);
  if (eventSelect) eventSelect.value = eventType;

  // Waktu otomatis ambil dari stopwatch timer yang sedang berjalan
  const currentMin = getTimerMinute(mid);
  const minInput = document.getElementById('hl-minute-' + mid);
  if (minInput) minInput.value = currentMin;

  const autoHint = document.getElementById('hl-timer-auto-hint-' + mid);
  if (autoHint) autoHint.textContent = `${currentMin}'`;

  onEventTypeChange(mid);
}

function closeHighlightForm(mid) {
  const form = document.getElementById('hl-form-container-' + mid);
  if (form) form.classList.add('hidden');
}

function getTeamMembers(mid, teamId) {
  const card = document.getElementById('match-card-' + mid);
  if (!card) return [];
  const t1Id = parseInt(card.dataset.team1Id, 10);
  try {
    if (teamId === t1Id) {
      return JSON.parse(card.dataset.team1Members || '[]');
    } else {
      return JSON.parse(card.dataset.team2Members || '[]');
    }
  } catch (e) {
    return [];
  }
}

function updatePlayerDropdown(mid) {
  const teamSelect = document.getElementById('hl-team-' + mid);
  const playerSelect = document.getElementById('hl-player-select-' + mid);
  if (!teamSelect || !playerSelect) return;

  const teamId = parseInt(teamSelect.value, 10);
  const members = getTeamMembers(mid, teamId);

  playerSelect.innerHTML = '';
  if (Array.isArray(members) && members.length > 0) {
    members.forEach(m => {
      const opt = document.createElement('option');
      opt.value = m;
      opt.textContent = m;
      playerSelect.appendChild(opt);
    });
  }
  const customOpt = document.createElement('option');
  customOpt.value = '__custom__';
  customOpt.textContent = (members && members.length > 0) ? '+ Nama Lain (Ketik Manual)' : 'Ketik Manual...';
  playerSelect.appendChild(customOpt);

  if (!members || members.length === 0) {
    playerSelect.value = '__custom__';
  }

  onPlayerSelectChange(mid);
}

function onPlayerSelectChange(mid) {
  const playerSelect = document.getElementById('hl-player-select-' + mid);
  const manualInput = document.getElementById('hl-player-manual-' + mid);
  if (!playerSelect || !manualInput) return;

  if (playerSelect.value === '__custom__') {
    manualInput.classList.remove('hidden');
    manualInput.focus();
  } else {
    manualInput.classList.add('hidden');
  }
}

function updateSubPlayerDropdowns(mid) {
  const teamSelect = document.getElementById('hl-team-' + mid);
  const outSelect = document.getElementById('hl-player-out-select-' + mid);
  const inSelect = document.getElementById('hl-player-in-select-' + mid);
  if (!teamSelect || !outSelect || !inSelect) return;

  const teamId = parseInt(teamSelect.value, 10);
  const members = getTeamMembers(mid, teamId);

  [outSelect, inSelect].forEach(sel => {
    sel.innerHTML = '';
    if (Array.isArray(members) && members.length > 0) {
      members.forEach(m => {
        const opt = document.createElement('option');
        opt.value = m;
        opt.textContent = m;
        sel.appendChild(opt);
      });
    }
    const customOpt = document.createElement('option');
    customOpt.value = '__custom__';
    customOpt.textContent = (members && members.length > 0) ? '+ Nama Lain (Ketik Manual)' : 'Ketik Manual...';
    sel.appendChild(customOpt);

    if (!members || members.length === 0) {
      sel.value = '__custom__';
    }
  });

  onSubPlayerSelectChange(mid, 'out');
  onSubPlayerSelectChange(mid, 'in');
}

function onSubPlayerSelectChange(mid, type) {
  const sel = document.getElementById(`hl-player-${type}-select-` + mid);
  const manual = document.getElementById(`hl-player-${type}-manual-` + mid);
  if (!sel || !manual) return;

  if (sel.value === '__custom__') {
    manual.classList.remove('hidden');
    manual.focus();
  } else {
    manual.classList.add('hidden');
  }
}

async function submitHighlight(mid) {
  const btn = document.getElementById('btn-hl-submit-' + mid);
  if (btn) btn.disabled = true;

  const teamSelect = document.getElementById('hl-team-' + mid);
  const eventSelect = document.getElementById('hl-event-' + mid);
  const minuteInput = document.getElementById('hl-minute-' + mid);

  const teamId = teamSelect ? teamSelect.value : '';
  const eventType = eventSelect ? eventSelect.value : 'goal';
  const minute = getTimerMinute(mid);
  if (minuteInput) minuteInput.value = minute;

  const card = document.getElementById('match-card-' + mid);
  const currentSetFromCard = card ? parseInt(card.dataset.currentSet || '0', 10) : 0;
  const period = currentSetFromCard > 0 ? currentSetFromCard : (futsalTimers[mid]?.half || 1);

  let playerName = '';
  let playerIn = '';
  let playerOut = '';

  if (eventType === 'substitution') {
    const outSelect = document.getElementById('hl-player-out-select-' + mid);
    const outManual = document.getElementById('hl-player-out-manual-' + mid);
    const inSelect = document.getElementById('hl-player-in-select-' + mid);
    const inManual = document.getElementById('hl-player-in-manual-' + mid);

    playerOut = (outSelect && outSelect.value === '__custom__') ? (outManual ? outManual.value.trim() : '') : (outSelect ? outSelect.value : '');
    playerIn = (inSelect && inSelect.value === '__custom__') ? (inManual ? inManual.value.trim() : '') : (inSelect ? inSelect.value : '');

    if (!playerOut || !playerIn) {
      alert('Mohon pilih atau masukkan nama Pemain Keluar dan Pemain Masuk.');
      if (btn) btn.disabled = false;
      return;
    }
    playerName = `Keluar: ${playerOut} ➔ Masuk: ${playerIn}`;
  } else {
    const playerSelect = document.getElementById('hl-player-select-' + mid);
    const manualInput = document.getElementById('hl-player-manual-' + mid);
    playerName = (playerSelect && playerSelect.value === '__custom__') ? (manualInput ? manualInput.value.trim() : '') : (playerSelect ? playerSelect.value : '');

    if (!playerName) {
      alert('Mohon pilih atau ketik nama pemain.');
      if (btn) btn.disabled = false;
      return;
    }
  }

  try {
    const formData = new URLSearchParams();
    formData.append('team_id', teamId);
    formData.append('event_type', eventType);
    formData.append('player_name', playerName);
    formData.append('player_in', playerIn);
    formData.append('player_out', playerOut);
    formData.append('minute', minute);
    formData.append('period', period);
    formData.append('_token', document.querySelector('meta[name="csrf"]').content);

    const res = await fetch('<?= url('admin/live-panel') ?>/' + mid + '/highlight', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-CSRF-Token': document.querySelector('meta[name="csrf"]').content,
      },
      body: formData.toString()
    });

    const data = await res.json();
    if (!data.ok) {
      alert(data.error || 'Gagal menyimpan catatan highlight.');
      return;
    }

    // Re-render timeline highlights
    renderHighlights(mid, data.highlights);

    // Update DOM dataset
    const card = document.getElementById('match-card-' + mid);
    if (card) {
      card.dataset.highlights = JSON.stringify(data.highlights);
    }

    // Update skor jika gol
    if (data.set1_a !== undefined) {
      const v1a = document.getElementById(`val-${mid}-1-a`);
      if (v1a) v1a.textContent = `${data.set1_a} Gol`;
    }
    if (data.set1_b !== undefined) {
      const v1b = document.getElementById(`val-${mid}-1-b`);
      if (v1b) v1b.textContent = `${data.set1_b} Gol`;
    }
    if (data.set2_a !== undefined) {
      const v2a = document.getElementById(`val-${mid}-2-a`);
      if (v2a) v2a.textContent = `${data.set2_a} Gol`;
    }
    if (data.set2_b !== undefined) {
      const v2b = document.getElementById(`val-${mid}-2-b`);
      if (v2b) v2b.textContent = `${data.set2_b} Gol`;
    }
    const s1 = document.getElementById(`summary-score1-${mid}`);
    if (s1) s1.textContent = data.score1;
    const s2 = document.getElementById(`summary-score2-${mid}`);
    if (s2) s2.textContent = data.score2;

    const prev = document.getElementById(`preview-${mid}`);
    if (prev) {
      prev.textContent = `Babak 1 (${data.set1_a}-${data.set1_b}) ➔ Babak 2 (${data.set2_a}-${data.set2_b}) · Total Gol: ${data.score1}-${data.score2}`;
    }

    closeHighlightForm(mid);
    showRefereeToast('✅ Catatan kejadian berhasil dicatat!');

  } catch (err) {
    console.error(err);
    alert('Terjadi kesalahan koneksi saat menyimpan highlight.');
  } finally {
    if (btn) btn.disabled = false;
  }
}

async function deleteHighlight(mid, highlightId) {
  if (!confirm('Batalkan / Hapus catatan kejadian ini?\nJika ini adalah GOL, skor tim akan otomatis berkurang.')) {
    return;
  }

  try {
    const formData = new URLSearchParams();
    formData.append('_token', document.querySelector('meta[name="csrf"]').content);

    const res = await fetch('<?= url('admin/live-panel') ?>/' + mid + '/highlight/' + highlightId + '/delete', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-CSRF-Token': document.querySelector('meta[name="csrf"]').content,
      },
      body: formData.toString()
    });

    const data = await res.json();
    if (!data.ok) {
      alert(data.error || 'Gagal menghapus highlight.');
      return;
    }

    // Re-render timeline
    renderHighlights(mid, data.highlights);

    // Update DOM dataset
    const card = document.getElementById('match-card-' + mid);
    if (card) {
      card.dataset.highlights = JSON.stringify(data.highlights);
    }

    // Update nilai skor babak & ringkasan yang dikurangi secara otomatis
    if (data.set1_a !== undefined) {
      const v1a = document.getElementById(`val-${mid}-1-a`);
      if (v1a) v1a.textContent = `${data.set1_a} Gol`;
    }
    if (data.set1_b !== undefined) {
      const v1b = document.getElementById(`val-${mid}-1-b`);
      if (v1b) v1b.textContent = `${data.set1_b} Gol`;
    }
    if (data.set2_a !== undefined) {
      const v2a = document.getElementById(`val-${mid}-2-a`);
      if (v2a) v2a.textContent = `${data.set2_a} Gol`;
    }
    if (data.set2_b !== undefined) {
      const v2b = document.getElementById(`val-${mid}-2-b`);
      if (v2b) v2b.textContent = `${data.set2_b} Gol`;
    }
    const s1 = document.getElementById(`summary-score1-${mid}`);
    if (s1) s1.textContent = data.score1;
    const s2 = document.getElementById(`summary-score2-${mid}`);
    if (s2) s2.textContent = data.score2;

    const prev = document.getElementById(`preview-${mid}`);
    if (prev) {
      prev.textContent = `Babak 1 (${data.set1_a}-${data.set1_b}) ➔ Babak 2 (${data.set2_a}-${data.set2_b}) · Total Gol: ${data.score1}-${data.score2}`;
    }

    showRefereeToast('✅ Catatan kejadian telah dibatalkan & skor diperbarui!');
  } catch (err) {
    console.error(err);
    alert('Terjadi kesalahan koneksi saat menghapus highlight.');
  }
}

function escapeHtml(str) {
  if (!str) return '';
  const div = document.createElement('div');
  div.textContent = str;
  return div.innerHTML;
}

// Inisialisasi timers dan highlights saat halaman siap
document.addEventListener('DOMContentLoaded', () => {
  initFutsalTimers();
  document.querySelectorAll('.live-match-card[data-sport-type="futsal"]').forEach(card => {
    const mid = parseInt(card.dataset.matchId, 10);
    try {
      const hl = JSON.parse(card.dataset.highlights || '[]');
      renderHighlights(mid, hl);
    } catch (e) {}
  });
});

// ==========================================
// REFEREE FULLSCREEN & LOCKED FOCUS MODE
// ==========================================
let activeFullscreenMatchId = null;
let isRefereeLocked = false;
let unlockPanelTimeout = null;

/**
 * Mengaktifkan Mode Fokus Wasit (Fullscreen & Lock)
 */
async function enterRefereeFullscreen(matchId) {
  const card = document.getElementById(`match-card-${matchId}`);
  if (!card) return;

  activeFullscreenMatchId = matchId;
  isRefereeLocked = true;

  // 1. Berikan class fullscreen pada kartu pertandingan
  card.classList.add('referee-fullscreen-active');
  document.body.classList.add('overflow-hidden');

  // Reset status landscape guard jika sebelumnya pernah diabaikan
  const guard = document.getElementById(`landscape-guard-${matchId}`);
  if (guard) {
    guard.classList.remove('guard-dismissed');
  }

  // 2. Request native HTML5 Fullscreen (jika didukung)
  try {
    if (card.requestFullscreen) {
      await card.requestFullscreen();
    } else if (card.webkitRequestFullscreen) {
      await card.webkitRequestFullscreen();
    } else if (document.documentElement.requestFullscreen) {
      await document.documentElement.requestFullscreen();
    }
  } catch (err) {
    console.log('Native fullscreen fallback to CSS overlay:', err);
  }

  // 3. Paksa orientasi layar HP menjadi landscape
  tryForceLandscape();

  // 4. Periksa orientasi saat ini untuk menampilkan landscape guard jika masih portrait
  checkOrientationGuard(matchId);

  // 5. Tampilkan toast konfirmasi penguncian
  showRefereeToast('🔒 Mode Wasit Terkunci pada Pertandingan Ini. Klik sudut kanan atas untuk membuka kunci.');
}

/**
 * Mencoba mengunci orientasi ke landscape melalui Screen Orientation API
 */
function tryForceLandscape() {
  try {
    if (window.screen && window.screen.orientation && typeof window.screen.orientation.lock === 'function') {
      window.screen.orientation.lock('landscape').catch(err => {
        console.log('Screen orientation lock not permitted by browser/OS:', err);
      });
    }
  } catch (e) {
    console.log('Orientation lock error:', e);
  }
}

/**
 * Memeriksa orientasi perangkat, menampilkan overlay landscape jika sedang portrait pada HP
 */
function checkOrientationGuard(matchId) {
  const guard = document.getElementById(`landscape-guard-${matchId}`);
  if (!guard) return;

  const isPortrait = window.matchMedia('(orientation: portrait)').matches || (window.innerHeight > window.innerWidth && window.innerWidth <= 900);
  if (isPortrait && !guard.classList.contains('guard-dismissed')) {
    guard.classList.remove('hidden');
    guard.classList.add('flex');
  } else {
    guard.classList.add('hidden');
    guard.classList.remove('flex');
  }
}

/**
 * Wasit memilih untuk mengabaikan imbauan landscape (tetap dalam portrait)
 */
function dismissLandscapeGuard(matchId) {
  const guard = document.getElementById(`landscape-guard-${matchId}`);
  if (guard) {
    guard.classList.add('guard-dismissed', 'hidden');
    guard.classList.remove('flex');
  }
}

/**
 * Menangani klik pada stealth hotspot tersembunyi di sudut kanan atas
 */
function handleStealthHotspotClick(matchId) {
  if (!isRefereeLocked || activeFullscreenMatchId !== matchId) return;

  const panel = document.getElementById(`unlock-panel-${matchId}`);
  if (!panel) return;

  // Tampilkan panel konfirmasi unlock
  panel.classList.remove('hidden');

  // Hitung mundur 6 detik sebelum tersembunyi kembali secara otomatis
  let secondsLeft = 6;
  const timerEl = document.getElementById(`unlock-timer-${matchId}`);
  if (timerEl) timerEl.textContent = secondsLeft;

  if (unlockPanelTimeout) {
    clearInterval(unlockPanelTimeout);
  }

  unlockPanelTimeout = setInterval(() => {
    secondsLeft--;
    if (timerEl) timerEl.textContent = secondsLeft;
    if (secondsLeft <= 0) {
      clearInterval(unlockPanelTimeout);
      hideUnlockPanel(matchId);
    }
  }, 1000);
}

/**
 * Menyembunyikan kembali panel unlock
 */
function hideUnlockPanel(matchId) {
  const panel = document.getElementById(`unlock-panel-${matchId}`);
  if (panel) {
    panel.classList.add('hidden');
  }
  if (unlockPanelTimeout) {
    clearInterval(unlockPanelTimeout);
    unlockPanelTimeout = null;
  }
}

/**
 * Buka kunci dan keluar dari mode fullscreen
 */
async function exitRefereeFullscreen(matchId) {
  hideUnlockPanel(matchId);

  const card = document.getElementById(`match-card-${matchId}`);
  if (card) {
    card.classList.remove('referee-fullscreen-active');
  }
  document.body.classList.remove('overflow-hidden');

  activeFullscreenMatchId = null;
  isRefereeLocked = false;

  // Buka kunci orientasi layar
  try {
    if (window.screen && window.screen.orientation && typeof window.screen.orientation.unlock === 'function') {
      window.screen.orientation.unlock();
    }
  } catch (e) {}

  // Keluar dari native fullscreen jika aktif
  try {
    if (document.fullscreenElement || document.webkitFullscreenElement) {
      if (document.exitFullscreen) {
        await document.exitFullscreen();
      } else if (document.webkitExitFullscreen) {
        await document.webkitExitFullscreen();
      }
    }
  } catch (err) {
    console.log('Exit fullscreen error:', err);
  }

  showRefereeToast('Mode fokus wasit dinonaktifkan.');
}

/**
 * Toast notifikasi ringkas
 */
function showRefereeToast(message) {
  let toast = document.getElementById('referee-toast');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'referee-toast';
    toast.className = 'fixed bottom-5 left-1/2 -translate-x-1/2 z-[100005] rounded-2xl border border-blue-500/50 bg-slate-900/95 px-5 py-3 text-xs sm:text-sm font-bold text-white shadow-2xl shadow-black/80 backdrop-blur-md transition-all duration-300 pointer-events-none opacity-0 translate-y-3';
    document.body.appendChild(toast);
  }
  toast.innerHTML = message;
  toast.classList.remove('opacity-0', 'translate-y-3');
  toast.classList.add('opacity-100', 'translate-y-0');

  clearTimeout(toast.timeout);
  toast.timeout = setTimeout(() => {
    toast.classList.remove('opacity-100', 'translate-y-0');
    toast.classList.add('opacity-0', 'translate-y-3');
  }, 4000);
}

// Pantau perubahan ukuran/orientasi layar saat fullscreen aktif
window.addEventListener('resize', () => {
  if (activeFullscreenMatchId) {
    checkOrientationGuard(activeFullscreenMatchId);
  }
});
window.addEventListener('orientationchange', () => {
  if (activeFullscreenMatchId) {
    setTimeout(() => checkOrientationGuard(activeFullscreenMatchId), 200);
  }
});

// Cegah keluar tanpa membuka kunci
document.addEventListener('fullscreenchange', () => {
  if (!document.fullscreenElement && activeFullscreenMatchId && isRefereeLocked) {
    // Jika browser keluar dari native fullscreen (misal tombol Esc), overlay tetap mengunci layar
    showRefereeToast('⚠️ Mode Wasit Terkunci. Klik sudut kanan atas untuk membuka kunci.');
  }
});

window.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    closeLiveRefereeModal();
    if (activeFullscreenMatchId && isRefereeLocked) {
      showRefereeToast('⚠️ Layar Terkunci. Klik sudut kanan atas untuk memunculkan tombol buka kunci.');
    }
  }
});

// Peringatan jika tab hendak ditutup/direfresh saat terkunci
window.addEventListener('beforeunload', (e) => {
  if (activeFullscreenMatchId && isRefereeLocked) {
    e.preventDefault();
    e.returnValue = 'Pertandingan sedang berlangsung dan layar terkunci.';
    return e.returnValue;
  }
});

// ================= PENGATURAN WASIT DI LIVE PANEL =================
function openLiveRefereeModal(matchId, sport, team1, team2, refMain, refLine1, refLine2) {
  document.getElementById('liveRefMatchId').value = matchId;
  document.getElementById('liveRefSport').value = sport;
  document.getElementById('liveRefModalSubtitle').textContent = `${team1} VS ${team2} (Match #${matchId})`;
  document.getElementById('liveModalRefMain').value = refMain || '';
  document.getElementById('liveModalRefLine1').value = refLine1 || '';
  document.getElementById('liveModalRefLine2').value = refLine2 || '';
  document.getElementById('liveRefereeModal').classList.remove('hidden');
}

function closeLiveRefereeModal() {
  document.getElementById('liveRefereeModal').classList.add('hidden');
}

document.getElementById('liveRefereeModal')?.addEventListener('click', (e) => {
  if (e.target === e.currentTarget) closeLiveRefereeModal();
});

async function handleLiveRefereeSubmit(e) {
  e.preventDefault();
  const matchId = document.getElementById('liveRefMatchId').value;
  const sport = document.getElementById('liveRefSport').value;
  const refMain = document.getElementById('liveModalRefMain').value.trim();
  const refLine1 = document.getElementById('liveModalRefLine1').value.trim();
  const refLine2 = document.getElementById('liveModalRefLine2').value.trim();
  const btn = document.getElementById('liveRefSubmitBtn');

  btn.disabled = true;
  btn.textContent = 'Menyimpan...';

  try {
    const formData = new FormData();
    formData.append('referee_main', refMain);
    formData.append('referee_line1', refLine1);
    formData.append('referee_line2', refLine2);
    formData.append('ajax', '1');
    formData.append('csrf_token', document.querySelector('meta[name="csrf"]')?.content || '');

    const res = await fetch(`<?= url('admin') ?>/${sport}/matches/${matchId}/referees`, {
      method: 'POST',
      body: formData,
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    });

    const data = await res.json();
    if (data.ok) {
      showRefereeToast('✅ Data perangkat wasit berhasil diperbarui!');
      
      // Update DOM pada kartu
      const mainEl = document.getElementById(`card-ref-main-${matchId}`);
      if (mainEl) mainEl.textContent = refMain || 'Belum diatur panitia';
      const l1El = document.getElementById(`card-ref-line1-${matchId}`);
      if (l1El) l1El.textContent = refLine1 || '-';
      const l2El = document.getElementById(`card-ref-line2-${matchId}`);
      if (l2El) l2El.textContent = refLine2 || '-';

      // Update fullscreen banner jika ada
      const fsMainEl = document.getElementById(`fs-ref-main-${matchId}`);
      if (fsMainEl) fsMainEl.textContent = refMain || 'Wasit';
      const fsLinesEl = document.getElementById(`fs-ref-lines-${matchId}`);
      if (fsLinesEl) {
        const linesStr = [refLine1, refLine2].filter(Boolean).join(', ');
        fsLinesEl.textContent = linesStr || '-';
      }

      // Update header jika role wasit
      const hWasit = document.getElementById('header-petugas-wasit');
      if (hWasit && refMain) hWasit.textContent = refMain;
      const hGarisText = document.getElementById('header-wasit-garis-text');
      const hGarisWrap = document.getElementById('header-wasit-garis-wrap');
      const combinedLines = [refLine1, refLine2].filter(Boolean).join(', ');
      if (hGarisText && hGarisWrap) {
        if (combinedLines) {
          hGarisText.textContent = combinedLines;
          hGarisWrap.classList.remove('hidden');
        } else {
          hGarisText.textContent = '';
          hGarisWrap.classList.add('hidden');
        }
      }

      closeLiveRefereeModal();
    } else {
      alert(data.error || 'Gagal menyimpan perangkat wasit.');
    }
  } catch (err) {
    console.error('Error saving referees:', err);
    alert('Terjadi kesalahan koneksi saat menyimpan wasit.');
  } finally {
    btn.disabled = false;
    btn.textContent = 'Simpan Perangkat Wasit';
  }
}
</script>

