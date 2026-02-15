<?php

namespace Skywalker\Tests;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Skywalker\Impersonate\Services\ImpersonateManager;
use Skywalker\Tests\Stubs\Models\User;

class ImpersonateTTLTest extends TestCase
{
    protected $manager;

    public function setUp(): void
    {
        parent::setUp();
        $this->manager = $this->app->make(ImpersonateManager::class);

        // Register a test route that uses the middleware
        Route::get('/test-ttl', function () {
            return 'Impersonation Active';
        })->middleware('web', 'impersonate.ttl');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_sets_started_at_session_key()
    {
        $admin = User::find(1);
        $user = User::find(2);

        $this->actingAs($admin);
        $this->manager->take($admin, $user);

        $this->assertTrue(session()->has($this->manager->getSessionStartedAt()));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_clears_started_at_session_key_on_leave()
    {
        $admin = User::find(1);
        $user = User::find(2);

        $this->actingAs($admin);
        $this->manager->take($admin, $user);
        $this->manager->leave();

        $this->assertFalse(session()->has($this->manager->getSessionStartedAt()));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_does_not_expire_when_ttl_is_disabled()
    {
        $this->app['config']->set('laravel-impersonate.ttl', 0);

        $admin = User::find(1);
        $user = User::find(2);

        $this->actingAs($admin);
        $this->manager->take($admin, $user);

        // Fast forward time by 1 hour
        Carbon::setTestNow(Carbon::now()->addMinutes(60));

        $response = $this->get('/test-ttl');

        $response->assertStatus(200);
        $response->assertSee('Impersonation Active');

        $this->assertTrue($this->manager->isImpersonating());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_does_not_expire_when_time_under_limit()
    {
        $this->app['config']->set('laravel-impersonate.ttl', 30); // 30 minutes

        $admin = User::find(1);
        $user = User::find(2);

        $this->actingAs($admin);
        $this->manager->take($admin, $user);

        // Fast forward time by 29 minutes
        Carbon::setTestNow(Carbon::now()->addMinutes(29));

        $response = $this->get('/test-ttl');

        $response->assertStatus(200);
        $response->assertSee('Impersonation Active');

        $this->assertTrue($this->manager->isImpersonating());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_expires_when_time_over_limit()
    {
        $this->app['config']->set('laravel-impersonate.ttl', 30); // 30 minutes

        $admin = User::find(1);
        $user = User::find(2);

        $this->actingAs($admin);
        $this->manager->take($admin, $user);

        // Fast forward time by 31 minutes
        Carbon::setTestNow(Carbon::now()->addMinutes(31));

        $response = $this->get('/test-ttl');

        $response->assertStatus(302); // Redirect back match leave_redirect_to
        $response->assertSessionHas('flash_message', 'Impersonation session expired.');

        $this->assertFalse($this->manager->isImpersonating());
    }
}
