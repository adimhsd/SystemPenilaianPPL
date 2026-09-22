<?php

namespace App\Services;

use App\Models\User;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserExportService
{
    public static function exportUsers(): StreamedResponse
    {
        $filename = 'daftar_akun_dpl_dan_user_ppl_' . date('Ymd_His') . '.xlsx';

        return response()->stream(function () {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Akun DPL & Pengguna PPL');

            // 1. Judul & Kop Dokumen
            $sheet->mergeCells('A1:I1');
            $sheet->setCellValue('A1', 'FAKULTAS EKONOMI DAN BISNIS UNIVERSITAS KUNINGAN');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1E40AF'));
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->mergeCells('A2:I2');
            $sheet->setCellValue('A2', 'DAFTAR AKUN AKSES PENGGUNA & DOSEN PEMBIMBING LAPANGAN (DPL)');
            $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $loginUrl = url('/login');
            $sheet->mergeCells('A3:I3');
            $sheet->setCellValue('A3', 'Portal Sistem: ' . $loginUrl . ' | Tanggal Unduh: ' . date('d/m/Y H:i') . ' WIB');
            $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(10)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('4B5563'));
            $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Kotak Catatan Informasi
            $sheet->mergeCells('A5:I5');
            $sheet->setCellValue('A5', 'CATATAN: Password default untuk akun awal adalah "password". Harap bagikan informasi akun ini secara aman dan sarankan pengguna untuk segera memperbarui password setelah berhasil masuk.');
            $sheet->getStyle('A5')->getFont()->setSize(9)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1E40AF'));
            $sheet->getStyle('A5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EFF6FF');
            $sheet->getStyle('A5')->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getRowDimension(5)->setRowHeight(24);

            // 2. Header Tabel
            $headers = [
                'A' => 'No',
                'B' => 'Nama Lengkap Dosen / Pengguna',
                'C' => 'Username (Untuk Login)',
                'D' => 'Email Terdaftar',
                'E' => 'Peran (Role)',
                'F' => 'Kelompok Bimbingan PPL',
                'G' => 'Jml Mhs Bimbingan',
                'H' => 'Password Awal (Default)',
                'I' => 'URL Akses Portal',
            ];

            foreach ($headers as $col => $label) {
                $sheet->setCellValue("{$col}7", $label);
            }

            // Styling Header
            $sheet->getStyle('A7:I7')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 10,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E40AF'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'D1D5DB'],
                    ],
                ],
            ]);
            $sheet->getRowDimension(7)->setRowHeight(28);

            // 3. Data Users (Eager load groups & students)
            $users = User::with(['groups.students'])->orderByRaw("CASE WHEN role = 'admin' THEN 1 ELSE 2 END")->orderBy('name')->get();

            $row = 8;
            $no = 1;
            foreach ($users as $user) {
                $groupNames = $user->groups->pluck('group_name')->join(', ');
                $totalStudents = $user->groups->sum(fn ($g) => $g->students->count());

                $sheet->setCellValue("A{$row}", $no);
                $sheet->setCellValue("B{$row}", $user->name);
                $sheet->setCellValueExplicit("C{$row}", (string) $user->username, DataType::TYPE_STRING);
                $sheet->setCellValue("D{$row}", $user->email);
                $sheet->setCellValue("E{$row}", $user->role === 'admin' ? 'Admin Fakultas' : 'Dosen Pembimbing (DPL)');
                $sheet->setCellValue("F{$row}", $groupNames ?: '-');
                $sheet->setCellValue("G{$row}", $totalStudents > 0 ? $totalStudents : '-');
                $sheet->setCellValueExplicit("H{$row}", 'password', DataType::TYPE_STRING);
                $sheet->setCellValue("I{$row}", $loginUrl);

                // Zebra striping
                if ($no % 2 === 0) {
                    $sheet->getStyle("A{$row}:I{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
                }

                // Alignments
                $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Password font styling (monospace look)
                $sheet->getStyle("H{$row}")->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('DC2626'));

                $row++;
                $no++;
            }

            $lastRow = $row - 1;

            // Border seluruh tabel
            if ($lastRow >= 8) {
                $sheet->getStyle("A8:I{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'E2E8F0'],
                        ],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
            }

            // Auto-width kolom
            foreach (range('A', 'I') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Stream response
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
            'Pragma' => 'public',
        ]);
    }
}
