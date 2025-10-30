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
                        <h4 class="d-flex">Create Employee
                            <a href="{{ url('employees') }}" class="btn btn-cta ms-auto">Back</a>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-3 col-lg-2 col-12"></div>
                            <div class="col-xl-6 col-lg-8 col-12">
                                <form action="{{ url('employees') }}" method="POST" class="data-form">
                                    @csrf
        
                                    <h4 class="page__subtitle">Add New</h4>
                        
                                    <div class="mb-3">
                                        <label for="">User Type <span class="text-danger">*</span></label>
                                        <select id="user_type" name="type" class="form-control">
                                            <option value="" selected disabled>Select</option>
                                            <option value="1">VP</option>
                                            <option value="2" >RSM</option>
                                            <option value="3" >ASM</option>
                                            <option value="4" >ASE</option>
                                        </select>
                                        
                                        @error('type') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="designation">Designation <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="designation" name="designation" placeholder="" value="{{ old('designation') ? old('designation') : '' }}">
                                        @error('designation') <p class="small text-danger">{{$message}}</p> @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="employee_id">Employee ID<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="employee_id" name="employee_id" placeholder="" value="{{ old('employee_id') ? old('employee_id') : '' }}">
                                        @error('employee_id') <p class="small text-danger">{{$message}}</p> @enderror
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="label-control">Full Name <span class="text-danger">*</span> </label>
                                        <input type="text" name="name" placeholder="" class="form-control" value=" {{old('name')}}">
                                        @error('name') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label class="label-control">Official Email  </label>
                                        <input type="email" name="email" placeholder="" class="form-control" value="{{old('email')}}">
                                        @error('email') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label class="label-control">Personal Email  </label>
                                        <input type="email" name="personal_mail" placeholder="" class="form-control" value="{{old('personal_mail')}}">
                                        @error('personal_mail') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label class="label-control">Contact <span class="text-danger">*</span> </label>
                                        <input type="number" name="mobile" placeholder="" class="form-control" value="{{old('mobile')}}">
                                        @error('mobile') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label class="label-control">WhatsApp Number </label>
                                        <input type="number" name="whatsapp_no" placeholder="" class="form-control" value="{{old('whatsapp_no')}}">
                                        @error('whatsapp_no') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label class="label-control">Alt. Mobile 1 </label>
                                        <input type="number" name="alt_number1" placeholder="" class="form-control" value="{{old('alt_number1')}}">
                                        @error('alt_number1') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label class="label-control">Alt. Mobile 2 </label>
                                        <input type="number" name="alt_number2" placeholder="" class="form-control" value="{{old('alt_number2')}}">
                                        @error('alt_number2') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label class="label-control">Alt. Mobile 3 </label>
                                        <input type="number" name="alt_number3" placeholder="" class="form-control" value="{{old('alt_number3')}}">
                                        @error('alt_number3') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="state">State <span class="text-danger">*</span></label>
                                        <select class="form-select select2" id="state" name="state" aria-label="Floating label select example">
                                            <option value="" selected >Select</option>
                                            @foreach ($state as $index => $item)
                                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('state') <p class="small text-danger">{{$message}}</p> @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="area">City/ Area</label>
                                        <select class="form-select select2" id="area" name="area" aria-label="Floating label select example" disabled>
                                            <option value="">Select State first</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="label-control">Date of Joining </label>
                                        <input type="date" name="date_of_joining" placeholder="" class="form-control" value="{{old('date_of_joining')}}">
                                        @error('date_of_joining') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label class="label-control">Password <span class="text-danger">*</span> </label>
                                        <input type="password" name="password" placeholder="" class="form-control" value="{{old('password')}}">
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
    $('select[name="state"]').on('change', (event) => {
        var value = $('select[name="state"]').val();
      
        $.ajax({
            url: '{{url("/")}}/areas/state/wise/'+value,
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

