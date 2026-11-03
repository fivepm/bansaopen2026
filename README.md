# Bansao Open 2026 — Manajemen Turnamen (PHP Native)

## Struktur
```
├── .env.example          # template konfigurasi (salin ke .env)
├── composer.json
├── phinx.php             # konfigurasi Phinx (baca .env)
├── db/migrations/        # admins, teams, matches
├── db/seeds/AdminSeeder.php
├── public/               # document root: index.php (front controller), assets/
└── src/
    ├── bootstrap.php, routes.php, helpers.php
    ├── Core/             # Router, Database (PDO dari .env), View, Auth, Csrf
    ├── Controllers/      # PublicController, Admin/{Auth,Dashboard,Team,Match}Controller
    ├── Models/MatchModel.php
    ├── Middleware/AuthMiddleware.php
    └── views/            # layouts, public, admin, partials
```

## Setup
1. `composer install`
2. `copy .env.example .env` lalu isi `DB_*`, `ADMIN_USERNAME`, `ADMIN_PASSWORD`
3. Buat database kosong sesuai `DB_NAME`
4. `vendor\bin\phinx migrate` lalu `vendor\bin\phinx seed:run`
5. Buka `http://localhost/bansaopen2026/` (Laragon) atau `php -S localhost:8000 -t public`
6. Login admin di `/admin/login`


## Tailwind CSS
Diatur lewat `TAILWIND_MODE` di `.env`:
- `cdn` (default, development): tanpa npm; CSS komponen diambil dari `resources/css/app.css`. Butuh internet.
- `build` (produksi): `npm install && npm run build` lalu set `TAILWIND_MODE=build`.


## Admin & Cabang Olahraga
- Login admin hanya lewat URL rahasia: `/<ADMIN_LOGIN_PATH>` (default `/admin-bansa-login`, atur di `.env`). Tamu yang membuka `/admin/*` mendapat 404.
- Panel admin per cabor: `/admin/{badminton|futsal|pingpong}` (Tim, Bracket/Pertandingan, Grup).
- Sistem turnamen per cabor: Gugur (bracket) atau Grup (round robin) - dipilih di halaman cabor.
- Setelah `git pull`: `vendor\bin\phinx migrate` (rename `sport`→`sport_type`, tabel `groups`, `group_standings`, kolom `stage`/`group_id` di `matches`, kolom `category` & `bracket_side`).

## Two-Sided Bracket (Grand Slam / E-Sports) & Kategori
- Tata letak bagan Kiri-Kanan (Two-Sided): Blok Kiri dan Blok Kanan bertanding menuju ke tengah (Grand Final & Perebutan Juara 3).
- Tim yang kalah di Semifinal otomatis masuk ke Perebutan Juara 3 (Third Place Playoff).
- Khusus cabor Badminton, bagan dibagi ke 2 kategori: Ganda Putra dan Ganda Putri, beralih instan dengan filter sub-tab JavaScript murni.

## Alur Admin Bracket & Auto-Advance Pemenang
1. **Tombol Mandiri "Set Live"**:
   - Berada langsung di setiap kotak pertandingan (status Belum Mulai yang timnya sudah lengkap).
   - Mengklik tombol ini seketika mengubah status menjadi `live` dan langsung mengarahkan ke **Live Panel** (`/admin/live-panel`).
2. **Modal "Ubah Status & Pengaturan"**:
   - Dibuka via tombol `⚙️ Ubah Status`.
   - Mengatur Pilihan Status (`upcoming` / `finished`). Opsi Live telah dipisahkan ke tombol mandiri.
   - Mengatur Pilihan Sistem Set: Poin Lanjut (2 Set) atau Rubber Set (Best of 3).
   - Mengatur Jadwal Pertandingan (Hari, Tanggal, Jam WIB, dan Lapangan) dengan live preview otomatis.
3. **Tampilan Skor Dinamis di Kotak Bracket**:
   - Menampilkan badge sistem pertandingan yang digunakan (`Poin Lanjut (2 Set)` atau `Rubber Set`).
   - Kolom Skor Poin Lanjut: Hanya 2 kolom skor (Set 1 dan Set 2).
   - Kolom Skor Rubber Set: 3 kolom skor (Set 1, Set 2, Set 3).
     - Jika menang 2-0 berturut-turut di Set 1 dan 2, kolom Set 3 otomatis strip (`-`).
     - Jika seri 1-1 di Set 1 dan 2, kolom Set 3 menampilkan skor angka penentu.
4. **Modal "Atur Tim" Khusus Babak Penyisihan**:
   - Hanya muncul pada kotak pertandingan Babak Penyisihan (First Round).
   - Babak lanjutan (Perempat Final, Semifinal, Final, Perebutan Juara 3) tidak menampilkan tombol "Atur Tim" untuk mencegah pengisian manual yang tidak sesuai alur bagan.
5. **Logika Otomatisasi Tim Lolos (Auto-Advance)**:
   - Ketika pertandingan selesai (`finished`), sistem PHP otomatis menghitung pemenang (berdasarkan Poin Lanjut akumulasi 2 set atau Rubber Set best of 3).
   - Pemenang otomatis dimasukkan ke slot pertandingan babak berikutnya (Round berikutnya).
   - Khusus babak Semifinal, pemenang otomatis melaju ke Grand Final dan tim yang kalah otomatis melaju ke Perebutan Juara 3.
6. **Aturan Alur Pertandingan & Pemilihan Set Aktif (Live Panel)**:
   - **Pemilihan Set Aktif & Tombol Selesai Set**: Wasit wajib menekan tombol alur (`Mulai Set 1`) untuk memulai pencatatan poin. Tombol set berikutnya (`Mulai Set 2`, `Mulai Set 3`) terkunci rapat sampai wasit menekan tombol **"✓ Selesai Set [X]"**.
   - **Penguncian Tombol Skor**: Tombol `+` dan `−` hanya aktif (*enabled*) pada set yang sedang dimainkan. Ketika tombol "Selesai Set" ditekan, skor set tersebut otomatis terkunci dan tombol set berikutnya terbuka dengan animasi pendar (*pulse*). Wasit juga dapat menekan "↺ Koreksi Skor" jika butuh merevisi poin.
   - **Transisi Set**:
     - *Poin Lanjut*: Saat wasit menekan "Mulai Set 2", skor akhir Set 1 otomatis disalin menjadi skor awal Set 2 (Carry-Over), dan tombol skor Set 2 terbuka.
     - *Rubber Set*: Saat wasit menekan "Mulai Set 2" atau "Mulai Set 3", skor set baru dimulai dari 0-0.
