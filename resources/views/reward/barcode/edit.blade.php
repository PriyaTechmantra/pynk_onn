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
                        <h4 class="d-flex">Edit QRcode
                            <a href="{{ route('reward.retailer.barcode.index') }}" class="btn btn-cta ms-auto">Back</a>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-3 col-lg-2 col-12"></div>
                            <div class="col-xl-6 col-lg-8 col-12">
                                <form method="POST" action="{{ route('reward.retailer.barcode.update', $data->id) }}" enctype="multipart/form-data" class="data-form">
                                    @csrf
                                        <h4 class="page__subtitle">Edit</h4>
                                        <div class="form-group mb-3">
                                            <label class="label-control">Name <span class="text-danger">*</span> </label>
                                            <input type="text" name="name" placeholder="" class="form-control" value="{{ $data->name }}">
                                            @error('name') <p class="small text-danger">{{ $message }}</p> @enderror
                                        </div>
                                        
                                      
                                         <div class="form-group mb-3">
                                            <label class="label-control"> code <span class="text-danger">*</span> </label>
                                            <input type="text" name="code" placeholder="" class="form-control" value="{{ $data->code }}" disabled>
                                            @error('code') <p class="small text-danger">{{ $message }}</p> @enderror
                                        </div>
                                        <div class="form-group mb-3">
                                            <label class="label-control">Amount <span class="text-danger">*</span> </label>
                                            <input type="number" name="amount" placeholder="" class="form-control" value="{{ $data->amount }}">
                                            @error('amount') <p class="small text-danger">{{ $message }}</p> @enderror
                                        </div>
                                        <div class="form-group mb-3">
                                            <label class="label-control">Max time of use <span class="text-danger">*</span> </label>
                                            <input type="number" name="max_time_of_use" placeholder="" class="form-control" value="{{ $data->max_time_of_use }}">
                                            @error('max_time_of_use') <p class="small text-danger">{{ $message }}</p> @enderror
                                        </div>
                                        <div class="form-group mb-3">
                                            <label class="label-control">Max time one can use <span class="text-danger">*</span> </label>
                                            <input type="number" name="max_time_one_can_use" placeholder="" class="form-control" value="{{ $data->max_time_one_can_use }}">
                                            @error('max_time_one_can_use') <p class="small text-danger">{{ $message }}</p> @enderror
                                        </div>
                                        <div class="form-group mb-3">
                                            <label class="label-control">Start date <span class="text-danger">*</span> </label>
                                            <input type="datetime-local" name="start_date" placeholder="" class="form-control" value="{{ date('Y-m-d h:i:s', strtotime($data->start_date)) }}">
                                            @error('start_date') <p class="small text-danger">{{ $message }}</p> @enderror
                                        </div>
                                        <div class="form-group mb-3">
                                            <label class="label-control">End date <span class="text-danger">*</span> </label>
                                            <input type="datetime-local" name="end_date" placeholder="" class="form-control" value="{{ date('Y-m-d h:i:s', strtotime($data->end_date)) }}">
                                            @error('end_date') <p class="small text-danger">{{ $message }}</p> @enderror
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
                                            <button type="submit" class="btn btn-sm btn-danger">Update</button>
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
