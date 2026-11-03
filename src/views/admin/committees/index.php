<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
  <div>
    <h1 class="text-2xl font-black text-white">Manajemen Susunan Kepanitiaan</h1>
    <p class="text-xs sm:text-sm text-slate-400">Kelola susunan panitia Bansa Open 2026. Satu orang dapat merangkap beberapa jabatan/divisi sekaligus.</p>
  </div>
  <a href="<?= url('admin/committees/create') ?>" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-bold text-white shadow-lg shadow-blue-600/30 hover:bg-blue-500 transition">
    <span>+</span>
    <span>Tambah Anggota Panitia</span>
  </a>
</div>

<div class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/90 shadow-xl shadow-black/20">
  <div class="overflow-x-auto">
    <table class="w-full text-left text-xs sm:text-sm">
      <thead class="border-b border-slate-800 bg-slate-950/60 text-[11px] font-bold uppercase tracking-wider text-slate-400">
        <tr>
          <th class="px-5 py-3.5 w-12 text-center">No</th>
          <th class="px-5 py-3.5">Nama Anggota</th>
          <th class="px-5 py-3.5">Jabatan / Divisi</th>
          <th class="px-5 py-3.5 w-40">Kontak (HP/WA)</th>
          <th class="px-5 py-3.5 w-32 text-right">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-800/80 text-slate-300">
        <?php if (empty($committees)): ?>
          <tr>
            <td colspan="5" class="px-5 py-12 text-center text-slate-500">
              <span class="block text-3xl mb-2">👥</span>
              Belum ada data panitia yang ditambahkan.<br>
              <a href="<?= url('admin/committees/create') ?>" class="mt-3 inline-block font-semibold text-blue-400 hover:underline">
                Tambah Panitia Pertama Sekarang →
              </a>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($committees as $i => $c): ?>
            <tr class="hover:bg-slate-800/40 transition">
              <td class="px-5 py-3.5 text-center text-slate-500 font-mono"><?= $i + 1 ?></td>
              <td class="px-5 py-3.5 font-bold text-white">
                <div class="flex items-center gap-3">
                  <?php if (!empty($c['photo'])): ?>
                    <img src="<?= asset($c['photo']) ?>" alt="<?= e($c['name']) ?>" class="h-9 w-9 shrink-0 rounded-full object-cover border border-slate-700 shadow-sm">
                  <?php else: ?>
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-800 text-slate-500 border border-slate-700/80 shadow-inner">
                      <i class="bi bi-person-fill text-lg"></i>
                    </span>
                  <?php endif; ?>
                  <span><?= e($c['name']) ?></span>
                </div>
              </td>
              <td class="px-5 py-3.5">
                <?php if (!empty($c['role_names'])): ?>
                  <div class="flex flex-wrap gap-1.5">
                    <?php foreach (explode(', ', $c['role_names']) as $roleName): ?>
                      <?php
                        $isCore = in_array($roleName, ['Penanggung Jawab', 'Ketua Panitia', 'Wakil Ketua', 'Sekretaris', 'Bendahara'], true);
                        $isKoord = str_contains($roleName, 'Koordinator') || str_starts_with($roleName, 'PJ');
                      ?>
                      <span class="inline-flex items-center rounded-lg px-2.5 py-0.5 text-[11px] font-semibold border <?= $isCore ? 'border-amber-500/40 bg-amber-950/40 text-amber-300' : ($isKoord ? 'border-blue-500/30 bg-blue-950/40 text-blue-300' : 'border-slate-700 bg-slate-800 text-slate-300') ?>">
                        <?= e($roleName) ?>
                      </span>
                    <?php endforeach; ?>
                  </div>
                <?php else: ?>
                  <span class="italic text-slate-500">(Belum ada jabatan)</span>
                <?php endif; ?>
              </td>
              <td class="px-5 py-3.5 text-slate-400 font-mono text-xs">
                <?= !empty($c['phone']) ? e($c['phone']) : '<span class="text-slate-600">-</span>' ?>
              </td>
              <td class="px-5 py-3.5 text-right whitespace-nowrap">
                <div class="inline-flex items-center gap-1.5">
                  <a href="<?= url('admin/committees/' . $c['id'] . '/edit') ?>" class="rounded-lg border border-slate-700 bg-slate-800 px-2.5 py-1 text-xs font-semibold text-slate-300 hover:border-slate-600 hover:text-white transition">
                    Edit
                  </a>
                  <form method="post" action="<?= url('admin/committees/' . $c['id'] . '/delete') ?>" onsubmit="return confirm('Yakin ingin menghapus panitia &quot;<?= e(addslashes($c['name'])) ?>&quot;?')">
                    <?= csrf_field() ?>
                    <button type="submit" class="rounded-lg border border-red-500/30 bg-red-950/40 px-2.5 py-1 text-xs font-semibold text-red-400 hover:bg-red-900/60 transition">
                      Hapus
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

