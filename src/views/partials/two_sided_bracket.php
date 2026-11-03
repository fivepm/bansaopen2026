<?php
/**
 * Visualisasi Bagan Kiri-Kanan (Two-Sided Bracket) gaya Grand Slam / E-Sports.
 * @var array $bracket array{totalRounds: int, left: array, right: array, final: ?array, thirdPlace: ?array}
 * @var string $catKey
 */
$R = (int)($bracket['totalRounds'] ?? 0);
$leftRounds = $bracket['left'] ?? [];
$rightRounds = $bracket['right'] ?? [];
$final = $bracket['final'] ?? null;
$thirdPlace = $bracket['thirdPlace'] ?? null;
?>

<?php if ($R === 0 || (empty($leftRounds) && empty($rightRounds) && empty($final))): ?>
  <div class="rounded-3xl border border-dashed border-slate-800 bg-slate-900/40 p-12 text-center text-slate-500">
    <span class="text-4xl">🌳</span>
    <p class="mt-3 text-sm">Bagan pertandingan untuk kategori ini belum dibuat oleh panitia.</p>
  </div>
<?php else: ?>
  <div class="bracket-scroll-container w-full overflow-x-auto rounded-3xl border border-slate-800/90 bg-[#090d16]/90 p-4 sm:p-8 shadow-2xl backdrop-blur-md">
    <div class="bracket-wrapper flex min-w-[1140px] items-stretch justify-between gap-6 py-4">

      <!-- ================= BLOK KIRI ================= -->
      <div class="flex flex-1 items-stretch justify-end gap-12">
        <?php for ($r = 1; $r < $R; $r++): $matches = $leftRounds[$r] ?? []; ?>
          <?php $isSemi = ($r === $R - 1); ?>
          <div class="bracket-col bracket-col-left flex min-w-[220px] max-w-[270px] flex-1 flex-col">
            <div class="mb-4 rounded-xl border border-slate-800 bg-slate-900/90 py-1.5 text-center shadow-sm">
              <h4 class="text-xs font-black uppercase tracking-wider text-blue-400">
                <?= e(round_name($r, $R)) ?> (Kiri)
              </h4>
            </div>
            <div class="flex flex-1 flex-col justify-around gap-6">
              <?php $pairs = array_chunk($matches, 2); ?>
              <?php foreach ($pairs as $pair): ?>
                <?php $isPair = (count($pair) === 2); ?>
                <div class="bracket-pair bracket-pair-left relative flex flex-col justify-around gap-8 flex-1">
                  <?php foreach ($pair as $idx => $m): ?>
                    <?php
                      $slotClass = $isSemi
                        ? 'bracket-slot-left bracket-slot-left-semi'
                        : ($isPair ? ($idx === 0 ? 'bracket-slot-left bracket-slot-top' : 'bracket-slot-left bracket-slot-bottom') : 'bracket-slot-left bracket-slot-single');
                    ?>
                    <div class="<?= $slotClass ?> relative">
                      <?php include dirname(__DIR__) . '/partials/match_card.php'; ?>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endfor; ?>
      </div>

      <!-- ================= PUSAT (TENGAH): FINAL & JUARA 3 ================= -->
      <div class="flex min-w-[280px] max-w-[320px] flex-col items-center justify-center px-4 py-4">
        
        <!-- Kartu Pertandingan Final -->
        <div class="grand-final-box relative w-full rounded-2xl border-2 border-amber-500/70 bg-gradient-to-b from-slate-900 to-slate-950 p-4 shadow-2xl shadow-amber-500/15 transition hover:border-amber-400">
          <div class="mb-3 flex items-center justify-between border-b border-amber-500/30 pb-2 text-xs">
            <span class="flex items-center gap-1.5 font-black uppercase tracking-wider text-amber-400">
              <span>🏆</span>
              <span>GRAND FINAL</span>
            </span>
            <?php if ($final): ?>
              <span class="badge <?= e($final['status']) ?>"><?= e(status_label($final['status'])) ?></span>
            <?php endif; ?>
          </div>

          <?php if ($final): $m = $final; ?>
            <?php include dirname(__DIR__) . '/partials/match_card.php'; ?>
            <?php if (!empty($final['winner_name'])): ?>
              <div class="mt-2.5 rounded-lg border border-amber-500/40 bg-amber-500/10 py-1.5 text-center text-xs font-extrabold text-amber-300">
                👑 JUARA 1: <?= e($final['winner_name']) ?>
              </div>
            <?php endif; ?>
          <?php else: ?>
            <div class="py-6 text-center text-xs text-slate-500 italic">Menunggu pemenang semifinal</div>
          <?php endif; ?>
        </div>

        <!-- Trophy Visual Divider -->
        <div class="my-4 flex items-center gap-2 text-slate-700">
          <span class="h-px w-10 bg-slate-800"></span>
          <span class="text-xs uppercase font-extrabold tracking-widest text-slate-500">Puncak Kejuaraan</span>
          <span class="h-px w-10 bg-slate-800"></span>
        </div>

        <!-- Kartu Perebutan Juara 3 (Third Place Playoff) -->
        <div class="w-full rounded-2xl border-2 border-orange-500/50 bg-gradient-to-b from-slate-900 to-slate-950 p-4 shadow-xl shadow-orange-500/10 transition hover:border-orange-400">
          <div class="mb-3 flex items-center justify-between border-b border-orange-500/30 pb-2 text-xs">
            <span class="flex items-center gap-1.5 font-black uppercase tracking-wider text-orange-400">
              <span>🥉</span>
              <span>PEREBUTAN JUARA 3</span>
            </span>
            <?php if ($thirdPlace): ?>
              <span class="badge <?= e($thirdPlace['status']) ?>"><?= e(status_label($thirdPlace['status'])) ?></span>
            <?php endif; ?>
          </div>

          <?php if ($thirdPlace): $m = $thirdPlace; ?>
            <?php include dirname(__DIR__) . '/partials/match_card.php'; ?>
            <?php if (!empty($thirdPlace['winner_name'])): ?>
              <div class="mt-2.5 rounded-lg border border-orange-500/40 bg-orange-500/10 py-1.5 text-center text-xs font-extrabold text-orange-300">
                🥉 JUARA 3: <?= e($thirdPlace['winner_name']) ?>
              </div>
            <?php endif; ?>
          <?php else: ?>
            <div class="py-6 text-center text-xs text-slate-500 italic">Menunggu peserta semifinal</div>
          <?php endif; ?>
        </div>

      </div>

      <!-- ================= BLOK KANAN ================= -->
      <!-- Mengalir mundur dari Semifinal Kanan menuju babak awal di sisi paling kanan -->
      <div class="flex flex-1 items-stretch justify-start gap-12">
        <?php for ($r = $R - 1; $r >= 1; $r--): $matches = $rightRounds[$r] ?? []; ?>
          <?php $isSemi = ($r === $R - 1); ?>
          <div class="bracket-col bracket-col-right flex min-w-[220px] max-w-[270px] flex-1 flex-col">
            <div class="mb-4 rounded-xl border border-slate-800 bg-slate-900/90 py-1.5 text-center shadow-sm">
              <h4 class="text-xs font-black uppercase tracking-wider text-indigo-400">
                <?= e(round_name($r, $R)) ?> (Kanan)
              </h4>
            </div>
            <div class="flex flex-1 flex-col justify-around gap-6">
              <?php $pairs = array_chunk($matches, 2); ?>
              <?php foreach ($pairs as $pair): ?>
                <?php $isPair = (count($pair) === 2); ?>
                <div class="bracket-pair bracket-pair-right relative flex flex-col justify-around gap-8 flex-1">
                  <?php foreach ($pair as $idx => $m): ?>
                    <?php
                      $slotClass = $isSemi
                        ? 'bracket-slot-right bracket-slot-right-semi'
                        : ($isPair ? ($idx === 0 ? 'bracket-slot-right bracket-slot-top' : 'bracket-slot-right bracket-slot-bottom') : 'bracket-slot-right bracket-slot-single');
                    ?>
                    <div class="<?= $slotClass ?> relative">
                      <?php include dirname(__DIR__) . '/partials/match_card.php'; ?>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endfor; ?>
      </div>

    </div>
  </div>
<?php endif; ?>
