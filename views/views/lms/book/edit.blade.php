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

                <div class="card">
                    <div class="card-header">
                        <h4>Edit Books
                            <a href="{{ url('books') }}" class="btn btn-danger float-end">Back</a>
                        </h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ url('books/'.$data->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="">Office</label>
                                <select class="form-select form-select-sm" aria-label="Default select example" name="office_id" id="office_id">
                                    <option value="" selected disabled>Select Office</option>
                                    @foreach ($office as $cat)
                                        <option value="{{$cat->id}}" {{$data->office_id == $cat->id ? 'selected' : ''}}> {{$cat->name}}({{$cat->address}})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="">Bookshelve</label>
                                <select class="form-select form-select-sm" aria-label="Default select example" name="bookshelves_id" id="bookshelves">
                                    <option value="" selected disabled>Select Bookshelve</option>
                                    
                                    
                                                        
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="">Category</label>
                               <select class="form-select form-select-sm" aria-label="Default select example" name="category_id" id="category_id">
                                <option value="" selected disabled>Select Category</option>
                                @foreach ($category as $cat)
                                    <option value="{{$cat->id}}" {{$data->category_id == $cat->id ? 'selected' : ''}}> {{$cat->name}}</option>
                                @endforeach
                               </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="">Title</label>
                                <input type="text" name="title" value="{{$data->title}}" class="form-control" />
                            </div>
                            <div class="mb-3">
                                <label for="">Author</label>
                                <input type="text" name="author" value="{{$data->author}}" class="form-control" />
                            </div>
                            <div class="mb-3">
                                <label for="">Publisher</label>
                                <input type="text" name="publisher" value="{{$data->publisher}}" class="form-control" />
                            </div>
                            <div class="mb-3">
                                <label for="">Edition</label>
                                <input type="text" name="edition" value="{{$data->edition}}" class="form-control" />
                            </div>
                            <div class="mb-3">
                                <label for="">Pages</label>
                                <input type="text" name="page" value="{{$data->page}}" class="form-control" />
                            </div>
                            <div class="mb-3">
                                <label for="">Quantity</label>
                                <input type="text" name="quantity" value="{{$data->quantity}}" class="form-control" />
                            </div>
                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary">Save</button>
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
    $('select[name="office_id"]').on('change', (event) => {
        var value = $('select[name="office_id"]').val();
        OfficeChange(value);
    });
    @if (request()->input('office_id'))
        OfficeChange({{request()->input('office_id')}})
    @else
        OfficeChange({{$data->office_id}})
    @endif

    function OfficeChange(value) {
        $.ajax({
            url: '{{url("/")}}/bookshelves/list/officewise/'+value,
            method: 'GET',
            success: function(result) {
                var content = '';
                var slectTag = 'select[name="bookshelves_id"]';
                var displayCollection =  "All";

                content += '<option value="" selected>'+displayCollection+'</option>';
                $.each(result.data, (key, value) => {
                    let selected = ``;
                    @if (request()->input('bookshelves_id')||$data->bookshelves_id)
                        if({{$data->bookshelves_id}} == value.id) {selected = 'selected';}
                    @endif
                    content += '<option value="'+value.id+'"'; content+=selected; content += '>'+value.number+'</option>';
                });
                $(slectTag).html(content).attr('disabled', false);
            }
        });
    }
    
</script>
@endsection