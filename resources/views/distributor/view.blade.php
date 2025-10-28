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
                        <h4 class="d-flex">Distributor Detail
                            
                            <a href="{{ url('distributors') }}" class="btn btn-cta ms-auto">Back</a>
                            @can('update employee')
                                <a href="{{ url('distributors/'.$data->id.'/edit') }}" class="btn btn-cta">
                                    Edit
                                </a>
                            @endcan
                            @can('add range')
                                <a href="{{ url('distributors/range/'.$data->id.'/add') }}" class="btn btn-cta">
                                    Range
                                </a>
                            @endcan
                            
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-3 col-lg-2 col-12"></div>
                            <div class="col-xl-6 col-lg-8 col-12">
                                
                                <div class="table-responsive">
                                    <table class="table">
                                         <div class="user-info">
                                            
                                         <tr>
                                            <td class="text-muted">VP : </td>
                                            <td>{{$team->vp->name ??''}}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">RSM : </td>
                                            <td>{{$team->rsm->name ??''}}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">ASM : </td>
                                            <td>{{$team->asm->name ??''}}</td>
                                        </tr>
                                             <tr>
                                                <td class="text-muted">Name : </td>
                                                <td>{{$data->name}}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">User Type: </td>
                                                <td>{{ $data->designation ? $data->designation : userTypeName($data->type) }}</td>
                                            </tr>
                                         </div>
                                        <tr>
                                            <td class="text-muted">Designation: </td>
                                            <td>{{ $data->designation ??''}}</td>
                                        </tr>
                                        
                                        <tr>
                                            <td class="text-muted">Employee ID :  </td>
                                            <td>{{ $data->employee_id }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Email : </td>
                                            <td>{{$data->email}}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Mobile : </td>
                                            <td>{{ $data->mobile ??''}}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">WhatsApp Number : </td>
                                            <td>{{ $data->whatsapp_no ??''}}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Alt. Mobile number 1 : </td>
                                            <td>{{ $data->alt_number1 ??''}}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Alt. Mobile number 2 : </td>
                                            <td>{{ $data->alt_number2 ??''}}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Alt. Mobile number 3 : </td>
                                            <td>{{ $data->alt_number3 ?? ''}}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Personal email : </td>
                                            <td>{{ $data->personal_mail ??''}}</td>
                                        </tr>
                                        
                                        <tr>
                                            <td class="text-muted">State : </td>
                                            <td>{{ $data->stateDetail->name ??''}}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Area/City : </td>
                                            <td>{{ $data->area->name ??''}}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Working Area List</td>
                                            @foreach ($workAreaList as $item)
                                                <td>
                                                    @can('delete employee area')
                                                        <a href="{{ route('employee.area.delete', $item->id) }}" 
                                                        class="working_area delete-confirm" 
                                                        title="Delete area/bit">
                                                            <svg xmlns="http://www.w3.org/2000/svg" 
                                                                width="16" height="16" 
                                                                viewBox="0 0 24 24" 
                                                                fill="none" stroke="currentColor" 
                                                                stroke-width="1" stroke-linecap="round" 
                                                                stroke-linejoin="round" 
                                                                class="feather feather-x">
                                                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                                                <line x1="6" y1="6" x2="18" y2="18"></line>
                                                            </svg>
                                                           
                                                        </a>
                                                   
                                                        
                                                    @endcan
                                                        <span>
                                                            {{ $item->area->name }}@if(!$loop->last), @endif
                                                        </span>
                                                </td>
                                            @endforeach
                                        </tr>

                                        <tr>
                                            <td class="text-muted">Date of Joining : </td>
                                            <td>{{ $data->date_of_joining ??''}}</td>
                                        </tr>
                                        @php
                                        

                                            $brandMap = [
                                                1 => 'ONN',
                                                2 => 'PYNK',
                                                3 => 'Both',
                                            ];

                                            $brands = [$data->brand];

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
                                        <tr>
                                            <td class="text-muted">Brand Permission : </td>
                                            <td>{{ $brandPermissions ??''}}</td>
                                        </tr>
                                         
                                        
                                        <tr>
                                            <td class="text-muted">Status : </td>
                                            <td>{{($data->status == 1) ? 'Active' : 'Inactive'}}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Created/Edited By: </td>
                                            <td>{{ $data->createdBy->name ??'' }}</td>
                                        </tr>
                                        
                                        <tr>
                                            <td class="text-muted">Created At: </td>
                                            <td>{{ date('d-m-Y', strtotime($data->created_at)) }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-2 col-12"></div>
                        </div>
                    </div>
                </div>


                <div class="card data-card">
                    <div class="card-header">
                        <h4 class="d-flex">Distributor Details
                            
                            
                        </h4>
                        @if($brandPermissions=='Both')
                                <div class="col-md-4">
                                    <label class="small text-muted">Brand</label>
                                    <select class="form-select form-select-sm" aria-label="Default select example" name="brand" id="brand">
                                        <option value="" selected disabled>Select</option>
                                                <option value="All" {{ (request()->input('brand') == "All") ? 'selected' : '' }}>All</option>
                                        
                                            <option value="1" {{ (request()->input('brand') == 1) ? 'selected' : '' }}>ONN</option>
                                            <option value="2" {{ (request()->input('brand') == 2) ? 'selected' : '' }}>PYNK</option>
                                            
                                            
                                    </select>
                                </div>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-3 col-lg-2 col-12"></div>
                            <div class="col-xl-6 col-lg-8 col-12">
                                
                                @forelse ($distributorList as $item)
                                    
                                    
                                        <div class="col-md-6">
                                            @can('view distributor')
                                            <a href="{{ url('distributors/'.$item->id) }}"><h5>{{$item->distributor->name ??''}}</h5></a>
                                            @else
                                            <h5>{{$item->distributor->name ??''}}</h5>
                                            @endcan
                                        </div>
                                    
                                    @empty
                                        <div class="col-md-4">
                                            <p class="">No Distributor found</p>
                                        </div>
                                @endforelse
                            </div>
                            <div class="col-xl-3 col-lg-2 col-12"></div>
                        </div>
                    </div>
                </div>


                <div class="card data-card">
                    <div class="card-header">
                        <h4 class="d-flex">Store Details
                            
                            
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">

                            <div class="col-6">
                                <div style="display: flex; align-items: center;">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="flexCheckDefault"
                                                onclick="headerCheckFunc()">
                                            <label class="form-check-label" for="flexCheckDefault"></label>
                                        </div>
                
                                        
                                        
                                </div>
                            </div>
                                <div class="col-6">
                                    @can('transfer stores')
                                    <a href="#transferModal" data-bs-toggle="modal" data-target=".bd-example-modal-lg" class="btn btn-danger">Transfer</a>
                                    @endcan
                                </div>
                            <div class="col-xl-6 col-lg-8 col-12">
                                
                                @forelse ($storeList as $item)
                                    
                                        <div class="col-md-4 mb-3">
                                            <div style="display: flex; align-items: center;">
                                                    <input name="status_check[]" class="tap-to-delete" type="checkbox" onclick="clickToRemove()"
                                                                    value="{{ $item->id }}" @php
                                                                        if (old('status_check')) {
                                                                            if (in_array($item->id, old('status_check'))) {
                                                                                echo 'checked';
                                                                            }
                                                                        }
                                                                    @endphp>
                                                      @can('view store')
                                                      <a href="{{ url('stores/'.$item->id) }}" style="margin-left: 10px;"><h5>{{$item->name}}({{$item->unique_code}})</h5></a>
                                                      @else
                                                        <h5>{{$item->name ??''}}({{$item->unique_code}})</h5>
                                                      @endcan
                                            </div>
                                        </div>
                                        
                                    
                                @empty
                                        <div class="col-md-4">
                                            <p class="">No Stores found</p>
                                        </div>
                                @endforelse
                            </div>
                            <div class="col-xl-3 col-lg-2 col-12"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
            <!-- modal-->


                                <!-- Modal -->
            <div class="modal fade bd-example-modal-lg" id="transferModal" tabindex="-1" aria-labelledby="transferModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="transferModalLabel">Transfer to another ASE & Distributor</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="{{ route('stores.transfer') }}" method="POST" id="transferForm">
                                @csrf
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <div class=" mb-3">
                                                <label for="aseUser">ASE *</label>
                                                <select class="form-select select2" style="height:200px" id="aseUser" name="aseUser[]" aria-label="Floating label select example" multiple>
                                                    <option value="" selected disabled>Select</option>
                                                    @foreach (DB::table('employees')->where('type', 4)->orderBy('name')->get() as $aseItem)
                                                        
                                                        <option value="{{ $aseItem->id }}">{{ $aseItem->name }}({{ $aseItem->state->name ??''}})</option>
                                                    @endforeach
                                                </select>
                                                
                                            </div>
                                            @error('aseUser') <p class="small text-danger">{{ $message }}</p> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="distributorUser">Distributor *</label>
                                            <div class=" mb-3">
                                                <select class="form-select select2" style="height:200px" id="distributorUser" name="distributorUser[]" aria-label="Floating label select example" multiple>
                                                    <option value="" selected disabled>Select</option>
                                                    @foreach ($distributorList as $distributorItem)
                                                        <option value="{{ $distributorItem->id }}">{{ $distributorItem->name }}({{ $distributorItem->states->name }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @error('distributorUser') <p class="small text-danger">{{ $message }}</p> @enderror
                                        </div>
                                    </div>
                                </div>
                                <div id="hiddenInputsContainer"></div>
                                <div class="col-12 mt-3">
                                    <button type="submit" class="btn btn-sm btn-danger">Add</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
             <div class="modal fade" id="newRangeModal" tabindex="-1" aria-labelledby="newRangeModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="newRangeModalLabel">Add new Area</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="{{route('employee.area.store')}}" method="POST">@csrf
                                
                                <input type="hidden" name="user_id" value="{{$data->id}}">
                                <div class="row">
                                    <div class="mb-3">
                                        <label for="state">State <span class="text-danger">*</span></label>
                                        <select class="form-select select2" id="state" name="state" aria-label="Floating label select example">
                                            <option value="" selected disabled>--Select State--</option>
                                            @foreach ($state as $index => $item)
                                                <option value="{{ $item->id }}" >{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('state') <p class="small text-danger">{{$message}}</p> @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="area">City/ Area <span class="text-danger">*</span></label>
                                        <select class="form-select select2" id="area" name="city" aria-label="Floating label select example" disabled>
                                            <option value="">Select State first</option>
                                        </select>
                                    </div>
                                
            
                                    <div class="col-12 mt-3">
                                        <button type="submit" class="btn btn-sm btn-danger">Add Area</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        {{-- <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary">Save changes</button>
                        </div> --}}
                    </div>
                </div>
            </div>
            
@endsection


@section('script')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- printThis Plugin -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/printThis/1.15.0/printThis.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        <script>
            $(document).ready(function() {
                $('.select2').select2();
            });
        </script>
<script>
    $('select[name="state"]').on('change', (event) => {
        var stateId = $(event.target).val();
        var selectedCityId = "{{ $data->city ?? '' }}"; // from backend

        $.ajax({
            url: '{{url("/")}}/areas/state/wise/' + stateId,
            method: 'GET',
            success: function(result) {
                var content = '';
                var slectTag = 'select[name="city"]';
                var displayCollection = "All";

                content += '<option value="" disabled>--Select City--</option>';
                $.each(result.data.area, (key, value) => {
                    let selected = (value.area_id == selectedCityId) ? 'selected' : '';
                    content += '<option value="' + value.area_id + '" ' + selected + '>' + value.area + '</option>';
                });
                $(slectTag).html(content).attr('disabled', false);
            }
        });
    });

    // 🔹 Trigger change once to pre-fill cities in edit form
    $('select[name="state"]').trigger('change');
</script>
<script>
document.getElementById('transferForm').addEventListener('submit', function(event) {
    const checkboxes = document.querySelectorAll('input[name="status_check[]"]:checked');
    const hiddenInputsContainer = document.getElementById('hiddenInputsContainer');

    hiddenInputsContainer.innerHTML = ''; // Clear any previous inputs

    checkboxes.forEach(function(checkbox) {
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'status_check[]';
        hiddenInput.value = checkbox.value;
        hiddenInputsContainer.appendChild(hiddenInput);
    });
});
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll('.delete-confirm').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault(); // stop normal link

            let url = this.getAttribute('href');

            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url; // redirect if confirmed
                }
            });
        });
    });
});
</script>
@endsection