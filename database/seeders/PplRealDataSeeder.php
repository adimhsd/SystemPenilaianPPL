<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\Mitra;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PplRealDataSeeder extends Seeder
{
    public function run(): void
    {
        $sqlPath = base_path('file_backup_[22-08-2026].sql');

        if (! file_exists($sqlPath)) {
            $this->command->error("File backup SQL tidak ditemukan di: {$sqlPath}");
            return;
        }

        $this->command->info('Membaca dan memproses file backup SQL FEB UNIKU...');

        $lines = file($sqlPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $users = [];
        $mitras = [];
        $kelompoks = [];
        $anggotas = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (! str_starts_with($line, 'INSERT INTO `')) {
                continue;
            }

            // 1. Parse Users
            if (str_starts_with($line, "INSERT INTO `users`")) {
                // INSERT INTO `users` (`id`, `username`, `password`, `role`, `nama_lengkap`, `no_hp`, `email`, `nip_nidn`, ...)
                if (preg_match("/VALUES \('(\d+)', '([^']*)', '([^']*)', '([^']*)', '([^']*)'(?:, (?:'([^']*)'|NULL))?(?:, (?:'([^']*)'|NULL))?(?:, (?:'([^']*)'|NULL))?/i", $line, $matches)) {
                    $id = (int) $matches[1];
                    $username = $matches[2];
                    $role = $matches[4];
                    $nama = $matches[5];
                    $noHp = ! empty($matches[6]) ? $matches[6] : null;
                    $email = ! empty($matches[7]) ? $matches[7] : null;
                    $nipNidn = ! empty($matches[8]) ? $matches[8] : null;

                    if ($role === 'admin' || $role === 'dpl') {
                        $users[$id] = [
                            'id' => $id,
                            'username' => $username,
                            'name' => $nama,
                            'role' => $role,
                            'no_hp' => $noHp,
                            'email' => $email ?: ($role === 'admin' ? 'admin@febuniku.ac.id' : strtolower($username) . '@uniku.ac.id'),
                            'nip_nidn' => $nipNidn,
                            'password' => Hash::make('password'), // Password standar: 'password'
                        ];
                    }
                }
            }

            // 2. Parse Mitra
            elseif (str_starts_with($line, "INSERT INTO `mitra`")) {
                // INSERT INTO `mitra` (`id`, `nama_mitra`, `kategori`, `alamat`, ...)
                if (preg_match("/VALUES \('(\d+)', '([^']*)', '([^']*)', '([^']*)'/i", $line, $matches)) {
                    $id = (int) $matches[1];
                    $mitras[$id] = [
                        'id' => $id,
                        'nama_mitra' => $matches[2],
                        'kategori' => $matches[3],
                        'alamat' => $matches[4],
                    ];
                }
            }

            // 3. Parse Kelompok PPL
            elseif (str_starts_with($line, "INSERT INTO `kelompok_ppl`")) {
                // INSERT INTO `kelompok_ppl` (`id`, `nama_kelompok`, `mitra_id`, `dpl_id`, `ketua_user_id`, `tahun_akademik`, ...)
                if (preg_match("/VALUES \('(\d+)', '([^']*)', '(\d+)', '(\d+)'(?:, '\d+')?(?:, '([^']*)')?/i", $line, $matches)) {
                    $id = (int) $matches[1];
                    $kelompoks[$id] = [
                        'id' => $id,
                        'group_name' => $matches[2],
                        'mitra_id' => (int) $matches[3],
                        'dpl_id' => (int) $matches[4],
                        'academic_year' => ! empty($matches[5]) ? $matches[5] : '2026/2027',
                    ];
                }
            }

            // 4. Parse Anggota Kelompok (Mahasiswa)
            elseif (str_starts_with($line, "INSERT INTO `anggota_kelompok`")) {
                // INSERT INTO `anggota_kelompok` (`id`, `kelompok_id`, `nim`, `nama`, `jenis_kelamin`, `prodi`, `konsentrasi`, `no_hp`, `alamat`, ...)
                if (preg_match("/VALUES \('(\d+)', '(\d+)', '([^']*)', '((?:\\\\'|[^'])*)', '([^']*)', '([^']*)'(?:, (?:'((?:\\\\'|[^'])*)'|NULL))?(?:, (?:'((?:\\\\'|[^'])*)'|NULL))?(?:, (?:'((?:\\\\'|[^'])*)'|NULL))?/i", $line, $matches)) {
                    $id = (int) $matches[1];
                    $anggotas[$id] = [
                        'id' => $id,
                        'group_id' => (int) $matches[2],
                        'nim' => $matches[3],
                        'name' => stripslashes($matches[4]),
                        'jenis_kelamin' => $matches[5],
                        'prodi' => $matches[6],
                        'konsentrasi' => ! empty($matches[7]) ? stripslashes($matches[7]) : null,
                        'no_hp' => ! empty($matches[8]) ? $matches[8] : null,
                        'alamat' => ! empty($matches[9]) ? stripslashes($matches[9]) : null,
                        'status' => 'draft',
                    ];
                }
            }
        }

        DB::beginTransaction();
        try {
            // A. Seed Users (Admin & 41 DPL)
            $this->command->info("Menyimpan " . count($users) . " akun Pengguna (1 Admin & 41 DPL)...");
            foreach ($users as $userData) {
                User::updateOrCreate(['id' => $userData['id']], $userData);
            }

            // B. Seed Mitras (81 Mitra)
            $this->command->info("Menyimpan " . count($mitras) . " data Mitra Instansi...");
            foreach ($mitras as $mitraData) {
                Mitra::updateOrCreate(['id' => $mitraData['id']], $mitraData);
            }

            // C. Seed Groups (78 Kelompok)
            $this->command->info("Menyimpan " . count($kelompoks) . " Kelompok PPL...");
            foreach ($kelompoks as $groupData) {
                $mitraNama = $mitras[$groupData['mitra_id']]['nama_mitra'] ?? null;
                $groupData['location'] = $mitraNama;
                Group::updateOrCreate(['id' => $groupData['id']], $groupData);
            }

            // D. Seed Students (393 Mahasiswa)
            $this->command->info("Menyimpan " . count($anggotas) . " data Mahasiswa PPL...");
            foreach ($anggotas as $studentData) {
                Student::updateOrCreate(['nim' => $studentData['nim']], $studentData);
            }

            DB::commit();
            $this->command->info('✅ Seluruh data riil PPL FEB UNIKU berhasil diimpor ke database!');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->command->error('Gagal mengimpor data: ' . $e->getMessage());
            throw $e;
        }
    }
}
