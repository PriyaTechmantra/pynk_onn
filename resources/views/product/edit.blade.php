@extends('layouts.app')


<style>
    .label-control {
        color: #525252;
        font-size: 12px;
    }
    .color_holder {
        display: flex;
        border: 1px dashed #ddd;
        border-radius: 6px;
        padding: 5px;
        background: #f0f0f0;
        flex-wrap: wrap;
        margin-bottom: 10px;
    }
    .color_holder_single {
        margin: 5px;
    }
    .color_box {
        display: flex;
        padding: 6px 10px;
        border-radius: 3px;
        align-items: center;
        margin: 0;
        background: #fff;
    }
    .color_box p {
        margin: 0;
        margin-left: 10px;
    }
    .color_box span, .color_box img {
        display: inline-block;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        /* margin-right: 10px; */
    }
    .sizeUpload {
        margin-bottom: 10px;
    }
    .size_holder {
        padding: 10px 0;
        border-top: 1px solid #ddd;
    }
    .img_thumb img {
        width: 100%;
        height: 100%;
        position: absolute;
        top: 0;
        left: 0;
        object-fit: cover;
    }
    .remove_image {
        display: inline-flex;
        width: 30px;
        height: 30px;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        color: #fff;
        background-color: #dc3545;
        border-color: #dc3545;
        position: absolute;
        top: 0;
        right: 0;
    }
    .remove_image i {
        line-height: 13px;
    }
    .image_upload {
        display: inline-flex;
        padding: 0 20px;
        border:  1px solid #ccc;
        background: #ddd;
        padding: 5px 12px;
        border-radius: 3px;
        vertical-align: top;
        cursor: pointer;
    }
    .status-toggle {
        padding: 6px 10px;
        border-radius: 3px;
        align-items: center;
        background: #fff;
    }
    .status-toggle a {
        text-decoration: none;
        color: #000
    }
    .color_holder {
        display: flex;
        border: 1px dashed #ddd;
        border-radius: 6px;
        padding: 5px;
        background: #f0f0f0;
        flex-wrap: wrap;
        margin-bottom: 10px;
    }
    .color_holder_single {
        margin: 5px;
    }
    .color_box {
        display: flex;
        padding: 6px 10px;
        border-radius: 3px;
        align-items: center;
        margin: 0;
        background: #fff;
    }
    .sizeUpload {
        margin-bottom: 10px;
    }
    .img_thumb {
        width: 100%;
        padding-bottom: calc((4/3)*100%);
        position: relative;
        border:  1px solid #ccc;
        max-width: 80px;
        min-width: 80px;
    }
    .img_thumb img {
        width: 100%;
        height: 100%;
        position: absolute;
        top: 0;
        left: 0;
        object-fit: cover;
    }
    .remove_image {
        display: inline-flex;
        width: 30px;
        height: 30px;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        color: #fff;
        background-color: #dc3545;
        border-color: #dc3545;
        position: absolute;
        top: 0;
        right: 0;
    }
    .remove_image i {
        line-height: 13px;
    }
    .image_upload {
        display: inline-flex;
        padding: 0 20px;
        border:  1px solid #ccc;
        background: #ddd;
        padding: 5px 12px;
        border-radius: 3px;
        vertical-align: top;
        cursor: pointer;
    }
    .status-toggle {
        padding: 6px 10px;
        border-radius: 3px;
        align-items: center;
        background: #fff;
    }
    .status-toggle a {
        text-decoration: none;
        color: #000
    }
    .color-fabric-image-holder {
        width: 36px;
        height: 36px;
    }
    .color-fabric-image {
        width: inherit;
        height: inherit;
        border-radius: 50%;
    }
    .change-image {
        position: absolute;
        bottom: -4px;
        right: -8px;
        background: #c1080a;
        width: 25px;
        height: 25px;
        border-radius: 50%;
        padding: 0 0;
    }
    .change-image .badge {
        padding: 3px;
        cursor: pointer;
    }
    .croppie-container {
        height: auto;
    }
</style>
@section('content')
<section>
      @if ($errors->any())
                <ul class="alert alert-warning">
                    @foreach ($errors->all() as $error)
                        <li>{{$error}}</li>
                    @endforeach
                </ul>
                @endif
    <form action="{{ url('products/'.$data->id) }}" method="POST" class="data-form">
    @csrf
    @method('PUT')
        <div class="row">
        <div class="col-sm-9">

                <div class="row mb-3">
                    <div class="mb-3">
                        <div class="card shadow-sm">
                            <div class="card-header" style="background: #dc3545 ; color: #fff;">
                        <!-- Communication Medium -->
                                <h6>Brand Permission:  <span class="text-danger">*</span></h6>
                                @error('brand') <p class="small text-danger">{{ $message }}</p> @enderror
                            </div>
                        <div class="card-body">
                            <div class="form-check">
                                <input 
                                    class="form-check-input medium-checkbox" 
                                    type="checkbox" 
                                    name="brand" 
                                    value="1" 
                                    id="mediumOnn"
                                    {{ $data->brand == 1 ? 'checked' : '' }}
                                    onchange="checkOnlyOne(this)"
                                >
                                <label class="form-check-label" for="mediumLMS">Onn</label>
                            </div>
                            <div class="form-check">
                                <input 
                                    class="form-check-input medium-checkbox" 
                                    type="checkbox" 
                                    name="brand" 
                                    value="2" 
                                    id="mediumPynk"
                                     {{ $data->brand == 2 ? 'checked' : '' }}
                                    onchange="checkOnlyOne(this)"
                                >
                                <label class="form-check-label" for="mediumFMS">Pynk</label>
                            </div>
                            
                            <div class="form-check">
                                <input 
                                    class="form-check-input medium-checkbox" 
                                    type="checkbox" 
                                    name="brand" 
                                    value="3" 
                                    id="mediumBoth"
                                     {{ $data->brand == 3 ? 'checked' : '' }}
                                    onchange="checkOnlyOne(this)"
                                >
                                
                                <label class="form-check-label" for="mediumCave">Both</label>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
                <div class="card shadow-sm">
                    <div class="card-header" style="background: #dc3545 ; color: #fff;">
                        <h6>Product Basic <h6>
                    </div>
                    <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-6">
                        <label for="" class="col-form-label">Collection <span class="text-danger">*</span></label>
                        <select class="form-control form-control-sm select2" name="collection_id" id="collection">
                               
                                <option value="" selected disabled>Select</option>
                                <option value="10000">All</option>
                                @foreach ($collection as $index => $item)
                                <option value="{{ $item->id }}" {{ ($data->collection_id == $item->id) ? 'selected' : '' }}>{{ $item->name }}</option>
                                @endforeach
                            </select>
                        @error('collection_id') <p class="small text-danger">{{ $message }}</p> @enderror
                    </div>
                        <div class="col-sm-6">
                            <label for="" class="col-form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm select2" aria-label="Default select example" name="cat_id" id="category">
                                <option value=""  selected>Select Category</option>
                                @foreach ($category as $index => $item)
                                            <option value="{{$item->id}}" {{ ($data->cat_id == $item->id) ? 'selected' : '' }}>{{ $item->name }}</option>
                                @endforeach
                            </select>
                            @error('cat_id') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
            
                        
                    </div>

                    <div class="form-group mb-3">
                        <label for="" class="col-form-label">Title/Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" placeholder="Add Product Title" class="form-control" value="{{old('name',$data->name)}}">
                        @error('name') <p class="small text-danger">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group mb-3">
                        <label for="" class="col-form-label">Style No <span class="text-danger">*</span></label>
                        <input type="text" name="style_no" placeholder="Add Product Style No" class="form-control" value="{{old('style_no',$data->name)}}">
                        @error('style_no') <p class="small text-danger">{{ $message }}</p> @enderror
                    </div>
                    
                                </div>
                                </div>

            <div class="card shadow-sm">
                <div class="card-header" style="background: #dc3545; color: #fff;" >
                    <h6>Short Description</h6>
                </div>
                <div class="card-body">
                    <textarea id="product_short_des" name="short_desc">{{old('short_desc',$data->short_desc)}}</textarea>
                    @error('short_desc') <p class="small text-danger">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header" style="background: #dc3545; color: #fff;">
                    <h6>Description</h6>
                </div>
                <div class="card-body">
                    <textarea id="product_des" name="desc">{{old('desc',$data->desc)}}</textarea>
                    @error('desc') <p class="small text-danger">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header" style="background: #dc3545 ; color: #fff;">
                    <h6>Product data</h6>
                </div>
                <div class="card-body pt-0">
                    <div class="admin__content">
                    <aside>
                        <nav>Price</nav>
                    </aside>
                    <content>
                        <div class="row mb-2 align-items-center">
                        <div class="col-3">
                            <label for="inputPassword6" class="col-form-label">Regular Price</label>
                        </div>
                        <div class="col-auto">
                            <input type="text" id="inputprice6" class="form-control" aria-describedby="priceHelpInline" name="price" value="{{old('price',$data->price)}}">
                            @error('price') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                        <div class="col-auto">
                            <span id="priceHelpInline" class="form-text">
                            Must be 8-20 characters long.
                            </span>
                        </div>
                        </div>
                        <div class="row mb-2 align-items-center">
                        <div class="col-3">
                            <label for="inputprice6" class="col-form-label">Offer Price</label>
                        </div>
                        <div class="col-auto">
                            <input type="text" id="inputprice6" class="form-control" aria-describedby="priceHelpInline" name="offer_price" value="{{old('offer_price',$data->offer_price)}}">
                            @error('offer_price') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                        <div class="col-auto">
                            <span id="passwordHelpInline" class="form-text">
                            Must be 8-20 characters long.
                            </span>
                        </div>
                        </div>
                    </content>
                    </div>
                    <div class="admin__content">
                        <aside>
                            <nav>Others</nav>
                        </aside>
                        <content>
                            <div class="row mb-2 align-items-center">
                                <div class="col-3">
                                    <label for="inputPassword6" class="col-form-label">Size Chart</label>
                                </div>
                                <div class="col-auto">
                                    <input type="text" id="inputprice6" class="form-control" aria-describedby="priceHelpInline" name="size_chart" value="{{old('size_chart',$data->size_chart)}}">
                                    @error('size_chart') <p class="small text-danger">{{ $message }}</p> @enderror
                                </div>
                                
                            </div>
                            <div class="row mb-2 align-items-center">
                                <div class="col-3">
                                    <label for="inputprice6" class="col-form-label">Pack</label>
                                </div>
                                <div class="col-auto">
                                    <input type="text" id="inputprice6" class="form-control" aria-describedby="priceHelpInline" name="pack" value="{{old('pack',$data->pack)}}">
                                    @error('pack') <p class="small text-danger">{{ $message }}</p> @enderror
                                </div>
                                
                            </div>
                            <div class="row mb-2 align-items-center">
                                <div class="col-3">
                                    <label for="inputprice6" class="col-form-label">Pack Count</label>
                                </div>
                                <div class="col-auto">
                                    <input type="text" id="inputprice6" class="form-control" aria-describedby="priceHelpInline" name="pack_count" value="{{old('pack_count',$data->pack_count)}}">
                                    @error('pack_count') <p class="small text-danger">{{ $message }}</p> @enderror
                                </div>
                                
                            </div>
                            <div class="row mb-2 align-items-center">
                                <div class="col-3">
                                    <label for="inputprice6" class="col-form-label">Master Pack</label>
                                </div>
                                <div class="col-auto">
                                    <input type="text" id="inputprice6" class="form-control" aria-describedby="priceHelpInline" name="master_pack" value="{{old('master_pack',$data->master_pack)}}">
                                    @error('master_pack') <p class="small text-danger">{{ $message }}</p> @enderror
                                </div>
                               
                            </div>
                            <div class="row mb-2 align-items-center">
                                <div class="col-3">
                                    <label for="inputprice6" class="col-form-label">Master Pack Count</label>
                                </div>
                                <div class="col-auto">
                                    <input type="text" id="inputprice6" class="form-control" aria-describedby="priceHelpInline" name="master_pack_count" value="{{old('master_pack_count',$data->master_pack_count)}}">
                                    @error('master_pack_count') <p class="small text-danger">{{ $message }}</p> @enderror
                                </div>
                                
                            </div>
                            <div class="row mb-2 align-items-center">
                                <div class="col-3">
                                    <label for="inputprice6" class="col-form-label">Only For</label>
                                </div>
                                <div class="col-auto">
                                    <input type="text" id="inputprice6" class="form-control" aria-describedby="priceHelpInline" name="only_for" value="{{old('only_for',$data->only_for)}}">
                                    @error('only_for') <p class="small text-danger">{{ $message }}</p> @enderror
                                </div>
                                
                            </div>
                        </content>
                    </div>
                   
                </div>
            </div>

            

        </div>
        <div class="col-sm-3">
			<div class="card shadow-sm">
                <div class="card-header" style="background: #dc3545 ; color: #fff;">
                    <h6>Product Main Image <span class="text-danger">*</span></h6>
                </div>
                <div class="card-body">
                     <div class="w-100 product__thumb">
                            <label for="thumbnail"><img id="output" src="{{ asset($data->image) }}"/></label>
                            @error('image') <p class="small text-danger">{{ $message }}</p> @enderror
                    </div>
                    <input type="file" id="thumbnail" accept="image/*" name="image" onchange="loadFile(event)" class="d-none">
                    <script>
                    var loadFile = function(event) {
                        var output = document.getElementById('output');
                        output.src = URL.createObjectURL(event.target.files[0]);
                        output.onload = function() {
                        URL.revokeObjectURL(output.src) // free memory
                        }
                    };
                    </script>
                </div>
            </div>
            
            <div class="card shadow-sm">
            <div class="card-header" style="background: #dc3545 ; color: #fff;">
                <h6>Publish</h6>
            </div>
            <div class="card-body text-end">
                <button type="submit" class="btn btn-sm btn-danger">Publish </button>
            </div>
            </div>
            
           
        </div>
        </div>
    </form>
    <div class="card shadow-sm" id="singleProductVariation">
        <div class="card-header">
            <div class="row justify-content-between">
                <div class="col-6">
                    <h3>Product variation</h3>
                    <p class="small text-muted m-0">Add color | size | multiple images from here</p>
                </div>
                <div class="col-6 text-end">
                    <a href="#csvUploadModal" data-bs-toggle="modal" class="btn btn-danger mt-2">Bulk upload</a>
                </div>
            </div>
        </div>
        <div class="card-body pt-0">
            <div class="admin__content">
                <aside>
                    <nav>Available colors</nav>
                    <p class="small text-muted">Drag & drop colors to set position</p>
                    <p class="small text-muted">Toggle color status</p>
                </aside>
                <content>
                   

                    <div class="color_holder row_position">
                        @foreach ($productColorSizeGroup as $productWiseColorsKey => $productWiseColorsVal)
                        <div class="color_holder_single single-color-holder d-flex" id="{{$productWiseColorsVal->id}}">
                            <div class="color_box shadow-sm" style="{!! ($productWiseColorsVal->status == 0) ? 'background: #c1080a59;' : '' !!}" id="color_box_up_{{$productWiseColorsVal->color}}">
                                <div>
                                @if($productWiseColorsVal->color_fabric != null)
                                    <img src="{{ asset($productWiseColorsVal->color_fabric) }}" alt="">
                                @else
                                    @if($productWiseColorsVal->colorData->name == 'Assorted')
                                        <span style="background: -webkit-linear-gradient(left,  rgba(219,2,2,1) 0%,rgba(219,2,2,1) 9%,rgba(219,2,2,1) 10%,rgba(254,191,1,1) 10%,rgba(254,191,1,1) 10%,rgba(254,191,1,1) 20%,rgba(1,52,170,1) 20%,rgba(1,52,170,1) 20%,rgba(1,52,170,1) 30%,rgba(15,0,13,1) 30%,rgba(15,0,13,1) 30%,rgba(15,0,13,1) 40%,rgba(239,77,2,1) 40%,rgba(239,77,2,1) 40%,rgba(239,77,2,1) 50%,rgba(254,191,1,1) 50%,rgba(137,137,137,1) 50%,rgba(137,137,137,1) 60%,rgba(254,191,1,1) 60%,rgba(254,191,1,1) 60%,rgba(254,191,1,1) 70%,rgba(189,232,2,1) 70%,rgba(189,232,2,1) 80%,rgba(209,2,160,1) 80%,rgba(209,2,160,1) 90%,rgba(48,45,0,1) 90%);"></span>
                                    @else
                                        <span style="background-color:{{ $productWiseColorsVal->colorData->code }}"></span>
                                    @endif
                                @endif
                                </div>
                                <p class="small card-title">
                                    @if ($productWiseColorsVal->colorData->name)
                                        {{$productWiseColorsVal->colorData->name}}
                                    @else
                                        @php
                                            $orgColorName = \App\Models\Color::select('name')->where('id', $productWiseColorsVal->color_id)->first();
                                        @endphp
                                        {{$orgColorName->name}}
                                    @endif
                                </p>
                            </div>

                            <div class="status-toggle shadow-sm">
                                <a href="javascript: void(0)" onclick="colorStatusToggle({{$productWiseColorsVal->id}}, {{$data->id}}, {{$productWiseColorsVal->color_id}})" title="Tap here to change status"><i class="fi fi-br-cube"></i></a>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <a href="javascript: void(0)" onclick="addColorModal()" class="btn btn-sm btn-success">Add new color</a>
                </content>
            </div>
            @foreach ($productColorGroup as $productColorKey => $productColorGroupVal)
            <div class="admin__content">
                <content>
                    @if ($productColorKey == 0)
                    <div class="row" style="position: sticky;top: 55px;background: white;z-index: 99;padding: 10px 0;">
                        <div class="col-sm-2">
                            <strong>SR</strong>
                            <strong>Color</strong>
                        </div>
                        <div class="col-sm-8">
                            {{--<input class="form-check-input me-3 bulkSelectAll" type="checkbox" value="" id="flexCheckDefault" onclick="headerCheckFunc()">--}}
                            <strong>Size</strong>
                        </div>
                        <div class="col-sm-2">
                            <form action="{{ route('products.variation.bulk.edit') }}" method="post" id="bulkActionForm">@csrf
                                <input type="hidden" name="name" value="{{$data->name}}">
                                <input type="hidden" name="style_no" value="{{$data->style_no}}">
                                <input type="hidden" name="product_id" value="{{$data->id}}">

                               {{-- <select class="form-select" name="bulkAction">
                                    <option selected>Bulk action</option>
                                    <option value="edit">Edit</option>
                                </select>--}}
                            </form>
                            {{-- <strong>Action</strong> --}}
                        </div>
                    </div>
                    <hr>
                    @endif
                    <div class="row">
                        {{-- <div class="col-sm-2">
                            <label for="inputPassword6" class="col-form-label">{{ $productColorKey + 1 }}</label>
                        </div> --}}
                        <div class="col-sm-2">
                            <label for="inputPassword6" class="col-form-label">{{ $productColorKey + 1 }}</label>
                            <div class="color_box" id="color_box_down_{{$productColorGroupVal->color_id}}">
                                <div>
                                @if($productColorGroupVal->color_fabric != null)
                                    <img src="{{ asset($productColorGroupVal->color_fabric) }}" alt="">
                                @else
                                    @if($productColorGroupVal->colorData->name == 'Assorted')
                                        <span style="background: -webkit-linear-gradient(left,  rgba(219,2,2,1) 0%,rgba(219,2,2,1) 9%,rgba(219,2,2,1) 10%,rgba(254,191,1,1) 10%,rgba(254,191,1,1) 10%,rgba(254,191,1,1) 20%,rgba(1,52,170,1) 20%,rgba(1,52,170,1) 20%,rgba(1,52,170,1) 30%,rgba(15,0,13,1) 30%,rgba(15,0,13,1) 30%,rgba(15,0,13,1) 40%,rgba(239,77,2,1) 40%,rgba(239,77,2,1) 40%,rgba(239,77,2,1) 50%,rgba(254,191,1,1) 50%,rgba(137,137,137,1) 50%,rgba(137,137,137,1) 60%,rgba(254,191,1,1) 60%,rgba(254,191,1,1) 60%,rgba(254,191,1,1) 70%,rgba(189,232,2,1) 70%,rgba(189,232,2,1) 80%,rgba(209,2,160,1) 80%,rgba(209,2,160,1) 90%,rgba(48,45,0,1) 90%);"></span>
                                    @else
                                        <span style="background-color:{{ $productColorGroupVal->colorData->code }}"></span>
                                    @endif
								@endif
                                </div>
								<p>
                                    @if ($productColorGroupVal->colorData->name)
                                        {{$productColorGroupVal->colorData->name}}
                                    @else
                                        @php
                                            $orgColorName = \App\Models\Color::select('name')->where('id', $productColorGroupVal->color_id)->first();
                                        @endphp
                                        {{$orgColorName->name}}
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-sm-8">
                            <div class="row">
                                <div class="col-sm-11">
                                    <div class="size_holder" style="border-top: 0;padding-top: 0;">
                                        <div class="row align-items-center">
                                            <div class="col-sm-2"><strong>Size details</strong></div>
                                            <div class="col-sm-2"><strong>Price</strong></div>
                                            <div class="col-sm-3"><strong>Offer Price</strong></div>
                                            <div class="col-sm-2"><strong>SKU code</strong></div>
                                            <div class="col-sm-3"></div>
                                        </div>
                                    </div>

                                    @php
                                        $productVariationColorSizes = \App\Models\ProductColorSize::where('product_id', $id)->where('color_id', $productColorGroupVal->color_id)->orderBy('size_id')->get();

                                        // dd($productVariationColorSizes);

                                        $prodSizesDIsplay = '';
                                        foreach($productVariationColorSizes as $productSizeKey => $productSizeVal) {
                                            $returnAlert = "return confirm('Are you sure ?')";

                                            $sizeName = $productSizeVal->size ? $productSizeVal->size->name : '<span class="text-danger" title="Please delete this & add again">SIZE MISMATCH</span>';

                                            if ($productSizeKey == 0) {
                                                $singleStyle = "border-top: 0;padding-top: 0;";
                                            } else {
                                                $singleStyle = '';
                                            }

                                            if ($productSizeVal->size->size_details != null || $productSizeVal->size->size_details != '') {
                                                $sizeDetailsDisplay = ' - <small class="text-muted">'.$productSizeVal->size->size_details.'</small>';
                                            } else {
                                                $sizeDetailsDisplay = '';
                                            }

                                            if ($productSizeVal->size->name) {
                                                $sizeDisplayName = $productSizeVal->size->name;
                                            } else {
                                                $orgSizeName = \App\Models\Size::select('name')->where('id', $productSizeVal->size_id)->first();

                                                $sizeDisplayName = $orgSizeName->name;
                                            }

                                            $funcSizeDetail = "'".$productSizeVal->size->size_details."'";
                                            $funcPriceDetail = "'".$productSizeVal->offer_price."'";
                                            $funcCodeDetail = "'".$productSizeVal->code."'";
                                            $funcSizeNameDetail = "'".$sizeDisplayName."'";

                                            

                                            $prodSizesDIsplay .= '
                                            <div class="size_holder" style="'.$singleStyle.'">
                                                <div class="row align-items-center justify-content-between">
                                                    <div class="col-sm-2">
                                                        
                                                        '.$sizeName.' '.$sizeDetailsDisplay.'
                                                    </div>
                                                    <div class="col-sm-2">Rs '.$productSizeVal->price.'</div> 
                                                    <div class="col-sm-2">Rs '.$productSizeVal->offer_price.'</div> 
                                                    <div class="col-sm-3">'.$productSizeVal->code.'</div>
                                                    
                                                    <div class="col-sm-3 text-end">
                                                        <div>
                                                            <a href="javascript: void(0)" onclick="editSizeFunc('.$funcSizeNameDetail.', '.$productSizeVal->id.', '.$funcSizeDetail.', '.$funcPriceDetail.', '.$funcCodeDetail.')" class="btn btn-sm btn-success">Edit</a><a clas="row align-items-center" href='.route('products.variation.size.delete', $productSizeVal->id).'  class="btn btn-sm btn-danger delete-confirm">Delete</a>
                                                            
                                                        </div>
                                                        
                                                    </div>
                                                </div>
                                            </div>';

                                            /*
                                            delete option below edit (removed for now)
                                            <a href='.route('admin.product.variation.size.delete', $productSizeVal->id).' onclick="'.$returnAlert.'" class="btn btn-sm btn-danger">Delete</a>
                                            */
                                        }
                                        $prodSizesDIsplay .= '';
                                    @endphp
                                    {!!$prodSizesDIsplay!!}
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-1">
                                    <label for="inputPassword6" class="col-form-label">Images</label>
                                </div>
                                <div class="col-sm">
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <form action="{{route('products.variation.image.add')}}" method="post" enctype="multipart/form-data">@csrf
                                                <input type="file" name="image[]" id="prodVar{{$productColorKey}}" class="d-none" multiple>
                                                <label class="image_upload" for="prodVar{{$productColorKey}}">Browse Image</label>

                                                <input type="hidden" name="product_id" value="{{$id}}">
                                                <input type="hidden" name="color_id" value="{{$productColorGroupVal->color_id}}">
                                                <button type="submit" class="btn btn-sm btn-success">+</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                @php
                                    $productVariationImages = \App\Models\ProductImage::where('product_id', $id)->where('color_id', $productColorGroupVal->color_id)->get();

                                    $prodImagesDIsplay = '';
                                    foreach($productVariationImages as $productImgKey => $productImgVal) {
                                        $prodImagesDIsplay .= '<div class="col-sm-auto" id="img__holder_'.$productColorKey.'_'.$productImgKey.'"><figure class="img_thumb"><img src='.asset($productImgVal->image).'><a href="javascript: void(0)" class="remove_image" onclick="deleteImage('.$productImgVal->id.', '.$productColorKey.', '.$productImgKey.')"><i class="fi fi-br-trash"></i></a></figure></div>';
                                    }
                                @endphp
                                {!!$prodImagesDIsplay!!}
                            </div>
                        </div>
                        <div class="col-sm-2">
                            

							<a href="javascript: void(0)" onclick="addSizeModal({{$productColorGroupVal->color_id}}, '{{ ($productColorGroupVal->colorData) ? $productColorGroupVal->colorData->name : '' }}')" class="btn btn-sm btn-success">Add new size</a>

                            <a href="javascript: void(0)" class="btn btn-sm btn-success" onclick="editColorModalOpen({{$productColorGroupVal->color_id}}, '{{ ($productColorGroupVal->colorData) ? $productColorGroupVal->colorData->name : '' }}')">Change Color</a>

                            <hr>

                            <a href="javascript: void(0)" class="btn btn-sm btn-primary" onclick="renameColorModalOpen({{$productColorGroupVal->color_id}}, '{{ $productColorGroupVal->colorData->name }}')">Rename Color</a>

                            <a href="{{ route('products.variation.color.delete',['productId' => $id, 'colorId' => $productColorGroupVal->color_id]) }}"  class="btn btn-sm btn-danger delete-confirm">Delete Color</a>
                        </div>
                    </div>
                </content>
            </div>
            @endforeach
        </div>
    </div>
</section>

<div class="modal fade" tabindex="-1" id="addColorModal" data-bs-backdrop="static">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add new color</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <form action="{{route('products.variation.color.add')}}" method="post">@csrf
                <input type="hidden" name="product_id" value="{{$id}}">
                {{-- <input type="hidden" name="color" value="{{$productColorGroupVal->color}}"> --}}
                <div class="form-group mb-3">
                <select class="form-control" name="color_id" id="addcolor_id">
                    <option value="" selected>Select color...</option>
                    @php
                        $color = \App\Models\Color::orderBy('name', 'asc')->get();
                        foreach ($color as $key => $value) {
                            echo '<option value="'.$value->id.'">'.$value->name.'</option>';
                        }
                    @endphp
                </select>
                </div>
                <div class="form-group mb-3">
                <select class="form-control" name="size_id" id="">
                    <option value="" selected>Select size...</option>
                    @php
                        $sizes = \App\Models\Size::get();
                        foreach ($sizes as $key => $value) {
                            echo '<option value="'.$value->id.'">'.$value->name.'</option>';
                        }
                    @endphp
                </select>
                </div>
                <div class="form-group mb-3">
                    <input class="form-control" type="text" name="price" id="" placeholder="Price">
                </div>
                <div class="form-group mb-3">
                    <input class="form-control" type="text" name="offer_price" id="" placeholder="Offer Price">
                </div>
                <div class="form-group mb-3">
                    <input class="form-control" type="text" name="sku_code" id="" placeholder="SKU code">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-sm btn-success">+ Save changes</button>
                </div>
            </form>
        </div>
      </div>
    </div>
</div>

{{-- edit color modal --}}
<div class="modal fade" tabindex="-1" id="editColorModal">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change color</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{route('products.variation.color.edit')}}" method="post">@csrf
                    <input type="hidden" name="product_id" value="{{$id}}">
                    <input type="hidden" name="current_color" value="">
                    <div class="form-group">
                        <p>Style no: <strong>{{$data->style_no}}</strong></p>
                        <p>Product: <strong>{{$data->name}}</strong></p>
                        <p>Current Color: <strong><span id="colorName"></span></strong></p>
                    </div>
                    <div class="form-group mb-3">
                        <label for="editColorCode">Change color</label>
                        <select class="form-control" name="update_color" id="editColorCode">
                            <option value="" disabled selected>Select color...</option>
                            @php
                                $color = \App\Models\Color::orderBy('name', 'asc')->get();
                                foreach ($color as $key => $value) {
                                    echo '<option value="'.$value->id.'">'.$value->name.'</option>';
                                }
                            @endphp
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-sm btn-success">Change color</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- rename color modal --}}
<div class="modal fade" tabindex="-1" id="renameColorModal">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rename color</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{route('products.variation.color.rename')}}" method="post">@csrf
                    <input type="hidden" name="product_id" value="{{$id}}">
                    <input type="hidden" name="current_color2" value="">
                    <div class="form-group">
                        <p>Style no: <strong>{{$data->style_no}}</strong></p>
                        <p>Product: <strong>{{$data->name}}</strong></p>
                        <p>Current name: <strong><span id="colorName2"></span></strong></p>
                    </div>
                    <div class="form-group mb-3">
                        <label>Enter new name</label>
                        <input type="text" class="form-control" name="update_color_name" id="">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-sm btn-success">Rename color</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- rename color modal --}}
<div class="modal fade" tabindex="-1" id="sizeDetailModal">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Size detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{route('products.variation.size.edit')}}" method="post">@csrf
                    {{-- <input type="hidden" name="product_id" value="{{$id}}"> --}}
                    <input type="hidden" name="id" value="">
                    <div class="form-group">
                        <p>Style no: <strong>{{$data->style_no}}</strong></p>
                        <p>Product: <strong>{{$data->name}}</strong></p>
                    </div>
                    <div class="form-group mb-3">
                        <label>Current Size: <span id="sizeNameDetail"></span> </label>
                        <select class="form-control" name="size_id" id="">
                            <option value="" selected>Change size...</option>
                            @php
                                $sizes = \App\Models\Size::get();
                                foreach ($sizes as $key => $value) {
                                    echo '<option value="'.$value->id.'">'.$value->name.'</option>';
                                }
                            @endphp
                        </select>
                    </div>
                    {{--<div class="form-group mb-3">
                        <label>Size detail</label>
                        <input type="text" class="form-control" name="size_details" id="">
                    </div>--}}
                    <div class="form-group mb-3">
                        <label>Price</label>
                        <input type="text" class="form-control" name="sizeedprice" id="">
                    </div>
                    <div class="form-group mb-3">
                        <label>Offer Price</label>
                        <input type="text" class="form-control" name="sizeedoffer_price" id="">
                    </div>
                    <div class="form-group mb-3">
                        <label>Code</label>
                        <input type="text" class="form-control" name="sizeedcode" id="">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-sm btn-success">Save size detail</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- bulk upload variation modal --}}
<div class="modal action-modal fade" id="csvUploadModal" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                Bulk Upload Existing Product Variation with SKU code, color & size
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                    <form method="post" action="{{ route('products.variation.csv.upload') }}" enctype="multipart/form-data">@csrf
                        <input type="file" name="file" class="form-control" accept=".csv">
                        <div class="cta-row">
                        <a href="{{ asset('backend/csv/product-variation-sample.csv') }}" class="btn-cta">Download Sample CSV</a>
                        <button type="submit" class="btn btn-cta" id="csvImportBtn">Import <i class="fas fa-upload"></i></button>
                        </div>
                    </form>
                </div>
            
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    function renameColorModalOpen(colorId, colorName) {
            $('#colorName2').text(colorName);
            $('input[name="update_color_name"]').val(colorName);
            $('input[name="current_color2"]').val(colorId);
            $('#renameColorModal').modal('show');
        }

		function editColorModalOpen(colorId, colorName) {
            $('#colorName').text(colorName);
            $('input[name="current_color"]').val(colorId);
            $('#editColorModal').modal('show');
        }

		function editSizeFunc(size, id, name, price, code) {
            $('#sizeNameDetail').text(size);
            $('#colorName3').text(name);
            $('input[name="id"]').val(id);
            $('input[name="size_details"]').val(name);
            $('input[name="sizeedprice"]').val(price);
            $('input[name="sizeedoffer_price"]').val(price);
            $('input[name="sizeedcode"]').val(code);
            $('#sizeDetailModal').modal('show');
        }
    

        ClassicEditor
        .create( document.querySelector( '#product_des' ) )
        .catch( error => {
            console.error( error );
        });
        ClassicEditor
        .create( document.querySelector( '#product_short_des' ) )
        .catch( error => {
            console.error( error );
        });

        $(document).on('click','.removeTimePrice',function(){
            var thisClickedBtn = $(this);
            thisClickedBtn.closest('tr').remove();
        });

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

        function deleteImage(imgId, id1, id2) {
            $.ajax({
                url : '{{route("products.variation.image.delete")}}',
                method : 'POST',
                data : {'_token' : '{{csrf_token()}}', id : imgId},
                beforeSend : function() {
                    $('#img__holder_'+id1+'_'+id2+' a').text('Deleting...');
                },
                success : function(result) {
                    $('#img__holder_'+id1+'_'+id2).hide();
                    toastFire('success', result.message);
                },
                error: function(xhr, status, error) {
                    // toastFire('danger', 'Something Went wrong');
                }
            });
        }

        $(".row_position").sortable({
            delay: 150,
            stop: function() {
                var selectedData = new Array();
                $('.row_position > .single-color-holder').each(function() {
                    selectedData.push($(this).attr("id"));
                });
                updateOrder(selectedData);
            }
        });

        function updateOrder(data) {
            // $('.loading-data').show();
            $.ajax({
                url : "{{route('products.variation.color.position')}}",
                type : 'POST',
                data: {
                    _token : '{{csrf_token()}}',
                    position : data
                },
                success:function(data) {
                    // toastFire('success', 'Color position updated successfully');
                    // $('.loading-data').hide();
                    // console.log();
                    if (data.status == 200) {
                        toastFire('success', data.message);
                    } else {
                        toastFire('error', data.message);
                    }
                }
            });
        }

        // product color status change
        function colorStatusToggle(id, productId, colorId) {
            $.ajax({
                url : '{{route("products.variation.color.status.toggle")}}',
                method : 'POST',
                data : {
                    _token : '{{csrf_token()}}',
                    productId : productId,
                    colorId : colorId,
                },
                success : function(result) {
                    if (result.status == 200) {
                        // toastFire('success', result.message);

                        if (result.type == 'active') {
                            $('#'+id+' .color_box').css('background', '#fff');
                            toastFire('success', result.message);
                            setTimeout(function () {
                                location.reload();
                            }, 100); 
                        } else {
                            $('#'+id+' .color_box').css('background', '#c1080a59');
                            toastFire('success', result.message);
                            setTimeout(function () {
                                location.reload();
                            }, 100); 
                        }
                    } else {
                        toastFire('error', result.message);
                    }
                }
            });
        }

        function addSizeModal(colorId, colorName) {
            $('#addColorModal .modal-title').text('Add new size');
            $('#addColorModal select[name="color"]').html('<option value="'+colorId+'">'+colorName+'</option>');
            $('#addColorModal').modal('show');
        }

        function addColorModal() {
            var contentData = `
            @php
                $color = \App\Models\Color::orderBy('name', 'asc')->get();
                foreach ($color as $key => $value) {
                    echo '<option value="'.$value->id.'">'.$value->name.'</option>';
                }
            @endphp
            `;
            $('#addColorModal .modal-title').text('Add new color');
            $('#addColorModal select[name="color"]').html('<option value="" selected>Select color...</option>'+ contentData);
            $('#addColorModal').modal('show');
        }


          // bulk action
        $('select[name="bulkAction"]').on('change', function() {
            $('#bulkActionForm').submit();
        });
</script>
    
    <script>
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll('.delete-confirm').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault(); // stop normal link

            let url = this.getAttribute('href');

            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url; // redirect if confirmed
                }
            });
        });
    });
});
</script>
@endsection
