@extends('layouts.app')

@section('content')
<div class="container mt-5">
        <div class="row">
            <div class="col-md-12">

                <div class="card data-card">
                    <div class="card-header">
                        <h4 class="d-flex">Used Qrcode detail
                            <a href="{{ route('reward.retailer.barcode.index') }}" class="btn btn-cta ms-auto">Back</a>
                        
                        </h4>
                    </div>
                    <div class="card-body">
                        
                            @if(!empty($data))
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
                                                        <td>{{ date('j M Y H:i a', strtotime($data->start_date)) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">End date: </td>
                                                        <td>{{ date('j M Y H:i a', strtotime($data->end_date)) }}</td>
                                                    </tr>
                                                </table>

                                                <hr>

                                                <p class="small text-muted mt-4 mb-2">Qrcodes</p>
                                                <a type="button" id="basic" class="btn btn-outline-danger btn-sm">Download pdf</a>
                                                <!-- <table class="table table-sm print-code" style="display:none">
                                                    <tr>
                                                        <th>#SR</th>
                                                        <th>Qrcode</th>
                                                        
                                                    </tr>
                                                    @forelse ($coupons as $couponKey => $coupon)
                                                    <tr>
                                                        <td>{{$couponKey+1}}</td>
                                                    <td><img src="https://bwipjs-api.metafloor.com/?bcid=qrcode&text={{$coupon->code}}&height=6&textsize=10&scale=6&includetext" alt="" style="height: 105px;width:105px"></td> 
                                                        
                                                    </tr>
                                                    @empty
                                                    <tr><td colspan="100%" class="small text-muted">No data found</td></tr>
                                                    @endforelse
                                                </table> -->
                                                <table class="table print-code" >
                                                    <tr>
                                                        <thead>
                                                            <th>#SR</th>
                                                            <th>Used Qrcode</th>
                                                            <th>Usage</th>
                                                            <th>User details</th>
                                                            <th>Scanned Points</th>
                                                            <th>Time</th>
                                                        <thead>
                                                    </tr>
                                                    @forelse ($coupons as $couponKey => $coupon)
                                                    @php
                                                        $usageCode = \App\Models\RetailerWalletTxn::where('barcode_id',$coupon->id)->with('users')->first();
                                                    //dd($usageCode);
                                                    @endphp
                                                    <tr>
                                                        <td>{{$couponKey+1}}</td>
                                                        
                                                    
                                                        <td><div style="width: 120px;" class="text-center">
                                                            <img src="https://chart.apis.google.com/chart?cht=qr&chs=300x300&chl={{$coupon->code}}" alt="" style="height: 105px;width:105px">

                                                        <p class="text-center my-3">{{$coupon->code}}</p></div>
                                                        
                                                        </td> 
                                                        
                                                        <td>
                                                            @if($coupon->no_of_usage >= $coupon->max_time_use)
                                                                {{$coupon->no_of_usage}}
                                                                    
                                                            @else
                                                                <p class="small text-danger">Not used yet</p>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            
                                                                <p colspan="100%" class="small text-dark">{{$usageCode->users->owner_name ?? ''}}</p>
                                                                <p colspan="100%" class="small text-muted">{{$usageCode->users->store_name ?? ''}}</p>
                                                        
                                                            <p class="small mb-0">{{$usageCode->users->email ?? ''}} </p>
                                                        </td>
                                                        <td>{{$usageCode->amount??''}}</td>
                                                        <td>{{ date('j M Y H:i a', strtotime($usageCode->created_at??'')) }}</td>
                                                    </tr>
                                                    @empty
                                                    <tr><td colspan="100%" class="small text-muted">No data found</td></tr>
                                                    @endforelse
                                                    
                                                </table>
                                            
                                            </div>
                                        </div>
                            @endif
                          
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
</script>
@endsection
