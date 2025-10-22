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
                        <h4 class="d-flex">Color Detail
                            <a href="{{ url('colors') }}" class="btn btn-cta ms-auto">Back</a>
                             <a href="{{ route('colors.edit',$data->id) }}" class="btn btn-cta">
                                Edit
                            </a>
                        </h4>
                    </div>
                    <div class="card-body">
                        <h5 class="display-6">{{ $data->name }}</h5>

                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="{{$data->code}} " stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-circle"><circle cx="12" cy="12" r="10"></circle></svg>
                        {{ $data->code }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

