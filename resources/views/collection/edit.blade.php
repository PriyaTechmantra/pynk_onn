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
                        <h4 class="d-flex">Edit Collection
                            <a href="{{ url('collections') }}" class="btn btn-cta ms-auto">Back</a>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-3 col-lg-2 col-12"></div>
                            <div class="col-xl-6 col-lg-8 col-12">
                                <form method="POST" action="{{ route('collections.update', $data->id) }}" enctype="multipart/form-data"  class="data-form">
                                    @csrf
                                    <h4 class="page__subtitle">Edit Collection</h4>
                                    <div class="form-group mb-3">
                                        <label class="label-control">Title <span class="text-danger">*</span> </label>
                                        <input type="text" name="title" placeholder="" class="form-control" value="{{ $data->name }}">
                                        @error('title') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="label-control">Description </label>
                                        <textarea name="description" class="form-control" rows="4">{{$data->description}}</textarea>
                                        @error('description') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                     <div class="form-group mb-3">
                                        <label class="label-control">
                                            Brand Permission:
                                        </label>
                                            <div class="form-check">
                                                <input type="checkbox" id="brandOnn" value="1" onchange="updateBrandValue()" 
                                                    @checked(old('brand', $data->brand ?? '') == 1 )>
                                                <label class="form-check-label" for="brandOnn">Onn</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" id="brandPynk" value="2" onchange="updateBrandValue()" 
                                                    @checked(old('brand', $data->brand ?? '') == 2)>
                                                <label class="form-check-label" for="brandPynk">Pynk</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" id="brandBoth" value="3" onchange="updateBrandValue()" 
                                                    @checked(old('brand', $data->brand ?? '') == 3)>
                                                <label class="form-check-label" for="brandBoth">Both</label>
                                            </div>
                                        <input type="hidden" name="brand" id="brandValue" value="{{$data->brand}}">
                                    </div>
                                    <div class="form-group mb-3">
                                        <div class="card-header p-0 mb-3">Icon </div>
                                        <div class="product__thumb text-start">
                                            <label for="icon"><img id="iconOutput" src="{{ asset($data->icon_path) }}" 
                                                class="img-thumbnail rounded shadow-sm" 
                                                style="width: 120px; height: 120px; object-fit: cover; cursor: pointer;"  /></label>
                                        </div>
                                        <input type="file" name="icon_path" id="icon" accept="image/*" onchange="loadIcon(event)" class="d-none">
                                        <script>
                                            let loadIcon = function(event) {
                                                let iconOutput = document.getElementById('iconOutput');
                                                iconOutput.src = URL.createObjectURL(event.target.files[0]);
                                                iconOutput.onload = function() {
                                                    URL.revokeObjectURL(iconOutput.src) // free memory
                                                }
                                            };
                                        </script>
                                        @error('icon_path') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="form-group mb-3">
                                        <div class="card-header p-0 mb-3">Sketch icon </div>
                                        <div class="product__thumb text-start">
                                            <label for="sketch_icon"><img id="sketchOutput" src="{{ asset($data->sketch_icon) }}" 
                                                    class="img-thumbnail rounded shadow-sm" 
                                                    style="width: 120px; height: 120px; object-fit: cover; cursor: pointer;"/></label>
                                        </div>
                                        <input type="file" name="sketch_icon" id="sketch_icon" accept="image/*" onchange="loadSketch(event)" class="d-none">
                                        <script>
                                            var loadSketch = function(event) {
                                            var sketchOutput = document.getElementById('sketchOutput');
                                            sketchOutput.src = URL.createObjectURL(event.target.files[0]);
                                            sketchOutput.onload = function() {
                                                URL.revokeObjectURL(sketchOutput.src) // free memory
                                            }
                                            };
                                        </script>
                                        @error('sketch_icon') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="form-group mb-3">
                                        <div class="card-header p-0 mb-3">Thumbnail </div>
                                        <div class="product__thumb text-start">
                                            <label for="thumbnail"><img id="output" src="{{ asset($data->image_path) }}" 
                                                class="img-thumbnail rounded shadow-sm" 
                                                style="width: 120px; height: 120px; object-fit: cover; cursor: pointer;"/></label>
                                        </div>
                                        <input type="file" name="image_path" id="thumbnail" accept="image/*" onchange="loadFile(event)" class="d-none">
                                        <script>
                                            var loadFile = function(event) {
                                                var output = document.getElementById('output');
                                                output.src = URL.createObjectURL(event.target.files[0]);
                                                output.onload = function() {
                                                    URL.revokeObjectURL(output.src) // free memory
                                                }
                                            };
                                        </script>
                                        @error('image_path') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="form-group mb-3">
                                        <div class="card-header p-0 mb-3">Banner Image </div>
                                        <div class="product__thumb text-start">
                                            <label for="banner"><img id="bannerOutput" src="{{ asset($data->banner_image) }}"
                                                class="img-thumbnail rounded shadow-sm" 
                                                style="width: 120px; height: 120px; object-fit: cover; cursor: pointer;" /></label>
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
                                        <button type="submit" class="btn btn-sm btn-danger">Update Collection</button>
                                    </div>
                                </form>
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
