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
      <span>&copy; <?= date('Y') ?> · Turnamen Olahraga Pemuda LDII</span>
    </div>
    <div class="text-xs text-slate-600">
      Real-time Bracket &amp; Live Score Engine
    </div>
  </div>
</footer>

<script>
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
