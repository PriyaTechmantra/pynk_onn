@extends('layouts.app')

@section('page', 'Product bulk action')

@section('content')
<section>
    <h5 class="text-muted">Title : <span class="text-dark">{{$request->name}}</span></h5>
    <h5 class="text-muted">Style no : <span class="text-dark">{{$request->style_no}}</span></h5>

    <form action="{{ route('products.variation.bulk.update') }}" method="post">@csrf
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>#SR</th>
                    <th>Color</th>
                    <th>Size</th>
                    <th>Price</th>
                    <th>Offer Price</th>
                    <th>SKU code</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $index => $item)
                    @php
                        $product_variation = \DB::table('product_color_sizes')->where('id', $item)->first();
                    @endphp
                    <tr>
                        <td>{{$index + 1}}</td>
                        <td>
                            @if ($product_variation->colorData->name)
                                {{$product_variation->colorData->name}}
                            @else
                                @php
                                    $color = \DB::table('colors')->where('id', $product_variation->color_id)->first();
                                @endphp
                                {{$color->name}}
                            @endif
                        </td>
                        <td>
                            @if ($product_variation->size->name)
                                {{$product_variation->size->name}}
                            @else
                                @php
                                    $size = \DB::table('sizes')->where('id', $product_variation->size_id)->first();
                                @endphp
                                {{$size->name}}
                            @endif
                        </td>
                         <td>
                            <div class="input-group input-group-sm mb-3">
                                <span class="input-group-text" id="inputGroup-sizing-sm">&#8377;</span>
                                <input type="text" name="price[]" class="form-control" value="{{$product_variation->price}}">
                            </div>
                        </td> 

                        <td>
                            <div class="input-group input-group-sm mb-3">
                                <span class="input-group-text" id="inputGroup-sizing-sm">&#8377;</span>
                                <input type="text" name="offer_price[]" class="form-control" value="{{$product_variation->offer_price}}">
                            </div>
                        </td>
                        <td>{{$product_variation->code}}</td>
                    </tr>

                    <input type="hidden" name="id[]" value="{{$item}}">
                @empty
                    <tr>
                        <td colspan="100%" class="small text-muted text-center">No data found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    
        <div class="card card-body" style="position: sticky;bottom: 0;">
            <div class="row">
                <div class="col-12 text-end">
                    <input type="hidden" name="product_id" value="{{$request->product_id}}">
                    <button type="submit" class="btn btn-danger">Save changes</button>
                </div>
            </div>
        </div>
    </form>
</section>
@endsection

@section('script')
    <script>

    </script>
@endsection
