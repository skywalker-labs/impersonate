<?php

namespace Skywalker\Tests;

use Illuminate\Http\Request;
use Skywalker\Tests\Stubs\Models\User;
use Skywalker\Impersonate\Middleware\ProtectFromImpersonation;

class MiddlewareProtectFromImpersonationTest extends TestCase
{
    /** @var User $user */
    protected $user;
    /** @var User $admin */
    protected $admin;
    /** @var Request $request */
    protected $request;
    /** @var ProtectFromImpersonation $middleware */
    protected $middleware;

    public function setUp() : void
    {
        parent::setUp();

        $this->user = User::find(2);
        $this->admin = User::find(1);
        $this->request = new Request();
        $this->middleware = new ProtectFromImpersonation;
    }

    /**
     * @param   void
     * @return  void
     */
    protected function logout()
    {
        $this->app['auth']->logout();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_acces_when_no_impersonating()
    {
        $this->actingAs($this->user);
        $return = $this->middleware->handle($this->request, function () {
            return 'This is private';
        });

        $this->assertEquals('This is private', $return);

        $this->logout();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_cant_acces_when_impersonating()
    {
        $this->actingAs($this->admin);
        $this->admin->impersonate($this->user);

        $return = $this->middleware->handle($this->request, function () {
            return 'This is private';
        });

        $this->assertNotEquals('This is private', $return);
        $this->logout();
    }
}
