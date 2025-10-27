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
