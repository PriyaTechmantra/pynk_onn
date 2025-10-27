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
                        <h4 class="d-flex">Update Employee
                            <a href="{{ url('employees') }}" class="btn btn-cta ms-auto">Back</a>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-3 col-lg-2 col-12"></div>
                            <div class="col-xl-6 col-lg-8 col-12">
                                <form action="{{ url('employees/'.$data->id) }}" method="POST" class="data-form">
                                    @csrf
                                    @method('PUT')
                                    <h4 class="page__subtitle">Add New</h4>
                        
                                    <div class="mb-3">
                                        <label for="">User Type <span class="text-danger">*</span></label>
                                        <select id="user_type" name="type" class="form-control">
                                            <option value="" selected disabled>Select</option>
                                            <option value="1" {{ ($data->type == 1 )? 'selected' : ''}}>VP</option>
                                            <option value="2" {{ ($data->type == 2 )? 'selected' : ''}}>RSM</option>
                                            <option value="3" {{ ($data->type == 3 )? 'selected' : ''}}>ASM</option>
                                            <option value="4" {{ ($data->type == 4 )? 'selected' : ''}}>ASE</option>
                                        </select>
                                        
                                        @error('type') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="designation">Designation <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="designation" name="designation" placeholder="" value="{{ old('designation',$data->designation) }}">
                                        @error('designation') <p class="small text-danger">{{$message}}</p> @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="employee_id">Employee ID<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="employee_id" name="employee_id" placeholder="" value="{{ old('employee_id',$data->employee_id)  }}">
                                        @error('employee_id') <p class="small text-danger">{{$message}}</p> @enderror
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="label-control">Full Name <span class="text-danger">*</span> </label>
                                        <input type="text" name="name" placeholder="" class="form-control" value=" {{old('name',$data->name)}}">
                                        @error('name') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label class="label-control">Official Email  </label>
                                        <input type="email" name="email" placeholder="" class="form-control" value="{{old('email' ,$data->email)}}">
                                        @error('email') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label class="label-control">Personal Email  </label>
                                        <input type="email" name="personal_mail" placeholder="" class="form-control" value="{{old('personal_mail' ,$data->personal_mail)}}">
                                        @error('personal_mail') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label class="label-control">Contact <span class="text-danger">*</span> </label>
                                        <input type="number" name="mobile" placeholder="" class="form-control" value="{{old('mobile',$data->mobile)}}">
                                        @error('mobile') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label class="label-control">WhatsApp Number </label>
                                        <input type="number" name="whatsapp_no" placeholder="" class="form-control" value="{{old('whatsapp_no',$data->whatsapp_no)}}">
                                        @error('whatsapp_no') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label class="label-control">Alt. Mobile 1 </label>
                                        <input type="number" name="alt_number1" placeholder="" class="form-control" value="{{old('alt_number1',$data->alt_number1)}}">
                                        @error('alt_number1') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label class="label-control">Alt. Mobile 2 </label>
                                        <input type="number" name="alt_number2" placeholder="" class="form-control" value="{{old('alt_number2',$data->alt_number2)}}">
                                        @error('alt_number2') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label class="label-control">Alt. Mobile 3 </label>
                                        <input type="number" name="alt_number3" placeholder="" class="form-control" value="{{old('alt_number3',$data->alt_number3)}}">
                                        @error('alt_number3') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="state">State <span class="text-danger">*</span></label>
                                        <select class="form-select select2" id="state" name="state" aria-label="Floating label select example">
                                            <option value="" selected >Select</option>
                                            @foreach ($state as $index => $item)
                                                <option value="{{ $item->id }}" {{ ($data->state == $item->id) ? 'selected' : '' }}>{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('state') <p class="small text-danger">{{$message}}</p> @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="area">City/ Area</label>
                                        <select class="form-select select2" id="city" name="city" aria-label="Floating label select example" disabled>
                                            <option value="">Select State first</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="label-control">Date of Joining  </label>
                                        <input type="date" name="date_of_joining" placeholder="" class="form-control" value="{{old('date_of_joining',$data->date_of_joining)}}">
                                        @error('date_of_joining') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label class="label-control">Password <span class="text-danger">*</span> </label>
                                        <input type="password" name="password" placeholder="" class="form-control" value="{{old('password',$data->password)}}">
                                        @error('password') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="mb-3">
                                            <!-- Communication Medium -->
                                            <h6>Brand Permission:  <span class="text-danger">*</span></h6>
                                            @error('brand') <p class="small text-danger">{{ $message }}</p> @enderror
                                            <div class="form-check">
                                                <input 
                                                    class="form-check-input medium-checkbox" 
                                                    type="checkbox" 
                                                    name="brand" 
                                                    value="1" 
                                                    id="mediumOnn"
                                                     {{ $data->brand == 1 ? 'checked' : '' }}
                                                    onchange="checkOnlyOne(this)"
                                                >
                                                <label class="form-check-label" for="mediumLMS">Onn</label>
                                            </div>
                                            <div class="form-check">
                                                <input 
                                                    class="form-check-input medium-checkbox" 
                                                    type="checkbox" 
                                                    name="brand" 
                                                    value="2" 
                                                    id="mediumPynk"
                                                    {{ $data->brand == 2 ? 'checked' : '' }}
                                                    onchange="checkOnlyOne(this)"
                                                >
                                                <label class="form-check-label" for="mediumFMS">Pynk</label>
                                            </div>
                                            
                                            <div class="form-check">
                                                <input 
                                                    class="form-check-input medium-checkbox" 
                                                    type="checkbox" 
                                                    name="brand" 
                                                    value="3" 
                                                    id="mediumBoth"
                                                    {{ $data->brand == 3 ? 'checked' : '' }}
                                                    onchange="checkOnlyOne(this)"
                                                >
                                                <label class="form-check-label" for="mediumCave">Both</label>
                                            </div>
                                        </div>
                                    
                                    <div class="text-end mb-3">
                                        <button type="submit" class="btn btn-submit">Save</button>
                                    </div>
                                </form>
                            </div>
                            <div class="col-xl-3 col-lg-2 col-12"></div>
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
    var selectedState = "{{ request()->input('state') ?? $data->state }}";
    var selectedArea = "{{ request()->input('city') ?? $data->city }}";

    // Trigger change if state is already selected (for edit/filter persistence)
    if (selectedState) {
        loadAreas(selectedState, selectedArea);
    }

    // When state changes manually
    $('#state').on('change', function() {
        var stateId = $(this).val();
        loadAreas(stateId, selectedArea);
    });

    // Function to load areas
    function loadAreas(stateId, selectedArea = '') {
        if (!stateId) return;

        $.ajax({
            url: '{{ url("/") }}/employees/state/' + stateId,
            method: 'GET',
            success: function(result) {
                var content = '<option value="" disabled>Select</option>';
                $.each(result.data.area, function(key, val) {
                    var selected = (val.area_id == selectedArea) ? 'selected' : '';
                    content += '<option value="' + val.area_id + '" ' + selected + '>' + val.area + '</option>';
                });

                $('#city').html(content).prop('disabled', false);
            }
        });
    }
});
</script>
@endsection

