<?php /** @var array $g grup, @var array $rows klasemen terurut */ ?>
<div class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/90 shadow-xl shadow-black/20">
  <div class="flex items-center justify-between border-b border-slate-800 bg-slate-950/80 px-5 py-3">
    <div class="flex items-center gap-2">
      <span class="flex h-3 w-3 rounded-full bg-blue-500 shadow-sm shadow-blue-500/50"></span>
      <h3 class="font-extrabold text-white text-base"><?= e($g['name']) ?></h3>
    </div>
    <span class="rounded-full bg-slate-800 px-2.5 py-0.5 text-xs font-medium text-slate-400"><?= count($rows) ?> Tim</span>
  </div>
  <div class="overflow-x-auto">
    <table class="w-full text-left text-sm text-slate-300">
      <thead class="bg-slate-950/50 text-xs uppercase tracking-wider text-slate-400 border-b border-slate-800">
        <tr>
          <th class="px-4 py-3 text-center w-12">#</th>
          <th class="px-4 py-3">Tim</th>
          <th class="px-3 py-3 text-center" title="Main">M</th>
          <th class="px-3 py-3 text-center text-emerald-400" title="Menang">W</th>
          <th class="px-3 py-3 text-center text-amber-400" title="Seri">D</th>
          <th class="px-3 py-3 text-center text-rose-400" title="Kalah">L</th>
          <th class="px-3 py-3 text-center" title="Selisih Skor">+/-</th>
          <th class="px-4 py-3 text-center font-bold text-blue-400" title="Poin">PTS</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-800/60">
        <?php foreach ($rows as $i => $r): ?>
          <tr class="transition-colors hover:bg-slate-800/40 <?= $i === 0 && $r['played'] > 0 ? 'bg-emerald-950/20 text-emerald-200' : '' ?>">
            <td class="px-4 py-3 text-center font-semibold">
              <?php if ($i === 0 && $r['played'] > 0): ?>
                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500/20 text-xs font-bold text-emerald-400">1</span>
              <?php else: ?>
                <span class="text-slate-500 text-xs"><?= $i + 1 ?></span>
              <?php endif; ?>
            </td>
            <td class="px-4 py-3 font-semibold text-white">
              <?= e($r['team_name']) ?>
            </td>
            <td class="px-3 py-3 text-center text-slate-400"><?= (int)$r['played'] ?></td>
            <td class="px-3 py-3 text-center font-medium text-emerald-400"><?= (int)$r['won'] ?></td>
            <td class="px-3 py-3 text-center font-medium text-amber-400"><?= (int)$r['drawn'] ?></td>
            <td class="px-3 py-3 text-center font-medium text-rose-400"><?= (int)$r['lost'] ?></td>
            <td class="px-3 py-3 text-center font-mono text-xs text-slate-300">
              <?= (int)$r['goal_diff'] > 0 ? '+' : '' ?><?= (int)$r['goal_diff'] ?>
            </td>
            <td class="px-4 py-3 text-center font-mono text-base font-extrabold text-blue-400">
              <?= (int)$r['points'] ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?>
          <tr><td colspan="8" class="px-4 py-6 text-center text-sm text-slate-500 italic">Belum ada tim di grup ini.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <div class="border-t border-slate-800/60 bg-slate-950/40 px-5 py-2.5 text-xs text-slate-500 flex flex-wrap items-center justify-between gap-2">
    <span>M: Main · W: Menang (<?= \App\Models\Standing::POINTS_WIN ?>) · D: Seri (<?= \App\Models\Standing::POINTS_DRAW ?>) · L: Kalah (<?= \App\Models\Standing::POINTS_LOSS ?>)</span>
    <span>Urutan: Poin → Selisih Skor → Skor Dicetak</span>
  </div>
</div>
