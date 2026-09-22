<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\DatabaseBackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class DatabaseBackupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_admin_can_access_backup_page(): void
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get('/backup-database');
        $response->assertStatus(200);
    }

    public function test_dpl_cannot_access_backup_page(): void
    {
        $dpl = User::where('role', 'dpl')->first();

        $response = $this->actingAs($dpl)->get('/backup-database');
        $response->assertStatus(403);
    }

    public function test_service_can_create_and_list_sql_backup(): void
    {
        $result = DatabaseBackupService::createBackup('sql');
        $this->assertTrue($result['success']);
        $this->assertFileExists($result['path']);

        $content = File::get($result['path']);
        $this->assertStringContainsString('CREATE TABLE `users`', $content);
        $this->assertStringContainsString('CREATE TABLE `students`', $content);
        $this->assertStringContainsString('CREATE TABLE `groups`', $content);
        $this->assertStringContainsString('CREATE TABLE `mitras`', $content);

        // Test list backups
        $list = DatabaseBackupService::listBackups();
        $this->assertNotEmpty($list);
        $this->assertEquals('SQL', $list[0]['format']);

        // Clean up
        DatabaseBackupService::deleteBackup($result['filename']);
    }

    public function test_admin_can_download_backup_and_dpl_is_forbidden(): void
    {
        $admin = User::where('role', 'admin')->first();
        $dpl = User::where('role', 'dpl')->first();

        $result = DatabaseBackupService::createBackup('sql');
        $filename = $result['filename'];

        // Admin download test
        $response = $this->actingAs($admin)->get("/backup-download/{$filename}");
        $response->assertStatus(200);

        // DPL download test (Forbidden)
        $response = $this->actingAs($dpl)->get("/backup-download/{$filename}");
        $response->assertStatus(403);

        // Clean up
        DatabaseBackupService::deleteBackup($filename);
    }
}
