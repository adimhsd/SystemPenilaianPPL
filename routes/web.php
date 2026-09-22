<?php

use App\Http\Controllers\GradeExportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/admin/export-grades', [GradeExportController::class, 'export'])->name('grades.export');
    Route::get('/admin/template-students', [GradeExportController::class, 'template'])->name('students.template');
    Route::get('/admin/backup-download/{filename}', function (string $filename) {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Hanya Administrator yang berhak mengunduh cadangan database.');
        }
        return \App\Services\DatabaseBackupService::downloadBackup($filename);
    })->name('backup.download');
});
