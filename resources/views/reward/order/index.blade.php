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
                        <h4>Reward Order report

                             @can('retailer reward order csv download')
                             <a href="{{ route('reward.retailer.order.export.csv', [
                                                'date_from'=>$request->date_from,
                                                'date_to'=>$request->date_to,
                                                'product'=>$request->product,
                                                'term'=>$request->term
                                            ]) }}" class="btn btn-sm btn-cta float-end" data-bs-toggle="tooltip" title="Export data in CSV">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                CSV
                             </a>
                            @endif
                        </h4>

                        <div class="search__filter mb-0">
                             <div class="row">
                                <div class="col-12">
                                    <p class="text-muted mt-1 mb-0">Showing {{$data->count()}} Entries</p>
                                </div>
                            </div>
                            <form action="{{route('reward.retailer.order.index')}}">
                                <div class="row">
                                    <div class="col-3">
                                        <label class="text-muted small">Date from</label>
                                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request()->input('date_from') ?? date('Y-m-01') }}">
                                    </div>
                                    <div class="col-3">
                                        <label class="text-muted small">Date to</label>
                                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request()->input('date_to') ?? date('Y-m-d') }}">
                                    </div>
                                    <div class="col-3">
                                        <label class="small text-muted">Product</label>
                                        <select name="product" id="productSelect" class="form-control select2" style="width: 100%;">
                                            <option value="">Select product</option>
                                        </select>
                                    </div>
                                    <div class="col-3">
                                        <label class="small text-muted">Search for Order No</label>
                                        <input type="search" name="term" class="form-control form-control-sm" placeholder="Search here.." value="{{ request()->input('term') }}">
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
                            </form>
                        </div>
                        <div class="card-body">
                            
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th class="index-col">#</th>
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
                                        @php
                                            $all_orders_total_amount = 0;
                                        @endphp
                                        @forelse ($data as $index => $item)
                                            @php

                                                 $all_orders_total_amount += ($item->qty);
                                            @endphp
                                            <tr id="row_{{ $item->id }}">
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $item->qty }}</td>
                                                <td>
                                                    #{{ $item->order_no }}
                                    
                                                </td>
                                                <td>{{ $item->user->name ?? '' }}</td>
                                                <td>{{ $item->user->email ?? '' }}</td>
                                                <td>{{ $item->user->mobile ?? '' }}</td>
                                                <td>{{ date('j M Y g:i A', strtotime($item->created_at)) }}</td>
                                                <td>
                                                    @if($item->asm_approval== 1) <span class="badge bg-success">Approved by ASM </span>
                                                    @elseif($item->asm_approval == 0)<span class="badge bg-danger">Hold by ASM </span>
                                                    @elseif($item->rsm_approval == 1) <span class="badge bg-success">Approved by RSM </span>
                                                    @elseif($item->rsm_approval == 0) <span class="badge bg-danger">Hold by RSM </span>
                                                    @elseif($item->vp_approval == 0) <span class="badge bg-danger">Hold by VP </span>
                                                    @elseif($item->vp_approval == 1) <span class="badge bg-success">Approved by VP </span>
                                                    @elseif($item->distributor_approval == 1) <span class="badge bg-success">Approved by Distributor </span>                 @elseif($item->distributor_approval == 0) <span class="badge bg-danger">Hold by Distributor </span>
                                                    @else
								                        <span class="badge bg-secondary">Waiting for approval </span>
                                                    @endif
								                    @if($item->asm_approval == 0)
									                    <p>{{$item->asm_note}}</p>
								                    @elseif($item->rsm_approval == 0) <p>{{$item->rsm_note}}</p>
								                    @elseif($item->vp_approval == 0) <p>{{$item->vp_note}}</p>
								                    @elseif($item->distributor_approval == 0) <p>{{$item->distributor_note}}</p>
									                @endif
                                                </td>
							                    @can('reward order status change')
                                                    <td>
                                                        @if($item->asm_approval != 2 || $item->rsm_approval != 2 || $item->vp_approval != 2 || $item->distributor_approval!=2)
                                                            @if($item->admin_status == 2)
                                                            <div class="btn-group" role="group">
                                                                <a href="{{ route('reward.retailer.order.approval', [$item->id, 1]) }}" type="button" class="status_1 btn btn-outline-primary btn-sm {{($item->admin_status == 1) ? 'active' : ''}}">Approved</a>

                                                                <a href="{{ route('reward.retailer.order.approval', [$item->id, 0]) }}" type="button" class="status_2 btn btn-outline-danger btn-sm {{($item->admin_status == 0) ? 'active' : ''}}">Rejected</a>
                                                            </div>
                                                            @elseif($item->admin_status == 1)
                                                                <span class="badge bg-success">Approved</span>
                                                            @else <span class="badge bg-danger">Rejected</span>
                                                            @endif
                                                        @endif
                                                        @if($item->admin_status == 0)
                                                        <form action="{{ route('reward.retailer.order.note.save')}}" method="POST">
                                                            @csrf
                                                            <div id="textbox" >
                                                                <label for="text">Reason for rejection:</label>
                                                                <input type="hidden" name="id" value="{{$item->id}}">
                                                                <textarea type="text" id="text" name="admin_note" row="3" col="3" >{{$item->admin_note}}</textarea>
                                                                <button type="submit"  title="" class="btn btn-sm btn-danger" data-bs-original-title="Search">Save</button>
                                                            </div>
                                                        </form>
                                                        @endif
                                                    </td>
							                    @endcan
                                            </tr>
                                        @empty
                                            <tr><td colspan="9" class="text-center">No record found</td></tr>
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
</script>

<script>
$(document).ready(function() {
    $('#productSelect').select2({
        placeholder: "Search product...",
        minimumInputLength: 2,  // start search after typing 2 letters
        ajax: {
            url: "{{ route('reward.retailer.product.product.search.ajax') }}",
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return { keyword: params.term };
            },
            processResults: function(data) {
                return {
                    results: $.map(data, function(item) {
                        return {
                            id: item.id,
                             text: item.title + " (" + item.amount + ")"
                        }
                    })
                };
            }
        }
    });

    // KEEP OLD SELECTED VALUE (for filters)
    @if(request()->input('product'))
        var id = "{{ request()->input('product') }}";
        var text = "{{ $selectedProductName ?? '' }}";

        var option = new Option(text, id, true, true);
        $('#productSelect').append(option).trigger('change');
    @endif
});
</script>

@endsection



