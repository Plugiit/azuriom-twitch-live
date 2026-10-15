<?php

namespace Azuriom\Plugin\TwitchLive\Commands;

use Azuriom\Plugin\TwitchLive\Twitch\LiveStreams;
use Illuminate\Console\Command;

class RefreshLiveCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twitch-live:refresh {--force : Refresh even if the cache is still valid}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh the community streamers live on Twitch';

    /**
     * Execute the console command.
     */
    public function handle(LiveStreams $liveStreams): int
    {
        $state = $this->option('force') ? $liveStreams->refresh() : $liveStreams->refreshIfStale();

        if ($state['error'] !== null) {
            $this->warn('Twitch error, last known state kept: '.$state['error']);
        }

        $this->info(sprintf(
            '%d stream(s) live%s, updated at %s.',
            count($state['streams']),
            $state['demo'] ? ' (demo mode)' : '',
            $state['updated_at'],
        ));

        return $state['error'] === null ? self::SUCCESS : self::FAILURE;
    }
}
