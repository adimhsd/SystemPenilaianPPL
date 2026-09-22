<?php

use App\Http\Controllers\GradeExportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/export-grades', [GradeExportController::class, 'export'])->name('grades.export');
    Route::get('/template-students', [GradeExportController::class, 'template'])->name('students.template');
    Route::get('/export-users', function () {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Hanya Administrator yang berhak mengekspor data akun pengguna.');
        }
        return \App\Services\UserExportService::exportUsers();
    })->name('users.export');
    Route::get('/backup-download/{filename}', function (string $filename) {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Hanya Administrator yang berhak mengunduh cadangan database.');
        }
        return \App\Services\DatabaseBackupService::downloadBackup($filename);
    })->name('backup.download');
});

// Backward compatibility redirects if user accesses old /admin URLs
Route::get('/admin/{any?}', function (?string $any = null) {
    return redirect('/' . ($any ? ltrim($any, '/') : ''));
})->where('any', '.*');
