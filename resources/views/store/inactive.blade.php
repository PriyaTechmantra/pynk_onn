@extends('admin.layouts.app')
@section('page', 'Inactive Store')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

@section('content')
<section>
    <div class="card card-body">
        <div class="search__filter mb-0">
            <div class="row align-items-center">
                <div class="col">
                    
                       <div class="col-md-12 text-end">
                    		<form class="row align-items-end justify-content-end" action="{{ route('admin.store.inactive') }}">
							<div class="col-auto">
								<input type="search" name="keyword" id="keyword" class="form-control form-control-sm" placeholder="Search by store contact" value="{{request()->input('keyword')}}" autocomplete="off">
							</div>
							<div class="col-auto">
								<div class="btn-group">
									<button type="submit" class="btn btn-danger btn-sm">
										Filter
									</button>
									<a href="{{ url()->current() }}" class="btn btn-sm btn-light" data-bs-toggle="tooltip" title="Clear Filter">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                </a>
										<a class="btn btn-danger btn-sm" href="{{ route('admin.store.index') }}">All Store ({{ $store->count() }})</a>
									<span><a href="{{ route('admin.store.inactive.export',['keyword'=>$request->keyword]) }}" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Export data in CSV">CSV Export
										<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
									</a></span>
									
									 <!--<div class="search-filter-right-el">-->
          <!--                              <a href="#csvForInactiveStore" data-bs-toggle="modal" class="btn btn-danger"> <iconify-icon icon="prime:plus-circle"></iconify-icon> Store Status Change</a>-->
          <!--                          </div>-->
								</div>
							</div>
						   </form>
					</div>
                   
                </div>
                
            </div>
        </div>
    </div>

    <table class="table" id="example5">
        <thead>
            <tr>
                {{-- <th>#SR</th> --}}
                <th>Store</th>
                {{-- <th>Firm</th> --}}
                <th>Created by</th>
                <th>Contact</th>
                <th>Distributor</th>
                <th>Address</th>
				<th>Date</th>
                <th>Status</th>
                {{-- <th>Image</th>
                <th>Invoice</th> --}}
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

                // ase name
                $ase = $item->user_id;
                 $username = \App\User::select('name')->where('id', $ase)->first();
               // distributor name
               $store_name = $item->store_name;
                //$distName = \App\Models\RetailerListOfOcc::select('distributor_name')->where('retailer', $store_name)->where('ase', $username->name)->where('area', $item->area)->first();
			//if (!empty(request()->input('distributor'))) {
			//$distName = \App\Models\RetailerListOfOcc::select('distributor_name')->where('distributor_name', request()->input('distributor'))->first();
		//	}else{
			 $distName = \App\Models\RetailerListOfOcc::select('distributor_name')->where('store_id', $item->id)->first();
			//}
			//dd($distName);
                //if (!empty(request()->input('distributor'))) {
                 //   if ($distName->distributor_name != request()->input('distributor')) {
                  //      continue;
                 //   }
              //  }
                @endphp

                <tr>
                    {{-- <td>{{ $index + $data->firstItem() }}</td> --}}
                    <td>
                        {{ ucwords($item->store_name) }}
                        <p class="small text-muted">- {{ ucwords($item->bussiness_name) }}</p>
                        <div class="row__action">
                            <a href="{{ route('admin.store.edit', $item->id) }}">Edit</a>

                            <a href="{{ route('admin.store.view', $item->id) }}">View</a>

                            @if ($item->status == 1)
                                <a href="{{ route('admin.store.status', $item->id) }}" data-bs-toggle="tooltip" title="This store is ACTIVE. Tap to make INACTIVE">Active</a>
                            @else
                                <a href="{{ route('admin.store.status', $item->id) }}" data-bs-toggle="tooltip" title="This store is INACTIVE. Tap to ACTIVATE">Inactive</a>
                            @endif

                            <a href="{{ route('admin.store.delete', $item->id) }}" class="text-danger" onclick="return confirm('Are you sure ?')">Delete</a> 
                        </div>
                    </td>
                    <td>
                        {{ $username ? $username->name : '' }}
                    </td>
                    <td>{{ $item->email }}<br>{{ $item->contact }}</td>
                    <td>
                        {{ $distName ? $distName->distributor_name : '' }}
                    </td> 
                    <td>{{ ucwords($item->address) }}<br>{{ $item->area }}<br>{{ $item->city }}<br>{{ $item->state }}
                    </td>
					<td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y g:i:s A')}}
                    </td>
                    <td>
                        <span class="badge bg-{{ $item->status == 1 ? 'success' : 'danger' }}">{{ $item->status == 1 ? 'Active' : 'Inactive' }}</span>
                    </td>
                    {{-- <td>
                        <a href="{{ route('admin.retailer.image.index', $item->id) }}"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-images" viewBox="0 0 16 16">
                            <path d="M4.502 9a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3z"/>
                            <path d="M14.002 13a2 2 0 0 1-2 2h-10a2 2 0 0 1-2-2V5A2 2 0 0 1 2 3a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v8a2 2 0 0 1-1.998 2zM14 2H4a1 1 0 0 0-1 1h9.002a2 2 0 0 1 2 2v7A1 1 0 0 0 15 11V3a1 1 0 0 0-1-1zM2.002 4a1 1 0 0 0-1 1v8l2.646-2.354a.5.5 0 0 1 .63-.062l2.66 1.773 3.71-3.71a.5.5 0 0 1 .577-.094l1.777 1.947V5a1 1 0 0 0-1-1h-10z"/>
                            </svg></a>
                    </td>
                    <td>
                        <a href="{{ route('admin.retailer.invoice.index', $item->id) }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-receipt" viewBox="0 0 16 16">
                                <path d="M1.92.506a.5.5 0 0 1 .434.14L3 1.293l.646-.647a.5.5 0 0 1 .708 0L5 1.293l.646-.647a.5.5 0 0 1 .708 0L7 1.293l.646-.647a.5.5 0 0 1 .708 0L9 1.293l.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .801.13l.5 1A.5.5 0 0 1 15 2v12a.5.5 0 0 1-.053.224l-.5 1a.5.5 0 0 1-.8.13L13 14.707l-.646.647a.5.5 0 0 1-.708 0L11 14.707l-.646.647a.5.5 0 0 1-.708 0L9 14.707l-.646.647a.5.5 0 0 1-.708 0L7 14.707l-.646.647a.5.5 0 0 1-.708 0L5 14.707l-.646.647a.5.5 0 0 1-.708 0L3 14.707l-.646.647a.5.5 0 0 1-.801-.13l-.5-1A.5.5 0 0 1 1 14V2a.5.5 0 0 1 .053-.224l.5-1a.5.5 0 0 1 .367-.27zm.217 1.338L2 2.118v11.764l.137.274.51-.51a.5.5 0 0 1 .707 0l.646.647.646-.646a.5.5 0 0 1 .708 0l.646.646.646-.646a.5.5 0 0 1 .708 0l.646.646.646-.646a.5.5 0 0 1 .708 0l.646.646.646-.646a.5.5 0 0 1 .708 0l.646.646.646-.646a.5.5 0 0 1 .708 0l.509.509.137-.274V2.118l-.137-.274-.51.51a.5.5 0 0 1-.707 0L12 1.707l-.646.647a.5.5 0 0 1-.708 0L10 1.707l-.646.647a.5.5 0 0 1-.708 0L8 1.707l-.646.647a.5.5 0 0 1-.708 0L6 1.707l-.646.647a.5.5 0 0 1-.708 0L4 1.707l-.646.647a.5.5 0 0 1-.708 0l-.509-.51z"/>
                                <path d="M3 4.5a.5.5 0 0 1 .5-.5h6a.5.5 0 1 1 0 1h-6a.5.5 0 0 1-.5-.5zm0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 1 1 0 1h-6a.5.5 0 0 1-.5-.5zm0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 1 1 0 1h-6a.5.5 0 0 1-.5-.5zm0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 0 1 0 1h-6a.5.5 0 0 1-.5-.5zm8-6a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1h-1a.5.5 0 0 1-.5-.5zm0 2a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1h-1a.5.5 0 0 1-.5-.5zm0 2a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1h-1a.5.5 0 0 1-.5-.5zm0 2a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1h-1a.5.5 0 0 1-.5-.5z"/>
                                </svg></a>
                    </td> --}}
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
</section>
<div class="modal fade" id="csvForInactiveStore" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                Bulk Upload
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="{{ route('admin.store.status.change') }}" enctype="multipart/form-data" id="borrowerCsvUpload">@csrf
                    <input type="file" name="file" class="form-control" accept=".csv">
                    <br>
                    <a href="{{ asset('admin/ase.csv') }}">Download Sample CSV</a>
                    <br>
                    <button type="submit" class="btn btn-danger mt-3" id="csvImportBtn">Import <i class="fas fa-upload"></i></button>
                </form>
            </div>
        </div>
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
</script>
@endsection