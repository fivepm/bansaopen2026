<?php
/**
 * @var array $m Record pertandingan
 * Kartu pertandingan dinamis untuk Bracket (Public & Admin), Live, dan Hasil.
 */
$w = !empty($m['winner_id']) ? (int)$m['winner_id'] : null;
$system = $m['scoring_system'] ?? 'rubber_set';
$isFutsal = (($m['sport_type'] ?? '') === 'futsal');

// Poin per set
$s1a = (int)($m['set1_a'] ?? 0);
$s1b = (int)($m['set1_b'] ?? 0);
$s2a = (int)($m['set2_a'] ?? 0);
$s2b = (int)($m['set2_b'] ?? 0);
$s3a = (int)($m['set3_a'] ?? 0);
$s3b = (int)($m['set3_b'] ?? 0);

$played1 = ($s1a > 0 || $s1b > 0);
$played2 = ($s2a > 0 || $s2b > 0);
$played3 = ($s3a > 0 || $s3b > 0);

// Pemenang per set
$win1_a = ($played1 && $s1a > $s1b);
$win1_b = ($played1 && $s1b > $s1a);
$win2_a = ($played2 && $s2a > $s2b);
$win2_b = ($played2 && $s2b > $s2a);
$win3_a = ($played3 && $s3a > $s3b);
$win3_b = ($played3 && $s3b > $s3a);

// Kondisi Rubber Set:
// 1. Menang berturut-turut di Set 1 dan Set 2 (2-0)
$is2_0 = ($win1_a && $win2_a) || ($win1_b && $win2_b);

// 2. Seri di Set 1 dan Set 2 (1-1)
$is1_1 = ($win1_a && $win2_b) || ($win1_b && $win2_a);

// Nilai kolom skor per tim:
if ($isFutsal) {
    // 2 Kolom untuk Futsal: Babak 1 dan Babak 2
    $col1_a = $played1 ? $s1a : ($m['status'] === 'live' ? 0 : '-');
    $col1_b = $played1 ? $s1b : ($m['status'] === 'live' ? 0 : '-');
    $col2_a = $played2 ? $s2a : ($m['status'] === 'live' ? 0 : '-');
    $col2_b = $played2 ? $s2b : ($m['status'] === 'live' ? 0 : '-');
    $col3_a = null;
    $col3_b = null;
} elseif ($system === 'poin_lanjut') {
    // 2 Kolom Skor untuk Sistem Poin Lanjut:
    // Kolom Set 1: Menampilkan skor akhir saat Set 1 selesai (titik tengah pertandingan)
    $col1_a = $played1 ? $s1a : ($m['status'] === 'live' ? 0 : '-');
    $col1_b = $played1 ? $s1b : ($m['status'] === 'live' ? 0 : '-');

    // Kolom Set 2: Menampilkan skor akumulasi paling akhir (penentu pemenang)
    if ($played2) {
        $col2_a = $s2a;
        $col2_b = $s2b;
    } elseif ($m['status'] === 'live' && $played1) {
        $col2_a = $s1a;
        $col2_b = $s1b;
    } elseif ($m['status'] === 'finished') {
        $col2_a = max($s1a, $s2a, (int)($m['score1'] ?? 0));
        $col2_b = max($s1b, $s2b, (int)($m['score2'] ?? 0));
    } else {
        $col2_a = '-';
        $col2_b = '-';
    }

    $col3_a = null;
    $col3_b = null;

    // Evaluasi pemenang Set 1 (titik tengah) dan Set 2 (skor akumulasi akhir)
    $win1_a = ($played1 && $s1a > $s1b);
    $win1_b = ($played1 && $s1b > $s1a);
    $win2_a = ($col2_a !== '-' && $col2_b !== '-' && (int)$col2_a > (int)$col2_b);
    $win2_b = ($col2_a !== '-' && $col2_b !== '-' && (int)$col2_b > (int)$col2_a);
    $win3_a = false;
    $win3_b = false;
} else {
    // Rubber Set (Best of 3): 3 Kolom Skor
    $col1_a = $played1 ? $s1a : ($m['status'] === 'live' ? 0 : '-');
    $col1_b = $played1 ? $s1b : ($m['status'] === 'live' ? 0 : '-');
    $col2_a = $played2 ? $s2a : ($played1 && $m['status'] === 'live' ? 0 : '-');
    $col2_b = $played2 ? $s2b : ($played1 && $m['status'] === 'live' ? 0 : '-');

    // Logika kolom ketiga:
    // - Jika 2-0 (menang berturut-turut di Set 1 & 2): isi tanda strip (-)
    // - Jika 1-1 (seri di Set 1 & 2): tampilkan skor angka
    // - Lainnya: strip (-)
    if ($is2_0) {
        $col3_a = '-';
        $col3_b = '-';
    } elseif ($is1_1) {
        $col3_a = $played3 ? $s3a : ($m['status'] === 'live' ? 0 : '-');
        $col3_b = $played3 ? $s3b : ($m['status'] === 'live' ? 0 : '-');
    } else {
        $col3_a = '-';
        $col3_b = '-';
    }
}
?>
<div class="match <?= e($m['status']) ?>" id="match-card-<?= (int)$m['id'] ?>">
  <!-- Header: Nomor, Lapangan, Keterangan Sistem Set, Status -->
  <div class="mhead flex items-center justify-between gap-2 px-3.5 py-2 bg-slate-950/80 border-b border-slate-800 text-xs">
    <div class="flex items-center gap-1.5 min-w-0 flex-1">
      <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-gradient-to-r from-blue-600/25 to-indigo-600/25 border border-blue-500/35 text-blue-400 font-extrabold text-[11px] shrink-0">
        #<?= (int)$m['round'] ?>-<?= (int)$m['position'] ?>
      </span>
      <?php if (!empty($m['court'])): ?>
        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-cyan-600/15 border border-cyan-500/30 text-cyan-400 font-semibold text-[10px] shrink-0">
          📍 <?= e($m['court']) ?>
        </span>
      <?php endif; ?>
      <?php if ($isFutsal): ?>
        <?php $isPlayoffStage = ($m['stage'] === 'knockout' || in_array($m['bracket_side'] ?? '', ['final', 'third_place'], true)); ?>
        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold border shrink-0 leading-none <?= $isPlayoffStage ? 'bg-amber-950/50 text-amber-300 border-amber-500/40' : 'bg-emerald-950/50 text-emerald-300 border-emerald-500/40' ?>" title="<?= $isPlayoffStage ? 'Tipe Playoff' : 'Tipe Fase Grup' ?>">
          <?= $isPlayoffStage ? '🏆 Playoff' : '⚽ Grup' ?>
        </span>
      <?php else: ?>
        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold border shrink-0 leading-none <?= $system === 'poin_lanjut' ? 'bg-amber-950/50 text-amber-300 border-amber-500/40' : 'bg-cyan-950/50 text-cyan-300 border-cyan-500/40' ?>" title="Sistem Pertandingan">
          <?= $system === 'poin_lanjut' ? 'Poin Lanjut (2 Set)' : 'Rubber Set' ?>
        </span>
      <?php endif; ?>
    </div>
    <span class="badge <?= e($m['status']) ?> text-[10px] !py-0.5 !px-2 shrink-0 leading-none">
      <?php if ($m['status'] === 'live' && !empty($m['current_set'])): ?>
        LIVE (<?= $isFutsal ? 'Babak ' : 'Set ' ?><?= (int)$m['current_set'] ?>)
      <?php else: ?>
        <?= e(status_label($m['status'])) ?>
      <?php endif; ?>
    </span>
  </div>

  <!-- Header Kolom Set / Babak -->
  <div class="mset-header flex items-center justify-between px-3.5 py-1 text-[10px] uppercase font-bold text-slate-500 border-b border-slate-800/40 bg-slate-950/30">
    <span class="mset-title">Tim &amp; Pemain</span>
    <div class="flex items-center gap-1.5 text-center font-mono">
      <?php if ($isFutsal): ?>
        <span class="w-10 text-center text-slate-400 font-bold leading-none" title="Skor Pertandingan (Total Gol)"><span class="score-num">Skor</span></span>
      <?php else: ?>
        <span class="w-6 text-center text-slate-400 leading-none" title="<?= $system === 'poin_lanjut' ? 'Set 1 (Skor Titik Tengah)' : 'Set 1' ?>">
          <span class="score-num">S1</span>
        </span>
        <span class="w-6 text-center text-slate-400 leading-none" title="<?= $system === 'poin_lanjut' ? 'Set 2 (Skor Akumulasi Akhir)' : 'Set 2' ?>">
          <span class="score-num">S2</span>
        </span>
        <?php if ($system === 'rubber_set'): ?>
          <span class="w-6 text-center text-slate-400 leading-none" title="Set 3 (Rubber Game)">
            <span class="score-num">S3</span>
          </span>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- Tim 1 -->
  <?php
    $m1Members = parse_team_members($m['team1_members'] ?? null);
    $p1 = match_team_placeholder($m, 1, $R ?? $totalRounds ?? null);
    $hasT1 = !empty($m['team1_name']);
  ?>
  <div class="team <?= $w && $w == $m['team1_id'] ? 'win' : '' ?> flex items-center justify-between border-b border-slate-800/60 px-3.5 py-2.5 gap-2.5">
    <div class="min-w-0 flex-1">
      <div class="text-xs font-bold leading-snug break-words <?= $w && $w == $m['team1_id'] ? '!text-emerald-300' : ($hasT1 ? 'text-slate-100' : 'text-slate-400') ?>" title="<?= e($hasT1 ? $m['team1_name'] : $p1) ?>">
        <?php if ($hasT1): ?>
          <span><?= e($m['team1_name']) ?></span>
          <?php if ($w && $w == $m['team1_id']): ?><span class="inline-block ml-1 text-xs" title="Pemenang Pertandingan">👑</span><?php endif; ?>
        <?php else: ?>
          <span class="inline-flex items-center gap-1.5 text-slate-400 font-semibold italic text-[11px] leading-tight">
            <span class="opacity-60 not-italic text-[10px]">⏱</span>
            <span class="break-words"><?= e($p1) ?></span>
          </span>
        <?php endif; ?>
      </div>
      <?php if (!empty($m1Members) && !$isFutsal): ?>
        <div class="mt-1 space-y-0.5 leading-tight">
          <?php foreach ($m1Members as $member): ?>
            <div class="text-[10px] text-slate-400 font-normal flex items-center gap-1.5 break-words" title="<?= e($member) ?>">
              <span class="text-[9px] text-slate-500">👤</span>
              <span class="break-words"><?= e($member) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
    <div class="flex items-center gap-1.5 font-mono text-xs shrink-0 self-center">
      <?php if ($isFutsal): ?>
        <?php
          $futsalScore1 = ($m['status'] === 'upcoming') ? '-' : (int)($m['score1'] ?? 0);
          $t1Win = ($w && $w == $m['team1_id']);
        ?>
        <span class="w-10 h-6 flex items-center justify-center text-center rounded border border-slate-800/80 font-bold leading-none <?= $t1Win ? 'bg-emerald-950/60 text-emerald-300 border-emerald-500/30' : ($futsalScore1 === '-' ? 'text-slate-500 bg-slate-950/40' : 'text-slate-200 bg-slate-950/70') ?>">
          <span class="score-num"><?= $futsalScore1 ?></span>
        </span>
      <?php else: ?>
        <span class="w-6 h-6 flex items-center justify-center text-center rounded border border-slate-800/80 leading-none <?= ($win1_a && $played1) ? 'bg-emerald-950/60 text-emerald-300 font-bold border-emerald-500/30' : ($col1_a === '-' ? 'text-slate-500 bg-slate-950/40' : 'text-slate-200 bg-slate-950/70') ?>">
          <span class="score-num"><?= $col1_a ?></span>
        </span>
        <span class="w-6 h-6 flex items-center justify-center text-center rounded border border-slate-800/80 leading-none <?= ($win2_a && $col2_a !== '-') ? 'bg-emerald-950/60 text-emerald-300 font-bold border-emerald-500/30' : ($col2_a === '-' ? 'text-slate-500 bg-slate-950/40' : 'text-slate-200 bg-slate-950/70') ?>">
          <span class="score-num"><?= $col2_a ?></span>
        </span>
        <?php if ($system === 'rubber_set'): ?>
          <span class="w-6 h-6 flex items-center justify-center text-center rounded border border-slate-800/80 leading-none <?= ($win3_a && $played3 && $is1_1) ? 'bg-emerald-950/60 text-emerald-300 font-bold border-emerald-500/30' : ($col3_a === '-' ? 'text-slate-500 bg-slate-950/40' : 'text-slate-200 bg-slate-950/70') ?>">
            <span class="score-num"><?= $col3_a ?></span>
          </span>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- Tim 2 -->
  <?php
    $m2Members = parse_team_members($m['team2_members'] ?? null);
    $p2 = match_team_placeholder($m, 2, $R ?? $totalRounds ?? null);
    $hasT2 = !empty($m['team2_name']);
  ?>
  <div class="team <?= $w && $w == $m['team2_id'] ? 'win' : '' ?> flex items-center justify-between px-3.5 py-2.5 gap-2.5">
    <div class="min-w-0 flex-1">
      <div class="text-xs font-bold leading-snug break-words <?= $w && $w == $m['team2_id'] ? '!text-emerald-300' : ($hasT2 ? 'text-slate-100' : 'text-slate-400') ?>" title="<?= e($hasT2 ? $m['team2_name'] : $p2) ?>">
        <?php if ($hasT2): ?>
          <span><?= e($m['team2_name']) ?></span>
          <?php if ($w && $w == $m['team2_id']): ?><span class="inline-block ml-1 text-xs" title="Pemenang Pertandingan">👑</span><?php endif; ?>
        <?php else: ?>
          <span class="inline-flex items-center gap-1.5 text-slate-400 font-semibold italic text-[11px] leading-tight">
            <span class="opacity-60 not-italic text-[10px]">⏱</span>
            <span class="break-words"><?= e($p2) ?></span>
          </span>
        <?php endif; ?>
      </div>
      <?php if (!empty($m2Members) && !$isFutsal): ?>
        <div class="mt-1 space-y-0.5 leading-tight">
          <?php foreach ($m2Members as $member): ?>
            <div class="text-[10px] text-slate-400 font-normal flex items-center gap-1.5 break-words" title="<?= e($member) ?>">
              <span class="text-[9px] text-slate-500">👤</span>
              <span class="break-words"><?= e($member) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
    <div class="flex items-center gap-1.5 font-mono text-xs shrink-0 self-center">
      <?php if ($isFutsal): ?>
        <?php
          $futsalScore2 = ($m['status'] === 'upcoming') ? '-' : (int)($m['score2'] ?? 0);
          $t2Win = ($w && $w == $m['team2_id']);
        ?>
        <span class="w-10 h-6 flex items-center justify-center text-center rounded border border-slate-800/80 font-bold leading-none <?= $t2Win ? 'bg-emerald-950/60 text-emerald-300 border-emerald-500/30' : ($futsalScore2 === '-' ? 'text-slate-500 bg-slate-950/40' : 'text-slate-200 bg-slate-950/70') ?>">
          <span class="score-num"><?= $futsalScore2 ?></span>
        </span>
      <?php else: ?>
        <span class="w-6 h-6 flex items-center justify-center text-center rounded border border-slate-800/80 leading-none <?= ($win1_b && $played1) ? 'bg-emerald-950/60 text-emerald-300 font-bold border-emerald-500/30' : ($col1_b === '-' ? 'text-slate-500 bg-slate-950/40' : 'text-slate-200 bg-slate-950/70') ?>">
          <span class="score-num"><?= $col1_b ?></span>
        </span>
        <span class="w-6 h-6 flex items-center justify-center text-center rounded border border-slate-800/80 leading-none <?= ($win2_b && $col2_b !== '-') ? 'bg-emerald-950/60 text-emerald-300 font-bold border-emerald-500/30' : ($col2_b === '-' ? 'text-slate-500 bg-slate-950/40' : 'text-slate-200 bg-slate-950/70') ?>">
          <span class="score-num"><?= $col2_b ?></span>
        </span>
        <?php if ($system === 'rubber_set'): ?>
          <span class="w-6 h-6 flex items-center justify-center text-center rounded border border-slate-800/80 leading-none <?= ($win3_b && $played3 && $is1_1) ? 'bg-emerald-950/60 text-emerald-300 font-bold border-emerald-500/30' : ($col3_b === '-' ? 'text-slate-500 bg-slate-950/40' : 'text-slate-200 bg-slate-950/70') ?>">
            <span class="score-num"><?= $col3_b ?></span>
          </span>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- Perangkat Wasit Pertandingan (Wasit Utama 1 orang, Wasit Garis 2 orang) -->
  <?php
    $hasRefMain = !empty($m['referee_main']);
    $hasRefLine1 = !empty($m['referee_line1']);
    $hasRefLine2 = !empty($m['referee_line2']);
    $hasReferees = ($hasRefMain || $hasRefLine1 || $hasRefLine2);
  ?>
  <?php if ($hasReferees): ?>
    <div class="mreferees flex flex-wrap items-center justify-between gap-x-2 gap-y-1 px-3.5 py-1.5 bg-slate-950/85 border-t border-slate-800/80 text-[10.5px]">
      <div class="flex flex-wrap items-center gap-x-3 gap-y-0.5 min-w-0 flex-1">
        <?php if ($hasRefMain): ?>
          <span class="inline-flex items-center gap-1 font-medium text-slate-300 truncate max-w-full" title="Wasit Utama: <?= e($m['referee_main']) ?>">
            <span class="text-amber-400 font-bold shrink-0">👨‍⚖️ Wasit:</span>
            <span class="font-semibold text-slate-200 truncate"><?= e($m['referee_main']) ?></span>
          </span>
        <?php endif; ?>
        <?php if ($hasRefLine1 || $hasRefLine2): ?>
          <span class="inline-flex items-center gap-1 font-medium text-slate-400 truncate max-w-full" title="Wasit Garis: <?= e(implode(', ', array_filter([$m['referee_line1'] ?? '', $m['referee_line2'] ?? '']))) ?>">
            <span class="text-blue-400 font-semibold shrink-0">🚩 Garis:</span>
            <span class="text-slate-300 truncate">
              <?= e(implode(', ', array_filter([$m['referee_line1'] ?? '', $m['referee_line2'] ?? '']))) ?>
            </span>
          </span>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>

  <!-- Footer: Hari, Tanggal, Jam, dan Status Pemenang jika Selesai -->
  <div class="mfoot flex items-center justify-between gap-1.5 px-3.5 py-2 bg-slate-950/60 border-t border-slate-800/80 text-[11px] text-slate-400">
    <span class="flex items-center gap-1.5 min-w-0 flex-1 truncate" title="<?= e(fmt_dt($m['scheduled_at'])) ?>">
      <span class="shrink-0">📅</span>
      <span class="truncate"><?= e(fmt_dt($m['scheduled_at'])) ?></span>
    </span>
    <?php if ($w): ?>
      <span class="text-[10px] font-bold text-emerald-400 shrink-0" title="Pemenang: <?= e($m['winner_name'] ?? 'Pemenang') ?>">
        Menang: <?= e($m['winner_name'] ?? 'Pemenang') ?>
      </span>
    <?php elseif ($m['status'] === 'finished' && $isFutsal): ?>
      <span class="text-[10px] font-bold text-amber-400 shrink-0">
        Hasil: Seri (Draw)
      </span>
    <?php endif; ?>
  </div>
</div>
