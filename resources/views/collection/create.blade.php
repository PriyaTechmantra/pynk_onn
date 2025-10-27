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
                        <h4 class="d-flex">Create Collection
                            <a href="{{ url('collections') }}" class="btn btn-cta ms-auto">Back</a>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-3 col-lg-2 col-12"></div>
                            <div class="col-xl-6 col-lg-8 col-12">
                                <form method="POST" action="{{ route('collections.store') }}" enctype="multipart/form-data" class="data-form">
                                    @csrf
                                        <h4 class="page__subtitle">Add New Collection</h4>
                                        <div class="form-group mb-3">
                                            <label class="label-control">Title <span class="text-danger">*</span> </label>
                                            <input type="text" name="title" placeholder="" class="form-control" value="{{old('title')}}">
                                            @error('title') <p class="small text-danger">{{ $message }}</p> @enderror
                                        </div>
                                        <div class="form-group mb-3">
                                            <label class="label-control">Description </label>
                                            <textarea name="description" class="form-control" rows="4">{{old('description')}}</textarea>
                                            @error('description') <p class="small text-danger">{{ $message }}</p> @enderror
                                        </div>
                                        <div class="form-group mb-3">
                                            <label class="label-control">Brand Permission</label>
                                            <div class="form-check">
                                                <input 
                                                    class="form-check-input medium-checkbox" 
                                                    type="checkbox" 
                                                    id="brandOnn" 
                                                    value="1"
                                                    onchange="updateBrandValue()"
                                                >
                                                <label class="form-check-label" for="brandOnn">Onn</label>
                                            </div>
                                            <div class="form-check">
                                                <input 
                                                    class="form-check-input medium-checkbox" 
                                                    type="checkbox" 
                                                    id="brandPynk" 
                                                    value="2"
                                                    onchange="updateBrandValue()"
                                                >
                                                <label class="form-check-label" for="brandPynk">Pynk</label>
                                            </div>
                                            <div class="form-check">
                                                <input 
                                                    class="form-check-input medium-checkbox" 
                                                    type="checkbox" 
                                                    id="brandBoth" 
                                                    value="3"
                                                    onchange="updateBrandValue()"
                                                >
                                                <label class="form-check-label" for="brandBoth">Both</label>
                                            </div>
                                            <input type="hidden" name="brand" id="brandValue">
                                        </div>
                                        <div class="form-group mb-3">
                                             <div class="card-header p-0 mb-3">Icon <span class="text-danger">*</span> </div>
                                    
                                                 <div class="product__thumb text-start">
                                                    <label for="icon" class="d-inline-block">
                                                        <img id="iconOutput" 
                                                            src="{{ asset('images/placeholder-image.jpg') }}" 
                                                            class="img-thumbnail rounded shadow-sm" 
                                                            style="width: 100px; height: 60px; object-fit: cover; cursor: pointer;">
                                                    </label>
                                                </div>
                                                <input type="file" name="icon_path" id="icon" accept="image/*" onchange="loadIcon(event)" class="d-none">
                                                    <script>
                                                        let loadIcon = function(event) {
                                                        let iconOutput = document.getElementById('iconOutput');
                                                        iconOutput.src = URL.createObjectURL(event.target.files[0]);
                                                        iconOutput.onload = function() {
                                                            URL.revokeObjectURL(iconOutput.src)
                                                        }
                                                        };
                                                    </script>
                                                @error('icon_path') <p class="small text-danger">{{ $message }}</p> @enderror
                                        </div>
                                        <div class="form-group mb-3">
                                            <div class="card-header p-0 mb-3">Sketch icon <span class="text-danger">*</span></div>
                                            <div class="product__thumb text-start">
                                                <label for="sketch_icon" class="d-inline-block">
                                                    <img id="sketchOutput" src="{{ asset('images/placeholder-image.jpg') }}" 
                                                        class="img-thumbnail rounded shadow-sm" 
                                                        style="width: 100px; height: 60px; object-fit: cover; cursor: pointer;"/>
                                                </label>
                                            </div>
                                            <input type="file" name="sketch_icon" id="sketch_icon" accept="image/*" onchange="loadSketch(event)" class="d-none">
                                            <script>
                                                let loadSketch = function(event) {
                                                    let sketchOutput = document.getElementById('sketchOutput');
                                                    sketchOutput.src = URL.createObjectURL(event.target.files[0]);
                                                    sketchOutput.onload = function() {
                                                    URL.revokeObjectURL(sketchOutput.src) // free memory
                                                    }
                                                };
                                            </script>
                                                @error('sketch_icon') <p class="small text-danger">{{ $message }}</p> @enderror
                                        </div>
                                        <div class="form-group mb-3">
                                            <div class="card-header p-0 mb-3">Thumbnail <span class="text-danger">*</span></div>
                                            <div class="product__thumb text-start">
                                                <label for="thumbnail">
                                                    <img id="output" src="{{ asset('images/placeholder-image.jpg') }}" 
                                                            class="img-thumbnail rounded shadow-sm" 
                                                            style="width: 100px; height: 60px; object-fit: cover; cursor: pointer;" />
                                                </label>
                                            </div>
                                            <input type="file" name="image_path" id="thumbnail" accept="image/*" onchange="loadFile(event)" class="d-none">
                                            <script>
                                                let loadFile = function(event) {
                                                    let output = document.getElementById('output');
                                                    output.src = URL.createObjectURL(event.target.files[0]);
                                                    output.onload = function() {
                                                        URL.revokeObjectURL(output.src) // free memory
                                                    }
                                                };
                                            </script>
                                            @error('image_path') <p class="small text-danger">{{ $message }}</p> @enderror
                                        </div>
                                        <div class="form-group mb-3">
                                            <div class="card-header p-0 mb-3">Banner <span class="text-danger">*</span></div>
                                            <div class="product__thumb text-start">
                                                <label for="banner">
                                                    <img id="bannerOutput" src="{{ asset('admin/images/placeholder-image.jpg') }}" 
                                                        class="img-thumbnail rounded shadow-sm" 
                                                        style="width: 100px; height: 60px; object-fit: cover; cursor: pointer;"/>
                                                </label>
                                            </div>
                                            <input type="file" name="banner_image" id="banner" accept="image/*" onchange="loadBanner(event)" class="d-none">
                                            <script>
                                                let loadBanner = function(event) {
                                                    let output = document.getElementById('bannerOutput');
                                                    output.src = URL.createObjectURL(event.target.files[0]);
                                                    output.onload = function() {
                                                        URL.revokeObjectURL(output.src) // free memory
                                                        }
                                                    };
                                            </script>
                                                @error('banner_image') <p class="small text-danger">{{ $message }}</p> @enderror
                                        </div>
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-sm btn-danger">Add New Collection</button>
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

    function updateBrandValue() {
        let brandOnn = document.getElementById('brandOnn');
        let brandPynk = document.getElementById('brandPynk');
        let brandBoth = document.getElementById('brandBoth');
        let brandValueInput = document.getElementById('brandValue');

        if (brandBoth.checked) {
            // brandOnn.checked = false;
            // brandPynk.checked = false;
            brandValueInput.value = 3;
            return;
        }

        if (!brandBoth.checked) {
            if (brandOnn.checked && brandPynk.checked) {
                brandValueInput.value = 3;
            } else if (brandOnn.checked) {
                brandValueInput.value = 1;
            } else if (brandPynk.checked) {
                brandValueInput.value = 2;
            } else {
                brandValueInput.value = '';
            }
        }
    }
</script>
@endsection

