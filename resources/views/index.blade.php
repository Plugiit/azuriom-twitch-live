@extends('layouts.app')

@section('title', trans('plugiit-twitch-live::messages.title'))

@push('styles')
    <link href="{{ plugin_asset('plugiit-twitch-live', 'css/twitch-live.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="d-flex flex-wrap align-items-end justify-content-between gap-2 mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-twitch" style="color: #9146ff"></i> {{ trans('plugiit-twitch-live::messages.title') }}</h1>
            <p class="text-body-secondary mb-0">{{ trans('plugiit-twitch-live::messages.subtitle') }}</p>
        </div>
        @if($state['updated_at'])
            <span class="small text-body-secondary">
                <i class="bi bi-arrow-repeat"></i>
                {{ trans('plugiit-twitch-live::messages.updated', ['time' => \Carbon\Carbon::parse($state['updated_at'])->diffForHumans()]) }}
            </span>
        @endif
    </div>

    @if($state['demo'])
        <div class="alert alert-info" role="status"><i class="bi bi-cone-striped"></i> {{ trans('plugiit-twitch-live::messages.demo') }}</div>
    @endif

    @if($state['stale'])
        <div class="alert alert-warning" role="status"><i class="bi bi-exclamation-triangle"></i> {{ trans('plugiit-twitch-live::messages.stale') }}</div>
    @endif

    @if($featured !== null)
        <div class="card mb-4 ptl-featured">
            <div class="row g-0">
                <div class="col-lg-8">
                    @if($state['demo'])
                        <div class="ratio ratio-16x9 ptl-thumb rounded-start">
                            <div>
                                @if($featured['thumbnail'])
                                    <img src="{{ $featured['thumbnail'] }}" alt="">
                                @endif
                                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center p-4" style="background: rgba(0,0,0,.55)">
                                    <p class="text-white text-center mb-0"><i class="bi bi-play-circle fs-1 d-block mb-2"></i>{{ trans('plugiit-twitch-live::messages.demo_player', ['name' => $featured['name']]) }}</p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="ratio ratio-16x9">
                            <iframe src="https://player.twitch.tv/?channel={{ urlencode($featured['login']) }}&parent={{ urlencode($parent) }}&muted=true&autoplay=true"
                                    title="{{ $featured['name'] }} – Twitch" allowfullscreen loading="lazy"
                                    class="rounded-start border-0"></iframe>
                        </div>
                    @endif
                </div>
                <div class="col-lg-4">
                    <div class="card-body h-100 d-flex flex-column">
                        <span class="badge text-bg-warning align-self-start mb-2"><i class="bi bi-star-fill"></i> {{ trans('plugiit-twitch-live::messages.featured') }}</span>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            @include('plugiit-twitch-live::_avatar', ['avatar' => $featured['avatar'], 'name' => $featured['name'], 'size' => 56])
                            <div>
                                <h2 class="h4 mb-0">{{ $featured['name'] }}</h2>
                                @if($featured['member'])
                                    <div class="small text-body-secondary">{{ trans('plugiit-twitch-live::messages.member', ['name' => $featured['member']]) }}</div>
                                @endif
                            </div>
                        </div>
                        <p class="mb-2">{{ $featured['title'] }}</p>
                        <ul class="list-unstyled small text-body-secondary mb-3">
                            <li><span class="ptl-dot me-1"></span> {{ trans_choice('plugiit-twitch-live::messages.viewers', $featured['viewers'], ['count' => number_format($featured['viewers'], 0, ',', ' ')]) }}</li>
                            @if($featured['game_name'] !== '')
                                <li><i class="bi bi-controller"></i> {{ $featured['game_name'] }}</li>
                            @endif
                            @if($duration = \Azuriom\Plugin\PlugiitTwitchLive\Twitch\LiveStreams::duration($featured['started_at']))
                                <li><i class="bi bi-clock"></i> {{ trans('plugiit-twitch-live::messages.since', ['duration' => $duration]) }}</li>
                            @endif
                        </ul>
                        <a href="https://www.twitch.tv/{{ $featured['login'] }}" class="btn btn-primary mt-auto" target="_blank" rel="noopener noreferrer" style="background: #9146ff; border-color: #9146ff">
                            <i class="bi bi-twitch"></i> {{ trans('plugiit-twitch-live::messages.watch') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @php($others = $featured !== null ? $streams->reject(fn ($stream) => $stream['login'] === $featured['login']) : $streams)

    @if($streams->isEmpty())
        <div class="alert alert-secondary" role="status">
            <i class="bi bi-camera-video-off"></i>
            {{ $state['configured'] ? trans('plugiit-twitch-live::messages.none') : trans('plugiit-twitch-live::messages.not_configured') }}
        </div>
    @else
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-3 mb-4">
            @foreach($others as $stream)
                <div class="col">
                    @include('plugiit-twitch-live::_stream-card', ['stream' => $stream])
                </div>
            @endforeach
        </div>
    @endif

    @if($offline->isNotEmpty())
        <h2 class="h5 mt-4 mb-3">{{ trans('plugiit-twitch-live::messages.offline_title') }}</h2>
        <div class="d-flex flex-wrap gap-2">
            @foreach($offline as $streamer)
                <a href="{{ $streamer->channelUrl() }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-2" target="_blank" rel="noopener noreferrer"
                   title="{{ trans('plugiit-twitch-live::messages.offline') }}">
                    @include('plugiit-twitch-live::_avatar', ['avatar' => $streamer->avatar_url ?? $streamer->user?->getAvatar(), 'name' => $streamer->displayName(), 'size' => 24])
                    {{ $streamer->displayName() }}
                </a>
            @endforeach
        </div>
    @endif
@endsection
