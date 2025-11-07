@extends('layouts.app')
@section('content')
<section class="store-sec ">
    <div class="row">
        <div class="col-xl-12 order-2 order-xl-1">
            <div class="card search-card">
                <div class="card-body">
                    <div class="search__filter mb-5">
                        <div class="row align-items-center justify-content-between">
                            <div class="col-md-12 mb-3">
                                <p class="text-muted mt-1 mb-0">Showing {{$loginCountWiseReport->count()}} out of {{$loginCountWiseReport->total()}} Entries</p>
                            </div>
                            <div class="col-md-12 mb-3">
                                <div class="search-filter-right">
                                    <div class="search-filter-right-el">
                                        <form action="{{ route('reward.retailer.user.login.store.count',$request->state) }}" method="GET">
                                          <div class="search-filter-right">
                                              
                                                <div class="search-filter-right-el">
                                                    <label for="distributor" class="small text-muted">Distributor</label>
                                                    <select class="form-control form-control-sm select2" id="distributor" name="distributor_id">
                                                        <option value="" selected disabled>Select</option>
                                                        @foreach ($allDistributors as $item)
                                                            <option value="{{$item->id}}" {{ (request()->input('distributor_id') == $item->id) ? 'selected' : '' }}>{{$item->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="search-filter-right-el">
                                                    <label for="ase" class="small text-muted">ASE</label>
                                                    <select class="form-control form-control-sm select2" id="ase" name="ase_id">
                                                        <option value="" selected disabled>Select</option>
                                                        @foreach ($allASEs as $item)
                                                            <option value="{{$item->id}}" {{ (request()->input('ase_id') == $item->id) ? 'selected' : '' }}>{{$item->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="search-filter-right-el">
                                                    <label for="ase" class="small text-muted">ASM</label>
                                                    <select class="form-control form-control-sm select2" id="asm" name="asm_id">
                                                        <option value="" selected disabled>Select</option>
                                                        @foreach ($allASMs as $item)
                                                            <option value="{{$item->id}}" {{ (request()->input('asm_id') == $item->id) ? 'selected' : '' }}>{{$item->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                               
                                                <div class="search-filter-right-el">
                                                    <label class="small text-muted">Area</label>
                                                    <select class="form-control form-control-sm select2" name="area_id" >
                                                        <option value="" disabled>Select</option>
                                                        <option value="" selected>All</option>
                                                        @foreach ($areaData as $state)
                                                            <option value="{{$state->id}}" {{ request()->input('area_id') == $state->id ? 'selected' : '' }}>{{$state->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                
                                                
                                            </div>
											
                                            <div class="search-filter-right search-filter-right-store mt-4">
                                                
                                                <div class="search-filter-right-el">
                                                    <input type="search" name="keyword" id="term" class="form-control form-control-sm" placeholder="Search by name/ contact" value="{{app('request')->input('keyword')}}" autocomplete="off">
                                                </div>
											
                                                <div class="search-filter-right-el">
                                                    <button type="submit" class="btn btn-outline-danger btn-sm store-filter-btn">
                                                        <iconify-icon icon="carbon:filter"></iconify-icon> Filter
                                                    </button>
                                                    <a href="{{ url()->current() }}" class="btn btn-sm btn-light clear-filter store-filter-times" data-bs-toggle="tooltip" title="Clear Filter">
                                                        <iconify-icon icon="basil:cross-outline"></iconify-icon>
                                                    </a>
                                                </div>
                                               <div class="search-filter-right-el">
                                                    <a   href="{{ route('reward.retailer.user.login.store.export.csv',[$request->state,'ase'=>$request->ase_id,'asm'=>$request->asm_id,'distributor'=>$request->distributor_id,'area_id'=>$request->area_id,'keyword'=>$request->keyword]) }}" class="btn btn-outline-danger btn-sm" data-bs-toggle="tooltip" title="Export data in CSV">
                                                        
                                                        <iconify-icon icon="material-symbols:download"></iconify-icon> CSV
                                                    </a>
                                                </div> 
                                            </div>
                                             
                                        </form>
                                    </div>
                                    
                                    
                                    
                                   
                                </div>
                            </div>
                            
							
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table admin-table ">
                        <thead>
                            <tr>
                                <th>#SR</th>
                                <th class="text-center"><i class="fi fi-br-picture"></i></th>
                                <th>Uniquecode</th> 
                                <th>Store</th>
                                <th>Contact</th>
                                <th>Distributor</th>
                                <th>Address</th>

                                <th>Date</th>
								<th>Approval</th>
                                <th>Status</th>
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
                              $distName = \App\Models\Team::select('users.name')->join('users', 'users.id', 'teams.distributor_id')->where('store_id', $item->id)->first();
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
                                    <p class="small text-muted">- {{ ucwords($item->business_name) }}</p>
                                    <div class="row__action">
                                        <form action="{{ route('stores.destroy',$item->id) }}" method="POST">
                                            <a href="{{ route('stores.edit', $item->id) }}">Edit</a>
                                            <a href="{{ route('stores.show', $item->id) }}">View</a>
											 
                                    </div>
                                </td>
                                @if(!empty($item->users))
                                <td>
                                    {{ $item->users->name ??'' }}
                                    <p class="small text-muted">@if($item->users->type==5)<span>(ASM)</span>@elseif($item->users->type==6)<span>(ASE)</span>@endif</p>
                                </td>
                                @endif
                                <td>{{ $item->email }}<br>{{ $item->contact }}</td>
                                <td>
                                    {{ $distName ? $distName->name : '' }}
                                </td> 
                                <td>{{ ucwords($item->address) }}<br>{{ $item->areas->name ??'' }}<br>{{ $item->areas->name ??''}}<br>{{ $item->states->name ??''}}</td>
                                <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y g:i:s A')}}
                                </td>
								<td>
								       <span class="badge bg-secondary">Waiting for approval </span>
                                </td>
                                <td><span class="badge bg-{{($item->status == 1) ? 'success' : 'danger'}}">{{($item->status == 1) ? 'Active' : 'Inactive'}}</span></td>
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
</section>


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
