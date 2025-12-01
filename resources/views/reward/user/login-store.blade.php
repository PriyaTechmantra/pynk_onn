@extends('layouts.app')
@section('content')

<div class="container mt-2">
        <div class="row">
            <div class="col-md-12">

                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif

                <div class="card data-card mt-3">
                    <div class="card-header">
                        <h4 class="d-flex">Store 
                             <a href="{{ route('reward.retailer.user.loginStoreCountCsv', ['state' => $request->state] + request()->all()) }}" class="btn btn-sm btn-cta ms-auto" data-bs-toggle="tooltip" title="Export data in CSV">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                CSV
                            </a>
                        </h4>

                        <div class="search__filter mb-0">
                            <div class="row">
                                <div class="col-12">
                                    <p class="text-muted mt-1 mb-0">Showing {{$loginCountWiseReport->count()}} out of {{$loginCountWiseReport->total()}} Entries</p>
                            </div>
                            <div class="row">
                                        
                                <div class="col-12">
                                    <form action="{{ route('reward.retailer.user.login.store.count',$request->state) }}" method="GET">
                                        <div class="row align-items-center">
                                            
                                            <div class="col-2">
                                                <label for="distributor" class="small text-muted">Distributor</label>
                                                    <select class="form-control form-control-sm select2" id="distributor" name="distributor_id">
                                                        <option value="" selected disabled>Select</option>
                                                        @foreach ($allDistributors as $item)
                                                            <option value="{{$item->id}}" {{ (request()->input('distributor_id') == $item->id) ? 'selected' : '' }}>{{$item->name}}</option>
                                                        @endforeach
                                                    </select>
                                            </div>
                                            <div class="col-2">

                                                 <label class="small text-muted">Brand</label>
                                                <select name="brand_selection" class="form-control form-control-sm">
                                                    <option value="">Select Brand</option>
                                                    <option value="3" {{ request()->input('brand_selection') == 3 ? 'selected' : '' }}>ALL</option>
                                                    <option value="1" {{ request()->input('brand_selection') == 1 ? 'selected' : '' }}>ONN</option>
                                                    <option value="2" {{ request()->input('brand_selection') == 2 ? 'selected' : '' }}>PYNK</option>
                                                </select>
                                                
                                            </div>
                                            
                                            <div class="col-2">
                                                <label for="ase" class="small text-muted">ASE</label>
                                                    <select class="form-control form-control-sm select2" id="ase" name="ase_id">
                                                        <option value="" selected disabled>Select</option>
                                                        @foreach ($allASEs as $item)
                                                            <option value="{{$item->id}}" {{ (request()->input('ase_id') == $item->id) ? 'selected' : '' }}>{{$item->name}}</option>
                                                        @endforeach
                                                    </select>
                                            </div>

                                            <div class="col-2">

                                                <label class="small text-muted">Area</label>
                                                    <select class="form-control form-control-sm select2" name="area_id" >
                                                        <option value="" disabled>Select</option>
                                                        <option value="" selected>All</option>
                                                        @foreach ($areaData as $state)
                                                            <option value="{{$state->id}}" {{ request()->input('area_id') == $state->id ? 'selected' : '' }}>{{$state->name}}</option>
                                                        @endforeach
                                                    </select>

                                            </div>
                                            <div class="col-2">

                                             <div class="search-filter-right-el">
                                                    <input type="search" name="keyword" id="term" class="form-control form-control-sm" placeholder="Search by name/ contact" value="{{app('request')->input('keyword')}}" autocomplete="off">
                                                </div>
                                            </div>

                                            <div class="col-2 text-end">
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
                            <table class="table admin-table ">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th class="text-center"><i class="fi fi-br-picture"></i></th>
                                        <th>Uniquecode</th> 
                                        <th>Store</th>
                                        <th>Contact</th>
                                        <th>Distributor</th>
                                        <th>Address</th>
                                        <th>Date</th>
                                        
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($loginCountWiseReport as $index => $item)
                                    
                                    @php
                                    if (!empty($_GET['status'])) {
                                    if ($_GET['status'] == 'active') {
                                    if ($item->status == 0) continue;
                                    } else {
                                    if ($item->status == 1) continue;
                                    }
                                    }
                                    $distName = \App\Models\Team::select('distributors.name')->join('distributors', 'distributors.id', 'teams.distributor_id')->where('store_id', $item->id)->first();
                                    $storename = \App\Models\Team::where('store_id', $item->id)->first();
                                    @endphp
                                    <tr>
                                        <td>{{ ($loginCountWiseReport->firstItem()) + $index }}</td>
                                        <td class="text-center column-thumb">
                                        @if(!empty($item->image))
                                            <img src="{{ asset($item->image) }}" style="max-width: 80px;max-height: 80px;">
                                            
                                        @endif
                                        </td>
                                        <td>{{ $item->unique_code }}</td>
                                        <td>
                                            {{ ucwords($item->name) }}
                                            <p class="small text-muted">- {{ ucwords($item->bussiness_name) }}</p>
                                            <!-- <div class="row__action">
                                                <form action="{{ route('stores.destroy',$item->id) }}" method="POST">
                                                    <a href="{{ route('stores.edit', $item->id) }}">Edit</a>
                                                    <a href="{{ route('stores.show', $item->id) }}">View</a>
                                                    
                                            </div> -->
                                        </td>
                                        @if(!empty($item->users))
                                        <td>
                                            {{ $item->users->name ??'' }}
                                            <p class="small text-muted">@if($item->users->type==3)<span>(ASM)</span>@elseif($item->users->type==4)<span>(ASE)</span>@endif</p>
                                        </td>
                                        @endif
                                        <td>{{ $item->email }}<br>{{ $item->contact }}</td>
                                        <td>
                                            {{ $distName ? $distName->name : '' }}
                                        </td> 
                                        <td>{{ ucwords($item->address) }}<br>{{ $item->areas->name ??'' }}<br>{{ $item->areas->name ??''}}<br>{{ $item->states->name ??''}}</td>
                                        <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y g:i:s A')}}
                                        </td>
                                       
                                        <td><span class="badge bg-{{($item->status == 1) ? 'success' : 'danger'}}">{{($item->status == 1) ? 'Active' : 'Inactive'}}</span></td>
                                        <td>

                                                 <a href="{{ route('stores.edit', $item->id) }}" class="btn btn-cta">
                                                    <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="512" height="512" x="0" y="0" viewBox="0 0 492.493 492" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="M304.14 82.473 33.165 353.469a10.799 10.799 0 0 0-2.816 4.949L.313 478.973a10.716 10.716 0 0 0 2.816 10.136 10.675 10.675 0 0 0 7.527 3.114 10.6 10.6 0 0 0 2.582-.32l120.555-30.04a10.655 10.655 0 0 0 4.95-2.812l271-270.977zM476.875 45.523 446.711 15.36c-20.16-20.16-55.297-20.14-75.434 0l-36.949 36.95 105.598 105.597 36.949-36.949c10.07-10.066 15.617-23.465 15.617-37.715s-5.547-27.648-15.617-37.719zm0 0" fill="#ffffff" opacity="1" data-original="#000000" class=""></path></g></svg>
                                                    </a>
                                                <a href="{{ route('stores.show', $item->id) }}" class="btn btn-cta">
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="20" height="20">
                                                            <path d="M508.745 246.041c-4.574-6.257-113.557-153.206-252.748-153.206S7.818 239.784 3.249 246.035a16.896 16.896 0 0 0 0 19.923c4.569 6.257 113.557 153.206 252.748 153.206s248.174-146.95 252.748-153.201a16.875 16.875 0 0 0 0-19.922zM255.997 385.406c-102.529 0-191.33-97.533-217.617-129.418 26.253-31.913 114.868-129.395 217.617-129.395 102.524 0 191.319 97.516 217.617 129.418-26.253 31.912-114.868 129.395-217.617 129.395z" fill="#ffffff"/>
                                                            <path d="M255.997 154.725c-55.842 0-101.275 45.433-101.275 101.275s45.433 101.275 101.275 101.275S357.272 311.842 357.272 256s-45.433-101.275-101.275-101.275zm0 168.791c-37.23 0-67.516-30.287-67.516-67.516s30.287-67.516 67.516-67.516 67.516 30.287 67.516 67.516-30.286 67.516-67.516 67.516z" fill="#ffffff"/>
                                                        </svg>
                                                </a>
                                                   
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="100%" class="small text-muted">No data found</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-end">
                            {{ $loginCountWiseReport->appends($_GET)->links() }}
                        </div> 
                    </div>
                </div>
        </div>
    </div>
</div>



@endsection
@section('script')
<script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
<script>
   
        var value = {{$state}};

        $.ajax({
            url: '{{url("/")}}/admin/state-wise-area/'+value,
            method: 'GET',
            success: function(result) {
                var content = '';
                var slectTag = 'select[name="area_id"]';
                var displayCollection =  "All";

                content += '<option value="" selected>'+displayCollection+'</option>';
                $.each(result.data.area, (key, value) => {
                    content += '<option value="'+value.area_id+'">'+value.area+'</option>';
                });
                $(slectTag).html(content).attr('disabled', false);
            }
        });
    
</script>
<script src="http://code.jquery.com/jquery-latest.min.js" type="text/javascript"></script>
<script>
    $(function() {
        $('#btnExport').click(function() {
            console.log("hello");
            //$('#tblHead').css("display","block");
            var url = 'data:application/vnd.ms-excel,' + encodeURIComponent($('#tableWrap').html())
            location.href = url
            return false
            $('#tblHead').css("display", "none");
        });
    });
</script>
@endsection
