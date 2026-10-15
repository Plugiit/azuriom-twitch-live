@extends('admin.layouts.admin')

@section('title', trans('twitch-live::admin.settings.title'))

@section('content')
    @if($demoMode)
        <div class="alert alert-warning"><i class="bi bi-cone-striped"></i> {{ trans('twitch-live::admin.settings.demo_enabled') }}</div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header">
            <h2 class="card-title h6 mb-0"><i class="bi bi-activity"></i> {{ trans('twitch-live::admin.status.title') }}</h2>
        </div>
        <div class="card-body">
            @if(! $demoMode && ($clientId === null || ! $hasSecret))
                <div class="alert alert-info mb-3">{{ trans('twitch-live::admin.status.not_configured') }}</div>
            @endif

            <dl class="row mb-2">
                <dt class="col-sm-4">{{ trans('twitch-live::admin.status.live') }}</dt>
                <dd class="col-sm-8">{{ $state !== null ? count($state['streams']) : '—' }}</dd>
                <dt class="col-sm-4">{{ trans('twitch-live::admin.status.updated') }}</dt>
                <dd class="col-sm-8">{{ $state !== null && $state['updated_at'] ? format_date(\Carbon\Carbon::parse($state['updated_at']), true) : trans('twitch-live::admin.status.never') }}</dd>
                @if($state['error'] ?? null)
                    <dt class="col-sm-4 text-danger">{{ trans('twitch-live::admin.status.error') }}</dt>
                    <dd class="col-sm-8 text-danger">{{ $state['error'] }}</dd>
                @endif
            </dl>

            <p class="small text-muted">{{ trans('twitch-live::admin.status.scheduler') }}</p>

            <form action="{{ route('twitch-live.admin.refresh') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-primary"><i class="bi bi-arrow-repeat"></i> {{ trans('twitch-live::admin.refresh.button') }}</button>
            </form>
        </div>
    </div>

    <form action="{{ route('twitch-live.admin.settings.save') }}" method="POST">
        @csrf

        <div class="card shadow mb-4">
            <div class="card-header">
                <h2 class="card-title h6 mb-0"><i class="bi bi-key"></i> {{ trans('twitch-live::admin.settings.credentials') }}</h2>
            </div>
            <div class="card-body">
                <p class="text-muted">{{ trans('twitch-live::admin.settings.credentials_info') }}</p>

                <div class="row gx-3">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="clientIdInput">{{ trans('twitch-live::admin.settings.client_id') }}</label>
                        <input type="text" class="form-control @error('client_id') is-invalid @enderror" id="clientIdInput" name="client_id" value="{{ old('client_id', $clientId) }}" maxlength="100" autocomplete="off">

                        @error('client_id')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="clientSecretInput">{{ trans('twitch-live::admin.settings.client_secret') }}</label>
                        <input type="password" class="form-control @error('client_secret') is-invalid @enderror" id="clientSecretInput" name="client_secret" maxlength="100" autocomplete="new-password"
                               placeholder="{{ $hasSecret ? '••••••••••••' : '' }}">
                        <div class="form-text">{{ $hasSecret ? trans('twitch-live::admin.settings.secret_saved') : trans('twitch-live::admin.settings.secret_empty') }}</div>

                        @error('client_secret')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror

                        @if($hasSecret)
                            <div class="form-check mt-1">
                                <input type="checkbox" class="form-check-input" id="removeSecret" name="remove_secret">
                                <label class="form-check-label" for="removeSecret">{{ trans('twitch-live::admin.settings.remove_secret') }}</label>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="form-check form-switch">
                    <input type="checkbox" class="form-check-input" id="demoSwitch" name="demo_mode" @checked(old('demo_mode', $demoMode))>
                    <label class="form-check-label" for="demoSwitch">{{ trans('twitch-live::admin.settings.demo_mode') }}</label>
                </div>
                <div class="form-text">{{ trans('twitch-live::admin.settings.demo_mode_help') }}</div>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header">
                <h2 class="card-title h6 mb-0"><i class="bi bi-funnel"></i> {{ trans('twitch-live::admin.settings.filters') }}</h2>
            </div>
            <div class="card-body">
                <div class="mb-3 form-check form-switch">
                    <input type="checkbox" class="form-check-input" id="minecraftSwitch" name="only_minecraft" @checked(old('only_minecraft', $onlyMinecraft))>
                    <label class="form-check-label" for="minecraftSwitch">{{ trans('twitch-live::admin.settings.only_minecraft') }}</label>
                </div>

                <div class="mb-0">
                    <label class="form-label" for="keywordInput">{{ trans('twitch-live::admin.settings.title_keyword') }}</label>
                    <input type="text" class="form-control @error('title_keyword') is-invalid @enderror" id="keywordInput" name="title_keyword" value="{{ old('title_keyword', $titleKeyword) }}" maxlength="50">
                    <div class="form-text">{{ trans('twitch-live::admin.settings.title_keyword_help') }}</div>

                    @error('title_keyword')
                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header">
                <h2 class="card-title h6 mb-0"><i class="bi bi-display"></i> {{ trans('twitch-live::admin.settings.display') }}</h2>
            </div>
            <div class="card-body">
                <div class="mb-3 col-md-6">
                    <label class="form-label" for="cacheInput">{{ trans('twitch-live::admin.settings.cache_minutes') }}</label>
                    <div class="input-group @error('cache_minutes') has-validation @enderror">
                        <input type="number" min="1" max="60" class="form-control @error('cache_minutes') is-invalid @enderror" id="cacheInput" name="cache_minutes" value="{{ old('cache_minutes', $cacheMinutes) }}" required>
                        <span class="input-group-text">{{ trans('twitch-live::admin.settings.minutes') }}</span>

                        @error('cache_minutes')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                    <div class="form-text">{{ trans('twitch-live::admin.settings.cache_minutes_help') }}</div>
                </div>

                <div class="mb-3 form-check form-switch">
                    <input type="checkbox" class="form-check-input" id="badgeSwitch" name="navbar_badge" @checked(old('navbar_badge', $navbarBadge))>
                    <label class="form-check-label" for="badgeSwitch">{{ trans('twitch-live::admin.settings.navbar_badge') }}</label>
                </div>

                <div class="form-check form-switch">
                    <input type="checkbox" class="form-check-input" id="submissionsSwitch" name="player_submissions" @checked(old('player_submissions', $playerSubmissions))>
                    <label class="form-check-label" for="submissionsSwitch">{{ trans('twitch-live::admin.settings.player_submissions') }}</label>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary mb-4">
            <i class="bi bi-save"></i> {{ trans('messages.actions.save') }}
        </button>
    </form>

    <div class="card shadow mb-4">
        <div class="card-body">
            <h2 class="h6">{{ trans('twitch-live::admin.settings.widget') }}</h2>
            <p class="mb-2">{{ trans('twitch-live::admin.settings.widget_info') }}</p>
            <pre class="mb-0"><code>@@include('twitch-live::widget')
@@include('twitch-live::widget', ['limit' => 3, 'hideWhenEmpty' => true])</code></pre>
        </div>
    </div>
@endsection
