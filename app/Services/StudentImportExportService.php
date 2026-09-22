<?php

namespace App\Services;

use App\Models\Group;
use App\Models\Mitra;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentImportExportService
{
    /**
     * Download template resmi import data mahasiswa & kelompok dalam format Excel (.xlsx)
     */
    public static function downloadTemplate(): StreamedResponse
    {
        $filename = 'template_import_mahasiswa_ppl.xlsx';

        return response()->stream(function () {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Template Mahasiswa PPL');

            // Header kolom
            $headers = [
                'NIM',
                'Nama Mahasiswa',
                'Program Studi',
                'Nama Kelompok',
                'Lokasi / Nama Mitra',
                'Username DPL',
                'Tahun Akademik',
            ];

            // Set Header
            foreach ($headers as $colIndex => $headerText) {
                $colLetter = chr(65 + $colIndex);
                $sheet->setCellValue("{$colLetter}1", $headerText);
            }

            // Header Styling (FEB Deep Blue #1E40AF)
            $headerStyle = [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'name' => 'Calibri',
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E40AF'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'D1D5DB'],
                    ],
                ],
            ];
            $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);
            $sheet->getRowDimension(1)->setRowHeight(28);

            // Baris Contoh Data
            $samples = [
                ['202201001', 'Ahmad Rizki Fauzi', 'Manajemen', 'KELOMPOK 01', 'Bank BJB Cabang Kuningan', 'DPL_PPL01', '2026/2027'],
                ['202202001', 'Siti Nurhaliza', 'Akuntansi', 'KELOMPOK 02', 'Dinas Koperasi & UMKM Kuningan', 'DPL_PPL02', '2026/2027'],
                ['202203001', 'Budi Santoso', 'Bisnis Digital', 'KELOMPOK 03', 'PT Telkom Indonesia Kuningan', 'DPL_PPL03', '2026/2027'],
            ];

            $rowNum = 2;
            foreach ($samples as $row) {
                $sheet->setCellValueExplicit("A{$rowNum}", $row[0], DataType::TYPE_STRING);
                $sheet->setCellValue("B{$rowNum}", $row[1]);
                $sheet->setCellValue("C{$rowNum}", $row[2]);
                $sheet->setCellValue("D{$rowNum}", $row[3]);
                $sheet->setCellValue("E{$rowNum}", $row[4]);
                $sheet->setCellValue("F{$rowNum}", $row[5]);
                $sheet->setCellValue("G{$rowNum}", $row[6]);

                $sheet->getStyle("A{$rowNum}:G{$rowNum}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'E5E7EB'],
                        ],
                    ],
                ]);
                $sheet->getStyle("A{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("C{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("D{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("F{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("G{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getRowDimension($rowNum)->setRowHeight(22);
                $rowNum++;
            }

            // Auto-size columns
            foreach (range('A', 'G') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Export rekapitulasi nilai PPL ke format Excel (.xlsx) dengan styling FEB UNIKU
     */
    public static function exportGrades(?int $groupId = null, ?int $dplId = null): StreamedResponse
    {
        $filename = 'rekap_nilai_ppl_feb_uniku_' . date('Ymd_His') . '.xlsx';

        return response()->stream(function () use ($groupId, $dplId) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Rekap Nilai PPL');

            // Judul Dokumen
            $sheet->setCellValue('A1', 'FAKULTAS EKONOMI DAN BISNIS - UNIVERSITAS KUNINGAN');
            $sheet->setCellValue('A2', 'REKAPITULASI NILAI PRAKTEK PENGALAMAN LAPANGAN (PPL)');
            $sheet->setCellValue('A3', 'Tahun Akademik: 2026/2027 | Tanggal Cetak: ' . date('d/m/Y H:i'));

            $sheet->mergeCells('A1:L1');
            $sheet->mergeCells('A2:L2');
            $sheet->mergeCells('A3:L3');

            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1E40AF'));
            $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(10)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('6B7280'));

            // Header Tabel
            $headers = [
                'No',
                'NIM',
                'Nama Mahasiswa',
                'Program Studi',
                'Kelompok PPL',
                'Lokasi / Mitra',
                'Dosen Pembimbing (DPL)',
                'Nilai Mitra (60%)',
                'Nilai DPL (40%)',
                'Nilai Akhir',
                'Nilai Huruf',
                'Status Nilai',
            ];

            $headerRow = 5;
            foreach ($headers as $colIndex => $headerText) {
                $colLetter = chr(65 + $colIndex);
                $sheet->setCellValue("{$colLetter}{$headerRow}", $headerText);
            }

            // Styling Header Tabel
            $sheet->getStyle("A{$headerRow}:L{$headerRow}")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'name' => 'Calibri',
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E40AF'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'D1D5DB'],
                    ],
                ],
            ]);
            $sheet->getRowDimension($headerRow)->setRowHeight(28);

            // Query Data Mahasiswa
            $query = Student::with(['group.dpl', 'group.mitra'])->orderBy('group_id')->orderBy('nim');

            if ($groupId) {
                $query->where('group_id', $groupId);
            }

            if ($dplId) {
                $query->whereHas('group', fn ($q) => $q->where('dpl_id', $dplId));
            }

            $currentRow = $headerRow + 1;
            $no = 1;

            foreach ($query->cursor() as $student) {
                $mitraNama = $student->group?->mitra?->nama_mitra ?? $student->group?->location ?? '-';

                $sheet->setCellValue("A{$currentRow}", $no++);
                $sheet->setCellValueExplicit("B{$currentRow}", $student->nim, DataType::TYPE_STRING);
                $sheet->setCellValue("C{$currentRow}", $student->name);
                $sheet->setCellValue("D{$currentRow}", $student->prodi ?? '-');
                $sheet->setCellValue("E{$currentRow}", $student->group?->group_name ?? '-');
                $sheet->setCellValue("F{$currentRow}", $mitraNama);
                $sheet->setCellValue("G{$currentRow}", $student->group?->dpl?->name ?? '-');
                
                // Nilai
                if (is_numeric($student->mitra_score)) {
                    $sheet->setCellValue("H{$currentRow}", (float) $student->mitra_score);
                    $sheet->getStyle("H{$currentRow}")->getNumberFormat()->setFormatCode('0.00');
                } else {
                    $sheet->setCellValue("H{$currentRow}", '-');
                }

                if (is_numeric($student->dpl_score)) {
                    $sheet->setCellValue("I{$currentRow}", (float) $student->dpl_score);
                    $sheet->getStyle("I{$currentRow}")->getNumberFormat()->setFormatCode('0.00');
                } else {
                    $sheet->setCellValue("I{$currentRow}", '-');
                }

                if (is_numeric($student->final_score)) {
                    $sheet->setCellValue("J{$currentRow}", (float) $student->final_score);
                    $sheet->getStyle("J{$currentRow}")->getNumberFormat()->setFormatCode('0.00');
                } else {
                    $sheet->setCellValue("J{$currentRow}", '-');
                }

                $sheet->setCellValue("K{$currentRow}", $student->letter_grade ?? '-');
                $sheet->setCellValue("L{$currentRow}", $student->status === 'locked' ? 'Final (Terkunci)' : 'Draft');

                // Garis baris & alignment
                $sheet->getStyle("A{$currentRow}:L{$currentRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'E5E7EB'],
                        ],
                    ],
                ]);

                $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("B{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("D{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("H{$currentRow}:J{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("K{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("L{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getRowDimension($currentRow)->setRowHeight(22);
                $currentRow++;
            }

            // Auto-size columns A through L
            foreach (range('A', 'L') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Import data mahasiswa dan kelompok dari file Excel (.xlsx / .xls) maupun CSV.
     * Mendukung pemetaan otomatis: NIM, Nama Mahasiswa, Program Studi, Nama Kelompok, Lokasi / Mitra, Username DPL, Tahun Akademik.
     */
    public static function importFromExcel(string $filePath): array
    {
        $imported = 0;
        $updated = 0;
        $errors = [];

        if (! file_exists($filePath)) {
            return [
                'success' => false,
                'message' => 'File import tidak ditemukan di server.',
                'imported' => 0,
                'updated' => 0,
                'errors' => ['File tidak ditemukan.'],
            ];
        }

        try {
            // Load Spreadsheet (mendukung otomatis .xlsx, .xls, dan .csv)
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray(null, true, false, false);
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Gagal membaca berkas: ' . $e->getMessage(),
                'imported' => 0,
                'updated' => 0,
                'errors' => [$e->getMessage()],
            ];
        }

        if (empty($data) || count($data) < 2) {
            return [
                'success' => false,
                'message' => 'File tidak memiliki data atau baris kosong.',
                'imported' => 0,
                'updated' => 0,
                'errors' => ['Format tabel kosong.'],
            ];
        }

        // Deteksi Header pada baris pertama yang berisi teks
        $headerRowIndex = 0;
        foreach ($data as $idx => $row) {
            $filtered = array_filter(array_map('trim', $row));
            if (! empty($filtered)) {
                $headerRowIndex = $idx;
                break;
            }
        }

        $header = array_map(fn ($col) => strtolower(trim((string) $col)), $data[$headerRowIndex]);

        // Mapping Kolom Dinamis
        $colNim = null;
        $colName = null;
        $colProdi = null;
        $colGroup = null;
        $colLocation = null;
        $colDpl = null;
        $colYear = null;

        foreach ($header as $index => $colNameText) {
            if (str_contains($colNameText, 'nim')) {
                $colNim = $index;
            } elseif (str_contains($colNameText, 'nama mahasiswa') || $colNameText === 'nama' || $colNameText === 'name') {
                $colName = $index;
            } elseif (str_contains($colNameText, 'prodi') || str_contains($colNameText, 'program studi') || str_contains($colNameText, 'jurusan')) {
                $colProdi = $index;
            } elseif (str_contains($colNameText, 'kelompok') || str_contains($colNameText, 'group')) {
                $colGroup = $index;
            } elseif (str_contains($colNameText, 'lokasi') || str_contains($colNameText, 'mitra') || str_contains($colNameText, 'instansi')) {
                $colLocation = $index;
            } elseif (str_contains($colNameText, 'dpl') || str_contains($colNameText, 'dosen')) {
                $colDpl = $index;
            } elseif (str_contains($colNameText, 'tahun') || str_contains($colNameText, 'akademik') || str_contains($colNameText, 'year')) {
                $colYear = $index;
            }
        }

        // Positional fallback jika header tidak terdeteksi dengan nama
        if ($colNim === null) $colNim = 0;
        if ($colName === null) $colName = 1;

        if ($colProdi === null && count($header) >= 7) {
            $colProdi = 2;
            $colGroup ??= 3;
            $colLocation ??= 4;
            $colDpl ??= 5;
            $colYear ??= 6;
        } else {
            $colGroup ??= 2;
            $colLocation ??= 3;
            $colDpl ??= 4;
            $colYear ??= 5;
        }

        DB::beginTransaction();
        try {
            $totalRows = count($data);
            for ($i = $headerRowIndex + 1; $i < $totalRows; $i++) {
                $row = $data[$i];
                $actualRowNumber = $i + 1;

                if (empty(array_filter(array_map('trim', $row)))) {
                    continue;
                }

                $nim = trim((string) ($row[$colNim] ?? ''));
                $name = trim((string) ($row[$colName] ?? ''));
                $prodi = $colProdi !== null ? trim((string) ($row[$colProdi] ?? '')) : null;
                $groupName = trim((string) ($row[$colGroup] ?? ''));
                $location = $colLocation !== null ? trim((string) ($row[$colLocation] ?? '')) : '';
                $dplUsername = $colDpl !== null ? trim((string) ($row[$colDpl] ?? '')) : '';
                $academicYear = $colYear !== null && ! empty($row[$colYear]) ? trim((string) $row[$colYear]) : '2026/2027';

                if (empty($nim) || empty($name) || empty($groupName)) {
                    $errors[] = "Baris #{$actualRowNumber}: Kolom NIM, Nama Mahasiswa, dan Nama Kelompok wajib diisi.";
                    continue;
                }

                // Normalisasi Program Studi jika ada
                if (! empty($prodi)) {
                    if (str_contains(strtolower($prodi), 'manajemen')) {
                        $prodi = 'Manajemen';
                    } elseif (str_contains(strtolower($prodi), 'akuntansi')) {
                        $prodi = 'Akuntansi';
                    } elseif (str_contains(strtolower($prodi), 'bisnis') || str_contains(strtolower($prodi), 'digital')) {
                        $prodi = 'Bisnis Digital';
                    }
                } else {
                    $prodi = null;
                }

                // Cari DPL berdasarkan username, NIP, atau Nama
                $dplId = null;
                if (! empty($dplUsername)) {
                    $dpl = User::where('role', 'dpl')
                        ->where(function ($q) use ($dplUsername) {
                            $q->where('username', $dplUsername)
                              ->orWhere('nip_nidn', $dplUsername)
                              ->orWhere('name', 'like', "%{$dplUsername}%");
                        })->first();

                    if ($dpl) {
                        $dplId = $dpl->id;
                    }
                }

                // Hubungkan atau temukan mitra jika ada
                $mitraId = null;
                if (! empty($location)) {
                    $mitra = Mitra::where('nama_mitra', 'like', "%{$location}%")->first();
                    if ($mitra) {
                        $mitraId = $mitra->id;
                    }
                }

                // Cari atau buat Kelompok
                $group = Group::firstOrCreate(
                    ['group_name' => $groupName],
                    [
                        'dpl_id' => $dplId,
                        'mitra_id' => $mitraId,
                        'location' => $location,
                        'academic_year' => $academicYear,
                    ]
                );

                // Update info kelompok jika sebelumnya masih kosong
                $groupUpdates = [];
                if ($dplId && ! $group->dpl_id) $groupUpdates['dpl_id'] = $dplId;
                if ($mitraId && ! $group->mitra_id) $groupUpdates['mitra_id'] = $mitraId;
                if ($location && ! $group->location) $groupUpdates['location'] = $location;
                if (! empty($groupUpdates)) {
                    $group->update($groupUpdates);
                }

                // Simpan atau update Mahasiswa
                $studentData = [
                    'group_id' => $group->id,
                    'name' => $name,
                ];
                if ($prodi) {
                    $studentData['prodi'] = $prodi;
                }

                $existing = Student::where('nim', $nim)->first();
                if ($existing) {
                    $existing->update($studentData);
                    $updated++;
                } else {
                    $studentData['nim'] = $nim;
                    $studentData['status'] = 'draft';
                    Student::create($studentData);
                    $imported++;
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => "Proses import Excel selesai: {$imported} mahasiswa baru ditambahkan, {$updated} diperbarui.",
                'imported' => $imported,
                'updated' => $updated,
                'errors' => $errors,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses file import: ' . $e->getMessage(),
                'imported' => $imported,
                'updated' => $updated,
                'errors' => array_merge($errors, [$e->getMessage()]),
            ];
        }
    }

    /**
     * Backward-compatible alias for CSV import
     */
    public static function importFromCsv(string $filePath): array
    {
        return self::importFromExcel($filePath);
    }
}
