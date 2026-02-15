<?php

namespace Skywalker\Impersonate\Middleware;

use Closure;
use Illuminate\Http\Response;
use Skywalker\Impersonate\Services\ImpersonateManager;

class InjectImpersonationUI
{
    /**
     * @var ImpersonateManager
     */
    protected $manager;

    /**
     * InjectImpersonationUI constructor.
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
        $response = $next($request);

        if (! config('laravel-impersonate.ui.enabled', true)) {
            return $response;
        }

        if (! $this->manager->isImpersonating()) {
            return $response;
        }

        if (! $response instanceof Response) {
            return $response;
        }

        if (strpos($response->headers->get('Content-Type'), 'text/html') === false) {
            return $response;
        }

        $content = $response->getContent();
        $ui = $this->renderUI();

        // Inject before </body>
        $pos = strripos($content, '</body>');
        if ($pos !== false) {
            $content = substr($content, 0, $pos) . $ui . substr($content, $pos);
            $response->setContent($content);
        }

        return $response;
    }

    /**
     * Render the UI html.
     *
     * @return string
     */
    protected function renderUI()
    {
        $impersonator = $this->manager->getImpersonator();
        $impersonated = auth()->user();
        $name = $impersonated ? $impersonated->name : 'User';
        $leaveUrl = route('impersonate.leave');

        $position = config('laravel-impersonate.ui.position', 'bottom');
        $colors = config('laravel-impersonate.ui.colors', ['background' => '#1f2937', 'text' => '#f3f4f6']);

        $style = "position: fixed; {$position}: 0; left: 0; right: 0; background-color: {$colors['background']}; color: {$colors['text']}; padding: 10px; text-align: center; z-index: 99999; font-family: sans-serif; box-shadow: 0 0 10px rgba(0,0,0,0.1);";

        return <<<HTML
<div style="{$style}">
    You are currently impersonating <strong>{$name}</strong>.
    <a href="{$leaveUrl}" style="color: inherit; text-decoration: underline; margin-left: 10px;">Leave Impersonation</a>
</div>
HTML;
    }
}
