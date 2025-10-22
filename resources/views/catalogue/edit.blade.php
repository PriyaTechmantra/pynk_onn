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
                                        <select name="state[]" id="stateSelect" class="form-control" multiple>
                                            @foreach($states as $state)
                                                <option value="{{ $state->id }}" 
                                                    {{ in_array($state->id, old('state', $data->state)) ? 'selected' : '' }}>
                                                    {{ $state->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('state') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="label-control">VP <span class="text-danger">*</span></label>
                                        <select name="vp[]" id="vpSelect" class="form-control" multiple>
                                            @foreach($vps as $vp)
                                                <option value="{{ $vp->id }}"
                                                    {{ in_array($vp->id, old('vp', $data->vp ?? [])) ? 'selected' : '' }}>
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
</script>
@endsection