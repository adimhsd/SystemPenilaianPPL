<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('export_users')
                ->label('Export Akun DPL (.xlsx)')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(route('users.export'))
                ->openUrlInNewTab(),
            CreateAction::make()->label('Tambah Pengguna Baru'),
        ];
    }
}
