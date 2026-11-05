<?php
/**
 * Tab "Undian Tim" - spin acak 2 tim untuk slot Babak Penyisihan.
 * @var string $sport
 * @var array $categories
 * @var string $catFilter
 * @var bool $hasBracket
 * @var array $slots   [{id, round, position, bracket_side}]
 * @var array $teams   [{id, name}] (belum punya jadwal)
 */
$sideLabel = ['left' => 'Kiri', 'right' => 'Kanan'];
?>
<?php if (count($categories) > 1): ?>
  <div class="mb-4 flex items-center gap-2">
    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Pilih Kategori:</span>
    <?php foreach ($categories as $ck => $cl): ?>
      <a href="<?= url("admin/$sport/draw?cat=$ck") ?>" class="badge <?= ($catFilter === $ck) ? 'finished !font-bold' : '' ?>"><?= e($cl) ?></a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php if (!$hasBracket): ?>
  <div class="card text-center">
    <p class="text-slate-300">Bagan untuk kategori ini belum dibuat.</p>
    <a class="btn small mt-3" href="<?= url("admin/$sport/matches?cat=$catFilter") ?>">Buat Bagan Dulu</a>
  </div>
<?php else: ?>
<div id="draw-app" class="grid-draw grid grid-cols-1 gap-5 lg:grid-cols-2" style="display:grid">
  <!-- Kolom Kiri: Slot -->
  <div class="card">
    <div class="mb-3 flex items-center justify-between">
      <h3 class="text-sm font-black uppercase tracking-wider text-white">Slot Babak Penyisihan Kosong</h3>
      <span class="badge" id="slot-count"><?= count($slots) ?></span>
    </div>
    <div id="slot-list" class="space-y-2">
      <?php foreach ($slots as $s): ?>
        <label class="draw-slot flex cursor-pointer items-center justify-between gap-3 rounded-xl border border-slate-700 bg-slate-950/60 px-4 py-3 hover:border-blue-500/60"
               data-slot-id="<?= (int)$s['id'] ?>">
          <span class="flex items-center gap-3">
            <input type="radio" name="slot" value="<?= (int)$s['id'] ?>" class="!w-4 !h-4">
            <b class="text-sm text-white" data-label="1-<?= (int)$s['position'] ?>">Match 1-<?= (int)$s['position'] ?></b>
          </span>
          <span class="badge">Sisi <?= e($sideLabel[$s['bracket_side']] ?? '') ?> · Penyisihan</span>
        </label>
      <?php endforeach; ?>
    </div>
    <p id="slot-empty" class="<?= $slots ? 'hidden' : '' ?> py-6 text-center text-sm text-slate-400">
      ✅ Semua slot penyisihan sudah terisi. Lihat hasilnya di tab Bracket.
    </p>
  </div>

  <!-- Kolom Kanan: Spin -->
  <div class="card flex flex-col justify-between">
    <div>
      <div class="mb-3 flex items-center justify-between">
        <h3 class="text-sm font-black uppercase tracking-wider text-white">Mesin Undian</h3>
        <span class="badge">Tim tersedia: <b id="team-count"><?= count($teams) ?></b></span>
      </div>

      <!-- Tampilan jika semua tim sudah habis dibagi / diplotting -->
      <div id="draw-completed" class="<?= empty($teams) ? 'flex' : 'hidden' ?> flex-col items-center justify-center rounded-2xl border border-emerald-500/30 bg-emerald-950/20 p-8 text-center shadow-lg my-4">
        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-500/20 text-3xl text-emerald-400 mb-3 shadow-md shadow-emerald-500/10">
          🎉
        </div>
        <h4 class="text-base sm:text-lg font-black text-white">Semua Tim Sudah Diplotting!</h4>
        <p class="mt-2 text-xs sm:text-sm text-slate-300 max-w-sm leading-relaxed">
          Seluruh tim pada kategori ini telah habis dibagi dan dijadwalkan ke dalam bagan pertandingan babak penyisihan.
        </p>
        <div class="mt-5 flex flex-wrap items-center justify-center gap-3">
          <a href="<?= url("admin/$sport/matches?cat=$catFilter") ?>" class="btn small !bg-emerald-600 hover:!bg-emerald-500 text-white font-bold shadow-md shadow-emerald-600/30 flex items-center gap-1.5">
            <span>Lihat di Tab Bracket</span>
            <span>&rarr;</span>
          </a>
        </div>
      </div>

      <!-- Badan Mesin Undian (Aktif jika masih ada tim yang perlu diundi) -->
      <div id="draw-machine-body" class="<?= empty($teams) ? 'hidden' : '' ?>">
        <div class="rounded-2xl border border-slate-800 bg-slate-950 p-4 text-center">
          <!-- Roda + jarum -->
          <div class="relative mx-auto w-full max-w-[340px]">
            <div class="absolute left-1/2 top-0 z-10 -translate-x-1/2 -translate-y-1"
                 style="width:0;height:0;border-left:13px solid transparent;border-right:13px solid transparent;border-top:30px solid #f59e0b;filter:drop-shadow(0 2px 4px rgba(0,0,0,.6))"></div>
            <canvas id="wheel" class="block w-full" style="aspect-ratio:1/1"></canvas>
            <div class="pointer-events-none absolute left-1/2 top-1/2 flex h-12 w-12 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full border-4 border-amber-500 bg-slate-900 text-lg shadow-lg">🎯</div>
          </div>

          <!-- Hasil: Tim terpilih pertama (kiri) VS Tim terpilih kedua (kanan) -->
          <div class="mt-4 grid grid-cols-[1fr_auto_1fr] items-center gap-2">
            <div id="pick-a" class="flex min-h-[5rem] flex-col items-center justify-center rounded-xl border border-dashed border-blue-500/40 bg-blue-600/10 px-2 py-2 text-sm font-black text-blue-300">—</div>
            <div class="text-xs font-black text-slate-500">VS</div>
            <div id="pick-b" class="flex min-h-[5rem] flex-col items-center justify-center rounded-xl border border-dashed border-indigo-500/40 bg-indigo-600/10 px-2 py-2 text-sm font-black text-indigo-300">—</div>
          </div>
        </div>

        <p id="draw-msg" class="mt-3 min-h-[1.25rem] text-xs text-amber-300"></p>
        <button type="button" id="btn-spin" class="btn mt-2 w-full !py-3 text-base font-black">🎰 Spin Undian!</button>

        <details class="mt-4 text-xs text-slate-400">
          <summary class="cursor-pointer font-bold">Daftar tim yang belum dijadwalkan</summary>
          <ul id="team-list" class="mt-2 list-disc pl-5 space-y-0.5">
            <?php foreach ($teams as $t): ?>
              <li data-team-id="<?= (int)$t['id'] ?>"><?= e($t['name']) ?></li>
            <?php endforeach; ?>
          </ul>
        </details>
      </div>
    </div>
  </div>
</div>

<!-- Modal hasil -->
<div id="draw-modal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/70 p-4 backdrop-blur-sm">
  <div class="w-full max-w-md rounded-3xl border border-amber-500/40 bg-slate-900 p-6 text-center shadow-2xl">
    <div class="text-3xl">🎉</div>
    <h3 class="mt-1 text-lg font-black text-white">Hasil Undian</h3>
    <p id="modal-slot" class="text-xs text-slate-400"></p>
    <div class="my-5 space-y-2">
      <div id="modal-a" class="rounded-xl bg-blue-600/20 px-4 py-3"></div>
      <div class="text-xs font-black text-slate-500">VS</div>
      <div id="modal-b" class="rounded-xl bg-indigo-600/20 px-4 py-3"></div>
    </div>
    <p id="modal-status" class="mb-3 text-xs text-slate-400"></p>
    <button type="button" id="modal-close" class="btn w-full" disabled>Menyimpan…</button>
  </div>
</div>

<script>
(function () {
  const sport = <?= json_encode($sport) ?>;
  const category = <?= json_encode($catFilter) ?>;
  const assignUrl = (id) => <?= json_encode(url("admin/$sport/draw/")) ?> + id + '/assign';
  const csrf = document.querySelector('meta[name="csrf"]').content;
  let pool = <?= json_encode(array_map(fn($t) => ['id' => (int)$t['id'], 'name' => $t['name'], 'members' => parse_team_members($t['members'] ?? null)], $teams), JSON_UNESCAPED_UNICODE) ?>;

  const $ = (id) => document.getElementById(id);
  const btn = $('btn-spin'), msg = $('draw-msg');
  let spinning = false;

  const selectedSlot = () => document.querySelector('input[name="slot"]:checked');
  const syncCounts = () => {
    const teamCountEl = $('team-count');
    if (teamCountEl) teamCountEl.textContent = pool.length;

    const n = document.querySelectorAll('#slot-list .draw-slot').length;
    const slotCountEl = $('slot-count');
    if (slotCountEl) slotCountEl.textContent = n;

    const slotEmptyEl = $('slot-empty');
    if (slotEmptyEl) slotEmptyEl.classList.toggle('hidden', n > 0);

    const isAllPlotted = (pool.length === 0);
    const completedEl = $('draw-completed');
    const machineBodyEl = $('draw-machine-body');

    if (completedEl && machineBodyEl) {
      completedEl.classList.toggle('hidden', !isAllPlotted);
      completedEl.classList.toggle('flex', isAllPlotted);
      machineBodyEl.classList.toggle('hidden', isAllPlotted);
    }

    if (msg && !spinning) {
      if (pool.length === 1) {
        msg.textContent = '⚠️ Tersisa 1 tim (jumlah ganjil). Tim lainnya sudah diplotting.';
      } else {
        msg.textContent = '';
      }
    }

    if (btn && !spinning) {
      btn.disabled = (pool.length < 2);
    }
  };

  function shuffle(a) {
    a = a.slice();
    for (let i = a.length - 1; i > 0; i--) {
      const j = Math.floor(Math.random() * (i + 1));
      [a[i], a[j]] = [a[j], a[i]];
    }
    return a;
  }

  // ===== Roda undian (canvas) + jarum di atas =====
  const canvas = $('wheel');
  const ctx = canvas.getContext('2d');
  const COLORS = ['#2563eb', '#7c3aed', '#db2777', '#ea580c', '#16a34a', '#0891b2', '#ca8a04', '#dc2626'];
  let rotation = 0;           // radian
  let wheelTeams = pool.slice();
  let hlIdx = -1, hlPulse = 0; // segmen terpilih (sorot)

  // Sorot segmen terpilih: segmen lain meredup, segmen terpilih berkedip
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

  function fit() {
    const size = canvas.clientWidth || 320;
    const dpr = window.devicePixelRatio || 1;
    canvas.width = canvas.height = Math.round(size * dpr);
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    drawWheel();
  }

  function drawWheel() {
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

  // Putar roda hingga jarum (atas) berhenti pada segmen terpilih; return Promise<index>
  function spinWheel(duration) {
    return new Promise((resolve) => {
      const n = wheelTeams.length;
      const seg = (Math.PI * 2) / n;
      const idx = Math.floor(Math.random() * n);
      const jitter = (Math.random() - 0.5) * seg * 0.7;
      // pusat segmen idx harus berada di sudut -90° (jarum di atas)
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

  const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

  function esc(s) {
    return String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  }
  // Isi kartu tim: nama + anggota
  function teamHtml(t, nameCls) {
    const mem = (t.members || []).map((m) => `<div class="truncate">${esc(m)}</div>`).join('');
    return `<div class="${nameCls || 'text-sm'} font-black leading-tight">${esc(t.name)}</div>` +
           (mem ? `<div class="mt-1 text-[11px] font-semibold opacity-75 leading-snug">${mem}</div>` : '');
  }

  function setPick(el, team) {
    el.innerHTML = teamHtml(team);
    el.classList.remove('border-dashed');
    el.animate(
      [{ transform: 'scale(1.18)', boxShadow: '0 0 0 0 rgba(245,158,11,.8)' },
       { transform: 'scale(1)', boxShadow: '0 0 0 14px rgba(245,158,11,0)' }],
      { duration: 550, easing: 'cubic-bezier(.2,.8,.2,1)' }
    );
  }
  function resetPicks() {
    for (const id of ['pick-a', 'pick-b']) {
      const el = $(id); el.textContent = '—';
      el.classList.add('border-dashed');
    }
  }

  // Kartu pemenang: muncul (pop) di tengah roda, lalu meluncur ke kotak bawah
  async function flyToBox(team, targetEl, tone) {
    const wr = canvas.getBoundingClientRect();
    const tr = targetEl.getBoundingClientRect();
    const card = document.createElement('div');
    const toneCls = tone === 'a' ? 'bg-blue-600 border-blue-300' : 'bg-indigo-600 border-indigo-300';
    card.className = `fixed z-[10000] rounded-2xl border-2 px-4 py-3 text-center text-white shadow-2xl ${toneCls}`;
    card.style.cssText += `;left:${wr.left + wr.width / 2}px;top:${wr.top + wr.height / 2}px;width:210px;` +
      'transform:translate(-50%,-50%) scale(.2);opacity:0;pointer-events:none;' +
      'transition:transform .5s cubic-bezier(.34,1.56,.64,1),opacity .3s ease;';
    card.innerHTML = teamHtml(team, 'text-lg');
    document.body.appendChild(card);
    card.getBoundingClientRect(); // reflow
    card.style.transform = 'translate(-50%,-50%) scale(1.1)';
    card.style.opacity = '1';
    await sleep(1300);

    // meluncur ke kotak target
    card.style.transition = 'left .8s cubic-bezier(.65,0,.35,1),top .8s cubic-bezier(.65,0,.35,1),width .8s cubic-bezier(.65,0,.35,1),transform .8s ease,opacity .25s ease .6s';
    card.style.left = (tr.left + tr.width / 2) + 'px';
    card.style.top = (tr.top + tr.height / 2) + 'px';
    card.style.width = tr.width + 'px';
    card.style.transform = 'translate(-50%,-50%) scale(.9)';
    await sleep(850);
    setPick(targetEl, team);
    card.remove();
  }
  window.addEventListener('resize', fit);
  fit();
  syncCounts();

  async function save(slotId, a, b) {
    const body = new URLSearchParams({ team1_id: a.id, team2_id: b.id, category, _token: csrf });
    const res = await fetch(assignUrl(slotId), {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': csrf },
      body: body.toString(),
    });
    let data = {};
    try { data = await res.json(); } catch (e) {}
    if (!res.ok || !data.ok) throw new Error(data.error || 'Gagal menyimpan hasil undian.');
  }

  btn.addEventListener('click', async () => {
    if (spinning) return;
    msg.textContent = '';
    const slotEl = selectedSlot();
    if (!slotEl) { msg.textContent = '⚠️ Pilih slot pertandingan terlebih dahulu.'; return; }
    if (pool.length < 2) { msg.textContent = '⚠️ Tim tersedia kurang dari 2.'; return; }

    spinning = true; btn.disabled = true; btn.textContent = '🎰 Mengundi…';
    const slotId = slotEl.value;
    const label = slotEl.closest('.draw-slot').querySelector('b').dataset.label || '';
    resetPicks();
    wheelTeams = pool.slice();
    drawWheel();

    // Putaran 1: tim pertama -> kiri bawah, lalu dihapus dari roda
    const i1 = await spinWheel(4200);
    const a = wheelTeams[i1];
    await highlightSegment(i1);
    await flyToBox(a, $('pick-a'), 'a');
    wheelTeams.splice(i1, 1);
    hlIdx = -1;
    drawWheel();
    await sleep(400);

    // Putaran 2: tim kedua -> kanan bawah (setelah "VS")
    const i2 = await spinWheel(4200);
    const b = wheelTeams[i2];
    await highlightSegment(i2);
    await flyToBox(b, $('pick-b'), 'b');
    hlIdx = -1;
    drawWheel();
    await sleep(500);

    $('modal-slot').textContent = 'Match ' + label;
    $('modal-a').innerHTML = teamHtml(a, 'text-lg text-blue-300');
    $('modal-b').innerHTML = teamHtml(b, 'text-lg text-indigo-300');
    $('modal-status').textContent = 'Menyimpan ke database…';
    const closeBtn = $('modal-close');
    closeBtn.disabled = true; closeBtn.textContent = 'Menyimpan…';
    $('draw-modal').classList.remove('hidden'); $('draw-modal').classList.add('flex');

    try {
      await save(slotId, a, b);
      pool = pool.filter((t) => t.id !== a.id && t.id !== b.id);
      document.querySelectorAll('#team-list li').forEach((li) => {
        const id = +li.dataset.teamId;
        if (id === a.id || id === b.id) li.remove();
      });
      slotEl.closest('.draw-slot').remove();
      syncCounts();
      $('modal-status').textContent = '✅ Tersimpan. Pertandingan ini kini tampil di tab Bracket.';
      closeBtn.textContent = 'Tutup';
    } catch (err) {
      $('modal-status').textContent = '❌ ' + err.message;
      closeBtn.textContent = 'Tutup (tidak tersimpan)';
    } finally {
      closeBtn.disabled = false;
    }
  });

  $('modal-close').addEventListener('click', () => {
    $('draw-modal').classList.add('hidden'); $('draw-modal').classList.remove('flex');
    spinning = false; btn.disabled = false; btn.textContent = '🎰 Spin Undian!';
    resetPicks();
    wheelTeams = pool.slice();
    drawWheel();
    syncCounts();
  });
})();
</script>
<?php endif; ?>

