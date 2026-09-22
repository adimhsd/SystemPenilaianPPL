<?php

namespace App\Filament\Resources\Students\Schemas;

use App\Models\Student;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StudentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)->schema([
                    TextInput::make('nim')
                        ->label('NIM Mahasiswa')
                        ->required()
                        ->disabled(fn () => ! (auth()->user()?->isAdmin() ?? false)),
                    TextInput::make('name')
                        ->label('Nama Lengkap')
                        ->required()
                        ->disabled(fn () => ! (auth()->user()?->isAdmin() ?? false)),
                    Select::make('group_id')
                        ->label('Kelompok PPL')
                        ->relationship('group', 'group_name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->disabled(fn () => ! (auth()->user()?->isAdmin() ?? false)),
                    Select::make('prodi')
                        ->label('Program Studi')
                        ->options([
                            'Manajemen' => 'S1 Manajemen',
                            'Akuntansi' => 'S1 Akuntansi',
                            'Bisnis Digital' => 'S1 Bisnis Digital',
                        ])
                        ->disabled(fn () => ! (auth()->user()?->isAdmin() ?? false)),
                    Select::make('jenis_kelamin')
                        ->label('Jenis Kelamin')
                        ->options([
                            'Laki-laki' => 'Laki-laki',
                            'Perempuan' => 'Perempuan',
                        ])
                        ->disabled(fn () => ! (auth()->user()?->isAdmin() ?? false)),
                    TextInput::make('konsentrasi')
                        ->label('Konsentrasi')
                        ->disabled(fn () => ! (auth()->user()?->isAdmin() ?? false)),
                ]),

                Fieldset::make('Penilaian PPL (Bobot 60:40)')
                    ->schema([
                        TextInput::make('mitra_score')
                            ->label('Nilai Mitra / Instansi (Bobot 60%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01)
                            ->live(onBlur: true)
                            ->disabled(fn (?Student $record) => $record?->status === 'locked' && ! (auth()->user()?->isAdmin() ?? false))
                            ->helperText('Nilai dari pihak mitra/lapangan (skala 0 - 100)')
                            ->afterStateUpdated(function ($state, callable $set, callable $get): void {
                                $mitra = is_numeric($state) ? (float) $state : null;
                                $dpl = is_numeric($get('dpl_score')) ? (float) $get('dpl_score') : null;
                                if ($mitra !== null || $dpl !== null) {
                                    $final = Student::calculateFinalScore($mitra, $dpl);
                                    $set('final_score', $final);
                                    $set('letter_grade', Student::calculateLetterGrade($final));
                                }
                            }),

                        TextInput::make('dpl_score')
                            ->label('Nilai Laporan DPL (Bobot 40%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01)
                            ->live(onBlur: true)
                            ->disabled(fn (?Student $record) => $record?->status === 'locked' && ! (auth()->user()?->isAdmin() ?? false))
                            ->helperText('Nilai evaluasi laporan dari DPL (skala 0 - 100)')
                            ->afterStateUpdated(function ($state, callable $set, callable $get): void {
                                $dpl = is_numeric($state) ? (float) $state : null;
                                $mitra = is_numeric($get('mitra_score')) ? (float) $get('mitra_score') : null;
                                if ($mitra !== null || $dpl !== null) {
                                    $final = Student::calculateFinalScore($mitra, $dpl);
                                    $set('final_score', $final);
                                    $set('letter_grade', Student::calculateLetterGrade($final));
                                }
                            }),

                        TextInput::make('final_score')
                            ->label('Nilai Akhir (Otomatis)')
                            ->numeric()
                            ->readOnly()
                            ->helperText('Formula: (Mitra x 0.6) + (Laporan x 0.4)'),

                        TextInput::make('letter_grade')
                            ->label('Nilai Huruf (Standar FEB)')
                            ->readOnly()
                            ->helperText('A: 81-100 | AB: 75-80.9 | B: 69-74.9 | BC: 63-68.9 | C: 57-62.9 | CD: 51-56.9 | D: 45-50.9 | E: <45'),

                        Select::make('status')
                            ->label('Status Penilaian')
                            ->options([
                                'draft' => 'Draft (Dapat Diubah)',
                                'locked' => 'Kunci Nilai (Final / Terkunci)',
                            ])
                            ->default('draft')
                            ->required()
                            ->helperText('Jika dikunci (Locked), nilai mahasiswa tidak bisa diubah oleh DPL kecuali dibuka oleh Admin.'),
                    ])
                    ->columns(2),
            ]);
    }
}
