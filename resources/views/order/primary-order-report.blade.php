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
                            Primary Order
                            @can('view primary order report')
                            <a href="{{ url('primary/order/csv/export',['brand'=>$request->brand,'date_from'=>$request->date_from,'date_to'=>$request->date_to,'state'=>$request->state,'area'=>$request->area,'distributor'=>$request->distributor,'ase'=>$request->ase,'product'=>$request->product,'keyword'=>$request->keyword]) }}" class="btn btn-sm btn-cta ms-auto" data-bs-toggle="tooltip" title="Export data in CSV">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                CSV
                            </a>
                            @endcan
                            
                        </h4>
                                <div class="search__filter mb-0">
                                    <div class="row">
                                        <div class="col-12">
                                            <p class="text-muted mt-1 mb-0">Showing {{$data->all_orders->count()}} out of {{$data->all_orders->total()}} Entries</p>
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
                                                    <div class="col">
                                                        <label for="ase" class="small text-muted">ASE</label>
                                                        <select class="form-select form-select-sm select2" id="ase" name="ase">
                                                            <option value="" selected disabled>Select</option>
                                                            @foreach ($allASEs as $item)
                                                                <option value="{{$item->id}}" {{ (request()->input('ase') == $item->id) ? 'selected' : '' }}>{{$item->name}}({{$item->stateDetail->name}})</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    
                                                   
                                                    
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
                                                    <div class="col">
                                                        <label for="state" class="small text-muted">Product</label>
                                                        <select name="product" id="product" class="form-select form-select-sm select2">
                                                            <option value="" disabled>Select</option>
                                                            <option value="" selected>All</option>
                                                            @foreach ($product as $state)
                                                                <option value="{{$state->id}}" {{ request()->input('product') == $state->id ? 'selected' : '' }}>{{$state->name}}({{$state->style_no}})</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col">
                                                                 <label for="ase" class="small text-muted">Keyword</label>
                                                                <input type="search" name="keyword" id="keyword" class="form-control form-control-sm" placeholder="Search by order no" value="{{request()->input('keyword')}}" autocomplete="off">
                                                            
                                                    </div>
                                                    
                                                        <!--<div class="btn-group books_btn_group">-->
                                                            
                                                        
                                                        <!--</div>-->
                                                    
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
                                        <th>Product</th> 
                                        <th>Qty</th>
                                        <th>Order No</th>
                                        <th>State</th>
                                        <th>Area</th>
                                        <th>ASE</th>
                                        <th>Distributor</th>
				                        <th>Date</th>
                                        <th>Status</th>
                                       
                                        <th class="action_btn">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                    @php
                        $all_orders_total_amount = 0;
                    @endphp

                    @forelse ($data->all_orders as $index => $item)
					
                        @php
                            $all_orders_total_amount += ($item->qty);
					       $color=DB::table('colors')->where('id',$item->color)->first();
                        @endphp
                        <tr id="row_{{$item->id}}">
                            <td>
                                {{ $index + 1 }}
                            </td>
                            <td>
                                            @php
                                                

                                            $brandMap = [
                                                1 => 'ONN',
                                                2 => 'PYNK',
                                                3 => 'Both',
                                            ];

                                            // Collect brand IDs from items (avoid duplicates)
                                            $brands = $data->pluck('brand')->unique()->toArray();

                                            // Determine brand permissions
                                            if (in_array(3, $brands)) {
                                                // If any brand is "Both"
                                                $brandPermissions = 'Both';
                                            } elseif (in_array(1, $brands) && in_array(2, $brands)) {
                                                // If both ONN and PYNK exist
                                                $brandPermissions = 'Both';
                                            } elseif (in_array(1, $brands)) {
                                                $brandPermissions = 'ONN';
                                            } elseif (in_array(2, $brands)) {
                                                $brandPermissions = 'PYNK';
                                            } else {
                                                // Fallback for unexpected values
                                                $brandPermissions = collect($brands)
                                                    ->map(fn($b) => $brandMap[$b] ?? 'Unknown')
                                                    ->implode(', ');
                                            }

                                    @endphp

                                           {{ $brandPermissions ?? '' }}
                                        </td>
                            <td>
                                <p class="text-dark mb-1">({{$item->product_style_no}}) {{$item->product_name}}</p>
                                <p class="small text-muted mb-1">{{$color->name ?? ''}}</p>
                                <p class="small text-muted mb-1">{{$item->size}}</p>

                            </td>
                            
                            <td>
                                <p class="text-dark mb-1">{{$item->qty}}</p>
                            </td>
                            <td>
                                <p class="small text-dark mb-1">#{{$item->order_no}}</p>
                            </td>
                            <td>
                                <p class="small text-dark mb-1">{{$item->state}}</p>
                            </td>
                            <td>
                                <p class="small text-dark mb-1">{{$item->area}}</p>
                            </td>

                            <td>
                                <p class="small text-dark mb-1">{{$item->fname.' '.$item->lname}}</p>
                            </td>
                            <td>
                                <p class="small text-dark mb-1">{{$item->distributor_name}}</p>
                            </td>
                            
                            <td>
                                <div class="order-time">
                                    <p class="small text-muted mb-0">
                                        <span class="text-dark font-weight-bold mb-2">
                                            {{date('j M Y g:i A', strtotime($item->created_at))}}
                                        </span>
                                    </p>
                                </div>
                            </td>
							<td>
								@if($item->status ==1)
								<span class="btn btn-sm btn-primary">Wait for approval</span>
								@elseif($item->status==2)
								<span class="btn btn-sm btn-success">Approved</span>
								@else
								<span class="btn btn-sm btn-danger">Rejected</span>
								@endif
							</td>
                        </tr>
                    @empty
                        <td colspan="9" class="text-center">No record found</td>
                    @endforelse
                    <tr>
                        <td></td>
                        
                        <td>
                            <p class="small text-dark mb-1 fw-bold">TOTAL</p>
                        </td>
                        <td>
                            <p class="small text-dark mb-1 fw-bold">{{ number_format($all_orders_total_amount) }}</p>
                        </td>
                        <td></td>
                        <td></td>
                    </tr>
                </tbody>
                            </table>
                        </div>
                         {{ $data->all_orders->appends($_GET)->render() }}
                        
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