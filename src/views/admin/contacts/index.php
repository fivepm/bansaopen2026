<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
  <div>
    <h1 class="text-2xl font-black text-white">Pengaturan Kontak &amp; Sosial Media</h1>
    <p class="text-xs sm:text-sm text-slate-400">Kelola akun dan nomor resmi turnamen. Satu platform dapat memiliki lebih dari satu akun (contoh: 2 nomor WhatsApp atau 2 akun Instagram).</p>
  </div>
  <a href="<?= url('admin/contacts/create') ?>" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-bold text-white shadow-lg shadow-blue-600/30 hover:bg-blue-500 transition">
    <span>+</span>
    <span>Tambah Akun Kontak Baru</span>
  </a>
</div>

<!-- Tabel Daftar Akun Kontak -->
<div class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/90 shadow-xl shadow-black/20">
  <div class="overflow-x-auto">
    <table class="w-full text-left text-xs sm:text-sm">
      <thead class="border-b border-slate-800 bg-slate-950/60 text-[11px] font-bold uppercase tracking-wider text-slate-400">
        <tr>
          <th class="px-5 py-3.5 w-12 text-center">No</th>
          <th class="px-5 py-3.5 w-44">Platform</th>
          <th class="px-5 py-3.5">Nama Akun / Nomor</th>
          <th class="px-5 py-3.5">Tautan / Target URL</th>
          <th class="px-5 py-3.5 w-24 text-center">Urutan</th>
          <th class="px-5 py-3.5 w-32 text-right">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-800/80 text-slate-300">
        <?php if (empty($contacts)): ?>
          <tr>
            <td colspan="6" class="px-5 py-12 text-center text-slate-500">
              <span class="block text-3xl mb-2">📱</span>
              Belum ada akun kontak atau sosial media yang ditambahkan.<br>
              <a href="<?= url('admin/contacts/create') ?>" class="mt-3 inline-block font-semibold text-blue-400 hover:underline">
                Tambah Akun Kontak Pertama →
              </a>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($contacts as $i => $c): ?>
            <tr class="hover:bg-slate-800/40 transition">
              <td class="px-5 py-3.5 text-center text-slate-500 font-mono"><?= $i + 1 ?></td>
              <td class="px-5 py-3.5 whitespace-nowrap">
                <span class="inline-flex items-center gap-2 rounded-lg border px-2.5 py-1 text-xs font-bold <?= $c['platform_badge'] ?>">
                  <i class="bi <?= $c['platform_icon'] ?>"></i>
                  <span><?= e($c['platform_label']) ?></span>
                </span>
              </td>
              <td class="px-5 py-3.5 font-bold text-white">
                <?= e($c['account_name']) ?>
              </td>
              <td class="px-5 py-3.5 text-xs text-slate-400 font-mono">
                <a href="<?= e($c['formatted_url']) ?>" target="_blank" rel="noopener noreferrer" class="hover:text-blue-400 hover:underline inline-flex items-center gap-1.5" title="Uji klik tautan">
                  <span><?= e($c['url']) ?></span>
                  <i class="bi bi-box-arrow-up-right text-[10px] text-slate-500"></i>
                </a>
              </td>
              <td class="px-5 py-3.5 text-center text-slate-400 font-mono text-xs">
                <?= (int)$c['sort_order'] ?>
              </td>
              <td class="px-5 py-3.5 text-right whitespace-nowrap">
                <div class="inline-flex items-center gap-1.5">
                  <a href="<?= url('admin/contacts/' . $c['id'] . '/edit') ?>" class="rounded-lg border border-slate-700 bg-slate-800 px-2.5 py-1 text-xs font-semibold text-slate-300 hover:border-slate-600 hover:text-white transition">
                    Edit
                  </a>
                  <form method="post" action="<?= url('admin/contacts/' . $c['id'] . '/delete') ?>" onsubmit="return confirm('Yakin ingin menghapus akun kontak &quot;<?= e(addslashes($c['account_name'])) ?>&quot;?')">
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

<!-- Pratinjau Tampilan Footer Publik -->
<div class="mt-8 rounded-3xl border border-slate-800 bg-slate-900/90 p-6 shadow-xl shadow-black/20">
  <div class="flex items-center justify-between border-b border-slate-800/80 pb-3 mb-4">
    <div>
      <h3 class="text-sm font-extrabold text-white flex items-center gap-2">
        <span>👁️</span>
        <span>Pratinjau Footer Halaman Publik</span>
      </h3>
      <p class="text-xs text-slate-400">Berikut simulasi tampilan tombol kontak dan akun yang berjejer rapi di bagian bawah halaman beranda:</p>
    </div>
    <span class="rounded-full bg-blue-600/15 border border-blue-500/30 px-2.5 py-0.5 text-[10px] font-bold text-blue-400">
      Live Footer Preview
    </span>
  </div>

  <div class="rounded-2xl border border-slate-800/80 bg-[#070a12] p-6 text-center">
    <div class="inline-flex items-center gap-2 rounded-full border border-slate-800 bg-slate-900/80 px-3 py-1 text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-4 shadow-inner">
      <span>📱</span>
      <span>Hubungi &amp; Ikuti Kami</span>
    </div>

    <?php if (empty($contacts)): ?>
      <p class="text-xs italic text-slate-600">Belum ada akun kontak yang aktif.</p>
    <?php else: ?>
      <div class="flex flex-wrap items-center justify-center gap-3">
        <?php foreach ($contacts as $c): ?>
          <div class="group flex items-center gap-2.5 rounded-2xl border <?= $c['platform_badge'] ?> px-4 py-2.5 shadow-md shadow-black/20">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl <?= $c['platform_bg'] ?> text-white text-base shadow">
              <i class="bi <?= $c['platform_icon'] ?>"></i>
            </span>
            <div class="text-left leading-tight">
              <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">
                <?= e($c['platform_label']) ?>
              </div>
              <div class="text-xs font-bold text-white">
                <?= e($c['account_name']) ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>
