<?php
/**
 * Halaman Khusus Live Panel / Operator Skor Wasit
 * Antarmuka interaktif dengan tombol + dan - per set via Fetch API
 */
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

  <div class="flex items-center gap-2">
    <span class="rounded-xl border border-slate-700 bg-slate-800/80 px-3.5 py-1.5 text-xs text-slate-300">
      👤 Petugas: <b><?= e(\App\Core\Auth::user()['name'] ?? 'Wasit') ?></b>
      <span class="ml-1 text-slate-400">(<?= e(\App\Core\Auth::roleLabel()) ?>)</span>
    </span>
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
      ?>
      <div id="match-card-<?= $m['id'] ?>" class="live-match-card rounded-3xl border-2 border-red-500/60 bg-gradient-to-b from-slate-900 via-slate-900 to-slate-950 p-6 shadow-2xl shadow-red-500/10 transition-all">
        
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
            <!-- Badge Sistem Scoring -->
            <span class="rounded-xl border border-blue-500/30 bg-blue-950/40 px-3 py-1 text-xs font-bold text-blue-400">
              <?= $isRubber ? '🏆 Rubber Set (Best of 3)' : '📈 Poin Lanjut (Akumulasi 2 Set)' ?>
            </span>

            <span class="inline-flex items-center gap-1.5 rounded-full border border-red-500/40 bg-red-500/20 px-3 py-1 text-xs font-black text-red-400">
              <span class="h-2 w-2 rounded-full bg-red-400 animate-ping"></span>
              LIVE
            </span>
          </div>
        </div>

        <!-- Scoreboard Ringkasan Utama -->
        <div class="my-6 grid grid-cols-[1fr_auto_1fr] items-center gap-4 text-center">
          <!-- Tim 1 -->
          <div class="space-y-1">
            <span class="text-base sm:text-xl font-black text-white line-clamp-2">
              <?= e($m['team1_name'] ?? 'Tim 1') ?>
            </span>
            <div class="text-xs text-slate-400">
              <?= $isRubber ? 'Sets Won' : 'Total Poin' ?>:
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
            <div class="text-xs text-slate-400">
              <?= $isRubber ? 'Sets Won' : 'Total Poin' ?>:
              <span id="summary-score2-<?= $m['id'] ?>" class="font-mono text-2xl sm:text-3xl font-black text-indigo-400">
                <?= (int)$m['score2'] ?>
              </span>
            </div>
          </div>
        </div>

        <?php
          $currentSet = (int)($m['current_set'] ?? 0);
          $isSetFinished = (bool)($m['is_set_finished'] ?? false);

          // Kelayakan tombol Mulai Set:
          // Set 1: bisa diklik jika belum mulai (0) atau sedang di Set 1
          $canStartSet1 = ($currentSet === 0 || $currentSet === 1);
          // Set 2: HANYA bisa diklik jika Set 1 sudah SELESAI (current_set >= 1 && is_set_finished) atau sedang di Set 2
          $canStartSet2 = (($currentSet === 1 && $isSetFinished) || $currentSet === 2);
          // Set 3 (Rubber): HANYA bisa diklik jika Set 2 sudah SELESAI dan seri 1-1, atau sedang di Set 3
          $setsA = ((int)$m['set1_a'] > (int)$m['set1_b'] ? 1 : 0) + ((int)$m['set2_a'] > (int)$m['set2_b'] ? 1 : 0);
          $setsB = ((int)$m['set1_b'] > (int)$m['set1_a'] ? 1 : 0) + ((int)$m['set2_b'] > (int)$m['set2_a'] ? 1 : 0);
          $canStartSet3 = $isRubber && (($currentSet === 2 && $isSetFinished && $setsA === 1 && $setsB === 1) || $currentSet === 3);
        ?>

        <!-- Alur Pertandingan: Pemilihan Set Aktif & Tombol Selesai Set -->
        <div class="mb-5 rounded-2xl border border-slate-800 bg-slate-950/80 p-4 shadow-inner">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
              <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Set Aktif:</span>
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

            <!-- Tombol Pemilihan Set & Selesai Set -->
            <div class="flex flex-wrap items-center gap-2">
              <!-- Tombol Selesai Set (Hanya saat ada set yang sedang berjalan) -->
              <div id="wrapper-btn-finish-top-<?= $m['id'] ?>">
                <?php if ($currentSet > 0 && !$isSetFinished): ?>
                  <button type="button"
                          onclick="finishSet(<?= $m['id'] ?>, <?= $currentSet ?>)"
                          id="btn-top-finish-<?= $m['id'] ?>"
                          class="flex items-center gap-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-black px-3.5 py-1.5 text-xs shadow-lg shadow-emerald-600/30 active:scale-95 transition">
                    ✓ Selesai Set <?= $currentSet ?>
                  </button>
                <?php elseif ($currentSet > 0 && $isSetFinished): ?>
                  <button type="button"
                          onclick="reopenSet(<?= $m['id'] ?>)"
                          id="btn-top-reopen-<?= $m['id'] ?>"
                          class="flex items-center gap-1.5 rounded-xl border border-slate-700 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold px-3 py-1.5 text-xs active:scale-95 transition">
                    ↺ Koreksi Set <?= $currentSet ?>
                  </button>
                <?php endif; ?>
              </div>

              <!-- Tombol Mulai Set 1 -->
              <button type="button"
                      onclick="startSet(<?= $m['id'] ?>, 1)"
                      id="btn-set-<?= $m['id'] ?>-1"
                      class="btn-flow-set flex items-center gap-1.5 rounded-xl px-3.5 py-1.5 text-xs transition active:scale-95 <?= ($currentSet === 1) ? 'bg-emerald-600 border border-emerald-500 text-white font-black shadow-lg shadow-emerald-600/30 ring-2 ring-emerald-400/40' : 'bg-slate-800/90 border border-slate-700 text-slate-300 font-bold hover:bg-slate-700 hover:text-white' ?>"
                      <?= (!$canStartSet1) ? 'disabled="disabled" title="Set 1 sudah selesai"' : '' ?>>
                ▶ Mulai Set 1
              </button>

              <!-- Tombol Mulai Set 2 (Terkunci sebelum Set 1 Selesai) -->
              <button type="button"
                      onclick="startSet(<?= $m['id'] ?>, 2)"
                      id="btn-set-<?= $m['id'] ?>-2"
                      class="btn-flow-set flex items-center gap-1.5 rounded-xl px-3.5 py-1.5 text-xs transition active:scale-95 <?= ($currentSet === 2) ? 'bg-emerald-600 border border-emerald-500 text-white font-black shadow-lg shadow-emerald-600/30 ring-2 ring-emerald-400/40' : (($canStartSet2 && $currentSet !== 2) ? 'bg-blue-600 border border-blue-500 text-white font-black animate-pulse shadow-md shadow-blue-600/30' : 'bg-slate-800/90 border border-slate-700 text-slate-300 font-bold hover:bg-slate-700 hover:text-white') ?> <?= (!$canStartSet2) ? 'opacity-30 cursor-not-allowed pointer-events-none' : '' ?>"
                      <?= (!$canStartSet2) ? 'disabled="disabled" title="Selesaikan Set 1 terlebih dahulu"' : '' ?>>
                ▶ Mulai Set 2
              </button>

              <!-- Tombol Mulai Set 3 (Hanya jika Rubber Set & terkunci sebelum Set 2 selesai) -->
              <?php if ($isRubber): ?>
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

        <!-- Interactive Set Point Controls (Set 1, Set 2, Set 3) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 <?= $isRubber ? 'lg:grid-cols-3' : '' ?> gap-4 pt-2">
          
          <!-- SET 1 -->
          <?php
            $isSet1Active = ($currentSet === 1);
            $isSet1Editable = ($isSet1Active && !$isSetFinished);
          ?>
          <div id="box-set-<?= $m['id'] ?>-1" class="rounded-2xl p-4 transition-all duration-300 flex flex-col justify-between <?= $isSet1Active ? ($isSetFinished ? 'border-2 border-cyan-500/80 bg-slate-900/90 shadow-xl' : 'border-2 border-emerald-500/90 bg-slate-900/90 shadow-xl shadow-emerald-500/10 ring-2 ring-emerald-500/20') : 'border border-slate-800 bg-slate-950/40 opacity-60' ?>">
            <div>
              <div class="mb-3 text-center text-xs font-black uppercase tracking-wider text-slate-400 border-b border-slate-800/80 pb-2 flex items-center justify-between">
                <span>SET 1</span>
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

              <!-- Tim 1 Set 1 -->
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

              <!-- Tim 2 Set 1 -->
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
            </div>

            <!-- Footer Box Set 1: Tombol Selesai Set 1 / Koreksi -->
            <div id="footer-set-<?= $m['id'] ?>-1" class="mt-4 pt-3 border-t border-slate-800/80 flex items-center justify-between gap-2">
              <?php if ($currentSet === 1 && !$isSetFinished): ?>
                <span class="text-[11px] text-slate-400">Poin sedang dicatat</span>
                <button type="button"
                        onclick="finishSet(<?= $m['id'] ?>, 1)"
                        class="flex items-center gap-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-3 py-1.5 text-xs shadow-md shadow-emerald-600/30 active:scale-95 transition">
                  ✓ Selesai Set 1
                </button>
              <?php elseif ($currentSet === 1 && $isSetFinished): ?>
                <span class="text-[11px] text-cyan-300 font-bold">✓ Set 1 Telah Selesai</span>
                <button type="button"
                        onclick="reopenSet(<?= $m['id'] ?>)"
                        class="text-xs text-slate-400 hover:text-white underline">
                  ↺ Koreksi Skor
                </button>
              <?php elseif ($currentSet > 1): ?>
                <span class="text-[11px] text-slate-400 font-medium">✓ Set 1 Selesai</span>
                <span class="text-[10px] text-slate-500">Terkunci</span>
              <?php else: ?>
                <span class="text-[11px] text-slate-500 font-medium">Belum Dimulai</span>
                <span class="text-[10px] text-slate-600">Terkunci</span>
              <?php endif; ?>
            </div>
          </div>

          <!-- SET 2 -->
          <?php
            $isSet2Active = ($currentSet === 2);
            $isSet2Editable = ($isSet2Active && !$isSetFinished);
            $set2AVal = (!$isRubber && (int)$m['set2_a'] === 0 && (int)$m['set1_a'] > 0) ? (int)$m['set1_a'] : (int)$m['set2_a'];
            $set2BVal = (!$isRubber && (int)$m['set2_b'] === 0 && (int)$m['set1_b'] > 0) ? (int)$m['set1_b'] : (int)$m['set2_b'];
          ?>
          <div id="box-set-<?= $m['id'] ?>-2" class="rounded-2xl p-4 transition-all duration-300 flex flex-col justify-between <?= $isSet2Active ? ($isSetFinished ? 'border-2 border-cyan-500/80 bg-slate-900/90 shadow-xl' : 'border-2 border-emerald-500/90 bg-slate-900/90 shadow-xl shadow-emerald-500/10 ring-2 ring-emerald-500/20') : 'border border-slate-800 bg-slate-950/40 opacity-60' ?>">
            <div>
              <div class="mb-3 text-center text-xs font-black uppercase tracking-wider text-slate-400 border-b border-slate-800/80 pb-2 flex items-center justify-between gap-1 flex-wrap">
                <div class="flex items-center gap-1.5">
                  <span>SET 2</span>
                  <?php if (!$isRubber): ?>
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

              <!-- Tim 1 Set 2 -->
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

              <!-- Tim 2 Set 2 -->
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
            </div>

            <!-- Footer Box Set 2: Tombol Selesai Set 2 / Koreksi -->
            <div id="footer-set-<?= $m['id'] ?>-2" class="mt-4 pt-3 border-t border-slate-800/80 flex items-center justify-between gap-2">
              <?php if ($currentSet === 2 && !$isSetFinished): ?>
                <span class="text-[11px] text-slate-400">Poin sedang dicatat</span>
                <button type="button"
                        onclick="finishSet(<?= $m['id'] ?>, 2)"
                        class="flex items-center gap-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-3 py-1.5 text-xs shadow-md shadow-emerald-600/30 active:scale-95 transition">
                  ✓ Selesai Set 2
                </button>
              <?php elseif ($currentSet === 2 && $isSetFinished): ?>
                <span class="text-[11px] text-cyan-300 font-bold">✓ Set 2 Telah Selesai</span>
                <button type="button"
                        onclick="reopenSet(<?= $m['id'] ?>)"
                        class="text-xs text-slate-400 hover:text-white underline">
                  ↺ Koreksi Skor
                </button>
              <?php elseif ($currentSet > 2): ?>
                <span class="text-[11px] text-slate-400 font-medium">✓ Set 2 Selesai</span>
                <span class="text-[10px] text-slate-500">Terkunci</span>
              <?php else: ?>
                <span class="text-[11px] text-slate-500 font-medium">Belum Dimulai</span>
                <span class="text-[10px] text-slate-600">Terkunci</span>
              <?php endif; ?>
            </div>
          </div>

          <!-- SET 3 (Hanya untuk Rubber Set) -->
          <?php if ($isRubber): ?>
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

        <!-- Tombol Aksi Pertandingan -->
        <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-slate-800 pt-4">
          <div class="flex items-center gap-2 text-xs">
            <span class="text-slate-400">Ringkasan:</span>
            <span id="preview-<?= $m['id'] ?>" class="font-bold text-amber-400">
              <?php if ($isRubber): ?>
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

  } catch (err) {
    console.error(err);
    alert('Terjadi kesalahan koneksi saat mengaktifkan set.');
  } finally {
    if (btn) btn.disabled = false;
  }
}

/**
 * Menandai set aktif telah selesai.
 * Mengunci skor set ini dan MEMBUKA tombol untuk memilih set berikutnya!
 */
async function finishSet(matchId, setNum) {
  if (!confirm(`Selesaikan Set ${setNum} sekarang? Anda dapat memilih set berikutnya setelah ini.`)) {
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
      alert(data.error || 'Gagal menyelesaikan set.');
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

  } catch (err) {
    console.error(err);
    alert('Terjadi kesalahan koneksi saat menyelesaikan set.');
  }
}

/**
 * Membuka kembali set aktif untuk mengoreksi skor.
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
      alert(data.error || 'Gagal membuka kembali set.');
      return;
    }

    // Ubah status isSetFinished menjadi false pada DOM
    const curr = data.current_set;
    applyMatchState(matchId, {
      currentSet: curr,
      isSetFinished: false
    });

  } catch (err) {
    console.error(err);
    alert('Terjadi kesalahan koneksi saat membuka kembali set.');
  }
}

/**
 * Sinkronisasi visual status match ke DOM
 */
function applyMatchState(matchId, state) {
  const currentSet = state.currentSet;
  const isFinished = state.isSetFinished;

  // 1. Update Top Active Set Badge
  const badgeActive = document.getElementById(`badge-active-set-${matchId}`);
  if (badgeActive) {
    if (currentSet > 0 && !isFinished) {
      badgeActive.innerHTML = `
        <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-500/40 bg-emerald-500/20 px-3 py-1 text-xs font-black text-emerald-400">
          <span class="h-2 w-2 rounded-full bg-emerald-400 animate-ping"></span>
          SET ${currentSet}${currentSet === 3 ? ' (RUBBER)' : ''} SEDANG AKTIF
        </span>
      `;
    } else if (currentSet > 0 && isFinished) {
      badgeActive.innerHTML = `
        <span class="inline-flex items-center gap-1.5 rounded-full border border-cyan-500/40 bg-cyan-500/20 px-3 py-1 text-xs font-black text-cyan-300">
          ✓ SET ${currentSet} TELAH SELESAI
        </span>
      `;
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
                class="flex items-center gap-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-black px-3.5 py-1.5 text-xs shadow-lg shadow-emerald-600/30 active:scale-95 transition">
          ✓ Selesai Set ${currentSet}
        </button>
      `;
    } else if (currentSet > 0 && isFinished) {
      wrapperTop.innerHTML = `
        <button type="button"
                onclick="reopenSet(${matchId})"
                id="btn-top-reopen-${matchId}"
                class="flex items-center gap-1.5 rounded-xl border border-slate-700 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold px-3 py-1.5 text-xs active:scale-95 transition">
          ↺ Koreksi Set ${currentSet}
        </button>
      `;
    } else {
      wrapperTop.innerHTML = '';
    }
  }

  // 3. Update Tombol Flow Set (Mulai Set 1, Mulai Set 2, Mulai Set 3)
  const b1 = document.getElementById(`btn-set-${matchId}-1`);
  const b2 = document.getElementById(`btn-set-${matchId}-2`);
  const b3 = document.getElementById(`btn-set-${matchId}-3`);

  // Logika kelayakan tombol:
  // Mulai Set 2: HANYA jika Set 1 sudah SELESAI (isFinished && currentSet === 1) atau sedang di Set 2
  const canStartSet2 = (currentSet === 1 && isFinished) || currentSet === 2;
  // Mulai Set 3: HANYA jika Set 2 sudah SELESAI dan Rubber dibutuhkan
  const canStartSet3 = state.nextSet === 3 || currentSet === 3 || (currentSet === 2 && isFinished && state.scoringSystem === 'rubber_set');

  if (b1) {
    if (currentSet === 1) {
      b1.className = 'btn-flow-set flex items-center gap-1.5 rounded-xl px-3.5 py-1.5 text-xs transition active:scale-95 bg-emerald-600 border border-emerald-500 text-white font-black shadow-lg shadow-emerald-600/30 ring-2 ring-emerald-400/40';
    } else {
      b1.className = 'btn-flow-set flex items-center gap-1.5 rounded-xl px-3.5 py-1.5 text-xs transition active:scale-95 bg-slate-800/90 border border-slate-700 text-slate-300 font-bold hover:bg-slate-700 hover:text-white';
    }
  }

  if (b2) {
    if (currentSet === 2) {
      b2.disabled = false;
      b2.className = 'btn-flow-set flex items-center gap-1.5 rounded-xl px-3.5 py-1.5 text-xs transition active:scale-95 bg-emerald-600 border border-emerald-500 text-white font-black shadow-lg shadow-emerald-600/30 ring-2 ring-emerald-400/40';
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
    if (currentSet === 1 && !isFinished) {
      helperMsg.innerHTML = '<span class="text-emerald-400 font-semibold">Set 1 Aktif:</span> Tombol "Mulai Set 2" dikunci. Wasit wajib menekan tombol <b>"✓ Selesai Set 1"</b> setelah Set 1 berakhir agar tombol Set 2 terbuka.';
    } else if (currentSet === 1 && isFinished) {
      helperMsg.innerHTML = '<span class="text-cyan-300 font-semibold">Set 1 Selesai:</span> Tombol <b>"▶ Mulai Set 2"</b> kini terbuka! Silakan klik untuk melanjutkan ke Set 2.';
    } else if (currentSet === 2 && !isFinished) {
      helperMsg.innerHTML = '<span class="text-emerald-400 font-semibold">Set 2 Aktif:</span> Tekan tombol <b>"✓ Selesai Set 2"</b> setelah set kedua berakhir.';
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

  // 5. Update Kotak Set (Boxes), Badges, Tombol Skor, dan Footer Box
  for (let s = 1; s <= 3; s++) {
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
            ✓ Selesai Set ${s}
          </button>
        `;
      } else if (isThisActive && isFinished) {
        footer.innerHTML = `
          <span class="text-[11px] text-cyan-300 font-bold">✓ Set ${s} Telah Selesai</span>
          <button type="button"
                  onclick="reopenSet(${matchId})"
                  class="text-xs text-slate-400 hover:text-white underline">
            ↺ Koreksi Skor
          </button>
        `;
      } else if (s < currentSet) {
        footer.innerHTML = `
          <span class="text-[11px] text-slate-400 font-medium">✓ Set ${s} Selesai</span>
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
  if (prevEl && state.winnerPreview) {
    if (state.scoringSystem === 'poin_lanjut') {
      prevEl.textContent = `Set 1 (Tengah: ${state.set1_a}-${state.set1_b}) ➔ Set 2 (Akumulasi Akhir: ${state.set2_a}-${state.set2_b}) · [${state.winnerPreview}]`;
    } else if (state.set1_a !== undefined) {
      prevEl.textContent = `Set 1 (${state.set1_a}-${state.set1_b}), Set 2 (${state.set2_a}-${state.set2_b}), Set 3 (${state.set3_a}-${state.set3_b}) · [${state.winnerPreview}]`;
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
      if (data.scoring_system === 'poin_lanjut') {
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
</script>

