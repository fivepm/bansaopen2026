<?php
/**
 * @var array $standings
 * @var array $groupMatches
 * @var string $sport
 */
?>
<div class="mb-5">
  <h2 class="text-xl sm:text-2xl font-black text-white">Klasemen &amp; Jadwal Fase Grup</h2>
  <p class="text-xs sm:text-sm text-slate-400">Tabel klasemen dan jadwal pertandingan fase grup diperbarui secara otomatis dan real-time.</p>
</div>

<?php if (empty($standings)): ?>
  <div class="rounded-2xl border border-dashed border-slate-800 bg-slate-900/40 p-10 text-center text-slate-500">
    Grup turnamen belum dibuat oleh panitia.
  </div>
<?php else: ?>
  <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <?php foreach ($standings as $gid => $s): $g = $s['group']; $rows = $s['rows']; ?>
      <div class="space-y-4">
        <?php include dirname(__DIR__) . '/partials/standings_table.php'; ?>

        <details class="group rounded-2xl border border-slate-800 bg-slate-900/70 p-4 shadow-md transition-all" open>
          <summary class="flex cursor-pointer items-center justify-between font-bold text-sm text-slate-200 hover:text-white">
            <span>📅 Jadwal &amp; Skor Pertandingan <?= e($g['name']) ?></span>
            <span class="text-xs text-blue-400 group-open:rotate-180 transition-transform">▼</span>
          </summary>
          <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
            <?php foreach ($groupMatches[$gid] ?? [] as $m): $total = 0; ?>
              <div>
                <div class="mb-1 text-xs text-slate-400 font-semibold">
                  <?= $sport === 'futsal' ? 'Match ke-' : 'Hari ke-' ?><?= (int)$m['round'] ?>
                </div>
                <?php include dirname(__DIR__) . '/partials/match_card.php'; ?>
              </div>
            <?php endforeach; ?>
            <?php if (empty($groupMatches[$gid])): ?>
              <p class="text-xs italic text-slate-500 col-span-2">Jadwal pertandingan grup ini belum dibuat.</p>
            <?php endif; ?>
          </div>
        </details>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

