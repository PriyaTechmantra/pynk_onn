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
                        <h4 class="d-flex">Edit Bookshelves
                            <a href="{{ url('bookshelves') }}" class="btn btn-cta ms-auto">Back</a>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-3 col-lg-2 col-12"></div>
                            <div class="col-xl-6 col-lg-8 col-12">
                                <form action="{{ url('bookshelves/'.$data->id) }}" method="POST" class="data-form">
                                    @csrf
                                    @method('PUT')
        
                                    <div class="mb-3">
                                        <label for="">Office</label>
                                        <select class="form-select form-select-sm select2" aria-label="Default select example" name="office_id" id="office_id">
                                            <option value=""  selected>Select Office</option>
                                            @foreach ($office as $index => $item)
                                                        <option value="{{$item->id}}" {{ ($data->office_id == $item->id) ? 'selected' :  '' }}>{{ $item->name }}({{$item->address}})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="">Office Area</label>
                                        <textarea type="text" name="area" class="form-control" />{{$data->area}}</textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="">Bookshelf Number</label>
                                        <input type="text" name="number" value="{{$data->number}}" class="form-control" />
                                    </div>
                                    <div class="mb-3">
                                        <label for="">Manager</label>
                                        <input type="text" name="manager" value="{{$data->manager}}" class="form-control" />
                                    </div>
                                    
                                    <div class="text-end mb-3">
                                        <button type="submit" class="btn btn-submit">Update</button>
                                    </div>
                                </form>
                            </div>
                            <div class="col-xl-3 col-lg-2 col-12"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection