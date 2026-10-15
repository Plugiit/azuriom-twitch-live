@extends('admin.layouts.admin')

@section('title', trans('twitch-live::admin.streamers.edit', ['login' => $streamer->login]))

@section('content')
    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('twitch-live.admin.streamers.update', $streamer) }}" method="POST">
                @method('PUT')

                @include('twitch-live::admin.streamers._form')

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> {{ trans('messages.actions.save') }}
                </button>
                <a href="{{ route('twitch-live.admin.streamers.destroy', $streamer) }}" class="btn btn-danger" data-confirm="delete">
                    <i class="bi bi-trash"></i> {{ trans('messages.actions.delete') }}
                </a>
            </form>
        </div>
    </div>
@endsection
