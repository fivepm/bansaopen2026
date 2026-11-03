<h1>🏸 Badminton</h1>
<div class="grid">
  <div class="card stat"><b><?= $stats['teams'] ?></b><span>Tim terdaftar</span></div>
  <div class="card stat"><b><?= $stats['live'] ?></b><span>Sedang berlangsung</span></div>
  <div class="card stat"><b><?= $stats['done'] ?></b><span>Pertandingan selesai</span></div>
</div>
<div class="grid">
  <a class="card sport" href="<?= url('badminton/bracket') ?>"><h2>Bagan Pertandingan</h2><p class="muted">Bracket dari penyisihan hingga final</p></a>
  <a class="card sport" href="<?= url('badminton/live') ?>"><h2>Pertandingan Berlangsung</h2><p class="muted">Skor sementara real-time</p></a>
  <a class="card sport" href="<?= url('badminton/hasil') ?>"><h2>Hasil &amp; Skor</h2><p class="muted">Riwayat pertandingan selesai</p></a>
</div>

