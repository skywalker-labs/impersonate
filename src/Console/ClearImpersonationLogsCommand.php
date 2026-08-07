<?php

namespace Skywalker\Impersonate\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;

class ClearImpersonationLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'impersonate:clear-logs {--days=30 : The number of days to retain logs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear old impersonation logs from the database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (!Config::get('laravel-impersonate.logging', false)) {
            $this->info('Impersonation logging is disabled in configuration.');
            return 0;
        }

        $days = $this->option('days');
        $date = Carbon::now()->subDays($days);

        $logModelClass = Config::get('laravel-impersonate.log_model', \Skywalker\Impersonate\Models\ImpersonationLog::class);
        $logModel = new $logModelClass;

        $deleted = $logModel->where('created_at', '<', $date)->delete();

        $this->info("Deleted {$deleted} impersonation logs older than {$days} days.");

        return 0;
    }
}
