<?php
/**
 * Konten dinamis tab Live Score (untuk render awal dan AJAX polling re-render).
 * @var array $liveMatches
 * @var string $sport
 */
$isGroup = (\App\Models\Sports::system($sport) === 'group');
?>
<?php if (empty($liveMatches)): ?>
  <div id="live-empty-state" class="flex flex-col items-center justify-center rounded-3xl border border-dashed border-slate-800 bg-slate-900/50 p-12 text-center shadow-inner">
    <span class="text-5xl">🏸</span>
    <h3 class="mt-4 text-lg font-bold text-white">Tidak Ada Pertandingan yang Sedang Berlangsung</h3>
    <p class="mt-1 max-w-md text-xs sm:text-sm text-slate-400">
      Belum ada pertandingan aktif saat ini. Anda dapat memeriksa jadwal pada bagan pertandingan atau melihat riwayat hasil pertandingan sebelumnya.
    </p>
    <div class="mt-6 flex flex-wrap gap-3">
      <button type="button" onclick="switchTab('bracket')" class="rounded-xl bg-blue-600 px-4 py-2 text-xs font-bold text-white hover:bg-blue-500 transition">
        <?= $isGroup ? 'Lihat Klasemen &amp; Jadwal' : 'Buka Bagan Pertandingan' ?>
      </button>
      <button type="button" onclick="switchTab('hasil')" class="rounded-xl border border-slate-700 bg-slate-800 px-4 py-2 text-xs font-semibold text-slate-200 hover:bg-slate-700 transition">
        Lihat Hasil Selesai
      </button>
    </div>
  </div>
<?php else: ?>
  <div id="live-matches-grid" class="grid grid-cols-1 gap-6 xl:grid-cols-2">
    <?php foreach ($liveMatches as $m): ?>
      <?php
        $currSet = (int)($m['current_set'] ?? 0);
        $isSetFin = (bool)($m['is_set_finished'] ?? false);
        $isFutsal = (($m['sport_type'] ?? $sport) === 'futsal');
        $system = $m['scoring_system'] ?? 'rubber_set';
        $isRubber = ($system === 'rubber_set');
        $s2aVal = (!$isFutsal && !$isRubber && (int)$m['set2_a'] === 0 && (int)$m['set1_a'] > 0) ? (int)$m['set1_a'] : (int)$m['set2_a'];
        $s2bVal = (!$isFutsal && !$isRubber && (int)$m['set2_b'] === 0 && (int)$m['set1_b'] > 0) ? (int)$m['set1_b'] : (int)$m['set2_b'];
        $m1m = parse_team_members($m['team1_members'] ?? null);
        $m2m = parse_team_members($m['team2_members'] ?? null);
        $mHl = $highlights[$m['id']] ?? [];
      ?>
      <?php if ($isFutsal): ?>
        <!-- ======================================================== -->
        <!-- CARD LIVE FUTSAL: FOOTBALL MANAGER STYLE                 -->
        <!-- ======================================================== -->
        <?php
          $t1Initials = strtoupper(mb_substr(trim($m['team1_name'] ?? 'T1'), 0, 2));
          $t2Initials = strtoupper(mb_substr(trim($m['team2_name'] ?? 'T2'), 0, 2));

          // Kalkulasi waktu awal agar tidak pernah berkedip atau menampilkan 00:00 saat re-render
          $dur = (int)($m['futsal_half_duration'] ?? 5);
          $maxSeconds = $dur * 120;
          $elapsed = (int)($m['futsal_elapsed_seconds'] ?? 0);
          $timerStatus = $m['futsal_timer_status'] ?? 'stopped';
          $isHalfTime = ($currSet === 1 && $isSetFin);
          $isFullTime = ($currSet === 2 && $isSetFin);

          if ($isFullTime) {
              $elapsed = $maxSeconds; // waktu otomatis menjadi batas waktu maksimal yang ditentukan admin
          } elseif ($isHalfTime) {
              $elapsed = $dur * 60;
          } elseif ($timerStatus === 'running' && !empty($m['futsal_timer_updated_at'])) {
              $diff = time() - strtotime($m['futsal_timer_updated_at']);
              if ($diff > 0) {
                  $elapsed = min($maxSeconds, $elapsed + $diff);
              }
          }
          $clockMins = (int)floor($elapsed / 60);
          $clockSecs = (int)($elapsed % 60);
          $clockFormatted = sprintf('%02d:%02d', $clockMins, $clockSecs);
        ?>
        <div id="live-card-<?= $m['id'] ?>" class="live-match-card relative overflow-hidden rounded-3xl border-2 border-emerald-500/50 bg-gradient-to-b from-[#0f172a] via-[#090d16] to-[#040711] shadow-2xl shadow-emerald-950/40 transition-all">
          
          <!-- FM Stadium Matchday Top Bar -->
          <div class="flex flex-wrap items-center justify-between gap-2.5 bg-gradient-to-r from-slate-900 via-slate-900/95 to-slate-950 px-5 py-3 border-b border-slate-800">
            <div class="flex items-center gap-2.5">
              <span class="text-xl">⚽</span>
              <div>
                <div class="text-xs sm:text-sm font-black text-white uppercase tracking-wider flex items-center gap-2">
                  <span><?= !empty($m['court']) ? e($m['court']) : 'Lapangan Futsal' ?></span>
                  <?php if (!empty($m['category']) && $m['category'] !== 'umum'): ?>
                    <span class="rounded bg-emerald-950/80 border border-emerald-500/40 px-2 py-0.5 text-[10px] text-emerald-300 font-bold">
                      <?= e(category_label($m['category'])) ?>
                    </span>
                  <?php endif; ?>
                </div>
                <div class="text-[11px] text-slate-400">
                  <?= e(match_stage_label($m, 0)) ?>
                </div>
              </div>
            </div>

            <!-- FM Matchday Badge & Fullscreen Button -->
            <div class="flex items-center gap-2">
              <div id="live-chyron-<?= $m['id'] ?>" class="flex items-center gap-2">
                <?php if ($isHalfTime): ?>
                  <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-500/60 bg-amber-500/20 px-3.5 py-1 text-xs font-black text-amber-300 shadow-md shadow-amber-500/20">
                    ⏸️ HALF TIME (ISTIRAHAT)
                  </span>
                <?php elseif ($currSet > 0 && !$isSetFin): ?>
                  <span class="inline-flex items-center gap-1.5 rounded-full border border-red-500/60 bg-red-500/20 px-3.5 py-1 text-xs font-black text-red-300 animate-pulse shadow-md shadow-red-500/20">
                    <span class="h-2 w-2 rounded-full bg-red-400 animate-ping"></span>
                    BABAK <?= $currSet ?> BERLANGSUNG
                  </span>
                <?php elseif ($isFullTime): ?>
                  <span class="inline-flex items-center gap-1.5 rounded-full border border-cyan-500/60 bg-cyan-500/20 px-3.5 py-1 text-xs font-black text-cyan-300 shadow-md shadow-cyan-500/20">
                    ✓ FULL TIME
                  </span>
                <?php else: ?>
                  <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-500/60 bg-amber-500/20 px-3.5 py-1 text-xs font-bold text-amber-300">
                    <span class="h-2 w-2 rounded-full bg-amber-400 animate-ping"></span>
                    LIVE SCORE
                  </span>
                <?php endif; ?>
              </div>
              <button type="button"
                      onclick="toggleFullScreenCard('live-card-<?= $m['id'] ?>')"
                      data-card-id="live-card-<?= $m['id'] ?>"
                      class="card-fs-btn flex h-7 w-7 items-center justify-center rounded-lg border border-slate-700/80 bg-slate-800/60 text-slate-300 hover:bg-slate-700 hover:text-white transition shadow"
                      title="Layar Penuh Pertandingan Ini">
                <span class="text-xs">⛶</span>
              </button>
            </div>
          </div>

          <!-- FM Scoreboard Header (Tim A | SKOR & TIMER TERPISAH | Tim B) -->
          <div class="p-5 sm:p-7">
            <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-3 sm:gap-6">
              
              <!-- TIM A (Kiri) - Tanpa Anggota di Bawah Nama Tim -->
              <div class="flex items-center gap-3 sm:gap-4 justify-start min-w-0">
                <div class="flex h-12 w-12 sm:h-16 sm:w-16 shrink-0 items-center justify-center rounded-2xl sm:rounded-3xl bg-gradient-to-br from-blue-600 via-indigo-600 to-slate-900 border-2 border-blue-400/50 shadow-xl shadow-blue-600/20 text-white font-black text-base sm:text-xl select-none">
                  <?= e($t1Initials) ?>
                </div>
                <div class="min-w-0 flex-1">
                  <div class="text-sm sm:text-xl font-black text-white tracking-tight leading-tight truncate" title="<?= e($m['team1_name'] ?? 'Tim 1') ?>">
                    <?= e($m['team1_name'] ?? 'Tim 1') ?>
                  </div>
                </div>
              </div>

              <!-- SKOR & TIMER TERPISAH (Tengah) -->
              <div class="flex flex-col items-center justify-center px-1 text-center">
                
                <!-- 1. Keterangan Babak ke Berapa (Terpisah di Atas Skor) -->
                <div class="mb-2">
                  <span id="public-babak-<?= $m['id'] ?>"
                        class="inline-flex items-center gap-1.5 rounded-full px-3 py-0.5 text-[11px] sm:text-xs font-black uppercase tracking-wider <?= $isHalfTime ? 'bg-amber-500/20 border border-amber-500/50 text-amber-300 shadow-sm shadow-amber-500/20' : ($isFullTime ? 'bg-cyan-500/20 border border-cyan-500/50 text-cyan-300 shadow-sm shadow-cyan-500/20' : 'bg-slate-800/90 border border-slate-700 text-emerald-400 shadow-sm') ?>">
                    <span id="public-dot-<?= $m['id'] ?>" class="h-2 w-2 rounded-full <?= $isHalfTime ? 'bg-amber-400' : ($isFullTime ? 'bg-cyan-400' : 'bg-emerald-400') ?> <?= (!$isHalfTime && !$isFullTime && (($m['futsal_timer_status'] ?? 'stopped') === 'running')) ? 'animate-ping' : '' ?>"></span>
                    <span id="public-babak-text-<?= $m['id'] ?>"><?= $isHalfTime ? 'HALF TIME' : ($isFullTime ? 'FULL TIME' : (($currSet === 2) ? 'BABAK 2' : 'BABAK 1')) ?></span>
                  </span>
                </div>

                <!-- 2. Angka Skor Besar Utama -->
                <div class="flex items-center gap-2 sm:gap-4">
                  <span id="live-score1-<?= $m['id'] ?>" class="font-mono text-4xl sm:text-6xl font-black text-white drop-shadow-lg">
                    <?= (int)$m['score1'] ?>
                  </span>
                  <span class="text-2xl sm:text-4xl font-black text-slate-600 select-none">:</span>
                  <span id="live-score2-<?= $m['id'] ?>" class="font-mono text-4xl sm:text-6xl font-black text-white drop-shadow-lg">
                    <?= (int)$m['score2'] ?>
                  </span>
                </div>

                <!-- 3. Stopwatch Timer Berdiri Sendiri (Terpisah di Bawah Skor) -->
                <div class="mt-2.5">
                  <div class="inline-flex items-center gap-2 rounded-2xl bg-slate-950/95 border-2 <?= $isHalfTime ? 'border-amber-500/50 shadow-amber-500/20' : ($isFullTime ? 'border-cyan-500/50 shadow-cyan-500/20' : 'border-emerald-500/50 shadow-emerald-500/20') ?> px-4 py-1.5 shadow-xl">
                    <span class="text-xs">⏱️</span>
                    <span id="public-clock-<?= $m['id'] ?>"
                          class="public-futsal-clock font-mono text-base sm:text-2xl font-black <?= $isHalfTime ? 'text-amber-300' : ($isFullTime ? 'text-cyan-300' : 'text-emerald-400') ?> tracking-widest"
                          data-match-id="<?= (int)$m['id'] ?>"
                          data-half-duration="<?= $dur ?>"
                          data-elapsed="<?= $elapsed ?>"
                          data-status="<?= $isFullTime ? 'stopped' : e($timerStatus) ?>"
                          data-updated-at="<?= e($m['futsal_timer_updated_at'] ?? '') ?>">
                      <?= $clockFormatted ?>
                    </span>
                  </div>
                </div>

              </div>

              <!-- TIM B (Kanan) - Tanpa Anggota di Bawah Nama Tim -->
              <div class="flex items-center gap-3 sm:gap-4 justify-end text-right min-w-0">
                <div class="min-w-0 flex-1">
                  <div class="text-sm sm:text-xl font-black text-white tracking-tight leading-tight truncate" title="<?= e($m['team2_name'] ?? 'Tim 2') ?>">
                    <?= e($m['team2_name'] ?? 'Tim 2') ?>
                  </div>
                </div>
                <div class="flex h-12 w-12 sm:h-16 sm:w-16 shrink-0 items-center justify-center rounded-2xl sm:rounded-3xl bg-gradient-to-br from-rose-600 via-orange-600 to-slate-900 border-2 border-rose-400/50 shadow-xl shadow-rose-600/20 text-white font-black text-base sm:text-xl select-none">
                  <?= e($t2Initials) ?>
                </div>
              </div>

            </div>
          </div>

          <!-- FOOTBALL MANAGER VERTICAL TIMELINE -->
          <div class="border-t border-slate-800/90 bg-[#060a14]/95 p-4 sm:p-6">
            <!-- Timeline Header (Tim 1 | MATCH TIMELINE | Tim 2) - Fixed Equal Width agar MATCH TIMELINE Tepat di Tengah -->
            <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-2 sm:gap-4 mb-4 pb-2 border-b border-slate-800/70">
              <div class="min-w-0 flex items-center gap-1.5 text-xs font-black uppercase tracking-wider text-blue-400">
                <span class="shrink-0 text-[10px]">◀</span>
                <span class="truncate" title="<?= e($m['team1_name'] ?? 'Tim A') ?>"><?= e($m['team1_name'] ?? 'Tim A') ?></span>
              </div>
              <div class="shrink-0 px-2 sm:px-4 text-center">
                <span class="text-[10px] sm:text-[11px] font-black uppercase tracking-widest text-slate-400 whitespace-nowrap">
                  MATCH TIMELINE
                </span>
              </div>
              <div class="min-w-0 flex items-center justify-end gap-1.5 text-xs font-black uppercase tracking-wider text-rose-400 text-right">
                <span class="truncate" title="<?= e($m['team2_name'] ?? 'Tim B') ?>"><?= e($m['team2_name'] ?? 'Tim B') ?></span>
                <span class="shrink-0 text-[10px]">▶</span>
              </div>
            </div>

            <!-- Timeline Vertikal (Garis Tengah & Pembagian Kiri/Kanan) -->
            <div class="relative py-2">
              <!-- Garis Vertikal Tengah -->
              <div class="absolute left-1/2 top-0 bottom-0 -translate-x-1/2 w-0.5 bg-gradient-to-b from-emerald-500/60 via-slate-700 to-slate-800/80"></div>

              <!-- State Kosong jika Belum Ada Highlight -->
              <div id="live-timeline-empty-<?= $m['id'] ?>" class="py-6 text-center text-xs text-slate-500 italic relative z-10 <?= !empty($mHl) ? 'hidden' : '' ?>">
                Belum ada kejadian tercatat. Pertandingan sedang berlangsung!
              </div>

              <!-- Daftar Highlights Stabil (Tanpa Animasi Ulang untuk Item yang Sudah Ada) -->
              <div id="live-timeline-list-<?= $m['id'] ?>" class="space-y-3 relative z-10" data-team1-id="<?= (int)$m['team1_id'] ?>">
                <?php foreach ($mHl as $h): ?>
                  <?php
                    $isTeamA = ((int)$h['team_id'] === (int)$m['team1_id']);
                    $evtInfo = \App\Models\MatchHighlight::EVENTS[$h['event_type']] ?? \App\Models\MatchHighlight::EVENTS['goal'];
                  ?>
                  <?php if ($h['event_type'] === 'half_time' || $h['event_type'] === 'full_time'): ?>
                    <?php $isHt = ($h['event_type'] === 'half_time'); ?>
                    <div id="fm-event-item-<?= $h['id'] ?>" data-hl-id="<?= (int)$h['id'] ?>" class="flex items-center justify-center my-3 transition-all duration-300">
                      <div class="inline-flex items-center gap-2 rounded-full border <?= $isHt ? 'border-amber-500/60 bg-amber-950/80 text-amber-300 shadow-amber-500/20' : 'border-cyan-500/60 bg-cyan-950/80 text-cyan-300 shadow-cyan-500/20' ?> px-4 py-1 text-xs font-black shadow-lg">
                        <span class="text-sm"><?= $isHt ? '⏸️' : '🏁' ?></span>
                        <span><?= $isHt ? 'HALF TIME' : 'FULL TIME' ?></span>
                        <span class="font-mono text-[11px] opacity-80">(<?= e($h['minute']) ?>')</span>
                      </div>
                    </div>
                  <?php else: ?>
                    <div id="fm-event-item-<?= $h['id'] ?>" data-hl-id="<?= (int)$h['id'] ?>" class="grid grid-cols-[1fr_auto_1fr] items-center gap-2 sm:gap-4 transition-all duration-300">
                      
                      <!-- SISI TIM A (KIRI) -->
                      <?php if ($isTeamA): ?>
                        <div class="justify-self-end text-right min-w-0 max-w-full">
                          <div class="inline-flex items-center gap-2 rounded-xl border <?= $evtInfo['badge'] ?> px-3 py-1.5 text-xs shadow-md shadow-black/40 backdrop-blur-sm transition hover:scale-[1.02]">
                            <div class="min-w-0">
                              <?php if ($h['event_type'] === 'substitution' && !empty($h['player_out']) && !empty($h['player_in'])): ?>
                                <div class="text-slate-300 text-[11px] font-semibold leading-tight">
                                  <span class="text-rose-300 font-bold"><?= e($h['player_out']) ?></span> ➔ <span class="text-emerald-300 font-bold"><?= e($h['player_in']) ?></span>
                                </div>
                              <?php else: ?>
                                <div class="font-black text-white truncate max-w-[130px] sm:max-w-[200px] leading-tight">
                                  <?= e($h['player_name']) ?>
                                </div>
                              <?php endif; ?>
                              <div class="text-[9px] uppercase tracking-wider opacity-80 font-bold mt-0.5">
                                <?= $evtInfo['label'] ?>
                              </div>
                            </div>
                            <span class="text-base shrink-0"><?= $evtInfo['emoji'] ?></span>
                          </div>
                        </div>
                      <?php else: ?>
                        <div></div>
                      <?php endif; ?>

                      <!-- PIN TITIK TENGAH (MENIT KEJADIAN) -->
                      <div class="flex flex-col items-center justify-center shrink-0">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-slate-900 border-2 border-emerald-500/70 font-mono text-[10px] font-black text-emerald-300 shadow-lg shadow-black/90 ring-2 ring-emerald-500/20">
                          <?= e(\App\Models\MatchHighlight::formatMinute($h['minute'] ?? '1', (int)($m['futsal_half_duration'] ?? 5), (int)($h['period'] ?? 1))) ?>'
                        </span>
                      </div>

                      <!-- SISI TIM B (KANAN) -->
                      <?php if (!$isTeamA): ?>
                        <div class="justify-self-start text-left min-w-0 max-w-full">
                          <div class="inline-flex items-center gap-2 rounded-xl border <?= $evtInfo['badge'] ?> px-3 py-1.5 text-xs shadow-md shadow-black/40 backdrop-blur-sm transition hover:scale-[1.02]">
                            <span class="text-base shrink-0"><?= $evtInfo['emoji'] ?></span>
                            <div class="min-w-0">
                              <?php if ($h['event_type'] === 'substitution' && !empty($h['player_out']) && !empty($h['player_in'])): ?>
                                <div class="text-slate-300 text-[11px] font-semibold leading-tight">
                                  <span class="text-rose-300 font-bold"><?= e($h['player_out']) ?></span> ➔ <span class="text-emerald-300 font-bold"><?= e($h['player_in']) ?></span>
                                </div>
                              <?php else: ?>
                                <div class="font-black text-white truncate max-w-[130px] sm:max-w-[200px] leading-tight">
                                  <?= e($h['player_name']) ?>
                                </div>
                              <?php endif; ?>
                              <div class="text-[9px] uppercase tracking-wider opacity-80 font-bold mt-0.5">
                                <?= $evtInfo['label'] ?>
                              </div>
                            </div>
                          </div>
                        </div>
                      <?php else: ?>
                        <div></div>
                      <?php endif; ?>

                    </div>
                  <?php endif; ?>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- Footer Bar -->
            <div class="mt-4 pt-3 border-t border-slate-800/80 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-400">
              <div class="flex items-center gap-2">
                <span class="inline-flex h-2 w-2 rounded-full bg-emerald-500 animate-ping"></span>
                <span class="text-emerald-400 font-semibold">Live Real-Time Polling</span>
                <span>·</span>
                <span>Jadwal: <?= e(fmt_dt($m['scheduled_at'])) ?></span>
              </div>
              <?php
                $isPlayoff = (($m['stage'] ?? '') === 'knockout' || ($m['stage'] ?? '') === 'playoff' || in_array($m['bracket_side'] ?? '', ['final', 'third_place'], true));
              ?>
              <div class="inline-flex items-center gap-1.5 font-bold <?= $isPlayoff ? 'text-amber-300' : 'text-emerald-400' ?>">
                <span><?= $isPlayoff ? '🏆' : '⚽' ?></span>
                <span><?= $isPlayoff ? 'Playoff' : 'Grup' ?></span>
              </div>
            </div>
          </div>

        </div>

      <?php else: ?>
        <!-- ======================================================== -->
        <!-- CARD LIVE NON-FUTSAL: BADMINTON / PINGPONG (MULTI-SET)   -->
        <!-- ======================================================== -->
        <div id="live-card-<?= $m['id'] ?>" class="live-match-card relative overflow-hidden rounded-3xl border-2 border-red-500/50 bg-[#0c121e] shadow-2xl shadow-red-500/10 transition-all">
          
          <!-- TV Broadcast Chyron Top Bar -->
          <div class="flex flex-wrap items-center justify-between gap-2.5 bg-gradient-to-r from-slate-900 via-slate-900/95 to-slate-950 px-5 py-3 border-b border-slate-800">
            <div class="flex items-center gap-2.5">
              <span class="text-xl"><?= \App\Models\Sports::emoji($m['sport_type'] ?? $sport) ?></span>
              <div>
                <div class="text-xs sm:text-sm font-black text-white uppercase tracking-wider flex items-center gap-2">
                  <span><?= !empty($m['court']) ? e($m['court']) : 'Lapangan Pertandingan' ?></span>
                  <?php if (!empty($m['category']) && $m['category'] !== 'umum'): ?>
                    <span class="rounded bg-blue-950/80 border border-blue-500/40 px-2 py-0.5 text-[10px] text-blue-300 font-bold">
                      <?= e(category_label($m['category'])) ?>
                    </span>
                  <?php endif; ?>
                </div>
                <div class="text-[11px] text-slate-400">
                  <?= e(match_stage_label($m, 0)) ?>
                </div>
              </div>
            </div>

            <!-- Active Set Indicator Badge & Fullscreen Button -->
            <div class="flex items-center gap-2">
              <div id="live-chyron-<?= $m['id'] ?>" class="flex items-center gap-2">
                <span class="rounded-xl border border-blue-500/30 bg-blue-950/40 px-2.5 py-1 text-[11px] font-bold text-blue-300 hidden sm:inline-block">
                  <?= $isRubber ? 'Rubber Set' : 'Poin Lanjut' ?>
                </span>

                <?php if ($currSet > 0 && !$isSetFin): ?>
                  <span class="inline-flex items-center gap-1.5 rounded-full border border-red-500/60 bg-red-500/20 px-3.5 py-1 text-xs font-black text-red-300 animate-pulse shadow-md shadow-red-500/20">
                    <span class="h-2 w-2 rounded-full bg-red-400 animate-ping"></span>
                    <?= "SET $currSet" . (($currSet === 3) ? ' (RUBBER GAME)' : '') . " BERLANGSUNG" ?>
                  </span>
                <?php elseif ($currSet > 0 && $isSetFin): ?>
                  <span class="inline-flex items-center gap-1.5 rounded-full border border-cyan-500/60 bg-cyan-500/20 px-3.5 py-1 text-xs font-black text-cyan-300">
                    ⏸️ <?= "SET $currSet SELESAI" ?>
                  </span>
                <?php else: ?>
                  <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-500/60 bg-amber-500/20 px-3.5 py-1 text-xs font-bold text-amber-300">
                    <span class="h-2 w-2 rounded-full bg-amber-400 animate-ping"></span>
                    LIVE SCORE
                  </span>
                <?php endif; ?>
              </div>
              <button type="button"
                      onclick="toggleFullScreenCard('live-card-<?= $m['id'] ?>')"
                      data-card-id="live-card-<?= $m['id'] ?>"
                      class="card-fs-btn flex h-7 w-7 items-center justify-center rounded-lg border border-slate-700/80 bg-slate-800/60 text-slate-300 hover:bg-slate-700 hover:text-white transition shadow"
                      title="Layar Penuh Pertandingan Ini">
                <span class="text-xs">⛶</span>
              </button>
            </div>
          </div>

          <!-- TV Scoreboard Display (Broadcast Table) -->
          <div class="p-4 sm:p-6">
            <div class="overflow-x-auto">
              <table class="w-full text-left border-collapse">
                <thead>
                  <tr class="border-b border-slate-800 text-[11px] uppercase tracking-wider text-slate-400">
                    <th class="pb-3 font-extrabold">Tim Peserta</th>
                    <th class="pb-3 text-center w-20 font-extrabold <?= ($currSet === 1 && !$isSetFin) ? 'text-amber-300' : '' ?>" title="<?= !$isRubber ? 'Set 1 (Skor Tengah)' : 'Set 1' ?>">
                      Set 1 <?= ($currSet === 1 && !$isSetFin) ? '⚡' : '' ?>
                    </th>
                    <th class="pb-3 text-center w-20 font-extrabold <?= ($currSet === 2 && !$isSetFin) ? 'text-amber-300' : '' ?>" title="<?= !$isRubber ? 'Set 2 (Akumulasi Akhir)' : 'Set 2' ?>">
                      Set 2 <?= ($currSet === 2 && !$isSetFin) ? '⚡' : '' ?>
                    </th>
                    <?php if ($isRubber): ?>
                      <th class="pb-3 text-center w-20 font-extrabold <?= ($currSet === 3 && !$isSetFin) ? 'text-amber-300' : '' ?>" title="Set 3 (Rubber Game)">
                        Set 3 <?= ($currSet === 3 && !$isSetFin) ? '⚡' : '' ?>
                      </th>
                    <?php endif; ?>
                    <th class="pb-3 text-center w-24 font-extrabold text-blue-400" title="<?= $isRubber ? 'Total Set Menang' : 'Total Poin Kumulatif' ?>">
                      <?= $isRubber ? 'Set Won' : 'Total Poin' ?>
                    </th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                  <!-- Tim 1 Row -->
                  <tr class="group">
                    <td class="py-4 pr-3">
                      <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 sm:h-9 sm:w-9 shrink-0 items-center justify-center rounded-xl bg-blue-600/20 border border-blue-500/30 text-xs sm:text-sm font-black text-blue-400 shadow-inner">
                          1
                        </span>
                        <div class="min-w-0">
                          <div class="text-sm sm:text-lg font-black text-white tracking-tight line-clamp-1">
                            <?= $m['team1_name'] ? e($m['team1_name']) : 'TBD' ?>
                          </div>
                          <?php if (in_array(($m['sport_type'] ?? $sport), ['badminton', 'pingpong'], true) && !empty($m1m)): ?>
                            <div class="text-[11px] sm:text-xs font-medium text-slate-400 leading-snug"><?= e(implode(', ', $m1m)) ?></div>
                          <?php endif; ?>
                        </div>
                      </div>
                    </td>
                    <!-- Set 1 Tim 1 -->
                    <td class="py-4 text-center">
                      <span id="live-set1-a-<?= $m['id'] ?>" class="inline-block transition-all <?= ($currSet === 1 && !$isSetFin) ? 'font-mono text-3xl sm:text-4xl font-black text-amber-300 bg-amber-950/60 border-2 border-amber-500/60 rounded-xl px-3 py-1.5 shadow-lg shadow-amber-500/20 ring-2 ring-amber-400/30' : 'font-mono text-xl sm:text-2xl font-bold text-slate-300 bg-slate-950/80 rounded-xl px-3 py-1.5 border border-slate-800' ?>">
                        <?= (int)$m['set1_a'] ?>
                      </span>
                    </td>
                    <!-- Set 2 Tim 1 -->
                    <td class="py-4 text-center">
                      <span id="live-set2-a-<?= $m['id'] ?>" class="inline-block transition-all <?= ($currSet === 2 && !$isSetFin) ? 'font-mono text-3xl sm:text-4xl font-black text-amber-300 bg-amber-950/60 border-2 border-amber-500/60 rounded-xl px-3 py-1.5 shadow-lg shadow-amber-500/20 ring-2 ring-amber-400/30' : 'font-mono text-xl sm:text-2xl font-bold text-slate-300 bg-slate-950/80 rounded-xl px-3 py-1.5 border border-slate-800' ?>">
                        <?= $s2aVal ?>
                      </span>
                    </td>
                    <!-- Set 3 Tim 1 -->
                    <?php if ($isRubber): ?>
                      <td class="py-4 text-center">
                        <span id="live-set3-a-<?= $m['id'] ?>" class="inline-block transition-all <?= ($currSet === 3 && !$isSetFin) ? 'font-mono text-3xl sm:text-4xl font-black text-amber-300 bg-amber-950/60 border-2 border-amber-500/60 rounded-xl px-3 py-1.5 shadow-lg shadow-amber-500/20 ring-2 ring-amber-400/30' : 'font-mono text-xl sm:text-2xl font-bold text-slate-300 bg-slate-950/80 rounded-xl px-3 py-1.5 border border-slate-800' ?>">
                          <?= (int)$m['set3_a'] ?>
                        </span>
                      </td>
                    <?php endif; ?>
                    <!-- Total Score Tim 1 -->
                    <td class="py-4 text-center">
                      <span id="live-score1-<?= $m['id'] ?>" class="font-mono text-2xl sm:text-3xl font-black text-blue-400 drop-shadow">
                        <?= (int)$m['score1'] ?>
                      </span>
                    </td>
                  </tr>

                  <!-- Tim 2 Row -->
                  <tr class="group">
                    <td class="py-4 pr-3">
                      <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 sm:h-9 sm:w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-600/20 border border-indigo-500/30 text-xs sm:text-sm font-black text-indigo-400 shadow-inner">
                          2
                        </span>
                        <div class="min-w-0">
                          <div class="text-sm sm:text-lg font-black text-white tracking-tight line-clamp-1">
                            <?= $m['team2_name'] ? e($m['team2_name']) : 'TBD' ?>
                          </div>
                          <?php if (in_array(($m['sport_type'] ?? $sport), ['badminton', 'pingpong'], true) && !empty($m2m)): ?>
                            <div class="text-[11px] sm:text-xs font-medium text-slate-400 leading-snug"><?= e(implode(', ', $m2m)) ?></div>
                          <?php endif; ?>
                        </div>
                      </div>
                    </td>
                    <!-- Set 1 Tim 2 -->
                    <td class="py-4 text-center">
                      <span id="live-set1-b-<?= $m['id'] ?>" class="inline-block transition-all <?= ($currSet === 1 && !$isSetFin) ? 'font-mono text-3xl sm:text-4xl font-black text-amber-300 bg-amber-950/60 border-2 border-amber-500/60 rounded-xl px-3 py-1.5 shadow-lg shadow-amber-500/20 ring-2 ring-amber-400/30' : 'font-mono text-xl sm:text-2xl font-bold text-slate-300 bg-slate-950/80 rounded-xl px-3 py-1.5 border border-slate-800' ?>">
                        <?= (int)$m['set1_b'] ?>
                      </span>
                    </td>
                    <!-- Set 2 Tim 2 -->
                    <td class="py-4 text-center">
                      <span id="live-set2-b-<?= $m['id'] ?>" class="inline-block transition-all <?= ($currSet === 2 && !$isSetFin) ? 'font-mono text-3xl sm:text-4xl font-black text-amber-300 bg-amber-950/60 border-2 border-amber-500/60 rounded-xl px-3 py-1.5 shadow-lg shadow-amber-500/20 ring-2 ring-amber-400/30' : 'font-mono text-xl sm:text-2xl font-bold text-slate-300 bg-slate-950/80 rounded-xl px-3 py-1.5 border border-slate-800' ?>">
                        <?= $s2bVal ?>
                      </span>
                    </td>
                    <!-- Set 3 Tim 2 -->
                    <?php if ($isRubber): ?>
                      <td class="py-4 text-center">
                        <span id="live-set3-b-<?= $m['id'] ?>" class="inline-block transition-all <?= ($currSet === 3 && !$isSetFin) ? 'font-mono text-3xl sm:text-4xl font-black text-amber-300 bg-amber-950/60 border-2 border-amber-500/60 rounded-xl px-3 py-1.5 shadow-lg shadow-amber-500/20 ring-2 ring-amber-400/30' : 'font-mono text-xl sm:text-2xl font-bold text-slate-300 bg-slate-950/80 rounded-xl px-3 py-1.5 border border-slate-800' ?>">
                          <?= (int)$m['set3_b'] ?>
                        </span>
                      </td>
                    <?php endif; ?>
                    <!-- Total Score Tim 2 -->
                    <td class="py-4 text-center">
                      <span id="live-score2-<?= $m['id'] ?>" class="font-mono text-2xl sm:text-3xl font-black text-indigo-400 drop-shadow">
                        <?= (int)$m['score2'] ?>
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Chyron Footer Bar -->
            <div class="mt-4 pt-3.5 border-t border-slate-800/80 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-400">
              <div class="flex items-center gap-2">
                <span class="inline-flex h-2 w-2 rounded-full bg-emerald-500 animate-ping"></span>
                <span class="text-emerald-400 font-semibold">Live Score</span>
                <span>·</span>
                <span>Jadwal: <?= e(fmt_dt($m['scheduled_at'])) ?></span>
              </div>
              <div class="font-bold text-slate-300">
                <?= $isRubber ? 'Rubber Game (Mencari 2 Kemenangan Set)' : 'Poin Lanjut (Akumulasi 2 Set Penuh)' ?>
              </div>
            </div>
          </div>

        </div>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

