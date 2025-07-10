@extends('base')
@section('body')
    @livewire('devicesManager', ['request' => request()])
@endsection
    