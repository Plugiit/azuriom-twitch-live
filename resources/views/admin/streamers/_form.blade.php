@csrf

<div class="row gx-3">
    <div class="col-md-6 mb-3">
        <label class="form-label" for="loginInput">{{ trans('twitch-live::admin.streamers.login') }}</label>
        <div class="input-group @error('login') has-validation @enderror">
            <span class="input-group-text">twitch.tv/</span>
            <input type="text" class="form-control @error('login') is-invalid @enderror" id="loginInput" name="login" value="{{ old('login', $streamer->login ?? '') }}" pattern="[A-Za-z0-9_]{4,25}" maxlength="25" required>

            @error('login')
            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>
        <div class="form-text">{{ trans('twitch-live::admin.streamers.login_help') }}</div>
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label" for="userInput">{{ trans('twitch-live::admin.streamers.member') }}</label>
        <input type="text" class="form-control @error('user_name') is-invalid @enderror" id="userInput" name="user_name" value="{{ old('user_name', $streamer->user->name ?? '') }}" maxlength="50">
        <div class="form-text">{{ trans('twitch-live::admin.streamers.member_help') }}</div>

        @error('user_name')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>
</div>

<div class="row gx-3">
    <div class="col-md-6 mb-3">
        <label class="form-label" for="statusSelect">{{ trans('twitch-live::admin.streamers.status') }}</label>
        <select class="form-select @error('status') is-invalid @enderror" id="statusSelect" name="status">
            @foreach(\Azuriom\Plugin\TwitchLive\Models\Streamer::STATUSES as $status)
                <option value="{{ $status }}" @selected(old('status', $streamer->status ?? 'approved') === $status)>{{ trans('twitch-live::messages.profile.status.'.$status) }}</option>
            @endforeach
        </select>

        @error('status')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>
</div>

<div class="mb-3">
    <div class="form-check form-switch">
        <input type="checkbox" class="form-check-input" id="featuredSwitch" name="is_featured" @checked(old('is_featured', $streamer->is_featured ?? false))>
        <label class="form-check-label" for="featuredSwitch">{{ trans('twitch-live::admin.streamers.featured') }}</label>
    </div>
    <div class="form-text">{{ trans('twitch-live::admin.streamers.featured_help') }}</div>
</div>
