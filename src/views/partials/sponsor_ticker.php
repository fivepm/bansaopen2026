<?php
/**
 * Ticker Sponsor Resmi Kami - Animasi CSS Murni 2 Baris Berlawanan Arah.
 * Baris 1: Kanan ke Kiri
 * Baris 2: Kiri ke Kanan
 * Menampilkan seluruh logo sponsor secara dinamis dan seimbang.
 * @var array $sponsors
 */
if (empty($sponsors)) {
    return;
}

// Pastikan indeks tersusun rapi
$sponsors = array_values($sponsors);
$totalSponsors = count($sponsors);

// Baris 1: Urutan normal
$row1 = $sponsors;

// Baris 2: Urutan berlawanan / di-offset agar kedua baris tidak monoton dan bervariasi
if ($totalSponsors > 1) {
    $shift = (int)ceil($totalSponsors / 2);
    $row2 = array_merge(array_slice($sponsors, $shift), array_slice($sponsors, 0, $shift));
} else {
    $row2 = $sponsors;
}

// Gandakan elemen agar track cukup panjang melintasi layar sebelum animasi berulang (looping mulus)
$prepareTrack = static function (array $list): array {
    $minItems = 8;
    $multiplier = max(1, (int)ceil($minItems / max(1, count($list))));
    $half = [];
    for ($i = 0; $i < $multiplier; $i++) {
        $half = array_merge($half, $list);
    }
    return $half;
};

$half1 = $prepareTrack($row1);
$half2 = $prepareTrack($row2);

// Durasi animasi proporsional terhadap jumlah item (halus, stabil, dan tidak terlalu cepat)
$dur1 = max(24, (int)(count($half1) * 3.5));
$dur2 = max(24, (int)(count($half2) * 3.5));
?>
<section class="mt-14">
  <div class="mb-6 text-center">
    <span class="inline-block text-xs font-bold uppercase tracking-widest text-blue-400">Dukungan &amp; Kemitraan</span>
    <h2 class="mt-1 text-xl sm:text-2xl font-black tracking-tight text-white">Sponsor Resmi Kami</h2>
    <p class="text-xs text-slate-400">Terima kasih kepada seluruh mitra dan pihak sponsor yang mendukung suksesnya turnamen <?= e($_ENV['APP_NAME'] ?? 'Bansa Open 2026') ?>.</p>
  </div>

  <div class="ticker relative overflow-hidden rounded-2xl border border-slate-800/90 bg-slate-900/50 py-5 space-y-4 shadow-xl backdrop-blur-md" aria-label="Sponsor Kami">
    <!-- Baris 1: Bergerak dari Kanan ke Kiri -->
    <div class="ticker-track-left flex w-max" style="--dur: <?= $dur1 ?>s">
      <?php foreach ([0, 1] as $copy): foreach ($half1 as $s): ?>
        <div class="ticker-item flex shrink-0 items-center justify-center px-2.5" <?= $copy ? 'aria-hidden="true"' : '' ?>>
          <div class="flex h-16 w-36 items-center justify-center rounded-xl border border-slate-700/50 bg-slate-800/60 p-2.5 shadow-sm transition-all hover:border-blue-500/50 hover:bg-slate-800">
            <img src="<?= e(asset($s['logo_path'])) ?>" alt="<?= $copy ? '' : e($s['name']) ?>" title="<?= e($s['name']) ?>" loading="lazy" class="max-h-full max-w-full object-contain filter drop-shadow">
          </div>
        </div>
      <?php endforeach; endforeach; ?>
    </div>

    <!-- Baris 2: Bergerak dari Kiri ke Kanan -->
    <div class="ticker-track-right flex w-max" style="--dur: <?= $dur2 ?>s">
      <?php foreach ([0, 1] as $copy): foreach ($half2 as $s): ?>
        <div class="ticker-item flex shrink-0 items-center justify-center px-2.5" <?= $copy ? 'aria-hidden="true"' : '' ?>>
          <div class="flex h-16 w-36 items-center justify-center rounded-xl border border-slate-700/50 bg-slate-800/60 p-2.5 shadow-sm transition-all hover:border-indigo-500/50 hover:bg-slate-800">
            <img src="<?= e(asset($s['logo_path'])) ?>" alt="<?= $copy ? '' : e($s['name']) ?>" title="<?= e($s['name']) ?>" loading="lazy" class="max-h-full max-w-full object-contain filter drop-shadow">
          </div>
        </div>
      <?php endforeach; endforeach; ?>
    </div>
  </div>
</section>
