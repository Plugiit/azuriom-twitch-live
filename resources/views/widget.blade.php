{{--
    "Live now" widget, to include in any theme view:
        @include('plugiit-twitch-live::widget')
        @include('plugiit-twitch-live::widget', ['limit' => 3, 'hideWhenEmpty' => true])
--}}
@php
    $ptlState = app(\Azuriom\Plugin\PlugiitTwitchLive\Twitch\LiveStreams::class)->state();
    $ptlStreams = array_slice($ptlState['streams'], 0, (int) ($limit ?? 5));
@endphp

@if($ptlStreams !== [] || ! ($hideWhenEmpty ?? false))
    @once
        @push('styles')
            <link href="{{ plugin_asset('plugiit-twitch-live', 'css/twitch-live.css') }}" rel="stylesheet">
        @endpush
    @endonce

    <div class="card plugiit-twitch-live-widget mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            <span class="fw-semibold"><i class="bi bi-twitch" style="color: #9146ff"></i> {{ trans('plugiit-twitch-live::messages.widget.title') }}</span>
            @if(count($ptlState['streams']) > 0)
                <span class="badge rounded-pill" style="background: #eb0400">{{ count($ptlState['streams']) }}</span>
            @endif
        </div>
        <ul class="list-group list-group-flush">
            @forelse($ptlStreams as $stream)
                <li class="list-group-item">
                    <a href="https://www.twitch.tv/{{ $stream['login'] }}" target="_blank" rel="noopener noreferrer" class="d-flex align-items-center gap-2 text-reset">
                        @include('plugiit-twitch-live::_avatar', ['avatar' => $stream['avatar'], 'name' => $stream['name'], 'size' => 32])
                        <span class="min-w-0 flex-grow-1">
                            <span class="d-block fw-semibold text-truncate">{{ $stream['name'] }}</span>
                            <span class="d-block small text-body-secondary text-truncate" title="{{ $stream['title'] }}">{{ $stream['title'] }}</span>
                        </span>
                        <span class="small text-nowrap"><span class="ptl-dot"></span> {{ number_format($stream['viewers'], 0, ',', ' ') }}</span>
                    </a>
                </li>
            @empty
                <li class="list-group-item small text-body-secondary">{{ trans('plugiit-twitch-live::messages.widget.none') }}</li>
            @endforelse
        </ul>
        <div class="card-footer text-end">
            <a href="{{ route('plugiit-twitch-live.index') }}" class="small">{{ trans('plugiit-twitch-live::messages.widget.more') }} <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
@endif
