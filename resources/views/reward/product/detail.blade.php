@extends('layouts.app')
@section('page', 'Product detail')

@section('content')
<section>
        <div class="row">
            <div class="col-sm-3">
                <div class="card shadow-sm">
                    <div class="card-header">Main image</div>
                    <div class="card-body">
                        <div class="w-100 product__thumb">
                            <label for="thumbnail"><img id="output" src="{{ asset($data->image) }}"/></label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-9">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <h5 class="text-muted">{{$data->title}}</h5>
                        </div>

                                  <hr>
                        <div class="form-group mb-3">
                            <h4>
                                <span class="text-muted small"> {{$data->amount}}</span>
                            </h4>
                        </div>
                        <hr>
                       {{-- <div class="form-group mb-3">
                            <p class="small">Short Description</p>
                            {!! $data->short_desc !!}
                        </div> --}}
                        <div class="form-group mb-3">
                            <p class="small">Description</p>
                            {!! $data->desc !!}
                        </div>
                        <hr>
                    </div>
                </div>
                <div class="card shadow-sm">
                    <div class="card-body">
                    <aside>
                        <nav>Product specification</nav>
                    </aside>
                    <content>
                        <div class="row mb-2 align-items-center">
                            <table class="table table-sm" id="timePriceTable">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Description</th>
                                        
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($spec as $index => $item)
                                    <tr>
                                        <td>
                                            {{$item->name}}
                                        </td>
                                        <td>
                                            {{$item->description}}
                                               
                                        </td>
                                        
                                    </tr>
                                    @empty
                                    <tr><td colspan="100%" class="small text-muted text-center">No data found</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        
                    </content>
                </div>
            </div>
            </div>
        </div>
    </form>
</section>
@endsection
