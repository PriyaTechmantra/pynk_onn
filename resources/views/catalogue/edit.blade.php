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
                        <h4 class="d-flex">Edit Catalogue
                            <a href="{{ url('catalogues') }}" class="btn btn-cta ms-auto">Back</a>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                         <div class="col-xl-3 col-lg-2 col-12"></div>
                            <div class="col-xl-6 col-lg-8 col-12">
                                <form method="POST" action="{{route('catalogues.update',$data->id)}}" enctype="multipart/form-data" class="data-form">
                                @csrf
                                    <h4 class="page__subtitle">Edit Collection</h4>
                                    <div class="form-group mb-3">
                                        <label class="label-control">Title <span class="text-danger">*</span> </label>
                                        <input type="text" name="title" placeholder="" class="form-control" value="{{ $data->title }}">
                                        @error('title') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="label-control">Start Date </label>
                                        <input type="date" name="start_date" class="form-control" value="{{date('Y-m-d', strtotime($data->start_date))}}">
                                        @error('start_date') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="label-control">End Date </label>
                                        <input type="date" name="end_date" class="form-control" value="{{date('Y-m-d', strtotime($data->end_date))}}">
                                        @error('end_date') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                   <div class="form-group mb-3">
                                        <label class="label-control">States <span class="text-danger">*</span></label>
                                        @php
                                            $selectedStates = [];

                                            if (!empty($data->state)) {
                                                if (is_array($data->state)) {
                                                    $selectedStates = $data->state;
                                                } elseif (is_string($data->state)) {
                                                    $selectedStates = explode(',', $data->state);
                                                } else {
                                                    $selectedStates = [$data->state];
                                                }
                                            }
                                        @endphp

                                        <select name="state[]" id="stateSelect" class="form-control" multiple>
                                            @foreach($states as $state)
                                                <option value="{{ $state->id }}" {{ in_array($state->id, $selectedStates) ? 'selected' : '' }}>
                                                    {{ $state->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('state') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="label-control">VP <span class="text-danger">*</span></label>
                                            @php
                                    $selectedVps = [];

                                    if (!empty($data->vp)) {
                                        if (is_array($data->vp)) {
                                            $selectedVps = $data->vp;
                                        } elseif (is_string($data->vp)) {
                                            $selectedVps = explode(',', $data->vp);
                                        } else {
                                            $selectedVps = [$data->vp];
                                        }
                                    }
                                @endphp

                                <select name="vp[]" id="vpSelect" class="form-control" multiple>
                                    @foreach($vps as $vp)
                                        <option value="{{ $vp->id }}" {{ in_array($vp->id, $selectedVps) ? 'selected' : '' }}>
                                            {{ $vp->name }}
                                        </option>
                                    @endforeach
                                </select>

                                        @error('vp')
                                            <p class="small text-danger">{{ $message }}</p>
                                        @enderror
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
                                            <label class="label-control">Image <span class="text-danger">*</span></label>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="product__thumb">
                                                    <label for="image">
                                                        <img id="iconOutput" src="{{ asset($data->image) }}" width="200px" style="object-fit:cover; cursor:pointer;" />
                                                    </label>
                                                </div>

                                                    <input type="file" name="image" id="image" accept="image/*" onchange="loadIcon(event)" class="d-none">
                                            </div>

                                            @error('image') 
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
                                            <label class="label-control me-3">Pdf <span class="text-danger">*</span></label>
                                            <div class="d-flex align-items-center gap-2">
                                                <input class="form-control" type="file" name="pdf" id="pdf" value="{{ old('pdf', $data->pdf) }}">
                                            </div>
                                            @error('pdf') 
                                                <p class="small text-danger">{{ $message }}</p> 
                                            @enderror
                                            <a class="btn btn-sm btn-primary" href="{{ asset($data->pdf) }}" target="_blank">
                                                    View PDF
                                            </a>
                                        </div>

                                    <div class="form-group">
                                        <button type="submit" class="btn btn-sm btn-danger">Update Catalogue</button>
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
@section('script')
<script>

$(document).ready(function() {
    $('#stateSelect, #vpSelect').select2({
        placeholder: "Select options",
        allowClear: true
    });
});

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

  
