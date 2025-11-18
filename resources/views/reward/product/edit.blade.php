@extends('layouts.app')
@section('page', 'Edit Product')

@section('content')

<style>
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
    }
    .color_box span {
        margin-right: 10px;
    }
    .sizeUpload {
        margin-bottom: 10px;
    }
    .size_holder {
        padding: 10px 0;
        border-top: 1px solid #ddd;
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
        object-fit: contain;
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
</style>

<section>
    <form method="POST" action="{{ route('reward.retailer.product.update',$data->id) }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="product_id" value="{{$data->id}}">
        <div class="row">
            <div class="col-sm-9">
                <div class="form-group mb-3">
                    <input type="text" name="title" placeholder="Add Product Title" class="form-control" value="{{$data->title}}">
                    @error('title') <p class="small text-danger">{{ $message }}</p> @enderror
                </div>
    
               {{-- <div class="card shadow-sm">
                    <div class="card-header">
                        Product Short Description
                    </div>
                    <div class="card-body">
                        <textarea id="product_short_des" name="short_desc">{{$data->short_desc}}</textarea>
                        @error('short_desc') <p class="small text-danger">{{ $message }}</p> @enderror
                    </div>
                </div> --}}

                <div class="form-group mb-5">
                    <textarea id="product_des" name="desc">{{$data->desc}}</textarea>
                    @error('desc') <p class="small text-danger">{{ $message }}</p> @enderror
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
                                    <input type="text" id="inputprice6" class="form-control" aria-describedby="priceHelpInline" name="amount" value="{{old('amount',$data->amount)}}">
                                    @error('amount') <p class="small text-danger">{{ $message }}</p> @enderror
                                </div>
                                </div>
                                
                            </content>
                        </div>
                    </div>
                </div>
            </div>
			
            <div class="col-sm-3">
                
                 <div class="card shadow-sm">
                    <div class="card-header">
                        Product Image
                    </div>
                    <div class="card-body">
                        <div class="w-100 product__thumb">
                            <label for="thumbnail"><img id="output" src="{{ asset($data->image) }}"/></label>
                            @error('image') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                        <input type="file" id="thumbnail" accept="image/*" name="image" onchange="loadFile(event)" class="d-none">
                        <p class="mb-2"><small class="text-muted">Click on the image to browse</small></p>
						<p class="mb-0"><small>Image Size: 870px X 1160px</small></p>
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
				<div class="card shadow-sm" style="position: sticky;top: 60px;">
                   
                    <div class="card-body text-end">
                        <input type="hidden" name="product_id" value="{{$data->id}}">
                        <button type="submit" class="btn btn-danger w-100">Save changes</button>
                    </div>
                </div>
            </div>
        </div>
         <div class="card shadow-sm">
                <div class="mb-4">
                   <div class="card-header">
                       Brand Permission:
                   </div>
                    <div class="card-body">

                        <div class="form-check">
                           <input type="checkbox" id="brandOnn" value="1" onchange="updateBrandValue()" 
                               @checked(old('brand', $data->brand ?? '') == 1 )>
                           <label class="form-check-label" for="brandOnn">Onn</label>
                        </div>

                        <div class="form-check">
                            <input type="checkbox" id="brandPynk" value="2" onchange="updateBrandValue()" 
                                @checked(old('brand', $data->brand ?? '') == 2)>
                            <label class="form-check-label" for="brandPynk">Pynk</label>
                        </div>

                        <div class="form-check">
                            <input type="checkbox" id="brandBoth" value="3" onchange="updateBrandValue()" 
                                @checked(old('brand', $data->brand ?? '') == 3)>
                            <label class="form-check-label" for="brandBoth">Both</label>
                        </div>
                        <input type="hidden" name="brand" id="brandValue" value="{{$data->brand}}">
                    </div>
                </div>
        </div>
    </form>
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h3>Product specification</h3>
                        <p class="small text-muted m-0"></p>
                        <div class="col-sm-auto position: relative  float: right">
                            <a href="javascript: void(0)" onclick="addColorModal()" class="btn btn-sm btn-success  float: right;">Add</a>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                       
                        <div class="admin__content">
                            <content>
                                <div class="row">
                                    <div class="col-sm-auto ">
                                        <strong>#</strong>
                                    </div>
                                    <div class="col-sm-auto ">
                                        <strong>Title</strong>
                                    </div>
                                    <div class="col-sm ">
                                        <strong></strong>
                                    </div>
                                    <div class="col-sm-auto ">
                                        <strong>Description</strong>
                                    </div>
                                    <div class="col-sm ">
                                        <strong></strong>
                                    </div>
                                    <div class="col-sm ">
                                        <strong></strong>
                                    </div>
                                    <div class="col-sm ">
                                        <strong></strong>
                                    </div>
                                    <div class="col-sm ">
                                        <strong></strong>
                                    </div>
                                    <div class="col-sm">
                                        <div class="row">
                                            <div class="col-sm-11">
                                                <strong>Action</strong>
                                            </div>
                                        </div>
                                    </div>
                    
                                </div>
                                <hr>
                                @forelse ($spec as $index => $item)
                                <div class="row">
                                    <div class="col-sm-auto">
                                        <label for="inputPassword6" class="col-form-label">{{ $index + 1 }}</label>
                                    </div>
                                    
                                    <div class="col-sm-2">
                                        <div class="color_box">
                                            <p >{{ $item->name }}</p>
                                        </div>
                                    </div>
                                    <div class="col-sm">
                                        <div class="row">
                                            <div class="col-sm-11">
                                                {!!$item->description!!}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-auto">
                                        {{-- <a href="javascript: void(0)" class="btn btn-sm btn-success" onclick="editColorModalOpen({{$item->id}})">Edit</a> --}}
                                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#editColorModal_{{$item->id}}">
                                            Edit
                                          </button>
                                        <a href="{{ route('reward.retailer.product.specification.delete',$item->id,['product_id' =>$data->id]) }}" onclick="return confirm('Are you sure ?')" class="btn btn-sm btn-danger">Delete</a>
                                    </div>
                                </div>

                                {{-- edit color modal --}}
                                <div class="modal fade" id="editColorModal_{{$item->id}}" tabindex="-1" aria-labelledby="editColorModal({{$item->id}})" aria-hidden="true">
                                    <div class="modal-dialog modal-sm">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Specification</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="{{route('reward.retailer.product.specification.edit',$item->id)}}" method="post">@csrf
                                                    <input type="hidden" name="id" value="{{$item->id}}">
                                                    <input type="hidden" name="product_id" value="{{$data->id}}">
                                                    @php
                                                        $spec = \App\Models\ProductSpecification::where('id',$item->id)->first();
                                                        //dd($id);
                                                    
                                                    @endphp
                                                    <div class="form-group mb-3">
                                                        <input class="form-control" type="text" name="name" placeholder="Add Title" value="{{$item->name ?? ''}}">
                                                    </div>
                                                    <div class="form-group mb-3">
                                                        <textarea class="form-control" type="text" name="description" placeholder="Add Description">{{$item->description ?? ''}}</textarea>
                                                    </div>
                                                    <div class="form-group">
                                                        <button type="submit" class="btn btn-sm btn-success">Save</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </content>
                        </div>
                        
            
                    </div>
                        
                    </div>
                   
            </div>
</section>
<div class="modal fade" tabindex="-1" id="addColorModal" data-bs-backdrop="static">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <form action="{{route('reward.retailer.product.specification.add')}}" method="post">@csrf
                <input type="hidden" name="product_id" value="{{$data->id}}">
                {{-- <input type="hidden" name="color" value="{{$productColorGroupVal->color}}"> --}}
                
                <div class="form-group mb-3">
                    <input class="form-control" type="text" name="name" placeholder="Add Title">
                </div>
                <div class="form-group mb-3">
                    <textarea class="form-control" type="text" name="description" placeholder="Add Description"></textarea>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-sm btn-success">+ Save changes</button>
                </div>
            </form>
        </div>
      </div>
    </div>
</div>

@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js" integrity="sha512-uto9mlQzrs59VwILcLiRYeLKPPbS/bT71da/OEBYEwcdNUk8jYIy+D176RYoop1Da+f9mvkYrmj5MCLZWEtQuA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    
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

    function addColorModal() {
            var contentData = `
            
            `;
            $('#addColorModal .modal-title').text('Add');
            $('#addColorModal input[name="name"]').html(contentData);
            $('#addColorModal').modal('show');
        }

        function editColorModalOpen(Id) {
            $('input[name="id"]').val(Id);
            console.log(Id);
            $('#editColorModal').modal('show');
        }

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