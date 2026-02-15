<?php

namespace Skywalker\Tests;

use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use Skywalker\Impersonate\Models\ImpersonationLog;
use Skywalker\Tests\Stubs\Models\User;

class ImpersonationLoggingTest extends TestCase
{
    #[Test]
    public function it_can_log_impersonation_when_enabled()
    {
        Config::set('laravel-impersonate.logging', true);

        $admin = User::create(['name' => 'Admin', 'email' => 'admin@test.com', 'password' => 'password', 'is_admin' => 1]);
        $user = User::create(['name' => 'User', 'email' => 'user@test.com', 'password' => 'password', 'can_be_impersonated' => 1]);

        $this->actingAs($admin);
        $this->get('/impersonate/take/' . $user->id . '/web');

        $this->assertDatabaseHas('impersonation_logs', [
            'impersonator_id' => $admin->id,
            'impersonated_id' => $user->id,
        ]);
    }

    #[Test]
    public function it_does_not_log_impersonation_when_disabled()
    {
        Config::set('laravel-impersonate.logging', false);

        $admin = User::create(['name' => 'Admin', 'email' => 'admin@test.com', 'password' => 'password', 'is_admin' => 1]);
        $user = User::create(['name' => 'User', 'email' => 'user@test.com', 'password' => 'password', 'can_be_impersonated' => 1]);

        $this->actingAs($admin);
        $this->get('/impersonate/take/' . $user->id);

        $this->assertDatabaseMissing('impersonation_logs', [
            'impersonator_id' => $admin->id,
            'impersonated_id' => $user->id,
        ]);
    }
}
