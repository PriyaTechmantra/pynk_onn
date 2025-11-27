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
                                        @forelse ($data as $index => $item)
                                            <tr id="row_{{ $item->id }}">
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $item->qty }}</td>
                                                <td>
                                                    #{{ $item->order_no }}
                                    
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
                                                    @elseif($item->distributor_approval == 1)<span class="badge bg-success">Approved by Distributor</span>
                                                    @elseif($item->distributor_approval == 0)<span class="badge bg-danger">Hold by Distributor</span>
                                                    @else<span class="btn btn-secondary">Waiting for approval</span>@endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('reward.retailer.order.view', $item->id) }}" class="btn btn-cta">
                                                        <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="512" height="512" x="0" y="0" viewBox="0 0 511.999 511.999" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="M508.745 246.041c-4.574-6.257-113.557-153.206-252.748-153.206S7.818 239.784 3.249 246.035a16.896 16.896 0 0 0 0 19.923c4.569 6.257 113.557 153.206 252.748 153.206s248.174-146.95 252.748-153.201a16.875 16.875 0 0 0 0-19.922zM255.997 385.406c-102.529 0-191.33-97.533-217.617-129.418 26.253-31.913 114.868-129.395 217.617-129.395 102.524 0 191.319 97.516 217.617 129.418-26.253 31.912-114.868 129.395-217.617 129.395z" fill="#ffffff" opacity="1" data-original="#000000" class=""></path><path d="M255.997 154.725c-55.842 0-101.275 45.433-101.275 101.275s45.433 101.275 101.275 101.275S357.272 311.842 357.272 256s-45.433-101.275-101.275-101.275zm0 168.791c-37.23 0-67.516-30.287-67.516-67.516s30.287-67.516 67.516-67.516 67.516 30.287 67.516 67.516-30.286 67.516-67.516 67.516z" fill="#ffffff" opacity="1" data-original="#000000" class=""></path></g></svg>
                                                    </a>
                                                        <div class="btn-group mt-1">

                                                            @if($item->admin_status == 2)
                                                                    <a href="{{ route('reward.retailer.order.approval', [$item->id, 1]) }}" class="btn btn-primary btn-sm">Approved</a>
                                                                    <a href="{{ route('reward.retailer.order.approval', [$item->id, 0]) }}" class="btn btn-danger btn-sm">Rejected</a>
                                                            @elseif($item->admin_status == 1)
                                                                <span class="btn btn-success btn-sm">Approved</span>
                                                            @else
                                                                <span class="btn btn-danger btn-sm">Rejected</span>
                                                            @endif
                                                        </div>

                                                </td>
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



