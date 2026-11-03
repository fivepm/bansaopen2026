<?php if (!$matches): ?>
  <p class="card empty">Belum ada pertandingan yang selesai.</p>
<?php else: ?>
<div class="grid">
  <?php foreach ($matches as $m): ?>
    <div><div class="muted small-t"><?= e(match_stage_label($m, $total)) ?><?= $m['winner_name'] ? ' · Pemenang: <b>' . e($m['winner_name']) . '</b>' : ' · Seri' ?></div><?php include dirname(__DIR__) . '/partials/match_card.php'; ?></div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
