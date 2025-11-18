@extends('layouts.app')

@section('content')
<style>
	.d-btn {
		white-space: nowrap;
		border: none;
		background: transparent;
		color: #0d6efd;
	}
</style>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <div class="card data-card">
                    <div class="card-header">
                        <h4 class="d-flex">Qrcode detail
                            <a href="{{route('reward.retailer.barcode.index') }}" class="btn btn-cta ms-auto">Back</a>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h3 class="text-muted">{{ $data->name }}</h3>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            @if ($data->end_date < \Carbon\Carbon::now() )
                                            <h3 class="text-danger mt-3 fw-bold">EXPIRED</h3>
                                            @endif
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <p class="small text-muted mt-4 mb-2">Details</p>
                                            <table class="">
                                                <tr>
                                                    <td class="text-muted">No of Qrcodes: </td>
                                                    <td>{{count($coupons)}}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">Points: </td>
                                                    <td>{{$data->type == 1 ? $data->amount.' ' : ' '. $data->amount}}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">Max time usage : </td>
                                                    <td>{{$data->max_time_of_use}}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">Max time usage for single user :  </td>
                                                    <td>{{$data->max_time_one_can_use}}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">No of usage : </td>
                                                    <td>{{$data->no_of_usage}}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">Start date: </td>
                                                    <td>{{ date('j M Y h:m A', strtotime($data->start_date)) }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">End date: </td>
                                                    <td>{{ date('j M Y h:m A', strtotime($data->end_date)) }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">Distributor: </td>
                                                    <td>{{ $data->distributor->name ??'' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">State: </td>
                                                    <td>{{ $data->state->name ??'' }}</td>
                                                </tr>
                                            </table>

                                            <hr>

                                            <p class="small text-muted mt-4 mb-2">Qrcodes</p>
                                            <div class="col-md-12 text-end">
                                                <form class="row align-items-end justify-content-end" action="">
                                        
                                                    <div class="col-auto">
                                                        <input type="search" name="keyword" id="keyword" class="form-control form-control-sm" placeholder="Search by QRcode" value="{{request()->input('keyword')}}" autocomplete="off">
                                                    </div>
                                                    <div class="col-auto">
                                                        <div class="btn-group">
                                                            <button type="submit" class="btn btn-danger btn-sm">
                                                                Filter
                                                            </button>

                                                            <a href="{{ url()->current() }}" class="btn btn-sm btn-light" data-bs-toggle="tooltip" title="Clear Filter">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                                            </a>

                                                            <a type="button" class="btn btn-sm btn-danger" data-bs-toggle="tooltip"  id="basic">
                                                            Download pdf
                                                            </a>
                                                            <a href="{{ route('reward.retailer.barcode.csv.export',['slug'=>$data->slug,'keyword'=>$request->keyword]) }}" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="">
                                                                Download CSV
                                                            </a>
                                                            
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                            
                                            <table class="mt-4 table print-code" >
                                                <tr>
                                                    <thead>
                                                        <th>#SR</th>
                                                        <th>Qrcode</th>
                                                        <th>Status</th>
                                                        <th>Action</th>
                                                    </thead>
                                                </tr>
                                                @forelse ($coupons as $couponKey => $coupon)
                                                @php
                                                $finalCode='Code : '.$coupon->code.' Note :'.$coupon->note;
                                                @endphp
                                                <tr>
                                                    <td>{{$couponKey+1}}</td>
                                                    <td>
                                                    <div style="width: 120px;" class="text-center">
                                                        <img src="https://bwipjs-api.metafloor.com/?bcid=qrcode&text={{$coupon->code}}&height=6&textsize=10&scale=6&includetext" alt="" style="height: 105px;width:105px" id="{{$coupon->code}}">
                                                    <p class="text-center my-3">{{$coupon->code}}</p>
                                                    <a href="javascript:void(0);"  class="qr-txt" val="{{$coupon->code}}">Copy Text</a>

                                                        </div>
                                                    
                                                    </td> 
                                                    <td><a href="{{ route('reward.retailer.barcode.status', $coupon->id) }}" class="text-white badge bg-{{($coupon->status == 1) ? 'success' : 'danger'}}">{{($coupon->status == 1) ? 'Active' : 'Inactive'}}</a></td>
                                                    
                                                    <td>
                                                        <a href="{{ route('reward.retailer.barcode.edit', $coupon->id) }}" class="btn btn-cta">
                                                            <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="512" height="512" x="0" y="0" viewBox="0 0 492.493 492" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="M304.14 82.473 33.165 353.469a10.799 10.799 0 0 0-2.816 4.949L.313 478.973a10.716 10.716 0 0 0 2.816 10.136 10.675 10.675 0 0 0 7.527 3.114 10.6 10.6 0 0 0 2.582-.32l120.555-30.04a10.655 10.655 0 0 0 4.95-2.812l271-270.977zM476.875 45.523 446.711 15.36c-20.16-20.16-55.297-20.14-75.434 0l-36.949 36.95 105.598 105.597 36.949-36.949c10.07-10.066 15.617-23.465 15.617-37.715s-5.547-27.648-15.617-37.719zm0 0" fill="#ffffff" opacity="1" data-original="#000000" class=""></path></g></svg>
                                                        </a>
                                                        <a href="{{ route('reward.retailer.barcode.show', $coupon->id) }}" class="btn btn-cta">
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
                                            </table>
                                        
                            
                                        
                                            <table class="table table-sm " id="print-qr-code" style="display: none;">
                                                <tr>
                                                    <th>#SR</th>
                                                
                                                    <th>Qrcode</th>
                                                    
                                                </tr>
                                                @forelse ($coupons as $couponKey => $coupon)
                                                @php
                                                $finalCode='Code : '.$coupon->code.' Note :'.$coupon->note;
                                                @endphp
                                                <tr>
                                                    <td>{{$couponKey+1}}</td>
                                                
                                                <td>
                                                    <div style="width: 120px;" class="text-center">
                                                        
                                                        <img src="https://bwipjs-api.metafloor.com/?bcid=qrcode&text={{$coupon->code}}&height=6&textsize=10&scale=6&includetext" alt="" style="height: 50px;width:50px" id="{{$coupon->code}}">
                                                        <p class="text-center my-3">{{$coupon->code}}</p>
                                                    

                                                    </div>
                                                    
                                                    </td> 
                                                
                                                
                                                    
                                                </tr>
                                                @empty
                                                <tr><td colspan="100%" class="small text-muted">No data found</td></tr>
                                                @endforelse
                                            </table>
                                        
                                        </div>
                                    </div>
                                </div>
                        </div>

      
                    </div>
                </div>
            </div>
        </div>
    </div>



@endsection
@section('script')
<script src="{{ asset('backend/js/printThis.js') }}"></script>
<script>
 $('#basic').on("click", function () {
      $('.print-code').printThis();
    });
	
	function downloadFile(fileName, i) {
        var div1 = document.getElementsByClassName("d-btn");
        console.log(div1);
		console.log(i);
        var imageurl = div1[i-1].getAttribute("data-href");
    $.ajax({
        url: imageurl,
        method: 'GET',
        xhrFields: {
            responseType: 'blob'
        },
        success: function (data) {
            var a = document.createElement('a');
            var url = window.URL.createObjectURL(data);
            a.href = url;
            a.download = fileName;
            document.body.append(a);
            a.click();
            a.remove();
            window.URL.revokeObjectURL(url);
        }
    });
    }

	
	$(document).ready(function(){
    $('.qr-txt').on("click", function(){
        value = $(this).attr('val'); //Upto this I am getting value
		console.log(value);
		toastFire("success", "Copy text successfully");
        var $temp = $("<input>");
          $("body").append($temp);
          $temp.val(value).select();
          document.execCommand("copy");
          $temp.remove();
    })
})
</script>

@endsection
