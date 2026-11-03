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
        $system = $m['scoring_system'] ?? 'rubber_set';
        $isRubber = ($system === 'rubber_set');
        $s2aVal = (!$isRubber && (int)$m['set2_a'] === 0 && (int)$m['set1_a'] > 0) ? (int)$m['set1_a'] : (int)$m['set2_a'];
        $s2bVal = (!$isRubber && (int)$m['set2_b'] === 0 && (int)$m['set1_b'] > 0) ? (int)$m['set1_b'] : (int)$m['set2_b'];
        $m1m = parse_team_members($m['team1_members'] ?? null);
        $m2m = parse_team_members($m['team2_members'] ?? null);
      ?>
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

          <!-- Active Set Indicator Badge (International TV Style) -->
          <div id="live-chyron-<?= $m['id'] ?>" class="flex items-center gap-2">
            <span class="rounded-xl border border-blue-500/30 bg-blue-950/40 px-2.5 py-1 text-[11px] font-bold text-blue-300 hidden sm:inline-block">
              <?= $isRubber ? 'Rubber Set' : 'Poin Lanjut' ?>
            </span>

            <?php if ($currSet > 0 && !$isSetFin): ?>
              <span class="inline-flex items-center gap-1.5 rounded-full border border-red-500/60 bg-red-500/20 px-3.5 py-1 text-xs font-black text-red-300 animate-pulse shadow-md shadow-red-500/20">
                <span class="h-2 w-2 rounded-full bg-red-400 animate-ping"></span>
                SET <?= $currSet ?><?= ($currSet === 3) ? ' (RUBBER GAME)' : '' ?> BERLANGSUNG
              </span>
            <?php elseif ($currSet > 0 && $isSetFin): ?>
              <span class="inline-flex items-center gap-1.5 rounded-full border border-cyan-500/60 bg-cyan-500/20 px-3.5 py-1 text-xs font-black text-cyan-300">
                ⏸️ SET <?= $currSet ?> SELESAI
              </span>
            <?php else: ?>
              <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-500/60 bg-amber-500/20 px-3.5 py-1 text-xs font-bold text-amber-300">
                <span class="h-2 w-2 rounded-full bg-amber-400 animate-ping"></span>
                LIVE SCORE
              </span>
            <?php endif; ?>
          </div>
        </div>

        <!-- TV Scoreboard Display (BWF Broadcast Table) -->
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
                        <?php if (!empty($m1m)): ?>
                          <div class="text-[11px] text-slate-400 truncate">
                            <?= e(implode(' / ', $m1m)) ?>
                          </div>
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
                        <?php if (!empty($m2m)): ?>
                          <div class="text-[11px] text-slate-400 truncate">
                            <?= e(implode(' / ', $m2m)) ?>
                          </div>
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
              <span class="text-emerald-400 font-semibold">Live Polling 1s</span>
              <span>·</span>
              <span>Jadwal: <?= e(fmt_dt($m['scheduled_at'])) ?></span>
            </div>
            <div class="font-bold text-slate-300">
              <?= $isRubber ? 'Rubber Game (Mencari 2 Kemenangan Set)' : 'Poin Lanjut (Akumulasi 2 Set Penuh)' ?>
            </div>
          </div>
        </div>

      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

