<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PruneAuditLogsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_deletes_audit_logs_older_than_the_retention_period(): void
    {
        $oldLog = $this->createAuditLog(now()->subDays(120));
        $recentLog = $this->createAuditLog(now()->subDays(10));

        $this->artisan('audit:prune', ['--days' => 90])
            ->assertSuccessful();

        $this->assertDatabaseMissing('audit_logs', ['id' => $oldLog->id]);
        $this->assertDatabaseHas('audit_logs', ['id' => $recentLog->id]);
    }

    public function test_dry_run_does_not_delete_any_audit_logs(): void
    {
        $oldLog = $this->createAuditLog(now()->subDays(120));

        $this->artisan('audit:prune', ['--days' => 90, '--dry-run' => true])
            ->assertSuccessful();

        $this->assertDatabaseHas('audit_logs', ['id' => $oldLog->id]);
    }

    public function test_it_rejects_a_retention_period_below_one_day(): void
    {
        $log = $this->createAuditLog(now()->subDay());

        $this->artisan('audit:prune', ['--days' => 0])
            ->assertFailed();

        $this->assertDatabaseHas('audit_logs', ['id' => $log->id]);
    }

    private function createAuditLog(\DateTimeInterface $createdAt): AuditLog
    {
        $log = AuditLog::query()->create([
            'action' => 'test',
        ]);

        $log->forceFill(['created_at' => $createdAt])->save();

        return $log;
    }
}
