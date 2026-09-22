<?php

namespace App\Filament\Resources\Groups\Tables;

use App\Models\Group;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GroupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('group_name')
                    ->label('Nama Kelompok')
                    ->searchable()
                    ->weight('bold')
                    ->sortable(),
                TextColumn::make('mitra.nama_mitra')
                    ->label('Instansi / Mitra')
                    ->searchable()
                    ->icon('heroicon-o-building-office-2')
                    ->weight('medium')
                    ->default(fn (Group $record) => $record->location ?? '-'),
                TextColumn::make('mitra.kategori')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'SKPD' => 'info',
                        'Swasta' => 'success',
                        'UMKM' => 'warning',
                        'Desa' => 'primary',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('dpl.name')
                    ->label('Dosen Pembimbing (DPL)')
                    ->searchable()
                    ->sortable()
                    ->default('- Belum Ditentukan -'),
                TextColumn::make('students_count')
                    ->counts('students')
                    ->label('Jml Mahasiswa')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                TextColumn::make('grading_progress')
                    ->label('Status Penilaian')
                    ->state(function (Group $record): string {
                        $total = $record->students()->count();
                        $graded = $record->students()->whereNotNull('final_score')->count();
                        return "{$graded} / {$total} Mahasiswa";
                    })
                    ->badge()
                    ->color(function (Group $record): string {
                        $total = $record->students()->count();
                        $graded = $record->students()->whereNotNull('final_score')->count();
                        if ($total === 0) return 'gray';
                        if ($graded === $total) return 'success';
                        if ($graded > 0) return 'warning';
                        return 'danger';
                    }),
                TextColumn::make('academic_year')
                    ->label('Tahun')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('dpl_id')
                    ->label('Filter DPL')
                    ->options(fn () => \App\Models\User::where('role', 'dpl')->orderBy('name')->pluck('name', 'id'))
                    ->visible(fn () => auth()->user()?->isAdmin() ?? false)
                    ->searchable()
                    ->preload(),
                \Filament\Tables\Filters\SelectFilter::make('mitra_kategori')
                    ->label('Kategori Mitra')
                    ->options([
                        'SKPD' => 'SKPD (Pemerintah/Dinas)',
                        'Swasta' => 'Swasta / Korporasi',
                        'UMKM' => 'UMKM',
                        'Desa' => 'Pemerintah Desa',
                    ])
                    ->query(fn (\Illuminate\Database\Eloquent\Builder $query, array $data) => filled($data['value'] ?? null)
                        ? $query->whereHas('mitra', fn ($q) => $q->where('kategori', $data['value']))
                        : $query
                    ),
            ])
            ->recordActions([
                Action::make('input_nilai')
                    ->label('Kelola Nilai')
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary')
                    ->url(fn (Group $record): string => \App\Filament\Resources\Students\StudentResource::getUrl('index', ['tableFilters' => ['group_id' => ['value' => $record->id]]])),
                EditAction::make()
                    ->visible(fn () => auth()->user()?->isAdmin() ?? false),
                DeleteAction::make()
                    ->visible(fn () => auth()->user()?->isAdmin() ?? false),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->isAdmin() ?? false),
                ]),
            ]);
    }
}
