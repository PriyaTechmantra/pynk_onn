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
                        <h4>Qrcode
                            <a href="" class="btn btn-sm btn-cta float-end">Generate New Qrcodes</a>
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
                                        <div class="row g-2 align-items-center">
                                            
                                            <div class="col-2 d-flex align-items-center gap-2">
                                                <select name="brand_selection" class="form-control form-control-sm">
                                                    <option value="">Select Brand</option>
                                                    <option value="3" {{ request()->input('brand_selection') == 3 ? 'selected' : '' }}>ALL</option>
                                                    <option value="1" {{ request()->input('brand_selection') == 1 ? 'selected' : '' }}>ONN</option>
                                                    <option value="2" {{ request()->input('brand_selection') == 2 ? 'selected' : '' }}>PYNK</option>
                                                </select>
                                            </div>

                                            <div class="col-5 d-flex align-items-center gap-2">
                                                <label class="text-muted small mb-0">From</label>
                                                <input type="date" name="date_from" 
                                                    class="form-control form-control-sm" 
                                                    value="{{ request()->input('date_from') ?? date('Y-m-01') }}">

                                                <label class="text-muted small mb-0">To</label>
                                                <input type="date" name="date_to" 
                                                    class="form-control form-control-sm" 
                                                    value="{{ request()->input('date_to') ?? date('Y-m-d') }}">

                                                    
                                            </div>

                                            <div class="col-3">
                                                <input type="search" name="term" id="term" 
                                                    class="form-control form-control-sm" 
                                                    placeholder="Search by keyword." 
                                                    value="{{ app('request')->input('term') }}" 
                                                    autocomplete="off">
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
                                        <th>Details</th>
                                        <th>State</th>
                                        <th>Points</th>
                                        <th> Qrcodes</th>
                                        <th>Validity</th>
                                        <th>Date</th>
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
                                        <td>{{($data->firstItem()) + $index}}</td>
                                        <td>
                                        {{$item->name}}
                                        <div class="row__action">
                                            <a href="{{ route('reward.retailer.barcode.view', $item->slug) }}">View</a>
                                        {{-- <a href="{{ route('reward.retailer.barcode.status', $item->id) }}">{{($item->status == 1) ? 'Active' : 'Inactive'}}</a> --}}
                                        
                                        </div>
                                        </td>
                                        <td>{{$item->state->name ??''}}</td>
                                        <td>{{$item->type == 1 ? $item->amount. '' : ''.$item->amount}}</td>
                                        <td>
                                            @php
                                                $couponsCount = \DB::table('retailer_barcodes')->where('slug', $item->slug)->count();
                                            @endphp
                                            <div class="btn-group">
                                                <a href="{{ route('reward.retailer.barcode.view', $item->slug) }}" class="btn btn-sm btn-primary">{{$couponsCount}}</a>
                                                {{-- <a href="{{ route('reward.retailer.barcode.detail.csv.export', $item->slug) }}" class="btn btn-sm btn-warning">Export generated Qrcodes</a> --}}
                                            </div>
                                        </td>
                                        @php
                                                $usedcouponsCount = \DB::table('retailer_barcodes')->where('slug', $item->slug)->where('no_of_usage','!=',0)->count();
                                            @endphp
                                    
                                        <td>{{date('d M Y', strtotime($item->start_date))}} - {{date('d M Y', strtotime($item->end_date))}}</td>
                                        <td>Published<br/>{{date('d M Y', strtotime($item->created_at))}}</td>
                                        {{--<td><span class="badge bg-{{($item->status == 1) ? 'success' : 'danger'}}">{{($item->status == 1) ? 'Active' : 'Inactive'}}</span></td> --}}
                                    </tr>
                                    @empty
                                    <tr><td colspan="100%" class="small text-muted">No data found</td></tr>
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



<div class="modal fade" id="storelimitModal" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                bulk upload
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="" enctype="multipart/form-data" id="borrowerCsvUpload">@csrf
                    <input type="file" name="file" class="form-control" accept=".csv">
                    <br>
                    <a href="">Download Sample CSV</a>
                    <br>
                    <button type="submit" class="btn btn-danger mt-3" id="csvImportBtn">Import <i class="fas fa-upload"></i></button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection