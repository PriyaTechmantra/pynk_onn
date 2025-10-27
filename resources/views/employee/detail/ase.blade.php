@extends('layouts.app')
@section('page', '')

@section('content')
@php
    $brandMap = [1 => 'ONN', 2 => 'PYNK', 3 => 'Both'];
    $brandPermissions = $brandMap[$data->employee->brand] ?? 'Unknown';
     // Logged-in user permission (fetched from user_permission_categories table)
    $userPermission = \App\Models\UserPermissionCategory::where('user_id', auth()->id())
        ->value('brand'); // assuming column name is 'brand' in user_permission_categories

    $userBrandPermission = $brandMap[$userPermission] ?? 'Unknown';
@endphp

<style>
    .working_area {
        display: inline-flex;
        align-items: center;
        background: #f8f9fa;
        border-radius: 6px;
        padding: 6px 12px;
        margin-right: 6px;
        color: #000;
        text-decoration: none;
        font-size: 14px;
    }
    .working_area svg { margin-right: 6px; }
    .select2-container { width: 100% !important; z-index: 9999; }
</style>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-12">

            {{-- Error Messages --}}
            @if ($errors->any())
                <div class="alert alert-warning">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

        <div class="card data-card">
                    <div class="card-header">
                    
                     <h4 class="d-flex">ASE Detail
                        <a href="{{ url('employees') }}" class="btn btn-cta ms-auto">Back</a>
                        <a href="{{ url('employees/'.$data->employee->id.'/edit') }}" class="btn btn-cta">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <button class="btn btn-cta" data-bs-toggle="modal" data-bs-target="#newAreaModal">
                            <i class="bi bi-plus-circle"></i> Add Area
                        </button>
                    </h4>
                </div>
            <div class="card-body">

                {{-- HEADER --}}
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        
                        <span class="badge bg-primary fs-5">{{ $data->employee->designation }}</span>
                    </div>
                    <div>
                        
                    </div>
                </div>

                {{-- PRIMARY INFO --}}
                <h5 class="text-primary border-bottom pb-2">Primary Information</h5>
                <div class="row mb-4">
                    <div class="col-md-3"><strong>Brand:</strong> {{ $brandPermissions }}</div>
                    <div class="col-md-3"><strong>Name:</strong> {{ $data->employee->name }}</div>
                    <div class="col-md-3"><strong>Mobile:</strong> {{ $data->employee->mobile }}</div>
                    <div class="col-md-3"><strong>Official Email:</strong> {{ $data->employee->email }}</div>
                    <div class="col-md-3"><strong>Code:</strong> {{ $data->employee->employee_id }}</div>
                    <div class="col-md-3"><strong>Date of Joining:</strong> {{ date('d-m-Y', strtotime($data->employee->date_of_joining)) }}</div>
                   
                </div>

                {{-- LOCATION INFO --}}
                <h5 class="text-primary border-bottom pb-2">Location Information</h5>
                <div class="row mb-4">
                    <div class="col-md-3"><strong>State:</strong> {{ $data->employee->stateDetail->name ?? 'N/A' }}</div>
                    <div class="col-md-3"><strong>Area:</strong> {{ $data->employee->area->name ?? 'N/A' }}</div>
                    <div class="col-md-6">
                        <p class="small text-muted mb-1">Working Area List</p>
                        @foreach ($data->workAreaList as $item)
                            <a href="{{ route('employee.area.delete', $item->id) }}" 
                               class="working_area delete-confirm" title="Delete area">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" 
                                     stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <line x1="18" y1="6" x2="6" y2="18" />
                                    <line x1="6" y1="6" x2="18" y2="18" />
                                </svg>
                                {{ $item->area->name }}
                            </a>
                        @endforeach
                    </div>
                </div>

                {{-- TEAM INFO --}}
                <h5 class="text-primary border-bottom pb-2">Team Information</h5>
                <div class="row mb-4">
                    <div class="col-md-3"><strong>VP:</strong> {{ $data->team->vp->name ?? 'N/A' }}</div>
                    <div class="col-md-3"><strong>RSM:</strong> {{ $data->team->rsm->name ?? 'N/A' }}</div>
                    <div class="col-md-3"><strong>ASM:</strong> {{ $data->team->asm->name ?? 'N/A' }}</div>
                </div>

                {{-- CONTACT INFO --}}
                <h5 class="text-primary border-bottom pb-2">Optional Contact Information</h5>
                <div class="row mb-4">
                    <div class="col-md-3"><strong>Alt. Mobile 1:</strong> {{ $data->employee->alt_number1 ?? 'NA' }}</div>
                    <div class="col-md-3"><strong>Alt. Mobile 2:</strong> {{ $data->employee->alt_number2 ?? 'NA' }}</div>
                    <div class="col-md-3"><strong>Alt. Mobile 3:</strong> {{ $data->employee->alt_number3 ?? 'NA' }}</div>
                    <div class="col-md-3"><strong>Personal Email:</strong> {{ $data->employee->personal_mail ?? 'NA' }}</div>
                </div>

                {{-- OTHERS --}}
                <h5 class="text-primary border-bottom pb-2">Others</h5>
                <div class="row mb-4">
                    <div class="col-md-3"><strong>Created / Edited By:</strong> {{ $data->employee->createdBy->name ?? 'N/A' }}</div>
                    <div class="col-md-3"><strong>Created At:</strong> {{ date('d-m-Y', strtotime($data->employee->created_at)) }}</div>
                </div>

            </div>
        </div>

        {{-- DISTRIBUTOR DETAILS --}}
        <div class="card shadow-sm mb-4">
            
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="m-0">Distributor Details</h4>
                @if($brandPermissions == 'Both'  && $userBrandPermission === 'Both')
                    <select class="form-select form-select-sm w-auto" id="brand" name="brand">
                        {{--<option value="All">All</option>--}}
                        <option value="1">ONN</option>
                        <option value="2">PYNK</option>
                    </select>
                @endif
            </div>
            {{--<div class="card-body">
                @forelse ($data->distributorList as $item)
                    <p>
                        @can('view distributor')
                            <a href="{{ url('distributors/'.$item->id) }}" class="text-decoration-none">
                                {{ $item->distributor->name ?? '' }}({{$item->distributor->states->name}})
                            </a>
                        @else
                            {{ $item->distributor->name ?? '' }}({{$item->distributor->states->name}})
                        @endcan
                    </p>
                @empty
                    <p class="text-muted">No Distributor found</p>
                @endforelse
            </div>--}}
            <div class="card-body" id="distributorList">
                {{-- Default distributor list (shown first time when page loads) --}}
                @forelse ($data->distributorList as $item)
                    <p>
                        @can('view distributor')
                            <a href="{{ url('distributors/'.$item->id) }}" class="text-decoration-none">
                                {{ $item->distributor->name ?? '' }} ({{ $item->distributor->states->name ?? '' }})
                            </a>
                        @else
                            {{ $item->distributor->name ?? '' }} ({{ $item->distributor->states->name ?? '' }})
                        @endcan
                    </p>
                @empty
                    <p class="text-muted">No Distributor found</p>
                @endforelse
            </div>
           
        </div>

        {{-- STORE DETAILS --}}
        <div class="card shadow-sm mb-4">
            
            <div class="card-header d-flex justify-content-between align-items-center">
                
                <div style="display: flex; align-items: center;">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="flexCheckDefault"
                                onclick="headerCheckFunc()">
                            <label class="form-check-label" for="flexCheckDefault"></label>
                        </div>

                        <h4 class="m-0">Store Details</h4>
                        @if($brandPermissions == 'Both'  && $userBrandPermission === 'Both')
                            <select class="form-select form-select-sm w-auto" id="storebrand" name="storebrand">
                                {{--<option value="All">All</option>--}}
                                <option value="1">ONN</option>
                                <option value="2">PYNK</option>
                            </select>
                        @endif
                </div>
                           
                
                @can('transfer stores')
                    <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#transferModal">Transfer</button>
                @endcan
            </div>
            <div class="card-body" id="storeList">
                @forelse ($data->storeList as $item)
                    <div class="form-check mb-2">
                        <input name="status_check[]" class="tap-to-delete" type="checkbox" onclick="clickToRemove()"
                                                                    value="{{ $item->id }}" @php
                                                                        if (old('status_check')) {
                                                                            if (in_array($item->id, old('status_check'))) {
                                                                                echo 'checked';
                                                                            }
                                                                        }
                                                                    @endphp>
                        @can('view store')
                            <a href="{{ url('stores/'.$item->id) }}" class="ms-2 text-decoration-none">
                                {{ $item->name }} ({{ $item->unique_code }})
                            </a>
                        @else
                            <span class="ms-2">{{ $item->name }} ({{ $item->unique_code }})</span>
                        @endcan
                    </div>
                @empty
                    <p class="text-muted">No stores found</p>
                @endforelse
            </div>
        </div>
    </div>
</section>

{{-- MODALS --}}
<div class="modal fade" id="newAreaModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('employee.area.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Add New Area</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="employee_id" value="{{ $data->employee->id }}">
                    <div class="mb-3">
                        <label class="form-label">State</label>
                        <select name="state" class="form-select select2">
                            <option disabled selected>Select</option>
                            @foreach ($data->state as $st)
                                <option value="{{ $st->id }}">{{ $st->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Area</label>
                        <select name="city[]" class="form-select select2" multiple disabled>
                            <option>Select state first</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Add</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- TRANSFER MODAL --}}
<div class="modal fade" id="transferModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form id="transferForm" action="{{ route('stores.transfer') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Transfer Store</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Employee</label>
                        <select name="aseUser[]" id="aseUser" class="form-select select2">
                            <option disabled selected>Select ASE</option>
                           @foreach ($aseList as $aseItem)
                                <option value="{{ $aseItem->id }}">{{ $aseItem->name }}({{$aseItem->stateDetail->name}})</option>
                            @endforeach
                        </select>
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
                    <div id="hiddenInputsContainer"></div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Transfer</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(function() {
    $('.select2').select2();

    // Fetch areas dynamically
    $('select[name="state"]').on('change', function() {
        const stateId = $(this).val();
        $.get(`{{ url('/') }}/areas/state/wise/${stateId}`, function(result) {
            let options = '<option disabled selected>--Select City--</option>';
            $.each(result.data.area, (key, value) => {
                options += `<option value="${value.area_id}">${value.area}</option>`;
            });
            $('select[name="city[]"]').html(options).prop('disabled', false);
        });
    });

    // Pass checked stores to transfer modal
    $('#transferForm').on('submit', function() {
        $('#hiddenInputsContainer').empty();
        $('input[name="status_check[]"]:checked').each(function() {
            $('#hiddenInputsContainer').append(`<input type="hidden" name="status_check[]" value="${$(this).val()}">`);
        });
    });
});
</script>
<script>
    $(document).on('change', '#brand', function() {
        var brand = $(this).val();
        var url = "{{ url('employees') }}/{{ $id }}";

        $.ajax({
            url: url,
            type: 'GET',
            data: { brand: brand },
            success: function(response) {
                // Replace only the distributor list part from the response
                var newList = $(response).find('#distributorList').html();
                $('#distributorList').html(newList);
            },
            error: function() {
                alert('Something went wrong.');
            }
        });
    });

    $(document).on('change', '#storebrand', function() {
        var storebrand = $(this).val();
        var url = "{{ url('employees') }}/{{ $id }}";

        $.ajax({
            url: url,
            type: 'GET',
            data: { storebrand: storebrand },
            success: function(response) {
                // Replace only the distributor list part from the response
                var newList = $(response).find('#storeList').html();
                $('#storeList').html(newList);
            },
            error: function() {
                alert('Something went wrong.');
            }
        });
    });
</script>
@endsection
