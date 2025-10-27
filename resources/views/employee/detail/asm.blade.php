@extends('layouts.app')
@section('page', '')

@section('content')
@php
    $brandMap = [1 => 'ONN', 2 => 'PYNK', 3 => 'Both'];
    $brandPermissions = $brandMap[$data->employee->brand] ?? 'Unknown';
     // Logged-in user permission (fetched from user_permission_categories table)
    $userPermission = \App\Models\UserPermissionCategory::where('user_id', auth()->id())
        ->value('brand'); // assuming column name is 'brand' in user_permission_categories

    $userBrandPermission = $brandMap[$userPermission] ?? 'Unknown';


    
    
@endphp

<style>
    .working_area {
        display: inline-flex;
        align-items: center;
        background: #f8f9fa;
        border-radius: 6px;
        padding: 6px 12px;
        margin-right: 6px;
        color: #000;
        text-decoration: none;
        font-size: 14px;
    }
    .working_area svg { margin-right: 6px; }
    .select2-container { width: 100% !important; z-index: 9999; }
</style>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-12">

            {{-- Error Messages --}}
            @if ($errors->any())
                <div class="alert alert-warning">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

        <div class="card data-card">
                    <div class="card-header">
                    
                     <h4 class="d-flex">ASM Detail
                        <a href="{{ url('employees') }}" class="btn btn-cta ms-auto">Back</a>
                        <a href="{{ url('employees/'.$data->employee->id.'/edit') }}" class="btn btn-cta">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        
                    </h4>
                </div>
            <div class="card-body">

                {{-- HEADER --}}
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        
                        <span class="badge bg-primary fs-5">{{ $data->employee->designation }}</span>
                    </div>
                    <div>
                        
                    </div>
                </div>

                {{-- PRIMARY INFO --}}
                <h5 class="text-primary border-bottom pb-2">Primary Information</h5>
                <div class="row mb-4">
                    <div class="col-md-3"><strong>Brand:</strong> {{ $brandPermissions }}</div>
                    <div class="col-md-3"><strong>Name:</strong> {{ $data->employee->name }}</div>
                    <div class="col-md-3"><strong>Mobile:</strong> {{ $data->employee->mobile }}</div>
                    <div class="col-md-3"><strong>Official Email:</strong> {{ $data->employee->email }}</div>
                    <div class="col-md-3"><strong>Code:</strong> {{ $data->employee->employee_id }}</div>
                    <div class="col-md-3"><strong>Date of Joining:</strong> {{ date('d-m-Y', strtotime($data->employee->date_of_joining)) }}</div>
                </div>

                {{-- LOCATION INFO --}}
                <h5 class="text-primary border-bottom pb-2">Location Information</h5>
                <div class="row mb-4">
                    <div class="col-md-3"><strong>State:</strong> {{ $data->employee->stateDetail->name ?? 'N/A' }}</div>
                    <div class="col-md-3"><strong>Area:</strong> {{ $data->employee->area->name ?? 'N/A' }}</div>
                    
                </div>

                

                {{-- CONTACT INFO --}}
                <h5 class="text-primary border-bottom pb-2">Optional Contact Information</h5>
                <div class="row mb-4">
                    <div class="col-md-3"><strong>Alt. Mobile 1:</strong> {{ $data->employee->alt_number1 ?? 'NA' }}</div>
                    <div class="col-md-3"><strong>Alt. Mobile 2:</strong> {{ $data->employee->alt_number2 ?? 'NA' }}</div>
                    <div class="col-md-3"><strong>Alt. Mobile 3:</strong> {{ $data->employee->alt_number3 ?? 'NA' }}</div>
                    <div class="col-md-3"><strong>Personal Email:</strong> {{ $data->employee->personal_mail ?? 'NA' }}</div>
                </div>

                {{-- OTHERS --}}
                <h5 class="text-primary border-bottom pb-2">Others</h5>
                <div class="row mb-4">
                    <div class="col-md-3"><strong>Created / Edited By:</strong> {{ $data->employee->createdBy->name ?? 'N/A' }}</div>
                    <div class="col-md-3"><strong>Created At:</strong> {{ date('d-m-Y', strtotime($data->employee->created_at)) }}</div>
                </div>

            </div>
        </div>

        {{-- DISTRIBUTOR DETAILS --}}
        <div class="card shadow-sm mb-4">
            
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="m-0">Team information</h4>
                <div class="col-12">
                    <p class="text-dark small">
                        States: {{asmStates($data->employee->id)->count()}}
                        | Area: {{asmAreaCount($data->employee->id)->count()}}
                        | VP: {{asmVp($data->employee->id)->count()}}
                        | RSM: {{asmRSMCount($data->employee->id)->count()}}
                        | ASE: {{asmASECount($data->employee->id)->count()}}
                    </p>
                </div>
                
            </div>
            
            <div class="card-body">
                <div class="col-12 mb-3">
                            <form class="row align-items-end justify-content-end" action="">
                                @if($brandPermissions == 'Both'  && $userBrandPermission === 'Both')
                                <div class="col-auto">
                                    <label class="small text-muted">Brand</label>
                                    <select class="form-select form-select-sm" name="brand" id="brand">
                                        <option value="" >Select</option>
                                        
                                        <option value="1" {{ ($request->brand == 1) ? 'selected' : '' }}>ONN</option>
                                        <option value="2" {{ ($request->brand == 2) ? 'selected' : '' }}>PYNK</option>
                                        <option value="3" {{ ($request->brand == 3) ? 'selected' : '' }}>Both</option>
                                        
                                        
                                    </select>
                                </div>
                                @endif
                                <div class="col-auto">
                                    <label class="small text-muted">User type</label>
                                    <select class="form-select form-select-sm" name="user_type" id="type">
                                        <option value="" disabled>Select</option>
                                        <option value="" selected>All</option>
                                        <option value="1" {{ ($request->user_type == 1) ? 'selected' : '' }} >VP</option>
                                        <option value="2" {{ ($request->user_type == 2) ? 'selected' : '' }} >RSM</option>
                                        <option value="3" {{ ($request->user_type == 3) ? 'selected' : '' }} disabled>ASM</option>
                                        <option value="4" {{ ($request->user_type == 4) ? 'selected' : '' }}>ASE</option>
                                        
                                    </select>
                                </div>
                                {{--<div class="col-auto">
                                    <label class="small text-muted">User name</label>
                                    <select class="form-select form-select-sm" name="user_name" id="user_name" disabled>
                                        <option value="{{ $request->user_name }}">Select Type first</option>
                                    </select>
                                </div>--}}
                                <div class="col-auto">
                                    <label class="small text-muted">State</label>
                                    <select class="form-control form-control-sm" name="state" id="state">
                                        <option value="" disabled>Select</option>
                                        <option value="all" selected>All</option>
                                        @foreach ($state as $index => $item)
                                            <option value="{{ $item->id }}" {{ ($request->state == $item->id) ? 'selected' : '' }}>{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <label class="small text-muted">Area</label>
                                    <select class="form-control form-control-sm" name="area" id="area" disabled>
                                        <option value="{{ $request->area }}">Select Area first</option>
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <label class="small text-muted">Search</label>
                                    <input type="search" name="keyword" id="term" class="form-control form-control-sm" placeholder="name" value="{{app('request')->input('keyword')}}" autocomplete="off">
                                </div>
                                <div class="col-auto">
                                    <div class="btn-group">
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            Filter
                                        </button>

                                        <a href="{{ url()->current() }}" class="btn btn-sm btn-light" data-bs-toggle="tooltip" title="Clear Filter">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                        </a>

                                         <a href="" id="downloadCsv" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Export data in CSV">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                        </a> 
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="col-12">
                            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>#SR</th>
                                            <th>Brand</th>
                                            <th>State</th>
                                            <th>Area</th>
                                            <th>RSM</th>
                                            <th>ASM</th>
                                            <th>ASE</th>
                                            <th>Distributor</th>
                                            <th>Retailer</th>
                                            @can('team delete')
                                            <th>Action</th>
                                            @endcan
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($data->team as $index => $item)
                                            <tr>
                                                <td>{{$index + $data->team->firstItem()}}</td>
                                                <td>
                                                    @php
                                               

                                                    $brandMap = [
                                                        1 => 'ONN',
                                                        2 => 'PYNK',
                                                        3 => 'Both',
                                                    ];

                                                    $brands = [$item->brand];

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

                                                    {{ $brandPermissions ?? '' }}
                                                </td>
                                                <td>{{$item->states->name}}</td>
                                                <td>{{$item->areas->name}}</td>
                                                <td>{{$item->rsm->name}}</td>
                                                <td>{{$item->asm->name}}</td>
                                                <td>{{$item->ase->name}}</td>
                                                <td>{{$item->distributor->name}}</td>
                                                <td>{{$item->store->name ??''}}</td>
                                                @can('team delete')
                                                <td><a href="{{ route('team.delete', $item->id) }}" class="text-danger" onclick="return confirm('Are you sure ?')">Delete</a></td>
                                                @endcan
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="100%" class="small text-muted text-center">No records found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-end">
                                {{$data->team->appends($_GET)->links()}}
                            </div>
                        </div>
            </div>
           
        </div>

        
    </div>
</section>



@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(function() {
    $('.select2').select2();

    

    // Pass checked stores to transfer modal
    $('#transferForm').on('submit', function() {
        $('#hiddenInputsContainer').empty();
        $('input[name="status_check[]"]:checked').each(function() {
            $('#hiddenInputsContainer').append(`<input type="hidden" name="status_check[]" value="${$(this).val()}">`);
        });
    });
});
</script>
<script>
    $(document).on('change', '#brand', function() {
        var brand = $(this).val();
        var url = "{{ url('employees') }}/{{ $id }}";

        $.ajax({
            url: url,
            type: 'GET',
            data: { brand: brand },
            success: function(response) {
                // Replace only the distributor list part from the response
                var newList = $(response).find('#distributorList').html();
                $('#distributorList').html(newList);
            },
            error: function() {
                alert('Something went wrong.');
            }
        });
    });

    $(document).on('change', '#storebrand', function() {
        var storebrand = $(this).val();
        var url = "{{ url('employees') }}/{{ $id }}";

        $.ajax({
            url: url,
            type: 'GET',
            data: { storebrand: storebrand },
            success: function(response) {
                // Replace only the distributor list part from the response
                var newList = $(response).find('#storeList').html();
                $('#storeList').html(newList);
            },
            error: function() {
                alert('Something went wrong.');
            }
        });
    });


    function typeWiseUser(value){
			$.ajax({
				url: '{{url("/")}}/employee/type-wise-name/'+value,
                method: 'GET',
                success: function(result) {
					var content = '';
					var slectTag = 'select[id="user_name"]';
					var displayCollection = (result.data.type == "all") ? "All " : "All "+" name";
					content += '<option value="" selected>'+displayCollection+'</option>';
					let type = "{{ app('request')->input('user_name') }}";

					$.each(result.data.name, (key, value) => {
						if(value.name == '') return;
						if (value.name == type) {
                            content += '<option value="'+value.id+'" selected>'+value.name+'</option>';
                        } else {
                            content += '<option value="'+value.id+'">'+value.name+'</option>';
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


        // Fetch areas dynamically
    $('select[name="state"]').on('change', function() {
        const stateId = $(this).val();
        $.get(`{{ url('/') }}/areas/state/wise/${stateId}`, function(result) {
            let options = '<option disabled selected>--Select City--</option>';
            $.each(result.data.area, (key, value) => {
                options += `<option value="${value.area_id}">${value.area}</option>`;
            });
            $('select[name="city"]').html(options).prop('disabled', false);
        });
    });
</script>
<script>
$(document).ready(function() {
    var selectedState = "{{ request()->input('state') ?? '' }}";
    var selectedArea = "{{ request()->input('area') ?? '' }}";

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
                var content = '<option value="">All</option>';
                $.each(result.data.area, function(key, val) {
                    var selected = (val.area_id == selectedArea) ? 'selected' : '';
                    content += '<option value="' + val.area_id + '" ' + selected + '>' + val.area + '</option>';
                });

                $('#area').html(content).prop('disabled', false);
            }
        });
    }
});
</script>
<script>
document.getElementById("downloadCsv").addEventListener("click", function () {
    let table = document.querySelector(".table"); // your table selector
    let rows = table.querySelectorAll("tr");
    let csv = [];

    // Loop through each row
    for (let i = 0; i < rows.length; i++) {
        let row = [];
        let cols = rows[i].querySelectorAll("th, td");

        // Loop through each cell
        for (let j = 0; j < cols.length; j++) {
            // Escape quotes
            let data = cols[j].innerText.replace(/"/g, '""');
            row.push('"' + data + '"');
        }
        csv.push(row.join(","));
    }

    // Create a CSV file
    let csvFile = new Blob([csv.join("\n")], { type: "text/csv" });

    // Create a download link
    let downloadLink = document.createElement("a");
    downloadLink.download = "team_information_asm.csv";
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = "none";

    // Trigger the download
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
});
</script>
@endsection
