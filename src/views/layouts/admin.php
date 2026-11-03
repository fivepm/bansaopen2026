<?php
/** @var string $content */
$flash = flash();
$csrf = \App\Core\Csrf::token();
$isOperator = \App\Core\Auth::isOperator();
$isSuperAdmin = \App\Core\Auth::isSuperAdmin();
$user = \App\Core\Auth::user();

$liveCount = 0;
try {
    $liveCount = (int)(\App\Core\Database::one("SELECT COUNT(*) c FROM matches WHERE status = 'live'")['c'] ?? 0);
} catch (\Throwable) {}
?>
<!DOCTYPE html>
<html lang="id" class="dark">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf" content="<?= e($csrf) ?>">
<title><?= e($title ?? '') ?> · Panel Panitia</title>
<link rel="icon" type="image/png" href="<?= e(favicon_url()) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<?php require __DIR__ . '/../partials/tailwind.php'; ?>
</head>
<body class="min-h-screen bg-[#090d16] text-slate-100 antialiased flex flex-col font-['Plus_Jakarta_Sans',system-ui,sans-serif] selection:bg-blue-600 selection:text-white">
<header class="sticky top-0 z-50 border-b border-slate-800/80 bg-slate-950/90 backdrop-blur-md">
  <div class="container mx-auto flex max-w-6xl flex-wrap items-center justify-between px-4 py-3">
    <a class="flex items-center gap-2.5 text-base sm:text-lg font-black text-white" href="<?= url($isOperator ? 'admin/live-panel' : 'admin') ?>">
      <span><?= $isOperator ? '⚡' : '⚙️' ?></span>
      <span class="bg-gradient-to-r from-white to-slate-300 bg-clip-text text-transparent">
        <?= $isOperator ? 'Operator Wasit' : 'Panel Panitia' ?>
      </span>
    </a>

    <button type="button" class="flex h-9 w-9 items-center justify-center rounded-xl border border-slate-700/80 text-slate-300 hover:border-slate-600 hover:text-white md:hidden" aria-label="Menu" onclick="document.getElementById('admin-menu').classList.toggle('hidden')">
      <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
    </button>

    <nav id="admin-menu" class="hidden w-full flex-col gap-1.5 pt-3 md:flex md:w-auto md:flex-row md:items-center md:gap-2 md:pt-0">
      <?php if ($isSuperAdmin): ?>
        <a href="<?= url('admin') ?>" class="rounded-xl px-3 py-1.5 text-xs font-semibold text-slate-300 hover:bg-slate-800 hover:text-white transition">
          Dashboard
        </a>

        <!-- Dropdown 1: Cabor (Live Panel, Badminton, Futsal, Pingpong) -->
        <details class="admin-dropdown relative">
          <summary class="flex cursor-pointer list-none items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-bold transition <?= (!empty($sport) || str_contains($_SERVER['REQUEST_URI'] ?? '', 'live-panel')) ? 'bg-blue-600/20 text-blue-300 border border-blue-500/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' ?>">
            <span>🏆 Cabor</span>
            <?php if ($liveCount > 0): ?>
              <span class="flex h-2 w-2 rounded-full bg-red-500 animate-ping"></span>
            <?php endif; ?>
            <svg class="h-3.5 w-3.5 text-slate-400 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
          </summary>
          <div class="absolute left-0 mt-1.5 z-50 w-52 rounded-2xl border border-slate-800 bg-slate-900/95 p-1.5 shadow-2xl backdrop-blur-xl">
            <a href="<?= url('admin/live-panel') ?>" class="flex items-center justify-between rounded-xl px-3 py-2 text-xs font-bold text-red-400 hover:bg-red-950/50 hover:text-red-300 transition">
              <span class="flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-red-500 animate-pulse"></span>
                <span>Live Panel</span>
              </span>
              <?php if ($liveCount > 0): ?>
                <span class="rounded-full bg-red-500 px-1.5 py-0.2 text-[10px] font-black text-white"><?= $liveCount ?></span>
              <?php endif; ?>
            </a>
            <div class="my-1 border-t border-slate-800/80"></div>
            <?php foreach (\App\Models\Sports::LIST as $slug => $s): ?>
              <a href="<?= url("admin/$slug") ?>" class="flex items-center gap-2 rounded-xl px-3 py-2 text-xs font-semibold <?= ($sport ?? '') === $slug ? 'bg-blue-600/20 text-blue-400 font-bold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' ?> transition">
                <span><?= $s['emoji'] ?></span>
                <span><?= e($s['label']) ?></span>
              </a>
            <?php endforeach; ?>
          </div>
        </details>

        <!-- Dropdown 2: Pengaturan (Kepanitiaan, Kontak, Jadwal, Wasit/User, Sponsor, Logo) -->
        <details class="admin-dropdown relative">
          <summary class="flex cursor-pointer list-none items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-bold transition <?= (str_contains($_SERVER['REQUEST_URI'] ?? '', '/committees') || str_contains($_SERVER['REQUEST_URI'] ?? '', '/contacts') || str_contains($_SERVER['REQUEST_URI'] ?? '', '/users') || str_contains($_SERVER['REQUEST_URI'] ?? '', '/sponsors') || str_contains($_SERVER['REQUEST_URI'] ?? '', '/settings') || str_contains($_SERVER['REQUEST_URI'] ?? '', '/schedules')) ? 'bg-blue-600/20 text-blue-300 border border-blue-500/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' ?>">
            <span>⚙️ Pengaturan</span>
            <svg class="h-3.5 w-3.5 text-slate-400 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
          </summary>
          <div class="absolute left-0 mt-1.5 z-50 w-52 rounded-2xl border border-slate-800 bg-slate-900/95 p-1.5 shadow-2xl backdrop-blur-xl">
            <a href="<?= url('admin/committees') ?>" class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-semibold text-slate-300 hover:bg-slate-800 hover:text-white transition">
              <span>👥</span>
              <span>Kepanitiaan</span>
            </a>
            <a href="<?= url('admin/contacts') ?>" class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-semibold text-slate-300 hover:bg-slate-800 hover:text-white transition">
              <span>📱</span>
              <span>Kontak &amp; Sosmed</span>
            </a>
            <a href="<?= url('admin/schedules') ?>" class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-semibold text-slate-300 hover:bg-slate-800 hover:text-white transition">
              <span>📅</span>
              <span>Jadwal &amp; Lokasi</span>
            </a>
            <!-- <div class="my-1 border-t border-slate-800/80"></div> -->
            <a href="<?= url('admin/users') ?>" class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-semibold text-slate-300 hover:bg-slate-800 hover:text-white transition">
              <span>🛡️</span>
              <span>Wasit / User</span>
            </a>
            <a href="<?= url('admin/sponsors') ?>" class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-semibold text-slate-300 hover:bg-slate-800 hover:text-white transition">
              <span>🤝</span>
              <span>Sponsor</span>
            </a>
            <a href="<?= url('admin/settings') ?>" class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-semibold text-slate-300 hover:bg-slate-800 hover:text-white transition">
              <span>🖼️</span>
              <span>Logo</span>
            </a>
          </div>
        </details>

      <?php else: ?>
        <!-- Menu Khusus Operator Wasit -->
        <a href="<?= url('admin/live-panel') ?>" class="flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-bold text-red-400 bg-red-950/40 border border-red-500/30 hover:bg-red-900/50 transition">
          <span class="h-2 w-2 rounded-full bg-red-500 animate-pulse"></span>
          <span>Live Panel</span>
          <?php if ($liveCount > 0): ?>
            <span class="rounded-full bg-red-500 px-1.5 py-0.2 text-[10px] font-black text-white"><?= $liveCount ?></span>
          <?php endif; ?>
        </a>
      <?php endif; ?>

      <a href="<?= url('/') ?>" target="_blank" class="rounded-xl border border-slate-700/60 bg-slate-800/40 px-3 py-1.5 text-xs font-semibold text-slate-300 hover:bg-slate-700 hover:text-white transition">
        Lihat Publik ↗
      </a>

      <form method="post" action="<?= url('admin/logout') ?>" class="inline ml-1">
        <?= csrf_field() ?>
        <button class="btn small danger !py-1 !px-2.5 text-xs">Keluar</button>
      </form>
    </nav>
  </div>
</header>

<main class="container mx-auto max-w-6xl flex-1 px-4 py-8">
  <?php if ($flash): ?>
    <div class="mb-6 flex items-center gap-3 rounded-2xl border <?= $flash['type'] === 'success' ? 'border-emerald-500/30 bg-emerald-950/40 text-emerald-300' : 'border-rose-500/30 bg-rose-950/40 text-rose-300' ?> p-4 shadow-lg backdrop-blur-sm">
      <span class="text-xl"><?= $flash['type'] === 'success' ? '✓' : '⚠' ?></span>
      <p class="text-sm font-medium"><?= e($flash['msg']) ?></p>
    </div>
  <?php endif; ?>

  <?php if (!empty($sport) && $isSuperAdmin): ?>
    <?php require __DIR__ . '/../partials/admin_sport_tabs.php'; ?>
  <?php endif; ?>

  <?= $content ?>
</main>

<script>
// Menutup dropdown navbar admin saat klik di luar atau saat membuka dropdown lainnya
document.addEventListener('click', (e) => {
  if (!e.target.closest('.admin-dropdown')) {
    document.querySelectorAll('.admin-dropdown[open]').forEach(d => d.removeAttribute('open'));
  }
});
document.querySelectorAll('.admin-dropdown').forEach(dd => {
  dd.addEventListener('toggle', () => {
    if (dd.open) {
      document.querySelectorAll('.admin-dropdown').forEach(other => {
        if (other !== dd) other.removeAttribute('open');
      });
    }
  });
});
</script>
</body>
</html>
