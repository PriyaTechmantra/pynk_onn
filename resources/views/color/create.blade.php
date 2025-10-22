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
                        <h4 class="d-flex">Create Color
                            <a href="{{ url('colors/') }}" class="btn btn-cta ms-auto">Back</a>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-3 col-lg-2 col-12"></div>
                            <div class="col-xl-6 col-lg-8 col-12">
                                <form method="POST" action="{{route('colors.store')}}" enctype="multipart/form-data" class="data-form">
                                    @csrf
                                    <h4 class="page__subtitle">Add New Color</h4>
                                    
                                    <div class="form-group mb-3">
                                        <label for="title">Title <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="title" name="title" value="{{ old('name') }}">
                                        @error('title') <p class="small text-danger">{{$message}}</p> @enderror
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="title">Code <span class="text-danger">*</span></label>
                                        <input type="color" class="form-control" id="code" name="code" value="{{ old('code') }}">
                                            @error('code') <p class="small text-danger">{{$message}}</p> @enderror
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="label-control">Brand Permission</label>
                                            <div class="form-check">
                                                <input class="form-check-input medium-checkbox" type="checkbox" name="brand[]" value="1" id="mediumOnn" onchange="toggleSelectBox()">
                                                <label class="form-check-label" for="mediumLMS">Onn</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input medium-checkbox" type="checkbox" name="brand[]" value="2" id="mediumPynk" onchange="toggleSelectBox()">
                                                <label class="form-check-label" for="mediumFMS">Pynk</label>
                                            </div>
                                                                    
                                            <div class="form-check">
                                                <input class="form-check-input medium-checkbox" type="checkbox" name="brand[]" value="3" id="mediumBoth" onchange="toggleSelectBox()">
                                                <label class="form-check-label" for="mediumCave">Both</label>
                                            </div>
                                    </div>
                            
                                  
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-danger">Save changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection

