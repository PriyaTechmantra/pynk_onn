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
                        <h4 class="d-flex">Edit Size
                            <a href="{{ url('sizes') }}" class="btn btn-cta ms-auto">Back</a>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                         <div class="col-xl-3 col-lg-2 col-12"></div>
                            <div class="col-xl-6 col-lg-8 col-12">
                                <form method="POST" action="{{route('sizes.update',$data->id)}}" enctype="multipart/form-data" class="data-form">
                                @csrf
                                    <h4 class="page__subtitle">Edit Size</h4>
                                    
                                    <div class="form-group mb-3">
                                        <label class="label-control">Title <span class="text-danger">*</span> </label>
                                        <input type="text" name="title" placeholder="" class="form-control" value="{{ $data->name }}">
                                        @error('title') <p class="small text-danger">{{ $message }}</p> @enderror
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
                                        <button type="submit" class="btn btn-sm btn-danger">Update Size</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</section>
@endsection
