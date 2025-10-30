@extends('layouts.app')
<style>
.nav-tabs .nav-link.active {
    background-color: #ff007f; /* pink for PYNK, optional */
    color: #fff;
}
</style>
@section('content')


<div class="container mt-2">
        <div class="row">
            <div class="col-md-12">

                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                <ul class="alert alert-warning">
                    @foreach ($errors->all() as $error)
                        <li>{{$error}}</li>
                    @endforeach
                </ul>
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
                        <h4 class="d-flex">
                            {{$distributor->name}} Range/Collection
                            
                            <a href="{{ url('distributors') }}" class="btn btn-cta ms-auto">Back</a>
                            @can('create distributor range')
                            <a href="#newRangeModal" data-bs-toggle="modal" class="btn btn-sm btn-danger">Add Range</a>
                            @endcan
                        </h4>
                                <div class="search__filter mb-0">
                                    <div class="row">
                                        <div class="col-12">
                                            <p class="text-muted mt-1 mb-0">Showing {{$data->count()}} out of {{$data->total()}} Entries</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        
                                        <div class="col-12">
                                            
                                        </div>
                                        
                                    </div>
                                </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th class="sl_no index-col">#</th>
                                        <th>Brand Permission</th>
                                        <th>Range</th>
                                        <th>ASE</th>
                                        <th>Created At</th>
                                        <th class="action_btn">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($data as $index => $item)
                                    
                                    
                                    <tr>
                                        <td class="index-col">{{ $index + 1 }}</td>
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
                                        <td>
                                            {{ $item->range->name ?? '' }}
                                            
                                        </td>
                                         <td>
                                            {{ $item->ase->name ?? '' }}
                                            
                                        </td>
                                        
                                        
                                       
                                        
                                       
                                        <td>{{date('d-m-Y', strtotime($item->created_at))}}</td>
                                       
                                        <td style="white-space: nowrap;">
                                            
                                            @can('delete distributor range')
                                            <a  href="{{ url('distributors/'.$item->id.'/range/delete') }}" class="btn btn-cta delete-confirm">
                                                <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="512" height="512" x="0" y="0" viewBox="0 0 24 24" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="M19 7a1 1 0 0 0-1 1v11.191A1.92 1.92 0 0 1 15.99 21H8.01A1.92 1.92 0 0 1 6 19.191V8a1 1 0 0 0-2 0v11.191A3.918 3.918 0 0 0 8.01 23h7.98A3.918 3.918 0 0 0 20 19.191V8a1 1 0 0 0-1-1ZM20 4h-4V2a1 1 0 0 0-1-1H9a1 1 0 0 0-1 1v2H4a1 1 0 0 0 0 2h16a1 1 0 0 0 0-2ZM10 4V3h4v1Z" fill="#ffffff" opacity="1" data-original="#000000" class=""></path><path d="M11 17v-7a1 1 0 0 0-2 0v7a1 1 0 0 0 2 0ZM15 17v-7a1 1 0 0 0-2 0v7a1 1 0 0 0 2 0Z" fill="#ffffff" opacity="1" data-original="#000000" class=""></path></g></svg>
                                            </a>
                                            @endcan
                                            
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No record found</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                         {{ $data->appends($_GET)->render() }}
                        
                    </div>

                </div>
            </div>
        </div>
    </div>

                    
    <div class="modal action-modal fade" id="csvModal" data-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    Distributor Details Bulk Upload
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="{{ url('distributors/bulk/upload') }}" enctype="multipart/form-data">@csrf
                        <input type="file" name="file" class="form-control" accept=".csv">
                        <div class="cta-row">
                        <a href="{{ asset('backend/csv/sample-distributors.csv') }}" class="btn-cta">Download Sample CSV</a>
                        <button type="submit" class="btn btn-cta" id="csvImportBtn">Import <i class="fas fa-upload"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>



    <div class="modal fade" id="newRangeModal" tabindex="-1" aria-labelledby="newRangeModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="newRangeModalLabel">Add new Range</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form action="{{ url('distributors/'.$distributor->id.'/range/add') }}" method="post">@csrf
                    <div class="row">
                            <div class="col-12">
                                <label class="small text-muted">Brand</label>
                                <select class="form-select form-select-sm" aria-label="Default select example" name="brand" id="brand">
                                    <option value="" selected disabled>Select</option>
                                            <option value="3" {{ (request()->input('brand') == 3) ? 'selected' : '' }}>All</option>
                                    
                                        <option value="1" {{ (request()->input('brand') == 1) ? 'selected' : '' }}>ONN</option>
                                        <option value="2" {{ (request()->input('brand') == 2) ? 'selected' : '' }}>PYNK</option>
                                        
                                        
                                </select>
                            </div>
                        <div class="col-12">
                            <label for="collection_id" class="small text-muted">Select Range</label>
                            <select name="collection_id" id="collection_id" class="form-select form-select-sm">
                                 <option value="" selected disabled>Select</option>
                                @foreach($collections as $collection)
                                    <option value="{{ $collection->id }}">{{ $collection->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="user_id" class="small text-muted">Select ASE</label>
                            <select name="user_id" id="user_id" class="form-control form-control-sm">
                                <option value="" selected disabled>Select</option>
                                @foreach($aseList as $aseD)
                                    @php
                                        $brandMap = [1 => 'ONN', 2 => 'PYNK', 3 => 'Both'];
                                        $brandPermissions = $brandMap[$aseD->ase->brand] ?? 'Unknown';
                                        // Logged-in user permission (fetched from user_permission_categories table)
                                        $userPermission = \App\Models\UserPermissionCategory::where('user_id', auth()->id())
                                            ->value('brand'); // assuming column name is 'brand' in user_permission_categories

                                        $userBrandPermission = $brandMap[$userPermission] ?? 'Unknown';
                                    @endphp
                                    <option value="{{ $aseD->ase->id }}" data-name="{{ $aseD->ase->name }}">{{ $aseD->ase->name }}({{$brandPermissions}}) ({{ $aseD->ase->area->name }}, {{ $aseD->ase->stateDetail->name }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 mt-3">
                            
                            <input type="hidden" name="distributor_id" value="{{ $distributor->id }}">
                            <button type="submit" class="btn btn-sm btn-danger">Add Range</button>
                        </div>
                    </div>
                </form>
			</div>
			{{-- <div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary">Save changes</button>
			</div> --}}
		</div>
	</div>
</div>
@endsection


@section('script')


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

    $(document).on('click', '.brand-tab', function() {
    var brand = $(this).data('brand');
    
    // Highlight active tab
    $('.brand-tab').removeClass('active');
    $(this).addClass('active');

    // Fetch employees via AJAX
    $.ajax({
        url: '{{ url("employees") }}',
        method: 'GET',
        data: { brand: brand },
        success: function(response) {
            // replace table body
        },
        error: function() {
            alert('Failed to load employees.');
        }
    });
});

</script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll('.delete-confirm').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault(); // stop normal link

            let url = this.getAttribute('href');

            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url; // redirect if confirmed
                }
            });
        });
    });
});
</script>

@endsection