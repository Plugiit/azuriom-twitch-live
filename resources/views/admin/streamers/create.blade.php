@extends('admin.layouts.admin')

@section('title', trans('twitch-live::admin.streamers.create'))

@section('content')
    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('twitch-live.admin.streamers.store') }}" method="POST">
                @include('twitch-live::admin.streamers._form')

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> {{ trans('messages.actions.save') }}
                </button>
            </form>
        </div>
    </div>
@endsection
