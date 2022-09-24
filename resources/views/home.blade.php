@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col">
                <posts-table :posts="{{$posts}}"></posts-table>
            </div>
        </div>
    </div>
@endsection
