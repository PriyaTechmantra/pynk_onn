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
                            Employee
                            @can('employee export')
                            <a href="{{ url('employees/csv/export',['office_id'=>$request->office_id,'bookshelves_id'=>$request->bookshelves_id,'category_id'=>$request->category_id,'keyword'=>$request->keyword]) }}" class="btn btn-sm btn-cta ms-auto" data-bs-toggle="tooltip" title="Export data in CSV">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                CSV
                            </a>
                            @endcan
                            @can('employee bulk upload')
                            <a href="#csvModal" data-bs-toggle="modal" class="btn btn-sm btn-cta">Bulk Upload</a>
                            @endcan
                            
                            @can('create employee')
                            <a href="{{ url('employees/create') }}" class="btn btn-sm btn-cta">Add Employee</a>
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
                                                                 <option value="All" {{ (request()->input('brand') == "All") ? 'selected' : '' }}>All</option>
                                                            
                                                                <option value="1" {{ (request()->input('brand') == 1) ? 'selected' : '' }}>ONN</option>
                                                                <option value="2" {{ (request()->input('brand') == 2) ? 'selected' : '' }}>PYNK</option>
                                                                
                                                                
                                                        </select>
                                                    </div>
                                                     @endif
                                                    <div class="col">
                                                        <label class="small text-muted">Type</label>
                                                        <select class="form-select form-select-sm" aria-label="Default select example" name="type" id="user_type">
                                                            <option value="" selected disabled>Select</option>
                                                            <option value="" selected>All</option>
                                                            
                                                                <option value="1" {{ (request()->input('type') == 1) ? 'selected' : '' }}>VP</option>
                                                                <option value="2" {{ (request()->input('type') == 2) ? 'selected' : '' }}>RSM</option>
                                                                <option value="3" {{ (request()->input('type') == 3) ? 'selected' : '' }}>ASM</option>
                                                                <option value="4" {{ (request()->input('type') == 4) ? 'selected' : '' }}>ASE</option>
                                                                
                                                        </select>
                                                    </div>
                                                    
                                                    <div class="col">
                                                        <label for="state" class="text-muted small">State</label>
                                                        <select name="state" id="state" class="form-control form-control-sm select2">
                                                            <option value="" selected disabled>Select</option>
                                                            
                                                            @foreach ($state as $state)
                                                                <option value="{{$state->id}}" {{ request()->input('state') == $state->id ? 'selected' : '' }}>{{$state->name}}</option>
                                                            @endforeach
                                                        </select>
                                                  </div>
                                              
                                                    <div class="col">
                                                    <label class="small text-muted">Area</label>
                                                        <select class="form-control form-control-sm select2" name="area" disabled>
                                                        <option value="{{ $request->area }}">Select state first</option>
                                                        </select>
                                                    </div>
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
                                        <th>Brand Permission</th>
                                        <th>Name</th>
                                        <th>Designation</th>
                                        <th>Mobile</th> 
                                        <th>Working Area & State</th>
                                        <th style="min-width: 200px">Manager</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th class="action_btn">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($data as $index => $item)
                                    
                                    @php
                                        $area ='';
                                        $areaDetail = DB::table('user_areas')->where('user_id','=',$item->id)->where('is_deleted', 0)->groupby('area_id')->get();
                                        
                                        if(!empty($areaDetail)) {
                                            foreach($areaDetail as $key => $obj) {
                                                $areaList=DB::table('areas')->where('id','=',$obj->area_id)->first();
                                                $area .= $areaList->name ??'';
                                                if((count($areaDetail) - 1) != $key) $area .= ', ';
                                            }
                                        }
										$login=DB::table('user_logins')->where('user_id',$item->id)->orderby('id','desc')->first();
							 
                                    @endphp
                                    <tr>
                                        <td class="index-col">{{ $index + 1 }}</td>
                                        <td>
                                            @php
                                                $assignedPermissions = DB::table('user_permission_categories')
                                                ->select('user_permission_categories.*')
                                                ->join('employees','employees.id','=','user_permission_categories.employee_id')
                                                ->where('user_permission_categories.employee_id', $item->id)
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

                                           {{ $brandPermissions ?? '' }}
                                        </td>
                                        <td>
                                            <p class="">{{ $item->name ?? '' }}</p>
                                            <p class="small">{{$item->employee_id}}</p>
                                        </td>
                                        <td>
                                          {{ $item->designation ? $item->designation : userTypeName($item->type) }}
                                        </td>
                                        <td>{{$item->mobile}} {{$item->whatsapp_no}}</td>
                                        <td>
                                             @if($item->type == 4)
                                            <p class="small text-dark">{{$area}}</p>
                        				    @endif
                                          {{$item->stateDetail->name ??''}}
                                         
                                          
                                        </td>
                                        <td>
                                          <p style="text-transform: uppercase;">{!! findManagerDetails($item->id, $item->type) !!}</p>
                                        </td>
                                        
                                        <td>
                                            @can('employee status change')
                                            <a href="{{ url('employees/'.$item->id.'/status/change') }}">
                                                <span class="badge badge-status bg-{{ $item->status == 1 ? 'success' : 'danger' }}">{{ $item->status == 1 ? 'Active' : 'Inactive' }}</span>
                                            </a>
                                            @endcan
                                        </td>
                                        <td>{{date('d-m-Y', strtotime($item->created_at))}}</td>
                                       
                                        <td style="white-space: nowrap;">
                                            @can('update employee')
                                            <a href="{{ url('employees/'.$item->id.'/edit') }}" class="btn btn-cta">
                                                <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="512" height="512" x="0" y="0" viewBox="0 0 492.493 492" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="M304.14 82.473 33.165 353.469a10.799 10.799 0 0 0-2.816 4.949L.313 478.973a10.716 10.716 0 0 0 2.816 10.136 10.675 10.675 0 0 0 7.527 3.114 10.6 10.6 0 0 0 2.582-.32l120.555-30.04a10.655 10.655 0 0 0 4.95-2.812l271-270.977zM476.875 45.523 446.711 15.36c-20.16-20.16-55.297-20.14-75.434 0l-36.949 36.95 105.598 105.597 36.949-36.949c10.07-10.066 15.617-23.465 15.617-37.715s-5.547-27.648-15.617-37.719zm0 0" fill="#ffffff" opacity="1" data-original="#000000" class=""></path></g></svg>
                                            </a>
                                            @endcan
                                            @can('view employee')
                                            <a href="{{ url('employees/'.$item->id) }}" class="btn btn-cta">
                                                <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="512" height="512" x="0" y="0" viewBox="0 0 511.999 511.999" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="M508.745 246.041c-4.574-6.257-113.557-153.206-252.748-153.206S7.818 239.784 3.249 246.035a16.896 16.896 0 0 0 0 19.923c4.569 6.257 113.557 153.206 252.748 153.206s248.174-146.95 252.748-153.201a16.875 16.875 0 0 0 0-19.922zM255.997 385.406c-102.529 0-191.33-97.533-217.617-129.418 26.253-31.913 114.868-129.395 217.617-129.395 102.524 0 191.319 97.516 217.617 129.418-26.253 31.912-114.868 129.395-217.617 129.395z" fill="#ffffff" opacity="1" data-original="#000000" class=""></path><path d="M255.997 154.725c-55.842 0-101.275 45.433-101.275 101.275s45.433 101.275 101.275 101.275S357.272 311.842 357.272 256s-45.433-101.275-101.275-101.275zm0 168.791c-37.23 0-67.516-30.287-67.516-67.516s30.287-67.516 67.516-67.516 67.516 30.287 67.516 67.516-30.286 67.516-67.516 67.516z" fill="#ffffff" opacity="1" data-original="#000000" class=""></path></g></svg>
                                            </a>
                                            @endcan
                                            @can('delete employee')
                                            <a  href="{{ url('employees/'.$item->id.'/delete') }}" class="btn btn-cta delete-confirm">
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