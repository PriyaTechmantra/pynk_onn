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
                        <h4>Store wise monthly scan report
                            <a href="{{ route('reward.qrcode.redeem.retailer.scan.report.csv.export', [
                                    'month' => request('month'),
                                    'keyword' => request('keyword'),
                                    'brand_selection' => request('brand_selection')
                                ]) }}" class="btn btn-sm btn-cta float-end" data-bs-toggle="tooltip" title="Export data in CSV">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                CSV
                            </a>
                        </h4>
                        <div class="search__filter mb-0">
                            <div class="row">
                                <div class="col-12">
                                    <p class="text-muted mt-1 mb-0">Showing {{$data->count()}} out of {{$data->total()}} Entries</p>
                                </div>
                                
                                
                                <div class="col-md-12 text-end">
                                    <form class="row align-items-end justify-content-end" action="{{route('reward.qrcode.redeem.retailer.scan.report')}}" method="GET">
                                         <div class="col-2">
                                                <label class="small text-muted">Brand</label>

                                                <select name="brand_selection" class="form-control form-control-sm">
                                                    <option value="">Select Brand</option>
                                                    <option value="3" {{ request()->input('brand_selection') == 3 ? 'selected' : '' }}>ALL</option>
                                                    <option value="1" {{ request()->input('brand_selection') == 1 ? 'selected' : '' }}>ONN</option>
                                                    <option value="2" {{ request()->input('brand_selection') == 2 ? 'selected' : '' }}>PYNK</option>
                                                </select>
                                            </div>

                                        <div class="col-auto">
                                            <label for="month" class="text-muted small">Month</label>
                                            <input type="month" name="month" id="month" class="form-control form-control-sm" aria-label="Default select example" value="{{$month}}">
                                        </div>
                                        
                                        
                                        <div class="col-auto">
                                            <input type="search" name="keyword" class="form-control form-control-sm" placeholder="Search by store name/ firm name" value="{{ request('keyword') }}" autocomplete="off">
                                        </div>
                                        <div class="col-auto text-end">
                                                <button type="submit" class="btn btn-sm btn-cta">
                                                    Filter
                                                </button>

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
                                        <th>Created by</th>
                                        <th>Contact</th>
                                        <th>Distributor</th>
                                        <th>Scan Limit Per Month</th>
                                        <th>Scan Count Monthly</th>
                                        <th>Scan Left Monthly</th>
                                        <th>Onn Currency</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($data as $index => $item)
                                
                                        @php
                                        $ase = $item->user_id;
                                        $username = \App\Models\User::select('name')->where('id', $ase)->first();
                                        $displayASEName = '';
                                        foreach(explode(',',$item->user_id) as $aseKey => $aseVal) 
                                        {
                                            //dd($distVal);
                                            $catDetails = DB::table('employees')->where('id', $aseVal)->get();
                                    
                                            if(count($catDetails)>0){
                                                $displayASEName .=  $catDetails[0]->name.',';
                                            }else{
                                                $displayASEName .= '';
                                            }
                                        
                                        }
                                        
                                        \DB::enableQueryLog();
                                        $scanCount=\App\Models\RetailerWalletTxn::where('user_id', $item->id)->where('type',1)->where('created_at', 'like','%'.$month.'%')->count();
                                        
                                        //dd(\DB::getQueryLog());
                                        $scanLeft=20-$scanCount;
                                        @endphp

                                        <tr>
                                            {{-- <td>{{ $index + $data->firstItem() }}</td> --}}
                                            <td>
                                                {{ $item ? $item->unique_code : '' }}
                                            </td>
                                            <td>
                                                {{ ucwords($item->name) }}
                                                <p class="small text-muted">- {{ ucwords($item->bussiness_name) }}</p>
                                                
                                            </td>
                                            <td>
                                                {{ substr($displayASEName, 0, -1) ? substr($displayASEName,0, -1) : '' }}
                                            </td>
                                            <td>{{ $item->email }}<br>{{ $item->contact }}</td>
                                            <td>
                                                @php
                                                $did = $item->distributor_id;
                                                $dis_name = \App\Models\Distributor::select('name')->where('id', $did)->first();
                                                @endphp
                                                {{ $dis_name->name ?? '' }}
                                            </td>
                                            <td>20</td>
                                            <td>
                                                {{ $scanCount ??'' }}
                                            </td> 
                                            <td>{{ $scanLeft ??'' }}
                                            </td>
                                            <td>{{ $item->wallet }}</td>
                                            
                                            
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

	
	<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });
    </script>

@endsection