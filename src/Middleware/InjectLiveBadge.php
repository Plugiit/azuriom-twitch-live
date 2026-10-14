<?php

namespace Azuriom\Plugin\PlugiitTwitchLive\Middleware;

use Azuriom\Plugin\PlugiitTwitchLive\Twitch\LiveStreams;
use Azuriom\Plugin\PlugiitTwitchLive\Twitch\Settings;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

/**
 * Adds a "live" counter next to the navbar links to the Twitch live page.
 */
class InjectLiveBadge
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (! $this->shouldInject($request, $response)) {
            return $response;
        }

        $count = app(LiveStreams::class)->liveCount();

        if ($count < 1) {
            return $response;
        }

        $content = $response->getContent();
        $position = strripos($content, '</body>');

        if ($position === false) {
            return $response;
        }

        $html = view('plugiit-twitch-live::badge', [
            'count' => $count,
            'url' => route('plugiit-twitch-live.index'),
        ])->render();

        $response->setContent(substr_replace($content, $html, $position, 0));

        return $response;
    }

    protected function shouldInject(Request $request, $response): bool
    {
        return $response instanceof Response
            && $request->isMethod('GET')
            && ! $request->ajax()
            && ! $request->expectsJson()
            && ! $request->is('admin', 'admin/*', 'api/*')
            && $response->getStatusCode() === 200
            && str_contains((string) $response->headers->get('Content-Type'), 'text/html')
            && Route::has('plugiit-twitch-live.index')
            && Settings::navbarBadge();
    }
}
