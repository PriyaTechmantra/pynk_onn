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
                        <h4 class="d-flex">Edit Area
                            <a href="{{ url('areas') }}" class="btn btn-cta ms-auto">Back</a>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-3 col-lg-2 col-12"></div>
                            <div class="col-xl-6 col-lg-8 col-12">
                                <form action="{{ url('areas/'.$data->id) }}" method="POST" class="data-form">
                                    @csrf
                                    @method('PUT')
        
                                    <div class="mb-3">
                                        <label for="">Name</label>
                                        <input type="text" name="name" value="{{ $data->name }}" class="form-control" />
                                        @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="">State</label>
                                        <select 
                                                class="form-select form-select-sm" 
                                                aria-label="Default select example" 
                                                name="state_id" 
                                                id="state_id" 
                                                
                                            >
                                                    <option value="" selected disabled>Select</option>
                                                    @foreach ($state as $cat)
                                                        <option value="{{$cat->id}}" {{($data->state_id) == $cat->id ? 'selected' : ''}}> {{$cat->name}}</option>
                                                    @endforeach
                                        </select>
                                    </div>
                                    
                                    <div class="text-end mb-3">
                                        <button type="submit" class="btn btn-submit">Update</button>
                                    </div>
                                </form>
                            </div>
                            <div class="col-xl-3 col-lg-2 col-12"></div>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection