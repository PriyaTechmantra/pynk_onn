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
                        <h4 class="d-flex">Edit Employee
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
        
                                    <div class="mb-3">
                                        <label for="">User Type</label>
                                        <select id="user_type" name="type" class="form-control">
                                            <option value="" selected disabled>--Select--</option>
                                            <option value="1" {{$data->type == 1 ? 'selected' : ''}}>VP</option>
                                            <option value="2" {{$data->type == 2 ? 'selected' : ''}}>RSM</option>
                                            <option value="3" {{$data->type == 3 ? 'selected' : ''}}>ASM</option>
                                            <option value="4" {{$data->type == 4 ? 'selected' : ''}} >ASE</option>
                                        </select>
                                        
                                        @error('type') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="designation">Designation <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="designation" name="designation" placeholder="" value="{{ old('designation',$data->designation)}}">
                                        @error('designation') <p class="small text-danger">{{$message}}</p> @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="employee_id">Employee ID<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="employee_id" name="employee_id" placeholder="" value="{{ old('employee_id',$data->employee_id)}}">
                                        @error('employee_id') <p class="small text-danger">{{$message}}</p> @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label class="label-control">Full Name <span class="text-danger">*</span> </label>
                                        <input type="text" name="name" placeholder="" class="form-control" value=" {{old('name',$data->name)}}">
                                        @error('name') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label class="label-control">Email  </label>
                                        <input type="email" name="email" placeholder="" class="form-control" value="{{old('email',$data->email)}}">
                                        @error('email') <p class="small text-danger">{{ $message }}</p> @enderror
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
                                        <label for="state">State <span class="text-danger">*</span></label>
                                        <select class="form-select select2" id="state" name="state" aria-label="Floating label select example">
                                            <option value="" selected >--Select State--</option>
                                            @foreach ($state as $index => $item)
                                                <option value="{{ $item->id }}" {{$data->state == $item->id ? 'selected' : ''}}>{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('state') <p class="small text-danger">{{$message}}</p> @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="area">City/ Area</label>
                                        <select class="form-select select2" id="area" name="city" aria-label="Floating label select example" disabled>
                                            <option value="">Select State first</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="label-control">Date of Joining <span class="text-danger">*</span> </label>
                                        <input type="date" name="date_of_joining" placeholder="" class="form-control" value="{{old('date_of_joining',$data->date_of_joining)}}">
                                        @error('date_of_joining') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label class="label-control">Password <span class="text-danger">*</span> </label>
                                        <input type="password" name="password" placeholder="" class="form-control" value="{{old('password',$data->password)}}">
                                        @error('password') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    @php
                                         $assignedPermissions = DB::table('user_permission_categories')->select('user_permission_categories.*')->join('employees','employees.id','=','user_permission_categories.employee_id')->where('user_permission_categories.employee_id', $data->id)->get()->toArray();
                                         $brand = collect($assignedPermissions)->pluck('brand')->toArray();
                                    @endphp
                                    <div class="mb-3">
                                            <!-- Communication Medium -->
                                            <h6>Brand Permission:</h6>
                                            
                                            <div class="form-check">
                                                <input 
                                                    class="form-check-input medium-checkbox" 
                                                    type="checkbox" 
                                                    name="brand" 
                                                    value="1" 
                                                    id="mediumOnn"
                                                    {{ isset($assignedPermissions) && in_array('1', $brand) ? 'checked' : '' }}
                                                    onchange="toggleSelectBox()"
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
                                                    {{ isset($assignedPermissions) && in_array('2', $brand) ? 'checked' : '' }}
                                                    onchange="toggleSelectBox()"
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
                                                    {{ isset($assignedPermissions) && in_array('3', $brand) ? 'checked' : '' }}
                                                    onchange="toggleSelectBox()"
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
    $('select[name="state"]').on('change', (event) => {
        var stateId = $(event.target).val();
        var selectedCityId = "{{ $data->city ?? '' }}"; // from backend

        $.ajax({
            url: '{{url("/")}}/areas/state/wise/' + stateId,
            method: 'GET',
            success: function(result) {
                var content = '';
                var slectTag = 'select[name="city"]';
                var displayCollection = "All";

                content += '<option value="" disabled>--Select City--</option>';
                $.each(result.data.area, (key, value) => {
                    let selected = (value.area_id == selectedCityId) ? 'selected' : '';
                    content += '<option value="' + value.area_id + '" ' + selected + '>' + value.area + '</option>';
                });
                $(slectTag).html(content).attr('disabled', false);
            }
        });
    });

    // 🔹 Trigger change once to pre-fill cities in edit form
    $('select[name="state"]').trigger('change');
</script>

@endsection