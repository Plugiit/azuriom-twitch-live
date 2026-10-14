@extends('admin.layouts.admin')

@section('title', trans('plugiit-twitch-live::admin.streamers.title'))

@section('content')
    @if(setting('plugiit-twitch-live.demo_mode'))
        <div class="alert alert-warning"><i class="bi bi-cone-striped"></i> {{ trans('plugiit-twitch-live::admin.settings.demo_enabled') }}</div>
    @endif

    @if($pending->isNotEmpty())
        <div class="card shadow mb-4 border-warning">
            <div class="card-header">
                <h2 class="card-title h6 mb-0"><i class="bi bi-hourglass-split"></i> {{ trans('plugiit-twitch-live::admin.streamers.pending') }} <span class="badge bg-warning text-dark">{{ $pending->count() }}</span></h2>
            </div>
            <div class="card-body">
                <p class="text-muted">{{ trans('plugiit-twitch-live::admin.streamers.pending_info') }}</p>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                        <tr>
                            <th scope="col">{{ trans('plugiit-twitch-live::admin.streamers.login') }}</th>
                            <th scope="col">{{ trans('plugiit-twitch-live::admin.streamers.member') }}</th>
                            <th scope="col">{{ trans('plugiit-twitch-live::admin.streamers.declared') }}</th>
                            <th scope="col">{{ trans('messages.fields.action') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($pending as $streamer)
                            <tr>
                                <td><a href="{{ $streamer->channelUrl() }}" target="_blank" rel="noopener noreferrer">{{ $streamer->login }}</a></td>
                                <td>
                                    @if($streamer->user)
                                        <a href="{{ route('admin.users.edit', $streamer->user) }}">{{ $streamer->user->name }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ format_date_compact($streamer->created_at) }}</td>
                                <td class="text-nowrap">
                                    <form action="{{ route('plugiit-twitch-live.admin.streamers.approve', $streamer) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-check-lg"></i> {{ trans('plugiit-twitch-live::admin.streamers.approve') }}</button>
                                    </form>
                                    <form action="{{ route('plugiit-twitch-live.admin.streamers.reject', $streamer) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-x-lg"></i> {{ trans('plugiit-twitch-live::admin.streamers.reject') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-body">
            @if($streamers->isEmpty())
                <p class="text-muted">{{ trans('plugiit-twitch-live::admin.streamers.empty') }}</p>
            @else
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                        <tr>
                            <th scope="col">{{ trans('plugiit-twitch-live::admin.streamers.login') }}</th>
                            <th scope="col">{{ trans('plugiit-twitch-live::admin.streamers.member') }}</th>
                            <th scope="col">{{ trans('plugiit-twitch-live::admin.streamers.featured') }}</th>
                            <th scope="col">{{ trans('plugiit-twitch-live::admin.streamers.status') }}</th>
                            <th scope="col">{{ trans('messages.fields.action') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($streamers as $streamer)
                            <tr>
                                <td>
                                    @if($streamer->avatar_url)
                                        <img src="{{ $streamer->avatar_url }}" alt="" width="28" height="28" class="rounded-circle me-1">
                                    @endif
                                    <a href="{{ $streamer->channelUrl() }}" target="_blank" rel="noopener noreferrer">{{ $streamer->displayName() }}</a>
                                    @if($liveLogins->contains($streamer->login))
                                        <span class="badge bg-danger">{{ trans('plugiit-twitch-live::admin.streamers.live') }}</span>
                                    @endif
                                </td>
                                <td>{{ $streamer->user?->name ?? '—' }}</td>
                                <td>@if($streamer->is_featured)<i class="bi bi-star-fill text-warning"></i>@else — @endif</td>
                                <td>
                                    <span class="badge bg-{{ $streamer->isApproved() ? 'success' : 'danger' }}">{{ trans('plugiit-twitch-live::messages.profile.status.'.$streamer->status) }}</span>
                                </td>
                                <td class="text-nowrap">
                                    <a href="{{ route('plugiit-twitch-live.admin.streamers.edit', $streamer) }}" class="mx-1" title="{{ trans('messages.actions.edit') }}" data-bs-toggle="tooltip"><i class="bi bi-pencil-square"></i></a>
                                    <a href="{{ route('plugiit-twitch-live.admin.streamers.destroy', $streamer) }}" class="mx-1" title="{{ trans('messages.actions.delete') }}" data-bs-toggle="tooltip" data-confirm="delete"><i class="bi bi-trash"></i></a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                {{ $streamers->links() }}
            @endif

            <a class="btn btn-primary" href="{{ route('plugiit-twitch-live.admin.streamers.create') }}">
                <i class="bi bi-plus-lg"></i> {{ trans('messages.actions.add') }}
            </a>
        </div>
    </div>
@endsection
