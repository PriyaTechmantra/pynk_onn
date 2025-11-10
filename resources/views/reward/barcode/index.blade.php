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
                            <a href="{{route('reward.retailer.barcode.create')}}" class="btn btn-sm btn-cta float-end">Generate New Qrcodes</a>
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
                                        <!-- <th>State</th> -->
                                        <th>Points</th>
                                        <th> Qrcodes</th>
                                        <th>Used Qrcodes</th>
                                        <th>Validity</th>
                                        <th>Date</th>
                                        <th width="20%">Action</th>

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
                                        <!-- <div class="row__action">
                                            <a href="{{ route('reward.retailer.barcode.view', $item->slug) }}">View</a>
                                        
                                        </div> -->
                                        </td>
                                        <!-- <td>{{$item->state->name ??''}}</td> -->
                                        <td>{{$item->type == 1 ? $item->amount. '' : ''.$item->amount}}</td>
                                        <td>
                                            @php
                                                $couponsCount = \DB::table('retailer_barcodes')->where('slug', $item->slug)->count();
                                            @endphp
                                            <div class="btn-group">
                                                <a href="{{ route('reward.retailer.barcode.view', $item->slug) }}" class="btn btn-sm btn-primary">{{$couponsCount}}</a>
                                            </div>
                                        </td>
                                        <td>

                                            @php
                                                    $usedcouponsCount = \DB::table('retailer_barcodes')->where('slug', $item->slug)->where('no_of_usage','!=',0)->count();
                                            @endphp
                                            <div class="btn-group">
                                                <a href="{{ route('reward.retailer.barcode.useqrcode', $item->slug) }}" class="btn btn-sm btn-primary">{{$usedcouponsCount}}</a>
                                            </div>
                                        </td>
                                    
                                        <td>{{date('d M Y', strtotime($item->start_date))}} - {{date('d M Y', strtotime($item->end_date))}}</td>
                                        <td>Published<br/>{{date('d M Y', strtotime($item->created_at))}}</td>
                                        {{--<td><span class="badge bg-{{($item->status == 1) ? 'success' : 'danger'}}">{{($item->status == 1) ? 'Active' : 'Inactive'}}</span></td> --}}
                                         <td>
                                            <!-- <a href="{{route('news.edit', $item->id) }}" class="btn btn-cta">
                                            <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="512" height="512" x="0" y="0" viewBox="0 0 492.493 492" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="M304.14 82.473 33.165 353.469a10.799 10.799 0 0 0-2.816 4.949L.313 478.973a10.716 10.716 0 0 0 2.816 10.136 10.675 10.675 0 0 0 7.527 3.114 10.6 10.6 0 0 0 2.582-.32l120.555-30.04a10.655 10.655 0 0 0 4.95-2.812l271-270.977zM476.875 45.523 446.711 15.36c-20.16-20.16-55.297-20.14-75.434 0l-36.949 36.95 105.598 105.597 36.949-36.949c10.07-10.066 15.617-23.465 15.617-37.715s-5.547-27.648-15.617-37.719zm0 0" fill="#ffffff" opacity="1" data-original="#000000" class=""></path></g></svg>
                                            </a> -->
                                           <a href="{{ route('reward.retailer.barcode.view', $item->slug) }}" class="btn btn-cta">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="20" height="20">
                                                    <path d="M508.745 246.041c-4.574-6.257-113.557-153.206-252.748-153.206S7.818 239.784 3.249 246.035a16.896 16.896 0 0 0 0 19.923c4.569 6.257 113.557 153.206 252.748 153.206s248.174-146.95 252.748-153.201a16.875 16.875 0 0 0 0-19.922zM255.997 385.406c-102.529 0-191.33-97.533-217.617-129.418 26.253-31.913 114.868-129.395 217.617-129.395 102.524 0 191.319 97.516 217.617 129.418-26.253 31.912-114.868 129.395-217.617 129.395z" fill="#ffffff"/>
                                                    <path d="M255.997 154.725c-55.842 0-101.275 45.433-101.275 101.275s45.433 101.275 101.275 101.275S357.272 311.842 357.272 256s-45.433-101.275-101.275-101.275zm0 168.791c-37.23 0-67.516-30.287-67.516-67.516s30.287-67.516 67.516-67.516 67.516 30.287 67.516 67.516-30.286 67.516-67.516 67.516z" fill="#ffffff"/>
                                                </svg>
                                            </a>
                                           
                                        </td>
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