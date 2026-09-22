<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_student_create_page(): void
    {
        $this->seed();
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get('/students/create');
        $response->assertStatus(200);
        $response->assertSee('NIM Mahasiswa');
    }

    public function test_dpl_can_access_student_create_page(): void
    {
        $this->seed();
        $dpl = User::where('role', 'dpl')->first();

        $response = $this->actingAs($dpl)->get('/students/create');
        $response->assertStatus(200);
        $response->assertSee('NIM Mahasiswa');
    }
}
