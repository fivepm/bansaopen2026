<?php
declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/** Upload gambar aman: hanya JPG/PNG, maks 2 MB, nama file acak, disimpan di public/assets/uploads/. */
final class Upload
{
    public const MAX_BYTES = 2 * 1024 * 1024;
    private const ALLOWED = ['image/jpeg' => 'jpg', 'image/png' => 'png'];

    public static function root(): string
    {
        return dirname(__DIR__, 2) . '/public/assets/uploads';
    }

    /**
     * @param array  $file   entri dari $_FILES
     * @param string $folder sub-folder, mis. "logo" atau "sponsors"
     * @return string path relatif terhadap public/assets (mis. "uploads/logo/ab12.png")
     * @throws RuntimeException berisi pesan yang aman ditampilkan ke user
     */
    public static function image(array $file, string $folder): string
    {
        $err = $file['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($err !== UPLOAD_ERR_OK) {
            throw new RuntimeException(match ($err) {
                UPLOAD_ERR_NO_FILE => 'Pilih file gambar terlebih dahulu.',
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Ukuran file melebihi batas (maks 2 MB).',
                default => 'Upload gagal, silakan coba lagi.',
            });
        }
        if (!is_uploaded_file($file['tmp_name'])) {
            throw new RuntimeException('File upload tidak valid.');
        }
        if ($file['size'] > self::MAX_BYTES) {
            throw new RuntimeException('Ukuran file melebihi batas (maks 2 MB).');
        }

        // Validasi berdasarkan isi file, bukan ekstensi/MIME dari klien
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!isset(self::ALLOWED[$mime]) || @getimagesize($file['tmp_name']) === false) {
            throw new RuntimeException('Format file harus JPG atau PNG.');
        }

        $folder = preg_replace('/[^a-z0-9_-]/i', '', $folder);
        $dir = self::root() . '/' . $folder;
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException('Folder upload tidak dapat dibuat.');
        }

        $name = bin2hex(random_bytes(12)) . '.' . self::ALLOWED[$mime];
        if (!move_uploaded_file($file['tmp_name'], "$dir/$name")) {
            throw new RuntimeException('File gagal disimpan.');
        }
        @chmod("$dir/$name", 0644);

        return "uploads/$folder/$name";
    }

    /**
     * Upload gambar dari data URL base64 (mis. hasil crop JavaScript canvas).
     * @param string $dataUrl data URL base64 (contoh: "data:image/jpeg;base64,...")
     * @param string $folder  sub-folder, mis. "committees"
     * @return string path relatif terhadap public/assets (mis. "uploads/committees/ab12.jpg")
     * @throws RuntimeException jika data tidak valid atau gagal disimpan
     */
    public static function base64(string $dataUrl, string $folder): string
    {
        if (!preg_match('/^data:image\/(jpeg|png|jpg);base64,/', $dataUrl, $matches)) {
            throw new RuntimeException('Format data gambar tidak valid.');
        }

        $ext = ($matches[1] === 'png') ? 'png' : 'jpg';
        $decoded = base64_decode(substr($dataUrl, strpos($dataUrl, ',') + 1), true);
        if ($decoded === false) {
            throw new RuntimeException('Gagal memproses data gambar base64.');
        }

        if (strlen($decoded) > self::MAX_BYTES) {
            throw new RuntimeException('Ukuran gambar melebihi batas (maks 2 MB).');
        }

        $folder = preg_replace('/[^a-z0-9_-]/i', '', $folder);
        $dir = self::root() . '/' . $folder;
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException('Folder upload tidak dapat dibuat.');
        }

        $name = bin2hex(random_bytes(12)) . '.' . $ext;
        $dest = "$dir/$name";
        if (file_put_contents($dest, $decoded) === false) {
            throw new RuntimeException('File gambar gagal disimpan.');
        }
        @chmod($dest, 0644);

        return "uploads/$folder/$name";
    }

    /** Hapus file upload lama (hanya jika berada di dalam folder uploads). */
    public static function delete(?string $relative): void
    {
        if (!$relative) {
            return;
        }
        $path = realpath(dirname(__DIR__, 2) . '/public/assets/' . $relative);
        $root = realpath(self::root());
        if ($path && $root && str_starts_with($path, $root . DIRECTORY_SEPARATOR) && is_file($path)) {
            @unlink($path);
        }
    }
}

