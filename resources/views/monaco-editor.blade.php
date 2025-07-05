@extends('base')
@section('body')
    @livewire('monaco-editor', [
        'class' => $class,
        'field' => $field,
        'id' => $id,
        'language' => $language
    ])
@endsection