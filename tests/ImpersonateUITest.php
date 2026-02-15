<?php

namespace Skywalker\Tests;

use Illuminate\Support\Facades\Route;
use Skywalker\Impersonate\Services\ImpersonateManager;
use Skywalker\Tests\Stubs\Models\User;

class ImpersonateUITest extends TestCase
{
    protected $manager;

    public function setUp(): void
    {
        parent::setUp();
        $this->manager = $this->app->make(ImpersonateManager::class);

        // Register a test route that uses the middleware
        Route::get('/test-ui', function () {
            return '<html><body><h1>Hello World</h1></body></html>';
        })->middleware('web', 'impersonate.ui');

        Route::get('/test-json', function () {
            return response()->json(['hello' => 'world']);
        })->middleware('web', 'impersonate.ui');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_injects_ui_when_enabled_and_impersonating()
    {
        $this->app['config']->set('laravel-impersonate.ui.enabled', true);

        $admin = User::find(1);
        $user = User::find(2);

        $this->actingAs($admin);
        $this->manager->take($admin, $user);

        $response = $this->get('/test-ui');

        $response->assertStatus(200);
        $response->assertSee('You are currently impersonating');
        $response->assertSee($user->name);
        $response->assertSee('Leave Impersonation');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_does_not_inject_ui_when_disabled()
    {
        $this->app['config']->set('laravel-impersonate.ui.enabled', false);

        $admin = User::find(1);
        $user = User::find(2);

        $this->actingAs($admin);
        $this->manager->take($admin, $user);

        $response = $this->get('/test-ui');

        $response->assertStatus(200);
        $response->assertDontSee('You are currently impersonating');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_does_not_inject_ui_when_not_impersonating()
    {
        $this->app['config']->set('laravel-impersonate.ui.enabled', true);

        $admin = User::find(1);
        $this->actingAs($admin);

        $response = $this->get('/test-ui');

        $response->assertStatus(200);
        $response->assertDontSee('You are currently impersonating');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_does_not_inject_ui_on_json_response()
    {
        $this->app['config']->set('laravel-impersonate.ui.enabled', true);

        $admin = User::find(1);
        $user = User::find(2);

        $this->actingAs($admin);
        $this->manager->take($admin, $user);

        $response = $this->get('/test-json');

        $response->assertStatus(200);
        $response->assertDontSee('You are currently impersonating');
    }
}
