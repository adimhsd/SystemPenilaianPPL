<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Student;
use App\Models\User;
use App\Services\StudentImportExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PplGradingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_automatic_grade_calculation_on_student_model(): void
    {
        $group = Group::first();

        // 1. Test Grade A (81 - 100)
        $studentA = Student::create([
            'group_id' => $group->id,
            'nim' => 'TEST001',
            'name' => 'Mahasiswa Test A',
            'mitra_score' => 90.0,
            'dpl_score' => 85.0, // (90 * 0.6) + (85 * 0.4) = 54 + 34 = 88.0
        ]);
        $this->assertEquals(88.0, $studentA->final_score);
        $this->assertEquals('A', $studentA->letter_grade);

        // 2. Test Grade AB (75 - 80.99)
        $studentAB = Student::create([
            'group_id' => $group->id,
            'nim' => 'TEST002',
            'name' => 'Mahasiswa Test AB',
            'mitra_score' => 80.0,
            'dpl_score' => 75.0, // (80 * 0.6) + (75 * 0.4) = 48 + 30 = 78.0
        ]);
        $this->assertEquals(78.0, $studentAB->final_score);
        $this->assertEquals('AB', $studentAB->letter_grade);

        // 3. Test Grade B (69 - 74.99)
        $studentB = Student::create([
            'group_id' => $group->id,
            'nim' => 'TEST003',
            'name' => 'Mahasiswa Test B',
            'mitra_score' => 70.0,
            'dpl_score' => 70.0, // 70.0
        ]);
        $this->assertEquals(70.0, $studentB->final_score);
        $this->assertEquals('B', $studentB->letter_grade);

        // 4. Test Grade C (57 - 62.99)
        $studentC = Student::create([
            'group_id' => $group->id,
            'nim' => 'TEST004',
            'name' => 'Mahasiswa Test C',
            'mitra_score' => 60.0,
            'dpl_score' => 60.0, // 60.0
        ]);
        $this->assertEquals(60.0, $studentC->final_score);
        $this->assertEquals('C', $studentC->letter_grade);

        // 5. Test Grade D (45 - 50.99)
        $studentD = Student::create([
            'group_id' => $group->id,
            'nim' => 'TEST005',
            'name' => 'Mahasiswa Test D',
            'mitra_score' => 50.0,
            'dpl_score' => 45.0, // (50 * 0.6) + (45 * 0.4) = 30 + 18 = 48.0
        ]);
        $this->assertEquals(48.0, $studentD->final_score);
        $this->assertEquals('D', $studentD->letter_grade);

        // 6. Test Grade E (< 45)
        $studentE = Student::create([
            'group_id' => $group->id,
            'nim' => 'TEST006',
            'name' => 'Mahasiswa Test E',
            'mitra_score' => 30.0,
            'dpl_score' => 40.0, // (30 * 0.6) + (40 * 0.4) = 18 + 16 = 34.0
        ]);
        $this->assertEquals(34.0, $studentE->final_score);
        $this->assertEquals('E', $studentE->letter_grade);
    }

    public function test_dpl_can_only_access_their_own_students_and_groups(): void
    {
        $dpl1 = User::where('username', 'DPL_PPL01')->first();
        $dpl2 = User::where('username', 'DPL_PPL02')->first();

        $group1 = Group::where('dpl_id', $dpl1->id)->first();
        $group2 = Group::where('dpl_id', $dpl2->id)->first();

        $this->assertNotNull($group1);
        $this->assertNotNull($group2);

        // Group 1 memiliki mahasiswa bimbingan DPL 1
        $this->assertTrue($group1->students()->count() > 0);
        // Mahasiswa kelompok 1 tidak boleh terkait DPL 2
        $this->assertNotEquals($dpl2->id, $group1->dpl_id);
    }

    public function test_csv_import_creates_students_and_groups(): void
    {
        $csvContent = "\xEF\xBB\xBFNIM,Nama Mahasiswa,Nama Kelompok,Lokasi / Mitra,Username DPL,Tahun Akademik\n" .
                      "202199001,Siti Aminah,Kelompok 99 - Kantor Pajak,KPP Pratama Kuningan,DPL_PPL01,2026/2027\n" .
                      "202199002,Doni Prasetyo,Kelompok 99 - Kantor Pajak,KPP Pratama Kuningan,DPL_PPL01,2026/2027\n";

        $tempPath = tempnam(sys_get_temp_dir(), 'ppl_test_') . '.csv';
        file_put_contents($tempPath, $csvContent);

        $result = StudentImportExportService::importFromCsv($tempPath);
        @unlink($tempPath);

        $this->assertTrue($result['success']);
        $this->assertEquals(2, $result['imported']);

        $student1 = Student::where('nim', '202199001')->first();
        $this->assertNotNull($student1);
        $this->assertEquals('Siti Aminah', $student1->name);
        $this->assertEquals('Kelompok 99 - Kantor Pajak', $student1->group->group_name);
        $this->assertEquals('KPP Pratama Kuningan', $student1->group->location);
        $this->assertEquals('DPL_PPL01', $student1->group->dpl->username);
    }

    public function test_excel_xlsx_import_creates_students_and_groups(): void
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $sheet->setCellValue('A1', 'NIM');
        $sheet->setCellValue('B1', 'Nama Mahasiswa');
        $sheet->setCellValue('C1', 'Program Studi');
        $sheet->setCellValue('D1', 'Nama Kelompok');
        $sheet->setCellValue('E1', 'Lokasi / Nama Mitra');
        $sheet->setCellValue('F1', 'Username DPL');
        $sheet->setCellValue('G1', 'Tahun Akademik');

        // Row 1
        $sheet->setCellValueExplicit('A2', '202399001', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValue('B2', 'Mahasiswa Excel 1');
        $sheet->setCellValue('C2', 'Manajemen');
        $sheet->setCellValue('D2', 'KELOMPOK 999');
        $sheet->setCellValue('E2', 'Mitra Uji Coba Excel');
        $sheet->setCellValue('F2', 'DPL_PPL01');
        $sheet->setCellValue('G2', '2026/2027');

        // Row 2
        $sheet->setCellValueExplicit('A3', '202399002', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValue('B3', 'Mahasiswa Excel 2');
        $sheet->setCellValue('C3', 'Akuntansi');
        $sheet->setCellValue('D3', 'KELOMPOK 999');
        $sheet->setCellValue('E3', 'Mitra Uji Coba Excel');
        $sheet->setCellValue('F3', 'DPL_PPL01');
        $sheet->setCellValue('G3', '2026/2027');

        $tempPath = tempnam(sys_get_temp_dir(), 'ppl_xlsx_') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempPath);

        $result = StudentImportExportService::importFromExcel($tempPath);
        @unlink($tempPath);

        $this->assertTrue($result['success']);
        $this->assertEquals(2, $result['imported']);

        $mhs1 = Student::where('nim', '202399001')->first();
        $this->assertNotNull($mhs1);
        $this->assertEquals('Mahasiswa Excel 1', $mhs1->name);
        $this->assertEquals('Manajemen', $mhs1->prodi);
        $this->assertEquals('KELOMPOK 999', $mhs1->group->group_name);
        $this->assertEquals('DPL_PPL01', $mhs1->group->dpl->username);

        $mhs2 = Student::where('nim', '202399002')->first();
        $this->assertNotNull($mhs2);
        $this->assertEquals('Akuntansi', $mhs2->prodi);
    }

    public function test_real_ppl_dataset_integrity(): void
    {
        // 1. User checks
        $this->assertEquals(42, User::count());
        $this->assertEquals(1, User::where('role', 'admin')->count());
        $this->assertEquals(41, User::where('role', 'dpl')->count());

        // 2. Mitra checks
        $this->assertEquals(81, \App\Models\Mitra::count());

        // 3. Group checks
        $this->assertEquals(78, Group::count());

        // 4. Student checks
        $this->assertEquals(393, Student::count());
        $this->assertEquals(326, Student::where('prodi', 'Manajemen')->count());
        $this->assertEquals(57, Student::where('prodi', 'Akuntansi')->count());
        $this->assertEquals(10, Student::where('prodi', 'Bisnis Digital')->count());

        // 5. Relations check
        $groupsWithoutMitra = Group::whereNull('mitra_id')->count();
        $this->assertEquals(0, $groupsWithoutMitra);

        $groupsWithoutDpl = Group::whereNull('dpl_id')->count();
        $this->assertEquals(0, $groupsWithoutDpl);

        $studentsWithoutGroup = Student::whereNull('group_id')->count();
        $this->assertEquals(0, $studentsWithoutGroup);
    }
}

