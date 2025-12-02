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


                @php
                            $assignedPermissions = DB::table('user_permission_categories')
                            ->select('user_permission_categories.*')
                            ->join('users','users.id','=','user_permission_categories.user_id')
                            ->where('user_permission_categories.user_id', Auth::user()->id)
                            ->get();

                            $brandMap = [
                                1 => 'ONN',
                                2 => 'PYNK',
                                3 => 'Both',
                            ];

                            $brands = $assignedPermissions->pluck('brand')->unique()->toArray();

                            // Check conditions
                                if (in_array(3, $brands)) {
                                    $brandPermissions = 'Both';
                                } elseif (in_array(1, $brands) && in_array(2, $brands)) {
                                    $brandPermissions = 'Both';
                                } else {
                                    $brandPermissions = collect($brands)
                                        ->map(fn($brand) => $brandMap[$brand] ?? $brand)
                                        ->implode(', ');
                                }
                                @endphp

                <div class="card data-card">
                    <div class="card-header">
                        <h4 class="d-flex">Edit Size
                            <a href="{{ url('sizes') }}" class="btn btn-cta ms-auto">Back</a>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                         <div class="col-xl-3 col-lg-2 col-12"></div>
                            <div class="col-xl-6 col-lg-8 col-12">
                                <form method="POST" action="{{route('sizes.update',$data->id)}}" enctype="multipart/form-data" class="data-form">
                                @csrf
                                    <h4 class="page__subtitle">Edit Size</h4>
                                    
                                    <div class="form-group mb-3">
                                        <label class="label-control">Title <span class="text-danger">*</span> </label>
                                        <input type="text" name="title" placeholder="" class="form-control" value="{{ $data->name }}">
                                        @error('title') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    
                                    @if($brandPermissions=='Both')
                                        <div class="mb-3">
                                            <!-- Communication Medium -->
                                            <h6>Brand Permission:  <span class="text-danger">*</span></h6>
                                             @error('brand') <p class="small text-danger">{{ $message }}</p> @enderror
                                             <div class="form-check">
                                                <input 
                                                    class="form-check-input medium-checkbox" 
                                                    type="checkbox" 
                                                    name="brand" 
                                                    value="1"
                                                    id="mediumOnn"
                                                     {{ ($data->brand == 1) ? 'checked' : '' }}
                                                    onchange="checkOnlyOne(this)"
                                                >
                                                <label class="form-check-label" for="mediumOnn">Onn</label>
                                            </div>

                                            <div class="form-check">
                                                <input 
                                                    class="form-check-input medium-checkbox" 
                                                    type="checkbox" 
                                                    name="brand" 
                                                    value="2" 
                                                    id="mediumPynk"
                                                     {{ ($data->brand == 2) ? 'checked' : '' }}
                                                    onchange="checkOnlyOne(this)"
                                                >
                                                <label class="form-check-label" for="mediumPynk">Pynk</label>
                                            </div>

                                            <div class="form-check">
                                                <input 
                                                    class="form-check-input medium-checkbox" 
                                                    type="checkbox" 
                                                    name="brand" 
                                                    value="3"  
                                                    id="mediumBoth"
                                                    {{ ($data->brand == 3) ? 'checked' : '' }}
                                                    onchange="checkOnlyOne(this)"
                                                >
                                                <label class="form-check-label" for="mediumBoth">Both</label>
                                            </div>
                                        </div>
                                        @endcan

                                    <div class="form-group">
                                        <button type="submit" class="btn btn-sm btn-danger">Update Size</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</section>
@endsection
@section('script')
<script>

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

<script>
    function checkOnlyOne(checkbox) {
        const checkboxes = document.querySelectorAll('.medium-checkbox');
        checkboxes.forEach(cb => {
            if (cb !== checkbox) cb.checked = false;
        });

        // ✅ Prevent form submission if none is selected
    document.querySelector('form').addEventListener('submit', function (e) {
        const selected = document.querySelector('.medium-checkbox:checked');
        if (!selected) {
            e.preventDefault();
            Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'warning',
            title: 'Please select a brand permission before submitting.',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
        }
    });
    }

    
    </script>
@endsection
