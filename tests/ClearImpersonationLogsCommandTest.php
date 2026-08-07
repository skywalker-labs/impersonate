<?php

namespace Skywalker\Tests;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use Skywalker\Impersonate\Models\ImpersonationLog;
use Skywalker\Tests\Stubs\Models\User;

class ClearImpersonationLogsCommandTest extends TestCase
{
    #[Test]
    public function it_can_clear_old_logs()
    {
        Config::set('laravel-impersonate.logging', true);

        // Create an old log (e.g. 40 days old)
        $oldDate = Carbon::now()->subDays(40);
        
        // Temporarily change now() to simulate past creation
        Carbon::setTestNow($oldDate);
        $oldLog = ImpersonationLog::create([
            'impersonator_id' => 1,
            'impersonated_id' => 2,
        ]);
        Carbon::setTestNow(); // reset

        // Create a new log (e.g. today)
        $newLog = ImpersonationLog::create([
            'impersonator_id' => 1,
            'impersonated_id' => 3,
        ]);

        $this->assertDatabaseCount('impersonation_logs', 2);

        // Run the command to clear logs older than 30 days
        $this->artisan('impersonate:clear-logs', ['--days' => 30])
             ->assertExitCode(0);

        // Assert only the new log remains
        $this->assertDatabaseCount('impersonation_logs', 1);
        $this->assertDatabaseHas('impersonation_logs', [
            'impersonated_id' => 3,
        ]);
    }
}
