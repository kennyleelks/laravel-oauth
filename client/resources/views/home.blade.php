@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if (!empty($user->avatar))
                        <img src="{{ $user->avatar }}" alt="{{ $user->name }}">
                    @else
                        <a href="{{ url('redirect') }}" class="btn btn-primary">第三方登入</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
