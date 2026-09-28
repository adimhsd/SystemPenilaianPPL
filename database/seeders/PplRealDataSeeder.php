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

            // F. Sinkronisasi Data Mahasiswa MBKM (Rekognisi PPL)
            $this->command->info('Menyimpan data MBKM Rekognisi PPL (76 Mahasiswa, 13 Kelompok, 2 DPL Baru)...');
            $mitraMbkm = Mitra::firstOrCreate(
                ['nama_mitra' => 'MBKM'],
                [
                    'kategori' => 'MBKM',
                    'alamat' => 'Program MBKM Rekognisi PPL FEB UNIKU',
                ]
            );

            $dplNuke = User::firstOrCreate(
                ['username' => 'DPL_PPL42'],
                [
                    'name' => 'Siti Nuke Nurfatimah, M.Sc',
                    'email' => 'dpl_ppl42@uniku.ac.id',
                    'password' => Hash::make('FEB_Tangguh'),
                    'role' => 'dpl',
                ]
            );

            $dplTeti = User::firstOrCreate(
                ['username' => 'DPL_PPL43'],
                [
                    'name' => 'Teti Rahmawati, M.Si., Ak., CA',
                    'email' => 'dpl_ppl43@uniku.ac.id',
                    'password' => Hash::make('FEB_Tangguh'),
                    'role' => 'dpl',
                ]
            );

            $mbkmDplMap = [
                'KELOMPOK MBKM - Winda Oktaviani' => ['search' => 'Winda Oktaviani', 'fallback_id' => 41],
                'KELOMPOK MBKM - Dr. Munir Nur Komarudin' => ['search' => 'Munir Nur Komarudin', 'fallback_id' => 9],
                'KELOMPOK MBKM - Nurul Siti Jahidah' => ['search' => 'Nurul Siti Jahidah', 'fallback_id' => 37],
                'KELOMPOK MBKM - Wely Hadi Gunawan' => ['search' => 'Wely Hadi Gunawan', 'fallback_id' => 19],
                'KELOMPOK MBKM - Amir Hamzah' => ['search' => 'Amir Hamzah', 'fallback_id' => 40],
                'KELOMPOK MBKM - Dendi Purnama' => ['search' => 'Dendi Purnama', 'fallback_id' => 42],
                'KELOMPOK MBKM - Enung Nurhayati' => ['search' => 'Enung Nurhayati', 'fallback_id' => 28],
                'KELOMPOK MBKM - Siti Nuke Nurfatimah' => ['search' => 'Siti Nuke Nurfatimah', 'user' => $dplNuke],
                'KELOMPOK MBKM - Syahrul Syarifudin' => ['search' => 'Syahrul Syarifudin', 'fallback_id' => 32],
                'KELOMPOK MBKM - Teti Rahmawati' => ['search' => 'Teti Rahmawati', 'user' => $dplTeti],
                'KELOMPOK MBKM - Yudi Febriansyah' => ['search' => 'Yudi Febriansyah', 'fallback_id' => 3],
                'KELOMPOK MBKM - Wachjuni' => ['search' => 'Wachjuni', 'fallback_id' => 6],
                'KELOMPOK MBKM - Oktaviani Rita Puspasari' => ['search' => 'Oktaviani Rita Puspasari', 'fallback_id' => 30],
            ];

            $mbkmGroups = [];
            foreach ($mbkmDplMap as $groupName => $info) {
                $dplId = null;
                if (isset($info['user'])) {
                    $dplId = $info['user']->id;
                } else {
                    $u = User::where('name', 'like', '%' . $info['search'] . '%')->first();
                    $dplId = $u ? $u->id : $info['fallback_id'];
                }

                $mbkmGroups[$groupName] = Group::firstOrCreate(
                    ['group_name' => $groupName],
                    [
                        'mitra_id' => $mitraMbkm->id,
                        'dpl_id' => $dplId,
                        'location' => 'Program MBKM Rekognisi PPL',
                        'academic_year' => '2026/2027',
                    ]
                );
            }

            $mbkmStudentsData = array (
  0 => 
  array (
    'nim' => '20230510177',
    'name' => 'Audria Hayatun Nufus',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Manajemen',
    'group_name' => 'KELOMPOK MBKM - Winda Oktaviani',
  ),
  1 => 
  array (
    'nim' => '20230510080',
    'name' => 'Dani Aditria',
    'jenis_kelamin' => 'Laki-laki',
    'prodi' => 'Manajemen',
    'group_name' => 'KELOMPOK MBKM - Winda Oktaviani',
  ),
  2 => 
  array (
    'nim' => '20230510069',
    'name' => 'Dini Putri Dinanti',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Manajemen',
    'group_name' => 'KELOMPOK MBKM - Winda Oktaviani',
  ),
  3 => 
  array (
    'nim' => '20230510055',
    'name' => 'Jelita Dwi Maharani',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Manajemen',
    'group_name' => 'KELOMPOK MBKM - Winda Oktaviani',
  ),
  4 => 
  array (
    'nim' => '20230510416',
    'name' => 'Lusi Dwi Paramita',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Manajemen',
    'group_name' => 'KELOMPOK MBKM - Winda Oktaviani',
  ),
  5 => 
  array (
    'nim' => '20230510179',
    'name' => 'Regyna Oktasari',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Manajemen',
    'group_name' => 'KELOMPOK MBKM - Winda Oktaviani',
  ),
  6 => 
  array (
    'nim' => '20230510128',
    'name' => 'Siti Amellia Solekha',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Manajemen',
    'group_name' => 'KELOMPOK MBKM - Winda Oktaviani',
  ),
  7 => 
  array (
    'nim' => '20230510294',
    'name' => 'Andini Rossayani',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Manajemen',
    'group_name' => 'KELOMPOK MBKM - Winda Oktaviani',
  ),
  8 => 
  array (
    'nim' => '20230510062',
    'name' => 'Viny Revalia Putri',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Manajemen',
    'group_name' => 'KELOMPOK MBKM - Dr. Munir Nur Komarudin',
  ),
  9 => 
  array (
    'nim' => '20230510304',
    'name' => 'Anas Tasyah Nasution',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Manajemen',
    'group_name' => 'KELOMPOK MBKM - Dr. Munir Nur Komarudin',
  ),
  10 => 
  array (
    'nim' => '20230510324',
    'name' => 'Ziad Nur Farhan',
    'jenis_kelamin' => 'Laki-laki',
    'prodi' => 'Manajemen',
    'group_name' => 'KELOMPOK MBKM - Dr. Munir Nur Komarudin',
  ),
  11 => 
  array (
    'nim' => '20230510025',
    'name' => 'Ricky Ardiansyah',
    'jenis_kelamin' => 'Laki-laki',
    'prodi' => 'Manajemen',
    'group_name' => 'KELOMPOK MBKM - Nurul Siti Jahidah',
  ),
  12 => 
  array (
    'nim' => '20230510427',
    'name' => 'Muhammad Firas Hernando',
    'jenis_kelamin' => 'Laki-laki',
    'prodi' => 'Manajemen',
    'group_name' => 'KELOMPOK MBKM - Nurul Siti Jahidah',
  ),
  13 => 
  array (
    'nim' => '20230510287',
    'name' => 'Muhammad Zidan Alghifari',
    'jenis_kelamin' => 'Laki-laki',
    'prodi' => 'Manajemen',
    'group_name' => 'KELOMPOK MBKM - Wely Hadi Gunawan',
  ),
  14 => 
  array (
    'nim' => '20230510173',
    'name' => 'Virgiawan Listanto',
    'jenis_kelamin' => 'Laki-laki',
    'prodi' => 'Manajemen',
    'group_name' => 'KELOMPOK MBKM - Wely Hadi Gunawan',
  ),
  15 => 
  array (
    'nim' => '20230610104',
    'name' => 'Ayu Nurhawa',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Amir Hamzah',
  ),
  16 => 
  array (
    'nim' => '20230610050',
    'name' => 'Rinta Pebrianti',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Amir Hamzah',
  ),
  17 => 
  array (
    'nim' => '20230610028',
    'name' => 'Rita Afria Ningsih',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Amir Hamzah',
  ),
  18 => 
  array (
    'nim' => '20220610116',
    'name' => 'Salma Putri Salsabila',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Amir Hamzah',
  ),
  19 => 
  array (
    'nim' => '20230610012',
    'name' => 'Selvi Aulia',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Teti Rahmawati',
  ),
  20 => 
  array (
    'nim' => '20230610042',
    'name' => 'Sepira Dwiyanti',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Amir Hamzah',
  ),
  21 => 
  array (
    'nim' => '20230610088',
    'name' => 'Yulita Yuna Nadiana',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Amir Hamzah',
  ),
  22 => 
  array (
    'nim' => '20230610027',
    'name' => 'Zahra Putri Amelia',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Amir Hamzah',
  ),
  23 => 
  array (
    'nim' => '20230610103',
    'name' => 'Ainul Rahmawati',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Dendi Purnama',
  ),
  24 => 
  array (
    'nim' => '20230620086',
    'name' => 'Amelya Sari Salsabila',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Dendi Purnama',
  ),
  25 => 
  array (
    'nim' => '20230610085',
    'name' => 'Frika Dewi Listiani',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Dendi Purnama',
  ),
  26 => 
  array (
    'nim' => '20230610100',
    'name' => 'Intan Dwi Anggriani',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Dendi Purnama',
  ),
  27 => 
  array (
    'nim' => '20230610135',
    'name' => 'Intan Wardah Faridah',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Dendi Purnama',
  ),
  28 => 
  array (
    'nim' => '20230610053',
    'name' => 'Maedina',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Dendi Purnama',
  ),
  29 => 
  array (
    'nim' => '20230610075',
    'name' => 'Maitsa Nurjihan Hakim',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Dendi Purnama',
  ),
  30 => 
  array (
    'nim' => '20230610076',
    'name' => 'Naila Nursalma Hakim',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Dendi Purnama',
  ),
  31 => 
  array (
    'nim' => '20230610120',
    'name' => 'Nasywa Aulia Hanif',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Dendi Purnama',
  ),
  32 => 
  array (
    'nim' => '20230610115',
    'name' => 'Gemintang Septia',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Dendi Purnama',
  ),
  33 => 
  array (
    'nim' => '20230610072',
    'name' => 'Syifa Maulida',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Dendi Purnama',
  ),
  34 => 
  array (
    'nim' => '20230610004',
    'name' => 'Dinda Puspitasari',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Enung Nurhayati',
  ),
  35 => 
  array (
    'nim' => '20230610082',
    'name' => 'Adila Sekar Ramadhani',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Enung Nurhayati',
  ),
  36 => 
  array (
    'nim' => '20230610107',
    'name' => 'Adinda Nurul Hawa Abubakar',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Siti Nuke Nurfatimah',
  ),
  37 => 
  array (
    'nim' => '20230610134',
    'name' => 'Ananda Zakiyah Amalia',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Siti Nuke Nurfatimah',
  ),
  38 => 
  array (
    'nim' => '20230610056',
    'name' => 'Anes Fadila',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Siti Nuke Nurfatimah',
  ),
  39 => 
  array (
    'nim' => '20230610061',
    'name' => 'Azifa Ghiffari',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Siti Nuke Nurfatimah',
  ),
  40 => 
  array (
    'nim' => '20230610035',
    'name' => 'Biru Dean Samugraa',
    'jenis_kelamin' => 'Laki-laki',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Siti Nuke Nurfatimah',
  ),
  41 => 
  array (
    'nim' => '20230610091',
    'name' => 'Dhea Ainun Siti Khodizah',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Siti Nuke Nurfatimah',
  ),
  42 => 
  array (
    'nim' => '20230610124',
    'name' => 'Fauziah Risalatull Fibriyani',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Siti Nuke Nurfatimah',
  ),
  43 => 
  array (
    'nim' => '20230610049',
    'name' => 'Gisa Alsakinah',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Siti Nuke Nurfatimah',
  ),
  44 => 
  array (
    'nim' => '20230610114',
    'name' => 'Hana Dhiya Nisrina',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Siti Nuke Nurfatimah',
  ),
  45 => 
  array (
    'nim' => '20230610001',
    'name' => 'Keisha Virgi Annastasya',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Siti Nuke Nurfatimah',
  ),
  46 => 
  array (
    'nim' => '20230610073',
    'name' => 'Riska Dwi Yulianti',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Siti Nuke Nurfatimah',
  ),
  47 => 
  array (
    'nim' => '20230610101',
    'name' => 'Vaskal Maulana',
    'jenis_kelamin' => 'Laki-laki',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Siti Nuke Nurfatimah',
  ),
  48 => 
  array (
    'nim' => '20230610112',
    'name' => 'Nanda Adella Fauziah',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Syahrul Syarifudin',
  ),
  49 => 
  array (
    'nim' => '20230610043',
    'name' => 'Nanda Anugrah Bahara Fauzan',
    'jenis_kelamin' => 'Laki-laki',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Syahrul Syarifudin',
  ),
  50 => 
  array (
    'nim' => '20230610098',
    'name' => 'Ulfa Habibah',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Syahrul Syarifudin',
  ),
  51 => 
  array (
    'nim' => '20230610117',
    'name' => 'Siti Rohmah',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Teti Rahmawati',
  ),
  52 => 
  array (
    'nim' => '20230610064',
    'name' => 'Een Rohaeni',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Teti Rahmawati',
  ),
  53 => 
  array (
    'nim' => '20230610003',
    'name' => 'Indah Meilani',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Amir Hamzah',
  ),
  54 => 
  array (
    'nim' => '20230610125',
    'name' => 'Revita Mutiara Diandra',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Teti Rahmawati',
  ),
  55 => 
  array (
    'nim' => '20230610060',
    'name' => 'Riandi Kusumah',
    'jenis_kelamin' => 'Laki-laki',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Teti Rahmawati',
  ),
  56 => 
  array (
    'nim' => '20230610018',
    'name' => 'Risan Purnama',
    'jenis_kelamin' => 'Laki-laki',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Teti Rahmawati',
  ),
  57 => 
  array (
    'nim' => '20230610113',
    'name' => 'Yayah Laelatul Qhodariah',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Teti Rahmawati',
  ),
  58 => 
  array (
    'nim' => '20230610067',
    'name' => 'Yoga Dwi Saputra',
    'jenis_kelamin' => 'Laki-laki',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Teti Rahmawati',
  ),
  59 => 
  array (
    'nim' => '20230610059',
    'name' => 'Candra Maulana',
    'jenis_kelamin' => 'Laki-laki',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Yudi Febriansyah',
  ),
  60 => 
  array (
    'nim' => '20230610047',
    'name' => 'Fani Fauziah',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Yudi Febriansyah',
  ),
  61 => 
  array (
    'nim' => '20230610057',
    'name' => 'Fani Nur Apriliani',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Yudi Febriansyah',
  ),
  62 => 
  array (
    'nim' => '20230610013',
    'name' => 'Kamila Tri Meilinda',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Yudi Febriansyah',
  ),
  63 => 
  array (
    'nim' => '20230610020',
    'name' => 'Lindawati',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Yudi Febriansyah',
  ),
  64 => 
  array (
    'nim' => '20230610097',
    'name' => 'Muhammad Tryan Maulana',
    'jenis_kelamin' => 'Laki-laki',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Yudi Febriansyah',
  ),
  65 => 
  array (
    'nim' => '20230610066',
    'name' => 'Sera Mahliyyatus Sariroh',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Yudi Febriansyah',
  ),
  66 => 
  array (
    'nim' => '20230610070',
    'name' => 'Syafiq Hikmal Nurfikriansyah',
    'jenis_kelamin' => 'Laki-laki',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Yudi Febriansyah',
  ),
  67 => 
  array (
    'nim' => '20230610131',
    'name' => 'Zhiad Delafebrima',
    'jenis_kelamin' => 'Laki-laki',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Yudi Febriansyah',
  ),
  68 => 
  array (
    'nim' => '2023201004',
    'name' => 'Alizda Ahliha Lutfiya',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Bisnis Digital',
    'group_name' => 'KELOMPOK MBKM - Wachjuni',
  ),
  69 => 
  array (
    'nim' => '20232010008',
    'name' => 'Hanna Nur’aidah',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Bisnis Digital',
    'group_name' => 'KELOMPOK MBKM - Wachjuni',
  ),
  70 => 
  array (
    'nim' => '20232010009',
    'name' => 'Reisma Choerunnisa',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Bisnis Digital',
    'group_name' => 'KELOMPOK MBKM - Wachjuni',
  ),
  71 => 
  array (
    'nim' => '20232010013',
    'name' => 'Tyas Fitria Anastasya',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Bisnis Digital',
    'group_name' => 'KELOMPOK MBKM - Wachjuni',
  ),
  72 => 
  array (
    'nim' => '20230610039',
    'name' => 'Siti Zakhro Aulia Khumairoh',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Oktaviani Rita Puspasari',
  ),
  73 => 
  array (
    'nim' => '20230610116',
    'name' => 'Asmarani Astria Legiana',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Oktaviani Rita Puspasari',
  ),
  74 => 
  array (
    'nim' => '20230610143',
    'name' => 'Elsa Oktavia Azzahra',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Siti Nuke Nurfatimah',
  ),
  75 => 
  array (
    'nim' => '20230610063',
    'name' => 'Rida Komariah',
    'jenis_kelamin' => 'Perempuan',
    'prodi' => 'Akuntansi',
    'group_name' => 'KELOMPOK MBKM - Siti Nuke Nurfatimah',
  ),
);

            foreach ($mbkmStudentsData as $st) {
                $grp = $mbkmGroups[$st['group_name']] ?? null;
                if ($grp) {
                    Student::firstOrCreate(
                        ['nim' => $st['nim']],
                        [
                            'group_id' => $grp->id,
                            'name' => $st['name'],
                            'jenis_kelamin' => $st['jenis_kelamin'],
                            'prodi' => $st['prodi'],
                            'status' => 'draft',
                        ]
                    );
                }
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
