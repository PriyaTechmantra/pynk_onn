@extends('layouts.app')

@section('content')
@php
$allASE=DB::table('employees')->where('type',4)->get();

@endphp
<div class="container mt-2">
        <div class="row">
            <div class="col-md-12">

                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif

                <div class="card data-card mt-3">
                    <div class="card-header">
                        <h4>RETAILER WISE REPORT
                            <a href="{{ route('reward.qrcode.redeem.retailer.csv.export', ['date_from'=>$request->date_from,'date_to'=>$request->date_to]) }}" class="btn btn-sm btn-cta float-end" data-bs-toggle="tooltip" title="Report Export in CSV">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                    All Report Export in CSV
                                </a>
                                <a href="{{ route('reward.qrcode.redeem.retailer.product.csv.export', ['date_from'=>$request->date_from,'date_to'=>$request->date_to,'store'=>$request->store]) }}" class="btn btn-sm btn-cta float-end" data-bs-toggle="tooltip" title="Product wise Report Export in CSV">
                                   <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                    Retailer wise Report Export in CSV
                                </a>
                        </h4>

                        <div class="search__filter mb-0">
                            <div class="row">
                                <div class="col-12">
                                    <p class="text-muted mt-1 mb-0"></p>
                                </div>
                            </div>
                            <div class="row">
                                        
                                <div class="col-12">
                                    <form action="{{ route('reward.qrcode.redeem.retailer.wise.report') }}">
                                        <div class="row g-2 align-items-center">
                                            
                                            <div class="col-3">
                                                 <label class="small text-muted">Brand</label>
                                                <select name="brand_selection" class="form-control form-control-sm">
                                                    <option value="">Select Brand</option>
                                                    <option value="3" {{ request()->input('brand_selection') == 3 ? 'selected' : '' }}>ALL</option>
                                                    <option value="1" {{ request()->input('brand_selection') == 1 ? 'selected' : '' }}>ONN</option>
                                                    <option value="2" {{ request()->input('brand_selection') == 2 ? 'selected' : '' }}>PYNK</option>
                                                </select>
                                                
                                            </div>
                                            <div class="col-2">
                                                <label class="text-muted small">Date from</label>
                                                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request()->input('date_from') ?? date('Y-m-01') }}">
                                            </div>
                                            <div class="col-2">
                                               <label class="text-muted small">Date to</label>
                                                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request()->input('date_to') ?? date('Y-m-d') }}">
                                            </div>
                                            <div class="col-3">
                                                <label for="ase" class="small text-muted">Store</label>
                                                <select class="form-control form-control-sm select2" id="store" name="store">
                                                <option value="" selected disabled>Select Store</option>
                                                </select>
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
                            <table class="table">
                                <thead>
                                    <tr>
                                       <th>#</th>
                                        <th>DATE</th>
                                        <th>RETAILER UNIQUE CODE</th>
                                        <th>PARTICULARS</th>
            							 <th>OPENING BALANCE</th>
            							 <th>CREDIT</th>
                                        <th>DEBIT</th>
                                        
                                        <th>AVAILABLE BALANCE</th> 
                                    </tr>
                                </thead>
                                    <tbody>
                                        @forelse ($ledgerData as $index => $item)
                                       
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
                                            <td>{{ $index+1}}</td>
                                            <td>
                                               {{$item['date']}}
                                            
                                            </td>
                                            <td>{{$item['unique_code']}}</td>
                                            <td>
                                                {{$item['remarks']}}
                                            </td>
                							<td>
                                                {{$item['opening_balance']}}
                                            </td>
                                           
                                            <td>{{$item['credit']}}</td>
                                             <td>{{$item['debit']}}</td>
                                            <td>{{$item['available_balance']}}</span></td> 
                                        </tr>
                                        @empty
                                        <tr><td colspan="100%" class="small text-muted">No data found</td></tr>
                                        @endforelse
                                    </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>

@endsection

@section('script')
    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });
        
         $(document).ready(function () {
        $('#store').select2({
            placeholder: 'Type to search for a store',
            allowClear: true,
            minimumInputLength: 1, // Trigger search after typing one character
            ajax: {
                url: '{{ route("reward.qrcode.redeem.fetch.stores") }}', // Route to handle fetching stores
                dataType: 'json',
                delay: 250, // Add a slight delay to prevent excessive requests
                data: function (params) {
                    return {
                        search: params.term // Send search term to the server
                    };
                },
                processResults: function (data) {
                    // Format the response to fit Select2's requirements
                    return {
                        results: $.map(data, function (store) {
                            return {
                                id: store.id,
                                text: `${store.name} (${store.unique_code})`
                            };
                        })
                    };
                },
                cache: true
            }
        });
    });
    </script>
	

@endsection