@extends('layouts.app')

@section('content')
@php
$distributorTeam=\App\Models\Team::select('id','vp_id','rsm_id','asm_id','ase_id','distributor_id','state_id','area_id')->where('distributor_id',$data->id)->where('store_id',NULL)->where('status',1)->where('is_deleted',0)->paginate(50);

@endphp

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
                        <h4 class="d-flex">Edit Distributor
                            <a href="{{ url('distributors') }}" class="btn btn-cta ms-auto">Back</a>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-3 col-lg-2 col-12"></div>
                            <div class="col-xl-6 col-lg-8 col-12">
                                <form action="{{ url('distributors/'.$data->id) }}" method="POST" class="data-form">
                                    @csrf
                                    @method('PUT')
        
                                    <h4 class="page__subtitle">Add New</h4>
                        
                                    <div class="mb-3">
                                        <label for="employee_id">Distributor Code<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="code" name="code" placeholder="" value="{{ old('code',$data->code)  }}">
                                        @error('code') <p class="small text-danger">{{$message}}</p> @enderror
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="label-control">Full Name <span class="text-danger">*</span> </label>
                                        <input type="text" name="name" placeholder="" class="form-control" value=" {{old('name',$data->name)}}">
                                        @error('name') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label class="label-control">Official Email  </label>
                                        <input type="email" name="email" placeholder="" class="form-control" value="{{old('email',$data->email)}}">
                                        @error('email') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="label-control">Contact <span class="text-danger">*</span> </label>
                                        <input type="number" name="contact" placeholder="" class="form-control" value="{{old('contact',$data->contact)}}">
                                        @error('contact') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label class="label-control">WhatsApp Number </label>
                                        <input type="number" name="whatsapp" placeholder="" class="form-control" value="{{old('whatsapp',$data->whatsapp)}}">
                                        @error('whatsapp') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="state">State <span class="text-danger">*</span></label>
                                        <select class="form-select select2" id="state" name="state" aria-label="Floating label select example">
                                            <option value="" selected >Select</option>
                                            @foreach ($state as $index => $item)
                                                <option value="{{ $item->id }}" {{($data->state_id==$item->id)? 'selected' : '' }}>{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('state') <p class="small text-danger">{{$message}}</p> @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="area">City/ Area</label>
                                        <select class="form-select select2" id="area" name="area" aria-label="Floating label select example" disabled>
                                            <option value="">Select State first</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="label-control">Date of Joining </label>
                                        <input type="date" name="date_of_joining" placeholder="" class="form-control" value="{{old('date_of_joining',$data->date_of_joining)}}">
                                        @error('date_of_joining') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label class="label-control">Password <span class="text-danger">*</span> </label>
                                        <input type="password" name="password" placeholder="" class="form-control" value="{{old('password',$data->password)}}">
                                        @error('password') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
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
                                                    {{ $data->brand == 1 ? 'checked' : '' }}
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
                                                     {{ $data->brand == 2 ? 'checked' : '' }}
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
                                                     {{ $data->brand == 3 ? 'checked' : '' }}
                                                    onchange="checkOnlyOne(this)"
                                                >
                                               
                                                <label class="form-check-label" for="mediumCave">Both</label>
                                            </div>
                                        </div>
                                    
                                    <div class="text-end mb-3">
                                        <button type="submit" class="btn btn-submit">Save</button>
                                    </div>
                                </form>
                                
                            </div>
                            <div class="col-xl-3 col-lg-2 col-12"></div>
                        </div>
                    </div>
                </div>
                <div class="card data-card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">Team Details</h5>
            <p class="small text-muted m-0">Add | edit | delete team hierarchy from here</p>
        </div>
        <a href="#newRangeModal" data-bs-toggle="modal" class="btn btn-sm btn-success">
            Add New Record
        </a>
    </div>

    <div class="card-body">
        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
            <table class="table table-bordered table-striped table-sm align-middle text-center">
                <thead class="table-light sticky-top" style="top: 0; z-index: 2;">
                    <tr>
                        <th>#SR</th>
                        <th>State</th>
                        <th>Area</th>
                        <th>VP</th>
                        <th>RSM</th>
                        <th>ASM</th>
                        <th>ASE</th>
                        <th>Distributor</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($distributorTeam as $index => $row)
                        <tr>
                            <td>{{ $index + $distributorTeam->firstItem() }}</td>
                            <td>{{ $row->states->name ?? '' }}</td>
                            <td>{{ $row->areas->name ?? '' }}</td>
                            <td>{{ $row->vp->name ?? '' }}</td>
                            <td>{{ $row->rsm->name ?? '' }}</td>
                            <td>{{ $row->asm->name ?? '' }}</td>
                            <td>{{ $row->ase->name ?? '' }}</td>
                            <td>{{ $row->distributor->name ?? '' }}</td>
                            <td class="text-nowrap">
                                <a href="#exampleModal_{{ $row->id }}" data-bs-toggle="modal" class="btn btn-sm btn-success">
                                    <iconify-icon icon="tabler:edit"></iconify-icon>
                                </a>
                                <a href="{{ route('team.delete', $row->id) }}" class="btn btn-sm btn-danger delete-confirm">
                                    <iconify-icon icon="material-symbols:delete"></iconify-icon>
                                </a>
                            </td>
                        </tr>

                        {{-- Edit Modal --}}
                        <div class="modal fade" id="exampleModal_{{ $row->id }}" aria-labelledby="newRangeModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Update Team</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="{{ route('team.update', $row->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="distributor_id" value="{{ $data->id }}">
                                            
                                            <div class="row g-3">
                                                {{-- VP --}}
                                                <div class="col-md-6">
                                                    <label class="small text-muted">Brand</label>
                                                    
                                                    <select class="form-select form-select-sm" aria-label="Default select example" name="brand" id="brand">
                                                        <option value="" selected disabled>Select</option>
                                                                <option value="3" {{ ($row->brand == 3) ? 'selected' : '' }}>All</option>
                                                        
                                                            <option value="1" {{ ($row->brand == 1) ? 'selected' : '' }}>ONN</option>
                                                            <option value="2" {{ ($row->brand == 2) ? 'selected' : '' }}>PYNK</option>
                                                            
                                                            
                                                    </select>
                                                   
                                                    @error('brand') <p class="small text-danger">{{$message}}</p> @enderror
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="vp" class="small text-muted">VP *</label>
                                                    <select class="form-select form-select-sm" name="nsm_id">
                                                        <option value=""  selected>Select</option>
                                                        @foreach ($data->allZSM as $item)
                                                            <option value="{{ $item->id }}" {{ $row->vp_id == $item->id ? 'selected' : '' }}>
                                                                {{ $item->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('vp_id') <p class="small text-danger">{{ $message }}</p> @enderror
                                                </div>

                                                {{-- RSM --}}
                                                <div class="col-md-6">
                                                    <label class="small text-muted">RSM *</label>
                                                    <select class="form-select form-select-sm" name="rsm_id">
                                                        <option value=""  selected>Select</option>
                                                        @foreach ($data->allRSM as $item)
                                                            <option value="{{ $item->id }}" {{ $row->rsm_id == $item->id ? 'selected' : '' }}>
                                                                {{ $item->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('rsm_id') <p class="small text-danger">{{ $message }}</p> @enderror
                                                </div>

                                                {{-- ASM --}}
                                                <div class="col-md-6">
                                                    <label class="small text-muted">ASM *</label>
                                                    <select class="form-select form-select-sm" name="asm_id">
                                                        <option value=""  selected>Select</option>
                                                        @foreach ($data->allASM as $item)
                                                            <option value="{{ $item->id }}" {{ $row->asm_id == $item->id ? 'selected' : '' }}>
                                                                {{ $item->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('asm_id') <p class="small text-danger">{{ $message }}</p> @enderror
                                                </div>

                                                {{-- ASE --}}
                                                <div class="col-md-6">
                                                    <label class="small text-muted">ASE *</label>
                                                    <select class="form-select form-select-sm" name="ase_id">
                                                        <option value=""  selected>Select</option>
                                                        @foreach ($data->allASE as $item)
                                                            <option value="{{ $item->id }}" {{ $row->ase_id == $item->id ? 'selected' : '' }}>
                                                                {{ $item->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('ase_id') <p class="small text-danger">{{ $message }}</p> @enderror
                                                </div>

                                                {{-- State --}}
                                                <div class="col-md-6">
                                                    <label class="small text-muted">State *</label>
                                                    <select class="form-select form-select-sm" name="stateId" id="stateId_{{ $row->id }}">
                                                        <option value=""  selected>Select</option>
                                                        @foreach ($state as $item)
                                                            <option value="{{ $item->id }}" {{ $row->state_id == $item->id ? 'selected' : '' }}>
                                                                {{ $item->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('stateId') <p class="small text-danger">{{ $message }}</p> @enderror
                                                </div>

                                                {{-- Area --}}
                                                <div class="col-md-6">
                                                    <label class="small text-muted">City / Area *</label>
                                                    <select class="form-select form-select-sm" name="areaId" id="areaId_{{ $row->id }}" {{ empty($row->state_id) ? 'disabled' : '' }}>
                                                        @if(!empty($row->areas))
                                                            <option value="{{ $row->areas->id }}" selected>{{ $row->areas->name }}</option>
                                                        @else
                                                            <option value="">Select State first</option>
                                                        @endif
                                                    </select>
                                                    @error('areaId') <p class="small text-danger">{{ $message }}</p> @enderror
                                                </div>

                                                <div class="col-12 mt-3 text-end">
                                                    <button type="submit" class="btn btn-danger btn-sm">Update</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted">No records found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end mt-3">
            {{ $distributorTeam->appends($_GET)->links() }}
        </div>
    </div>
</div>

            </div>
        </div>
    </div>
@endsection

<div class="modal fade distributor-edit" id="newRangeModal"  aria-labelledby="newRangeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newRangeModalLabel">Add new team for Distributor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{route('team.add')}}" method="POST">@csrf
                    <input type="hidden" name="distributor_id" value="{{$data->id}}">
                    <div class="row">
                         <div class="col-md-6">
                            <div class="form-group">
                                <label class="small text-muted">Brand</label>
                                <div class="form-floating mb-3">
                                <select class="form-select form-select-sm" aria-label="Default select example" name="brand" id="brand">
                                    <option value="" selected disabled>Select</option>
                                            <option value="3" {{ (request()->input('brand') == 3) ? 'selected' : '' }}>All</option>
                                    
                                        <option value="1" {{ (request()->input('brand') == 1) ? 'selected' : '' }}>ONN</option>
                                        <option value="2" {{ (request()->input('brand') == 2) ? 'selected' : '' }}>PYNK</option>
                                        
                                        
                                </select>
                                </div>
                                @error('brand') <p class="small text-danger">{{$message}}</p> @enderror
                            </div>
                            </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="small text-muted" for="vp">VP *</label>
                                <div class="form-floating mb-3">
                                    <select class="form-select form-select-sm" id="zsm_data" name="vp_id" aria-label="Floating label select example">
                                        <option value="" selected>Select</option>
                                        @foreach ($data->allZSM as $item)
                                            <option value="{{$item->id}}">{{$item->name}}</option>
                                        @endforeach
                                    </select>
                                    
                                </div>
                                @error('vp_id') <p class="small text-danger">{{$message}}</p> @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="rsm">RSM *</label>
                                <div class="form-floating mb-3">
                                    <select class="form-select form-select-sm" id="rsm_data" name="rsm_id" aria-label="Floating label select example">
                                        <option value="" selected>Select</option>
                                        @foreach ($data->allRSM as $item)
                                            <option value="{{$item->id}}" >{{$item->name}}</option>
                                        @endforeach
                                    </select>
                                  
                                </div>
                                @error('rsm_id') <p class="small text-danger">{{$message}}</p> @enderror
                            </div>
                        </div>
                        
                        
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="asm">ASM *</label>
                                <div class="form-floating mb-3">
                                    <select class="form-select form-select-sm" id="asm_data" name="asm_id" aria-label="Floating label select example">
                                        <option value="" selected>Select</option>
                                        @foreach ($data->allASM as $item)
                                            <option value="{{$item->id}}" >{{$item->name}}</option>
                                        @endforeach
                                    </select>
                                   
                                </div>
                                @error('asm_id') <p class="small text-danger">{{$message}}</p> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="asm">ASE *</label>
                                <div class="form-floating mb-3">
                                    <select class="form-select form-select-sm" id="ase_data" name="ase_id" aria-label="Floating label select example">
                                        <option value="" selected>Select</option>
                                        @foreach ($data->allASE as $item)
                                            <option value="{{$item->id}}">{{$item->name}}</option>
                                        @endforeach
                                    </select>
                                    
                                </div>
                                @error('ase_id') <p class="small text-danger">{{$message}}</p> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="state">State *</label>
                                <div class="form-floating mb-3">
                                    <select class="form-select form-select-sm" id="state_data" name="stateId" aria-label="Floating label select example">
                                        <option value="" selected>Select</option>
                                        @foreach ($state as $index => $item)
                                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                  
                                </div>
                                @error('stateId') <p class="small text-danger">{{$message}}</p> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="area">City/ Area *</label><span></span>
                                <div class="form-floating mb-3">
                                    <select class="form-select form-select-sm" id="area_data" name="areaId" aria-label="Floating label select example" disabled>
                                        <option value="">Select State first</option>
                                    </select>
                                   
                                </div>
                                @error('areaId') <p class="small text-danger">{{$message}}</p> @enderror
                            </div>
                        </div>

                        <div class="col-12 mt-3">
                                <button type="submit" class="btn btn-danger btn-sm">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@section('script')

<script>
    $(document).ready(function() {
    var selectedState = "{{ request()->input('state') ?? $data->state_id }}";
    var selectedArea = "{{ request()->input('area') ?? $data->area_id }}";

    // Trigger change if state is already selected (for edit/filter persistence)
    if (selectedState) {
        loadAreas(selectedState, selectedArea);
    }

    // When state changes manually
    $('#state').on('change', function() {
        var stateId = $(this).val();
        loadAreas(stateId, selectedArea);
    });

    // Function to load areas
    function loadAreas(stateId, selectedArea = '') {
        if (!stateId) return;

        $.ajax({
            url: '{{ url("/") }}/employees/state/' + stateId,
            method: 'GET',
            success: function(result) {
                var content = '<option value="" disabled>Select</option>';
                $.each(result.data.area, function(key, val) {
                    var selected = (val.area_id == selectedArea) ? 'selected' : '';
                    content += '<option value="' + val.area_id + '" ' + selected + '>' + val.area + '</option>';
                });

                $('#area').html(content).prop('disabled', false);
            }
        });
    }
});
</script>

<script>
   $('select[name="stateId"]').on('change', (event) => {
        var value = $('select[name="stateId"]').val();
      
        $.ajax({
             url: '{{ url("/") }}/employees/state/' + value,
            method: 'GET',
            success: function(result) {
                var content = '';
                var slectTag = 'select[name="areaId"]';
                var displayCollection =  "All";

                content += '<option value="" selected>'+displayCollection+'</option>';
                $.each(result.data.area, (key, value) => {
                    content += '<option value="'+value.area_id+'">'+value.area+'</option>';
                });
                $(slectTag).html(content).attr('disabled', false);
            }
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

