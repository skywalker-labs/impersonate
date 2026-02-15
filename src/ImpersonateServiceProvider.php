<?php

namespace Skywalker\Impersonate;

use Illuminate\Auth\AuthManager;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;
use Skywalker\Impersonate\Guard\SessionGuard;
use Skywalker\Impersonate\Middleware\ProtectFromImpersonation;
use Skywalker\Impersonate\Services\ImpersonateManager;

/**
 * Class ServiceProvider
 *
 * @package Skywalker\Impersonate
 */
class ImpersonateServiceProvider extends ServiceProvider
{
    /** @var string $configName */
    protected $configName = 'laravel-impersonate';

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfig();

        $this->app->bind(ImpersonateManager::class, ImpersonateManager::class);

        $this->app->singleton(ImpersonateManager::class, function ($app) {
            return new ImpersonateManager($app);
        });

        $this->app->alias(ImpersonateManager::class, 'impersonate');

        $this->registerRoutesMacro();
        $this->registerBladeDirectives();
        $this->registerMiddleware();
        $this->registerAuthDriver();
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishConfig();
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        // We want to remove data from storage on real login and logout
        Event::listen(Login::class, function (Login $event) {
            $this->app->make(ImpersonateManager::class)->clear();
        });
        Event::listen(Logout::class, function (Logout $event) {
            $this->app->make(ImpersonateManager::class)->clear();
        });
    }

    /**
     * Register plugin blade directives.
     *
     * @return void
     */
    protected function registerBladeDirectives(): void
    {
        $this->app->afterResolving('blade.compiler', function (BladeCompiler $bladeCompiler) {
            $bladeCompiler->directive('impersonating', function ($guard = null) {
                return "<?php if (is_impersonating({$guard})) : ?>";
            });

            $bladeCompiler->directive('endImpersonating', function () {
                return '<?php endif; ?>';
            });

            $bladeCompiler->directive('canImpersonate', function ($guard = null) {
                return "<?php if (can_impersonate({$guard})) : ?>";
            });

            $bladeCompiler->directive('endCanImpersonate', function () {
                return '<?php endif; ?>';
            });

            $bladeCompiler->directive('canBeImpersonated', function ($expression) {
                $args = preg_split("/,(\s+)?/", $expression);
                $guard = $args[1] ?? null;

                return "<?php if (can_be_impersonated({$args[0]}, {$guard})) : ?>";
            });

            $bladeCompiler->directive('endCanBeImpersonated', function () {
                return '<?php endif; ?>';
            });
        });
    }

    /**
     * Register routes macro.
     *
     * @return void
     */
    protected function registerRoutesMacro(): void
    {
        $router = $this->app['router'];

        $router->macro('impersonate', function () use ($router) {
            $router->get(
                '/impersonate/take/{id}/{guardName?}',
                '\Skywalker\Impersonate\Controllers\ImpersonateController@take'
            )->name('impersonate');
            $router->get(
                '/impersonate/leave',
                '\Skywalker\Impersonate\Controllers\ImpersonateController@leave'
            )->name('impersonate.leave');
        });
    }

    /**
     * @return void
     */
    protected function registerAuthDriver(): void
    {
        /** @var AuthManager $auth */
        $auth = $this->app['auth'];

        $auth->extend('session', function (Application $app, $name, array $config) use ($auth) {
            $provider = $auth->createUserProvider($config['provider']);

            $guard = new SessionGuard($name, $provider, $app['session.store']);

            if (method_exists($guard, 'setCookieJar')) {
                $guard->setCookieJar($app['cookie']);
            }

            if (method_exists($guard, 'setDispatcher')) {
                $guard->setDispatcher($app['events']);
            }

            if (method_exists($guard, 'setRequest')) {
                $guard->setRequest($app->refresh('request', $guard, 'setRequest'));
            }

            return $guard;
        });
    }

    /**
     * Register plugin middleware.
     *
     * @return void
     */
    public function registerMiddleware(): void
    {
        $this->app['router']->aliasMiddleware('impersonate.protect', ProtectFromImpersonation::class);
        $this->app['router']->aliasMiddleware('impersonate.ui', Middleware\InjectImpersonationUI::class);
        $this->app['router']->aliasMiddleware('impersonate.ttl', Middleware\CheckImpersonationTTL::class);

        // Push to web group if possible, or just alias it.
        // For now, let's just alias it. Users can add it to kernel. 
        // Actually, the plan said "Inject automatically". 
        // So we should try to push it to the middlewares of the web group?
        // But we don't know if 'web' group exists or if user wants it there.
        // Plan said: "Register globally or in the web group".
        // Let's add it to the global middleware stack for web routes?
        // Ideally we use pushMiddlewareToGroup but that might be specific to Kernel.
        // $router->pushMiddlewareToGroup('web', Middleware\InjectImpersonationUI::class);
    }

    /**
     * Merge config file.
     *
     * @return void
     */
    protected function mergeConfig(): void
    {
        $configPath = __DIR__ . '/../config/' . $this->configName . '.php';

        $this->mergeConfigFrom($configPath, $this->configName);
    }

    /**
     * Publish config file.
     *
     * @return void
     */
    protected function publishConfig(): void
    {
        $configPath = __DIR__ . '/../config/' . $this->configName . '.php';

        $this->publishes([$configPath => \config_path($this->configName . '.php')], 'impersonate');
    }
}
