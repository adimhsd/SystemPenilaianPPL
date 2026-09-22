<?php

namespace App\Filament\Resources\Mitras\Tables;

use App\Models\Mitra;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MitrasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_mitra')
                    ->label('Nama Instansi / Mitra')
                    ->searchable()
                    ->weight('bold')
                    ->sortable(),
                TextColumn::make('kategori')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'SKPD' => 'info',
                        'Swasta' => 'success',
                        'UMKM' => 'warning',
                        'Desa' => 'primary',
                        default => 'gray',
                    })
                    ->sortable()
                    ->searchable(),
                TextColumn::make('alamat')
                    ->label('Alamat')
                    ->limit(60)
                    ->tooltip(fn (Mitra $record): ?string => $record->alamat)
                    ->searchable(),
                TextColumn::make('groups_count')
                    ->counts('groups')
                    ->label('Jml Kelompok')
                    ->badge()
                    ->color('info')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('kategori')
                    ->label('Kategori Mitra')
                    ->options([
                        'SKPD' => 'SKPD (Pemerintah/Dinas)',
                        'Swasta' => 'Swasta / Korporasi',
                        'UMKM' => 'UMKM',
                        'Desa' => 'Pemerintah Desa',
                        'BUMN/BUMD' => 'BUMN / BUMD',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
