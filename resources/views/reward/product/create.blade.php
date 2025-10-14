@extends('layouts.app')

@section('content')
<section>
    <form method="post" action="{{ route('reward.retailer.product.store') }}" enctype="multipart/form-data">@csrf
        <div class="row">
            <div class="col-sm-9">
                <div class="form-group mb-3">
                    <input type="text" name="title" placeholder="Add Product Title" class="form-control" value="{{old('title')}}">
                     @error('title') <p class="small text-danger">{{ $message }}</p> @enderror
                </div>

               {{--  <div class="card shadow-sm">
                    <div class="card-header">
                        Short Description
                    </div>
                    <div class="card-body">
                        <textarea id="product_short_des" name="short_desc">{{old('short_desc')}}</textarea>
                        @error('short_desc') <p class="small text-danger">{{ $message }}</p> @enderror
                    </div>
                </div> --}}

                <div class="card shadow-sm">
                    <div class="card-header">
                        Description
                    </div>
                    <div class="card-body">
                        <textarea id="product_des" name="desc">{{old('desc')}}</textarea>
                        @error('desc') <p class="small text-danger">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header">
                        Product data
                    </div>
                    <div class="card-body pt-0">
                        <div class="admin__content">
                            <aside>
                                <nav>Points</nav>
                            </aside>
                            <content>
                                <div class="row mb-2 align-items-center">
                                <div class="col-3">
                                    <label for="inputPassword6" class="col-form-label">Points</label>
                                </div>
                                <div class="col-auto">
                                    <input type="text" id="inputprice6" class="form-control" aria-describedby="priceHelpInline" name="amount" value="{{old('amount')}}">
                                    @error('amount') <p class="small text-danger">{{ $message }}</p> @enderror
                                </div>
                                </div>
                                
                            </content>
                        </div>
                    </div>
                </div>
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h3>Product specification</h3>
                        <p class="small text-muted m-0">Add model | color from here</p>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <table class="table table-sm" id="timePriceTable">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Description</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <input class="form-control" type="text" name="name[]">
                                            </td>
                                            <td>
                                                <textarea class="form-control" type="text" name="description[]"></textarea>
                                                   
                                            </td>
                                            <td><a class="btn btn-success actionTimebtn addNewTime">+</a></td>
                                        </tr>
                                    </tbody>
                                </table>
                                @error('name')<p class="text-danger">{{$message}}</p>@enderror
                                @error('description')<p class="text-danger">{{$message}}</p>@enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card shadow-sm">
                    <div class="mb-3">
                        <div class="card-header">
                            Brand Permission:
                        </div>
                        <div class="card-body">

                            <div class="form-check">
                            <input 
                                class="form-check-input medium-checkbox" 
                                type="checkbox" 
                                name="brand[]" 
                                value="1" 
                                id="mediumOnn"
                                onchange="toggleSelectBox()"
                            >
                                <label class="form-check-label" for="mediumLMS">Onn</label>
                            </div>
                            <div class="form-check">
                                <input 
                                    class="form-check-input medium-checkbox" 
                                    type="checkbox" 
                                    name="brand[]" 
                                    value="2" 
                                    id="mediumPynk"
                                    onchange="toggleSelectBox()"
                                >
                                <label class="form-check-label" for="mediumFMS">Pynk</label>
                            </div>
                                                
                        <div class="form-check">
                            <input 
                                class="form-check-input medium-checkbox" 
                                type="checkbox" 
                                name="brand[]" 
                                value="3" 
                                id="mediumBoth"
                                onchange="toggleSelectBox()"
                            >
                            <label class="form-check-label" for="mediumCave">Both</label>
                        </div>
                    </div>
                   </div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="card shadow-sm">
                    <div class="card-header">
                        Product Main Image
                    </div>
                    <div class="card-body">
                        <div class="w-100 product__thumb">
                        <label for="thumbnail"><img id="output" src="{{ asset('admin/images/placeholder-image.jpg') }}"/></label>
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
                <div class="card-header">
                    Publish
                </div>
                <div class="card-body text-end">
                    <button type="submit" class="btn btn-sm btn-danger">Publish </button>
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
                <input class="form-control" type="text" name="name[]">
            </td>
            <td>
                <textarea class="form-control" type="text" name="description[]"></textarea>
            </td>
            <td><a class="btn btn-success actionTimebtn addNewTime">+</a></td>
        </tr>
        `;

		$('#timePriceTable').append(toAppend);
	});

	$(document).on('click','.removeTimePrice',function(){
		var thisClickedBtn = $(this);
		thisClickedBtn.closest('tr').remove();
	});
    
    </script>
    
@endsection