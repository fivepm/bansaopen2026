<?php if ($system === 'knockout'): ?>
  <?php if (count($categories) > 1): ?>
    <div class="mb-4 flex items-center gap-2">
      <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Pilih Kategori:</span>
      <?php foreach ($categories as $ck => $cl): ?>
        <a href="<?= url("admin/$sport/matches?cat=$ck") ?>" class="badge <?= ($catFilter === $ck) ? 'finished !font-bold' : '' ?>">
          <?= e($cl) ?>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form class="card row" method="post" action="<?= url("admin/$sport/matches/generate") ?>" onsubmit="return confirm('Membuat bagan baru akan menghapus pertandingan sistem gugur untuk kategori ini. Lanjut?')">
    <?= csrf_field() ?>
    <input type="hidden" name="category" value="<?= e($catFilter) ?>">
    <label>Ukuran Bagan Kiri-Kanan (Two-Sided)
      <select name="slots">
        <?php foreach ([4, 8, 16, 32] as $n): ?>
          <option value="<?= $n ?>"><?= $n ?> Peserta (<?= $n / 2 ?> Kiri, <?= $n / 2 ?> Kanan)</option>
        <?php endforeach; ?>
      </select>
    </label>
    <button class="btn">Generate Bagan</button>
    <?php if (!empty($twoSided['totalRounds'])): ?>
      <button class="btn danger" formaction="<?= url("admin/$sport/matches/reset") ?>">Hapus Bagan</button>
    <?php endif; ?>
  </form>

  <?php if (!empty($twoSided['totalRounds'])): ?>
    <div class="mt-6 space-y-6">
      <!-- Puncak Tengah: Grand Final & Juara 3 -->
      <div class="rounded-2xl border border-amber-500/40 bg-slate-900/90 p-5 shadow-xl">
        <h3 class="text-base font-black text-amber-400 uppercase tracking-wider flex items-center gap-2">
          <span>🏆</span>
          <span>Pertandingan Puncak (Tengah) — <?= e($categories[$catFilter] ?? '') ?></span>
        </h3>
        <div class="mt-4 grid grid-cols-1 gap-5 md:grid-cols-2">
          <!-- Final -->
          <?php if (!empty($twoSided['final'])): $m = $twoSided['final']; ?>
            <div class="slot rounded-2xl border-2 border-amber-500/60 p-4 bg-slate-950/80 shadow-lg" data-id="<?= (int)$m['id'] ?>">
              <div class="mb-2 flex items-center justify-between text-xs font-bold text-amber-400">
                <span>🏆 GRAND FINAL</span>
                <span class="badge <?= e($m['status']) ?>"><?= e(status_label($m['status'])) ?></span>
              </div>
              <?php include dirname(__DIR__, 2) . '/partials/match_card.php'; ?>
              <?php include dirname(__DIR__, 2) . '/partials/admin_match_actions.php'; ?>
            </div>
          <?php endif; ?>

          <!-- Perebutan Juara 3 -->
          <?php if (!empty($twoSided['thirdPlace'])): $m = $twoSided['thirdPlace']; ?>
            <div class="slot rounded-2xl border-2 border-orange-500/50 p-4 bg-slate-950/80 shadow-lg" data-id="<?= (int)$m['id'] ?>">
              <div class="mb-2 flex items-center justify-between text-xs font-bold text-orange-400">
                <span>🥉 PEREBUTAN JUARA 3 (Second Runner-Up)</span>
                <span class="badge <?= e($m['status']) ?>"><?= e(status_label($m['status'])) ?></span>
              </div>
              <?php include dirname(__DIR__, 2) . '/partials/match_card.php'; ?>
              <?php include dirname(__DIR__, 2) . '/partials/admin_match_actions.php'; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Blok Kiri & Blok Kanan -->
      <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Blok Kiri -->
        <div class="rounded-2xl border border-slate-800 bg-slate-900/80 p-5">
          <h3 class="text-sm font-extrabold text-blue-400 uppercase tracking-wider mb-4 flex items-center gap-2">
            <span>◀</span>
            <span>Bagan Blok Kiri</span>
          </h3>
          <div class="space-y-5">
            <?php foreach ($twoSided['left'] as $r => $matches): ?>
              <div>
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">
                  <?= e(round_name($r, $twoSided['totalRounds'])) ?> (Kiri)
                </h4>
                <div class="space-y-3">
                  <?php foreach ($matches as $m): ?>
                    <div class="slot" data-id="<?= (int)$m['id'] ?>">
                      <?php include dirname(__DIR__, 2) . '/partials/match_card.php'; ?>
                      <?php include dirname(__DIR__, 2) . '/partials/admin_match_actions.php'; ?>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Blok Kanan -->
        <div class="rounded-2xl border border-slate-800 bg-slate-900/80 p-5">
          <h3 class="text-sm font-extrabold text-indigo-400 uppercase tracking-wider mb-4 flex items-center gap-2">
            <span>Bagan Blok Kanan</span>
            <span>▶</span>
          </h3>
          <div class="space-y-5">
            <?php foreach ($twoSided['right'] as $r => $matches): ?>
              <div>
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">
                  <?= e(round_name($r, $twoSided['totalRounds'])) ?> (Kanan)
                </h4>
                <div class="space-y-3">
                  <?php foreach ($matches as $m): ?>
                    <div class="slot" data-id="<?= (int)$m['id'] ?>">
                      <?php include dirname(__DIR__, 2) . '/partials/match_card.php'; ?>
                      <?php include dirname(__DIR__, 2) . '/partials/admin_match_actions.php'; ?>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  <?php else: ?>
    <p class="card empty">Belum ada bagan untuk kategori ini. Pilih ukuran lalu klik Generate Bagan.</p>
  <?php endif; ?>

<?php else: /* ===== Sistem Grup ===== */ ?>
  <?php if (!$groups || !$groupMatches): ?>
    <p class="card empty">Jadwal grup belum dibuat. Atur grup di tab <a href="<?= url("admin/$sport/groups") ?>">Grup &amp; Klasemen</a>, lalu generate jadwal.</p>
  <?php endif; ?>
  <?php foreach ($groups as $g): if (empty($groupMatches[(int)$g['id']])) { continue; } ?>
    <h2 class="mt-6 text-lg font-bold text-white"><?= e($g['name']) ?></h2>
    <div class="grid">
      <?php foreach ($groupMatches[(int)$g['id']] as $m): ?>
        <div class="slot" data-id="<?= (int)$m['id'] ?>">
          <div class="muted small-t"><?= $sport === 'futsal' ? 'Match ke-' : 'Hari ke-' ?><?= (int)$m['round'] ?></div>
          <?php include dirname(__DIR__, 2) . '/partials/match_card.php'; ?>
          <?php include dirname(__DIR__, 2) . '/partials/admin_match_actions.php'; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endforeach; ?>

  <?php if (!empty($playoffs['final']) || !empty($playoffs['thirdPlace'])): ?>
    <div class="mt-10 border-t border-slate-800 pt-6">
      <div class="mb-4">
        <h2 class="text-xl font-black text-white flex items-center gap-2">
          <span>🏆</span>
          <span>Babak Playoff &amp; Final</span>
        </h2>
        <p class="text-xs text-slate-400 mt-1">
          Juara Grup otomatis lolos ke Grand Final, dan Runner-up Grup lolos ke Perebutan Juara 3 segera setelah masing-masing grup menyelesaikan pertandingannya (tanpa harus menunggu grup lain selesai).
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <?php if (!empty($playoffs['final'])): $m = $playoffs['final']; ?>
          <div class="slot rounded-2xl border-2 border-amber-500/40 bg-slate-900/80 p-4 shadow-xl shadow-amber-500/5" data-id="<?= (int)$m['id'] ?>">
            <div class="flex items-center justify-between mb-2">
              <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-500/50 bg-amber-500/20 px-3 py-0.5 text-xs font-black text-amber-300">
                👑 GRAND FINAL
              </span>
              <span class="text-[11px] text-slate-400">Penentu Juara 1 &amp; 2</span>
            </div>
            <?php include dirname(__DIR__, 2) . '/partials/match_card.php'; ?>
            <?php include dirname(__DIR__, 2) . '/partials/admin_match_actions.php'; ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($playoffs['thirdPlace'])): $m = $playoffs['thirdPlace']; ?>
          <div class="slot rounded-2xl border-2 border-blue-500/30 bg-slate-900/80 p-4 shadow-xl shadow-blue-500/5" data-id="<?= (int)$m['id'] ?>">
            <div class="flex items-center justify-between mb-2">
              <span class="inline-flex items-center gap-1.5 rounded-full border border-blue-500/50 bg-blue-500/20 px-3 py-0.5 text-xs font-black text-blue-300">
                🥉 PEREBUTAN JUARA 3
              </span>
              <span class="text-[11px] text-slate-400">Penentu Juara 3</span>
            </div>
            <?php include dirname(__DIR__, 2) . '/partials/match_card.php'; ?>
            <?php include dirname(__DIR__, 2) . '/partials/admin_match_actions.php'; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
<?php endif; ?>

<!-- ================= MODAL 1: UBAH STATUS, SISTEM SET & JADWAL ================= -->
<div id="statusModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4 hidden overflow-y-auto">
  <div class="relative w-full max-w-lg rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-2xl transition-all my-8 max-h-[90vh] overflow-y-auto">
    <div class="flex items-center justify-between border-b border-slate-800 pb-3 mb-4">
      <div>
        <h3 class="text-base font-black text-white flex items-center gap-2">
          <span>⚙️</span>
          <span>Ubah Status &amp; Pengaturan Pertandingan</span>
        </h3>
        <p id="statusModalSubtitle" class="text-xs text-slate-400 mt-0.5">Pertandingan #...</p>
      </div>
      <button type="button" onclick="closeStatusModal()" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-800 hover:text-white transition">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>

    <form id="statusModalForm" method="post" action="">
      <?= csrf_field() ?>
      <input type="hidden" name="category" value="<?= e($catFilter) ?>">

      <!-- Card info tim -->
      <div class="mb-4 rounded-xl border border-slate-800 bg-slate-950/70 p-3">
        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Pertandingan:</div>
        <div class="flex items-center justify-between text-xs font-semibold text-slate-200">
          <span id="statusModalTeam1" class="truncate font-bold text-blue-400 max-w-[45%]">Tim 1</span>
          <span class="text-slate-500 px-2 font-mono text-[11px]">VS</span>
          <span id="statusModalTeam2" class="truncate font-bold text-indigo-400 max-w-[45%] text-right">Tim 2</span>
        </div>
      </div>

      <div class="space-y-4">
        <!-- 1. Pilihan Status (Hanya Belum Mulai & Selesai) -->
        <div>
          <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-300">
            Status Pertandingan
          </label>
          <select id="modalStatusSelect" name="status" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2 text-sm text-slate-100 focus:border-blue-500 focus:outline-none" onchange="toggleStatusSections()">
            <option value="upcoming">Belum Mulai</option>
            <option value="finished">✓ Selesai</option>
          </select>
          <p class="mt-1 text-[11px] text-slate-400">
            ℹ️ <i>Untuk memulai pertandingan secara langsung, tekan tombol mandiri <b>"Set Live"</b> di kotak pertandingan.</i>
          </p>
        </div>

        <!-- 2. Pilihan Sistem Set / Format Futsal -->
        <?php if ($sport === 'futsal'): ?>
          <input type="hidden" id="modalScoringSystemSelect" name="scoring_system" value="rubber_set">
          <div>
            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-300">
              Tipe Pertandingan
            </label>
            <select id="modalFutsalStageSelect" name="stage" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2 text-sm text-slate-100 focus:border-blue-500 focus:outline-none">
              <option value="group">Grup</option>
              <option value="playoff">Playoff</option>
            </select>
            <p class="mt-1 text-[11px] text-slate-400">
              Pilih <b>Grup</b> untuk babak penyisihan liga atau <b>Playoff</b> untuk perebutan juara.
            </p>
          </div>

          <!-- Input Durasi Waktu Babak (Menit) -->
          <div>
            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-300 flex items-center justify-between">
              <span>⏱️ Lama Pertandingan per Babak (Menit)</span>
              <span class="text-[10px] text-emerald-400 font-normal">Otomatis untuk wasit</span>
            </label>
            <input type="number" min="1" max="60" name="futsal_half_duration" id="modalFutsalHalfDuration" value="5" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2 text-sm text-slate-100 focus:border-blue-500 focus:outline-none font-mono">
            <p class="mt-1 text-[11px] text-slate-400">
              Wasit di Live Panel langsung memulai babak dengan durasi ini tanpa perlu mengatur waktu lagi.
            </p>
          </div>
        <?php else: ?>
          <div>
            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-300">
              Pilihan Sistem Set
            </label>
            <select id="modalScoringSystemSelect" name="scoring_system" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2 text-sm text-slate-100 focus:border-blue-500 focus:outline-none" onchange="toggleStatusSections()">
              <option value="poin_lanjut">Poin Lanjut (2 Set)</option>
              <option value="rubber_set">Rubber Set (Best of 3)</option>
            </select>
            <p class="mt-1 text-[11px] text-slate-400" id="scoringSystemHelp">
              Poin Lanjut: Akumulasi total poin 2 set. Rubber Set: Mencari 2 kemenangan set.
            </p>
          </div>
        <?php endif; ?>

        <!-- 3. Jadwal Pertandingan (Hari, Tanggal, Jam, & Lapangan) -->
        <div class="rounded-xl border border-slate-800 bg-slate-950/70 p-3.5 space-y-3">
          <div class="flex items-center justify-between text-xs font-bold text-blue-400">
            <span>📅 Jadwal &amp; Lapangan Pertandingan</span>
            <button type="button" onclick="clearSchedule()" class="text-[10px] text-slate-400 hover:text-rose-400 transition underline cursor-pointer">
              Kosongkan Jadwal
            </button>
          </div>

          <div class="grid grid-cols-2 gap-2 text-xs">
            <div>
              <label class="text-[11px] text-slate-300 mb-1 block font-semibold">Tanggal</label>
              <input type="date" name="schedule_date" id="modalScheduleDate" class="w-full rounded-xl border border-slate-700 bg-slate-900 px-3 py-1.5 text-xs text-white focus:border-blue-500 focus:outline-none" oninput="updateSchedulePreview()">
            </div>
            <div>
              <label class="text-[11px] text-slate-300 mb-1 block font-semibold">Jam (WIB)</label>
              <input type="time" name="schedule_time" id="modalScheduleTime" class="w-full rounded-xl border border-slate-700 bg-slate-900 px-3 py-1.5 text-xs text-white focus:border-blue-500 focus:outline-none" oninput="updateSchedulePreview()">
            </div>
          </div>

          <!-- Preview Hari & Tanggal Otomatis -->
          <div id="schedulePreview" class="text-xs font-semibold text-emerald-400 bg-emerald-950/30 border border-emerald-500/20 rounded-lg p-2.5 flex items-center gap-1.5">
            <span>🗓️</span>
            <span id="schedulePreviewText">Jadwal belum ditentukan</span>
          </div>

          <div>
            <label class="text-[11px] text-slate-300 mb-1 block font-semibold">Nama Lapangan / Court (Opsional)</label>
            <input type="text" name="court" id="modalCourt" placeholder="Misal: Lapangan 1" maxlength="30" class="w-full rounded-xl border border-slate-700 bg-slate-900 px-3 py-1.5 text-xs text-white focus:border-blue-500 focus:outline-none">
          </div>
        </div>

        <?php if ($sport === 'futsal'): ?>
          <!-- Futsal: Tanpa input skor manual. Skor mutlak dari highlight gol di Live Panel -->
          <div id="futsalScoreNotice" class="rounded-xl border border-emerald-500/30 bg-emerald-950/20 p-3 text-xs text-emerald-300 flex items-center gap-2">
            <span class="text-base">⚽</span>
            <div>
              <b>Skor Futsal:</b> Terhitung mutlak secara otomatis dari penambahan highlight Gol di Live Panel.
            </div>
          </div>
        <?php else: ?>
          <!-- Section khusus input skor jika status 'finished' (Badminton / Tenis Meja) -->
          <div id="finishedScoresSection" class="rounded-xl border border-slate-800 bg-slate-950/70 p-3.5 space-y-3 hidden">
            <div class="flex items-center justify-between text-xs font-bold text-emerald-400">
              <span>Input / Konfirmasi Poin Set:</span>
              <span class="text-[10px] text-slate-400 font-normal">Pemenang dihitung otomatis</span>
            </div>

            <div class="grid grid-cols-2 gap-2 text-xs">
              <div>
                <span class="text-[11px] text-slate-400 block mb-1 truncate"><span id="labelSet1Title">Set 1</span> (<span id="labelSet1A">Tim 1</span>)</span>
                <input type="number" name="set1_a" id="modalSet1A" min="0" value="0" class="w-full !py-1 text-center font-mono" oninput="syncPoinLanjutModal()">
              </div>
              <div>
                <span class="text-[11px] text-slate-400 block mb-1 truncate"><span id="labelSet1TitleB">Set 1</span> (<span id="labelSet1B">Tim 2</span>)</span>
                <input type="number" name="set1_b" id="modalSet1B" min="0" value="0" class="w-full !py-1 text-center font-mono" oninput="syncPoinLanjutModal()">
              </div>
            </div>

            <div class="grid grid-cols-2 gap-2 text-xs">
              <div>
                <span class="text-[11px] text-slate-400 block mb-1 truncate"><span id="labelSet2Title">Set 2</span> (<span id="labelSet2A">Tim 1</span>)</span>
                <input type="number" name="set2_a" id="modalSet2A" min="0" value="0" class="w-full !py-1 text-center font-mono">
              </div>
              <div>
                <span class="text-[11px] text-slate-400 block mb-1 truncate"><span id="labelSet2TitleB">Set 2</span> (<span id="labelSet2B">Tim 2</span>)</span>
                <input type="number" name="set2_b" id="modalSet2B" min="0" value="0" class="w-full !py-1 text-center font-mono">
              </div>
            </div>

            <div id="set3Row" class="grid grid-cols-2 gap-2 text-xs">
              <div>
                <span class="text-[11px] text-slate-400 block mb-1 truncate">Set 3 (<span id="labelSet3A">Tim 1</span>)</span>
                <input type="number" name="set3_a" id="modalSet3A" min="0" value="0" class="w-full !py-1 text-center font-mono">
              </div>
              <div>
                <span class="text-[11px] text-slate-400 block mb-1 truncate">Set 3 (<span id="labelSet3B">Tim 2</span>)</span>
                <input type="number" name="set3_b" id="modalSet3B" min="0" value="0" class="w-full !py-1 text-center font-mono">
              </div>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <div class="mt-6 flex items-center justify-end gap-2.5 pt-3 border-t border-slate-800">
        <button type="button" onclick="closeStatusModal()" class="btn ghost small !py-2 !px-4">Batal</button>
        <button type="submit" class="btn small !py-2 !px-4 !bg-blue-600 hover:!bg-blue-500 font-bold">Simpan Pengaturan</button>
      </div>
    </form>
  </div>
</div>

<!-- ================= MODAL 2: ATUR TIM BABAK PENYISIHAN ================= -->
<div id="teamsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4 hidden">
  <div class="relative w-full max-w-md rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-2xl transition-all">
    <div class="flex items-center justify-between border-b border-slate-800 pb-3 mb-4">
      <div>
        <h3 class="text-base font-black text-white flex items-center gap-2">
          <span>👥</span>
          <span>Atur Tim Babak Penyisihan</span>
        </h3>
        <p id="teamsModalSubtitle" class="text-xs text-blue-400 font-semibold mt-0.5">Pertandingan #...</p>
      </div>
      <button type="button" onclick="closeTeamsModal()" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-800 hover:text-white transition">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>

    <form id="teamsModalForm" method="post" action="">
      <?= csrf_field() ?>
      <input type="hidden" name="category" value="<?= e($catFilter) ?>">

      <div class="space-y-4">
        <div>
          <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-300">
            Tim 1 (Tim A)
          </label>
          <select id="modalTeam1Select" name="team1_id" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2 text-sm text-slate-100 focus:border-blue-500 focus:outline-none">
            <option value="">-- Belum Ditentukan (TBD) --</option>
            <?php foreach (($teams ?? []) as $t): ?>
              <option value="<?= (int)$t['id'] ?>"><?= e($t['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-300">
            Tim 2 (Tim B)
          </label>
          <select id="modalTeam2Select" name="team2_id" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2 text-sm text-slate-100 focus:border-blue-500 focus:outline-none">
            <option value="">-- Belum Ditentukan (TBD) --</option>
            <?php foreach (($teams ?? []) as $t): ?>
              <option value="<?= (int)$t['id'] ?>"><?= e($t['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="rounded-xl border border-blue-500/20 bg-blue-950/30 p-3 text-xs text-blue-300 leading-relaxed">
          ℹ️ <b>Sistem Otomatis (Auto-Advance):</b> Babak selanjutnya (Perempat Final, Semifinal, Final, Perebutan Juara 3) akan diisi secara otomatis oleh pemenang babak sebelumnya setelah pertandingan diselesaikan.
        </div>
      </div>

      <div class="mt-6 flex items-center justify-end gap-2.5 pt-3 border-t border-slate-800">
        <button type="button" onclick="closeTeamsModal()" class="btn ghost small !py-2 !px-4">Batal</button>
        <button type="submit" class="btn small !py-2 !px-4 !bg-blue-600 hover:!bg-blue-500 font-bold">Simpan Susunan Tim</button>
      </div>
    </form>
  </div>
</div>

<script>
// ================= CONTROLLER MODAL STATUS, SISTEM & JADWAL =================
function updateSchedulePreview() {
  const dateVal = document.getElementById('modalScheduleDate').value;
  const timeVal = document.getElementById('modalScheduleTime').value;
  const previewEl = document.getElementById('schedulePreviewText');
  
  if (!dateVal) {
    previewEl.textContent = 'Jadwal belum ditentukan';
    return;
  }

  const parts = dateVal.split('-');
  if (parts.length !== 3) {
    previewEl.textContent = dateVal;
    return;
  }
  
  const y = parseInt(parts[0], 10);
  const m = parseInt(parts[1], 10) - 1;
  const d = parseInt(parts[2], 10);
  const dateObj = new Date(y, m, d);
  
  const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
  const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
  
  const dayName = days[dateObj.getDay()];
  const dayNum = String(d).padStart(2, '0');
  const monthName = months[m];
  const timeStr = timeVal ? ` · Pukul ${timeVal} WIB` : '';

  previewEl.textContent = `${dayName}, ${dayNum} ${monthName} ${y}${timeStr}`;
}

function clearSchedule() {
  document.getElementById('modalScheduleDate').value = '';
  document.getElementById('modalScheduleTime').value = '';
  updateSchedulePreview();
}

function openStatusModal(btn) {
  const d = btn.dataset;
  const form = document.getElementById('statusModalForm');
  form.action = '<?= url("admin/$sport/matches") ?>/' + d.matchId + '/status-system';

  document.getElementById('statusModalSubtitle').textContent = 'Pertandingan ' + (d.matchTitle || '#' + d.matchId);
  document.getElementById('statusModalTeam1').textContent = d.team1Name || 'TBD';
  document.getElementById('statusModalTeam2').textContent = d.team2Name || 'TBD';
  
  const t1 = d.team1Name || 'Tim 1';
  const t2 = d.team2Name || 'Tim 2';
  const elSet1A = document.getElementById('labelSet1A'); if (elSet1A) elSet1A.textContent = t1;
  const elSet1B = document.getElementById('labelSet1B'); if (elSet1B) elSet1B.textContent = t2;
  const elSet2A = document.getElementById('labelSet2A'); if (elSet2A) elSet2A.textContent = t1;
  const elSet2B = document.getElementById('labelSet2B'); if (elSet2B) elSet2B.textContent = t2;
  const elSet3A = document.getElementById('labelSet3A'); if (elSet3A) elSet3A.textContent = t1;
  const elSet3B = document.getElementById('labelSet3B'); if (elSet3B) elSet3B.textContent = t2;

  // Pilihan status: jika sebelumnya live, di modal diarahkan ke upcoming atau finished
  const currentStatus = d.status || 'upcoming';
  document.getElementById('modalStatusSelect').value = (currentStatus === 'finished') ? 'finished' : 'upcoming';
  document.getElementById('modalScoringSystemSelect').value = d.scoringSystem || 'rubber_set';

  // Jadwal & Lapangan
  const schedAt = d.scheduledAt || '';
  if (schedAt && schedAt.includes(' ')) {
    const [dt, tm] = schedAt.split(' ');
    document.getElementById('modalScheduleDate').value = dt || '';
    document.getElementById('modalScheduleTime').value = tm ? tm.substring(0, 5) : '';
  } else if (schedAt) {
    document.getElementById('modalScheduleDate').value = schedAt;
    document.getElementById('modalScheduleTime').value = '';
  } else {
    document.getElementById('modalScheduleDate').value = '';
    document.getElementById('modalScheduleTime').value = '';
  }
  document.getElementById('modalCourt').value = d.court || '';
  updateSchedulePreview();

  // Futsal settings: Tipe Pertandingan & Durasi Babak
  const stageEl = document.getElementById('modalFutsalStageSelect');
  if (stageEl) {
    stageEl.value = (d.stage === 'knockout' || d.stage === 'playoff') ? 'playoff' : 'group';
  }
  const durEl = document.getElementById('modalFutsalHalfDuration');
  if (durEl) {
    durEl.value = d.futsalHalfDuration || 5;
  }

  // Skor set jika selesai
  const isPoinLanjut = ((d.scoringSystem || 'rubber_set') === 'poin_lanjut');
  const s1a = parseInt(d.set1A || 0, 10);
  const s1b = parseInt(d.set1B || 0, 10);
  const s2a = parseInt(d.set2A || 0, 10);
  const s2b = parseInt(d.set2B || 0, 10);

  if (document.getElementById('modalSet1A')) {
    document.getElementById('modalSet1A').value = s1a;
    document.getElementById('modalSet1B').value = s1b;
    document.getElementById('modalSet2A').value = (isPoinLanjut && s2a === 0 && s1a > 0) ? s1a : s2a;
    document.getElementById('modalSet2B').value = (isPoinLanjut && s2b === 0 && s1b > 0) ? s1b : s2b;
    document.getElementById('modalSet3A').value = d.set3A || 0;
    document.getElementById('modalSet3B').value = d.set3B || 0;
  }

  toggleStatusSections();
  document.getElementById('statusModal').classList.remove('hidden');
}

function syncPoinLanjutModal() {
  const sysEl = document.getElementById('modalScoringSystemSelect');
  if (!sysEl || sysEl.value !== 'poin_lanjut') return;
  if (!document.getElementById('modalSet1A')) return;
  
  const s1a = parseInt(document.getElementById('modalSet1A').value, 10) || 0;
  const s1b = parseInt(document.getElementById('modalSet1B').value, 10) || 0;
  const el2A = document.getElementById('modalSet2A');
  const el2B = document.getElementById('modalSet2B');

  const s2a = parseInt(el2A.value, 10) || 0;
  const s2b = parseInt(el2B.value, 10) || 0;

  if (s2a === 0 || s2a < s1a) {
    el2A.value = s1a;
  }
  if (s2b === 0 || s2b < s1b) {
    el2B.value = s1b;
  }
}

function closeStatusModal() {
  document.getElementById('statusModal').classList.add('hidden');
}

function toggleStatusSections() {
  const status = document.getElementById('modalStatusSelect').value;
  const sysEl = document.getElementById('modalScoringSystemSelect');
  const sys = sysEl ? sysEl.value : 'rubber_set';
  const isFutsal = <?= json_encode($sport === 'futsal') ?>;
  
  const finishedSection = document.getElementById('finishedScoresSection');
  const set3Row = document.getElementById('set3Row');
  const sysHelp = document.getElementById('scoringSystemHelp');

  const isPoinLanjut = (sys === 'poin_lanjut');
  const l1 = document.getElementById('labelSet1Title');
  const l1b = document.getElementById('labelSet1TitleB');
  const l2 = document.getElementById('labelSet2Title');
  const l2b = document.getElementById('labelSet2TitleB');

  if (isFutsal) {
    if (l1) l1.textContent = 'Babak 1';
    if (l1b) l1b.textContent = 'Babak 1';
    if (l2) l2.textContent = 'Babak 2';
    if (l2b) l2b.textContent = 'Babak 2';
  } else if (isPoinLanjut) {
    if (l1) l1.textContent = 'Set 1 (Skor Tengah)';
    if (l1b) l1b.textContent = 'Set 1 (Skor Tengah)';
    if (l2) l2.textContent = 'Set 2 (Akumulasi Akhir)';
    if (l2b) l2b.textContent = 'Set 2 (Akumulasi Akhir)';
    if (sysHelp) sysHelp.textContent = 'Poin Lanjut: Set 2 melanjutkan skor Set 1 (Carry-Over). Skor akhir penentu adalah skor akumulasi di Set 2.';
    syncPoinLanjutModal();
  } else {
    if (l1) l1.textContent = 'Set 1';
    if (l1b) l1b.textContent = 'Set 1';
    if (l2) l2.textContent = 'Set 2';
    if (l2b) l2b.textContent = 'Set 2';
    if (sysHelp) sysHelp.textContent = 'Rubber Set: Mencari 2 kemenangan set (Best of 3).';
  }

  if (finishedSection) {
    if (status === 'finished') {
      finishedSection.classList.remove('hidden');
    } else {
      finishedSection.classList.add('hidden');
    }
  }

  if (set3Row) {
    if (!isFutsal && sys === 'rubber_set') {
      set3Row.classList.remove('hidden');
    } else {
      set3Row.classList.add('hidden');
    }
  }
}

// ================= CONTROLLER MODAL ATUR TIM =================
function openTeamsModal(btn) {
  const d = btn.dataset;
  const form = document.getElementById('teamsModalForm');
  form.action = '<?= url("admin/$sport/matches") ?>/' + d.matchId + '/teams';

  document.getElementById('teamsModalSubtitle').textContent = 'Pertandingan ' + (d.matchTitle || '#' + d.matchId);
  document.getElementById('modalTeam1Select').value = d.team1Id || '';
  document.getElementById('modalTeam2Select').value = d.team2Id || '';

  document.getElementById('teamsModal').classList.remove('hidden');
}

function closeTeamsModal() {
  document.getElementById('teamsModal').classList.add('hidden');
}

// Event Listeners
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.open-status-modal-btn').forEach(btn => {
    btn.addEventListener('click', () => openStatusModal(btn));
  });

  document.querySelectorAll('.open-teams-modal-btn').forEach(btn => {
    btn.addEventListener('click', () => openTeamsModal(btn));
  });

  // Tombol Escape menutup semua modal
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closeStatusModal();
      closeTeamsModal();
    }
  });

  // Klik backdrop luar modal
  document.getElementById('statusModal')?.addEventListener('click', (e) => {
    if (e.target === e.currentTarget) closeStatusModal();
  });
  document.getElementById('teamsModal')?.addEventListener('click', (e) => {
    if (e.target === e.currentTarget) closeTeamsModal();
  });
  document.getElementById('teamsModal')?.addEventListener('click', (e) => {
    if (e.target === e.currentTarget) closeTeamsModal();
  });
});

// Update skor instan (AJAX) untuk pertandingan grup atau sistem lain jika ada
document.querySelectorAll('.quick button').forEach(btn => btn.addEventListener('click', async () => {
  const slot = btn.closest('.slot');
  const body = new URLSearchParams({side: btn.dataset.side, delta: btn.dataset.delta});
  const res = await fetch('<?= url("admin/$sport/matches") ?>/' + slot.dataset.id + '/score', {
    method: 'POST', body, headers: {'X-CSRF-Token': document.querySelector('meta[name=csrf]').content}
  });
  const j = await res.json();
  if (!j.ok) return alert(j.error);
  const scores = slot.querySelectorAll('.team b');
  if (scores.length >= 2) {
    scores[0].textContent = j.score1;
    scores[1].textContent = j.score2;
  }
}));
</script>
