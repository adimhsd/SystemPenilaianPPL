<?php

namespace App\Filament\Resources\Students\Pages;

use App\Filament\Resources\Students\StudentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;

    protected static ?string $title = 'Tambah Data Mahasiswa';

    public function getHeading(): string
    {
        return 'Tambah Data Mahasiswa';
    }

    public function getSubheading(): ?string
    {
        return 'Formulir pendaftaran dan penempatan mahasiswa ke kelompok bimbingan PPL FEB UNIKU';
    }
}
