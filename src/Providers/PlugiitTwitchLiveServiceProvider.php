<?php

namespace Azuriom\Plugin\PlugiitTwitchLive\Providers;

use Azuriom\Extensions\Plugin\BasePluginServiceProvider;
use Azuriom\Models\ActionLog;
use Azuriom\Models\Permission;
use Azuriom\Plugin\PlugiitTwitchLive\Commands\RefreshLiveCommand;
use Azuriom\Plugin\PlugiitTwitchLive\Middleware\InjectLiveBadge;
use Azuriom\Plugin\PlugiitTwitchLive\Models\Streamer;
use Azuriom\Plugin\PlugiitTwitchLive\Twitch\LiveStreams;
use Azuriom\Plugin\PlugiitTwitchLive\View\ProfileCardComposer;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\View;

class PlugiitTwitchLiveServiceProvider extends BasePluginServiceProvider
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
            'plugiit-twitch-live.admin' => 'plugiit-twitch-live::admin.permission',
        ]);

        ActionLog::registerLogModels([
            Streamer::class,
        ], 'plugiit-twitch-live::admin.logs');

        ActionLog::registerLogs('plugiit-twitch-live.settings.updated', [
            'icon' => 'twitch',
            'color' => 'info',
            'message' => 'plugiit-twitch-live::admin.logs.settings',
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
        $schedule->command('plugiit-twitch-live:refresh')
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
            'plugiit-twitch-live.index' => trans('plugiit-twitch-live::messages.title'),
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
            'plugiit-twitch-live' => [
                'name' => trans('plugiit-twitch-live::admin.nav.title'),
                'type' => 'dropdown',
                'icon' => 'bi bi-twitch',
                'route' => 'plugiit-twitch-live.admin.*',
                'permission' => 'plugiit-twitch-live.admin',
                'items' => [
                    'plugiit-twitch-live.admin.streamers.index' => trans('plugiit-twitch-live::admin.nav.streamers'),
                    'plugiit-twitch-live.admin.settings' => trans('plugiit-twitch-live::admin.nav.settings'),
                ],
            ],
        ];
    }
}
