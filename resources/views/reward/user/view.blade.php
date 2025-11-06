@extends('layouts.app')

@section('page', 'User detail')

@section('content')
{{-- <section>
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                        </div>
                        <div class="col-md-10">
                            <p><span class="small text-dark">Owner Name :</span> {{ strtoupper($data->owner_name) }}</p>
                            <p><span class="small text-dark">Shop Name :</span> {{ ($data->shop_name) }}</p>
                            <p><span class="small text-dark">shop Address :</span> {{ $data->owner_address}}</p>
                            <p class="small text-muted" style="word-break: break-all;">
                                <span class="text-dark">Email: </span>
                                {{$data->email}}
                            </p>
                           
                            <p class="small text-dark"><span class="text-muted">Contact Details:</span> {{$data->mobile}}</p>
                            <p><span class="small text-dark">WhatsApp Details:</span> {{ $data->whatsapp_no }}</p>
                           
                            <p><span class="text-muted">Address : </span> {{ $data->address }}</p>
                            <p><span class="small text-dark">District :</span> {{ $data->landmark }}</p>
                            <p><span class="small text-dark">City :</span> {{ $data->city }}</p>
                            <p><span class="small text-dark">State :</span> {{ $data->state }}</p>
                            <p><span class="small text-dark">Pincode :</span> {{ $data->pin }}</p>
                            <div class="col-sm-4 mb-3">
                                <div class="form-group">
                                    <p class="small text-muted mb-1">Aadhar</p>
                                    <img src="{{ asset($data->aadhar) }}" alt="" class="w-100">
                                </div>
                            </div>
                            <p>Published<br/>{{date('d M Y', strtotime($data->created_at))}}</p>

                        </div>
                    </div>
                    <a type="submit" href="{{ route('reward.retailer.user.index') }}" class="btn btn-sm btn-danger">Back</a>
                </div>

            </div>
        </div>
    </div>
</section> --}}
<section>
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="badge bg-primary" style="font-size: 26px;">Retailer</div>
                        </div>

                        <div class="col-md-6 text-end">
                            <a href="{{ url()->previous() }}" class="btn btn-danger">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-left"><polyline points="15 18 9 12 15 6"></polyline></svg>
                                Go back
                            </a>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-12"><p class="text-dark">Owner information</p></div>
                        <div class="col-sm-4 mb-3">
                            <div class="form-group">
                                <p class="small text-muted mb-1">Owner Name</p>
                                <h5> {{ $data->owner_name ? $data->owner_name : 'NA' }}</h5>
                            </div>
                        </div>
                        <div class="col-sm-4 mb-3">
                            <div class="form-group">
                                <p class="small text-muted mb-1">Shop Name</p>
                                <h5> {{ $data->shop_name ? $data->shop_name : 'NA' }}</h5>
                            </div>
                        </div>
                        <div class="col-sm-4 mb-3">
                            <div class="form-group">
                                <p class="small text-muted mb-1">Shop Address</p>
                                <h5> {{ $data->shop_address ? $data->shop_address : 'NA' }}</h5>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-12"><p class="text-dark">Contact information</p></div>
                        <div class="col-sm-4 mb-3">
                            <div class="form-group">
                                <p class="small text-muted mb-1">Contact</p>
                                <h5> {{ $data->mobile ? $data->mobile : 'NA' }}</h5>
                            </div>
                        </div>
                        <div class="col-sm-4 mb-3">
                            <div class="form-group">
                                <p class="small text-muted mb-1">Whatsapp</p>
                                <h5> {{ $data->whatsapp_no ? $data->whatsapp_no : 'NA' }}</h5>
                            </div>
                        </div>
                        <div class="col-sm-4 mb-3">
                            <div class="form-group">
                                <p class="small text-muted mb-1">Email</p>
                                <h5> {{ $data->email ? $data->email : 'NA' }}</h5>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-12"><p class="text-dark">Address information</p></div>
                        <div class="col-sm-4 mb-3">
                            <div class="form-group">
                                <p class="small text-muted mb-1">District</p>
                                <h5> {{ $data->district ? $data->district : 'NA' }}</h5>
                            </div>
                        </div>
                        <div class="col-sm-4 mb-3">
                            <div class="form-group position-relative">
                                <p class="small text-muted mb-1">Pincode</p>
                                <h5> {{ $data->pin ? $data->pin : 'NA' }}</h5>
                            </div>
                        </div>
                        <div class="col-sm-4 mb-3">
                            <div class="form-group">
                                <p class="small text-muted mb-1">State</p>
                                <h5> {{ $data->state ? $data->state : 'NA' }}</h5>
                            </div>
                        </div>
                        <div class="col-sm-4 mb-3">
                            <div class="form-group">
                                <p class="small text-muted mb-1">City</p>
                                <h5> {{ $data->city ? $data->city : 'NA' }}</h5>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-3">
                        <div class="col-12"><p class="text-dark">Document information</p></div>
                        @if(!empty($data->aadhar))
                        <div class="col-sm-4 mb-3">
                            <div class="form-group">
                                <p class="small text-muted mb-1">Aadhar</p>
                                <img src="{{asset($data->aadhar)}}" alt="" class="w-100">
                            </div>
                        </div>
                        @endif
                        @if(!empty($data->pan))
                        <div class="col-sm-4 mb-3">
                            <div class="form-group">
                                <p class="small text-muted mb-1">Pan</p>
                                <img src="{{asset($data->pan)}}" alt="" class="w-100">
                            </div>
                        </div>
                        @endif
                        @if(!empty($data->gst))
                        <div class="col-sm-4 mb-3">
                            <div class="form-group">
                                <p class="small text-muted mb-1">Gst</p>
                                <img src="{{asset($data->gst)}}" alt="" class="w-100">
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
