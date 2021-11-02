@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ $user->name }}'s avatar</div>

                <div class="card-body">
                    <img class="img-fluid" src="{{ $user->avatar }}" alt="">
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
