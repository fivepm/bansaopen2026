<?php
/**
 * Konten Tab: Urutan Jadwal Pertandingan (Chronological Order of Play)
 * Murni sebagai papan informasi urutan main dan pembagian lapangan tanpa perolehan skor.
 * 
 * @var array $scheduleMatches
 * @var string $sport
 * @var array $categories
 */
$totalMatches = count($scheduleMatches);
$hasMultipleCategories = count($categories) > 1;

// Hitung total round babak knockout per kategori untuk penamaan babak akurat (16 Besar, Perempat Final, dll)
$totalRoundsPerCat = [];
foreach ($scheduleMatches as $sm) {
    if (($sm['stage'] ?? 'knockout') === 'knockout') {
        $c = $sm['category'] ?? 'umum';
        $r = (int)($sm['round'] ?? 1);
        if (!isset($totalRoundsPerCat[$c]) || $r > $totalRoundsPerCat[$c]) {
            $totalRoundsPerCat[$c] = $r;
        }
    }
}
?>

<div class="space-y-6">
  <!-- Header & Bar Kontrol Filter Jadwal -->
  <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
    <div>
      <div class="flex items-center gap-2">
        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-blue-600/20 text-base font-bold text-blue-400">
          📅
        </span>
        <h2 class="text-xl sm:text-2xl font-black text-white">
          Urutan Jadwal Pertandingan &amp; Lapangan
        </h2>
      </div>
      <p class="mt-1 text-xs sm:text-sm text-slate-400">
        Papan informasi urutan main (<span class="font-semibold text-slate-300">Order of Play</span>) berdasarkan waktu pertandingan untuk kesiapan atlet &amp; ofisial.
      </p>
    </div>

    <!-- Filter Buttons (Kategori & Status) -->
    <div class="flex flex-wrap items-center gap-2">
      <!-- Filter Kategori (khusus cabor dengan multi-kategori seperti Badminton) -->
      <?php if ($hasMultipleCategories): ?>
        <div class="flex items-center gap-1 rounded-2xl border border-slate-800 bg-slate-900/90 p-1 shadow-md">
          <button type="button"
                  id="btn-schedcat-all"
                  onclick="filterScheduleCategory('all')"
                  class="schedcat-btn rounded-xl px-3 py-1.5 text-xs font-bold transition-all bg-blue-600 text-white">
            Semua (<?= $totalMatches ?>)
          </button>
          <?php foreach ($categories as $ck => $cl): ?>
            <?php
              $countCat = count(array_filter($scheduleMatches, fn($m) => ($m['category'] ?? 'umum') === $ck));
            ?>
            <button type="button"
                    id="btn-schedcat-<?= $ck ?>"
                    onclick="filterScheduleCategory('<?= $ck ?>')"
                    class="schedcat-btn rounded-xl px-3 py-1.5 text-xs font-bold text-slate-400 hover:text-white transition-all">
              <?= e($cl) ?> (<?= $countCat ?>)
            </button>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- Filter Status Pertandingan -->
      <div class="flex items-center gap-1 rounded-2xl border border-slate-800 bg-slate-900/90 p-1 shadow-md">
        <button type="button"
                id="btn-schedstatus-all"
                onclick="filterScheduleStatus('all')"
                class="schedstatus-btn rounded-xl px-3 py-1.5 text-xs font-bold transition-all bg-slate-800 text-white">
          Semua Status
        </button>
        <button type="button"
                id="btn-schedstatus-live"
                onclick="filterScheduleStatus('live')"
                class="schedstatus-btn rounded-xl px-3 py-1.5 text-xs font-bold text-slate-400 hover:text-red-400 transition-all">
          🔴 Live
        </button>
        <button type="button"
                id="btn-schedstatus-upcoming"
                onclick="filterScheduleStatus('upcoming')"
                class="schedstatus-btn rounded-xl px-3 py-1.5 text-xs font-bold text-slate-400 hover:text-slate-200 transition-all">
          ⏳ Belum Mulai
        </button>
        <button type="button"
                id="btn-schedstatus-finished"
                onclick="filterScheduleStatus('finished')"
                class="schedstatus-btn rounded-xl px-3 py-1.5 text-xs font-bold text-slate-400 hover:text-emerald-400 transition-all">
          ✓ Selesai
        </button>
      </div>
    </div>
  </div>

  <?php if (empty($scheduleMatches)): ?>
    <div class="rounded-3xl border border-dashed border-slate-800 bg-slate-900/40 p-12 text-center text-slate-500">
      <span class="text-4xl">📅</span>
      <h3 class="mt-3 text-base font-bold text-slate-300">Belum Ada Jadwal Pertandingan</h3>
      <p class="mt-1 text-xs text-slate-500">Jadwal dan alokasi lapangan untuk cabang olahraga ini belum dirilis oleh panitia pelaksana.</p>
    </div>
  <?php else: ?>
    <!-- Daftar Kartu Pertandingan Urutan Main (Order of Play) -->
    <div class="space-y-4" id="schedule-cards-list">
      <?php foreach ($scheduleMatches as $idx => $m): ?>
        <?php
          $seqIndex = $idx + 1;
          $mCat = $m['category'] ?? 'umum';
          $mRounds = $totalRoundsPerCat[$mCat] ?? 0;
          $matchOrderName = match_order_name($m, $seqIndex, $mRounds);

          // Waktu dan jam pertandingan
          $hasTime = !empty($m['scheduled_at']);
          $timeFormatted = 'Waktu TBD';
          $dateFormatted = 'Jadwal belum ditentukan';
          if ($hasTime) {
              $ts = strtotime($m['scheduled_at']);
              $timeFormatted = date('H:i', $ts) . ' WIB';
              $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
              $day = $days[(int)date('w', $ts)];
              $d = date('d', $ts);
              $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
              $mo = $months[(int)date('n', $ts) - 1];
              $y = date('Y', $ts);
              $dateFormatted = "$day, $d $mo $y";
          }

          // Lapangan / Court
          $courtName = !empty($m['court']) ? $m['court'] : 'Lapangan TBD';

          // Tim A dan Tim B
          $hasT1 = !empty($m['team1_name']);
          $hasT2 = !empty($m['team2_name']);
          $p1 = match_team_placeholder($m, 1, $mRounds);
          $p2 = match_team_placeholder($m, 2, $mRounds);
          $t1Name = $hasT1 ? $m['team1_name'] : $p1;
          $t2Name = $hasT2 ? $m['team2_name'] : $p2;

          // Daftar anggota / pemain (memecah nama per baris)
          $t1Members = parse_team_members($m['team1_members'] ?? null);
          $t2Members = parse_team_members($m['team2_members'] ?? null);

          // Status Pertandingan
          $mStatus = $m['status'] ?? 'upcoming';
          $side = $m['bracket_side'] ?? 'left';
        ?>
        <div class="schedule-match-card rounded-2xl border border-slate-800 bg-slate-900/90 p-4 sm:p-5 shadow-xl shadow-black/20 transition-all hover:border-slate-700 hover:shadow-2xl <?= $mStatus === 'live' ? 'border-l-4 !border-l-red-500' : ($mStatus === 'finished' ? 'border-l-4 !border-l-emerald-500' : 'border-l-4 !border-l-blue-500') ?>"
             data-category="<?= e($mCat) ?>"
             data-status="<?= e($mStatus) ?>"
             id="schedule-card-<?= (int)$m['id'] ?>">

          <!-- Baris Atas: Nama Pertandingan, Kategori, Waktu, Lapangan, dan Status -->
          <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b border-slate-800/80 pb-3.5">
            <div class="flex flex-wrap items-center gap-2">
              <!-- Nama Pertandingan Utama (e.g. "Match 1-1 - Babak 16 Besar") -->
              <h3 class="text-sm sm:text-base font-black tracking-tight text-white flex items-center gap-2">
                <span class="flex h-6 w-auto px-2.5 items-center justify-center rounded-lg bg-gradient-to-r from-blue-600/30 via-indigo-600/30 to-blue-600/30 border border-blue-500/40 text-[11px] font-black text-blue-300 shrink-0 shadow-sm">
                  #<?= (int)($m['round'] ?? 1) ?>-<?= (int)($m['position'] ?? 1) ?>
                </span>
                <span><?= e($matchOrderName) ?></span>
              </h3>

              <!-- Keterangan Bagan Kiri/Kanan jika Sistem Knockout -->
              <?php if (($m['stage'] ?? 'knockout') === 'knockout'): ?>
                <?php if ($side === 'left'): ?>
                  <span class="rounded-md border border-slate-700/80 bg-slate-800/80 px-2 py-0.5 text-[10px] font-bold text-slate-300">
                    Bagan Kiri
                  </span>
                <?php elseif ($side === 'right'): ?>
                  <span class="rounded-md border border-slate-700/80 bg-slate-800/80 px-2 py-0.5 text-[10px] font-bold text-slate-300">
                    Bagan Kanan
                  </span>
                <?php elseif ($side === 'final'): ?>
                  <span class="rounded-md border border-amber-500/40 bg-amber-950/40 px-2 py-0.5 text-[10px] font-black text-amber-300">
                    🏆 Puncak Final
                  </span>
                <?php elseif ($side === 'third_place'): ?>
                  <span class="rounded-md border border-orange-500/40 bg-orange-950/40 px-2 py-0.5 text-[10px] font-black text-orange-300">
                    🥉 Juara 3
                  </span>
                <?php endif; ?>
              <?php endif; ?>

              <!-- Badge Kategori (Ganda Putra / Ganda Putri) -->
              <?php if ($hasMultipleCategories && !empty($categories[$mCat])): ?>
                <span class="rounded-md border border-indigo-500/30 bg-indigo-950/50 px-2 py-0.5 text-[10px] font-bold text-indigo-300">
                  <?= e($categories[$mCat]) ?>
                </span>
              <?php endif; ?>
            </div>

            <!-- Metadata Kanan: Jam/Waktu, Lapangan, dan Status -->
            <div class="flex flex-wrap items-center gap-2">
              <!-- Waktu & Jam Pertandingan -->
              <div class="flex items-center gap-1.5 rounded-xl border border-amber-500/35 bg-gradient-to-r from-amber-500/15 via-orange-500/10 to-amber-500/15 px-3 py-1 text-xs font-bold text-amber-300 shadow-sm"
                   title="<?= e($dateFormatted) ?>">
                <i class="bi bi-clock-fill text-amber-400"></i>
                <span class="font-mono"><?= e($timeFormatted) ?></span>
                <?php if ($hasTime): ?>
                  <span class="text-slate-600 hidden sm:inline">·</span>
                  <span class="text-[11px] font-medium text-slate-300 hidden sm:inline"><?= e($dateFormatted) ?></span>
                <?php endif; ?>
              </div>

              <!-- Nama Lapangan / Court -->
              <div class="flex items-center gap-1.5 rounded-xl border border-cyan-500/35 bg-gradient-to-r from-cyan-500/15 via-teal-500/10 to-cyan-500/15 px-3 py-1 text-xs font-bold text-cyan-300 shadow-sm">
                <i class="bi bi-geo-alt-fill text-cyan-400"></i>
                <span><?= e($courtName) ?></span>
              </div>

              <!-- Status Pertandingan (Belum Mulai, Live, Selesai) -->
              <?php if ($mStatus === 'live'): ?>
                <span class="inline-flex items-center gap-1.5 rounded-xl border border-red-500/60 bg-red-950/60 px-3 py-1 text-xs font-black text-red-300 animate-pulse shadow-sm shadow-red-500/20">
                  <span class="relative flex h-2 w-2">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-red-400 opacity-75"></span>
                    <span class="relative inline-flex h-2 w-2 rounded-full bg-red-500"></span>
                  </span>
                  <span>Live</span>
                </span>
              <?php elseif ($mStatus === 'finished'): ?>
                <span class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-500/40 bg-emerald-950/50 px-3 py-1 text-xs font-bold text-emerald-300 shadow-sm">
                  <i class="bi bi-check-circle-fill text-emerald-400"></i>
                  <span>Selesai</span>
                </span>
              <?php else: ?>
                <span class="inline-flex items-center gap-1.5 rounded-xl border border-slate-700 bg-slate-800/80 px-3 py-1 text-xs font-semibold text-slate-300 shadow-sm">
                  <i class="bi bi-hourglass-split text-slate-400"></i>
                  <span>Belum Mulai</span>
                </span>
              <?php endif; ?>
            </div>
          </div>

          <!-- Baris Bawah: Tim A VS Tim B beserta Daftar Anggota di bawahnya (MURNI TANPA SKOR) -->
          <div class="mt-4 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4 rounded-2xl border border-slate-800/80 bg-gradient-to-r from-slate-950/80 via-slate-900/60 to-slate-950/80 p-4 shadow-inner schedule-teams-box">
            
            <!-- SISI KIRI: TIM A -->
            <div class="flex-1 flex flex-col justify-center min-w-0">
              <div class="flex items-center gap-2.5">
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-cyan-500 text-xs font-black text-white shadow-md shadow-blue-500/30">
                  A
                </span>
                <div class="text-sm sm:text-base font-black text-white leading-tight break-words <?= $hasT1 ? '' : 'italic text-slate-400' ?>">
                  <?= e($t1Name) ?>
                </div>
              </div>

              <!-- Daftar Anggota Pemain Tim A -->
              <?php if (!empty($t1Members)): ?>
                <div class="mt-2.5 pl-9 space-y-1">
                  <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Daftar Pemain:</div>
                  <ul class="space-y-1">
                    <?php foreach ($t1Members as $member): ?>
                      <li class="flex items-center gap-2 text-xs text-slate-300 font-medium">
                        <span class="text-slate-500 text-[11px]">👤</span>
                        <span><?= e($member) ?></span>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                </div>
              <?php elseif (!$hasT1): ?>
                <div class="mt-2 pl-9 text-xs text-slate-500 italic">
                  <?= ((int)($m['round'] ?? 1) <= 1) ? 'Menunggu penentuan dari panitia' : 'Menunggu pemenang babak sebelumnya' ?>
                </div>
              <?php endif; ?>
            </div>

            <!-- SISI TENGAH: VS SEPARATOR -->
            <div class="shrink-0 flex flex-col items-center justify-center my-1 md:my-0 px-2">
              <div class="vs-circle flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-slate-800 via-slate-900 to-slate-950 border border-slate-700/80 text-[11px] font-black text-amber-400 shadow-lg shadow-black/40 ring-2 ring-slate-800/50">
                VS
              </div>
            </div>

            <!-- SISI KANAN: TIM B -->
            <div class="flex-1 flex flex-col justify-center min-w-0 md:text-right">
              <div class="flex items-center gap-2.5 md:justify-end">
                <div class="text-sm sm:text-base font-black text-white leading-tight break-words order-2 md:order-1 <?= $hasT2 ? '' : 'italic text-slate-400' ?>">
                  <?= e($t2Name) ?>
                </div>
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-purple-500 to-pink-500 text-xs font-black text-white shadow-md shadow-purple-500/30 order-1 md:order-2">
                  B
                </span>
              </div>

              <!-- Daftar Anggota Pemain Tim B -->
              <?php if (!empty($t2Members)): ?>
                <div class="mt-2.5 pl-9 md:pl-0 md:pr-9 space-y-1">
                  <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500 md:text-right">Daftar Pemain:</div>
                  <ul class="space-y-1">
                    <?php foreach ($t2Members as $member): ?>
                      <li class="flex items-center gap-2 text-xs text-slate-300 font-medium md:justify-end">
                        <span class="order-2 md:order-1"><?= e($member) ?></span>
                        <span class="text-slate-500 text-[11px] order-1 md:order-2">👤</span>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                </div>
              <?php elseif (!$hasT2): ?>
                <div class="mt-2 pl-8 md:pl-0 md:pr-8 text-xs text-slate-500 italic md:text-right">
                  <?= ((int)($m['round'] ?? 1) <= 1) ? 'Menunggu penentuan dari panitia' : 'Menunggu pemenang babak sebelumnya' ?>
                </div>
              <?php endif; ?>
            </div>

          </div>

          <!-- Perangkat Wasit Pertandingan (Wasit Utama 1 orang & Wasit Garis 2 orang) -->
          <?php
            $hasRefMain = !empty($m['referee_main']);
            $hasRefLine1 = !empty($m['referee_line1']);
            $hasRefLine2 = !empty($m['referee_line2']);
            $hasReferees = ($hasRefMain || $hasRefLine1 || $hasRefLine2);
          ?>
          <?php if ($hasReferees): ?>
            <div class="mt-3.5 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-800 bg-slate-950/70 px-4 py-2.5 text-xs text-slate-300 schedule-referee-bar">
              <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                <span class="inline-flex items-center gap-1.5 font-bold uppercase tracking-wider text-[11px] text-amber-400">
                  <span>👨‍⚖️</span>
                  <span>Perangkat Wasit:</span>
                </span>
                <?php if ($hasRefMain): ?>
                  <span class="inline-flex items-center gap-1.5 rounded-xl border border-amber-500/30 bg-amber-500/10 px-3 py-1 text-xs font-semibold text-amber-300">
                    <span class="opacity-80">Wasit Utama:</span>
                    <span class="font-bold text-white"><?= e($m['referee_main']) ?></span>
                  </span>
                <?php endif; ?>
                <?php if ($hasRefLine1 || $hasRefLine2): ?>
                  <span class="inline-flex items-center gap-1.5 rounded-xl border border-blue-500/30 bg-blue-500/10 px-3 py-1 text-xs font-semibold text-blue-300">
                    <span class="opacity-80">🚩 Wasit Garis:</span>
                    <span class="font-bold text-white">
                      <?= e(implode(', ', array_filter([$m['referee_line1'] ?? '', $m['referee_line2'] ?? '']))) ?>
                    </span>
                  </span>
                <?php endif; ?>
              </div>
            </div>
          <?php endif; ?>

        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<script>
// Filter Kategori Pertandingan pada Tab Jadwal
function filterScheduleCategory(catKey) {
  document.querySelectorAll('.schedcat-btn').forEach(b => {
    b.classList.remove('bg-blue-600', 'text-white');
    b.classList.add('text-slate-400', 'hover:text-white');
  });

  const activeBtn = document.getElementById('btn-schedcat-' + catKey);
  if (activeBtn) {
    activeBtn.classList.add('bg-blue-600', 'text-white');
    activeBtn.classList.remove('text-slate-400', 'hover:text-white');
  }

  applyScheduleFilters();
}

// Filter Status Pertandingan pada Tab Jadwal
function filterScheduleStatus(statusKey) {
  document.querySelectorAll('.schedstatus-btn').forEach(b => {
    b.classList.remove('bg-slate-800', 'text-white');
    b.classList.add('text-slate-400', 'hover:text-white');
  });

  const activeBtn = document.getElementById('btn-schedstatus-' + statusKey);
  if (activeBtn) {
    activeBtn.classList.add('bg-slate-800', 'text-white');
    activeBtn.classList.remove('text-slate-400', 'hover:text-white');
  }

  applyScheduleFilters();
}

function applyScheduleFilters() {
  const activeCatBtn = document.querySelector('.schedcat-btn.bg-blue-600');
  const catKey = activeCatBtn ? activeCatBtn.id.replace('btn-schedcat-', '') : 'all';

  const activeStatusBtn = document.querySelector('.schedstatus-btn.bg-slate-800');
  const statusKey = activeStatusBtn ? activeStatusBtn.id.replace('btn-schedstatus-', '') : 'all';

  document.querySelectorAll('.schedule-match-card').forEach(card => {
    const matchCat = card.dataset.category || 'umum';
    const matchStatus = card.dataset.status || 'upcoming';

    const matchCatOk = (catKey === 'all' || matchCat === catKey);
    const matchStatusOk = (statusKey === 'all' || matchStatus === statusKey);

    if (matchCatOk && matchStatusOk) {
      card.classList.remove('hidden');
    } else {
      card.classList.add('hidden');
    }
  });
}
</script>

