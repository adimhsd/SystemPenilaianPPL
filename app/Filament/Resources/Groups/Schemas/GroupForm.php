<?php

namespace App\Filament\Resources\Groups\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class GroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('dpl_id')
                    ->label('Dosen Pembimbing Lapangan (DPL)')
                    ->relationship('dpl', 'name', fn ($query) => $query->where('role', 'dpl'))
                    ->searchable()
                    ->preload()
                    ->required(fn () => auth()->user()?->isAdmin() ?? false)
                    ->disabled(fn () => ! (auth()->user()?->isAdmin() ?? false)),
                TextInput::make('group_name')
                    ->label('Nama Kelompok')
                    ->placeholder('Misal: Kelompok 01 - Bank BJB Kuningan')
                    ->required()
                    ->disabled(fn () => ! (auth()->user()?->isAdmin() ?? false)),
                Select::make('mitra_id')
                    ->label('Instansi / Mitra PPL')
                    ->relationship('mitra', 'nama_mitra')
                    ->searchable()
                    ->preload()
                    ->required(fn () => auth()->user()?->isAdmin() ?? false)
                    ->disabled(fn () => ! (auth()->user()?->isAdmin() ?? false)),
                TextInput::make('location')
                    ->label('Catatan Lokasi Tambahan')
                    ->placeholder('Opsional jika ada catatan spesifik')
                    ->disabled(fn () => ! (auth()->user()?->isAdmin() ?? false)),
                TextInput::make('academic_year')
                    ->label('Tahun Akademik')
                    ->default('2026/2027')
                    ->required()
                    ->disabled(fn () => ! (auth()->user()?->isAdmin() ?? false)),
            ]);
    }
}
