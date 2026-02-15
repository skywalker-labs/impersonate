<?php

namespace Skywalker\Impersonate\Middleware;

use Closure;
use Illuminate\Support\Facades\Redirect;
use Skywalker\Impersonate\Services\ImpersonateManager;

class CheckImpersonationTTL
{
    /**
     * @var ImpersonateManager
     */
    protected $manager;

    /**
     * CheckImpersonationTTL constructor.
     *
     * @param ImpersonateManager $manager
     */
    public function __construct(ImpersonateManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $ttl = config('laravel-impersonate.ttl');

        if (empty($ttl) || $ttl <= 0) {
            return $next($request);
        }

        if (! $this->manager->isImpersonating()) {
            return $next($request);
        }

        $startedAt = session($this->manager->getSessionStartedAt());

        if (! $startedAt) {
            // Should not happen if isImpersonating is true, but safety check.
            return $next($request);
        }

        $secondsDiff = now()->timestamp - $startedAt;
        $minutesDiff = $secondsDiff / 60;

        if ($minutesDiff > $ttl) {
            $this->manager->leave();

            return Redirect::to($this->manager->getLeaveRedirectTo())
                ->with('flash_message', 'Impersonation session expired.');
        }

        return $next($request);
    }
}
