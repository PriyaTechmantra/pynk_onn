@extends('layouts.app')
<style>
.nav-tabs .nav-link.active {
    background-color: #ff007f; /* pink for PYNK, optional */
    color: #fff;
}
</style>
@section('content')


<div class="container mt-2">
        <div class="row">
            <div class="col-md-12">

                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
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

                
                
              
                <div class="card data-card mt-3">
                    <div class="card-header">
                        <h4 class="d-flex">
                            Attendance
                           
                            
                            
                            
                        </h4>
                                <div class="search__filter mb-0">
                                    <div class="row">
                                        <div class="col-12">
                                            <p class="text-muted mt-1 mb-0">Showing  Entries</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        
                                        <div class="col-12">
                                            <form action="">
                                                <div class="row">
                                                    @if($brandPermissions=='Both')
                                                    <div class="col">
                                                        <label class="small text-muted">Brand</label>
                                                        <select class="form-select form-select-sm" aria-label="Default select example" name="brand_id" id="brand_id">
                                                            <option value="" selected disabled>Select</option>
                                                                 <option value="3" {{ (request()->input('brand_id') == 3) ? 'selected' : '' }}>All</option>
                                                            
                                                                <option value="1" {{ (request()->input('brand_id') == 1) ? 'selected' : '' }}>ONN</option>
                                                                <option value="2" {{ (request()->input('brand_id') == 2) ? 'selected' : '' }}>PYNK</option>
                                                                
                                                                
                                                        </select>
                                                    </div>
                                                     @endif
                                                     <div class="col">
                                                        <label class="small text-muted">VP</label>
                                                        <select class="form-control form-control-sm select2" name="vp_id">
                                                       
                                                        <option value="{{ $request->vp_id }}">Select brand first</option>
                                                        </select>
                                                    </div>
                                                    <div class="col">
                                                        <label class="small text-muted">State</label>
                                                        <select class="form-control form-control-sm select2" name="state_id">
                                                        
                                                        <option value="{{ $request->state_id }}">Select vp first</option>
                                                        </select>
                                                    </div>
                                                    <div class="col">
                                                        <label class="small text-muted">RSM</label>
                                                        <select class="form-control form-control-sm select2" name="rsm_id" disabled>
                                                        <option value="{{ $request->rsm_id }}">Select state first</option>
                                                        </select>
                                                    </div>
                                                    <div class="col">
                                                        <label class="small text-muted">ASM</label>
                                                        <select class="form-control form-control-sm select2" name="asm_id" disabled>
                                                        <option value="{{ $request->asm_id }}">Select rsm first</option>
                                                        </select>
                                                    </div>
                                                    <div class="col">
                                                        <label class="small text-muted">ASE</label>
                                                        <select class="form-control form-control-sm select2" name="ase_id" disabled>
                                                        <option value="{{ $request->ase_id }}">Select asm first</option>
                                                        </select>
                                                    </div>
                                              
                                                    
                                                    <div class="col">
                                                        <label class="small text-muted">Month</label>
                                                        <input type="month" name="month" id="month" class="form-control form-control-sm" aria-label="Default select example" value="{{$month}}">
                                                    </div>
                                                </div>
                                                <div class="row mt-2">
                                                    <div class="col-12 text-end">
                                                        <!--<div class="btn-group books_btn_group">-->
                                                            <button type="submit" class="btn btn-sm btn-cta">
                                                                Filter
                                                            </button>
                            
                                                            <a href="{{ url()->current() }}" class="btn btn-sm btn-cta" data-bs-toggle="tooltip" title="Clear Filter">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                                            </a>
                                                               @can('attendance report export')
                            
                                                                <a href="javascript: void(0)" class="btn btn-sm btn-cta" data-bs-toggle="tooltip" title="Export data in CSV" onclick="ajaxExcelExport()" id="csvEXP">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg> EXPORT
                                                                </a>
                                                                @endcan
                                                        
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        
                                    </div>
                                </div>
                    </div>
                    <div class="card-body">
                        @if (!empty($data)) 
                        @php
                            $my_month =  explode("-",$month);
                            $year_val = $my_month[0];
                            $month_val = $my_month[1];
                            $dates_month=dates_month($month_val,$year_val);
                            $month_names = $dates_month['month_names'];
                            $date_values = $dates_month['date_values'];
                            $totaldays=count($dates_month['date_values']);
                        @endphp
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                            <th class="sl_no index-col">#</th>
                                            <th>Brand Permission</th>
                                            <th>Name</th>
                                            <th>Designation</th>
                                            <th>Mobile</th> 
                                            <th style="min-width: 200px">Manager</th>
                                            <th>Status</th>
                                            {{-- <th>Total Days</th> --}}
                                            @foreach ($month_names as $months)
                                                <th>{{$months}}</th>
                                            @endforeach
                                        
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($data as $index => $item)
                                            @php
                                                $findTeamDetails = findManagerDetails($item->id, $item->type);
                                                
                                            @endphp
                                            <tr>
                                                 <td class="index-col">{{ $index + 1 }}</td>
                                                <td> 
                                                    @php
                                               

                                                            $brandMap = [
                                                                1 => 'ONN',
                                                                2 => 'PYNK',
                                                                3 => 'Both',
                                                            ];

                                                            $brands = [$item->brand];

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

                                                    {{ $brandPermissions ?? '' }}
                                                </td> 
                                                
                                                <td>
                                                    <p class="">{{ $item->name ?? '' }}</p>
                                                    <p class="small">{{$item->employee_id}}</p>
                                                </td>
                                                <td>
                                                {{ $item->designation ? $item->designation : userTypeName($item->type) }}
                                                </td>
                                                <td>{{$item->mobile}} {{$item->whatsapp_no}}</td>
                                                <td>
                                                    <p style="text-transform: uppercase;">{!! findManagerDetails($item->id, $item->type) !!}</p>
                                                </td>
                                                    
                                                <td>
                                                        
                                                            <span class="badge badge-status bg-{{ $item->status == 1 ? 'success' : 'danger' }}">{{ $item->status == 1 ? 'Active' : 'Inactive' }}</span>
                                                        
                                                       
                                                </td>
                                                {{-- <td> {{$totaldays}} </td> --}}

                                                {{-- {{dd($date_values)}} --}}

                                                @foreach ($date_values as $date)
                                                    @php
                                                        $dates_attendance=dates_attendance($item->id, $date);
                                                        
                                                    @endphp
                                                    @if($dates_attendance[0][0]['date_wise_attendance'][0]['is_present']=='A')
                                                        <td class="redColor" style="background-color: red;color: #fff;padding: 10px;text-align: center;border: 1px solid #fff; vertical-align: middle;">
                                                            {{$dates_attendance[0][0]['date_wise_attendance'][0]['is_present']}}
                                                        </td>

                                                    @elseif($dates_attendance[0][0]['date_wise_attendance'][0]['is_present']=='P')
                                                        <td class="redColor" style="background-color: rgb(1, 134, 52); color:#fff;padding: 10px;text-align: center;border: 1px solid #fff; vertical-align: middle;">
                                                            {{$dates_attendance[0][0]['date_wise_attendance'][0]['is_present']}}
                                                        </td>

                                                    @elseif($dates_attendance[0][0]['date_wise_attendance'][0]['is_present']=='W')
                                                        <td class="redColor"  style="background-color: rgb(241, 225, 0); color:#fff; padding: 10px;text-align: center;border: 1px solid #fff; vertical-align: middle;">
                                                            {{$dates_attendance[0][0]['date_wise_attendance'][0]['is_present']}}
                                                        </td>
                                                    @elseif($dates_attendance[0][0]['date_wise_attendance'][0]['is_present']=='L')
                                                        <td class="redColor"  style="background-color: #FFA500; color:#fff; padding: 10px;text-align: center;border: 1px solid #fff; vertical-align: middle;">
                                                            {{$dates_attendance[0][0]['date_wise_attendance'][0]['is_present']}}
                                                        </td>
                                                    @else
                                                        <td class="redColor"  style="background-color: #294fa1da; color:#fff; padding: 10px;text-align: center;border: 1px solid #fff; vertical-align: middle;">
                                                            {{$dates_attendance[0][0]['date_wise_attendance'][0]['is_present']}}
                                                        </td>
                                                    @endif
                                                @endforeach
                                            </tr>
                                        @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No record found</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                         {{ $data->appends($_GET)->render() }}
                        
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

                    
    <div class="modal action-modal fade" id="csvModal" data-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    Employee Details Bulk Upload
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="{{ url('employees/bulk/upload') }}" enctype="multipart/form-data">@csrf
                        <input type="file" name="file" class="form-control" accept=".csv">
                        <div class="cta-row">
                        <a href="{{ asset('backend/csv/sample-employee.csv') }}" class="btn-cta">Download Sample CSV</a>
                        <button type="submit" class="btn btn-cta" id="csvImportBtn">Import <i class="fas fa-upload"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection


@section('script')

<script>
    $('select[name="brand_id"]').on('change', (event) => {
        var value = $('select[name="brand_id"]').val();
        VPChange(value);
    });

    @if (request()->input('brand_id'))
        VPChange({{request()->input('brand_id')}})
    @endif
     function VPChange(value) {
        $.ajax({
            url: '{{url("/")}}/vp/brand/wise/'+value,
            method: 'GET',
            success: function(result) {
                var content = '';
                var slectTag = 'select[name="vp_id"]';
                var displayCollection =  "All";

                content += '<option value="" selected>'+displayCollection+'</option>';
                $.each(result.data, (key, value) => {
                    let selected = ``;
                    @if (request()->input('vp_id'))
                        if({{request()->input('vp_id')}} == value.id) {selected = 'selected';}
                    @endif
                    content += '<option value="'+value.id+'" ${selected}>'+value.name+'</option>';
                });
                $(slectTag).html(content).attr('disabled', false);
            }
        });
    }
     
</script>
<script>
    $('select[name="vp_id"]').on('change', (event) => {
        var value = $('select[name="vp_id"]').val();
      
        $.ajax({
            url: '{{url("/")}}/state/vp/wise/'+value,
            method: 'GET',
            success: function(result) {
                var content = '';
                var slectTag = 'select[name="state_id"]';
                var displayCollection =  "All";

                content += '<option value="" selected>'+displayCollection+'</option>';
                $.each(result.data, (key, value) => {
                    let selected = ``;
                    @if (request()->input('state_id'))
                        if({{request()->input('state_id')}} == value.states.id) {selected = 'selected';}
                    @endif
                    content += '<option value="'+value.states.id+'" ${selected}>'+value.states.name+'</option>';
                });
                $(slectTag).html(content).attr('disabled', false);
            }
        });
    });
</script>

<script>
    $('select[name="state_id"]').on('change', (event) => {
        var value = $('select[name="state_id"]').val();
      
        $.ajax({
            url: '{{url("/")}}/rsm/state/wise/'+value,
            method: 'GET',
            success: function(result) {
                var content = '';
                var slectTag = 'select[name="rsm_id"]';
                var displayCollection =  "All";

                content += '<option value="" selected>'+displayCollection+'</option>';
                $.each(result.data, (key, value) => {
                    content += '<option value="'+value.rsm.id+'">'+value.rsm.name+'</option>';
                });
                $(slectTag).html(content).attr('disabled', false);
            }
        });
    });
</script>

<script>
    $('select[name="rsm_id"]').on('change', (event) => {
        var value = $('select[name="rsm_id"]').val();
      
        $.ajax({
            url: '{{url("/")}}/asm/rsm/wise/'+value,
            method: 'GET',
            success: function(result) {
                var content = '';
                var slectTag = 'select[name="asm_id"]';
                var displayCollection =  "All";

                content += '<option value="" selected>'+displayCollection+'</option>';
                $.each(result.data, (key, value) => {
                    content += '<option value="'+value.asm.id+'">'+value.asm.name+'</option>';
                });
                $(slectTag).html(content).attr('disabled', false);
            }
        });
    });
</script>

<script>
    $('select[name="asm_id"]').on('change', (event) => {
        var value = $('select[name="asm_id"]').val();
      
        $.ajax({
            url: '{{url("/")}}/ase/asm/wise/'+value,
            method: 'GET',
            success: function(result) {
                var content = '';
                var slectTag = 'select[name="ase_id"]';
                var displayCollection =  "All";

                content += '<option value="" selected>'+displayCollection+'</option>';
                $.each(result.data, (key, value) => {
                    content += '<option value="'+value.ase.id+'">'+value.ase.name+'</option>';
                });
                $(slectTag).html(content).attr('disabled', false);
            }
        });
    });
</script>
<script>

    $(document).on('click', '.brand-tab', function() {
    var brand = $(this).data('brand');
    
    // Highlight active tab
    $('.brand-tab').removeClass('active');
    $(this).addClass('active');

    // Fetch employees via AJAX
    $.ajax({
        url: '{{ url("employees") }}',
        method: 'GET',
        data: { brand: brand },
        success: function(response) {
            // replace table body
        },
        error: function() {
            alert('Failed to load employees.');
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
<script>
    $(function() {
        $('#btnExport').click(function() {
            console.log("hello");
            //$('#tblHead').css("display","block");
            var url = 'data:application/vnd.ms-excel,' + encodeURIComponent($('#tableWrap').html())
            location.href = url
            return false
            $('#tblHead').css("display", "none");
        });
    });

    function ajaxExcelExport() {
        $.ajax({
            url: '{{url("/")}}/attendance/report/csv/export',
            method: 'GET',
            data: {
                'vp_id': $('select[name="vp_id"]').val(),
                'rsm_id': $('select[name="rsm_id"]').val(),
                
                'asm_id': $('select[name="asm_id"]').val(),
                'ase_id': $('select[name="ase_id"]').val(),
                'month': $('input[name="month"]').val(),
                'checkbox': $('input[name="checkbox"]').val(),
            },
            beforeSend: function() {
                $('#csvEXP').html('Please wait').attr('disabled', true);
            },
            success: function(result) {
                if (result.status === 200) {
                    $('#expTab').html(result.data);


                    // var url = 'data:application/vnd.ms-excel,' + encodeURIComponent($('#expTab').html())
                    // location.href = url


                    var myBlob =  new Blob( [$('#expTab').html()] , {type:'application/vnd.ms-excel'});
                    var url = window.URL.createObjectURL(myBlob);
                    var a = document.createElement("a");
                    document.body.appendChild(a);
                    a.href = url;
                    a.download = "export.xls";
                    a.click();
                    //adding some delay in removing the dynamically created link solved the problem in FireFox
                    setTimeout(function() {window.URL.revokeObjectURL(url);},0);




                    $('#csvEXP').html('<iconify-icon icon="material-symbols:download"></iconify-icon> Downloading...').attr('disabled', false);
                    setTimeout(()=> {
                        $('#csvEXP').html('<iconify-icon icon="material-symbols:download"></iconify-icon> EXPORT').attr('disabled', false);
                    }, 1500);
                    return false
                }
                $('#csvEXP').html('<iconify-icon icon="material-symbols:download"></iconify-icon> EXPORT').attr('disabled', false);
            }
        });
    }
</script>
@endsection