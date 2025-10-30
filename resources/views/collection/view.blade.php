@extends('layouts.app')

@section('page', 'Collection detail')

@section('content')
@php
    $brandMap = [1 => 'ONN', 2 => 'PYNK', 3 => 'Both'];
    $brandPermissions = $brandMap[$data->brand] ?? 'Unknown';
     // Logged-in user permission (fetched from user_permission_categories table)
    $userPermission = \App\Models\UserPermissionCategory::where('user_id', auth()->id())
        ->value('brand'); // assuming column name is 'brand' in user_permission_categories

    $userBrandPermission = $brandMap[$userPermission] ?? 'Unknown';
@endphp
<section class="detail-sec">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <h2>{{ $brandPermissions }}</h2>
                            <h2 class="fw-bold">{{ $data->name }}</h2>
                            <p class="small">{{ $data->description }}</p>
                            <hr>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <p class="text-muted">Icon</p>
                            <img src="{{ asset($data->icon_path) }}" alt="" style="height: 50px">
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <h3 class="text-muted fw-bold">Products</h3>
                            <p>{{$data->ProductDetails->count()}} products total</p>

                            <table class="table admin-table">
                                <thead>
                                <tr>
                                    <th class="text-center"><i class="fi fi-br-picture"></i></th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Package</th>
                                    <th>Color+Size</th>
                                    <th>Price</th>
                                    <th>Offer Price</th>
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
                                                <img src="{{ asset($item->image) }}" style="max-width: 80px;max-height: 80px;">
                                        </td>
										
                                        <td>
											<p class="mb-1 text-dark">{{$item->style_no}}</p>
                                            <p class="small text-muted">{{$item->name}}</p>
                                            <div class="row__action">
                                                <a href="{{ route('products.edit', $item->id) }}">Edit</a>
                                                <a href="{{ route('products.show', $item->id) }}">View</a>
                                            </div>
                                        </td>
                                        <td>{{$item->category ? $item->category->name : ''}}</td>
										
										<td>{{$item->master_pack}}</td>
										<td>
											@php
											$colors = \App\Models\ProductColorSize::select('color_id')->where('product_id',$item->id)->groupBy('color_id')->with('colorData','size')->get();
											foreach($colors as $color) {
												echo '<p class="small text-dark d-flex">'.$color->colorData->name.'(#'.$color->colorData->name.')';
												$sizes = \App\Models\ProductColorSize::select('size_id','offer_price')->where('product_id',$item->id)->where('color_id',$color->color_id)->groupBy('size_id')->with('colorData','size')->get(); 
												echo '<span class="ms-auto">No of sizes - ';
												echo count($sizes);
											echo '</span></p>';
											echo '<table class="table no-shadow">';
											echo '<tr><th class="px-0">Size</th><th class="px-0">Price</th></tr>';
													
												foreach($sizes as $size) {
											echo '<tr><td class=""><p class="small text-dark mb-0">'.$size->size->name.'</p></td>';
											echo '<td class=""><p class="small text-dark mb-0">Rs'.$size->offer_price.'</p></td></tr>';
												}
											echo '</table>';
											}
											@endphp
										</td>
										<td>
                                            Rs. {{$item->price}}
                                        </td>	
                                        <td>
                                            Rs. {{$item->offer_price}}
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
</section>
@endsection
