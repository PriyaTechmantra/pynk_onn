@extends('layouts.app')
@section('page', '')

@section('content')
@php
    $brandMap = [1 => 'ONN', 2 => 'PYNK', 3 => 'Both'];
    $brandPermissions = $brandMap[$data->distributor->brand] ?? 'Unknown';
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
                    
                     <h4 class="d-flex">Distributor Detail
                        <a href="{{ url('distributors') }}" class="btn btn-cta ms-auto">Back</a>
                        @can('update distributor')
                        <a href="{{ url('distributors/'.$data->distributor->id.'/edit') }}" class="btn btn-cta">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        @endcan
                        @can('view distributor range')
                        <a href="{{ url('distributors/'.$data->distributor->id.'/range') }}" class="btn btn-cta">
                            <i class="bi bi-pencil"></i> Range
                        </a>
                        @endcan
                    </h4>
                </div>
            <div class="card-body">

                {{-- HEADER --}}
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        
                        <span class="badge bg-primary fs-5">Distributor</span>
                    </div>
                    <div>
                        
                    </div>
                </div>

                {{-- PRIMARY INFO --}}
                <h5 class="text-primary border-bottom pb-2">Primary Information</h5>
                <div class="row mb-4">
                    <div class="col-md-3"><strong>Brand:</strong> {{ $brandPermissions }}</div>
                    <div class="col-md-3"><strong>Name:</strong> {{ $data->distributor->name }}</div>
                    <div class="col-md-3"><strong>Mobile:</strong> {{ $data->distributor->contact }}</div>
                    <div class="col-md-3"><strong>Official Email:</strong> {{ $data->distributor->email }}</div>
                    <div class="col-md-3"><strong>Code:</strong> {{ $data->distributor->code }}</div>
                    <div class="col-md-3"><strong>Date of Joining:</strong> {{ date('d-m-Y', strtotime($data->distributor->date_of_joining)) ?? 'NA' }}</div>
                   
                </div>

                {{-- LOCATION INFO --}}
                <h5 class="text-primary border-bottom pb-2">Location Information</h5>
                <div class="row mb-4">
                    <div class="col-md-3"><strong>State:</strong> {{ $data->distributor->states->name ?? 'N/A' }}</div>
                    <div class="col-md-3"><strong>Area:</strong> {{ $data->distributor->areas->name ?? 'N/A' }}</div>
                    
                </div>

                {{-- TEAM INFO --}}
                <h5 class="text-primary border-bottom pb-2">Team Information</h5>
                <div class="row mb-4">
                    <div class="col-md-3"><strong>VP:</strong> {{ $data->team->vp->name ?? 'N/A' }}</div>
                    <div class="col-md-3"><strong>RSM:</strong> {{ $data->team->rsm->name ?? 'N/A' }}</div>
                    <div class="col-md-3"><strong>ASM:</strong> {{ $data->team->asm->name ?? 'N/A' }}</div>
                    <div class="col-md-3"><strong>ASE:</strong> {{ $data->team->ase->name ?? 'N/A' }}</div>
                </div>

                

                {{-- OTHERS --}}
                <h5 class="text-primary border-bottom pb-2">Others</h5>
                <div class="row mb-4">
                    <div class="col-md-3"><strong>Created / Edited By:</strong> {{ $data->distributor->createdBy->name ?? 'N/A' }}</div>
                    <div class="col-md-3"><strong>Created At:</strong> {{ date('d-m-Y', strtotime($data->distributor->created_at)) }}</div>
                </div>

            </div>
        </div>

        

        {{-- STORE DETAILS --}}
        <div class="card shadow-sm mb-4">
            
            <div class="card-header d-flex justify-content-between align-items-center">
                
                <div style="display: flex; align-items: center;">
                        

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
        var url = "{{ url('distributors') }}/{{ $id }}";

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
