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
                            'password' => Hash::make($role === 'dpl' ? 'FEB_Tangguh' : 'password'), // Password DPL: FEB_Tangguh, Admin: password
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

            // E. Sinkronisasi Data Ploting FIX (Excel)
            $this->command->info("Menyelaraskan data ploting dengan PLOTING PPL FIX.xlsx...");
            $mitra79 = Mitra::firstOrCreate(
                ['nama_mitra' => 'Virginia Mahakarya Property'],
                [
                    'kategori' => 'Swasta',
                    'alamat' => 'Karangmangu Kabupaten Kuningan',
                ]
            );

            $dplIqbal = User::where('name', 'like', '%Iqbal Arraniri%')->first();
            $group79 = Group::firstOrCreate(
                ['group_name' => 'KELOMPOK 79'],
                [
                    'mitra_id' => $mitra79->id,
                    'dpl_id' => $dplIqbal?->id ?? 11,
                    'location' => 'Virginia Mahakarya Property',
                    'academic_year' => '2026/2027',
                ]
            );

            Student::firstOrCreate(
                ['nim' => '20230510122'],
                [
                    'group_id' => $group79->id,
                    'name' => 'Helmy Alpian D',
                    'jenis_kelamin' => 'Laki-laki',
                    'prodi' => 'Manajemen',
                    'konsentrasi' => 'Pemasaran',
                    'status' => 'draft',
                ]
            );

            Student::firstOrCreate(
                ['nim' => '20230510378'],
                [
                    'group_id' => $group79->id,
                    'name' => 'Muhammad Raji A',
                    'jenis_kelamin' => 'Laki-laki',
                    'prodi' => 'Manajemen',
                    'konsentrasi' => 'Pemasaran',
                    'status' => 'draft',
                ]
            );

            // Mutasi kelompok
            $g16 = Group::whereIn('group_name', ['KELOMPOK 16', 'Kelompok 16'])->first();
            $g22 = Group::whereIn('group_name', ['KELOMPOK 22', 'Kelompok 22'])->first();
            $g69 = Group::whereIn('group_name', ['KELOMPOK 69', 'Kelompok 69'])->first();
            $g76 = Group::whereIn('group_name', ['KELOMPOK 76', 'Kelompok 76'])->first();
            $g77 = Group::whereIn('group_name', ['KELOMPOK 77', 'Kelompok 77'])->first();

            if ($g22) Student::where('nim', '20230510423')->update(['group_id' => $g22->id]);
            if ($g77) Student::where('nim', '20230510098')->update(['group_id' => $g77->id]);
            if ($g76) Student::where('nim', '20230510396')->update(['group_id' => $g76->id]);
            if ($g16) Student::whereIn('nim', ['20230510219', '20230510284'])->update(['group_id' => $g16->id]);
            if ($g69) Student::whereIn('nim', ['20230610080', '20230610087'])->update(['group_id' => $g69->id]);

            // DPL switch
            $dplRina = User::where('name', 'like', '%Rina Masruroh%')->first();
            $dplFaishal = User::where('name', 'like', '%Faishal Rahimi%')->first();
            $dplNeni = User::where('name', 'like', '%Neni Nurhayati%')->first();

            if ($dplRina) Group::whereIn('group_name', ['KELOMPOK 08', 'KELOMPOK 8', 'Kelompok 08', 'Kelompok 8'])->update(['dpl_id' => $dplRina->id]);
            if ($dplFaishal) Group::whereIn('group_name', ['KELOMPOK 13', 'Kelompok 13'])->update(['dpl_id' => $dplFaishal->id]);
            if ($dplNeni) Group::whereIn('group_name', ['KELOMPOK 70', 'Kelompok 70'])->update(['dpl_id' => $dplNeni->id]);

            // Hapus mahasiswa yang tidak ada di Excel
            Student::where('nim', '20230510246')->delete();

            DB::commit();
            $this->command->info('✅ Seluruh data riil PPL FEB UNIKU berhasil diimpor ke database!');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->command->error('Gagal mengimpor data: ' . $e->getMessage());
            throw $e;
        }
    }
}
