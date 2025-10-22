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
                        <h4 class="d-flex">Edit Color
                            <a href="{{ url('colors') }}" class="btn btn-cta ms-auto">Back</a>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-3 col-lg-2 col-12"></div>
                            <div class="col-xl-6 col-lg-8 col-12">
                                <form method="POST" action="{{ route('colors.update', $data->id) }}" enctype="multipart/form-data">@csrf
                                    <div class="form-group mb-3">
                                        <label for="title">Title <span clas="textdanger">*</span></label>
                                        <input type="text" class="form-control" id="title" name="title" value="{{ $data->name }}">
                                            @error('title') <p class="small text-danger">{{$message}}</p> @enderror
                                    </div>
                                    <div class="form-group mb-3">
                                        <input type="color" class="form-control" id="code" name="code" value="{{$data->code}}">
                                        <label for="title">Code <span clas="textdanger">*</span></label>
                                            @error('code') <p class="small text-danger">{{$message}}</p> @enderror
                                    </div>
                                    <div class="form-group mb-3">
                                       <label class="label-control">
                                           Brand Permission:
                                       </label>
                                       <div class="form-check">
                                           <input class="form-check-input" type="checkbox" name="brand[]" value="1" id="brandOnn"
                                               {{ in_array(1, $data->brand ?? []) ? 'checked' : '' }}>
                                           <label class="form-check-label" for="brandOnn">Onn</label>
                                       </div>
                                       <div class="form-check">
                                           <input class="form-check-input" type="checkbox" name="brand[]" value="2" id="brandPynk"
                                               {{ in_array(2, $data->brand ?? []) ? 'checked' : '' }}>
                                           <label class="form-check-label" for="brandPynk">Pynk</label>
                                       </div>
                                       <div class="form-check">
                                           <input class="form-check-input" type="checkbox" name="brand[]" value="3" id="brandBoth"
                                               {{ in_array(3, $data->brand ?? []) ? 'checked' : '' }}>
                                           <label class="form-check-label" for="brandBoth">Both</label>
                                       </div>
                                    </div>
                                    <div class="form-group">
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
