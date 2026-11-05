<?php
/**
 * Tab "Undian Grup" - Futsal / Sistem Liga
 * Mengundi tim yang belum punya grup ke grup tertentu dengan animasi roda/roulette.
 * @var string $sport
 * @var array $groups
 * @var array $unassignedTeams
 * @var array $standings
 */
?>
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
  <div>
    <h2 class="text-xl font-black text-white flex items-center gap-2">
      <span>🎰</span>
      <span>Undian Pembagian Grup (Group Draw)</span>
    </h2>
    <p class="text-xs sm:text-sm text-slate-400 mt-0.5">
      Pilih target grup, putar roda undian, dan masukkan tim ke dalam grup secara acak dan real-time.
    </p>
  </div>
  <div class="flex items-center gap-2">
    <a href="<?= url("admin/$sport/groups") ?>" class="btn small ghost text-xs flex items-center gap-1.5">
      <span>📊 Atur &amp; Generate Jadwal Grup</span>
      <span>&rarr;</span>
    </a>
  </div>
</div>

<?php if (empty($groups)): ?>
  <div class="rounded-3xl border border-dashed border-slate-800 bg-slate-900/50 p-12 text-center shadow-inner">
    <span class="text-5xl">📁</span>
    <h3 class="mt-4 text-lg font-bold text-white">Grup Belum Dibuat</h3>
    <p class="mt-1.5 max-w-md text-xs sm:text-sm text-slate-400 mx-auto">
      Untuk melakukan pengundian, buat grup terlebih dahulu (misal Grup A dan Grup B) di tab Grup &amp; Klasemen.
    </p>
    <a class="btn small mt-5" href="<?= url("admin/$sport/groups") ?>">+ Buat Grup Sekarang</a>
  </div>
<?php else: ?>
  <div id="group-draw-app" class="grid grid-cols-1 gap-6 lg:grid-cols-2" style="display:grid">
    <!-- Kolom Kiri: Mesin Undian Grup -->
    <div class="card flex flex-col justify-between">
      <div>
        <div class="mb-3 flex items-center justify-between">
          <h3 class="text-sm font-black uppercase tracking-wider text-white">Mesin Undian Grup</h3>
          <span class="badge">Belum ada grup: <b id="unassigned-count"><?= count($unassignedTeams) ?></b> Tim</span>
        </div>

        <!-- Notifikasi jika semua tim sudah masuk grup -->
        <div id="draw-completed" class="<?= empty($unassignedTeams) ? 'flex' : 'hidden' ?> flex-col items-center justify-center rounded-2xl border border-emerald-500/30 bg-emerald-950/20 p-8 text-center shadow-lg my-4">
          <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-500/20 text-3xl text-emerald-400 mb-3 shadow-md shadow-emerald-500/10">
            🎉
          </div>
          <h4 class="text-base sm:text-lg font-black text-white">Semua Tim Sudah Masuk Grup!</h4>
          <p class="mt-2 text-xs sm:text-sm text-slate-300 max-w-sm leading-relaxed">
            Seluruh tim telah berhasil diundi dan ditempatkan ke dalam grup masing-masing.
          </p>
          <div class="mt-5 flex flex-wrap items-center justify-center gap-3">
            <a href="<?= url("admin/$sport/groups") ?>" class="btn small !bg-emerald-600 hover:!bg-emerald-500 text-white font-bold shadow-md shadow-emerald-600/30 flex items-center gap-1.5">
              <span>Generate Jadwal Pertandingan</span>
              <span>&rarr;</span>
            </a>
          </div>
        </div>

        <!-- Badan Mesin Undian (Roda + Jarum + Kontrol) -->
        <div id="draw-machine-body" class="<?= empty($unassignedTeams) ? 'hidden' : '' ?> space-y-4">
          <!-- Pilihan Target Grup -->
          <div class="rounded-xl border border-slate-800 bg-slate-950/80 p-3.5">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
              Pilih Target Grup Penempatan:
            </label>
            <select id="target-group-select" class="w-full rounded-xl border border-slate-700 bg-slate-900 px-3.5 py-2 text-sm font-bold text-white focus:border-blue-500 focus:outline-none">
              <?php foreach ($groups as $g): ?>
                <option value="<?= (int)$g['id'] ?>">
                  <?= e($g['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <p class="mt-1 text-[11px] text-slate-400">
              Tim terpilih dari roda akan langsung dimasukkan ke grup ini.
            </p>
          </div>

          <!-- Roda + jarum -->
          <div class="rounded-2xl border border-slate-800 bg-slate-950 p-4 text-center">
            <div class="relative mx-auto w-full max-w-[320px]">
              <div class="absolute left-1/2 top-0 z-10 -translate-x-1/2 -translate-y-1"
                   style="width:0;height:0;border-left:13px solid transparent;border-right:13px solid transparent;border-top:30px solid #f59e0b;filter:drop-shadow(0 2px 4px rgba(0,0,0,.6))"></div>
              <canvas id="wheel" class="block w-full" style="aspect-ratio:1/1"></canvas>
              <div class="pointer-events-none absolute left-1/2 top-1/2 flex h-12 w-12 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full border-4 border-amber-500 bg-slate-900 text-lg shadow-lg">🎯</div>
            </div>

            <!-- Preview Hasil Terpilih Sementara -->
            <div id="pick-result-box" class="mt-4 flex min-h-[4rem] flex-col items-center justify-center rounded-xl border border-dashed border-amber-500/40 bg-amber-500/5 px-3 py-2 text-xs text-amber-300">
              <span class="text-slate-500 italic">Klik tombol di bawah untuk memutar undian</span>
            </div>
          </div>

          <p id="draw-msg" class="min-h-[1.25rem] text-xs text-amber-300"></p>
          <button type="button" id="btn-spin" class="btn w-full !py-3.5 text-base font-black shadow-lg shadow-blue-600/30">
            🎰 Spin Undian Grup!
          </button>

          <details class="text-xs text-slate-400">
            <summary class="cursor-pointer font-bold">Daftar tim yang belum memiliki grup (<span id="unassigned-list-count"><?= count($unassignedTeams) ?></span>)</summary>
            <ul id="unassigned-list" class="mt-2 list-disc pl-5 space-y-0.5">
              <?php foreach ($unassignedTeams as $t): ?>
                <li data-team-id="<?= (int)$t['id'] ?>"><?= e($t['name']) ?></li>
              <?php endforeach; ?>
            </ul>
          </details>
        </div>
      </div>
    </div>

    <!-- Kolom Kanan: Daftar Pembagian Grup Saat Ini -->
    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <h3 class="text-sm font-black uppercase tracking-wider text-white">Susunan Grup Saat Ini</h3>
        <span class="text-xs text-slate-400">Real-Time</span>
      </div>

      <div class="space-y-4" id="group-cards-container">
        <?php foreach ($standings as $gid => $s): $g = $s['group']; $rows = $s['rows']; ?>
          <div id="group-card-<?= $gid ?>" class="rounded-2xl border border-slate-800 bg-slate-900/90 p-4 shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-800 pb-2 mb-3">
              <h4 class="text-sm font-black text-white flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                <span><?= e($g['name']) ?></span>
              </h4>
              <span class="badge" id="group-count-<?= $gid ?>"><?= count($rows) ?> Tim</span>
            </div>

            <ul id="group-team-list-<?= $gid ?>" class="space-y-2">
              <?php foreach ($rows as $r): ?>
                <li class="flex items-center justify-between rounded-xl border border-slate-800/80 bg-slate-950/60 px-3 py-2 text-xs font-semibold text-slate-200"
                    data-team-id="<?= (int)$r['team_id'] ?>">
                  <div class="flex items-center gap-2">
                    <span class="text-slate-500">⚽</span>
                    <b><?= e($r['team_name']) ?></b>
                  </div>
                  <span class="text-[10px] text-slate-400 font-normal">Tergabung</span>
                </li>
              <?php endforeach; ?>
              <?php if (empty($rows)): ?>
                <li class="empty-placeholder py-4 text-center text-xs text-slate-500 italic">
                  Belum ada tim di grup ini.
                </li>
              <?php endif; ?>
            </ul>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Modal Hasil Undian -->
  <div id="draw-modal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/70 p-4 backdrop-blur-sm">
    <div class="w-full max-w-md rounded-3xl border border-amber-500/40 bg-slate-900 p-6 text-center shadow-2xl animate-fade-in">
      <div class="text-4xl mb-1">🎉</div>
      <h3 class="text-lg font-black text-white">Hasil Undian Grup</h3>
      <p id="modal-group-label" class="text-xs font-bold text-amber-400 mt-1 uppercase tracking-wider"></p>
      
      <div class="my-5 rounded-2xl border border-blue-500/30 bg-blue-600/15 p-4 text-center">
        <div id="modal-team-name" class="text-xl font-black text-white leading-tight"></div>
        <div id="modal-team-members" class="mt-2 text-xs text-slate-300 opacity-90 leading-relaxed"></div>
      </div>

      <p id="modal-status" class="mb-3 text-xs text-slate-400"></p>
      <button type="button" id="modal-close" class="btn w-full !py-2.5 font-bold">Tutup</button>
    </div>
  </div>

  <script>
  (function () {
    const sport = <?= json_encode($sport) ?>;
    const assignUrl = <?= json_encode(url("admin/$sport/group-draw/assign")) ?>;
    const csrf = document.querySelector('meta[name="csrf"]').content;
    let pool = <?= json_encode(array_map(fn($t) => [
      'id'          => (int)$t['id'],
      'name'        => $t['name'],
      'institution' => $t['institution'] ?? '',
      'members'     => parse_team_members($t['members'] ?? null)
    ], $unassignedTeams), JSON_UNESCAPED_UNICODE) ?>;

    const $ = (id) => document.getElementById(id);
    const btn = $('btn-spin'), msg = $('draw-msg');
    let spinning = false;

    const canvas = $('wheel');
    const ctx = canvas.getContext('2d');
    const COLORS = ['#2563eb', '#7c3aed', '#db2777', '#ea580c', '#16a34a', '#0891b2', '#ca8a04', '#dc2626'];
    let rotation = 0;
    let wheelTeams = pool.slice();
    let hlIdx = -1, hlPulse = 0;

    function syncCounts() {
      const uCount = pool.length;
      $('unassigned-count').textContent = uCount;
      const ulCount = $('unassigned-list-count');
      if (ulCount) ulCount.textContent = uCount;

      const isAllPlotted = (uCount === 0);
      const completedEl = $('draw-completed');
      const machineBodyEl = $('draw-machine-body');

      if (completedEl && machineBodyEl) {
        completedEl.classList.toggle('hidden', !isAllPlotted);
        completedEl.classList.toggle('flex', isAllPlotted);
        machineBodyEl.classList.toggle('hidden', isAllPlotted);
      }

      if (btn && !spinning) {
        btn.disabled = isAllPlotted;
      }
    }

    function fit() {
      if (!canvas) return;
      const size = canvas.clientWidth || 320;
      const dpr = window.devicePixelRatio || 1;
      canvas.width = canvas.height = Math.round(size * dpr);
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
      drawWheel();
    }

    function drawWheel() {
      if (!canvas) return;
      const size = canvas.clientWidth || 320;
      const c = size / 2, r = c - 6;
      ctx.clearRect(0, 0, size, size);
      const n = wheelTeams.length;
      if (!n) return;
      const seg = (Math.PI * 2) / n;
      for (let i = 0; i < n; i++) {
        const a0 = rotation + i * seg, a1 = a0 + seg;
        ctx.beginPath(); ctx.moveTo(c, c); ctx.arc(c, c, r, a0, a1); ctx.closePath();
        ctx.fillStyle = COLORS[i % COLORS.length]; ctx.fill();
        if (hlIdx >= 0) {
          ctx.fillStyle = i === hlIdx ? `rgba(255,255,255,${0.15 + 0.35 * hlPulse})` : 'rgba(7,10,18,0.6)';
          ctx.fill();
        }
        ctx.strokeStyle = '#0b1220'; ctx.lineWidth = 2; ctx.stroke();

        ctx.save();
        ctx.translate(c, c); ctx.rotate(a0 + seg / 2);
        ctx.fillStyle = '#fff'; ctx.textAlign = 'right'; ctx.textBaseline = 'middle';
        const fs = Math.max(9, Math.min(15, (r * seg) / 2.2, 15));
        ctx.font = `800 ${fs}px system-ui, sans-serif`;
        let label = wheelTeams[i].name, maxW = r - 38;
        while (label.length > 2 && ctx.measureText(label).width > maxW) label = label.slice(0, -2);
        if (label !== wheelTeams[i].name) label += '…';
        ctx.fillText(label, r - 12, 0);
        ctx.restore();
      }
      ctx.beginPath(); ctx.arc(c, c, r, 0, Math.PI * 2);
      ctx.strokeStyle = '#f59e0b'; ctx.lineWidth = 5; ctx.stroke();
    }

    function spinWheel(duration) {
      return new Promise((resolve) => {
        const n = wheelTeams.length;
        const seg = (Math.PI * 2) / n;
        const idx = Math.floor(Math.random() * n);
        const jitter = (Math.random() - 0.5) * seg * 0.7;
        let target = -Math.PI / 2 - (idx + 0.5) * seg + jitter;
        const TAU = Math.PI * 2;
        const minEnd = rotation + TAU * 6;
        target += Math.ceil((minEnd - target) / TAU) * TAU;

        const from = rotation, start = performance.now();
        (function frame(now) {
          const t = Math.min(1, (now - start) / duration);
          const ease = 1 - Math.pow(1 - t, 4);
          rotation = from + (target - from) * ease;
          drawWheel();
          if (t < 1) requestAnimationFrame(frame);
          else resolve(idx);
        })(start);
      });
    }

    function highlightSegment(idx) {
      return new Promise((resolve) => {
        hlIdx = idx;
        const start = performance.now(), dur = 900;
        (function frame(now) {
          const t = (now - start) / dur;
          hlPulse = 0.5 + 0.5 * Math.sin(t * Math.PI * 6);
          drawWheel();
          if (t < 1) requestAnimationFrame(frame); else { hlPulse = 1; drawWheel(); resolve(); }
        })(start);
      });
    }

    const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
    function esc(s) {
      return String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    }

    window.addEventListener('resize', fit);
    fit();
    syncCounts();

    async function saveAssignment(teamId, groupId) {
      const body = new URLSearchParams({ team_id: teamId, group_id: groupId, _token: csrf });
      const res = await fetch(assignUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': csrf },
        body: body.toString(),
      });
      let data = {};
      try { data = await res.json(); } catch (e) {}
      if (!res.ok || !data.ok) throw new Error(data.error || 'Gagal menyimpan undian grup.');
      return data;
    }

    btn.addEventListener('click', async () => {
      if (spinning) return;
      msg.textContent = '';
      if (pool.length === 0) {
        msg.textContent = 'Semua tim sudah memiliki grup.';
        return;
      }

      const sel = $('target-group-select');
      const targetGroupId = parseInt(sel.value, 10);
      const targetGroupName = sel.options[sel.selectedIndex].text.trim();

      spinning = true; btn.disabled = true; btn.textContent = '🎰 Mengundi…';
      wheelTeams = pool.slice();
      drawWheel();

      // Spin animasi
      const winIdx = await spinWheel(4200);
      const chosenTeam = wheelTeams[winIdx];
      await highlightSegment(winIdx);

      // Tampilkan hasil di box preview
      const pBox = $('pick-result-box');
      pBox.innerHTML = `
        <div class="text-white font-black text-sm">${esc(chosenTeam.name)}</div>
        <div class="text-amber-400 font-bold mt-0.5">&rarr; Masuk ke ${esc(targetGroupName)}</div>
      `;

      // Buka modal hasil
      $('modal-group-label').textContent = 'Dimasukkan ke ' + targetGroupName;
      $('modal-team-name').textContent = chosenTeam.name;
      const mems = (chosenTeam.members || []).join(' · ');
      $('modal-team-members').textContent = mems ? ('Anggota: ' + mems) : '';
      $('modal-status').textContent = 'Menyimpan ke database…';
      const closeBtn = $('modal-close');
      closeBtn.disabled = true; closeBtn.textContent = 'Menyimpan…';
      $('draw-modal').classList.remove('hidden'); $('draw-modal').classList.add('flex');

      try {
        await saveAssignment(chosenTeam.id, targetGroupId);

        // Hapus tim dari pool dan list unassigned
        pool = pool.filter((t) => t.id !== chosenTeam.id);
        const liItem = document.querySelector(`#unassigned-list li[data-team-id="${chosenTeam.id}"]`);
        if (liItem) liItem.remove();

        // Tambah ke daftar tim grup kanan secara visual
        const groupUl = $(`group-team-list-${targetGroupId}`);
        if (groupUl) {
          const ph = groupUl.querySelector('.empty-placeholder');
          if (ph) ph.remove();

          const newLi = document.createElement('li');
          newLi.className = 'flex items-center justify-between rounded-xl border border-blue-500/40 bg-blue-950/40 px-3 py-2 text-xs font-bold text-blue-200 animate-pulse';
          newLi.dataset.teamId = chosenTeam.id;
          newLi.innerHTML = `
            <div class="flex items-center gap-2">
              <span>⚽</span>
              <b>${esc(chosenTeam.name)}</b>
            </div>
            <span class="text-[10px] text-emerald-400 font-bold">Baru Masuk!</span>
          `;
          groupUl.appendChild(newLi);

          const cntEl = $(`group-count-${targetGroupId}`);
          if (cntEl) {
            const num = groupUl.querySelectorAll('li:not(.empty-placeholder)').length;
            cntEl.textContent = `${num} Tim`;
          }
        }

        $('modal-status').textContent = '✅ Berhasil disimpan ke ' + targetGroupName + '!';
        closeBtn.textContent = 'Lanjut Undian';
      } catch (err) {
        $('modal-status').textContent = '❌ ' + err.message;
        closeBtn.textContent = 'Tutup (tidak tersimpan)';
      } finally {
        closeBtn.disabled = false;
        syncCounts();
      }
    });

    $('modal-close').addEventListener('click', () => {
      $('draw-modal').classList.add('hidden'); $('draw-modal').classList.remove('flex');
      spinning = false; btn.disabled = false; btn.textContent = '🎰 Spin Undian Grup!';
      hlIdx = -1;
      wheelTeams = pool.slice();
      drawWheel();
      syncCounts();
    });
  })();
  </script>
<?php endif; ?>

