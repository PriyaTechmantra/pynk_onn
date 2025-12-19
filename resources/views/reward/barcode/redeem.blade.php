@extends('layouts.app')
@section('content')

<style>
    .working_area {
        display: inline-flex;
        vertical-align: top;
        padding: 6px 12px;
        align-items: center;
        background: #f7f7f7;
        border-radius: 6px;
        text-decoration: none;
        color: #000;
    }
    .working_area svg {
        margin-right: 10px;
    }
.select2-container {
    width: 100% !important;
         z-index: 9999 !important;
}
   .modal {
    overflow: hidden;
} 
    
</style>
@php
    $allASE=DB::table('employees')->where('type',4)->get();
@endphp
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
                        <h4>QRCODE REDEEM HISTORY
                            <br><br>
                            
                            @can('qrcode redeem history export')
                            <a href="{{ url('reward/qrcode/redeem/list/csv/export',['brand'=>$request->brand,'date_from'=>$request->date_from,'date_to'=>$request->date_to,'distributor'=>$request->distributor,'ase'=>$request->ase,'keyword'=>$request->keyword]) }}" class="btn btn-sm btn-cta ms-auto" data-bs-toggle="tooltip" title="Export data in CSV">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                CSV
                            </a>
                            @endcan
                            @can('distributor assignment to specific QRcode')
                            <a href="#csvdistributorAssignmentModal" data-bs-toggle="modal" class="btn btn-sm btn-cta">Assign distributor to QR</a>
                            @endcan
                            @can('QRcode sequence history csv export')
                             <a href="{{ route('reward.retailer.barcode.sequence.csv.download') }}" type="submit" class="btn btn-sm btn-danger">QR Code sequence history CSV download</a>
                            @endcan
                            @can('QRcode distribution error log csv export')
                             <a href="{{ route('reward.retailer.barcode.error.log.report.csv.export') }}" type="submit" class="btn btn-sm btn-danger">Error Logs</a>
                            @endcan
                            @can('serial number mismatch report csv export')
                                <a href="{{ route('reward.qrcode.redeem.mismatch.csv.export', ['date_from'=>$request->date_from,'date_to'=>$request->date_to]) }}" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Export data in CSV">
                                                    Serial Number Mismatch Coupon Report
                                </a>
                            @endcan

                        </h4>

                        <div class="search__filter mb-0">
                            <div class="row">
                                <div class="col-12">
                                    <p class="text-muted mt-1 mb-0">Showing {{$data->count()}} out of {{$data->total()}} Entries</p>
                                </div>
                            </div>
                            <div class="row">
                                        
                                <div class="col-12">                        
                                    <form action="">
                                        <div class="row">
                                            @if($brandPermissions=='Both')
                                            <div class="col">
                                                <label class="small text-muted">Brand</label>
                                                <select class="form-select form-select-sm" aria-label="Default select example" name="brand" id="brand">
                                                    <option value="" selected disabled>Select</option>
                                                            <option value="3" {{ (request()->input('brand') == 3) ? 'selected' : '' }}>All</option>
                                                    
                                                        <option value="1" {{ (request()->input('brand') == 1) ? 'selected' : '' }}>ONN</option>
                                                        <option value="2" {{ (request()->input('brand') == 2) ? 'selected' : '' }}>PYNK</option>
                                                        
                                                        
                                                </select>
                                            </div>
                                            @endif
                                            <div class="col">
                                                <label for="date_from" class="small text-muted">Date from</label>
                                                <input type="date" name="date_from" id="date_from" class="form-control form-control-sm" aria-label="Default select example" value="{{request()->input('date_from') }}">
                                            </div>
                                            <div class="col">
                                                <label for="date_to" class="small text-muted">Date to</label>
                                                <input type="date" name="date_to" id="date_to" class="form-control form-control-sm" aria-label="Default select example" value="{{request()->input('date_to')  }}">
                                            </div>
                                            
                                            
                                                   
                                                    
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col">
                                                <label for="distributor" class="small text-muted">Distributor</label>
                                                <select class="form-select form-select-sm select2" id="distributor" name="distributor">
                                                    <option value="" selected disabled>Select</option>
                                                    @foreach ($allDistributors as $item)

                                                        @php
                                                

                                                                $brandMap = [
                                                                    1 => 'ONN',
                                                                    2 => 'PYNK',
                                                                    3 => 'Both',
                                                                ];

                                                                // Collect brand IDs from items (avoid duplicates)
                                                                $disbrands = [$item->brand];
                                                                
                                                                // Determine brand permissions
                                                                if (in_array(3, $disbrands)) {
                                                                    // If any brand is "Both"
                                                                    $disbrandPermissions = 'Both';
                                                                } elseif (in_array(1, $disbrands) && in_array(2, $disbrands)) {
                                                                    // If both ONN and PYNK exist
                                                                    $disbrandPermissions = 'Both';
                                                                } elseif (in_array(1, $disbrands)) {
                                                                    $disbrandPermissions = 'ONN';
                                                                } elseif (in_array(2, $disbrands)) {
                                                                    $disbrandPermissions = 'PYNK';
                                                                } else {
                                                                    // Fallback for unexpected values
                                                                    $disbrandPermissions = collect($disbrands)
                                                                        ->map(fn($b) => $brandMap[$b] ?? 'Unknown')
                                                                        ->implode(', ');
                                                                }

                                                            @endphp
                                                        <option value="{{$item->id}}" {{ (request()->input('distributor') == $item->id) ? 'selected' : '' }}>{{$item->name}} ({{$item->code}}) ({{$item->states->name}}) ({{$disbrandPermissions}})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col">
                                                <label for="ase" class="small text-muted">ASE</label>
                                                <select class="form-select form-select-sm select2" id="ase" name="ase">
                                                    <option value="" selected disabled>Select</option>
                                                    @foreach ($allASEs as $item)
                                                            @php
                                                

                                                                $brandMap = [
                                                                    1 => 'ONN',
                                                                    2 => 'PYNK',
                                                                    3 => 'Both',
                                                                ];

                                                                // Collect brand IDs from items (avoid duplicates)
                                                                $asebrands = [$item->brand];
                                                                
                                                                // Determine brand permissions
                                                                if (in_array(3, $asebrands)) {
                                                                    // If any brand is "Both"
                                                                    $asebrandPermissions = 'Both';
                                                                } elseif (in_array(1, $asebrands) && in_array(2, $asebrands)) {
                                                                    // If both ONN and PYNK exist
                                                                    $asebrandPermissions = 'Both';
                                                                } elseif (in_array(1, $asebrands)) {
                                                                    $asebrandPermissions = 'ONN';
                                                                } elseif (in_array(2, $asebrands)) {
                                                                    $asebrandPermissions = 'PYNK';
                                                                } else {
                                                                    // Fallback for unexpected values
                                                                    $asebrandPermissions = collect($asebrands)
                                                                        ->map(fn($b) => $brandMap[$b] ?? 'Unknown')
                                                                        ->implode(', ');
                                                                }

                                                            @endphp
                                                        <option value="{{$item->id}}" {{ (request()->input('ase') == $item->id) ? 'selected' : '' }}>{{$item->name}} ({{$item->employee_id}}) ({{$item->stateDetail->name}}) ({{$asebrandPermissions}})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col">
                                                            <label for="ase" class="small text-muted">Keyword</label>
                                                        <input type="search" name="keyword" id="keyword" class="form-control form-control-sm" placeholder="Search by store name/ contact/QR details" value="{{request()->input('keyword')}}" autocomplete="off">
                                                    
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
                                                        <!--</div>-->
                                                    </div>
                                                </div>
                                            </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table" id="example5">
                                <thead>
                                    <tr>
                                        <th>#SR</th> 
                                        <th>QR Details</th>
                                        <th>Code</th> 
                                        <th>Serial Number</th> 
                                        <th>Distributor </th>
                                        <th>ASE </th>
                                        <th>Store Name</th>
                                        <th>Contact</th>
                                        <th>State/Area</th>
                                        <th>Address</th>
                                        <th>Assign Distributor</th>
                                        <th>Date</th>
                                        <th>Points</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($data as $index => $item)
                                        <tr>
                                            <td>{{ $index + $data->firstItem() }}</td>

                                            <td>
                                                {{ $item->name }}
                                                @php
                                                

                                                    $brandMap = [
                                                        1 => 'ONN',
                                                        2 => 'PYNK',
                                                        3 => 'Both',
                                                    ];

                                                    // Collect brand IDs from items (avoid duplicates)
                                                    $brands = [$item->brand];
                                                    
                                                    // Determine brand permissions
                                                    if (in_array(3, $brands)) {
                                                        // If any brand is "Both"
                                                        $brandPermissions = 'Both';
                                                    } elseif (in_array(1, $brands) && in_array(2, $brands)) {
                                                        // If both ONN and PYNK exist
                                                        $brandPermissions = 'Both';
                                                    } elseif (in_array(1, $brands)) {
                                                        $brandPermissions = 'ONN';
                                                    } elseif (in_array(2, $brands)) {
                                                        $brandPermissions = 'PYNK';
                                                    } else {
                                                        // Fallback for unexpected values
                                                        $brandPermissions = collect($brands)
                                                            ->map(fn($b) => $brandMap[$b] ?? 'Unknown')
                                                            ->implode(', ');
                                                    }

                                                @endphp

                                                ({{ $brandPermissions ?? '' }})
                                            </td>

                                            <td>{{ $item->code }}</td>

                                            <td>{{ $item->serial_number }}</td>

                                            <td>
                                                @php
                                                $did = $item->distributor_id;
                                                $dis_name = \App\Models\Distributor::select('name')->where('id', $did)->first();
                                                @endphp
                                                {{ $dis_name->name ?? '' }}
                                            </td>

                                            <td>
                                                @php
                                                $aseid = $item->ase_id;
                                                $ase_name = \App\Models\Employee::select('name')->where('id', $aseid)->first();
                                                @endphp
                                                {{ $ase_name->name ?? '' }}
                                            </td>

                                            <td>{{ ucwords($item->store_name) }}</td>

                                            <td>{{ $item->email }}<br>
                                                {{ $item->contact }}
                                            </td>
                                            @php
                                                $stid = $item->state_id;
                                                $st_name = \App\Models\State::select('name')->where('id', $stid)->first();
                                                $aid = $item->area_id;
                                                $ar_name = \App\Models\Area::select('name')->where('id', $aid)->first();
                                            @endphp

                                            <td>{{ $st_name->name }}, {{ $ar_name->name }}</td>

                                            <td>
                                                {{ ucwords($item->address) }}<br>
                                                {{ $ar_name->name }}<br>
                                                {{ $item->city }}<br>
                                                {{ $st_name->name }}
                                            </td>

                                            <td>
                                                {{-- distributor from sequence --}}
                                                @php
                                                    $qrSequences = DB::table('qr_sequences')->select('distributor_id', 'from', 'to')->get();

                                                    $matchedDistributorId = null;
                                                    foreach ($qrSequences as $qr) {
                                                        if ($item->serial_number >= $qr->from && $item->serial_number <= $qr->to) {
                                                            $matchedDistributorId = $qr->distributor_id;
                                                            break;
                                                        }
                                                    }
                                                    $distributorDetails = $matchedDistributorId
                                                        ? DB::table('distributors')->where('id',$matchedDistributorId)->first()
                                                        : null;
                                                @endphp

                                                @if ($distributorDetails)

                                                    {{ $distributorDetails->name }} <br>

                                                    @php
                                                        $stateName = DB::table('states')
                                                            ->where('id', $distributorDetails->state_id)
                                                            ->value('name');
                                                    @endphp

                                                    {{ $stateName ?? 'N/A' }} <br>

                                                    {{ $distributorDetails->city }}

                                                @else
                                                    N/A
                                                @endif
                                            </td>

                                            <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y g:i:s A') }}</td>

                                            <td>{{ $item->amount }}</td>

                                        </tr>

                                    @empty
                                    <tr>
                                        <td colspan="100%" class="small text-muted text-center">No data found</td>
                                    </tr>
                                @endforelse
                                </tbody>

                            </table>
                            <div class="d-flex justify-content-end">
                                {{ $data->appends($_GET)->links() }}
                            </div>
                            {{-- bulk upload variation modal --}}
                            <div class="modal fade" id="bulkTransferASEModal" data-bs-backdrop="static">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            Bulk Upload
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form method="post" action="{{ route('reward.qrcode.redeem.csv.upload') }}" enctype="multipart/form-data" id="borrowerCsvUpload">@csrf
                                                <input type="file" name="file" class="form-control" accept=".csv">
                                                <br>
                                                <a href="{{ asset('admin/user.csv') }}">Download Sample CSV</a>
                                                <br>
                                                <button type="submit" class="btn btn-danger mt-3" id="csvImportBtn">Import <i class="fas fa-upload"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="modal fade" id="bulkTransferASEModal" data-backdrop="static">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            Bulk Upload
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>

                                        <div class="modal-body">

                                            <!-- FIRST FORM (SAVE SEQUENCE) -->
                                            <form method="post" action="{{ route('reward.retailer.barcode.sequence.save') }}" enctype="multipart/form-data" id="sequenceSaveForm">
                                                @csrf

                                                <div class="col-auto">
                                                    <label for="distributor" class="small text-muted">Distributor</label>
                                                    <select class="form-control form-control-sm" id="dis" name="distributor_id" required>
                                                        <option value="" disabled selected>Select</option>
                                                        @foreach ($allDistributors as $item)
                                                            <option value="{{ $item->id }}">
                                                                {{ $item->name }} ({{ $item->user_id }}) ({{ $item->states->name ?? 'N/A' }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-auto">
                                                    <label class="text-muted small">Sequence From</label>
                                                    <input type="number" name="from" class="form-control form-control-sm" required>
                                                </div>

                                                <div class="col-auto">
                                                    <label class="text-muted small">Sequence To</label>
                                                    <input type="number" name="to" class="form-control form-control-sm" required>
                                                </div>

                                                <div class="col-auto">
                                                    <label class="text-muted small">Coupon Count</label>
                                                    <input type="number" name="count" class="form-control form-control-sm" required>
                                                </div>

                                                <div class="col-auto">
                                                    <label class="text-muted small">Actual Date</label>
                                                    <input type="date" name="actual_date" class="form-control form-control-sm">
                                                </div>

                                                <button type="submit" class="btn btn-danger mt-3">Save</button>
                                            </form>


                                            <h1>Or</h1>

                                            <!-- SECOND FORM (CSV IMPORT) -->
                                            <form method="post" action="{{ route('reward.retailer.barcode.sequence.csv.upload') }}" enctype="multipart/form-data" id="sequenceCsvForm">
                                                @csrf

                                                <input type="file" name="file" class="form-control" accept=".csv" required>
                                                <br>

                                                <a href="{{ asset('admin/sequenceqr.csv') }}">Download Sample CSV</a>
                                                <br>

                                                <button type="submit" class="btn btn-danger mt-3">Import</button>
                                            </form>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
</div>

@endsection

@section('script')

	
	<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    
 <script>
    function stateWiseArea(value) {
        $.ajax({
            url: '{{url("/")}}/state-wise-area/'+value,
            method: 'GET',
            success: function(result) {
                var content = '';
                var slectTag = 'select[name="area"]';
                var displayCollection = (result.data.state == "all") ? "All Area" : "All "+" area";
                content += '<option value="" selected>'+displayCollection+'</option>';
                
                let cat = "{{ app('request')->input('area') }}";

                $.each(result.data.area, (key, value) => {
                    if(value.area == '') return;
                    if (value.area == cat) {
                        content += '<option value="'+value.area+'" selected>'+value.area+'</option>';
                    } else {
                        content += '<option value="'+value.area+'">'+value.area+'</option>';
                    }
                    //content += '<option value="'+value.area+'">'+value.area+'</option>';
                });
                $(slectTag).html(content).attr('disabled', false);
            }
        });
    }

    $('select[name="state"]').on('change', (event) => {
        var value = $('select[name="state"]').val();
        stateWiseArea(value);
    });

    @if(request()->input('state'))
        stateWiseArea("{{ request()->input('state') }}");
    @endif
</script>
@endsection