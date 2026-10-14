<p class="card-text">{{ trans('plugiit-twitch-live::messages.profile.info') }}</p>

@if($streamer !== null)
    @php
        $statusColor = ['approved' => 'success', 'pending' => 'warning', 'rejected' => 'danger'][$streamer->status] ?? 'secondary';
    @endphp
    <p>
        <i class="bi bi-twitch" style="color: #9146ff"></i>
        <a href="{{ $streamer->channelUrl() }}" target="_blank" rel="noopener noreferrer">twitch.tv/{{ $streamer->login }}</a>
        <span class="badge text-bg-{{ $statusColor }}">{{ trans('plugiit-twitch-live::messages.profile.status.'.$streamer->status) }}</span>
    </p>
@endif

<form action="{{ route('plugiit-twitch-live.channel.store') }}" method="POST">
    @csrf

    <div class="mb-3">
        <label class="form-label" for="twitchLoginInput">{{ trans('plugiit-twitch-live::messages.profile.login') }}</label>
        <div class="input-group @error('login') has-validation @enderror">
            <span class="input-group-text">twitch.tv/</span>
            <input type="text" class="form-control @error('login') is-invalid @enderror" id="twitchLoginInput" name="login"
                   value="{{ old('login', $streamer?->login) }}" pattern="[A-Za-z0-9_]{4,25}" maxlength="25" required>

            @error('login')
            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>
        <div class="form-text">{{ trans('plugiit-twitch-live::messages.profile.login_help') }}</div>
    </div>

    <button type="submit" class="btn btn-primary">
        <i class="bi bi-send"></i> {{ $streamer !== null ? trans('plugiit-twitch-live::messages.profile.update') : trans('plugiit-twitch-live::messages.profile.submit') }}
    </button>
</form>

@if($streamer !== null)
    <form action="{{ route('plugiit-twitch-live.channel.destroy') }}" method="POST" class="mt-2">
        @csrf
        @method('DELETE')

        <button type="submit" class="btn btn-link link-danger p-0">
            <i class="bi bi-x-circle"></i> {{ trans('plugiit-twitch-live::messages.profile.remove') }}
        </button>
    </form>
@endif
