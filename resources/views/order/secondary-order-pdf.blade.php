@extends('layouts.app')

@section('page', $data->order_no.' report')

@section('content')
<section>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-6">
                    <a href="{{ route('secondary.orders.index') }}" class="btn btn-dark">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-left"><polyline points="15 18 9 12 15 6"></polyline></svg>
                        Back to all Orders
                    </a>
                </div>
                <div class="col-6 text-end">
                    <a href="javascript: void(0)" class="btn btn-primary" onclick="printInvoice()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-printer"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                        Print
                    </a>
                </div>
            </div>

            <div class="printDiv" id="DivIdToPrint" style="margin-top:25px;">
                <div style="border:2px solid #000; padding: 0px 27px;">
                    <div class="row">
                        <div class="col-12 text-center">
                            <h3 style="margin: 28px 0;">Order Form</h3>
                        </div>
                    </div>

                    <div class="row align-items-center">
                        <div class="col-6">
                            <div style="padding:0 15px;">
                                <h4 style="font-weight: 500;">Lux Industries Limited</h4>
                                <p style="margin-bottom:4px;">17th floor, North Wing</p>
                                <p style="margin-bottom:4px;">Adventz Infinity</p>
                                <p style="margin-bottom:4px;">BN - 5, Sector V</p>
                                <p>Kolkata - 700091, W.B., India</p>
                            </div>
                        </div>
                        
                       
                        <div class="col-6">
                            <div style="padding: 0 15px 14px; border-left:2px solid #000;">
                                <p><strong>Order no./ Date:</strong> <u>{{$data->order_no}}/ {{date('d.m.Y', strtotime($data->created_at))}}</u></p>
                                <p><strong>Print Date:</strong> <u>{{date('d.m.Y')}}</u></p>
                                <p><strong>From:</strong></p>
                                <p><strong>M/S: </strong> <u>{{$data->stores ? $data->stores->name : ''}}</u></p>
                                @if ($data->stores)
                                    <p><u>{{ $data->stores->address.' '.$data->stores->area->name.' '.$data->stores->state->name.' '.$data->stores->city.' '.$data->stores->pin }}</u></p>
                                    <p><strong>Booking Place:</strong> <u>{{ $data->stores->city ? $data->stores->city : $data->stores->area->name }}</u></p>
                                @endif
                                @if(!empty($data->users))
                                    <p style="margin-bottom:0;"><strong>ASE:</strong> <u>{{$data->users ? $data->users->name : ''}}</u></p>
                                @else
                                    <p style="margin-bottom:0;"><strong>ASE:</strong> <u>{{$data->users ? $data->users->name : ''}}</u></p>
                                @endif
                            </div>

                        </div>
                    </div>
                </div>
                <div style="border:2px solid #000;  margin-top:25px;">
                    <div class="row">
                        <div class="col-12">
                            <table class="table table-sm table-bordered" >
                                <thead>
                                    @if(count(orderProductsUpdatedMatrix($data->orderProducts)) > 0)
                                    <tr>
                                        <th style="color: #6c757d; font-size: 13px; min-width:200px; border-bottom:2px solid #000; width: 242px;">Name of Quality Shape & Unit</th>
                                        <th style="color: #6c757d; font-size: 13px; border-left:2px solid #000; border-bottom:2px solid #000;">75</th>
                                        <th style="color: #6c757d; font-size: 13px; border-left:2px solid #000; border-bottom:2px solid #000;">80</th>
                                        <th style="color: #6c757d; font-size: 13px; border-left:2px solid #000; border-bottom:2px solid #000;">85</th>
                                        <th style="color: #6c757d; font-size: 13px; border-left:2px solid #000; border-bottom:2px solid #000;">90</th>
                                        <th style="color: #6c757d; font-size: 13px; border-left:2px solid #000; border-bottom:2px solid #000;">95</th>
                                        <th style="color: #6c757d; font-size: 13px; border-left:2px solid #000; border-bottom:2px solid #000;">100</th>
                                        <th style="color: #6c757d; font-size: 13px; border-left:2px solid #000; border-bottom:2px solid #000;">105</th>
                                        <th style="color: #6c757d; font-size: 13px; border-left:2px solid #000; border-bottom:2px solid #000;">110</th>
                                        <th style="color: #6c757d; font-size: 13px; border-left:2px solid #000; border-bottom:2px solid #000;">115</th>
                                        <th style="color: #6c757d; font-size: 13px; border-left:2px solid #000; border-bottom:2px solid #000;">120</th>
                                        <th style="color: #6c757d; font-size: 13px; border-left:2px solid #000; border-bottom:2px solid #000;">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(orderProductsUpdatedMatrix($data->orderProducts) as $productKey => $productValue)
                                    
                                    <tr>
                                        <td style="; border-bottom:2px solid #000;">
                                            <p class="small text-dark fw-bold mb-0">{{$productValue['product_name']}}</p>
                                            <p class="small text-dark fw-bold mb-0">{{$productValue['product_style_no']}}</p>
                                            <p class="small text-dark fw-bold mb-0">{{$productValue['color']}}</p>
                                        </td>
                                        <td style="border-left:2px solid #000; border-bottom:2px solid #000;"><p class="small text-dark fw-bold mb-0">{{ $productValue['75'] ? $productValue['75'] : '' }}</p></td>
                                        <td style="border-left:2px solid #000; border-bottom:2px solid #000;"><p class="small text-dark fw-bold mb-0">{{ $productValue['80'] ? $productValue['80'] : '' }}</p></td>
                                        <td style="border-left:2px solid #000; border-bottom:2px solid #000;"><p class="small text-dark fw-bold mb-0">{{ $productValue['85'] ? $productValue['85'] : '' }}</p></td>
                                        <td style="border-left:2px solid #000; border-bottom:2px solid #000;"><p class="small text-dark fw-bold mb-0">{{ $productValue['90'] ? $productValue['90'] : '' }}</p></td>
                                        <td style="border-left:2px solid #000; border-bottom:2px solid #000;"><p class="small text-dark fw-bold mb-0">{{ $productValue['95'] ? $productValue['95'] : '' }}</p></td>
                                        <td style="border-left:2px solid #000; border-bottom:2px solid #000;"><p class="small text-dark fw-bold mb-0">{{ $productValue['100'] ? $productValue['100'] : '' }}</p></td>
                                        <td style="border-left:2px solid #000; border-bottom:2px solid #000;"><p class="small text-dark fw-bold mb-0">{{ $productValue['105'] ? $productValue['105'] : '' }}</p></td>
                                        <td style="border-left:2px solid #000; border-bottom:2px solid #000;"><p class="small text-dark fw-bold mb-0">{{ $productValue['110'] ? $productValue['110'] : '' }}</p></td>
                                        <td style="border-left:2px solid #000; border-bottom:2px solid #000;"><p class="small text-dark fw-bold mb-0">{{ $productValue['115'] ? $productValue['115'] : '' }}</p></td>
                                        <td style="border-left:2px solid #000; border-bottom:2px solid #000;"><p class="small text-dark fw-bold mb-0">{{ $productValue['120'] ? $productValue['120'] : '' }}</p></td>
                                        <td style="border-left:2px solid #000; border-bottom:2px solid #000;"><p class="small text-dark fw-bold mb-0">{{$productValue['total']}}</p></td>
                                    </tr>
                                    @endforeach
                                    @endif
                                </tbody>
                                @if(count(orderProductsUpdatedMatrixChild($data->orderProducts)) > 0)
                                <thead>
                                    <tr>
                                        <th style="color: #6c757d; font-size: 13px; min-width:200px; border-bottom:2px solid #000; width: 242px;">Name of Quality Shape & Unit</th>
                                        <th style="color: #6c757d; font-size: 13px; border-left:2px solid #000; border-bottom:2px solid #000;">35</th>
                                        <th style="color: #6c757d; font-size: 13px; border-left:2px solid #000; border-bottom:2px solid #000;">40</th>
                                        <th style="color: #6c757d; font-size: 13px; border-left:2px solid #000; border-bottom:2px solid #000;">45</th>
                                        <th style="color: #6c757d; font-size: 13px; border-left:2px solid #000; border-bottom:2px solid #000;">50</th>
                                        <th style="color: #6c757d; font-size: 13px; border-left:2px solid #000; border-bottom:2px solid #000;">55</th>
                                        <th style="color: #6c757d; font-size: 13px; border-left:2px solid #000; border-bottom:2px solid #000;">60</th>
                                        <th style="color: #6c757d; font-size: 13px; border-left:2px solid #000; border-bottom:2px solid #000;">65</th>
                                        <th style="color: #6c757d; font-size: 13px; border-left:2px solid #000; border-bottom:2px solid #000;">70</th>
										<th style="color: #6c757d; font-size: 13px; border-left:2px solid #000; border-bottom:2px solid #000;">73</th>
                                        <th style="color: #6c757d; font-size: 13px; border-left:2px solid #000; border-bottom:2px solid #000;"></th>
                                        <th style="color: #6c757d; font-size: 13px; border-left:2px solid #000; border-bottom:2px solid #000;">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(orderProductsUpdatedMatrixChild($data->orderProducts) as $productKey => $productValue)
                                    <tr>
                                        <td style="; border-bottom:2px solid #000;">
                                            <p class="small text-dark fw-bold mb-0">{{$productValue['product_name']}}</p>
                                            <p class="small text-dark fw-bold mb-0">{{$productValue['product_style_no']}}</p>
                                            <p class="small text-dark fw-bold mb-0">{{$productValue['color']}}</p>
                                        </td>
                                        <td style="border-left:2px solid #000; border-bottom:2px solid #000;"><p class="small text-dark fw-bold mb-0">{{ $productValue['35'] ? $productValue['35'] : '' }}</p></td>
                                        <td style="border-left:2px solid #000; border-bottom:2px solid #000;"><p class="small text-dark fw-bold mb-0">{{ $productValue['40'] ? $productValue['40'] : '' }}</p></td>
                                        <td style="border-left:2px solid #000; border-bottom:2px solid #000;"><p class="small text-dark fw-bold mb-0">{{ $productValue['45'] ? $productValue['45'] : '' }}</p></td>
                                        <td style="border-left:2px solid #000; border-bottom:2px solid #000;"><p class="small text-dark fw-bold mb-0">{{ $productValue['50'] ? $productValue['50'] : '' }}</p></td>
                                        <td style="border-left:2px solid #000; border-bottom:2px solid #000;"><p class="small text-dark fw-bold mb-0">{{ $productValue['55'] ? $productValue['55'] : '' }}</p></td>
                                        <td style="border-left:2px solid #000; border-bottom:2px solid #000;"><p class="small text-dark fw-bold mb-0">{{ $productValue['60'] ? $productValue['60'] : '' }}</p></td>
                                        <td style="border-left:2px solid #000; border-bottom:2px solid #000;"><p class="small text-dark fw-bold mb-0">{{ $productValue['65'] ? $productValue['65'] : '' }}</p></td>
                                        <td style="border-left:2px solid #000; border-bottom:2px solid #000;"><p class="small text-dark fw-bold mb-0">{{ $productValue['70'] ? $productValue['70'] : '' }}</p></td>
                                        <td style="border-left:2px solid #000; border-bottom:2px solid #000;"><p class="small text-dark fw-bold mb-0">{{ $productValue['73'] ? $productValue['73'] : '' }}</p></td>
										<td style="border-left:2px solid #000; border-bottom:2px solid #000;"><p class="small text-dark fw-bold mb-0"></p></td>
                                        <td style="border-left:2px solid #000; border-bottom:2px solid #000;"><p class="small text-dark fw-bold mb-0">{{$productValue['total']}}</p></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                @endif
                                
                                @if(count(orderProductsUpdatedMatrixNew($data->orderProducts)) > 0)
                                <thead>
                                    <tr>
                                        <th style="color: #6c757d; font-size: 13px; min-width:200px; border-bottom:2px solid #000; width: 242px;">Name of Quality Shape & Unit</th>
                                        <th style="color: #6c757d; font-size: 13px; border-left:2px solid #000; border-bottom:2px solid #000;">S</th>
                                        <th style="color: #6c757d; font-size: 13px; border-left:2px solid #000; border-bottom:2px solid #000;">M</th>
                                        <th style="color: #6c757d; font-size: 13px; border-left:2px solid #000; border-bottom:2px solid #000;">L</th>
                                        <th style="color: #6c757d; font-size: 13px; border-left:2px solid #000; border-bottom:2px solid #000;">XL</th>
                                        <th style="color: #6c757d; font-size: 13px; border-left:2px solid #000; border-bottom:2px solid #000;">XXL</th>
                                        <th style="color: #6c757d; font-size: 13px; border-left:2px solid #000; border-bottom:2px solid #000;">3XL</th>
                                        <th style="color: #6c757d; font-size: 13px; border-left:2px solid #000; border-bottom:2px solid #000;">4XL</th>
                                        <th style="color: #6c757d; font-size: 13px; border-left:2px solid #000; border-bottom:2px solid #000;"></th>
										<th style="color: #6c757d; font-size: 13px; border-left:2px solid #000; border-bottom:2px solid #000;"></th>
                                        <th style="color: #6c757d; font-size: 13px; border-left:2px solid #000; border-bottom:2px solid #000;"></th>
                                        <th style="color: #6c757d; font-size: 13px; border-left:2px solid #000; border-bottom:2px solid #000;">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(orderProductsUpdatedMatrixNew($data->orderProducts) as $productKey => $productValue)
                                    
                                    <tr>
                                        <td style="; border-bottom:2px solid #000;">
                                            <p class="small text-dark fw-bold mb-0">{{$productValue['product_name']}}</p>
                                            <p class="small text-dark fw-bold mb-0">{{$productValue['product_style_no']}}</p>
                                            <p class="small text-dark fw-bold mb-0">{{$productValue['color']}}</p>
                                        </td>
                                        <td style="border-left:2px solid #000; border-bottom:2px solid #000;"><p class="small text-dark fw-bold mb-0">{{ $productValue['S'] ? $productValue['S'] : '' }}</p></td>
                                        <td style="border-left:2px solid #000; border-bottom:2px solid #000;"><p class="small text-dark fw-bold mb-0">{{ $productValue['M'] ? $productValue['M'] : '' }}</p></td>
                                        <td style="border-left:2px solid #000; border-bottom:2px solid #000;"><p class="small text-dark fw-bold mb-0">{{ $productValue['L'] ? $productValue['L'] : '' }}</p></td>
                                        <td style="border-left:2px solid #000; border-bottom:2px solid #000;"><p class="small text-dark fw-bold mb-0">{{ $productValue['XL'] ? $productValue['XL'] : '' }}</p></td>
                                        <td style="border-left:2px solid #000; border-bottom:2px solid #000;"><p class="small text-dark fw-bold mb-0">{{ $productValue['XXL'] ? $productValue['XXL'] : '' }}</p></td>
                                        <td style="border-left:2px solid #000; border-bottom:2px solid #000;"><p class="small text-dark fw-bold mb-0">{{ $productValue['3XL'] ? $productValue['3XL'] : '' }}</p></td>
                                        <td style="border-left:2px solid #000; border-bottom:2px solid #000;"><p class="small text-dark fw-bold mb-0">{{ $productValue['4XL'] ? $productValue['4XL'] : '' }}</p></td>
                                        <td style="border-left:2px solid #000; border-bottom:2px solid #000;"><p class="small text-dark fw-bold mb-0"></p></td>
                                        <td style="border-left:2px solid #000; border-bottom:2px solid #000;"><p class="small text-dark fw-bold mb-0"></p></td>
										<td style="border-left:2px solid #000; border-bottom:2px solid #000;"><p class="small text-dark fw-bold mb-0"></p></td>
                                        <td style="border-left:2px solid #000; border-bottom:2px solid #000;"><p class="small text-dark fw-bold mb-0">{{$productValue['total']}}</p></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                @endif
                                <tbody>
                                    <tr>
                                        <td style="">
                                            <p class="small text-dark mb-0"><strong>Total</strong></p>
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
										<td></td>
                                        <td style="border-left:2px solid #000;"><p class="small text-dark fw-bold mb-0">{{$data->orderProducts->sum('qty')}}</p></td>
                                    </tr>
                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('script')
<script src="{{ asset('admin/js/printThis.js') }}"></script>

<script>
    function printInvoice() {
        $('.printDiv').printThis();
    }
</script>
@endsection
