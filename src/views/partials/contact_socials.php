<?php
$contacts = \App\Models\Contact::all();
?>
<?php if (!empty($contacts)): ?>
  <!-- Section Informasi Kontak & Sosial Media Resmi -->
  <section id="kontak" class="mt-14 mb-4 flex flex-col items-center justify-center text-center">
    <div class="inline-flex items-center gap-2 rounded-full border border-slate-800 bg-slate-900/80 px-3.5 py-1 text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-4 shadow-inner">
      <span>📱</span>
      <span>Hubungi &amp; Ikuti Kami</span>
    </div>

    <!-- Daftar Akun Kontak & Sosmed (Berjejer Rapi dengan Ikon dan Nama Akun/Nomor) -->
    <div class="flex flex-wrap items-center justify-center gap-3 sm:gap-4 max-w-5xl mx-auto">
      <?php foreach ($contacts as $c): ?>
        <a href="<?= e($c['formatted_url']) ?>"
           target="_blank"
           rel="noopener noreferrer"
           aria-label="<?= e($c['platform_label']) ?>: <?= e($c['account_name']) ?>"
           class="group flex items-center gap-3 rounded-2xl border <?= $c['platform_badge'] ?> bg-slate-900/90 px-4 py-2.5 shadow-lg shadow-black/20 transition-all duration-300 hover:-translate-y-1 hover:border-slate-600 hover:bg-slate-800/90 hover:shadow-xl">
          <!-- Ikon Aplikasi -->
          <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl <?= $c['platform_bg'] ?> text-white text-lg shadow-md transition-transform duration-300 group-hover:scale-110">
            <i class="bi <?= $c['platform_icon'] ?>"></i>
          </span>

          <!-- Teks Nama Akun / Nomor di samping ikon -->
          <div class="text-left leading-tight">
            <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">
              <?= e($c['platform_label']) ?>
            </div>
            <div class="text-xs sm:text-sm font-bold text-white group-hover:text-blue-500 dark:group-hover:text-blue-300 transition-colors">
              <?= e($c['account_name']) ?>
            </div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </section>
<?php endif; ?>
