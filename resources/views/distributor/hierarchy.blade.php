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
                            Distributor Hierarchy
                            @can('distributor hierarchy csv export')
                            <a href="{{ url('distributors/hierarchy/exportCSV',['brand'=>$request->brand,'keyword'=>$request->keyword]) }}" class="btn btn-sm btn-cta ms-auto" data-bs-toggle="tooltip" title="Export data in CSV">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                CSV
                            </a>
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
                                            <form action="">
                                                <div class="row">
                                                    @if($brandPermissions=='Both')
                                                    <div class="col">
                                                        <label class="small text-muted">Brand</label>
                                                        <select class="form-select form-select-sm" aria-label="Default select example" name="brand" id="brand">
                                                            <option value="" selected disabled>Select</option>
                                                                 <option value="3" {{ (request()->input('brand') == 3) ? 'selected' : '' }}>All</option>
                                                            
                                                                <option value="1" {{ (request()->input('brand') == 1) ? 'selected' : '' }}>ONN</option>
                                                                <option value="2" {{ (request()->input('brand') == 2) ? 'selected' : '' }}>PYNK</option>
                                                                
                                                                
                                                        </select>
                                                    </div>
                                                     @endif
                                                    
                                                    <div class="col">
                                                        <label class="small text-muted">Keyword</label>
                                                        <input type="search" name="keyword" id="term" class="form-control form-control-sm" placeholder="Search by keyword." value="{{app('request')->input('keyword')}}" autocomplete="off">
                                                    </div>
                                                </div>
                                                <div class="row mt-2">
                                                    <div class="col-12 text-end">
                                                        <!--<div class="btn-group books_btn_group">-->
                                                            <button type="submit" class="btn btn-sm btn-cta">
                                                                Filter
                                                            </button>
                            
                                                            <a href="{{ url()->current() }}" class="btn btn-sm btn-cta" data-bs-toggle="tooltip" title="Clear Filter">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                                            </a>
                                                        <!--</div>-->
                                                    </div>
                                                </div>
                                            </form>
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
                                        <th>Brand</th>
                                        <th>Distributor</th>
                                        <th>State</th>
                                        <th>Area</th>
                                        <th>VP</th>
                                        <th>RSM</th>
                                        <th>ASM</th>
                                        <th>ASE</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    
                                    @forelse ($data as $index => $item)
                                        @php
                                            $findDistributorTeamDetails = findDistributorTeamDetails($item->id);
                                           
                                        @endphp

                                       

                                        <tr>
                                            <td class="index-col">{{  $index + 1 }}</td>

                                            <td>
                                                @php
                                                    $brandMap = [
                                                        1 => 'ONN',
                                                        2 => 'PYNK',
                                                        3 => 'Both',
                                                    ];

                                                    $brands = [$item->brand];

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
                                            <td>{{ $item->name ?? '' }}</td>
                                            <td>{{ $findDistributorTeamDetails[0]['state'] ?? '' }}</td>
                                            <td>{{ $findDistributorTeamDetails[0]['area'] ?? '' }}</td>
                                            <td>{{ $findDistributorTeamDetails[0]['vp'] ?? '' }}</td>
                                            <td>{{ $findDistributorTeamDetails[0]['rsm'] ?? '' }}</td>
                                            <td>{{ $findDistributorTeamDetails[0]['asm'] ?? '' }}</td>
                                            <td>{{ $findDistributorTeamDetails[0]['ase'] ?? '' }}</td>
                                            
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
                    Employee Details Bulk Upload
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="{{ url('employees/bulk/upload') }}" enctype="multipart/form-data">@csrf
                        <input type="file" name="file" class="form-control" accept=".csv">
                        <div class="cta-row">
                        <a href="{{ asset('backend/csv/sample-employee.csv') }}" class="btn-cta">Download Sample CSV</a>
                        <button type="submit" class="btn btn-cta" id="csvImportBtn">Import <i class="fas fa-upload"></i></button>
                        </div>
                    </form>
                </div>
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