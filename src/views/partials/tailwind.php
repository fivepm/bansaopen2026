<?php
/**
 * Mode Tailwind (.env → TAILWIND_MODE):
 *  - cdn   : Play CDN, tanpa npm / build. CSS komponen diambil dari resources/css/app.css (satu sumber).
 *  - build : memakai public/assets/app.css hasil `npm run build` (disarankan untuk produksi).
 */
$mode = $_ENV['TAILWIND_MODE'] ?? 'cdn';
$root = dirname(__DIR__, 3);

if ($mode === 'build'):
    ?>
<link rel="stylesheet" href="<?= asset('app.css') ?>?v=<?= @filemtime($root . '/public/assets/app.css') ?: 0 ?>">
<?php else:
    $css = (string)@file_get_contents($root . '/resources/css/app.css');
    $css = preg_replace('/^@tailwind\s+\w+;\s*$/m', '', $css);
    ?>
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config = { darkMode: 'class', theme: { extend: { colors: { pri: '#1d6ff2' } } } }</script>
<style type="text/tailwindcss">
<?= $css ?>
</style>
<?php endif; ?>
