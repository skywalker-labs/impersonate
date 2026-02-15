<?php

namespace Skywalker\Tests;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Skywalker\Impersonate\Services\ImpersonateManager;
use Skywalker\Tests\Stubs\Models\User;

class ImpersonateAccessControlTest extends TestCase
{
    protected $manager;

    public function setUp(): void
    {
        parent::setUp();
        $this->manager = $this->app->make(ImpersonateManager::class);

        // Register controller route for testing HTTP response
        Route::get('/impersonate/take/{id}/{guardName?}', '\Skywalker\Impersonate\Controllers\ImpersonateController@take')
            ->name('impersonate');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_allows_impersonation_when_gate_is_undefined()
    {
        // No gate defined

        $admin = User::find(1);
        $user = User::find(2);

        $this->actingAs($admin);

        $result = $this->manager->take($admin, $user);

        $this->assertTrue($result);
        $this->assertTrue($this->manager->isImpersonating());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_allows_impersonation_when_gate_allows()
    {
        Gate::define('impersonate', function ($impersonator, $impersonated) {
            return $impersonator->id === 1; // Only ID 1 can impersonate
        });

        $admin = User::find(1);
        $user = User::find(2);

        $this->actingAs($admin);

        $result = $this->manager->take($admin, $user);

        $this->assertTrue($result);
        $this->assertTrue($this->manager->isImpersonating());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_denies_impersonation_when_gate_denies_via_manager()
    {
        Gate::define('impersonate', function ($impersonator, $impersonated) {
            return false; // Always deny
        });

        $admin = User::find(1);
        $user = User::find(2);

        $this->actingAs($admin);

        $result = $this->manager->take($admin, $user);

        $this->assertFalse($result);
        $this->assertFalse($this->manager->isImpersonating());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_denies_impersonation_when_gate_denies_via_controller()
    {
        Gate::define('impersonate', function ($impersonator, $impersonated) {
            return false; // Always deny
        });

        $admin = User::find(1);
        $user = User::find(2);

        $this->actingAs($admin);

        $response = $this->get(route('impersonate', ['id' => $user->id]));

        $response->assertStatus(403);
        $this->assertFalse($this->manager->isImpersonating());
    }
}
