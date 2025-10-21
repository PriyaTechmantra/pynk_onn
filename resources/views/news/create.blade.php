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
                        <h4 class="d-flex">Create News
                            <a href="{{ url('news/') }}" class="btn btn-cta ms-auto">Back</a>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                         <div class="col-xl-3 col-lg-2 col-12"></div>
                            <div class="col-xl-6 col-lg-8 col-12">
                                <form method="POST" action="{{route('news.store')}}" enctype="multipart/form-data" class="data-form">
                                @csrf
                                    <h4 class="page__subtitle">Add News</h4>
                                    <div class="mb-3">
                                        <label class="label-control">Title<span class="text-danger">*</span> </label>
                                        <input type="text" name="title" placeholder="" class="form-control" value="{{old('title')}}">
                                        @error('title') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="">User Type <span class="text-danger">*</span></label>
                                        <select name="user_type[]" class="form-control" id="userTypeSelect" multiple>
                                            <option value="1" {{ (collect(old('user_type'))->contains(1)) ? 'selected' : '' }}>VP</option>
                                            <option value="2" {{ (collect(old('user_type'))->contains(2)) ? 'selected' : '' }}>RSM</option>
                                            <option value="3" {{ (collect(old('user_type'))->contains(3)) ? 'selected' : '' }}>ASM</option>
                                            <option value="4" {{ (collect(old('user_type'))->contains(4)) ? 'selected' : '' }}>ASE</option>
                                        </select>
                                        @error('user_type') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="label-control">Validity From<span class="text-danger">*</span> </label>
                                        <input type="date" name="start_date" class="form-control">{{old('start_date')}}</textarea>
                                        @error('start_date') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="label-control">Validity To<span class="text-danger">*</span></label>
                                        <input type="date" name="end_date" class="form-control">{{old('end_date')}}</textarea>
                                        @error('end_date') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                  
                                    <div class="form-group mb-3">
                                        <label class="label-control">Brand Permission</label>

                                                <div class="form-check">
                                                    <input 
                                                        class="form-check-input medium-checkbox" 
                                                        type="checkbox" 
                                                        name="brand[]" 
                                                        value="1" 
                                                        id="mediumOnn"
                                                        onchange="toggleSelectBox()"
                                                    >
                                                        <label class="form-check-label" for="mediumLMS">Onn</label>
                                                </div>
                                                <div class="form-check">
                                                    <input 
                                                        class="form-check-input medium-checkbox" 
                                                        type="checkbox" 
                                                        name="brand[]" 
                                                        value="2" 
                                                        id="mediumPynk"
                                                        onchange="toggleSelectBox()"
                                                    >
                                                    <label class="form-check-label" for="mediumFMS">Pynk</label>
                                                </div>
                                                                    
                                                <div class="form-check">
                                                    <input 
                                                        class="form-check-input medium-checkbox" 
                                                        type="checkbox" 
                                                        name="brand[]" 
                                                        value="3" 
                                                        id="mediumBoth"
                                                        onchange="toggleSelectBox()"
                                                    >
                                                    <label class="form-check-label" for="mediumCave">Both</label>
                                                </div>
                                    </div>
                                    <div class="col-12 col-md-6 col-xl-12">
                                        <div class="row">
                                            <div class="col-md-6 card">
                                                <div class="card-header p-0 mb-3">Image <span class="text-danger">*</span></div>
                                                <div class="card-body p-0">
                                                    <div class="w-100 product__thumb">
                                                        <label for="icon"><img id="iconOutput" src="{{ asset('admin/images/placeholder-image.jpg') }}" /></label>
                                                    </div>
                                                    <input type="file" name="image" id="icon" accept="image/*" onchange="loadIcon(event)" class="d-none">
                                                    <script>
                                                        let loadIcon = function(event) {
                                                            let iconOutput = document.getElementById('iconOutput');
                                                            iconOutput.src = URL.createObjectURL(event.target.files[0]);
                                                            iconOutput.onload = function() {
                                                                URL.revokeObjectURL(iconOutput.src) // free memory
                                                            }
                                                        };
                                                    </script>
                                                </div>
                                                @error('image') <p class="small text-danger">{{ $message }}</p> @enderror
                                            </div>
                                            <div class="col-md-6 card">
                                                <div class="card-header p-0 mb-3">Pdf <span class="text-danger">*</span></div>
                                                <div class="card-body p-0">
                                                    <div class="w-100 product__thumb">
                                                    </div>
                                                    <div class="col-sm-9">
                                                        <input class="form-control" type="file" name="pdf" id="pdf">
                                                </div>
                                                </div>
                                                @error('pdf') <p class="small text-danger">{{ $message }}</p> @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-sm btn-danger">Add News</button>
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
@section('script')

<script>
$(document).ready(function() {
    $('#userTypeSelect').select2({
        placeholder: "Select User Types",
        allowClear: true
    });
});
</script>
@endsection


