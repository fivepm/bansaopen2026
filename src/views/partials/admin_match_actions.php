<?php
/**
 * Aksi per kartu pertandingan di halaman Bracket / Admin.
 * 1. Tombol Mandiri "Set Live" (jika status Belum Mulai / upcoming dan tim lengkap).
 * 2. Tombol "Ubah Status" (membuka modal status, sistem set, dan jadwal).
 * 3. Tombol "Atur Tim" (khusus babak penyisihan / First Round).
 */
$isFirstRound = ($m['stage'] === 'knockout' && (int)$m['round'] === 1 && !in_array($m['bracket_side'] ?? '', ['final', 'third_place'], true));
$hasTeams = (!empty($m['team1_id']) && !empty($m['team2_id']));
?>
<div class="admin-actions flex flex-wrap items-center justify-between gap-1.5 mt-2 pt-2 border-t border-slate-800/60">
  <div class="flex flex-wrap items-center gap-1.5">
    
    <!-- 1. Tombol Mandiri "Set Live" -->
    <?php if ($m['status'] === 'upcoming'): ?>
      <?php if ($hasTeams): ?>
        <form method="post" action="<?= url("admin/$sport/matches/{$m['id']}/set-live") ?>" class="inline m-0" onsubmit="return confirm('Mulai pertandingan ini sekarang dan jadikan LIVE?')">
          <?= csrf_field() ?>
          <button type="submit" class="btn small !py-1 !px-2.5 !bg-red-600 hover:!bg-red-500 text-white font-bold shadow-md shadow-red-600/30 text-xs flex items-center gap-1.5">
            <span class="h-1.5 w-1.5 rounded-full bg-white animate-ping"></span>
            <span>Set Live</span>
          </button>
        </form>
      <?php else: ?>
        <button type="button" disabled class="btn small !py-1 !px-2 text-slate-500 bg-slate-800/60 border border-slate-700/60 cursor-not-allowed text-xs" title="Kedua tim harus ditentukan sebelum bisa di-set Live">
          Set Live
        </button>
      <?php endif; ?>
    <?php elseif ($m['status'] === 'live'): ?>
      <a class="btn small !bg-red-600 hover:!bg-red-500 text-white font-bold shadow-md shadow-red-600/30 animate-pulse !py-1 !px-2.5 text-xs flex items-center gap-1.5" href="<?= url('admin/live-panel') ?>">
        <span>⚡ Live Panel</span>
      </a>
    <?php endif; ?>

    <!-- 2. Tombol "Ubah Status" (Modal Status, Sistem Set, & Jadwal) -->
    <button type="button" 
            class="btn small ghost text-xs !py-1 !px-2.5 text-slate-200 hover:text-white hover:border-slate-500 open-status-modal-btn"
            data-match-id="<?= (int)$m['id'] ?>"
            data-match-title="#<?= (int)$m['round'] ?>-<?= (int)$m['position'] ?><?= !empty($m['court']) ? ' · ' . e($m['court']) : '' ?>"
            data-team1-id="<?= (int)($m['team1_id'] ?? 0) ?>"
            data-team2-id="<?= (int)($m['team2_id'] ?? 0) ?>"
            data-team1-name="<?= e($m['team1_name'] ?? 'TBD') ?>"
            data-team2-name="<?= e($m['team2_name'] ?? 'TBD') ?>"
            data-status="<?= e($m['status']) ?>"
            data-scoring-system="<?= e($m['scoring_system'] ?? 'rubber_set') ?>"
            data-scheduled-at="<?= e($m['scheduled_at'] ?? '') ?>"
            data-court="<?= e($m['court'] ?? '') ?>"
            data-set1-a="<?= (int)($m['set1_a'] ?? 0) ?>"
            data-set1-b="<?= (int)($m['set1_b'] ?? 0) ?>"
            data-set2-a="<?= (int)($m['set2_a'] ?? 0) ?>"
            data-set2-b="<?= (int)($m['set2_b'] ?? 0) ?>"
            data-set3-a="<?= (int)($m['set3_a'] ?? 0) ?>"
            data-set3-b="<?= (int)($m['set3_b'] ?? 0) ?>"
    >
      ⚙️ Ubah Status
    </button>

    <!-- 3. Tombol "Atur Tim" (Khusus Babak Penyisihan) -->
    <?php if ($isFirstRound): ?>
      <button type="button" 
              class="btn small ghost text-xs !py-1 !px-2.5 text-blue-400 hover:text-white border-blue-500/40 hover:bg-blue-600/30 open-teams-modal-btn"
              data-match-id="<?= (int)$m['id'] ?>"
              data-match-title="#<?= (int)$m['round'] ?>-<?= (int)$m['position'] ?><?= !empty($m['court']) ? ' · ' . e($m['court']) : '' ?>"
              data-team1-id="<?= (int)($m['team1_id'] ?? 0) ?>"
              data-team2-id="<?= (int)($m['team2_id'] ?? 0) ?>"
      >
        👥 Atur Tim
      </button>
    <?php endif; ?>
  </div>

  <div class="flex items-center gap-1">
    <?php if ($m['status'] === 'finished'): ?>
      <span class="badge finished text-[11px] !py-0.5">✓ Selesai</span>
    <?php elseif (!$hasTeams && $m['status'] === 'upcoming'): ?>
      <span class="text-[11px] text-slate-500 italic">Menunggu Tim</span>
    <?php endif; ?>
  </div>
</div>
