<?php

namespace App\Filament\Widgets;

use App\Models\Group;
use App\Models\Student;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseStatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseStatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = auth()->user();

        if ($user?->isAdmin()) {
            $totalDpl = User::where('role', 'dpl')->count();
            $totalGroups = Group::count();
            $totalStudents = Student::count();

            // Hitung kelompok yang sudah 100% dinilai
            $groupsWithScore = 0;
            $groups = Group::with('students')->get();
            foreach ($groups as $group) {
                $studentCount = $group->students->count();
                if ($studentCount > 0 && $group->students->whereNotNull('final_score')->count() === $studentCount) {
                    $groupsWithScore++;
                }
            }

            $percentage = $totalGroups > 0 ? round(($groupsWithScore / $totalGroups) * 100, 1) : 0;
            $unscoredStudents = Student::whereNull('final_score')->count();

            $totalMitra = \App\Models\Mitra::count();
            $manajemen = Student::where('prodi', 'Manajemen')->count();
            $akuntansi = Student::where('prodi', 'Akuntansi')->count();
            $bisnisDigital = Student::where('prodi', 'Bisnis Digital')->count();

            return [
                Stat::make('Total DPL', $totalDpl)
                    ->description('Dosen Pembimbing Lapangan')
                    ->descriptionIcon('heroicon-m-user-group')
                    ->color('primary'),

                Stat::make('Mitra & Instansi', $totalMitra)
                    ->description('Lokasi PPL Mahasiswa')
                    ->descriptionIcon('heroicon-m-building-office-2')
                    ->color('success'),

                Stat::make('Total Kelompok PPL', $totalGroups)
                    ->description("{$percentage}% kelompok selesai dinilai")
                    ->descriptionIcon('heroicon-m-building-office')
                    ->color('info'),

                Stat::make('Total Mahasiswa PPL', $totalStudents)
                    ->description("M: {$manajemen} | A: {$akuntansi} | BD: {$bisnisDigital}")
                    ->descriptionIcon('heroicon-m-academic-cap')
                    ->color('warning'),
            ];
        }

        // Statistik khusus tampilan DPL
        $dplGroups = Group::where('dpl_id', $user?->id)->with('students')->get();
        $totalDplGroups = $dplGroups->count();

        $dplStudents = $dplGroups->flatMap->students;
        $totalDplStudents = $dplStudents->count();
        $gradedStudents = $dplStudents->whereNotNull('final_score')->count();
        $pendingStudents = $totalDplStudents - $gradedStudents;

        $progressDpl = $totalDplStudents > 0 ? round(($gradedStudents / $totalDplStudents) * 100, 1) : 0;

        return [
            Stat::make('Kelompok Bimbingan', $totalDplGroups)
                ->description('Tanggung jawab periode ini')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('primary'),

            Stat::make('Total Mahasiswa Bimbingan', $totalDplStudents)
                ->description('Mahasiswa di seluruh kelompok Anda')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('info'),

            Stat::make('Sudah Dinilai', "{$gradedStudents} / {$totalDplStudents}")
                ->description("Progres: {$progressDpl}%")
                ->descriptionIcon('heroicon-m-check-badge')
                ->color($gradedStudents === $totalDplStudents && $totalDplStudents > 0 ? 'success' : 'warning'),

            Stat::make('Belum Dinilai', $pendingStudents)
                ->description($pendingStudents === 0 ? 'Semua nilai telah diinput!' : 'Menunggu input penilaian')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingStudents === 0 ? 'success' : 'danger'),
        ];
    }
}
