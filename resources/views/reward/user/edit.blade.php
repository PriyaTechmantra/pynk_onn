@php
    $state = DB::select("SELECT id,name FROM `states` GROUP BY name ORDER BY name");
@endphp
@extends('layouts.app')

@section('content')
<div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <div class="card data-card">
                    <div class="card-header">
                        <h4 class="d-flex">Edit New Register Store
                            <a href="{{route('reward.retailer.user.index') }}" class="btn btn-cta ms-auto">Back</a>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-3 col-lg-2 col-12"></div>
                               <div class="col-xl-6 col-lg-8 col-12">
                                <form method="POST" action="{{route('reward.retailer.user.update',$data->id)}}" enctype="multipart/form-data" class="data-form">
                                @csrf
                                    <h4 class="page__subtitle">Edit New Register Store</h4>
                                    <div class="form-group mb-3">
                                        <label class="label-control">Owner Name<span class="text-danger">*</span></label>
                                        <input type="text" name="owner_name" placeholder="" class="form-control" value="{{ $data->owner_name }}">
                                        @error('owner_name') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="form-group mb-3">
                                        <label class="label-control">Store Name<span class="text-danger">*</span></label>
                                        <input type="text" name="shop_name" placeholder="" class="form-control" value="{{ $data->name }}">
                                        @error('shop_name') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="label-control">Business Name<span class="text-danger">*</span></label>
                                        <input type="text" name="bussiness_name" placeholder="" class="form-control" value="{{ $data->bussiness_name }}">
                                        @error('bussiness_name') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <label class="label-control">Contact<span class="text-danger">*</span></label>
                                        <input type="number" name="contact" placeholder="" class="form-control" value="{{ $data->contact }}">
                                        @error('contact') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="label-control">Whatsapp</label>
                                        <input type="number" name="whatsapp" placeholder="" class="form-control" value="{{ $data->whatsapp }}">
                                        @error('whatsapp') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="label-control">Store Address<span class="text-danger">*</span></label>
                                        <textarea row="2" name="address" class="form-control" >{{ $data->address }}</textarea>
                                        @error('address') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="label-control">State<span class="text-danger">*</span></label>
                                        <select name="state_id" id="state_id" class="form-control">
                                            <option value="" disabled>Select</option>
                                            @foreach ($state as $item)
                                                <option value="{{ $item->id }}" {{ $data->state_id == $item->id ? 'selected' : '' }}>
                                                    {{ $item->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('state_id') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="label-control">City<span class="text-danger">*</span></label>
                                        <select class="form-control" id="area_id" name="area_id" {{ empty($data->state_id) ? 'disabled' : '' }}>
                                            @if(!empty($data->state_id))
                                                <option value="{{ $data->area_id }}">{{ $data->area->name ?? 'Selected area' }}</option>
                                            @else
                                                <option value="">Select state first</option>
                                            @endif
                                        </select>

                                        @error('area_id') <p class="small text-danger">{{ $message }}</p> @enderror
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
                                            <label class="label-control">Aadhar</label>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="product__thumb">
                                                    <label for="aadhar">
                                                        <img id="iconOutput" src="{{ asset($data->aadhar) }}" width="200px" style="object-fit:cover; cursor:pointer;" />
                                                    </label>
                                                </div>

                                                    <input type="file" name="aadhar" id="aadhar" accept="image/*" onchange="loadIcon(event)" class="d-none">
                                            </div>

                                            @error('aadhar') 
                                                <p class="small text-danger">{{ $message }}</p> 
                                            @enderror
                                    </div>
                                    <script>
                                    let loadIcon = function(event) {
                                        let iconOutput = document.getElementById('iconOutput');
                                        iconOutput.src = URL.createObjectURL(event.target.files[0]);
                                        iconOutput.onload = function() {
                                            URL.revokeObjectURL(iconOutput.src)
                                        }
                                    };
                                    </script>
                                    <div class="form-group mb-3">
                                        <label class="label-control">PAN</label>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="product__thumb">
                                                <label for="pan">
                                                    <img id="Output" src="{{ asset($data->pan) }}" width="200px" style="object-fit:cover; cursor:pointer;" />
                                                </label>
                                            </div>
                                                <input type="file" name="pan" id="pan" accept="image/*" onchange="panImage(event)"(event)" class="d-none">
                                        </div>
                                        @error('pan') 
                                            <p class="small text-danger">{{ $message }}</p> 
                                        @enderror
                                    </div>
                                    <script>
                                        let panImage = function(event) {
                                            let iconOutput = document.getElementById('Output');
                                            iconOutput.src = URL.createObjectURL(event.target.files[0]);
                                            iconOutput.onload = function() {
                                                URL.revokeObjectURL(iconOutput.src)
                                            }
                                        };
                                    </script>
                                    <div class="form-group mb-3">
                                            <label class="label-control">GST</label>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="product__thumb">
                                                    <label for="gst">
                                                        <img id="icon" src="{{ asset($data->gst) }}" width="200px" style="object-fit:cover; cursor:pointer;" />
                                                    </label>
                                                </div>

                                                    <input type="file" name="gst" id="gst" accept="image/*" onchange="gstImage(event)" class="d-none">
                                            </div>

                                            @error('gst') 
                                                <p class="small text-danger">{{ $message }}</p> 
                                            @enderror
                                    </div>
                                    <script>
                                    let gstImage = function(event) {
                                        let iconOutput = document.getElementById('icon');
                                        iconOutput.src = URL.createObjectURL(event.target.files[0]);
                                        iconOutput.onload = function() {
                                            URL.revokeObjectURL(iconOutput.src)
                                        }
                                    };
                                    </script>
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
    $('select[name="state"]').on('change', (event) => {
        var value = $('select[name="state"]').val();
      
        $.ajax({
            url: '{{url("/")}}/employees/state/'+value,
            method: 'GET',
            success: function(result) {
                var content = '';
                var slectTag = 'select[name="area"]';
                var displayCollection =  "All";

                content += '<option value="" selected>'+displayCollection+'</option>';
                $.each(result.data.area, (key, value) => {
                    content += '<option value="'+value.area_id+'">'+value.area+'</option>';
                });
                $(slectTag).html(content).attr('disabled', false);
            }
        });
    });
</script>
@endsection
