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
        // Jika database masih kosong (misal saat fresh migration / test environment),
        // sinkronisasi akan dijalankan secara utuh oleh PplRealDataSeeder.
        if (User::where('role', 'dpl')->count() === 0 || Group::count() === 0) {
            return;
        }

        // 1. Update password default semua akun DPL menjadi "FEB_Tangguh"
        User::where('role', 'dpl')->update([
            'password' => Hash::make('FEB_Tangguh'),
        ]);

        // 2. Tambah Mitra Baru: Virginia Mahakarya Property
        $mitra79 = Mitra::firstOrCreate(
            ['nama_mitra' => 'Virginia Mahakarya Property'],
            [
                'kategori' => 'Swasta',
                'alamat' => 'Karangmangu Kabupaten Kuningan',
            ]
        );

        // 3. Tambah Kelompok 79 (DPL: Dr. Iqbal Arraniri)
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

        // 4. Tambah Mahasiswa Baru ke Kelompok 79
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

        // 5. Mutasi Perpindahan Kelompok Mahasiswa
        $group16 = Group::whereIn('group_name', ['KELOMPOK 16', 'Kelompok 16'])->first();
        $group22 = Group::whereIn('group_name', ['KELOMPOK 22', 'Kelompok 22'])->first();
        $group69 = Group::whereIn('group_name', ['KELOMPOK 69', 'Kelompok 69'])->first();
        $group76 = Group::whereIn('group_name', ['KELOMPOK 76', 'Kelompok 76'])->first();
        $group77 = Group::whereIn('group_name', ['KELOMPOK 77', 'Kelompok 77'])->first();

        // 5a. Fahmi Fiahas Sunah: Pindah ke Kelompok 22
        if ($group22) {
            Student::where('nim', '20230510423')->update(['group_id' => $group22->id]);
        }

        // 5b. Dina Aulia Jannah Lubis: Pindah ke Kelompok 77
        if ($group77) {
            Student::where('nim', '20230510098')->update(['group_id' => $group77->id]);
        }

        // 5c. Muhammad Rizqi Fauzan: Pindah ke Kelompok 76
        if ($group76) {
            Student::where('nim', '20230510396')->update(['group_id' => $group76->id]);
        }

        // 5d. Syifa Awalia Putri & Syifa Nabilah: Pindah ke Kelompok 16
        if ($group16) {
            Student::whereIn('nim', ['20230510219', '20230510284'])->update(['group_id' => $group16->id]);
        }

        // 5e. Fika Khoirunnisa Kurnia Illahi & Nina Khoirunnisa: Pindah ke Kelompok 69
        if ($group69) {
            Student::whereIn('nim', ['20230610080', '20230610087'])->update(['group_id' => $group69->id]);
        }

        // 6. Penyesuaian Penugasan DPL Kelompok
        $dplRina = User::where('name', 'like', '%Rina Masruroh%')->first();
        $dplFaishal = User::where('name', 'like', '%Faishal Rahimi%')->first();
        $dplNeni = User::where('name', 'like', '%Neni Nurhayati%')->first();

        // Kelompok 08 -> Dr. Rina Masruroh
        if ($dplRina) {
            Group::whereIn('group_name', ['KELOMPOK 08', 'KELOMPOK 8', 'Kelompok 08', 'Kelompok 8'])->update(['dpl_id' => $dplRina->id]);
        }

        // Kelompok 13 -> Faishal Rahimi
        if ($dplFaishal) {
            Group::whereIn('group_name', ['KELOMPOK 13', 'Kelompok 13'])->update(['dpl_id' => $dplFaishal->id]);
        }

        // Kelompok 70 -> Dr. Neni Nurhayati
        if ($dplNeni) {
            Group::whereIn('group_name', ['KELOMPOK 70', 'Kelompok 70'])->update(['dpl_id' => $dplNeni->id]);
        }

        // 7. Hapus mahasiswa yang tidak ada di Excel plotting (Muhammad Zulfikri Mudzakir)
        Student::where('nim', '20230510246')->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan password DPL ke default awal jika rollback
        User::where('role', 'dpl')->update([
            'password' => Hash::make('password'),
        ]);

        // Hapus mahasiswa baru
        Student::whereIn('nim', ['20230510122', '20230510378'])->delete();

        // Hapus Kelompok 79
        Group::where('group_name', 'KELOMPOK 79')->delete();

        // Hapus Mitra Virginia Mahakarya Property
        Mitra::where('nama_mitra', 'Virginia Mahakarya Property')->delete();
    }
};
