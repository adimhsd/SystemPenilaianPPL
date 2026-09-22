<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_export_users_excel(): void
    {
        $this->seed();
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get('/export-users');
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('daftar_akun_dpl_dan_user_ppl_', (string) $response->headers->get('content-disposition'));
        $this->assertStringContainsString('.xlsx', (string) $response->headers->get('content-disposition'));
    }

    public function test_dpl_cannot_export_users_excel(): void
    {
        $this->seed();
        $dpl = User::where('role', 'dpl')->first();

        $response = $this->actingAs($dpl)->get('/export-users');
        $response->assertStatus(403);
    }
}
