<?php

namespace Azuriom\Plugin\TwitchLive\Providers;

use Azuriom\Extensions\Plugin\BasePluginServiceProvider;
use Azuriom\Models\ActionLog;
use Azuriom\Models\Permission;
use Azuriom\Plugin\TwitchLive\Commands\RefreshLiveCommand;
use Azuriom\Plugin\TwitchLive\Middleware\InjectLiveBadge;
use Azuriom\Plugin\TwitchLive\Models\Streamer;
use Azuriom\Plugin\TwitchLive\Twitch\LiveStreams;
use Azuriom\Plugin\TwitchLive\View\ProfileCardComposer;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\View;

class TwitchLiveServiceProvider extends BasePluginServiceProvider
{
    /**
     * The plugin's global HTTP middleware stack.
     */
    protected array $middleware = [
        InjectLiveBadge::class,
    ];

    /**
     * Register any plugin services.
     */
    public function register(): void
    {
        $this->registerMiddleware();

        $this->app->singleton(LiveStreams::class);
    }

    /**
     * Bootstrap any plugin services.
     */
    public function boot(): void
    {
        $this->loadViews();

        $this->loadTranslations();

        $this->loadMigrations();

        $this->registerRouteDescriptions();

        $this->registerAdminNavigation();

        Permission::registerPermissions([
            'twitch-live.admin' => 'twitch-live::admin.permission',
        ]);

        ActionLog::registerLogModels([
            Streamer::class,
        ], 'twitch-live::admin.logs');

        ActionLog::registerLogs('twitch-live.settings.updated', [
            'icon' => 'twitch',
            'color' => 'info',
            'message' => 'twitch-live::admin.logs.settings',
        ]);

        View::composer('profile.index', ProfileCardComposer::class);

        $this->registerSchedule();

        $this->commands(RefreshLiveCommand::class);
    }

    /**
     * Define the plugin's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // The command only calls Twitch when the cached state has expired.
        $schedule->command('twitch-live:refresh')
            ->everyMinute()
            ->withoutOverlapping()
            ->runInBackground();
    }

    /**
     * Returns the routes that should be able to be added to the navbar.
     *
     * @return array<string, string>
     */
    protected function routeDescriptions(): array
    {
        return [
            'twitch-live.index' => trans('twitch-live::messages.title'),
        ];
    }

    /**
     * Return the admin navigations routes to register in the dashboard.
     *
     * @return array<string, array<string, string>>
     */
    protected function adminNavigation(): array
    {
        return [
            'twitch-live' => [
                'name' => trans('twitch-live::admin.nav.title'),
                'type' => 'dropdown',
                'icon' => 'bi bi-twitch',
                'route' => 'twitch-live.admin.*',
                'permission' => 'twitch-live.admin',
                'items' => [
                    'twitch-live.admin.streamers.index' => trans('twitch-live::admin.nav.streamers'),
                    'twitch-live.admin.settings' => trans('twitch-live::admin.nav.settings'),
                ],
            ],
        ];
    }
}
