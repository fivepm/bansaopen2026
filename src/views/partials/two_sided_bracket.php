<?php
/**
 * Visualisasi Bagan Kiri-Kanan (Two-Sided Bracket) gaya Grand Slam / E-Sports.
 * Dilengkapi dengan Podium Kejuaraan (Hall of Champions) dan Garis Penghubung SVG Presisi.
 * 
 * @var array $bracket array{totalRounds: int, left: array, right: array, final: ?array, thirdPlace: ?array}
 * @var string $catKey
 */
$R = (int)($bracket['totalRounds'] ?? 0);
$leftRounds = $bracket['left'] ?? [];
$rightRounds = $bracket['right'] ?? [];
$final = $bracket['final'] ?? null;
$thirdPlace = $bracket['thirdPlace'] ?? null;

// Ekstraksi data Podium Kejuaraan (Champion, 1st Runner Up, 2nd Runner Up)
$isFinalFinished = (!empty($final) && !empty($final['winner_id']) && $final['status'] === 'finished');
$isThirdFinished = (!empty($thirdPlace) && !empty($thirdPlace['winner_id']) && $thirdPlace['status'] === 'finished');

// 1. Champion (Juara 1)
$championName = $isFinalFinished ? ($final['winner_name'] ?? 'Pemenang Grand Final') : 'Pemenang Grand Final';
$championMembers = [];

// 2. 1st Runner Up (Juara 2) - Finalis yang kalah di Grand Final
$firstRunnerUpName = 'Runner-up Grand Final';
$firstRunnerUpMembers = [];
if ($isFinalFinished) {
    if ((int)$final['winner_id'] === (int)$final['team1_id']) {
        $championMembers = parse_team_members($final['team1_members'] ?? null);
        $firstRunnerUpName = $final['team2_name'] ?? 'Runner-up Grand Final';
        $firstRunnerUpMembers = parse_team_members($final['team2_members'] ?? null);
    } else {
        $championMembers = parse_team_members($final['team2_members'] ?? null);
        $firstRunnerUpName = $final['team1_name'] ?? 'Runner-up Grand Final';
        $firstRunnerUpMembers = parse_team_members($final['team1_members'] ?? null);
    }
}

// 3. 2nd Runner Up (Juara 3) - Pemenang pertandingan Perebutan Juara 3
$secondRunnerUpName = $isThirdFinished ? ($thirdPlace['winner_name'] ?? 'Pemenang Perebutan Juara 3') : 'Pemenang Perebutan Juara 3';
$secondRunnerUpMembers = [];
if ($isThirdFinished) {
    if ((int)$thirdPlace['winner_id'] === (int)$thirdPlace['team1_id']) {
        $secondRunnerUpMembers = parse_team_members($thirdPlace['team1_members'] ?? null);
    } else {
        $secondRunnerUpMembers = parse_team_members($thirdPlace['team2_members'] ?? null);
    }
}
?>

<?php if ($R === 0 || (empty($leftRounds) && empty($rightRounds) && empty($final))): ?>
  <div class="rounded-3xl border border-dashed border-slate-800 bg-slate-900/40 p-12 text-center text-slate-500">
    <span class="text-4xl">🌳</span>
    <p class="mt-3 text-sm">Bagan pertandingan untuk kategori ini belum dibuat oleh panitia.</p>
  </div>
<?php else: ?>

  <!-- ================= PODIUM KEJUARAAN (PANGGUNG 3D PODIUM GAYA FUTSAL) ================= -->
  <div class="mb-8 rounded-3xl border border-slate-800 bg-gradient-to-b from-[#0e1726] via-[#090d16] to-[#040711] p-6 sm:p-8 shadow-2xl relative overflow-hidden backdrop-blur-md">
    <!-- Ambient Light Glow -->
    <div class="pointer-events-none absolute -top-24 left-1/2 -translate-x-1/2 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl"></div>

    <!-- Header Section -->
    <div class="text-center mb-6 relative z-10">
      <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-500/40 bg-amber-500/15 px-3.5 py-1 text-xs font-black text-amber-300 mb-2 shadow-sm shadow-amber-500/20">
        🏆 PODIUM KEJUARAAN
      </span>
      <h3 class="text-2xl sm:text-3xl font-black text-white tracking-tight">Tangga Juara &amp; Penghargaan</h3>
      <p class="text-xs sm:text-sm text-slate-400 mt-1 max-w-lg mx-auto">
        Daftar peraih podium kehormatan berdasarkan hasil akhir pertandingan Grand Final &amp; Perebutan Juara 3.
      </p>
    </div>

    <!-- PANGGUNG 3D PODIUM -->
    <div class="grid grid-cols-3 gap-2 sm:gap-4 max-w-2xl mx-auto items-end pt-6 pb-2 relative z-10">
      
      <!-- 2ND PLACE (Bawah Kiri - 1st Runner Up) -->
      <div class="flex flex-col items-center">
        <div class="flex flex-col items-center text-center mb-2 px-1">
          <div class="flex h-12 w-12 sm:h-16 sm:w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-slate-200 via-slate-400 to-slate-600 text-slate-900 font-black text-xl sm:text-2xl shadow-xl shadow-slate-400/20 border-2 border-slate-200">
            🥈
          </div>
          <div class="mt-2 text-xs sm:text-sm font-black text-slate-200 truncate max-w-[110px] sm:max-w-[190px]" title="<?= e($firstRunnerUpName) ?>">
            <?= e($firstRunnerUpName) ?>
          </div>
          <div class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider">
            1st Runner-Up
          </div>
          <?php if (!empty($firstRunnerUpMembers)): ?>
            <div class="mt-1 text-[10px] text-slate-400 truncate max-w-[110px] sm:max-w-[190px]">
              <?= e(implode(' / ', $firstRunnerUpMembers)) ?>
            </div>
          <?php endif; ?>
        </div>
        <!-- Bar Juara 2 -->
        <div class="w-full h-28 sm:h-36 rounded-t-2xl bg-gradient-to-b from-slate-400 via-slate-600 to-slate-800 border-t-2 border-x-2 border-slate-300/60 flex flex-col items-center justify-center shadow-lg shadow-black/60">
          <span class="font-mono text-3xl sm:text-5xl font-black text-slate-900/80 drop-shadow">2</span>
          <span class="text-[10px] sm:text-xs font-extrabold uppercase text-slate-200 tracking-widest mt-1">JUARA 2</span>
        </div>
      </div>

      <!-- 1ST PLACE (Tengah Atas - Champion) -->
      <div class="flex flex-col items-center -mt-6">
        <div class="flex flex-col items-center text-center mb-2 px-1">
          <span class="text-xl sm:text-2xl animate-bounce mb-1">👑</span>
          <div class="flex h-16 w-16 sm:h-20 sm:w-20 items-center justify-center rounded-3xl bg-gradient-to-br from-amber-300 via-amber-500 to-amber-700 text-amber-950 font-black text-2xl sm:text-3xl shadow-2xl shadow-amber-500/40 border-4 border-amber-200 ring-4 ring-amber-400/30">
            🥇
          </div>
          <div class="mt-2 text-sm sm:text-base font-black text-amber-300 truncate max-w-[120px] sm:max-w-[210px]" title="<?= e($championName) ?>">
            <?= e($championName) ?>
          </div>
          <div class="text-[10px] sm:text-xs font-black text-amber-400 uppercase tracking-wider">
            Champion
          </div>
          <?php if (!empty($championMembers)): ?>
            <div class="mt-1 text-[10px] text-amber-200/80 font-medium truncate max-w-[120px] sm:max-w-[210px]">
              <?= e(implode(' / ', $championMembers)) ?>
            </div>
          <?php endif; ?>
        </div>
        <!-- Bar Juara 1 (Paling Tinggi) -->
        <div class="w-full h-40 sm:h-52 rounded-t-2xl bg-gradient-to-b from-amber-400 via-amber-600 to-amber-800 border-t-4 border-x-2 border-amber-300 flex flex-col items-center justify-center shadow-2xl shadow-amber-950/80">
          <span class="font-mono text-4xl sm:text-6xl font-black text-amber-950/80 drop-shadow">1</span>
          <span class="text-[10px] sm:text-xs font-black uppercase text-amber-100 tracking-widest mt-1">JUARA 1</span>
        </div>
      </div>

      <!-- 3RD PLACE (Bawah Kanan - 2nd Runner Up) -->
      <div class="flex flex-col items-center">
        <div class="flex flex-col items-center text-center mb-2 px-1">
          <div class="flex h-12 w-12 sm:h-16 sm:w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-700 via-orange-700 to-orange-900 text-orange-200 font-black text-xl sm:text-2xl shadow-xl shadow-orange-700/20 border-2 border-orange-500">
            🥉
          </div>
          <div class="mt-2 text-xs sm:text-sm font-black text-orange-300 truncate max-w-[110px] sm:max-w-[190px]" title="<?= e($secondRunnerUpName) ?>">
            <?= e($secondRunnerUpName) ?>
          </div>
          <div class="text-[10px] sm:text-xs font-bold text-orange-400 uppercase tracking-wider">
            2nd Runner-Up
          </div>
          <?php if (!empty($secondRunnerUpMembers)): ?>
            <div class="mt-1 text-[10px] text-orange-300/80 truncate max-w-[110px] sm:max-w-[190px]">
              <?= e(implode(' / ', $secondRunnerUpMembers)) ?>
            </div>
          <?php endif; ?>
        </div>
        <!-- Bar Juara 3 -->
        <div class="w-full h-20 sm:h-28 rounded-t-2xl bg-gradient-to-b from-amber-700 via-orange-800 to-orange-950 border-t-2 border-x-2 border-orange-500/60 flex flex-col items-center justify-center shadow-lg shadow-black/60">
          <span class="font-mono text-3xl sm:text-5xl font-black text-orange-950/80 drop-shadow">3</span>
          <span class="text-[10px] sm:text-xs font-extrabold uppercase text-orange-200 tracking-widest mt-1">JUARA 3</span>
        </div>
      </div>

    </div>
  </div>

  <!-- ================= CONTAINER BAGAN PERTANDINGAN ================= -->
  <div class="bracket-scroll-container w-full overflow-x-auto rounded-3xl border border-slate-800/90 bg-[#090d16]/90 p-4 sm:p-8 shadow-2xl backdrop-blur-md">
    <div class="bracket-wrapper relative flex min-w-[1380px] w-max mx-auto items-stretch justify-between gap-8 sm:gap-12 py-4"
         data-bracket-wrapper
         data-total-rounds="<?= $R ?>">

      <!-- SVG Layer untuk Garis Penghubung Bracket yang Menyatu Sempurna -->
      <svg class="bracket-svg-layer pointer-events-none absolute inset-0 h-full w-full overflow-visible z-0" aria-hidden="true"></svg>

      <!-- ================= BLOK KIRI ================= -->
      <div class="flex flex-1 items-stretch justify-end gap-8 sm:gap-12 relative z-10">
        <?php for ($r = 1; $r < $R; $r++): $matches = $leftRounds[$r] ?? []; ?>
          <?php $isSemi = ($r === $R - 1); ?>
          <div class="bracket-col bracket-col-left flex min-w-[290px] max-w-[340px] flex-1 flex-col" data-col-side="left" data-round="<?= $r ?>">
            <div class="mb-4 rounded-xl border border-slate-800 bg-slate-900/90 py-1.5 text-center shadow-sm">
              <h4 class="text-xs font-black uppercase tracking-wider text-blue-400">
                <?= e(round_name($r, $R)) ?> (Kiri)
              </h4>
            </div>
            <div class="flex flex-1 flex-col justify-around gap-6">
              <?php $pairs = array_chunk($matches, 2); $mIdx = 0; ?>
              <?php foreach ($pairs as $pIdx => $pair): ?>
                <?php $isPair = (count($pair) === 2); ?>
                <div class="bracket-pair bracket-pair-left relative flex flex-col justify-around gap-8 flex-1">
                  <?php foreach ($pair as $idx => $m): ?>
                    <?php
                      $currentIdx = $mIdx++;
                      $slotClass = $isSemi
                        ? 'bracket-slot bracket-slot-left bracket-slot-left-semi'
                        : ($isPair ? ($idx === 0 ? 'bracket-slot bracket-slot-left bracket-slot-top' : 'bracket-slot bracket-slot-left bracket-slot-bottom') : 'bracket-slot bracket-slot-left bracket-slot-single');
                    ?>
                    <div class="<?= $slotClass ?> relative z-10"
                         data-bracket-slot
                         data-side="left"
                         data-round="<?= $r ?>"
                         data-index="<?= $currentIdx ?>"
                         data-is-semi="<?= $isSemi ? '1' : '0' ?>"
                         data-target-index="<?= (int)floor($currentIdx / 2) ?>"
                         data-status="<?= e($m['status'] ?? 'upcoming') ?>">
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
      <div class="flex min-w-[340px] max-w-[400px] flex-col items-center justify-center px-2 py-4 relative z-10">
        
        <!-- Kartu Pertandingan Final -->
        <div class="grand-final-box relative z-10 w-full rounded-2xl border-2 border-amber-500/70 bg-gradient-to-b from-slate-900 to-slate-950 p-4 shadow-2xl shadow-amber-500/15 transition hover:border-amber-400"
             data-bracket-target="final"
             data-status="<?= e($final['status'] ?? 'upcoming') ?>">
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
        <div class="third-place-box relative z-10 w-full rounded-2xl border-2 border-orange-500/50 bg-gradient-to-b from-slate-900 to-slate-950 p-4 shadow-xl shadow-orange-500/10 transition hover:border-orange-400"
             data-bracket-target="third_place"
             data-status="<?= e($thirdPlace['status'] ?? 'upcoming') ?>">
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
      <div class="flex flex-1 items-stretch justify-start gap-8 sm:gap-12 relative z-10">
        <?php for ($r = $R - 1; $r >= 1; $r--): $matches = $rightRounds[$r] ?? []; ?>
          <?php $isSemi = ($r === $R - 1); ?>
          <div class="bracket-col bracket-col-right flex min-w-[290px] max-w-[340px] flex-1 flex-col" data-col-side="right" data-round="<?= $r ?>">
            <div class="mb-4 rounded-xl border border-slate-800 bg-slate-900/90 py-1.5 text-center shadow-sm">
              <h4 class="text-xs font-black uppercase tracking-wider text-indigo-400">
                <?= e(round_name($r, $R)) ?> (Kanan)
              </h4>
            </div>
            <div class="flex flex-1 flex-col justify-around gap-6">
              <?php $pairs = array_chunk($matches, 2); $mIdx = 0; ?>
              <?php foreach ($pairs as $pIdx => $pair): ?>
                <?php $isPair = (count($pair) === 2); ?>
                <div class="bracket-pair bracket-pair-right relative flex flex-col justify-around gap-8 flex-1">
                  <?php foreach ($pair as $idx => $m): ?>
                    <?php
                      $currentIdx = $mIdx++;
                      $slotClass = $isSemi
                        ? 'bracket-slot bracket-slot-right bracket-slot-right-semi'
                        : ($isPair ? ($idx === 0 ? 'bracket-slot bracket-slot-right bracket-slot-top' : 'bracket-slot bracket-slot-right bracket-slot-bottom') : 'bracket-slot bracket-slot-right bracket-slot-single');
                    ?>
                    <div class="<?= $slotClass ?> relative z-10"
                         data-bracket-slot
                         data-side="right"
                         data-round="<?= $r ?>"
                         data-index="<?= $currentIdx ?>"
                         data-is-semi="<?= $isSemi ? '1' : '0' ?>"
                         data-target-index="<?= (int)floor($currentIdx / 2) ?>"
                         data-status="<?= e($m['status'] ?? 'upcoming') ?>">
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

<script>
/**
 * Render Garis Penghubung Bracket Berbasis SVG (Two-Sided Knockout Bracket).
 * Menyatukan dua pertandingan sebelumnya menuju satu pertandingan lanjutan dengan siku melengkung presisi (fork).
 */
(function() {
  function drawAllBracketLines() {
    const wrappers = document.querySelectorAll('[data-bracket-wrapper]');
    if (!wrappers.length) return;

    wrappers.forEach(wrapper => {
      // Pastikan wrapper sedang terlihat di layar (bukan hidden di tab yang tidak aktif)
      if (!wrapper.offsetWidth || !wrapper.offsetHeight) return;

      const svg = wrapper.querySelector('.bracket-svg-layer');
      if (!svg) return;

      const totalRounds = parseInt(wrapper.getAttribute('data-total-rounds') || '0', 10);
      if (totalRounds < 2) {
        svg.innerHTML = '';
        return;
      }

      const wrapperRect = wrapper.getBoundingClientRect();
      const w = wrapper.scrollWidth || wrapperRect.width;
      const h = wrapper.scrollHeight || wrapperRect.height;

      svg.setAttribute('viewBox', `0 0 ${w} ${h}`);
      svg.setAttribute('width', `${w}`);
      svg.setAttribute('height', `${h}`);
      svg.style.width = w + 'px';
      svg.style.height = h + 'px';

      function getCardEdge(el, side) {
        const card = el.querySelector('.match') || el;
        const r = card.getBoundingClientRect();
        const y = (r.top - wrapperRect.top) + (r.height / 2);
        const x = (side === 'left') ? (r.left - wrapperRect.left) : (r.right - wrapperRect.left);
        return { x, y, width: r.width, height: r.height };
      }

      const paths = [];

      // ================= 1. BLOK KIRI =================
      for (let r = 1; r < totalRounds - 1; r++) {
        const nextSlots = wrapper.querySelectorAll(`[data-bracket-slot][data-side="left"][data-round="${r + 1}"]`);
        for (let tIdx = 0; tIdx < nextSlots.length; tIdx++) {
          const tgtEl = wrapper.querySelector(`[data-bracket-slot][data-side="left"][data-round="${r + 1}"][data-index="${tIdx}"]`);
          if (!tgtEl) continue;
          const tgtPt = getCardEdge(tgtEl, 'left');

          const topEl = wrapper.querySelector(`[data-bracket-slot][data-side="left"][data-round="${r}"][data-index="${tIdx * 2}"]`);
          const botEl = wrapper.querySelector(`[data-bracket-slot][data-side="left"][data-round="${r}"][data-index="${tIdx * 2 + 1}"]`);

          if (topEl && botEl) {
            const topPt = getCardEdge(topEl, 'right');
            const botPt = getCardEdge(botEl, 'right');
            const xExit = Math.max(topPt.x, botPt.x);
            const xJ = xExit + (tgtPt.x - xExit) / 2;
            paths.push(createLeftForkPath(topPt, botPt, tgtPt, xJ));
          } else if (topEl) {
            const topPt = getCardEdge(topEl, 'right');
            const xJ = topPt.x + (tgtPt.x - topPt.x) / 2;
            paths.push(createLeftStepPath(topPt, tgtPt, xJ));
          } else if (botEl) {
            const botPt = getCardEdge(botEl, 'right');
            const xJ = botPt.x + (tgtPt.x - botPt.x) / 2;
            paths.push(createLeftStepPath(botPt, tgtPt, xJ));
          }
        }
      }

      // ================= 2. BLOK KANAN =================
      for (let r = 1; r < totalRounds - 1; r++) {
        const nextSlots = wrapper.querySelectorAll(`[data-bracket-slot][data-side="right"][data-round="${r + 1}"]`);
        for (let tIdx = 0; tIdx < nextSlots.length; tIdx++) {
          const tgtEl = wrapper.querySelector(`[data-bracket-slot][data-side="right"][data-round="${r + 1}"][data-index="${tIdx}"]`);
          if (!tgtEl) continue;
          const tgtPt = getCardEdge(tgtEl, 'right');

          const topEl = wrapper.querySelector(`[data-bracket-slot][data-side="right"][data-round="${r}"][data-index="${tIdx * 2}"]`);
          const botEl = wrapper.querySelector(`[data-bracket-slot][data-side="right"][data-round="${r}"][data-index="${tIdx * 2 + 1}"]`);

          if (topEl && botEl) {
            const topPt = getCardEdge(topEl, 'left');
            const botPt = getCardEdge(botEl, 'left');
            const xExit = Math.min(topPt.x, botPt.x);
            const xJ = xExit - (xExit - tgtPt.x) / 2;
            paths.push(createRightForkPath(topPt, botPt, tgtPt, xJ));
          } else if (topEl) {
            const topPt = getCardEdge(topEl, 'left');
            const xJ = topPt.x - (topPt.x - tgtPt.x) / 2;
            paths.push(createRightStepPath(topPt, tgtPt, xJ));
          } else if (botEl) {
            const botPt = getCardEdge(botEl, 'left');
            const xJ = botPt.x - (botPt.x - tgtPt.x) / 2;
            paths.push(createRightStepPath(botPt, tgtPt, xJ));
          }
        }
      }

      // ================= 3. SEMIFINAL KE GRAND FINAL & JUARA 3 =================
      const finalEl = wrapper.querySelector('[data-bracket-target="final"]');
      const thirdEl = wrapper.querySelector('[data-bracket-target="third_place"]');
      const semiLeftEl = wrapper.querySelector('[data-bracket-slot][data-side="left"][data-is-semi="1"]');
      const semiRightEl = wrapper.querySelector('[data-bracket-slot][data-side="right"][data-is-semi="1"]');

      // Semifinal Kiri -> Grand Final
      if (semiLeftEl && finalEl) {
        const semiPt = getCardEdge(semiLeftEl, 'right');
        const finalPt = getCardEdge(finalEl, 'left');
        const xJ = semiPt.x + (finalPt.x - semiPt.x) / 2;
        paths.push(createSemiToTarget(semiPt, finalPt, xJ, 'left', 'semi-left'));
      }

      // Semifinal Kanan -> Grand Final
      if (semiRightEl && finalEl) {
        const semiPt = getCardEdge(semiRightEl, 'left');
        const finalPt = getCardEdge(finalEl, 'right');
        const xJ = semiPt.x - (semiPt.x - finalPt.x) / 2;
        paths.push(createSemiToTarget(semiPt, finalPt, xJ, 'right', 'semi-right'));
      }

      // Semifinal Kiri -> Perebutan Juara 3 (Garis putus-putus bronze)
      if (semiLeftEl && thirdEl) {
        const semiPt = getCardEdge(semiLeftEl, 'right');
        const thirdPt = getCardEdge(thirdEl, 'left');
        const xJ = semiPt.x + (thirdPt.x - semiPt.x) / 2;
        paths.push(createSemiToTarget(semiPt, thirdPt, xJ, 'left', 'third-place'));
      }

      // Semifinal Kanan -> Perebutan Juara 3 (Garis putus-putus bronze)
      if (semiRightEl && thirdEl) {
        const semiPt = getCardEdge(semiRightEl, 'left');
        const thirdPt = getCardEdge(thirdEl, 'right');
        const xJ = semiPt.x - (semiPt.x - thirdPt.x) / 2;
        paths.push(createSemiToTarget(semiPt, thirdPt, xJ, 'right', 'third-place'));
      }

      // ================= 4. GENERATE SVG ELEMEN =================
      let svgHtml = `
<defs>
  <filter id="glow-semi-left" x="-20%" y="-20%" width="140%" height="140%">
    <feDropShadow dx="0" dy="0" stdDeviation="2.5" flood-color="#38bdf8" flood-opacity="0.6"/>
  </filter>
  <filter id="glow-semi-right" x="-20%" y="-20%" width="140%" height="140%">
    <feDropShadow dx="0" dy="0" stdDeviation="2.5" flood-color="#818cf8" flood-opacity="0.6"/>
  </filter>
</defs>
      `;

      for (const p of paths) {
        let stroke = '#475569';
        let strokeWidth = '2.5';
        let filter = '';
        let dash = '';
        let opacity = '1';

        if (p.type === 'semi-left') {
          stroke = '#38bdf8';
          strokeWidth = '3';
          filter = 'filter="url(#glow-semi-left)"';
        } else if (p.type === 'semi-right') {
          stroke = '#818cf8';
          strokeWidth = '3';
          filter = 'filter="url(#glow-semi-right)"';
        } else if (p.type === 'third-place') {
          stroke = '#f97316';
          strokeWidth = '2';
          dash = 'stroke-dasharray="6,5"';
          opacity = '0.65';
        }

        svgHtml += `<path d="${p.d}" fill="none" stroke="${stroke}" stroke-width="${strokeWidth}" stroke-linecap="round" stroke-linejoin="round" stroke-opacity="${opacity}" ${dash} ${filter} />`;
      }

      svg.innerHTML = svgHtml;
    });
  }

  function createLeftForkPath(topPt, botPt, tgtPt, xJ) {
    const yTgt = tgtPt.y;
    const rcTop = Math.max(0, Math.min(8, (xJ - topPt.x) / 2, (tgtPt.x - xJ) / 2, Math.abs(yTgt - topPt.y) / 2));
    const rcBot = Math.max(0, Math.min(8, (xJ - botPt.x) / 2, (tgtPt.x - xJ) / 2, Math.abs(botPt.y - yTgt) / 2));

    let d = '';

    // Cabang atas
    if (rcTop < 2 || Math.abs(yTgt - topPt.y) < 4) {
      d += `M ${topPt.x} ${topPt.y} H ${xJ} V ${yTgt} `;
    } else {
      d += `M ${topPt.x} ${topPt.y} L ${xJ - rcTop} ${topPt.y} Q ${xJ} ${topPt.y}, ${xJ} ${topPt.y + rcTop} L ${xJ} ${yTgt - rcTop} Q ${xJ} ${yTgt}, ${xJ + rcTop} ${yTgt} `;
    }

    // Cabang bawah
    if (rcBot < 2 || Math.abs(botPt.y - yTgt) < 4) {
      d += `M ${botPt.x} ${botPt.y} H ${xJ} V ${yTgt} `;
    } else {
      d += `M ${botPt.x} ${botPt.y} L ${xJ - rcBot} ${botPt.y} Q ${xJ} ${botPt.y}, ${xJ} ${botPt.y - rcBot} L ${xJ} ${yTgt + rcBot} Q ${xJ} ${yTgt}, ${xJ + rcBot} ${yTgt} `;
    }

    // Batang horizontal yang menyatu masuk ke pertandingan target
    const stemStart = (rcTop >= 2 || rcBot >= 2) ? xJ + Math.max(rcTop, rcBot) : xJ;
    d += `M ${stemStart} ${yTgt} L ${tgtPt.x} ${yTgt}`;

    return { d, type: 'normal' };
  }

  function createRightForkPath(topPt, botPt, tgtPt, xJ) {
    const yTgt = tgtPt.y;
    const rcTop = Math.max(0, Math.min(8, (topPt.x - xJ) / 2, (xJ - tgtPt.x) / 2, Math.abs(yTgt - topPt.y) / 2));
    const rcBot = Math.max(0, Math.min(8, (botPt.x - xJ) / 2, (xJ - tgtPt.x) / 2, Math.abs(botPt.y - yTgt) / 2));

    let d = '';

    // Cabang atas
    if (rcTop < 2 || Math.abs(yTgt - topPt.y) < 4) {
      d += `M ${topPt.x} ${topPt.y} H ${xJ} V ${yTgt} `;
    } else {
      d += `M ${topPt.x} ${topPt.y} L ${xJ + rcTop} ${topPt.y} Q ${xJ} ${topPt.y}, ${xJ} ${topPt.y + rcTop} L ${xJ} ${yTgt - rcTop} Q ${xJ} ${yTgt}, ${xJ - rcTop} ${yTgt} `;
    }

    // Cabang bawah
    if (rcBot < 2 || Math.abs(botPt.y - yTgt) < 4) {
      d += `M ${botPt.x} ${botPt.y} H ${xJ} V ${yTgt} `;
    } else {
      d += `M ${botPt.x} ${botPt.y} L ${xJ + rcBot} ${botPt.y} Q ${xJ} ${botPt.y}, ${xJ} ${botPt.y - rcBot} L ${xJ} ${yTgt + rcBot} Q ${xJ} ${yTgt}, ${xJ - rcBot} ${yTgt} `;
    }

    // Batang horizontal yang menyatu masuk ke pertandingan target
    const stemStart = (rcTop >= 2 || rcBot >= 2) ? xJ - Math.max(rcTop, rcBot) : xJ;
    d += `M ${stemStart} ${yTgt} L ${tgtPt.x} ${yTgt}`;

    return { d, type: 'normal' };
  }

  function createLeftStepPath(srcPt, tgtPt, xJ) {
    const yTgt = tgtPt.y;
    const rc = Math.max(0, Math.min(8, (xJ - srcPt.x) / 2, (tgtPt.x - xJ) / 2, Math.abs(yTgt - srcPt.y) / 2));
    if (rc < 2 || Math.abs(yTgt - srcPt.y) < 4) {
      return { d: `M ${srcPt.x} ${srcPt.y} H ${xJ} V ${yTgt} H ${tgtPt.x}`, type: 'normal' };
    }
    if (srcPt.y < yTgt) {
      return { d: `M ${srcPt.x} ${srcPt.y} L ${xJ - rc} ${srcPt.y} Q ${xJ} ${srcPt.y}, ${xJ} ${srcPt.y + rc} L ${xJ} ${yTgt - rc} Q ${xJ} ${yTgt}, ${xJ + rc} ${yTgt} L ${tgtPt.x} ${yTgt}`, type: 'normal' };
    } else {
      return { d: `M ${srcPt.x} ${srcPt.y} L ${xJ - rc} ${srcPt.y} Q ${xJ} ${srcPt.y}, ${xJ} ${srcPt.y - rc} L ${xJ} ${yTgt + rc} Q ${xJ} ${yTgt}, ${xJ + rc} ${yTgt} L ${tgtPt.x} ${yTgt}`, type: 'normal' };
    }
  }

  function createRightStepPath(srcPt, tgtPt, xJ) {
    const yTgt = tgtPt.y;
    const rc = Math.max(0, Math.min(8, (srcPt.x - xJ) / 2, (xJ - tgtPt.x) / 2, Math.abs(yTgt - srcPt.y) / 2));
    if (rc < 2 || Math.abs(yTgt - srcPt.y) < 4) {
      return { d: `M ${srcPt.x} ${srcPt.y} H ${xJ} V ${yTgt} H ${tgtPt.x}`, type: 'normal' };
    }
    if (srcPt.y < yTgt) {
      return { d: `M ${srcPt.x} ${srcPt.y} L ${xJ + rc} ${srcPt.y} Q ${xJ} ${srcPt.y}, ${xJ} ${srcPt.y + rc} L ${xJ} ${yTgt - rc} Q ${xJ} ${yTgt}, ${xJ - rc} ${yTgt} L ${tgtPt.x} ${yTgt}`, type: 'normal' };
    } else {
      return { d: `M ${srcPt.x} ${srcPt.y} L ${xJ + rc} ${srcPt.y} Q ${xJ} ${srcPt.y}, ${xJ} ${srcPt.y - rc} L ${xJ} ${yTgt + rc} Q ${xJ} ${yTgt}, ${xJ - rc} ${yTgt} L ${tgtPt.x} ${yTgt}`, type: 'normal' };
    }
  }

  function createSemiToTarget(srcPt, tgtPt, xJ, side, type) {
    if (side === 'left') {
      const rc = Math.max(0, Math.min(10, (xJ - srcPt.x) / 2, (tgtPt.x - xJ) / 2, Math.abs(tgtPt.y - srcPt.y) / 2));
      if (rc < 2 || Math.abs(tgtPt.y - srcPt.y) < 4) {
        return { d: `M ${srcPt.x} ${srcPt.y} H ${xJ} V ${tgtPt.y} H ${tgtPt.x}`, type };
      }
      if (srcPt.y < tgtPt.y) {
        return { d: `M ${srcPt.x} ${srcPt.y} L ${xJ - rc} ${srcPt.y} Q ${xJ} ${srcPt.y}, ${xJ} ${srcPt.y + rc} L ${xJ} ${tgtPt.y - rc} Q ${xJ} ${tgtPt.y}, ${xJ + rc} ${tgtPt.y} L ${tgtPt.x} ${tgtPt.y}`, type };
      } else {
        return { d: `M ${srcPt.x} ${srcPt.y} L ${xJ - rc} ${srcPt.y} Q ${xJ} ${srcPt.y}, ${xJ} ${srcPt.y - rc} L ${xJ} ${tgtPt.y + rc} Q ${xJ} ${tgtPt.y}, ${xJ + rc} ${tgtPt.y} L ${tgtPt.x} ${tgtPt.y}`, type };
      }
    } else {
      const rc = Math.max(0, Math.min(10, (srcPt.x - xJ) / 2, (xJ - tgtPt.x) / 2, Math.abs(tgtPt.y - srcPt.y) / 2));
      if (rc < 2 || Math.abs(tgtPt.y - srcPt.y) < 4) {
        return { d: `M ${srcPt.x} ${srcPt.y} H ${xJ} V ${tgtPt.y} H ${tgtPt.x}`, type };
      }
      if (srcPt.y < tgtPt.y) {
        return { d: `M ${srcPt.x} ${srcPt.y} L ${xJ + rc} ${srcPt.y} Q ${xJ} ${srcPt.y}, ${xJ} ${srcPt.y + rc} L ${xJ} ${tgtPt.y - rc} Q ${xJ} ${tgtPt.y}, ${xJ - rc} ${tgtPt.y} L ${tgtPt.x} ${tgtPt.y}`, type };
      } else {
        return { d: `M ${srcPt.x} ${srcPt.y} L ${xJ + rc} ${srcPt.y} Q ${xJ} ${srcPt.y}, ${xJ} ${srcPt.y - rc} L ${xJ} ${tgtPt.y + rc} Q ${xJ} ${tgtPt.y}, ${xJ - rc} ${tgtPt.y} L ${tgtPt.x} ${tgtPt.y}`, type };
      }
    }
  }

  window.drawAllBracketLines = drawAllBracketLines;

  // Pasang ResizeObserver agar otomatis menggambar ulang saat terlihat atau terjadi perubahan ukuran
  if (typeof ResizeObserver !== 'undefined') {
    if (!window.bracketResizeObserver) {
      window.bracketResizeObserver = new ResizeObserver(() => {
        requestAnimationFrame(drawAllBracketLines);
      });
    }
    document.querySelectorAll('[data-bracket-wrapper]').forEach(el => {
      window.bracketResizeObserver.observe(el);
    });
  }

  // Trigger saat render pertama
  requestAnimationFrame(drawAllBracketLines);
  window.addEventListener('load', () => requestAnimationFrame(drawAllBracketLines));
  window.addEventListener('resize', () => requestAnimationFrame(drawAllBracketLines));
})();
</script>
