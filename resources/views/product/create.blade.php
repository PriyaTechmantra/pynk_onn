@extends('layouts.app')



@section('content')
<section>
    <form method="post" action="{{ route('products.store') }}" enctype="multipart/form-data">@csrf
        <div class="row">
        <div class="col-sm-9">

                <div class="row mb-3">
                    <div class="mb-3">
                        <div class="card shadow-sm">
                            <div class="card-header" style="background: #dc3545 ; color: #fff;">
                        <!-- Communication Medium -->
                                <h6>Brand Permission:  <span class="text-danger">*</span></h6>
                                @error('brand') <p class="small text-white">{{ $message }}</p> @enderror
                            </div>
                        <div class="card-body">
                            <div class="form-check">
                                <input 
                                    class="form-check-input medium-checkbox" 
                                    type="checkbox" 
                                    name="brand" 
                                    value="1" 
                                    id="mediumOnn"
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
                                {{-- <option value="" disabled>Select</option>
                                <option value="all" selected>All</option> --}}
                                <option value="" selected disabled>Select</option>
                                <option value="10000">All</option>
                                @foreach ($collection as $index => $item)
                                <option value="{{ $item->id }}" {{ ($request->collection == $item->id) ? 'selected' : '' }}>{{ $item->name }}</option>
                                @endforeach
                            </select>
                        @error('collection_id') <p class="small text-danger">{{ $message }}</p> @enderror
                    </div>
                        <div class="col-sm-6">
                            <label for="" class="col-form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm select2" aria-label="Default select example" name="cat_id" id="category">
                                <option value=""  selected>Select Category</option>
                                @foreach ($category as $index => $item)
                                            <option value="{{$item->id}}" {{ (request()->input('cat_id') == $item->id) ? 'selected' :  '' }}>{{ $item->name }}</option>
                                @endforeach
                            </select>
                            @error('cat_id') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
            
                        
                    </div>

                    <div class="form-group mb-3">
                        <label for="" class="col-form-label">Title/Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" placeholder="Add Product Title" class="form-control" value="{{old('name')}}">
                        @error('name') <p class="small text-danger">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group mb-3">
                        <label for="" class="col-form-label">Style No <span class="text-danger">*</span></label>
                        <input type="text" name="style_no" placeholder="Add Product Style No" class="form-control" value="{{old('style_no')}}">
                        @error('style_no') <p class="small text-danger">{{ $message }}</p> @enderror
                    </div>
                    
                                </div>
                                </div>

            <div class="card shadow-sm">
                <div class="card-header" style="background: #dc3545; color: #fff;" >
                    <h6>Short Description</h6>
                </div>
                <div class="card-body">
                    <textarea id="product_short_des" name="short_desc">{{old('short_desc')}}</textarea>
                    @error('short_desc') <p class="small text-danger">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header" style="background: #dc3545; color: #fff;">
                    <h6>Description</h6>
                </div>
                <div class="card-body">
                    <textarea id="product_des" name="desc">{{old('desc')}}</textarea>
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
                            <input type="text" id="inputprice6" class="form-control" aria-describedby="priceHelpInline" name="price" value="{{old('price')}}">
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
                            <input type="text" id="inputprice6" class="form-control" aria-describedby="priceHelpInline" name="offer_price" value="{{old('offer_price')}}">
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
                                    <input type="text" id="inputprice6" class="form-control" aria-describedby="priceHelpInline" name="size_chart" value="{{old('size_chart')}}">
                                    @error('size_chart') <p class="small text-danger">{{ $message }}</p> @enderror
                                </div>
                                
                            </div>
                            <div class="row mb-2 align-items-center">
                                <div class="col-3">
                                    <label for="inputprice6" class="col-form-label">Pack</label>
                                </div>
                                <div class="col-auto">
                                    <input type="text" id="inputprice6" class="form-control" aria-describedby="priceHelpInline" name="pack" value="{{old('pack')}}">
                                    @error('pack') <p class="small text-danger">{{ $message }}</p> @enderror
                                </div>
                                
                            </div>
                            <div class="row mb-2 align-items-center">
                                <div class="col-3">
                                    <label for="inputprice6" class="col-form-label">Pack Count</label>
                                </div>
                                <div class="col-auto">
                                    <input type="text" id="inputprice6" class="form-control" aria-describedby="priceHelpInline" name="pack_count" value="{{old('pack_count')}}">
                                    @error('pack_count') <p class="small text-danger">{{ $message }}</p> @enderror
                                </div>
                                
                            </div>
                            <div class="row mb-2 align-items-center">
                                <div class="col-3">
                                    <label for="inputprice6" class="col-form-label">Master Pack</label>
                                </div>
                                <div class="col-auto">
                                    <input type="text" id="inputprice6" class="form-control" aria-describedby="priceHelpInline" name="master_pack" value="{{old('master_pack')}}">
                                    @error('master_pack') <p class="small text-danger">{{ $message }}</p> @enderror
                                </div>
                               
                            </div>
                            <div class="row mb-2 align-items-center">
                                <div class="col-3">
                                    <label for="inputprice6" class="col-form-label">Master Pack Count</label>
                                </div>
                                <div class="col-auto">
                                    <input type="text" id="inputprice6" class="form-control" aria-describedby="priceHelpInline" name="master_pack_count" value="{{old('master_pack_count')}}">
                                    @error('master_pack_count') <p class="small text-danger">{{ $message }}</p> @enderror
                                </div>
                                
                            </div>
                            <div class="row mb-2 align-items-center">
                                <div class="col-3">
                                    <label for="inputprice6" class="col-form-label">Only For</label>
                                </div>
                                <div class="col-auto">
                                    <input type="text" id="inputprice6" class="form-control" aria-describedby="priceHelpInline" name="only_for" value="{{old('only_for')}}">
                                    @error('only_for') <p class="small text-danger">{{ $message }}</p> @enderror
                                </div>
                                
                            </div>
                        </content>
                    </div>
                   
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-sm" id="timePriceTable">
                                <thead>
                                    <tr>
                                        <th>Color</th>
                                        <th>Size</th>
                                        <th>Price</th>
                                        <th>Offer Price</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <select class="form-control select2" name="color_id[]">
                                                <option value="" disabled hidden selected>Select...</option>
                                                @foreach($colors as $colorIndex => $colorValue)
                                                    <option value="{{$colorValue->id}}" @if (old('color') && in_array($colorValue,old('color'))){{('selected')}}@endif>{{$colorValue->name}}</option>
                                                @endforeach
                                            </select>
                                            @error('color_id') <p class="small text-danger">{{ $message }}</p> @enderror
                                        </td>
                                        <td>
                                            <select class="form-control select2" name="size_id[]">
                                                <option value="" disabled hidden selected>Select...</option>
                                                @foreach($sizes as $sizeIndex => $sizeValue)
                                                    <option value="{{$sizeValue->id}}">{{$sizeValue->name}}</option>
                                                @endforeach
                                            </select>
                                            @error('size_id') <p class="small text-danger">{{ $message }}</p> @enderror
                                        </td>
                                        <td>
                                            <input type="text" id="inputprice6" class="form-control" aria-describedby="priceHelpInline" name="sizeprice[]" value="{{ old('sizeprice')[0] ?? '' }}">
                                            @error('sizeprice') <p class="small text-danger">{{ $message }}</p> @enderror
                                        </td>
                                        <td>
                                            <input type="text" id="inputprice6" class="form-control" aria-describedby="priceHelpInline" name="sizeoffer_price[]" value="{{ old('sizeoffer_price')[0] ?? '' }}">
                                            @error('sizeoffer_price') <p class="small text-danger">{{ $message }}</p> @enderror
                                        </td>
                                        <td><a class="btn btn-sm btn-success actionTimebtn addNewTime">+</a></td>
                                    </tr>
                                </tbody>
                            </table>
                            
                        </div>
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
                    <label for="thumbnail"><img id="output" src="{{ asset('backend/images/placeholder-image.jpg') }}"/></label>
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
</section>
@endsection

@section('script')
<script>
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

    $(document).on('click','.addNewTime',function(){
		var thisClickedBtn = $(this);
		thisClickedBtn.removeClass(['addNewTime','btn-success']);
		thisClickedBtn.addClass(['removeTimePrice','btn-danger']).text('X');

		var toAppend = `
        <tr>
            <td>
                <select class="form-control select2" name="color_id[]">
                    <option value="" hidden selected>Select...</option>
                    @foreach($colors as $colorIndex => $colorValue)
                        <option value="{{$colorValue->id}}" @if (old('color') && in_array($colorValue,old('color'))){{('selected')}}@endif>{{$colorValue->name}}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <select class="form-control select2" name="size_id[]">
                    <option value="" hidden selected>Select...</option>
                    @foreach($sizes as $sizeIndex => $sizeValue)
                        <option value="{{$sizeValue->id}}">{{$sizeValue->name}}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="text" id="inputprice6" class="form-control" aria-describedby="priceHelpInline" name="sizeprice[]" value="{{ old('sizeprice')[0] ?? '' }}">
            </td>
            <td>
                <input type="text" id="inputprice6" class="form-control" aria-describedby="priceHelpInline" name="sizeoffer_price[]" value="{{old('sizeoffer_price')[0] ?? ''}}">
            </td>
            <td><a class="btn btn-sm btn-success actionTimebtn addNewTime">+</a></td>
        </tr>
        `;

		$('#timePriceTable').append(toAppend);
	});

	$(document).on('click','.removeTimePrice',function(){
		var thisClickedBtn = $(this);
		thisClickedBtn.closest('tr').remove();
	});
</script>
    
    <script>
		$('select[id="category"]').on('change', (event) => {
			var value = $('select[id="category"]').val();

			$.ajax({
				url: '{{url("/")}}/api/collection/'+value,
                method: 'GET',
                success: function(result) {
					var content = '';
					var slectTag = 'select[id="collection"]';
					var displayCollection =  "All";

					content += '<option value="" selected>'+displayCollection+'</option>';
					$.each(result.data, (key, value) => {
						content += '<option value="'+value.id+'">'+value.name+'</option>';
					});
					$(slectTag).html(content).attr('disabled', false);
                }
			});
		});
    </script>
     <script>
		$('select[id="color"]').on('change', (event) => {
			var value = $('select[id="color"]').val();

			$.ajax({
				url: '{{url("/")}}/api/size/list/'+value,
                method: 'GET',
                success: function(result) {
					var content = '';
					var slectTag = 'select[id="size"]';
					var displayCollection =  "All";

					content += '<option value="" selected>'+displayCollection+'</option>';
					$.each(result.data.primarySizes.size, (key, value) => {
						content += '<option value="'+value.id+'">'+value.name+'</option>';
					});
					$(slectTag).html(content).attr('disabled', false);
                }
			});
		});
    </script>
@endsection
