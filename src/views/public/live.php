<p class="muted">🔴 Halaman diperbarui otomatis setiap 10 detik.</p>
<?php if (!$matches): ?>
  <p class="card empty">Tidak ada pertandingan yang sedang berlangsung.</p>
<?php else: ?>
<div class="grid">
  <?php foreach ($matches as $m): ?>
    <div><div class="muted small-t"><?= e(match_stage_label($m, $total)) ?></div><?php include dirname(__DIR__) . '/partials/match_card.php'; ?></div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
