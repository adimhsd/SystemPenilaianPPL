<?php

namespace App\Filament\Resources\Students\Tables;

use App\Models\Group;
use App\Models\Student;
use App\Services\StudentImportExportService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class StudentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nim')
                    ->label('NIM')
                    ->searchable()
                    ->copyable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('name')
                    ->label('Nama Mahasiswa')
                    ->searchable()
                    ->weight('bold')
                    ->sortable(),
                TextColumn::make('prodi')
                    ->label('Prodi')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'Manajemen' => 'info',
                        'Akuntansi' => 'success',
                        'Bisnis Digital' => 'warning',
                        default => 'gray',
                    })
                    ->sortable()
                    ->searchable(),
                TextColumn::make('group.group_name')
                    ->label('Kelompok PPL')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('group.mitra.nama_mitra')
                    ->label('Mitra Lokasi')
                    ->limit(25)
                    ->tooltip(fn (Student $record) => $record->group?->mitra?->nama_mitra ?? $record->group?->location)
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('group.dpl.name')
                    ->label('DPL')
                    ->searchable()
                    ->sortable()
                    ->visible(fn () => auth()->user()?->isAdmin() ?? false),
                TextColumn::make('mitra_score')
                    ->label('Mitra (60%)')
                    ->alignCenter()
                    ->sortable()
                    ->default('-')
                    ->formatStateUsing(fn ($state) => is_numeric($state) ? number_format((float) $state, 1) : '-'),
                TextColumn::make('dpl_score')
                    ->label('Laporan (40%)')
                    ->alignCenter()
                    ->sortable()
                    ->default('-')
                    ->formatStateUsing(fn ($state) => is_numeric($state) ? number_format((float) $state, 1) : '-'),
                TextColumn::make('final_score')
                    ->label('Nilai Akhir')
                    ->alignCenter()
                    ->weight('bold')
                    ->sortable()
                    ->default('-')
                    ->formatStateUsing(fn ($state) => is_numeric($state) ? number_format((float) $state, 2) : '-'),
                TextColumn::make('letter_grade')
                    ->label('Grade')
                    ->alignCenter()
                    ->badge()
                    ->default('-')
                    ->color(fn (?string $state): string => match ($state) {
                        'A', 'AB' => 'success',
                        'B', 'BC' => 'info',
                        'C', 'CD' => 'warning',
                        'D', 'E' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'locked' => 'success',
                        'draft' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'locked' => 'Final (Terkunci)',
                        'draft' => 'Draft',
                        default => $state,
                    }),
            ])
            ->filters([
                SelectFilter::make('prodi')
                    ->label('Program Studi')
                    ->options([
                        'Manajemen' => 'S1 Manajemen',
                        'Akuntansi' => 'S1 Akuntansi',
                        'Bisnis Digital' => 'S1 Bisnis Digital',
                    ]),
                SelectFilter::make('group_id')
                    ->label('Kelompok PPL')
                    ->relationship('group', 'group_name', function (Builder $query) {
                        if (auth()->user()?->isDpl()) {
                            $query->where('dpl_id', auth()->id());
                        }
                        return $query;
                    })
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->label('Status Nilai')
                    ->options([
                        'draft' => 'Draft',
                        'locked' => 'Terkunci (Final)',
                    ]),
                SelectFilter::make('letter_grade')
                    ->label('Nilai Huruf')
                    ->options([
                        'A' => 'A (81 - 100)',
                        'AB' => 'AB (75 - 80.9)',
                        'B' => 'B (69 - 74.9)',
                        'BC' => 'BC (63 - 68.9)',
                        'C' => 'C (57 - 62.9)',
                        'CD' => 'CD (51 - 56.9)',
                        'D' => 'D (45 - 50.9)',
                        'E' => 'E (< 45)',
                    ]),
            ])
            ->recordActions([
                // Quick Input Nilai Modal Action
                Action::make('input_nilai')
                    ->label('Input Nilai')
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary')
                    ->visible(fn (Student $record) => $record->status !== 'locked' || (auth()->user()?->isAdmin() ?? false))
                    ->modalHeading(fn (Student $record) => "Input Nilai: {$record->name} ({$record->nim})")
                    ->modalDescription(fn (Student $record) => "Kelompok: {$record->group?->group_name} | Mitra: {$record->group?->location}")
                    ->form([
                        TextInput::make('mitra_score')
                            ->label('Nilai Mitra / Lapangan (Bobot 60%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01)
                            ->required()
                            ->default(fn (Student $record) => $record->mitra_score)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, callable $get): void {
                                $mitra = is_numeric($state) ? (float) $state : null;
                                $dpl = is_numeric($get('dpl_score')) ? (float) $get('dpl_score') : null;
                                if ($mitra !== null && $dpl !== null) {
                                    $final = Student::calculateFinalScore($mitra, $dpl);
                                    $set('calc_preview', "Nilai Akhir: {$final} | Grade: " . Student::calculateLetterGrade($final));
                                }
                            }),

                        TextInput::make('dpl_score')
                            ->label('Nilai Laporan DPL (Bobot 40%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01)
                            ->required()
                            ->default(fn (Student $record) => $record->dpl_score)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, callable $get): void {
                                $dpl = is_numeric($state) ? (float) $state : null;
                                $mitra = is_numeric($get('mitra_score')) ? (float) $get('mitra_score') : null;
                                if ($mitra !== null && $dpl !== null) {
                                    $final = Student::calculateFinalScore($mitra, $dpl);
                                    $set('calc_preview', "Nilai Akhir: {$final} | Grade: " . Student::calculateLetterGrade($final));
                                }
                            }),

                        TextInput::make('calc_preview')
                            ->label('Live Preview Kalkulasi')
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Standar Huruf Mutu: A (81–100), AB (75–80.9), B (69–74.9), BC (63–68.9), C (57–62.9), CD (51–56.9), D (45–50.9), E (0–44.9)')
                            ->default(function (Student $record) {
                                if ($record->final_score !== null) {
                                    return "Nilai Akhir: {$record->final_score} | Grade: {$record->letter_grade}";
                                }
                                return 'Masukkan kedua nilai untuk melihat kalkulasi otomatis.';
                            }),

                        Select::make('status')
                            ->label('Status Simpan')
                            ->options([
                                'draft' => 'Simpan sebagai Draft (Masih bisa diedit)',
                                'locked' => 'Kunci Nilai (Final)',
                            ])
                            ->default(fn (Student $record) => $record->status ?? 'draft')
                            ->required(),
                    ])
                    ->action(function (Student $record, array $data): void {
                        $mitra = (float) $data['mitra_score'];
                        $dpl = (float) $data['dpl_score'];
                        $final = Student::calculateFinalScore($mitra, $dpl);
                        $grade = Student::calculateLetterGrade($final);

                        $record->update([
                            'mitra_score' => $mitra,
                            'dpl_score' => $dpl,
                            'final_score' => $final,
                            'letter_grade' => $grade,
                            'status' => $data['status'],
                        ]);

                        Notification::make()
                            ->title('Nilai Berhasil Disimpan')
                            ->body("Nilai {$record->name} berhasil diperbarui (Akhir: {$final} / Grade: {$grade}).")
                            ->success()
                            ->send();
                    }),

                // Lock action
                Action::make('lock')
                    ->label('Kunci')
                    ->icon('heroicon-o-lock-closed')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Kunci Nilai Mahasiswa?')
                    ->modalDescription('Nilai yang sudah dikunci tidak dapat diubah oleh DPL kecuali dibuka oleh Admin.')
                    ->visible(fn (Student $record) => $record->status === 'draft' && $record->final_score !== null)
                    ->action(function (Student $record): void {
                        $record->update(['status' => 'locked']);
                        Notification::make()
                            ->title('Nilai Berhasil Dikunci')
                            ->success()
                            ->send();
                    }),

                // Unlock action (Admin only)
                Action::make('unlock')
                    ->label('Buka Kunci')
                    ->icon('heroicon-o-lock-open')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Buka Kunci Nilai?')
                    ->modalDescription('DPL akan dapat mengubah kembali nilai mahasiswa ini.')
                    ->visible(fn (Student $record) => $record->status === 'locked' && (auth()->user()?->isAdmin() ?? false))
                    ->action(function (Student $record): void {
                        $record->update(['status' => 'draft']);
                        Notification::make()
                            ->title('Kunci Nilai Dibuka')
                            ->body("DPL sekarang dapat mengedit nilai {$record->name}.")
                            ->info()
                            ->send();
                    }),

                EditAction::make()
                    ->visible(fn () => auth()->user()?->isAdmin() ?? false),

                DeleteAction::make()
                    ->visible(fn () => auth()->user()?->isAdmin() ?? false),
            ])
            ->headerActions([
                // Export Rekap Nilai Excel (.xlsx)
                Action::make('export_excel')
                    ->label('Export Rekap Nilai (Excel)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(route('grades.export'))
                    ->openUrlInNewTab(),

                // Import Data Mahasiswa (Admin Only)
                Action::make('import_mahasiswa')
                    ->label('Import Mahasiswa (Excel)')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('info')
                    ->visible(fn () => auth()->user()?->isAdmin() ?? false)
                    ->modalHeading('Import Data Mahasiswa & Kelompok PPL (Excel)')
                    ->modalDescription('Unggah file Excel (.xlsx) dengan kolom: NIM, Nama Mahasiswa, Program Studi, Nama Kelompok, Lokasi / Mitra, Username DPL, dan Tahun Akademik.')
                    ->form([
                        FileUpload::make('file')
                            ->label('Pilih File Excel (.xlsx)')
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel',
                                'text/csv',
                            ])
                            ->disk('local')
                            ->directory('imports')
                            ->required()
                            ->helperText('Gunakan format Excel (.xlsx). Anda dapat mengunduh format template resmi melalui tombol di bawah.'),
                    ])
                    ->modalSubmitActionLabel('Mulai Import Data')
                    ->extraModalFooterActions([
                        Action::make('download_template')
                            ->label('Download Template Import (.xlsx)')
                            ->icon('heroicon-o-document-arrow-down')
                            ->color('gray')
                            ->url(route('students.template'))
                            ->openUrlInNewTab(),
                    ])
                    ->action(function (array $data): void {
                        $filePath = storage_path('app/' . $data['file']);
                        $result = StudentImportExportService::importFromExcel($filePath);

                        if ($result['success']) {
                            Notification::make()
                                ->title('Import Berhasil')
                                ->body($result['message'])
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Gagal Import Data')
                                ->body($result['message'])
                                ->danger()
                                ->send();
                        }

                        // Hapus file temporary setelah import
                        if (file_exists($filePath)) {
                            @unlink($filePath);
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    \Filament\Actions\BulkAction::make('lock_selected')
                        ->label('Kunci Semua Nilai Terpilih')
                        ->icon('heroicon-o-lock-closed')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $count = 0;
                            foreach ($records as $record) {
                                if ($record->final_score !== null) {
                                    $record->update(['status' => 'locked']);
                                    $count++;
                                }
                            }
                            Notification::make()
                                ->title("{$count} Mahasiswa Berhasil Dikunci")
                                ->success()
                                ->send();
                        }),

                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->isAdmin() ?? false),
                ]),
            ]);
    }
}
