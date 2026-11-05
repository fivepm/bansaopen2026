<?php
/**
 * Konten Dinamis Tab Playoff & Final Futsal
 * Menyajikan UI Podium Kejuaraan (Champion, 1st Runner-Up, 2nd Runner-Up) serta kartu match Grand Final & Juara 3.
 * @var array $playoffs
 * @var string $sport
 */
$final = $playoffs['final'] ?? null;
$third = $playoffs['thirdPlace'] ?? null;

$isFinalFinished = ($final && $final['status'] === 'finished' && !empty($final['winner_id']));
$isThirdFinished = ($third && $third['status'] === 'finished' && !empty($third['winner_id']));

// 1. Champion (Juara 1) - Pemenang Grand Final
$championName = $isFinalFinished ? ($final['winner_name'] ?? 'Pemenang Grand Final') : 'Pemenang Grand Final';

// 2. 1st Runner Up (Juara 2) - Finalis yang kalah di Grand Final
$firstRunnerUpName = 'Runner-up Grand Final';
if ($isFinalFinished) {
    if ((int)$final['winner_id'] === (int)$final['team1_id']) {
        $firstRunnerUpName = $final['team2_name'] ?? 'Runner-up Grand Final';
    } else {
        $firstRunnerUpName = $final['team1_name'] ?? 'Runner-up Grand Final';
    }
}

// 3. 2nd Runner Up (Juara 3) - Pemenang pertandingan Perebutan Juara 3
$secondRunnerUpName = $isThirdFinished ? ($third['winner_name'] ?? 'Pemenang Perebutan Juara 3') : 'Pemenang Perebutan Juara 3';
?>

<div class="space-y-8">
  
  <!-- ======================================================== -->
  <!-- UI PODIUM JUARA (Champion Tengah, Juara 2 Kiri, Juara 3 Kanan) -->
  <!-- ======================================================== -->
  <div class="rounded-3xl border border-slate-800 bg-gradient-to-b from-[#0e1726] via-[#090d16] to-[#040711] p-6 sm:p-8 shadow-2xl relative overflow-hidden">
    
    <!-- Glow Effect Ambient -->
    <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="text-center mb-6 relative z-10">
      <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-500/40 bg-amber-500/15 px-3.5 py-1 text-xs font-black text-amber-300 mb-2 shadow-sm shadow-amber-500/20">
        🏆 PODIUM KEJUARAAN FUTSAL
      </span>
      <h2 class="text-2xl sm:text-3xl font-black text-white tracking-tight">Tangga Juara &amp; Penghargaan</h2>
      <p class="text-xs sm:text-sm text-slate-400 mt-1 max-w-lg mx-auto">
        Daftar peraih podium kehormatan berdasarkan hasil akhir pertandingan puncak Playoff &amp; Grand Final.
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
        </div>
        <!-- Bar Juara 3 -->
        <div class="w-full h-20 sm:h-28 rounded-t-2xl bg-gradient-to-b from-amber-700 via-orange-800 to-orange-950 border-t-2 border-x-2 border-orange-500/60 flex flex-col items-center justify-center shadow-lg shadow-black/60">
          <span class="font-mono text-3xl sm:text-5xl font-black text-orange-950/80 drop-shadow">3</span>
          <span class="text-[10px] sm:text-xs font-extrabold uppercase text-orange-200 tracking-widest mt-1">JUARA 3</span>
        </div>
      </div>

    </div>
  </div>

  <!-- ======================================================== -->
  <!-- BAGIAN JADWAL & HASIL PERTANDINGAN PLAYOFF & FINAL       -->
  <!-- ======================================================== -->
  <div>
    <div class="mb-4">
      <h3 class="text-xl font-black text-white flex items-center gap-2">
        <span>⚔️</span>
        <span>Pertandingan Playoff &amp; Babak Final</span>
      </h3>
      <p class="text-xs text-slate-400 mt-1">
        Juara Grup lolos ke Grand Final (Juara 1 &amp; 2), dan Runner-up Grup lolos ke Perebutan Juara 3 segera setelah masing-masing grup menyelesaikan pertandingannya.
      </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- GRAND FINAL CARD -->
      <?php if (!empty($final)): $m = $final; ?>
        <div class="rounded-2xl border-2 border-amber-500/50 bg-slate-900/90 p-4 shadow-xl shadow-amber-500/10">
          <div class="flex items-center justify-between mb-2 pb-2 border-b border-amber-500/20">
            <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-500/50 bg-amber-500/20 px-3 py-0.5 text-xs font-black text-amber-300">
              👑 GRAND FINAL
            </span>
            <span class="text-[11px] font-bold text-amber-400">Penentu Juara 1 &amp; 2</span>
          </div>
          <?php include dirname(__DIR__) . '/partials/match_card.php'; ?>
        </div>
      <?php else: ?>
        <div class="rounded-2xl border border-dashed border-slate-800 bg-slate-900/40 p-6 text-center text-slate-500 text-xs">
          Jadwal Grand Final belum dibuat.
        </div>
      <?php endif; ?>

      <!-- PEREBUTAN JUARA 3 CARD -->
      <?php if (!empty($third)): $m = $third; ?>
        <div class="rounded-2xl border-2 border-blue-500/40 bg-slate-900/90 p-4 shadow-xl shadow-blue-500/10">
          <div class="flex items-center justify-between mb-2 pb-2 border-b border-blue-500/20">
            <span class="inline-flex items-center gap-1.5 rounded-full border border-blue-500/50 bg-blue-500/20 px-3 py-0.5 text-xs font-black text-blue-300">
              🥉 PEREBUTAN JUARA 3
            </span>
            <span class="text-[11px] font-bold text-blue-400">Penentu Juara 3</span>
          </div>
          <?php include dirname(__DIR__) . '/partials/match_card.php'; ?>
        </div>
      <?php else: ?>
        <div class="rounded-2xl border border-dashed border-slate-800 bg-slate-900/40 p-6 text-center text-slate-500 text-xs">
          Jadwal Perebutan Juara 3 belum dibuat.
        </div>
      <?php endif; ?>
    </div>
  </div>

</div>

