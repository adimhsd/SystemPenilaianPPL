<?php

use App\Models\Group;
use App\Models\Mitra;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Guard: lewati jika database kosong (misal saat fresh migration / test environment)
        if (User::where('role', 'dpl')->count() === 0 || Group::count() === 0) {
            return;
        }

        // 1. Tambah Mitra MBKM (firstOrCreate agar tidak duplikat)
        $mitraMbkm = Mitra::firstOrCreate(
            ['nama_mitra' => 'MBKM'],
            [
                'kategori' => 'MBKM',
                'alamat' => 'Program MBKM Rekognisi PPL FEB UNIKU',
            ]
        );

        // 2. Tambah 2 Akun DPL Baru jika belum terdaftar
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

        // 3. Mapping 13 Kelompok MBKM ke DPL
        $dplMapping = [
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

        $groups = [];
        foreach ($dplMapping as $groupName => $info) {
            $dplId = null;
            if (isset($info['user'])) {
                $dplId = $info['user']->id;
            } else {
                $u = User::where('name', 'like', '%' . $info['search'] . '%')->first();
                $dplId = $u ? $u->id : $info['fallback_id'];
            }

            $groups[$groupName] = Group::firstOrCreate(
                ['group_name' => $groupName],
                [
                    'mitra_id' => $mitraMbkm->id,
                    'dpl_id' => $dplId,
                    'location' => 'Program MBKM Rekognisi PPL',
                    'academic_year' => '2026/2027',
                ]
            );
        }

        // 4. Tambah 76 Mahasiswa MBKM (firstOrCreate: TIDAK MENIMPA data mahasiswa yang sudah ada di database)
        $studentsData = array (
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
    'group_name' => 'KELOMPOK MBKM - Amir Hamzah',
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
    'group_name' => 'KELOMPOK MBKM - Teti Rahmawati',
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

        foreach ($studentsData as $st) {
            $grp = $groups[$st['group_name']] ?? null;
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $mbkmGroupIds = Group::where('group_name', 'like', 'KELOMPOK MBKM%')->pluck('id');
        Student::whereIn('group_id', $mbkmGroupIds)->delete();
        Group::whereIn('id', $mbkmGroupIds)->delete();
        User::whereIn('username', ['DPL_PPL42', 'DPL_PPL43'])->delete();
        Mitra::where('nama_mitra', 'MBKM')->delete();
    }
};