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
                        <h4 class="d-flex">Size Detail
                            <a href="{{ url('sizes') }}" class="btn btn-cta ms-auto">Back</a>
                             <a href="{{ route('sizes.edit',$data->id) }}" class="btn btn-cta">
                                Edit
                            </a>
                        </h4>
                    </div>
                    <div class="card-body">
                        <h5 class="display-6">{{ $data->name }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
