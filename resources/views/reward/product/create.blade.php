@extends('layouts.app')

@section('content')
<div class="container mt-5">
        <div class="row">
            <div class="col-md-12">

                @if ($errors->any())
                <ul class="alert alert-warning">
                    @foreach ($errors->all() as $error)
                        <li>{{$error}}</li>
                    @endforeach
                </ul>
                @endif

                <div class="card data-card">
                    <div class="card-header">
                        <h4 class="d-flex">Create Product
                            <a href="{{ url('reward/product') }}" class="btn btn-cta ms-auto">Back</a>
                        </h4>
                    </div>
                    <div class="card-body">

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
                                        <div class="mb-3">
                                            <div class="card-header">
                                                Brand Permission:
                                            </div>
                                            <div class="card-body">

                                                <div class="form-check">
                                                    <input 
                                                        class="form-check-input medium-checkbox" 
                                                        type="checkbox" 
                                                        id="brandOnn" 
                                                        value="1"
                                                        onchange="updateBrandValue()"
                                                    >
                                                    <label class="form-check-label" for="brandOnn">Onn</label>
                                                </div>
                                                <div class="form-check">
                                                    <input 
                                                        class="form-check-input medium-checkbox" 
                                                        type="checkbox" 
                                                        id="brandPynk" 
                                                        value="2"
                                                        onchange="updateBrandValue()"
                                                    >
                                                    <label class="form-check-label" for="brandPynk">Pynk</label>
                                                </div>
                                                <div class="form-check">
                                                    <input 
                                                        class="form-check-input medium-checkbox" 
                                                        type="checkbox" 
                                                        id="brandBoth" 
                                                        value="3"
                                                        onchange="updateBrandValue()"
                                                    >
                                                    <label class="form-check-label" for="brandBoth">Both</label>
                                                </div>
                                                <input type="hidden" name="brand" id="brandValue">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card text-dark shadow-sm">
                                        <div class="mb-4">
                                            <div class="card-body">
                                               Product specification
                                            </div>
                                            <div class="card-body">
                                                <p class="small text-muted m-0">Add model | color from here</p>
                                            </div>
                                        
                                        </div>
                                        <div class="card-body pt-0">
                            
                                            <div class="admin__content">
                                                <content>
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
                                                </content>

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
                    </div>
                </div>
            </div>
        </div>
    </div>



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
 
    function updateBrandValue() {
        let brandOnn = document.getElementById('brandOnn');
        let brandPynk = document.getElementById('brandPynk');
        let brandBoth = document.getElementById('brandBoth');
        let brandValueInput = document.getElementById('brandValue');

        if (brandBoth.checked) {
            // brandOnn.checked = false;
            // brandPynk.checked = false;
            brandValueInput.value = 3;
            return;
        }

        if (!brandBoth.checked) {
            if (brandOnn.checked && brandPynk.checked) {
                brandValueInput.value = 3;
            } else if (brandOnn.checked) {
                brandValueInput.value = 1;
            } else if (brandPynk.checked) {
                brandValueInput.value = 2;
            } else {
                brandValueInput.value = '';
            }
        }
    }
</script>
@endsection