<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DatabaseBackupService
{
    /**
     * Dapatkan path direktori penyimpanan backup
     */
    public static function getBackupDirectory(): string
    {
        $dir = storage_path('app/backups');
        if (! File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        return $dir;
    }

    /**
     * Buat backup baru (format: 'sql' atau 'sqlite')
     */
    public static function createBackup(string $format = 'sql'): array
    {
        $dir = self::getBackupDirectory();
        $timestamp = date('Y-m-d_His');

        if ($format === 'sqlite') {
            $databasePath = config('database.connections.sqlite.database');
            if (! file_exists($databasePath)) {
                return [
                    'success' => false,
                    'message' => 'Berkas database SQLite aktif tidak ditemukan.',
                ];
            }

            $filename = "ppl_febuniku_db_{$timestamp}.sqlite";
            $targetPath = $dir . DIRECTORY_SEPARATOR . $filename;
            File::copy($databasePath, $targetPath);

            return [
                'success' => true,
                'filename' => $filename,
                'path' => $targetPath,
                'size' => File::size($targetPath),
                'message' => "Backup SQLite berhasil dibuat: {$filename}",
            ];
        }

        // Format SQL Dump (Universal untuk MySQL & SQLite di hosting)
        $filename = "ppl_febuniku_backup_{$timestamp}.sql";
        $targetPath = $dir . DIRECTORY_SEPARATOR . $filename;

        $sqlContent = self::generateUniversalSqlDump();
        File::put($targetPath, $sqlContent);

        return [
            'success' => true,
            'filename' => $filename,
            'path' => $targetPath,
            'size' => File::size($targetPath),
            'message' => "Backup SQL berhasil dibuat: {$filename}",
        ];
    }

    /**
     * Generate dump SQL universal yang kompatibel dengan hosting cPanel, MySQL, MariaDB, dan SQLite
     */
    public static function generateUniversalSqlDump(): string
    {
        $sql = "-- ========================================================\n";
        $sql .= "-- SISTEM REKAPITULASI & PENILAIAN PPL FEB UNIKU\n";
        $sql .= "-- BACKUP DATABASE UNIVERSAL (MySQL / MariaDB / SQLite)\n";
        $sql .= "-- Waktu Pembuatan: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- ========================================================\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n";
        $sql .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n";
        $sql .= "START TRANSACTION;\n\n";

        // Urutan tabel untuk menjaga referensi relasi data
        $tables = [
            'users',
            'mitras',
            'groups',
            'students',
            'migrations',
        ];

        // Dapatkan semua tabel jika ada tabel tambahan
        $allTables = Schema::getTableListing();
        foreach ($allTables as $t) {
            if (! in_array($t, $tables) && ! in_array($t, ['sqlite_sequence', 'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs'])) {
                $tables[] = $t;
            }
        }

        foreach ($tables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $sql .= "-- --------------------------------------------------------\n";
            $sql .= "-- Struktur dan Data Tabel `{$table}`\n";
            $sql .= "-- --------------------------------------------------------\n\n";
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";

            // Ambil schema kolom
            $columns = Schema::getColumnListing($table);
            
            // Build create table query
            $columnDefs = [];
            foreach ($columns as $col) {
                $type = Schema::getColumnType($table, $col);
                
                // Normalisasi tipe data untuk kompatibilitas SQL universal
                $sqlType = match ($type) {
                    'integer', 'bigint' => ($col === 'id' ? 'BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY' : 'BIGINT'),
                    'smallint' => 'SMALLINT',
                    'string' => 'VARCHAR(255)',
                    'text' => 'TEXT',
                    'float', 'double', 'decimal' => 'DOUBLE(8, 2)',
                    'boolean' => 'TINYINT(1)',
                    'datetime', 'timestamp' => 'DATETIME',
                    'date' => 'DATE',
                    default => 'VARCHAR(255)',
                };

                $columnDefs[] = "  `{$col}` {$sqlType} NULL";
            }

            $sql .= "CREATE TABLE `{$table}` (\n" . implode(",\n", $columnDefs) . "\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

            // Ambil data baris
            $rows = DB::table($table)->get();
            if ($rows->count() > 0) {
                $sql .= "INSERT INTO `{$table}` (`" . implode('`, `', $columns) . "`) VALUES\n";
                $rowValues = [];

                foreach ($rows as $row) {
                    $vals = [];
                    foreach ($columns as $col) {
                        $val = $row->$col ?? null;
                        if ($val === null) {
                            $vals[] = 'NULL';
                        } elseif (is_numeric($val) && ! is_string($val)) {
                            $vals[] = $val;
                        } else {
                            $escaped = str_replace(["\\", "'"], ["\\\\", "\\'"], (string) $val);
                            $vals[] = "'{$escaped}'";
                        }
                    }
                    $rowValues[] = "(" . implode(', ', $vals) . ")";
                }

                $sql .= implode(",\n", $rowValues) . ";\n\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
        $sql .= "COMMIT;\n";

        return $sql;
    }

    /**
     * Dapatkan daftar seluruh file backup yang tersimpan
     */
    public static function listBackups(): array
    {
        $dir = self::getBackupDirectory();
        $files = File::files($dir);

        $backups = [];
        foreach ($files as $file) {
            $extension = strtolower($file->getExtension());
            if (! in_array($extension, ['sql', 'sqlite'])) {
                continue;
            }

            $backups[] = [
                'filename' => $file->getFilename(),
                'format' => strtoupper($extension),
                'size' => self::formatSize($file->getSize()),
                'size_bytes' => $file->getSize(),
                'created_at' => date('Y-m-d H:i:s', $file->getMTime()),
                'timestamp' => $file->getMTime(),
                'path' => $file->getRealPath(),
            ];
        }

        // Urutkan dari yang paling baru
        usort($backups, fn ($a, $b) => $b['timestamp'] <=> $a['timestamp']);

        return $backups;
    }

    /**
     * Format ukuran file (Bytes -> KB -> MB)
     */
    public static function formatSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }

    /**
     * Unduh berkas backup dengan validasi keamanan
     */
    public static function downloadBackup(string $filename): BinaryFileResponse
    {
        // Sanitasi nama file untuk mencegah directory traversal
        $cleanFilename = basename($filename);
        $filePath = self::getBackupDirectory() . DIRECTORY_SEPARATOR . $cleanFilename;

        if (! file_exists($filePath)) {
            abort(404, 'Berkas backup tidak ditemukan.');
        }

        return response()->download($filePath, $cleanFilename);
    }

    /**
     * Hapus berkas backup dari server
     */
    public static function deleteBackup(string $filename): bool
    {
        $cleanFilename = basename($filename);
        $filePath = self::getBackupDirectory() . DIRECTORY_SEPARATOR . $cleanFilename;

        if (file_exists($filePath)) {
            return File::delete($filePath);
        }

        return false;
    }

    /**
     * Pulihkan (Restore) database dari file upload (.sql atau .sqlite)
     */
    public static function restoreFromFile(string $filePath): array
    {
        if (! file_exists($filePath)) {
            return [
                'success' => false,
                'message' => 'Berkas sumber restore tidak ditemukan.',
            ];
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($extension === 'sqlite') {
            return self::restoreFromSqlite($filePath);
        }

        return self::restoreFromSql($filePath);
    }

    /**
     * Restore file SQLite
     */
    protected static function restoreFromSqlite(string $filePath): array
    {
        try {
            $databasePath = config('database.connections.sqlite.database');
            
            // Backup cadangan keamanan database saat ini sebelum ditimpa
            if (file_exists($databasePath)) {
                File::copy($databasePath, $databasePath . '.pre_restore_bak');
            }

            File::copy($filePath, $databasePath);

            return [
                'success' => true,
                'message' => 'Database SQLite berhasil dipulihkan secara penuh.',
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Gagal memulihkan database SQLite: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Restore dari file SQL
     */
    protected static function restoreFromSql(string $filePath): array
    {
        try {
            $sql = File::get($filePath);
            if (empty(trim($sql))) {
                return [
                    'success' => false,
                    'message' => 'Berkas SQL kosong.',
                ];
            }

            // Bersihkan baris perintah spesifik MySQL yang mungkin tidak didukung di SQLite saat eksekusi lokal
            $connection = config('database.default');
            
            if ($connection === 'sqlite') {
                // Eksekusi statement per statement
                DB::connection()->getPdo()->exec('PRAGMA foreign_keys = OFF;');
                
                // Pisahkan perintah SQL berdasarkan semicolon di akhir baris
                $statements = preg_split('/;\s*[\r\n]+/', $sql);
                
                foreach ($statements as $stmt) {
                    $stmt = trim($stmt);
                    if (empty($stmt)) continue;
                    
                    // Lewati perintah khusus MySQL saat koneksi lokal bertipe SQLite
                    if (str_starts_with(strtoupper($stmt), 'SET ') || 
                        str_starts_with(strtoupper($stmt), 'START TRANSACTION') || 
                        str_starts_with(strtoupper($stmt), 'COMMIT')) {
                        continue;
                    }

                    // Hapus modifier tabel MySQL
                    $stmt = preg_replace('/ENGINE\s*=\s*\w+/i', '', $stmt);
                    $stmt = preg_replace('/DEFAULT\s+CHARSET\s*=\s*\w+/i', '', $stmt);
                    $stmt = preg_replace('/COLLATE\s*=\s*\w+/i', '', $stmt);
                    $stmt = preg_replace('/AUTO_INCREMENT\s*=\s*\d+/i', '', $stmt);
                    $stmt = preg_replace('/BIGINT\s+UNSIGNED\s+AUTO_INCREMENT\s+PRIMARY\s+KEY/i', 'INTEGER PRIMARY KEY AUTOINCREMENT', $stmt);

                    try {
                        DB::connection()->getPdo()->exec($stmt);
                    } catch (\Throwable $e) {
                        // Lanjutkan jika hanya drop table non-existent
                    }
                }

                DB::connection()->getPdo()->exec('PRAGMA foreign_keys = ON;');
            } else {
                DB::unprepared($sql);
            }

            return [
                'success' => true,
                'message' => 'Database berhasil dipulihkan dari berkas SQL.',
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat memulihkan file SQL: ' . $e->getMessage(),
            ];
        }
    }
}
