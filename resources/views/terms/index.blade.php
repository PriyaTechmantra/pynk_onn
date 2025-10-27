@extends('layouts.app')
@section('page', 'Terms and Condition')
@section('content')
<section>
    <div class="row">
        <div class="col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <p class="text-muted small mb-1">Terms and Condition</p>
                            <p class="text-dark small">{!!$data->terms ?? ''	!!}</p>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
   
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    @if(!empty($data))
                        <form method="POST" action="{{ route('reward.retailer.terms.update',['id'=>$data->id]) }}" enctype="multipart/form-data">
                        @csrf
                            <h4 class="page__subtitle">Edit</h4>
                            <div class="form-group mb-3">
                                <label class="label-control">Terms and Condition <span class="text-danger">*</span> </label>
                                <textarea type="text" id="terms" name="terms" placeholder="" class="form-control">{{ $data->terms }}</textarea>
                                @error('terms') <p class="small text-danger">{{ $message }}</p> @enderror
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
                            
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-sm btn-danger">Update</button>
                                
                            </div>
                        </form>
                    @else
                        <form method="POST" action="{{ route('reward.retailer.terms.store') }}" enctype="multipart/form-data">
                        @csrf
                            <h4 class="page__subtitle">Add</h4>
                            <div class="form-group mb-3">
                                <label class="label-control">Terms and Condition <span class="text-danger">*</span> </label>
                                <textarea type="text" id="terms" name="terms" placeholder="" class="form-control"></textarea>
                                @error('terms') <p class="small text-danger">{{ $message }}</p> @enderror
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
                                
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-sm btn-danger">ADD</button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <br>
    
</section>
@endsection
@section('script')
<script>
 ClassicEditor
        .create( document.querySelector( '#terms' ) )
        .catch( error => {
            console.error( error );
        });
	ClassicEditor
        .create( document.querySelector( '#failure_message' ) )
        .catch( error => {
            console.error( error );
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