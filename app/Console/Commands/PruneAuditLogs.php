<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('audit:prune {--days=90 : Retensi audit log dalam hari} {--dry-run : Hitung tanpa menghapus data}')]
#[Description('Hapus audit log yang lebih lama dari periode retensi')]
class PruneAuditLogs extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');

        if ($days < 1) {
            $this->error('Opsi --days minimal 1 hari.');

            return self::FAILURE;
        }

        $query = AuditLog::query()->where('created_at', '<', now()->subDays($days));

        if ($this->option('dry-run')) {
            $this->info("{$query->count()} audit log lebih lama dari {$days} hari (dry run).");

            return self::SUCCESS;
        }

        $deleted = $query->delete();

        $this->info("{$deleted} audit log lebih lama dari {$days} hari berhasil dihapus.");

        return self::SUCCESS;
    }
}
