@extends('layouts.app')

@section('content')
<div class="container mt-2">
    <div class="row">
        <div class="col-md-12">

            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            @php
                            $assignedPermissions = DB::table('user_permission_categories')
                            ->select('user_permission_categories.*')
                            ->join('users','users.id','=','user_permission_categories.user_id')
                            ->where('user_permission_categories.user_id', Auth::user()->id)
                            ->get();

                            $brandMap = [
                                1 => 'ONN',
                                2 => 'PYNK',
                                3 => 'Both',
                            ];

                            $brands = $assignedPermissions->pluck('brand')->unique()->toArray();

                            // Check conditions
                                if (in_array(3, $brands)) {
                                    $brandPermissions = 'Both';
                                } elseif (in_array(1, $brands) && in_array(2, $brands)) {
                                    $brandPermissions = 'Both';
                                } else {
                                    $brandPermissions = collect($brands)
                                        ->map(fn($brand) => $brandMap[$brand] ?? $brand)
                                        ->implode(', ');
                                }
                                @endphp
                <div class="card data-card mt-3">
                    <div class="card-header">
                        <h4 class="d-flex">Store Login Count State Wise
                            <a href="{{ route('reward.retailer.user.loginCountExportCSV', request()->all()) }}" class="btn btn-sm btn-cta ms-auto" data-bs-toggle="tooltip" title="Export data in CSV">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                CSV
                            </a>
                        </h4>

                        <div class="search__filter mb-0">
                            <div class="row">
                                <div class="col-12">
                                    <p class="text-muted mt-1 mb-0">Showing {{$loginCountWiseReport->count()}} Entries</p>
                                </div>
                                
                            </div>
                            <div class="row">
                                        
                                <div class="col-12">
                                    <form action="{{route('reward.retailer.user.loginCount')}}">
                                        <div class="row g-2 align-items-center">
                                            @if($brandPermissions=='Both')
                                            <div class="col-4">
                                                 <label class="small text-muted">Brand</label>
                                                <select name="brand_selection" class="form-control form-control-sm">
                                                    <option value="">Select Brand</option>
                                                    <option value="3" {{ request()->input('brand_selection') == 3 ? 'selected' : '' }}>ALL</option>
                                                    <option value="1" {{ request()->input('brand_selection') == 1 ? 'selected' : '' }}>ONN</option>
                                                    <option value="2" {{ request()->input('brand_selection') == 2 ? 'selected' : '' }}>PYNK</option>
                                                </select>
                                                
                                            </div>
                                            @endif
                                            <div class="col-4">

                                             <label class="small text-muted">State</label>
                                                 <label for="state" class="small text-muted">State</label>
                                                        <select name="state_id" id="state" class="form-select form-select-sm select2">
                                                            <option value="" disabled>Select</option>
                                                            <option value="" selected>All</option>
                                                            @foreach ($states as $state)
                                                                <option value="{{$state->id}}" {{ request()->input('state') == $state->id ? 'selected' : '' }}>{{$state->name}}</option>
                                                            @endforeach
                                                        </select>
                                            </div>


                                            <div class="col-4 text-end">
                                                <button type="submit" class="btn btn-sm btn-cta">Filter</button>
                                                <a href="{{ url()->current() }}" 
                                                class="btn btn-sm btn-cta" 
                                                data-bs-toggle="tooltip" 
                                                title="Clear Filter">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" 
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" 
                                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" 
                                                        class="feather feather-x">
                                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>

                                       
                                    </form>
                                </div>
                                        
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table no-sticky" id="example5">
                                <thead>
                                    <tr>
                                    
                                        <th>State</th>
                                        <th>Count</th>
                                        <th></th>
                                        
                                    </tr>
                                </thead>
                                <tbody>
                                            
                                    @foreach ($loginCountWiseReport as $aseKey => $item)
                                    
                                                    @php
                                                            $stateData=DB::table('states')->where('id',$item->state_id)->first();
                                                    
                                                        @endphp
                                                            <tr>
                        
                                                                <td>
                        
                                                                    {{$stateData->name ??''}}
                                                                </td>
                                                                <td> {{number_format($item->count)}}</td>
                                                                @if(!empty($stateData))
                                                                <td> <a href="{{route('reward.retailer.user.login.store.count',$stateData->id)}}" class="btn btn-primary">View</a></td>
                                                                @endif
                                                            </tr>
                                                            
                                                        @endforeach
                                      
                                </tbody>
                            </table>     
                            
                            
                        </div>



    
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });
    </script>
    <script>
		function stateWiseArea(value) {
			$.ajax({
				url: '{{url("/")}}/state-wise-area/'+value,
                method: 'GET',
                success: function(result) {
					var content = '';
					var slectTag = 'select[name="area"]';
					var displayCollection = (result.data.state == "all") ? "All Area" : "All "+" area";
					content += '<option value="" selected>'+displayCollection+'</option>';
					
					let cat = "{{ app('request')->input('area') }}";

					$.each(result.data.area, (key, value) => {
						if(value.area == '') return;
						if (value.area == cat) {
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

		@if(request()->input('state'))
		stateWiseArea("{{ request()->input('state') }}");
		@endif

		function typeWiseUser(value){
			$.ajax({
				url: '{{url("/")}}/type-wise-name/'+value,
                method: 'GET',
                success: function(result) {
					var content = '';
					var slectTag = 'select[id="name"]';
					var displayCollection = (result.data.type == "all") ? "All " : "All "+" name";
					content += '<option value="" selected>'+displayCollection+'</option>';
					let type = "{{ app('request')->input('name') }}";

					$.each(result.data.name, (key, value) => {
						if(value.name == '') return;
						if (value.name == type) {
                            content += '<option value="'+value.name+'" selected>'+value.name+'</option>';
                        } else {
                            content += '<option value="'+value.name+'">'+value.name+'</option>';
                        }
						// content += '<option value="'+value.name+'">'+value.name+'</option>';
					});
					$(slectTag).html(content).attr('disabled', false);
                }
			});
		}

		$('select[id="type"]').on('change', (event) => {
			var value = $('select[id="type"]').val();
			typeWiseUser(value);
		});
		
		@if(request()->input('user_type'))
		typeWiseUser("{{ request()->input('user_type') }}");
		@endif
   </script>
@endsection
