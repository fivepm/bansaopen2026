<?php if (!$rounds): ?>
  <p class="card empty">Bagan belum dibuat oleh panitia.</p>
<?php else: ?>
<div class="bracket">
  <?php foreach ($rounds as $r => $matches): ?>
    <section class="round">
      <div class="bracket-round-header mb-3 rounded-xl border border-blue-500 bg-gradient-to-r from-blue-600 to-blue-700 dark:from-blue-600/60 dark:to-blue-800/80 py-2 text-center shadow-md shadow-blue-500/20">
        <h3 class="text-xs font-black uppercase tracking-wider text-white !text-white drop-shadow" style="color: #ffffff !important;"><?= e(round_name($r, $total)) ?></h3>
      </div>
      <div class="round-body">
        <?php foreach ($matches as $m): ?><div class="slot"><?php include dirname(__DIR__) . '/partials/match_card.php'; ?></div><?php endforeach; ?>
      </div>
    </section>
  <?php endforeach; ?>
</div>
<?php endif; ?>
