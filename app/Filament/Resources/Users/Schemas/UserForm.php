<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Lengkap (beserta Gelar)')
                    ->required()
                    ->maxLength(255),
                TextInput::make('username')
                    ->label('Username Login')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Select::make('role')
                    ->label('Peran / Hak Akses')
                    ->options([
                        'admin' => 'Admin Fakultas / Prodi',
                        'dpl' => 'Dosen Pembimbing Lapangan (DPL)',
                    ])
                    ->required()
                    ->default('dpl'),
                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->helperText('Biarkan kosong jika tidak ingin mengubah password.'),
            ]);
    }
}
