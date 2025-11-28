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
                        <h4 class="d-flex">Qrcode Generate
                            <a href="{{ route('reward.retailer.barcode.index') }}" class="btn btn-cta ms-auto">Back</a>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                        <div class="col-xl-3 col-lg-2 col-12"></div>
                            <div class="col-xl-6 col-lg-8 col-12">
                                <form method="POST" action="{{ route('reward.retailer.barcode.store') }}" enctype="multipart/form-data" class="data-form">
                                    @csrf
                                    
                                    <div class="form-group mb-3">
                                        <label class="label-control">Qrcode details <span class="text-danger">*</span> </label>
                                        <input type="text" name="name" id="name" placeholder="" class="form-control" value="{{old('name')}}">
                                        @error('name') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                
                                    <div class="form-group mb-3">
                                        <label class="label-control">Start date <span class="text-danger">*</span> </label>
                                        <input type="datetime-local" name="start_date" placeholder="" class="form-control" value="{{ old('start_date') }}" min="{{ date('Y-m-d\TH:i') }}">
                                        @error('start_date') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                
                                    <div class="form-group mb-3">
                                        <label class="label-control">End date <span class="text-danger">*</span> </label>
                                        <input type="datetime-local" name="end_date" placeholder="" class="form-control" value="{{ old('end_date') }}" 
                                        min="{{ date('Y-m-d\TH:i') }}">
                                        @error('end_date') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                
                                    <div class="form-group mb-3">
                                        <label class="label-control"> Amount <span class="text-danger">*</span> </label>
                                        <input type="number" name="amount" placeholder="" class="form-control" value="{{old('amount')}}">
                                        @error('amount') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                
                                    <div class="form-group mb-3">
                                        <label class="label-control">No of qrcodes to generate <span class="text-danger">*</span> </label>
                                        <input type="number" name="generate_number" placeholder="" class="form-control" value="{{old('generate_number')? old('generate_number') : '100' }}">
                                        @error('generate_number') <p class="small text-danger">{{ $message }}</p> @enderror
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
                                        @endif
                                

                                    <div class="row">
                                        <div class="col-12">
                                            <p class="small text-danger">Qrcodes code will be auto-generated</p>
                                        </div>
                                        <div class="col-12">
                                            {{-- <input type="hidden" name="type" value="1"> --}}
                                            <input type="hidden" name="max_time_of_use" value="1">
                                            <input type="hidden" name="max_time_one_can_use" value="1">
                                            <button type="submit" class="btn btn-danger w-100">Tap here to generate codes</button>
                                        </div>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>

@endsection

@section('script')
 <script>
        function formatDate(date) {
            const day = String(date.getDate()).padStart(2, '0');  
            const month = String(date.getMonth() + 1).padStart(2, '0'); 
            const year = date.getFullYear();  

            return `${day}-${month}-${year}`;
            console.log(year);
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

    <script>
function checkOnlyOne(checkbox) {
    const checkboxes = document.querySelectorAll('.medium-checkbox');
    checkboxes.forEach(cb => {
        if (cb !== checkbox) cb.checked = false;
    });
}

// ✅ Show toast only if brand section is visible & none selected
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');
    if (!form) return; // safety check

    form.addEventListener('submit', function (e) {
        const brandBlock = document.getElementById('brandPermissionBlock');

        // ✅ Only run validation if the block exists
        if (brandBlock) {
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
        }
    });
});
</script>
@endsection  
    