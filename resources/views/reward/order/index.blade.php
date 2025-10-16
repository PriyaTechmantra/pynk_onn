@extends('layouts.app')

@section('content')

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<div class="container mt-2">
        <div class="row">
            <div class="col-md-12">

                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif

                <div class="card data-card mt-3">
                    <div class="card-header">
                        <h4>Order report

                            <a href="#csvModal" data-bs-toggle="modal" class="btn btn-sm btn-cta float-end">Bulk Upload</a>
                            
                             <a href="{{ route('reward.retailer.order.export.csv', [
                                                'date_from'=>$request->date_from,
                                                'date_to'=>$request->date_to,
                                                'user_id'=>$request->user_id,
                                                'product'=>$request->product,
                                                'term'=>$request->term
                                            ]) }}" class="btn btn-sm btn-cta float-end" data-bs-toggle="tooltip" title="Export data in CSV">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                CSV
                             </a>
                           
                        </h4>

                        <div class="search__filter mb-0">
                            <div class="row">
                                <div class="col-12">
                                </div>
                            </div>
                            <div class="row">
                                        
            
                                 <div class="col-2">
                                    <label class="text-muted small">Date from</label>
                                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request()->input('date_from') ?? date('Y-m-01') }}">
                                </div>
                                <div class="col-2">
                                    <label class="text-muted small">Date to</label>
                                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request()->input('date_to') ?? date('Y-m-d') }}">
                                </div>
                                <div class="col-2">
                                    <label class="small text-muted">Product</label>
                                    <select name="product" class="form-control select2">
                                        <option value="" disabled>Select</option>
                                        <option value="" {{ request()->input('product') == 'all' ? 'selected' : '' }}>All</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" {{ request()->input('product') == $product->id ? 'selected' : '' }}>
                                                {{ $product->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-3">
                                    <input type="search" name="term" class="form-control" placeholder="Search for Order No" value="{{ request()->input('term') }}">
                                </div>
                                <div class="col-3">
                                    <div class="text-end">
                                        <button type="submit" class="btn btn-sm btn-cta">
                                            Filter
                                        </button>
                    
                                        <a href="{{ url()->current() }}" class="btn btn-sm btn-cta" data-bs-toggle="tooltip" title="Clear Filter">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                        </a>
                                       
                                    </div>
                                        
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#SR</th>
                                        <th>Qty</th>
                                        <th>Order No</th>
                                        <th>Store</th>
                                        <th>Email</th>
                                        <th>Mobile</th>
                                        <th>Date</th>
                                        <th>Approval</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $all_orders_total_amount = 0; @endphp
                                    @forelse ($data as $index => $item)
                                        @php $all_orders_total_amount += $item->qty; @endphp
                                        <tr id="row_{{ $item->id }}">
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $item->qty }}</td>
                                            <td>
                                                #{{ $item->order_no }}
                                                <div class="row__action">
                                                    <a href="{{ route('reward.retailer.order.view', $item->id) }}">View</a>
                                                </div>
                                            </td>
                                            <td>{{ $item->shop_name ?? '' }}</td>
                                            <td>{{ $item->email ?? '' }}</td>
                                            <td>{{ $item->mobile ?? '' }}</td>
                                            <td>{{ date('j M Y g:i A', strtotime($item->created_at)) }}</td>
                                            <td>
                                                @if($item->asm_approval == 1)<span class="badge bg-success">Approved by ASM</span>
                                                @elseif($item->asm_approval == 0)<span class="badge bg-danger">Hold by ASM</span>
                                                @elseif($item->rsm_approval == 1)<span class="badge bg-success">Approved by RSM</span>
                                                @elseif($item->rsm_approval == 0)<span class="badge bg-danger">Hold by RSM</span>
                                                @elseif($item->vp_approval == 1)<span class="badge bg-success">Approved by VP</span>
                                                @elseif($item->vp_approval == 0)<span class="badge bg-danger">Hold by VP</span>
                                                @elseif($item->distributor_approval == 1)<span class="badge bg-success">Approved by Distributor</span>
                                                @elseif($item->distributor_approval == 0)<span class="badge bg-danger">Hold by Distributor</span>
                                                @else<span class="badge bg-secondary">Waiting for approval</span>@endif
                                            </td>
                                            <td>
                                                    @if($item->admin_status == 2)
                                                        <div class="btn-group">
                                                            <a href="{{ route('reward.retailer.order.approval', [$item->id, 1]) }}" class="btn btn-outline-primary btn-sm">Approved</a>
                                                            <a href="{{ route('reward.retailer.order.approval', [$item->id, 0]) }}" class="btn btn-outline-danger btn-sm">Rejected</a>
                                                        </div>
                                                    @elseif($item->admin_status == 1)
                                                        <span class="badge bg-success">Approved</span>
                                                    @else
                                                        <span class="badge bg-danger">Rejected</span>
                                                    @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="100%" class="small text-muted">No data found</td></tr>
                                    @endforelse
                                    <tr>
                                        <td></td>
                                        <td><strong>TOTAL</strong></td>
                                        <td><strong>{{ number_format($all_orders_total_amount) }}</strong></td>
                                        <td colspan="6"></td>
                                    </tr>
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

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2();
    });
</script>
@endsection

<!-- 
<style>
    .chat_box {
        width: 300px;
        height: 100%;
        position: fixed;
        top: 0;
        right: 0;
        z-index: 999;
        display: flex;
        background: #fff;
        transform: translateX(100%);
        transition: all ease-in-out 0.5s;
    }
    .chat_box.active {
        transform: translateX(0%);
        box-shadow: 10px 10px 100px 10px rgb(0 0 0 / 30%);
    }
    .chat_box .card { width: 100%; margin: 0; }
    .chat_box .card-body { overflow: auto; margin-bottom: 42px; display: flex; flex-direction: column-reverse; }
    .chat_box .card-footer { position: fixed; bottom: 0; }
    .text-body { border-radius: 10px 10px 0 10px; }
    .text-body p { white-space: normal; text-align: right; color: #fff; line-height: 1.25; }
</style> -->



