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
                        <h4 class="d-flex">
                            New Register Store
                            <a href="{{ route('reward.retailer.user.exportCSV', request()->all()) }}" class="btn btn-sm btn-cta ms-auto" data-bs-toggle="tooltip" title="Export data in CSV">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                CSV
                            </a>
                        </h4>
                                <div class="search__filter mb-0">
                                    <div class="row">
                                        <div class="col-12">
                                            <p class="text-muted mt-1 mb-0">Showing {{$data->count()}} out of {{$data->total()}} Entries</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        
                                        <div class="col-12">
                                            <form action="{{route('reward.retailer.user.index')}}" method="GET">
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
                                                        <label for="date_from" class="small text-muted">Date from</label>
                                                        <input type="date" name="date_from" id="date_from" class="form-control form-control-sm" aria-label="Default select example" value="{{request()->input('date_from') ?? date('Y-m-01') }}">
                                                    </div>
                                                    <div class="col">
                                                        <label for="date_to" class="small text-muted">Date to</label>
                                                        <input type="date" name="date_to" id="date_to" class="form-control form-control-sm" aria-label="Default select example" value="{{request()->input('date_to') ?? date('Y-m-d') }}">
                                                    </div>
                                                    <div class="col">
                                                        <label for="distributor" class="small text-muted">Distributor</label>
                                                        <select class="form-select form-select-sm select2" id="distributor" name="distributor">
                                                            <option value="" selected disabled>Select</option>
                                                            @foreach ($allDistributors as $item)
                                                                <option value="{{$item->id}}" {{ (request()->input('distributor') == $item->id) ? 'selected' : '' }}>{{$item->name}}({{$item->states->name}})</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <!-- <div class="col">
                                                        <label for="ase" class="small text-muted">ASE</label>
                                                        <select class="form-select form-select-sm select2" id="ase" name="ase">
                                                            <option value="" selected disabled>Select</option>
                                                            @foreach ($allASEs as $item)
                                                                <option value="{{$item->id}}" {{ (request()->input('ase') == $item->id) ? 'selected' : '' }}>{{$item->name}}({{$item->stateDetail->name}})</option>
                                                            @endforeach
                                                        </select>
                                                    </div> -->
                                                    
                                                   
                                                    
                                                </div>
                                                <div class="row mt-2">
                                                     <div class="col">
                                                        <label for="state" class="small text-muted">State</label>
                                                        <select name="state" id="state" class="form-select form-select-sm select2">
                                                            <option value="" disabled>Select</option>
                                                            <option value="" selected>All</option>
                                                            @foreach ($state as $state)
                                                                <option value="{{$state->id}}" {{ request()->input('state') == $state->id ? 'selected' : '' }}>{{$state->name}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col">
                                                        <label class="small text-muted">Area</label>
                                                        <select class="form-select form-select-sm select2" id="area" name="area" disabled>
                                                            <option value="{{ $request->area }}">Select state first</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-3">
                                                        <label for="ase" class="small text-muted">Status</label>
                                                        <select class="form-select form-select-sm select2" id="status" name="status_id">
                                                            <option value="" >Select</option>
                                                            
                                                                <option value="active" {{ request()->input('status_id') == 'active' ? 'selected' : '' }}>Active</option>
                                                            <option value="inactive" {{ request()->input('status_id') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-3">
                                                                 <label for="ase" class="small text-muted">Keyword</label>
                                                                <input type="search" name="keyword" id="keyword" class="form-control form-control-sm" placeholder="Search by store name/ contact" value="{{request()->input('keyword')}}" autocomplete="off">
                                                            
                                                    </div>
                                                    
                                        
                                                </div>

                                                <div class="row mt-2">
                                                    
                                                    <div class="col-12 text-end">
                                                            
                                                            <button type="submit" class="btn btn-sm btn-cta">
                                                                Filter
                                                            </button>
                            
                                                            <a href="{{ url()->current() }}" class="btn btn-sm btn-cta" data-bs-toggle="tooltip" title="Clear Filter">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
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

                            <table class="table" id="example5">
                                <thead>
                                    <tr>
                                     
                                        <th>Uniquecode</th> 
                                        <th>Store</th>
                                        <th>Contact</th>
                                        <th>Address</th>
                                         
                                        <th>Status</th>
                                        <th>Date</th>
                                          <th class="action_btn">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($data as $index => $item)                                  
                                        @php
                                        if (!empty($_GET['status'])) {
                                            if ($_GET['status'] == 'active') {
                                                if ($item->status == 0) continue;
                                            } else {
                                                if ($item->status == 1) continue;
                                            }
                                        }

                                
                                        @endphp

                                        <tr>
                                           
                                            <td>
                                                {{ $item ? $item->unique_code : '' }}
                                            </td>
                                            <td>
                                                {{ ucwords($item->name) }}
                                                <p class="small text-muted">- {{ ucwords($item->bussiness_name) }}</p>
                                            </td>
                                            
                                            
                                            <td>{{ $item->email }}<br>{{ $item->contact }}</td>
                                           
                                            <td>{{ ucwords($item->address) }}<br>{{ $item->area->name }}<br>{{ $item->city }}<br>{{ $item->state->name }}
                                            </td>
                                            <td>
                                              
                                               
                                                    <span class="badge badge-status bg-{{ $item->status == 1 ? 'success' : 'danger' }}">{{ $item->status == 1 ? 'Active' : 'Inactive' }}</span>
                                                
                                              
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y g:i:s A')}}
                                            
                                            
                                        
                                       
                                        <td style="white-space: nowrap;">
                                            @can('update store')
                                            <a href="{{ url('stores/'.$item->id.'/edit') }}" class="btn btn-cta">
                                                <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="512" height="512" x="0" y="0" viewBox="0 0 492.493 492" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="M304.14 82.473 33.165 353.469a10.799 10.799 0 0 0-2.816 4.949L.313 478.973a10.716 10.716 0 0 0 2.816 10.136 10.675 10.675 0 0 0 7.527 3.114 10.6 10.6 0 0 0 2.582-.32l120.555-30.04a10.655 10.655 0 0 0 4.95-2.812l271-270.977zM476.875 45.523 446.711 15.36c-20.16-20.16-55.297-20.14-75.434 0l-36.949 36.95 105.598 105.597 36.949-36.949c10.07-10.066 15.617-23.465 15.617-37.715s-5.547-27.648-15.617-37.719zm0 0" fill="#ffffff" opacity="1" data-original="#000000" class=""></path></g></svg>
                                            </a>
                                            @endcan
                                            @can('view store')
                                            <a href="{{ url('stores/'.$item->id) }}" class="btn btn-cta">
                                                <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="512" height="512" x="0" y="0" viewBox="0 0 511.999 511.999" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="M508.745 246.041c-4.574-6.257-113.557-153.206-252.748-153.206S7.818 239.784 3.249 246.035a16.896 16.896 0 0 0 0 19.923c4.569 6.257 113.557 153.206 252.748 153.206s248.174-146.95 252.748-153.201a16.875 16.875 0 0 0 0-19.922zM255.997 385.406c-102.529 0-191.33-97.533-217.617-129.418 26.253-31.913 114.868-129.395 217.617-129.395 102.524 0 191.319 97.516 217.617 129.418-26.253 31.912-114.868 129.395-217.617 129.395z" fill="#ffffff" opacity="1" data-original="#000000" class=""></path><path d="M255.997 154.725c-55.842 0-101.275 45.433-101.275 101.275s45.433 101.275 101.275 101.275S357.272 311.842 357.272 256s-45.433-101.275-101.275-101.275zm0 168.791c-37.23 0-67.516-30.287-67.516-67.516s30.287-67.516 67.516-67.516 67.516 30.287 67.516 67.516-30.286 67.516-67.516 67.516z" fill="#ffffff" opacity="1" data-original="#000000" class=""></path></g></svg>
                                            </a>
                                            @endcan
                                            @can('delete store')
                                            <a  href="{{ url('stores/'.$item->id.'/delete') }}" class="btn btn-cta delete-confirm">
                                                <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="512" height="512" x="0" y="0" viewBox="0 0 24 24" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="M19 7a1 1 0 0 0-1 1v11.191A1.92 1.92 0 0 1 15.99 21H8.01A1.92 1.92 0 0 1 6 19.191V8a1 1 0 0 0-2 0v11.191A3.918 3.918 0 0 0 8.01 23h7.98A3.918 3.918 0 0 0 20 19.191V8a1 1 0 0 0-1-1ZM20 4h-4V2a1 1 0 0 0-1-1H9a1 1 0 0 0-1 1v2H4a1 1 0 0 0 0 2h16a1 1 0 0 0 0-2ZM10 4V3h4v1Z" fill="#ffffff" opacity="1" data-original="#000000" class=""></path><path d="M11 17v-7a1 1 0 0 0-2 0v7a1 1 0 0 0 2 0ZM15 17v-7a1 1 0 0 0-2 0v7a1 1 0 0 0 2 0Z" fill="#ffffff" opacity="1" data-original="#000000" class=""></path></g></svg>
                                            </a>
                                            @endcan
                                            
                                        </td>
                                        
                                        
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="100%" class="small text-muted">No data found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            <div class="d-flex justify-content-end">
                                {{ $data->appends($_GET)->links() }}
                            </div>
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
            url: '{{url("/")}}/employees/state/'+value,
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