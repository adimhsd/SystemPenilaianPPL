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
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('FEB UNIKU - Rekap Nilai PPL');
    }

    public function test_dpl_can_login_with_username_and_with_email(): void
    {
        // 1. Login menggunakan Username
        \Livewire\Livewire::test(\App\Filament\Pages\Auth\Login::class)
            ->fillForm([
                'login' => 'DPL_PPL01',
                'password' => 'FEB_Tangguh',
            ])
            ->call('authenticate')
            ->assertHasNoFormErrors()
            ->assertRedirect('/');

        $this->assertAuthenticated();
        auth()->logout();

        // 2. Login menggunakan Email
        \Livewire\Livewire::test(\App\Filament\Pages\Auth\Login::class)
            ->fillForm([
                'login' => 'dpl_ppl01@uniku.ac.id',
                'password' => 'FEB_Tangguh',
            ])
            ->call('authenticate')
            ->assertHasNoFormErrors()
            ->assertRedirect('/');

        $this->assertAuthenticated();
    }

    public function test_admin_can_access_all_resources(): void
    {
        $admin = User::where('role', 'admin')->first();

        // Akses Dashboard
        $response = $this->actingAs($admin)->get('/');
        $response->assertStatus(200);

        // Akses Manajemen Pengguna
        $response = $this->actingAs($admin)->get('/users');
        $response->assertStatus(200);

        // Akses Kelompok PPL
        $response = $this->actingAs($admin)->get('/groups');
        $response->assertStatus(200);

        // Akses Mitra
        $response = $this->actingAs($admin)->get('/mitras');
        $response->assertStatus(200);

        // Akses Input Nilai
        $response = $this->actingAs($admin)->get('/students');
        $response->assertStatus(200);
    }

    public function test_dpl_cannot_access_user_management(): void
    {
        $dpl = User::where('role', 'dpl')->first();

        // DPL mengakses Dashboard
        $response = $this->actingAs($dpl)->get('/');
        $response->assertStatus(200);

        // DPL dilarang mengakses User Management (403 Forbidden)
        $response = $this->actingAs($dpl)->get('/users');
        $response->assertStatus(403);

        // DPL dilarang mengakses Mitra Management (403 Forbidden)
        $response = $this->actingAs($dpl)->get('/mitras');
        $response->assertStatus(403);

        // DPL tetap bisa mengakses Kelompok dan Mahasiswa miliknya
        $response = $this->actingAs($dpl)->get('/groups');
        $response->assertStatus(200);

        $response = $this->actingAs($dpl)->get('/students');
        $response->assertStatus(200);
    }

    public function test_export_and_template_downloads(): void
    {
        $admin = User::where('role', 'admin')->first();

        // Download Template Excel (.xlsx)
        $templateResponse = $this->actingAs($admin)->get('/template-students');
        $templateResponse->assertStatus(200);
        $templateResponse->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('template_import_mahasiswa_ppl.xlsx', (string) $templateResponse->headers->get('content-disposition'));

        // Export Nilai Excel (.xlsx)
        $exportResponse = $this->actingAs($admin)->get('/export-grades');
        $exportResponse->assertStatus(200);
        $exportResponse->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('.xlsx', (string) $exportResponse->headers->get('content-disposition'));
    }
}
