<?php

namespace App\Filament\Resources\Students\Schemas;

use App\Models\Student;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StudentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Biodata Mahasiswa PPL')
                    ->description('Masukkan data lengkap mahasiswa dan penempatan kelompok bimbingan PPL.')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'lg' => 3,
                        ])->schema([
                            TextInput::make('nim')
                                ->label('NIM Mahasiswa')
                                ->placeholder('Misal: 20210210001')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->disabled(fn (string $operation) => $operation !== 'create' && ! (auth()->user()?->isAdmin() ?? false)),

                            TextInput::make('name')
                                ->label('Nama Lengkap')
                                ->placeholder('Nama lengkap sesuai SIAKAD')
                                ->required()
                                ->disabled(fn (string $operation) => $operation !== 'create' && ! (auth()->user()?->isAdmin() ?? false)),

                            Select::make('group_id')
                                ->label('Kelompok PPL')
                                ->relationship('group', 'group_name', function ($query) {
                                    if (auth()->user()?->isDpl()) {
                                        $query->where('dpl_id', auth()->id());
                                    }
                                })
                                ->searchable()
                                ->preload()
                                ->required()
                                ->disabled(fn (string $operation) => $operation !== 'create' && ! (auth()->user()?->isAdmin() ?? false)),

                            Select::make('prodi')
                                ->label('Program Studi')
                                ->options([
                                    'Manajemen' => 'S1 Manajemen',
                                    'Akuntansi' => 'S1 Akuntansi',
                                    'Bisnis Digital' => 'S1 Bisnis Digital',
                                ])
                                ->required()
                                ->disabled(fn (string $operation) => $operation !== 'create' && ! (auth()->user()?->isAdmin() ?? false)),

                            Select::make('konsentrasi')
                                ->label('Konsentrasi')
                                ->options(function () {
                                    $fromDb = Student::distinct()->whereNotNull('konsentrasi')->where('konsentrasi', '!=', '')->pluck('konsentrasi', 'konsentrasi')->toArray();
                                    $defaults = [
                                        'Pemasaran' => 'Pemasaran',
                                        'Keuangan' => 'Keuangan',
                                        'SDM' => 'SDM (Sumber Daya Manusia)',
                                        'Operasional' => 'Operasional',
                                        'Akuntansi' => 'Akuntansi',
                                        'Bisnis Digital' => 'Bisnis Digital',
                                    ];
                                    return array_merge($defaults, $fromDb);
                                })
                                ->searchable()
                                ->preload()
                                ->placeholder('Pilih Konsentrasi Studi')
                                ->disabled(fn (string $operation) => $operation !== 'create' && ! (auth()->user()?->isAdmin() ?? false)),

                            Select::make('jenis_kelamin')
                                ->label('Jenis Kelamin')
                                ->options([
                                    'Laki-laki' => 'Laki-laki',
                                    'Perempuan' => 'Perempuan',
                                ])
                                ->disabled(fn (string $operation) => $operation !== 'create' && ! (auth()->user()?->isAdmin() ?? false)),
                        ]),
                    ]),
            ]);
    }
}
