<?php if (!$rounds): ?>
  <p class="card empty">Bagan belum dibuat oleh panitia.</p>
<?php else: ?>
<div class="bracket">
  <?php foreach ($rounds as $r => $matches): ?>
    <section class="round">
      <h3><?= e(round_name($r, $total)) ?></h3>
      <div class="round-body">
        <?php foreach ($matches as $m): ?><div class="slot"><?php include dirname(__DIR__) . '/partials/match_card.php'; ?></div><?php endforeach; ?>
      </div>
    </section>
  <?php endforeach; ?>
</div>
<?php endif; ?>
