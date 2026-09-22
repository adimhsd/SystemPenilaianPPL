<?php

namespace App\Filament\Resources\Mitras\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MitraForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_mitra')
                    ->label('Nama Instansi / Mitra PPL')
                    ->required()
                    ->maxLength(255),
                Select::make('kategori')
                    ->label('Kategori Mitra')
                    ->options([
                        'SKPD' => 'SKPD (Pemerintah/Dinas)',
                        'Swasta' => 'Swasta / Korporasi',
                        'UMKM' => 'UMKM',
                        'Desa' => 'Pemerintah Desa',
                        'BUMN/BUMD' => 'BUMN / BUMD',
                    ])
                    ->searchable(),
                Textarea::make('alamat')
                    ->label('Alamat Lengkap Instansi / Mitra')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
