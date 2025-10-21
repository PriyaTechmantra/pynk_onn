@extends('layouts.app')

@section('content')
<div class="container mt-5">
        <div class="row">
            <div class="col-md-12">

                @if ($errors->any())
                <ul class="alert alert-warning">
                    @foreach ($errors->all() as $error)
                        <li>{{$error}}</li>
                    @endforeach
                </ul>
                @endif

                <div class="card data-card">
                    <div class="card-header">
                        <h4 class="d-flex">Catalogue Detail
                            <a href="{{ url('catalogues') }}" class="btn btn-cta ms-auto">Back</a>
                                <a href="{{ route('catalogues.edit',$data->id) }}" class="btn btn-cta">
                                    Edit
                                </a>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-6 col-lg-8 col-12">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h3>{{ $data->title }}</h3>
                                            <p class="small">Start Date: {{ $data->start_date }}</p>
                                            <p class="small">End Date : {{ $data->end_date }}</p>
                                            <hr>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <p class="text-muted">Image</p>
                                            <img src="{{ asset($data->image) }}" alt="" style="height: 50px">
                                        </div>
                                        <div class="col-md-3">
                                            <p class="text-muted">Pdf</p>
                                            <a href="{{ asset($data->pdf) }}" target="_blank"><i class="app-menu__icon fa fa-download"></i>Pdf</a>
                                        </div>
                                    </div>
                                    <hr>
                                </div>
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
