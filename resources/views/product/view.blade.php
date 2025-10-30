@extends('layouts.app')

@section('page', 'Product details')

@section('content')
@php
    $brandMap = [1 => 'ONN', 2 => 'PYNK', 3 => 'Both'];
    $brandPermissions = $brandMap[$data->brand] ?? 'Unknown';
     // Logged-in user permission (fetched from user_permission_categories table)
    $userPermission = \App\Models\UserPermissionCategory::where('user_id', auth()->id())
        ->value('brand'); // assuming column name is 'brand' in user_permission_categories

    $userBrandPermission = $brandMap[$userPermission] ?? 'Unknown';
@endphp
                    <div class="row">
                        <div class="col-sm-10">
                            
                           
                            
                         </div>
                         <div class="col-sm-1">
                            
                           @can('update product')
                            <a href="{{ url('products/'.$data->id.'/edit') }}" class="btn btn-danger">Edit</a>
                            @endcan
                            
                         </div>
                       <div class="col-sm-1">
                            
                            <a href="{{ url('products') }}" class="btn btn-danger">Back</a>
                            
                         </div>
                    </div>
<section>
    
        <div class="row">
            <div class="col-sm-3">
                <div class="card shadow-sm">
                    <div class="card-header" style="background: #dc3545 ; color: #fff;">Main image</div>
                    <div class="card-body">
                        <div class="w-100 product__thumb">
                            <label for="thumbnail"><img id="output" src="{{ asset($data->image) }}"/></label>
                        </div>
                    </div>
                </div>
                <div class="card shadow-sm">
                    <div class="card-header" style="background: #dc3545 ; color: #fff;">More images</div>
                    <div class="card-body">
                        <div class="w-100 product__thumb">
                        @foreach($images as $index => $singleImage)
                            <label for="thumbnail"><img id="output" src="{{ asset($singleImage->image) }}" class="img-thumbnail mb-3"/></label>
                        @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-9">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <h2>{{$brandPermissions}}</h2>
                            <h2>{{$data->name}}</h2>
                        </div>
                        <div class="form-group mb-3">
                            <p>
                                @if($data->collection)  <span class="text-muted">Collection : </span>{{$data->collection->name}} @endif
								@if($data->category) |  <span class="text-muted">Category : </span>{{$data->category->name}} @endif
								
								
							</p>
                        </div>

                        @if ($data->colorSize)
                            @php
                            if (!function_exists('in_array_r')) {
                                function in_array_r($needle, $haystack, $strict = false) {
                                    foreach ($haystack as $item) {
                                        if (($strict ? $item === $needle : $item == $needle) ||
                                            (is_array($item) && in_array_r($needle, $item, $strict))) {
                                            return true;
                                        }
                                    }
                                    return false;
                                }
                            }
                            $uniqueColors = [];

                          
                            

                            foreach ($data->colorSize as $variantKey => $variantValue) {
                                if (in_array_r($variantValue->colorData->code, $uniqueColors)) continue;

                                $uniqueColors[] = [
                                    'id' => $variantValue->colorData->id,
                                    'code' => $variantValue->colorData->code,
                                    'name' => $variantValue->colorData->name,
                                ];
                            }

                            // echo '<pre>';print_r($uniqueColors);

                            echo '<hr><div class="d-flex">';

                            foreach($uniqueColors as $colorCode) {
                                echo '<div onclick="sizeCheck('.$data->id.', '.$colorCode['id'].')" style="text-align:center;height: 70px;width: 40px;margin-right: 20px;"><div class="btn btn-sm rounded-circle" style="background-color: '.$colorCode['code'].';height: 40px;width: 40px;"></div><p class="small text-muted mb-0 mt-2">'.ucwords($colorCode['name']).'</p></div>';
                            }

                            echo '</div>';

                            echo '<br><br><p class="text-dark">Tap on color to get sizes</p>';

                            echo '<div id="sizeContainer"></div>';
                            @endphp
                        @endif
                        
                        <hr>
                        <div class="form-group mb-3">
                            <h4>
                                <span class="text-muted small"><del>Rs {{$data->price}}</del></span>
                                <span class="text-danger">Rs {{$data->offer_price}}</span>
                            </h4>
                        </div>
                        <hr>
                        <div class="form-group mb-3">
                            <p class="small">Short Description</p>
                            {!! $data->short_desc !!}
                        </div>
                        <hr>
                        {{-- <div class="form-group mb-3">
                            <p class="small">Description</p>
                            {!! $data->desc !!}
                        </div> --}}

                        <div class="admin__content">
                            <aside>
                                <nav>Others</nav>
                            </aside>
                            <content>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="inputPassword6" class="col-form-label">Size Chart</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="small">{{$data->size_chart}}</p>
                                    </div>
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="inputprice6" class="col-form-label">Pack</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="small">{{$data->pack}}</p>
                                    </div>
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="inputprice6" class="col-form-label">Pack Count</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="small">{{$data->pack_count}}</p>
                                    </div>
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="inputprice6" class="col-form-label">Master Pack</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="small">{{$data->master_pack}}</p>
                                    </div>
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="inputprice6" class="col-form-label">Master Pack Count</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="small">{{$data->master_pack_count}}</p>
                                    </div>
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="inputprice6" class="col-form-label">Only For</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="small">{{$data->only_for}}</p>
                                    </div>
                                </div>
                            </content>
                        </div>
                    </div>
                </div>
            </div>
        </div>
   
</section>
@endsection

@section('script')
    <script>
        function sizeCheck(productId, colorId) {
            $.ajax({
                url : '{{route("products.size")}}',
                method : 'POST',
                data : {'_token' : '{{csrf_token()}}', productId : productId, colorId : colorId},
                success : function(result) {
                    if (result.error === false) {
                        let content = '<div class="btn-group" role="group" aria-label="Basic radio toggle button group">';

                        $.each(result.data, (key, val) => {
                            content += `<input type="radio" class="btn-check" name="productSize" id="productSize${val.sizeId}" autocomplete="off"><label class="btn btn-outline-primary px-4" for="productSize${val.sizeId}">${val.sizeName}</label>`;
                        })

                        content += '</div>';

                        $('#sizeContainer').html(content);
                    }
                },
                error: function(xhr, status, error) {
                    // toastFire('danger', 'Something Went wrong');
                }
            });
        }
    </script>
@endsection