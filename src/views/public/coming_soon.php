<div class="relative my-12 flex flex-col items-center justify-center overflow-hidden rounded-3xl border border-slate-800 bg-gradient-to-b from-slate-900 to-slate-950 p-12 text-center shadow-2xl">
  <div class="pointer-events-none absolute h-64 w-64 rounded-full bg-blue-600/10 blur-3xl"></div>
  
  <div class="relative z-10 flex h-24 w-24 items-center justify-center rounded-3xl bg-slate-800/80 text-5xl shadow-inner">
    <?= \App\Models\Sports::emoji($sport) ?>
  </div>

  <h1 class="relative z-10 mt-6 text-3xl font-black text-white">
    Cabang Olahraga <?= e(sport_label($sport)) ?>
  </h1>

  <div class="relative z-10 mt-3 inline-flex items-center gap-1.5 rounded-full border border-slate-700 bg-slate-800/80 px-4 py-1 text-xs font-bold text-slate-300">
    <span>⏳</span>
    <span>BELUM DIMULAI</span>
  </div>

  <p class="relative z-10 mt-3 max-w-md text-sm text-slate-400">
    Data tim dan bagan pertandingan untuk cabang olahraga ini belum diatur oleh panitia pelaksana.
  </p>

  <div class="relative z-10 mt-8">
    <a class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-blue-600/30 transition hover:bg-blue-500" href="<?= url('/') ?>">
      ← Kembali ke Beranda
    </a>
  </div>
</div>
