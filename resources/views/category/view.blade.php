@extends('layouts.app')

@section('content')
<div class="container mt-5">
        <div class="row">
            <div class="col-md-12">

                <div class="card data-card">
                    <div class="card-header">
                        <h4 class="d-flex">Category Detail
                            <a href="{{ url('categories') }}" class="btn btn-cta ms-auto">Back</a>
                                <a href="{{ route('categories.edit',$data->id) }}" class="btn btn-cta">
                                    Edit
                                </a>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <h2>{{ $data->name }}</h2>
                                <div class="text-muted">{{ $data->parent }}</div>
                                    <p class="">{{ $data->description }}</p>
                                <hr>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <p class="text-muted">Icon</p>
                                <img src="{{ asset($data->icon_path) }}" alt="" style="height: 50px">
                            </div>
                            <div class="col-md-3">
                                <p class="text-muted">Sketch</p>
                                <img src="{{ asset($data->sketch_icon) }}" alt="" style="height: 50px">
                            </div>
                            <div class="col-md-3">
                                <p class="text-muted">Thumbnail</p>
                                <img src="{{ asset($data->image_path) }}" alt="" style="height: 50px">
                            </div>
                            <div class="col-md-3">
                                <p class="text-muted">Banner</p>
                                <img src="{{ asset($data->banner_image) }}" alt="" style="height: 50px">
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <h3 class="text-muted fw-bold">Products</h3>
                                <p class="mb-2">{{$data->ProductDetails->count()}} products total</p>
                               
                                <table class="table admin-table">
                                    <thead>
                                    <tr>
                                        <th class="text-center"><i class="fi fi-br-picture"></i></th>
                                        <th>Name</th>
                                        <th>Style No.</th>
                                        <th>Collection</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($data->ProductDetails as $index => $item)
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
                                            <td class="text-center column-thumb">
                                                <img src="{{asset('images/product-box.png')}}" />
                                            </td>
                                            <td>
                                                {{$item->name}}
                                                <div class="row__action">
                                                    <a href="{{ route('products.edit', $item->id) }}">Edit</a>
                                                    <a href="{{ route('products.show', $item->id) }}">View</a>
                                                </div>
                                            </td>
                                            <td>{{$item->style_no}}</td>
                                            <td>{{$item->collection ? $item->collection->name : ''}}</td>
                                            <td>
                                                <small> <del>{{$item->price}}</del> </small> Rs. {{$item->offer_price}}
                                            </td>
                                            <td><span class="badge bg-{{($item->status == 1) ? 'success' : 'danger'}}">{{($item->status == 1) ? 'Active' : 'Inactive'}}</span></td>
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
