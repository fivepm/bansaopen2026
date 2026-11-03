<?php if (!$standings): ?>
  <p class="card empty">Grup belum dibuat oleh panitia.</p>
<?php else: ?>
<div class="grid !grid-cols-1 lg:!grid-cols-2">
  <?php foreach ($standings as $gid => $s): $g = $s['group']; $rows = $s['rows']; ?>
    <div class="space-y-3">
      <?php include dirname(__DIR__) . '/partials/standings_table.php'; ?>
      <details class="card !p-3">
        <summary class="cursor-pointer font-semibold">Jadwal &amp; hasil <?= e($g['name']) ?></summary>
        <div class="mt-3 grid !grid-cols-1 sm:!grid-cols-2">
          <?php foreach ($matches[$gid] ?? [] as $m): $total = 0; ?>
            <div><div class="muted small-t">Hari ke-<?= (int)$m['round'] ?></div><?php include dirname(__DIR__) . '/partials/match_card.php'; ?></div>
          <?php endforeach; ?>
          <?php if (empty($matches[$gid])): ?><p class="muted">Jadwal belum dibuat.</p><?php endif; ?>
        </div>
      </details>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

