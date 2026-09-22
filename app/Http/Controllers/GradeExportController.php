<?php

namespace App\Http\Controllers;

use App\Services\StudentImportExportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GradeExportController extends Controller
{
    public function export(Request $request): StreamedResponse
    {
        $groupId = $request->query('group_id') ? (int) $request->query('group_id') : null;
        
        // Jika DPL, batasi hanya kelompok miliknya
        $dplId = null;
        if (auth()->user()?->isDpl()) {
            $dplId = auth()->id();
        }

        return StudentImportExportService::exportGrades($groupId, $dplId);
    }

    public function template(): StreamedResponse
    {
        return StudentImportExportService::downloadTemplate();
    }
}
