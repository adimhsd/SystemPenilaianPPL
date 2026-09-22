<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAndAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/admin/login');
        $response->assertStatus(200);
        $response->assertSee('FEB UNIKU - Rekap Nilai PPL');
    }

    public function test_admin_can_access_all_resources(): void
    {
        $admin = User::where('role', 'admin')->first();

        // Akses Dashboard
        $response = $this->actingAs($admin)->get('/admin');
        $response->assertStatus(200);

        // Akses Manajemen Pengguna
        $response = $this->actingAs($admin)->get('/admin/users');
        $response->assertStatus(200);

        // Akses Kelompok PPL
        $response = $this->actingAs($admin)->get('/admin/groups');
        $response->assertStatus(200);

        // Akses Mitra
        $response = $this->actingAs($admin)->get('/admin/mitras');
        $response->assertStatus(200);

        // Akses Input Nilai
        $response = $this->actingAs($admin)->get('/admin/students');
        $response->assertStatus(200);
    }

    public function test_dpl_cannot_access_user_management(): void
    {
        $dpl = User::where('role', 'dpl')->first();

        // DPL mengakses Dashboard
        $response = $this->actingAs($dpl)->get('/admin');
        $response->assertStatus(200);

        // DPL dilarang mengakses User Management (403 Forbidden)
        $response = $this->actingAs($dpl)->get('/admin/users');
        $response->assertStatus(403);

        // DPL dilarang mengakses Mitra Management (403 Forbidden)
        $response = $this->actingAs($dpl)->get('/admin/mitras');
        $response->assertStatus(403);

        // DPL tetap bisa mengakses Kelompok dan Mahasiswa miliknya
        $response = $this->actingAs($dpl)->get('/admin/groups');
        $response->assertStatus(200);

        $response = $this->actingAs($dpl)->get('/admin/students');
        $response->assertStatus(200);
    }

    public function test_export_and_template_downloads(): void
    {
        $admin = User::where('role', 'admin')->first();

        // Download Template Excel (.xlsx)
        $templateResponse = $this->actingAs($admin)->get('/admin/template-students');
        $templateResponse->assertStatus(200);
        $templateResponse->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('template_import_mahasiswa_ppl.xlsx', (string) $templateResponse->headers->get('content-disposition'));

        // Export Nilai Excel (.xlsx)
        $exportResponse = $this->actingAs($admin)->get('/admin/export-grades');
        $exportResponse->assertStatus(200);
        $exportResponse->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('.xlsx', (string) $exportResponse->headers->get('content-disposition'));
    }
}
