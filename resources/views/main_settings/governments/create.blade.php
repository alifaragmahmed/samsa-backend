@extends('layouts.admin')
@section('title') {{ __('governments') }} @endsection

@section('content')
@php
$links = [
['name' => __('home'), 'url' => url('/')],
['name' => __('governments'), 'url' => url('/governments')],
['name' => __('add government'), 'active' => true],
];
@endphp
@include('layouts.breadcrumb', ['links' => $links])

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3> {{ __('add') }} </h3>
                </div>
                <div class="card-body">
                    {!! Form::open(['method'=>'post','route'=>'governments.store'])!!}
                    @include('main_settings.governments.form')
                    <button type="submit" class="btn btn-primary waves-effect waves-light"> {{ __('save') }} </button>
                    {!!Form::close()!!}
                </div>
            </div>
        </div>
    </div>
@endsection
