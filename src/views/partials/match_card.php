<?php
/**
 * @var array $m Record pertandingan
 * Kartu pertandingan dinamis untuk Bracket (Public & Admin), Live, dan Hasil.
 */
$w = !empty($m['winner_id']) ? (int)$m['winner_id'] : null;
$system = $m['scoring_system'] ?? 'rubber_set';

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
if ($system === 'poin_lanjut') {
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
  <div class="mhead flex items-center justify-between gap-1.5 px-3 py-1.5 bg-slate-950/80 border-b border-slate-800 text-xs">
    <div class="flex items-center gap-1.5 truncate">
      <span class="font-bold text-slate-300">#<?= (int)$m['round'] ?>-<?= (int)$m['position'] ?><?= !empty($m['court']) ? ' · ' . e($m['court']) : '' ?></span>
      <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold border <?= $system === 'poin_lanjut' ? 'bg-amber-950/50 text-amber-300 border-amber-500/40' : 'bg-cyan-950/50 text-cyan-300 border-cyan-500/40' ?>" title="Sistem Pertandingan">
        <?= $system === 'poin_lanjut' ? 'Poin Lanjut (2 Set)' : 'Rubber Set' ?>
      </span>
    </div>
    <span class="badge <?= e($m['status']) ?> text-[10px] !py-0.5 !px-2 shrink-0">
      <?= ($m['status'] === 'live' && !empty($m['current_set'])) ? 'LIVE (Set ' . (int)$m['current_set'] . ')' : e(status_label($m['status'])) ?>
    </span>
  </div>

  <!-- Header Kolom Set (S1, S2, dan opsional S3) -->
  <div class="flex items-center justify-between px-3 pt-1.5 pb-1 text-[10px] uppercase font-bold text-slate-500 border-b border-slate-800/40 bg-slate-950/30">
    <span>Tim &amp; Pemain</span>
    <div class="flex items-center gap-1.5 text-center font-mono">
      <span class="w-6 text-slate-400" title="<?= $system === 'poin_lanjut' ? 'Set 1 (Skor Titik Tengah)' : 'Set 1' ?>">S1</span>
      <span class="w-6 text-slate-400" title="<?= $system === 'poin_lanjut' ? 'Set 2 (Skor Akumulasi Akhir)' : 'Set 2' ?>">S2</span>
      <?php if ($system === 'rubber_set'): ?>
        <span class="w-6 text-slate-400" title="Set 3 (Rubber Game)">S3</span>
      <?php endif; ?>
    </div>
  </div>

  <!-- Tim 1 -->
  <?php $m1Members = parse_team_members($m['team1_members'] ?? null); ?>
  <div class="team <?= $w && $w == $m['team1_id'] ? 'win' : '' ?> flex items-center justify-between border-b border-slate-800/60 px-3 py-2 gap-2">
    <div class="min-w-0 flex-1">
      <div class="truncate text-xs font-bold leading-snug <?= $w && $w == $m['team1_id'] ? '!text-emerald-300' : 'text-slate-100' ?>" title="<?= !empty($m['team1_name']) ? e($m['team1_name']) : 'TBD' ?>">
        <?= !empty($m['team1_name']) ? e($m['team1_name']) : '<i class="text-slate-500 font-normal">TBD</i>' ?>
      </div>
      <?php if (!empty($m1Members)): ?>
        <div class="mt-0.5 space-y-0.5 leading-tight">
          <?php foreach ($m1Members as $member): ?>
            <div class="truncate text-[10px] text-slate-400 font-normal flex items-center gap-1" title="<?= e($member) ?>">
              <span class="text-[9px] text-slate-600">👤</span>
              <span class="truncate"><?= e($member) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
    <div class="flex items-center gap-1.5 font-mono text-xs shrink-0 self-center">
      <span class="w-6 text-center py-0.5 rounded border border-slate-800/80 <?= ($win1_a && $played1) ? 'bg-emerald-950/60 text-emerald-300 font-bold border-emerald-500/30' : ($col1_a === '-' ? 'text-slate-500 bg-slate-950/40' : 'text-slate-200 bg-slate-950/70') ?>">
        <?= $col1_a ?>
      </span>
      <span class="w-6 text-center py-0.5 rounded border border-slate-800/80 <?= ($win2_a && $col2_a !== '-') ? 'bg-emerald-950/60 text-emerald-300 font-bold border-emerald-500/30' : ($col2_a === '-' ? 'text-slate-500 bg-slate-950/40' : 'text-slate-200 bg-slate-950/70') ?>">
        <?= $col2_a ?>
      </span>
      <?php if ($system === 'rubber_set'): ?>
        <span class="w-6 text-center py-0.5 rounded border border-slate-800/80 <?= ($win3_a && $played3 && $is1_1) ? 'bg-emerald-950/60 text-emerald-300 font-bold border-emerald-500/30' : ($col3_a === '-' ? 'text-slate-500 bg-slate-950/40' : 'text-slate-200 bg-slate-950/70') ?>">
          <?= $col3_a ?>
        </span>
      <?php endif; ?>
    </div>
  </div>

  <!-- Tim 2 -->
  <?php $m2Members = parse_team_members($m['team2_members'] ?? null); ?>
  <div class="team <?= $w && $w == $m['team2_id'] ? 'win' : '' ?> flex items-center justify-between px-3 py-2 gap-2">
    <div class="min-w-0 flex-1">
      <div class="truncate text-xs font-bold leading-snug <?= $w && $w == $m['team2_id'] ? '!text-emerald-300' : 'text-slate-100' ?>" title="<?= !empty($m['team2_name']) ? e($m['team2_name']) : 'TBD' ?>">
        <?= !empty($m['team2_name']) ? e($m['team2_name']) : '<i class="text-slate-500 font-normal">TBD</i>' ?>
      </div>
      <?php if (!empty($m2Members)): ?>
        <div class="mt-0.5 space-y-0.5 leading-tight">
          <?php foreach ($m2Members as $member): ?>
            <div class="truncate text-[10px] text-slate-400 font-normal flex items-center gap-1" title="<?= e($member) ?>">
              <span class="text-[9px] text-slate-600">👤</span>
              <span class="truncate"><?= e($member) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
    <div class="flex items-center gap-1.5 font-mono text-xs shrink-0 self-center">
      <span class="w-6 text-center py-0.5 rounded border border-slate-800/80 <?= ($win1_b && $played1) ? 'bg-emerald-950/60 text-emerald-300 font-bold border-emerald-500/30' : ($col1_b === '-' ? 'text-slate-500 bg-slate-950/40' : 'text-slate-200 bg-slate-950/70') ?>">
        <?= $col1_b ?>
      </span>
      <span class="w-6 text-center py-0.5 rounded border border-slate-800/80 <?= ($win2_b && $col2_b !== '-') ? 'bg-emerald-950/60 text-emerald-300 font-bold border-emerald-500/30' : ($col2_b === '-' ? 'text-slate-500 bg-slate-950/40' : 'text-slate-200 bg-slate-950/70') ?>">
        <?= $col2_b ?>
      </span>
      <?php if ($system === 'rubber_set'): ?>
        <span class="w-6 text-center py-0.5 rounded border border-slate-800/80 <?= ($win3_b && $played3 && $is1_1) ? 'bg-emerald-950/60 text-emerald-300 font-bold border-emerald-500/30' : ($col3_b === '-' ? 'text-slate-500 bg-slate-950/40' : 'text-slate-200 bg-slate-950/70') ?>">
          <?= $col3_b ?>
        </span>
      <?php endif; ?>
    </div>
  </div>

  <!-- Footer: Hari, Tanggal, Jam, dan Status Pemenang jika Selesai -->
  <div class="mfoot flex items-center justify-between gap-1 px-3 py-1.5 bg-slate-950/60 border-t border-slate-800/80 text-[11px] text-slate-400">
    <span class="truncate flex items-center gap-1.5">
      <span>📅</span>
      <span><?= e(fmt_dt($m['scheduled_at'])) ?></span>
    </span>
    <?php if ($w): ?>
      <span class="text-[10px] font-bold text-emerald-400 shrink-0">
        Menang: <?= e($m['winner_name'] ?? 'Pemenang') ?>
      </span>
    <?php endif; ?>
  </div>
</div>
