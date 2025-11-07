@php
    $state = DB::select("SELECT name FROM `states` GROUP BY name ORDER BY name");
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
                            <form method="POST" action="" enctype="multipart/form-data">@csrf
                                <div class="row mb-2">
                                    <div class="col-12">
                                        <p class="small text-muted mb-2">TYPE</p>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <div class="form-floating mb-3">
                                                <select class="form-select" id="user_type" name="user_type">
                                                    <option value="" selected disabled>Select</option>
                                                    <option value="1" {{ ($data->user_type == 1) ? 'selected' : '' }}>VP</option>
                                                    <option value="2" {{ ($data->user_type == 2) ? 'selected' : '' }}>RSM</option>
                                                    <option value="3" {{ ($data->user_type == 3) ? 'selected' : '' }}>ASM</option>
                                                    <option value="4" {{ ($data->user_type == 4) ? 'selected' : '' }}>ASE</option>
                                                    <option value="5" {{ ($data->user_type == 5) ? 'selected' : '' }}>Distributor</option>
                                                    <option value="6" {{ ($data->user_type == 6) ? 'selected' : '' }}>Retailer</option>
                                                </select>
                                                <label for="mobile">Type *</label>
                                            </div>
                                            @error('mobile') <p class="small text-danger">{{$message}}</p> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control" id="designation" name="designation" placeholder="name@example.com" value="{{ old('designation') ? old('designation') : $data->designation }}">
                                                <label for="designation">Designation *</label>
                                            </div>
                                            @error('designation') <p class="small text-danger">{{$message}}</p> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control" id="employee_id" name="employee_id" placeholder="name@example.com" value="{{ old('employee_id') ? old('employee_id') : $data->employee_id }}">
                                                <label for="employee_id">Employee ID</label>
                                            </div>
                                            @error('employee_id') <p class="small text-danger">{{$message}}</p> @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-2">
                                    <div class="col-12">
                                        <p class="small text-muted mb-2">Name details</p>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <div class="form-floating mb-3">
                                                <select class="form-select" id="title" name="title" aria-label="Floating label select example">
                                                    <option value="" selected disabled>Select</option>
                                                    <option value="" selected>NA</option>
                                                    <option value="Mr" {{ ($data->title == "Mr") ? 'selected' : '' }}>Mr</option>
                                                    <option value="Miss" {{ ($data->title == "Miss") ? 'selected' : '' }}>Miss</option>
                                                    <option value="Mrs" {{ ($data->title == "Mrs") ? 'selected' : '' }}>Mrs</option>
                                                    <option value="Dr" {{ ($data->title == "Dr") ? 'selected' : '' }}>Dr</option>
                                                    <option value="CA" {{ ($data->title == "CA") ? 'selected' : '' }}>CA</option>
                                                    <option value="Prof" {{ ($data->title == "Prof") ? 'selected' : '' }}>Prof</option>

                                                    {{-- <option value="Mr" {{ (old('title') == "Mr" || old('title') == "") ? 'selected' : '' }}>Mr</option>
                                                    <option value="Miss" {{ (old('title') == "Miss") ? 'selected' : '' }}>Miss</option>
                                                    <option value="Mrs" {{ (old('title') == "Mrs") ? 'selected' : '' }}>Mrs</option>
                                                    <option value="Dr" {{ (old('title') == "Dr") ? 'selected' : '' }}>Dr</option>
                                                    <option value="CA" {{ (old('title') == "CA") ? 'selected' : '' }}>CA</option>
                                                    <option value="Prof" {{ (old('title') == "Prof") ? 'selected' : '' }}>Prof</option> --}}
                                                </select>
                                                <label for="title">Name Prefix</label>
                                            </div>
                                            @error('title') <p class="small text-danger">{{$message}}</p> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control" id="name" name="name" placeholder="name@example.com" value="{{ old('name') ? old('name') : $data->name }}">
                                                <label for="name">Full name *</label>
                                            </div>
                                            @error('name') <p class="small text-danger">{{$message}}</p> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control" id="fname" name="fname" placeholder="name@example.com" value="{{ old('fname') ? old('fname') : $data->fname }}">
                                                <label for="fname">First name *</label>
                                            </div>
                                            @error('fname') <p class="small text-danger">{{$message}}</p> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control" id="lname" name="lname" placeholder="name@example.com" value="{{ old('lname') ? old('lname') : $data->lname }}">
                                                <label for="lname">Last name *</label>
                                            </div>
                                            @error('lname') <p class="small text-danger">{{$message}}</p> @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-2">
                                    <div class="col-12">
                                        <p class="small text-muted mb-2">Contact details</p>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <div class="form-floating mb-3">
                                                <input type="number" class="form-control" id="mobile" name="mobile" placeholder="name@example.com" value="{{ old('mobile') ? old('mobile') : $data->mobile }}">
                                                <label for="mobile">Mobile number *</label>
                                            </div>
                                            @error('mobile') <p class="small text-danger">{{$message}}</p> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <div class="form-floating mb-3">
                                                <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" value="{{ old('email') ? old('email') : $data->email }}">
                                                <label for="email">Email ID</label>
                                            </div>
                                            @error('email') <p class="small text-danger">{{$message}}</p> @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-2">
                                    <div class="col-12">
                                        <p class="small text-danger mb-2">Update Password</p>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control" id="password" name="password" placeholder="name@example.com" value="">
                                                <label for="password">Password</label>
                                            </div>
                                            @error('password') <p class="small text-danger">{{$message}}</p> @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-2">
                                    <div class="col-12">
                                        <p class="small text-muted mb-2">Location details</p>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <div class="form-floating mb-3">
                                                <select class="form-select" id="state" name="state" aria-label="Floating label select example">
                                                    <option value="" selected disabled>Select</option>
                                                    @foreach ($state as $index => $item)
                                                        <option value="{{ $item->name }}" {{ (strtolower($data->state) == strtolower($item->name)) ? 'selected' : '' }}>
                                                            {{ $item->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <label for="state">State *</label>
                                            </div>
                                            @error('state') <p class="small text-danger">{{$message}}</p> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <div class="form-floating mb-3">
                                                <select class="form-select" id="area" name="area" aria-label="Floating label select example" disabled>
                                                    <option value="">Select State first</option>
                                                </select>
                                                <label for="area">City/ Area *</label>
                                            </div>
                                            @error('area') <p class="small text-danger">{{$message}}</p> @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-danger">Update changes</button>
                                    </div>
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
        function stateWiseArea(value) {
			$.ajax({
				url: '{{url("/")}}/state-wise-area/'+value,
                method: 'GET',
                success: function(result) {
					var content = '';
					var slectTag = 'select[name="area"]';
					// var displayCollection = (result.data.state == "all") ? "All Area" : "All "+" area";
					// content += '<option value="" selected>'+displayCollection+'</option>';

					let cat = "{{ strtolower($data->city) }}";

					$.each(result.data.area, (key, value) => {
						if(value.area == '') return;
						if (value.area.toLowerCase() == cat) {
                            content += '<option value="'+value.area+'" selected>'+value.area+'</option>';
                        } else {
                            content += '<option value="'+value.area+'">'+value.area+'</option>';
                        }
						//content += '<option value="'+value.area+'">'+value.area+'</option>';
					});
					$(slectTag).html(content).attr('disabled', false);
                }
			});
		}

		$('select[name="state"]').on('change', (event) => {
			var value = $('select[name="state"]').val();
			stateWiseArea(value);
		});

        @if(!empty($data->state))
            stateWiseArea('{{$data->state}}')
        @endif
    </script>
@endsection
