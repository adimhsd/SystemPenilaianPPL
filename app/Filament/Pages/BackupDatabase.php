<?php

namespace App\Filament\Pages;

use App\Services\DatabaseBackupService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class BackupDatabase extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCircleStack;

    protected static ?string $navigationLabel = 'Backup & Restore DB';

    protected static ?string $title = 'Backup & Restore Database';

    protected static ?int $navigationSort = 90;

    protected string $view = 'filament.pages.backup-database';

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create_sql_backup')
                ->label('Buat Backup Baru (SQL)')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->action(function () {
                    $result = DatabaseBackupService::createBackup('sql');
                    if ($result['success']) {
                        Notification::make()
                            ->title('Backup SQL Berhasil')
                            ->body("Berkas cadangan {$result['filename']} berhasil dibuat.")
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Gagal Membuat Backup')
                            ->body($result['message'])
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('create_sqlite_backup')
                ->label('Download DB (.sqlite)')
                ->icon('heroicon-o-document-arrow-down')
                ->color('info')
                ->action(function () {
                    $result = DatabaseBackupService::createBackup('sqlite');
                    if ($result['success']) {
                        Notification::make()
                            ->title('Salinan SQLite Berhasil Dibuat')
                            ->body("Berkas {$result['filename']} berhasil disiapkan.")
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Gagal Menyiapkan Berkas')
                            ->body($result['message'])
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('upload_and_restore')
                ->label('Restore / Import Cadangan')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('warning')
                ->modalHeading('Pulihkan Database dari Berkas Cadangan?')
                ->modalDescription('PERINGATAN: Memulihkan database akan menimpa seluruh data aktif saat ini. Pastikan Anda telah membuat backup terbaru sebelum melakukan tindakan ini!')
                ->form([
                    FileUpload::make('backup_file')
                        ->label('Pilih Berkas Cadangan (.sql atau .sqlite)')
                        ->acceptedFileTypes([
                            'application/sql',
                            'text/plain',
                            'application/x-sqlite3',
                            'application/vnd.sqlite3',
                            'application/octet-stream',
                        ])
                        ->disk('local')
                        ->directory('temp_restore')
                        ->required()
                        ->helperText('Pilih berkas cadangan berekstensi .sql atau .sqlite yang sebelumnya telah Anda unduh.'),
                ])
                ->modalSubmitActionLabel('Mulai Pulihkan Database')
                ->action(function (array $data) {
                    $filePath = storage_path('app/' . $data['backup_file']);
                    $result = DatabaseBackupService::restoreFromFile($filePath);

                    if (file_exists($filePath)) {
                        @unlink($filePath);
                    }

                    if ($result['success']) {
                        Notification::make()
                            ->title('Pemulihan Database Berhasil')
                            ->body($result['message'])
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Gagal Memulihkan Database')
                            ->body($result['message'])
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    public function restoreBackupFile(string $filename): void
    {
        $filePath = DatabaseBackupService::getBackupDirectory() . DIRECTORY_SEPARATOR . basename($filename);
        $result = DatabaseBackupService::restoreFromFile($filePath);

        if ($result['success']) {
            Notification::make()
                ->title('Database Berhasil Dipulihkan')
                ->body($result['message'])
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Gagal Memulihkan')
                ->body($result['message'])
                ->danger()
                ->send();
        }
    }

    public function deleteBackupFile(string $filename): void
    {
        $deleted = DatabaseBackupService::deleteBackup($filename);
        if ($deleted) {
            Notification::make()
                ->title('Berkas Cadangan Dihapus')
                ->body("Berkas {$filename} telah berhasil dihapus.")
                ->info()
                ->send();
        } else {
            Notification::make()
                ->title('Gagal Menghapus')
                ->body('Berkas tidak ditemukan atau tidak dapat dihapus.')
                ->danger()
                ->send();
        }
    }

    public function getBackupsProperty(): array
    {
        return DatabaseBackupService::listBackups();
    }
}
