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
                        <h4 class="d-flex">Employee Detail
                            <a href="{{ url('employees') }}" class="btn btn-cta ms-auto">Back</a>
                            @can('update employee')
                                <a href="{{ url('employees/'.$data->id.'/edit') }}" class="btn btn-cta">
                                    Edit
                                </a>
                            @endcan
                            @if($data->type==4)
                            @can('add employee area')
                            <a href="#newRangeModal" data-bs-toggle="modal" class="btn btn-danger">Add area</a>
                            @endcan
                            @endif
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
                                         $assignedPermissions = DB::table('user_permission_categories')
                                            ->select('user_permission_categories.*')
                                            ->join('employees','employees.id','=','user_permission_categories.employee_id')
                                            ->where('user_permission_categories.employee_id', $data->id)
                                            ->get();

                                            $brandMap = [
                                                1 => 'ONN',
                                                2 => 'PYNK',
                                                3 => 'Both',
                                            ];

                                            $brandPermissions = $assignedPermissions->pluck('brand')
                                                ->map(function ($brand) use ($brandMap) {
                                                    return $brandMap[$brand] ?? $brand; // fallback if unknown
                                                })
                                                ->unique() // avoid duplicates
                                                ->implode(', '); // comma separated string
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
            </div>
        </div>
    </div>
            <!-- modal-->
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
    <!-- printThis Plugin -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/printThis/1.15.0/printThis.min.js"></script>
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