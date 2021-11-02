@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ $user->name }}'s avatar</div>

                <div class="card-body">
                    @if (empty($user->access_token))
                        <a href="{{ url('redirect') }}" class="btn btn-primary">第三方登入</a>
                    @else
                        <a href="{{ url('avatar') }}" class="btn btn-primary">更新頭像</a>
                    @endif
                    <hr>
                    @if (!empty($user->avatar))
                        <img class="img-fluid" src="{{ $user->avatar }}" alt="{{ $user->name }}">
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
