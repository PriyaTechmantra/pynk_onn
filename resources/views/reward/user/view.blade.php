@extends('layouts.app')

@section('content')
<div class="container mt-5">
        <div class="row">
            <div class="col-md-12">

                <div class="card data-card">
                    <div class="card-header">
                        <h4 class="d-flex">New Register Store Detail
                            <a href="{{ route('reward.retailer.user.index') }}" class="btn btn-cta ms-auto">Back</a>
                        </h4>
                    </div>
                    <div class="card-body">

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
                                    <h5> {{ $data->name ? $data->name : 'NA' }}</h5>
                                </div>
                            </div>
                            <div class="col-sm-4 mb-3">
                                <div class="form-group">
                                    <p class="small text-muted mb-1">Shop Address</p>
                                    <h5> {{ $data->address ? $data->address : 'NA' }}</h5>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-12"><p class="text-dark">Contact information</p></div>
                            <div class="col-sm-4 mb-3">
                                <div class="form-group">
                                    <p class="small text-muted mb-1">Contact</p>
                                    <h5> {{ $data->contact ? $data->contact : 'NA' }}</h5>
                                </div>
                            </div>
                            <div class="col-sm-4 mb-3">
                                <div class="form-group">
                                    <p class="small text-muted mb-1">Whatsapp</p>
                                    <h5> {{ $data->whatsapp ? $data->whatsapp : 'NA' }}</h5>
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
                                    <h5> {{ $data->state ? $data->state->name : 'NA' }}</h5>
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
</div>

@endsection
