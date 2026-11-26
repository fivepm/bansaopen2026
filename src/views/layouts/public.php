<?php
/** @var string $content */
$flash = flash();
$siteLogo = \App\Models\Setting::logoUrl();
$isFullWidth = !empty($fullWidth);
?>
<!DOCTYPE html>
<html lang="id" class="dark">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<script>
  // Inisialisasi tema instan sebelum render halaman (mencegah kedipan / flicker)
  (function() {
    try {
      const savedTheme = localStorage.getItem('bansa_theme');
      if (savedTheme === 'light') {
        document.documentElement.classList.remove('dark');
        document.documentElement.classList.add('light');
      } else {
        document.documentElement.classList.add('dark');
        document.documentElement.classList.remove('light');
      }
    } catch (e) {
      document.documentElement.classList.add('dark');
    }
  })();
</script>
<?php if ($siteLogo): ?><link rel="icon" href="<?= e($siteLogo) ?>"><?php endif; ?>
<title><?= e($title ?? '') ?> · <?= e($_ENV['APP_NAME'] ?? 'Bansa Open') ?></title>
<link rel="icon" type="image/png" href="<?= e(favicon_url()) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<?php if (!empty($refresh)): ?><meta http-equiv="refresh" content="<?= (int)$refresh ?>"><?php endif; ?>
<?php require __DIR__ . '/../partials/tailwind.php'; ?>
</head>
<body class="min-h-screen bg-blue-100 dark:bg-[#090d16] text-slate-100 antialiased flex flex-col font-['Plus_Jakarta_Sans',system-ui,sans-serif] selection:bg-blue-600 selection:text-white">
<header class="sticky top-0 z-50 border-b border-slate-800/80 bg-[#090d16]/85 backdrop-blur-md">
  <div class="<?= $isFullWidth ? 'w-full px-4 sm:px-6 lg:px-8' : 'container mx-auto max-w-7xl px-4' ?> flex flex-wrap items-center justify-between py-3.5">
    <a class="flex items-center gap-3 text-lg font-extrabold tracking-tight text-white transition hover:opacity-90" href="<?= url('/') ?>">
      <?php if ($siteLogo): ?>
        <img src="<?= e($siteLogo) ?>" alt="Logo Bansa Open" class="h-9 w-auto object-contain drop-shadow-md">
      <?php else: ?>
        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-500 text-lg shadow-md shadow-blue-500/20">🏆</span>
      <?php endif; ?>
      <span class="bg-gradient-to-r from-white via-slate-100 to-slate-300 bg-clip-text text-transparent">
        <?= e($_ENV['APP_NAME'] ?? 'Bansa Open') ?>
      </span>
    </a>

    <!-- Area Kanan Header Mobile: Tombol Tema (di samping toggle menu) & Tombol Toggle Menu -->
    <div class="flex items-center gap-2 md:hidden">
      <!-- Tombol Tema Mobile (di samping toggle menu) -->
      <button type="button" 
              class="theme-toggle-btn flex h-9 w-9 items-center justify-center rounded-xl border border-slate-700/80 bg-slate-900/60 text-slate-300 hover:border-slate-500 hover:text-white transition shadow-sm active:scale-95" 
              title="Ganti Tema (Gelap / Terang)" 
              aria-label="Toggle Dark/Light Mode"
              onclick="toggleTheme()">
        <span class="theme-icon-dark hidden text-base leading-none">🌙</span>
        <span class="theme-icon-light text-base leading-none">☀️</span>
      </button>

      <!-- Tombol Toggle Header (Menu Mobile) -->
      <button type="button" class="flex h-9 w-9 items-center justify-center rounded-xl border border-slate-700/80 text-slate-300 hover:border-slate-600 hover:text-white" aria-label="Menu" onclick="document.getElementById('mobile-menu').classList.toggle('hidden')">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
      </button>
    </div>

    <nav id="mobile-menu" class="hidden w-full flex-col gap-1.5 pt-3 md:flex md:w-auto md:flex-row md:items-center md:gap-1 md:pt-0">
      <?php
        $currentPath = trim((string)parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
        $isHome = ($currentPath === '' || str_ends_with($currentPath, 'public') || $currentPath === 'index.php');
        $isAnySport = false;
        foreach (\App\Models\Sports::LIST as $slug => $s) {
            if (($sport ?? '') === $slug || str_contains($currentPath, $slug)) {
                $isAnySport = true;
                break;
            }
        }
      ?>

      <!-- 1. Beranda -->
      <a href="<?= url('/') ?>" class="rounded-xl px-3.5 py-2 text-sm font-semibold transition <?= $isHome ? 'bg-blue-600/15 text-blue-400 border border-blue-500/30 font-bold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' ?>">
        Beranda
      </a>

      <!-- 2. Dropdown Cabor -->
      <details class="public-cabor-dropdown relative group">
        <summary class="flex cursor-pointer list-none items-center gap-1.5 rounded-xl px-3.5 py-2 text-sm font-semibold transition select-none <?= $isAnySport ? 'bg-blue-600/15 text-blue-400 border border-blue-500/30 font-bold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' ?>">
          <span>🏆</span>
          <span>Cabor</span>
          <svg class="h-3.5 w-3.5 text-slate-400 transition-transform duration-200 group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </summary>
        <div class="mt-1.5 w-full rounded-2xl border border-slate-800 bg-slate-900/95 p-1.5 shadow-2xl backdrop-blur-xl md:absolute md:left-0 md:top-full md:mt-2 md:w-48 z-50">
          <?php foreach (\App\Models\Sports::LIST as $slug => $s): ?>
            <?php $isActive = ($sport ?? '') === $slug || str_contains($currentPath, $slug); ?>
            <a href="<?= url($slug) ?>" class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs sm:text-sm font-medium transition <?= $isActive ? 'bg-blue-600/20 text-blue-300 font-bold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' ?>">
              <span class="text-base"><?= $s['emoji'] ?></span>
              <span><?= e($s['label']) ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      </details>

      <!-- 3. Kepanitiaan -->
      <a href="<?= url('kepanitiaan') ?>" class="flex items-center gap-1.5 rounded-xl px-3.5 py-2 text-sm font-semibold transition <?= str_contains($currentPath, 'kepanitiaan') ? 'bg-blue-600/15 text-blue-400 border border-blue-500/30 font-bold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' ?>">
        <span>👥</span>
        <span>Kepanitiaan</span>
      </a>

      <!-- Tombol Tema Desktop -->
      <div class="hidden md:flex items-center pl-2 ml-1 border-l border-slate-800/80">
        <button type="button" 
                class="theme-toggle-btn flex h-9 w-9 items-center justify-center rounded-xl border border-slate-700/80 bg-slate-900/60 text-slate-300 hover:border-slate-500 hover:text-white transition shadow-sm active:scale-95" 
                title="Ganti Tema (Gelap / Terang)" 
                aria-label="Toggle Dark/Light Mode"
                onclick="toggleTheme()">
          <span class="theme-icon-dark hidden text-base leading-none">🌙</span>
          <span class="theme-icon-light text-base leading-none">☀️</span>
        </button>
      </div>
    </nav>
  </div>
</header>

<main class="<?= $isFullWidth ? 'w-full max-w-none flex-1 px-3 sm:px-6 lg:px-8 py-6' : 'container mx-auto max-w-7xl flex-1 px-4 py-8' ?>">
  <?php if ($flash): ?>
    <div class="mb-6 flex items-center gap-3 rounded-2xl border <?= $flash['type'] === 'success' ? 'border-emerald-500/30 bg-emerald-950/40 text-emerald-300' : 'border-rose-500/30 bg-rose-950/40 text-rose-300' ?> p-4 shadow-lg backdrop-blur-sm">
      <span class="text-xl"><?= $flash['type'] === 'success' ? '✓' : '⚠' ?></span>
      <p class="text-sm font-medium"><?= e($flash['msg']) ?></p>
    </div>
  <?php endif; ?>

  <?= $content ?>
</main>

<footer class="border-t border-slate-800/80 bg-[#070a12] py-8 text-slate-500">
  <div class="<?= $isFullWidth ? 'w-full px-4 sm:px-6 lg:px-8' : 'container mx-auto max-w-7xl px-4' ?> flex flex-col items-center justify-between gap-4 text-center text-sm sm:flex-row sm:text-left">
    <div class="flex items-center gap-2">
      <span class="font-bold text-slate-300"><?= e($_ENV['APP_NAME'] ?? 'Bansa Open') ?></span>
      <span>&copy; <?= date('Y') ?> Â· Turnamen Olahraga Pemuda LDII</span>
    </div>
    <div class="text-xs text-slate-600">
      Real-time Bracket &amp; Live Score Engine
    </div>
  </div>
</footer>

<script>
// ================= SHARE JPG (Bracket & Laga Grup) =================
window.BANSA_SHARE = {
  logo: <?= json_encode($siteLogo ? (string)$siteLogo : '') ?>,
  name: <?= json_encode((string)($_ENV['APP_NAME'] ?? 'Bansa Open')) ?>,
  siteUrl: new URL(<?= json_encode(url('/')) ?>, window.location.href).href
};

function loadHtml2Canvas() {
  if (window.html2canvas) return Promise.resolve(window.html2canvas);
  return new Promise((resolve, reject) => {
    const s = document.createElement('script');
    s.src = 'https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js';
    s.onload = () => resolve(window.html2canvas);
    s.onerror = () => reject(new Error('Gagal memuat pustaka gambar (periksa koneksi internet).'));
    document.head.appendChild(s);
  });
}

function loadShareLogo(src) {
  return new Promise((resolve) => {
    if (!src) return resolve(null);
    const img = new Image();
    img.crossOrigin = 'anonymous';
    img.onload = () => resolve(img);
    img.onerror = () => resolve(null);
    img.src = src;
  });
}

function drawShareFit(ctx, text, x, y, maxW, align) {
  ctx.textAlign = align || 'left';
  ctx.textBaseline = 'middle';
  let t = text;
  while (ctx.measureText(t).width > maxW && t.length > 4) t = t.slice(0, -2);
  if (t !== text) t = t.trim() + '…';
  ctx.fillText(t, x, y);
}

/**
 * Ekspor elemen menjadi JPG lengkap dengan header (logo + judul) dan footer (link website).
 */
async function shareElementAsJpg(target, opts) {
  opts = opts || {};
  const btn = opts.button || null;
  const oldHtml = btn ? btn.innerHTML : '';
  if (!target) { alert('Konten belum tersedia untuk dibagikan.'); return; }
  if (btn) { btn.disabled = true; btn.innerHTML = '⏳ Memproses...'; }

  const restore = [];
  try {
    const h2c = await loadHtml2Canvas();
    const logo = await loadShareLogo(window.BANSA_SHARE.logo);
    if (document.fonts && document.fonts.ready) { try { await document.fonts.ready; } catch (e) {} }

    // Buka area scroll horizontal agar seluruh bagan ikut tertangkap
    [target, ...target.querySelectorAll('*')].forEach(el => {
      const cs = getComputedStyle(el);
      if (['auto', 'scroll'].includes(cs.overflowX) || ['auto', 'scroll'].includes(cs.overflowY)) {
        restore.push([el, el.style.overflow, el.style.maxHeight, el.style.width]);
        el.style.overflow = 'visible';
        el.style.maxHeight = 'none';
      }
    });
    const sw = Math.max(target.scrollWidth, target.offsetWidth);
    const sh = Math.max(target.scrollHeight, target.offsetHeight);

    const isDark = document.documentElement.classList.contains('dark');
    let bg = getComputedStyle(document.body).backgroundColor;
    if (!bg || bg === 'rgba(0, 0, 0, 0)' || bg === 'transparent') bg = isDark ? '#090d16' : '#dbeafe';

    const shot = await h2c(target, {
      scale: 2, useCORS: true, backgroundColor: bg,
      width: sw, height: sh, windowWidth: Math.max(sw, window.innerWidth), logging: false,
      ignoreElements: (el) => el.hasAttribute && el.hasAttribute('data-share-ignore'),
      onclone: (doc, clonedRoot) => {
        // Koreksi html2canvas: teks cenderung tergambar lebih rendah dari kotaknya.
        // Naikkan elemen yang memuat teks secara langsung sebesar SHARE_TEXT_LIFT (em).
        const SHARE_TEXT_LIFT = 0.3;
        const hasOwnText = (el) => Array.from(el.childNodes).some(n => n.nodeType === 3 && n.textContent.trim() !== '');
        const view = doc.defaultView;
        const root = clonedRoot && clonedRoot.nodeType === 1 ? clonedRoot : null;
        const scope = root ? [root, ...root.querySelectorAll('*')] : [];
        scope.forEach(el => {
          if (!hasOwnText(el)) return;
          const cs = view.getComputedStyle(el);
          if (cs.display === 'inline' && el.parentElement && hasOwnText(el.parentElement)) return;
          if (cs.position === 'static') el.style.position = 'relative';
          el.style.top = (parseFloat(cs.top) || 0) - (parseFloat(cs.fontSize) * SHARE_TEXT_LIFT) + 'px';
        });
      }
    });

    // Susun kanvas akhir: header + konten + footer
    const scale = 2;
    const W = Math.max(shot.width, 900 * scale);
    const headerH = 110 * scale, footerH = 70 * scale, pad = 24 * scale;
    const canvas = document.createElement('canvas');
    canvas.width = W;
    canvas.height = headerH + shot.height + footerH + pad;
    const ctx = canvas.getContext('2d');

    ctx.fillStyle = bg; ctx.fillRect(0, 0, canvas.width, canvas.height);

    // Header
    const g = ctx.createLinearGradient(0, 0, W, 0);
    g.addColorStop(0, '#1d4ed8'); g.addColorStop(0.55, '#4f46e5'); g.addColorStop(1, '#0891b2');
    ctx.fillStyle = g; ctx.fillRect(0, 0, W, headerH);
    let tx = pad;
    if (logo) {
      const lh = 70 * scale, lw = lh * (logo.width / logo.height);
      ctx.fillStyle = 'rgba(255,255,255,0.95)';
      ctx.beginPath(); ctx.roundRect ? ctx.roundRect(pad - 8 * scale, (headerH - lh) / 2 - 8 * scale, lw + 16 * scale, lh + 16 * scale, 14 * scale) : ctx.rect(pad - 8 * scale, (headerH - lh) / 2 - 8 * scale, lw + 16 * scale, lh + 16 * scale);
      ctx.fill();
      ctx.drawImage(logo, pad, (headerH - lh) / 2, lw, lh);
      tx = pad + lw + 28 * scale;
    }
    const ff = "'Plus Jakarta Sans', system-ui, sans-serif";
    ctx.fillStyle = '#ffffff';
    ctx.font = `800 ${28 * scale}px ${ff}`;
    drawShareFit(ctx, opts.title || window.BANSA_SHARE.name, tx, headerH / 2 - 15 * scale, W - tx - pad, 'left');
    ctx.fillStyle = 'rgba(255,255,255,0.85)';
    ctx.font = `600 ${16 * scale}px ${ff}`;
    drawShareFit(ctx, opts.subtitle || window.BANSA_SHARE.name, tx, headerH / 2 + 17 * scale, W - tx - pad, 'left');

    // Konten (di tengah horizontal)
    ctx.drawImage(shot, Math.round((W - shot.width) / 2), headerH + pad / 2);

    // Footer
    const fy = headerH + shot.height + pad;
    ctx.fillStyle = '#0f172a'; ctx.fillRect(0, fy, W, footerH);
    ctx.fillStyle = '#ffffff';
    ctx.font = `700 ${18 * scale}px ${ff}`;
    const linkText = window.BANSA_SHARE.siteUrl.replace(/^https?:\/\//, '').replace(/\/$/, '');
    drawShareFit(ctx, '🌐 ' + linkText, W / 2, fy + footerH / 2 - 11 * scale, W - pad * 2, 'center');
    ctx.fillStyle = '#94a3b8';
    ctx.font = `500 ${12 * scale}px ${ff}`;
    drawShareFit(ctx, window.BANSA_SHARE.name + ' Â· Dibuat ' + new Date().toLocaleString('id-ID'), W / 2, fy + footerH / 2 + 15 * scale, W - pad * 2, 'center');

    const blob = await new Promise(res => canvas.toBlob(res, 'image/jpeg', 0.92));
    if (!blob) throw new Error('Gagal membuat gambar.');
    const fileName = (opts.fileName || 'bansa-open') + '.jpg';
    const file = new File([blob], fileName, { type: 'image/jpeg' });

    if (navigator.canShare && navigator.canShare({ files: [file] })) {
      try {
        await navigator.share({ files: [file], title: opts.title || window.BANSA_SHARE.name, text: (opts.title || '') + '\n' + window.BANSA_SHARE.siteUrl });
        return;
      } catch (err) {
        if (err && err.name === 'AbortError') return;
      }
    }
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = fileName;
    document.body.appendChild(a); a.click(); a.remove();
    setTimeout(() => URL.revokeObjectURL(a.href), 5000);
  } catch (err) {
    console.error(err);
    alert(err.message || 'Gagal membuat gambar JPG.');
  } finally {
    restore.forEach(([el, ov, mh, w]) => { el.style.overflow = ov; el.style.maxHeight = mh; el.style.width = w; });
    if (btn) { btn.disabled = false; btn.innerHTML = oldHtml; }
  }
}

// Tutup dropdown cabor publik saat klik di luar
document.addEventListener('click', (e) => {
  if (!e.target.closest('.public-cabor-dropdown')) {
    document.querySelectorAll('.public-cabor-dropdown[open]').forEach(d => d.removeAttribute('open'));
  }
});

// SISTEM TOGGLE TEMA (GELAP / TERANG) BANSA OPEN
function toggleTheme() {
  const isDark = document.documentElement.classList.contains('dark');
  const targetTheme = isDark ? 'light' : 'dark';
  
  if (targetTheme === 'light') {
    document.documentElement.classList.remove('dark');
    document.documentElement.classList.add('light');
    try { localStorage.setItem('bansa_theme', 'light'); } catch(e){}
  } else {
    document.documentElement.classList.add('dark');
    document.documentElement.classList.remove('light');
    try { localStorage.setItem('bansa_theme', 'dark'); } catch(e){}
  }
  updateThemeIcons();

  // Render ulang garis SVG diagram bracket jika tersedia pada halaman aktif
  if (typeof window.drawAllBracketLines === 'function') {
    requestAnimationFrame(() => window.drawAllBracketLines());
  }
}

function updateThemeIcons() {
  const isLight = document.documentElement.classList.contains('light');
  document.querySelectorAll('.theme-icon-dark').forEach(el => {
    el.classList.toggle('hidden', !isLight);
  });
  document.querySelectorAll('.theme-icon-light').forEach(el => {
    el.classList.toggle('hidden', isLight);
  });
  document.querySelectorAll('.theme-toggle-btn').forEach(btn => {
    btn.setAttribute('title', isLight ? 'Beralih ke Mode Gelap' : 'Beralih ke Mode Terang');
  });
}

// Inisialisasi ikon tema saat DOM siap
document.addEventListener('DOMContentLoaded', updateThemeIcons);
updateThemeIcons();
</script>
</body>
</html>
