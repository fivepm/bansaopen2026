<?php
/**
 * Konten dinamis tab Hasil Pertandingan (untuk render awal dan AJAX polling re-render).
 * @var array $finishedMatches
 * @var string $sport
 */
?>
<?php if (empty($finishedMatches)): ?>
  <div id="hasil-empty-state" class="flex flex-col items-center justify-center rounded-3xl border border-dashed border-slate-800 bg-slate-900/40 p-12 text-center text-slate-500 shadow-inner">
    <span class="text-4xl">📋</span>
    <p class="mt-3 text-sm">Belum ada pertandingan yang selesai pada cabang olahraga ini.</p>
  </div>
<?php else: ?>
  <div id="finished-matches-list" class="space-y-4">
    <?php foreach ($finishedMatches as $m): ?>
      <?php
        $w = !empty($m['winner_id']) ? (int)$m['winner_id'] : null;
        $isFutsal = (($m['sport_type'] ?? $sport) === 'futsal');
        $system = $m['scoring_system'] ?? 'rubber_set';
        $isRubber = ($system === 'rubber_set');
        $t1Win = ($w && $w == $m['team1_id']);
        $t2Win = ($w && $w == $m['team2_id']);
        $isDraw = ($m['score1'] === $m['score2']);

        $s1a = (int)($m['set1_a'] ?? 0);
        $s1b = (int)($m['set1_b'] ?? 0);
        $s2a = (int)($m['set2_a'] ?? 0);
        $s2b = (int)($m['set2_b'] ?? 0);
        $s3a = (int)($m['set3_a'] ?? 0);
        $s3b = (int)($m['set3_b'] ?? 0);

        $s1aWin = ($s1a > $s1b);
        $s1bWin = ($s1b > $s1a);
        $s2aWin = ($s2a > $s2b);
        $s2bWin = ($s2b > $s2a);
        $s3aWin = (!$isFutsal && $s3a > $s3b && ($s3a > 0 || $s3b > 0));
        $s3bWin = (!$isFutsal && $s3b > $s3a && ($s3a > 0 || $s3b > 0));
        $hadSet3 = (!$isFutsal && $isRubber && ($s3a > 0 || $s3b > 0));

        $m1m = parse_team_members($m['team1_members'] ?? null);
        $m2m = parse_team_members($m['team2_members'] ?? null);
        $mHl = $highlights[$m['id']] ?? [];
      ?>
      <div class="rounded-3xl border border-slate-800 bg-gradient-to-b from-slate-900/90 to-slate-950 p-5 sm:p-6 shadow-xl shadow-black/30">
        
        <!-- Header Hasil Pertandingan -->
        <div class="flex items-center justify-between border-b border-slate-800/80 pb-3 text-xs text-slate-400">
          <div class="flex items-center gap-2">
            <span class="font-extrabold uppercase tracking-wider text-slate-300">
              <?= !empty($m['court']) ? e($m['court']) . ' · ' : '' ?>
              <?= e(match_stage_label($m, 0)) ?>
            </span>
            <?php if (!empty($m['category']) && $m['category'] !== 'umum'): ?>
              <span class="rounded bg-slate-800 px-2 py-0.5 text-[10px] text-blue-400 font-semibold">
                <?= e(category_label($m['category'])) ?>
              </span>
            <?php endif; ?>
          </div>
          <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1 rounded-full border border-emerald-500/40 bg-emerald-500/15 px-3 py-0.5 text-[11px] font-black text-emerald-300">
              ✓ SELESAI
            </span>
            <?php if ($isFutsal): ?>
              <?php if ($isDraw): ?>
                <span class="inline-flex items-center gap-1 rounded-full border border-amber-500/40 bg-amber-500/20 px-2.5 py-0.5 text-[11px] font-black text-amber-300 shadow-sm shadow-amber-500/20">
                  ⚖️ SERI
                </span>
              <?php elseif (!empty($m['winner_name'])): ?>
                <span class="inline-flex items-center gap-1 rounded-full border border-emerald-500/40 bg-emerald-500/20 px-2.5 py-0.5 text-[11px] font-black text-emerald-300 shadow-sm shadow-emerald-500/20">
                  🏆 <?= e($m['winner_name']) ?> (MENANG)
                </span>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        </div>

        <!-- Rincian Tabel Skor Berjejer per Set / Babak -->
        <div class="my-4 overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="border-b border-slate-800/60 text-[10px] uppercase font-bold text-slate-500">
                <th class="pb-2">Tim &amp; Pemain</th>
                <th class="pb-2 text-center w-16" title="<?= $isFutsal ? 'Babak 1' : (!$isRubber ? 'Set 1 (Skor Titik Tengah)' : 'Set 1') ?>">
                  <?= $isFutsal ? 'Babak 1' : 'Set 1' ?>
                </th>
                <th class="pb-2 text-center w-16" title="<?= $isFutsal ? 'Babak 2' : (!$isRubber ? 'Set 2 (Akumulasi Akhir)' : 'Set 2') ?>">
                  <?= $isFutsal ? 'Babak 2' : 'Set 2' ?>
                </th>
                <?php if (!$isFutsal && $isRubber): ?>
                  <th class="pb-2 text-center w-16" title="Set 3 (Rubber Game)">Set 3</th>
                <?php endif; ?>
                <th class="pb-2 text-center w-20 text-emerald-400">
                  <?= $isFutsal ? 'Total Gol' : ($isRubber ? 'Set Akhir' : 'Total Poin') ?>
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/40">
              <!-- Tim 1 -->
              <tr class="<?= $t1Win ? 'bg-emerald-950/20' : '' ?>">
                <td class="py-3 pr-2">
                  <div class="flex items-center gap-2">
                    <?php if ($t1Win): ?>
                      <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-500/20 text-xs">🏆</span>
                    <?php endif; ?>
                    <div class="min-w-0">
                      <div class="text-sm sm:text-base font-bold <?= $t1Win ? 'text-emerald-300 font-black' : 'text-slate-300' ?> flex items-center gap-2 line-clamp-1">
                        <span><?= $m['team1_name'] ? e($m['team1_name']) : 'TBD' ?></span>
                        <?php if ($isFutsal): ?>
                          <?php if ($isDraw): ?>
                            <span class="rounded bg-amber-950/80 border border-amber-500/40 px-1.5 py-0.2 text-[9px] font-black text-amber-300">SERI</span>
                          <?php elseif ($t1Win): ?>
                            <span class="rounded bg-emerald-950/80 border border-emerald-500/40 px-1.5 py-0.2 text-[9px] font-black text-emerald-300">MENANG</span>
                          <?php else: ?>
                            <span class="rounded bg-rose-950/80 border border-rose-500/40 px-1.5 py-0.2 text-[9px] font-black text-rose-300">KALAH</span>
                          <?php endif; ?>
                        <?php endif; ?>
                      </div>
                      <?php if (!empty($m1m) && !$isFutsal): ?>
                        <div class="text-[11px] text-slate-400 font-normal truncate">
                          <?= e(implode(' / ', $m1m)) ?>
                        </div>
                      <?php endif; ?>
                    </div>
                  </div>
                </td>
                <td class="py-3 text-center">
                  <span class="inline-block font-mono text-sm sm:text-base rounded-lg px-2.5 py-0.5 <?= $s1aWin ? 'bg-emerald-950/80 text-emerald-300 font-black border border-emerald-500/40' : 'bg-slate-950 text-slate-400' ?>">
                    <?= $s1a ?>
                  </span>
                </td>
                <td class="py-3 text-center">
                  <span class="inline-block font-mono text-sm sm:text-base rounded-lg px-2.5 py-0.5 <?= $s2aWin ? 'bg-emerald-950/80 text-emerald-300 font-black border border-emerald-500/40' : 'bg-slate-950 text-slate-400' ?>">
                    <?= $s2a ?>
                  </span>
                </td>
                <?php if (!$isFutsal && $isRubber): ?>
                  <td class="py-3 text-center">
                    <?php if ($hadSet3): ?>
                      <span class="inline-block font-mono text-sm sm:text-base rounded-lg px-2.5 py-0.5 <?= $s3aWin ? 'bg-emerald-950/80 text-emerald-300 font-black border border-emerald-500/40' : 'bg-slate-950 text-slate-400' ?>">
                        <?= $s3a ?>
                      </span>
                    <?php else: ?>
                      <span class="text-slate-600 font-mono">-</span>
                    <?php endif; ?>
                  </td>
                <?php endif; ?>
                <td class="py-3 text-center">
                  <span class="font-mono text-lg sm:text-xl font-black <?= $t1Win ? 'text-emerald-400' : 'text-slate-400' ?>">
                    <?= (int)$m['score1'] ?>
                  </span>
                </td>
              </tr>

              <!-- Tim 2 -->
              <tr class="<?= $t2Win ? 'bg-emerald-950/20' : '' ?>">
                <td class="py-3 pr-2">
                  <div class="flex items-center gap-2">
                    <?php if ($t2Win): ?>
                      <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-500/20 text-xs">🏆</span>
                    <?php endif; ?>
                    <div class="min-w-0">
                      <div class="text-sm sm:text-base font-bold <?= $t2Win ? 'text-emerald-300 font-black' : 'text-slate-300' ?> flex items-center gap-2 line-clamp-1">
                        <span><?= $m['team2_name'] ? e($m['team2_name']) : 'TBD' ?></span>
                        <?php if ($isFutsal): ?>
                          <?php if ($isDraw): ?>
                            <span class="rounded bg-amber-950/80 border border-amber-500/40 px-1.5 py-0.2 text-[9px] font-black text-amber-300">SERI</span>
                          <?php elseif ($t2Win): ?>
                            <span class="rounded bg-emerald-950/80 border border-emerald-500/40 px-1.5 py-0.2 text-[9px] font-black text-emerald-300">MENANG</span>
                          <?php else: ?>
                            <span class="rounded bg-rose-950/80 border border-rose-500/40 px-1.5 py-0.2 text-[9px] font-black text-rose-300">KALAH</span>
                          <?php endif; ?>
                        <?php endif; ?>
                      </div>
                      <?php if (!empty($m2m) && !$isFutsal): ?>
                        <div class="text-[11px] text-slate-400 font-normal truncate">
                          <?= e(implode(' / ', $m2m)) ?>
                        </div>
                      <?php endif; ?>
                    </div>
                  </div>
                </td>
                <td class="py-3 text-center">
                  <span class="inline-block font-mono text-sm sm:text-base rounded-lg px-2.5 py-0.5 <?= $s1bWin ? 'bg-emerald-950/80 text-emerald-300 font-black border border-emerald-500/40' : 'bg-slate-950 text-slate-400' ?>">
                    <?= $s1b ?>
                  </span>
                </td>
                <td class="py-3 text-center">
                  <span class="inline-block font-mono text-sm sm:text-base rounded-lg px-2.5 py-0.5 <?= $s2bWin ? 'bg-emerald-950/80 text-emerald-300 font-black border border-emerald-500/40' : 'bg-slate-950 text-slate-400' ?>">
                    <?= $s2b ?>
                  </span>
                </td>
                <?php if (!$isFutsal && $isRubber): ?>
                  <td class="py-3 text-center">
                    <?php if ($hadSet3): ?>
                      <span class="inline-block font-mono text-sm sm:text-base rounded-lg px-2.5 py-0.5 <?= $s3bWin ? 'bg-emerald-950/80 text-emerald-300 font-black border border-emerald-500/40' : 'bg-slate-950 text-slate-400' ?>">
                        <?= $s3b ?>
                      </span>
                    <?php else: ?>
                      <span class="text-slate-600 font-mono">-</span>
                    <?php endif; ?>
                  </td>
                <?php endif; ?>
                <td class="py-3 text-center">
                  <span class="font-mono text-lg sm:text-xl font-black <?= $t2Win ? 'text-emerald-400' : 'text-slate-400' ?>">
                    <?= (int)$m['score2'] ?>
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Match Highlights Timeline jika ada -->
        <?php if (!empty($mHl)): ?>
          <div class="my-3 pt-3 border-t border-slate-800/60">
            <div class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-1.5 flex items-center gap-1.5">
              <span>⚡</span>
              <span>Catatan Kejadian (Match Highlights)</span>
            </div>
            <div class="flex flex-wrap gap-1.5">
              <?php foreach ($mHl as $h): ?>
                <?php
                  $evtInfo = \App\Models\MatchHighlight::EVENTS[$h['event_type']] ?? \App\Models\MatchHighlight::EVENTS['goal'];
                ?>
                <span class="inline-flex items-center gap-1.5 rounded-lg border <?= $evtInfo['badge'] ?> px-2.5 py-1 text-xs">
                  <span class="font-mono font-bold"><?= e(\App\Models\MatchHighlight::formatMinute($h['minute'] ?? '1', (int)($m['futsal_half_duration'] ?? 5), (int)($h['period'] ?? 1))) ?>'</span>
                  <span><?= $evtInfo['emoji'] ?></span>
                  <?php if ($h['event_type'] === 'substitution' && !empty($h['player_out']) && !empty($h['player_in'])): ?>
                    <span class="text-slate-300">
                      Keluar: <b class="text-rose-300"><?= e($h['player_out']) ?></b> ➔ Masuk: <b class="text-emerald-300"><?= e($h['player_in']) ?></b>
                    </span>
                  <?php else: ?>
                    <span class="font-bold text-white"><?= e($h['player_name']) ?></span>
                  <?php endif; ?>
                  <span class="text-[10px] opacity-75">(<?= e($h['team_name'] ?? '') ?>)</span>
                </span>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <!-- Footer Banner Pemenang (Teks jam/waktu selesai dihapus untuk Futsal) -->
        <div class="flex items-center justify-between border-t border-slate-800/80 pt-3 text-xs">
          <?php if (!$isFutsal): ?>
            <span class="text-slate-500">Selesai: <?= e(fmt_dt($m['updated_at'])) ?></span>
          <?php else: ?>
            <?php
              $isPlayoff = (($m['stage'] ?? '') === 'knockout' || ($m['stage'] ?? '') === 'playoff' || in_array($m['bracket_side'] ?? '', ['final', 'third_place'], true));
            ?>
            <span class="inline-flex items-center gap-1.5 font-bold <?= $isPlayoff ? 'text-amber-300' : 'text-emerald-400' ?>">
              <span><?= $isPlayoff ? '🏆' : '⚽' ?></span>
              <span><?= $isPlayoff ? 'Playoff' : 'Grup' ?></span>
            </span>
          <?php endif; ?>
          <?php if (!empty($m['winner_name'])): ?>
            <span class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-950/60 border border-emerald-500/30 px-3 py-1 font-black text-emerald-300">
              🏆 Pemenang: <?= e($m['winner_name']) ?>
            </span>
          <?php elseif ($isDraw): ?>
            <span class="font-bold text-amber-400">Hasil Seri</span>
          <?php endif; ?>
        </div>

      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

