@extends('layouts.app')

@section('page', 'Secondary Order report')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@php
$monthName = ucfirst(strtolower(request('month'))); // "April"
$year = request('year');                            // "2025"
$brand= request('brand');
$monthNumber = date('m', strtotime("1 $monthName $year")); // always returns correct month
$monthValue = $year . '-' . $monthNumber;

// Build the first day of the month
$date_from = "$year-$monthNumber-01";

// Use PHP's DateTime to get the last day of the month
$date = new DateTime($date_from);
$date_to = $date->format('Y-m-t');
@endphp
<section>
    <div class="card card-body">
        <div class="row">
            <div class="col-md-12">
                
                <div class="row g-3 align-items-end mb-4">
                        <div class="col-auto">
                            <label for="date_from" class="text-muted small">Month</label>
                            <input type="month" name="month_year" id="month_year" class="form-control form-control-sm" value="{{ request('month_year', date('Y-m')) }}">
                        </div>
                        
                        <div class="col-auto">
                            <a type="button" class="btn btn-danger btn-sm report-btn" href="#" data-report="state">State wise report</a>
                        </div>
                        <div class="col-auto">
                           <a type="button" class="btn btn-danger btn-sm report-btn" href="#" data-report="product">Product style wise report</a>
                        </div>

                        <div class="col-auto">
                            <a type="button" class="btn btn-danger btn-sm report-btn" href="#" data-report="ase">ASE wise report</a>
                        </div>
                        <div class="col-auto">
                            <a type="button" class="btn btn-danger btn-sm report-btn" href="#" data-report="asm">ASM wise report</a>
                        </div>
                        <div class="col-auto">
                            <a type="button" class="btn btn-danger btn-sm report-btn" href="#" data-report="rsm">RSM wise report</a>
                        </div>
                        <div class="col-auto">
                            <a type="button" class="btn btn-danger btn-sm report-btn" href="#" data-report="vp">VP wise report</a>
                        </div>
                        <div class="col-auto">
                            <a type="button" class="btn btn-danger btn-sm report-btn" href="#" data-report="distributor">Distributor wise report</a>
                        </div>
                        
                </div>
                
            </div>
        </div>
        
        

    </div>
    @if(!empty($reportType) && !empty($data))
        @if($reportType=='state')
            <div class="row">
                
                <div class="col-md-12">
                    
                    <div class="card card-body">
                        
                        <h5>{{ ucfirst($reportType) }} wise Total Order QTY Report</h5>
                        <div class="col-auto text-end">
                            <a href="javascript:void(0);" id="downloadCSV" class="btn btn-sm btn-danger text-end" data-bs-toggle="tooltip" title="Export data in CSV">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                            </a>
                        </div>
                        <div style="">
                            <table id="order-report-table" class="table">
                                <thead>
                            
                                    <tr>
                                        <th>{{ ucfirst($reportType) }}</th>
                                        <th>Total Order Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $totalSales = 0;
                                    @endphp
                                    @foreach ($data as $row)
                                    
                                        <tr>
                                            <td><a href="{{route('secondary.order.report',['state'=>$row->id,'date_from'=>$date_from,'date_to'=>$date_to,'brand'=>$brand])}}" target="_blank">{{ $row->name ?? 'N/A' }}</a></td>
                                            <td>{{ $row->total_sales }}</td>
                                        </tr>
                                         @php
                                            $totalSales += $row->total_sales;
                                        @endphp
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>Total</th>
                                        <th>{{ $totalSales }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                    </div>
                </div>
            </div>
        @endif
    @endif
    @if(!empty($reportType) && !empty($data))
        @if($reportType=='product')
            <div class="row">
                
                <div class="col-md-12">
                    
                    <div class="card card-body">
                        
                        <h5>{{ ucfirst($reportType) }} Style wise Total Order QTY Report</h5>
                        <div class="col-auto text-end">
                            <a href="javascript:void(0);" id="downloadproductCSV" class="btn btn-sm btn-danger text-end" data-bs-toggle="tooltip" title="Export data in CSV">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                            </a>
                        </div>
                        <div style="">
                            <table id="order-report-product-table" class="table">
                                <thead>
                            
                                    <tr>
                                        <th>{{ ucfirst($reportType) }} Style No</th>
                                        <th>Total Order Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $totalSales = 0;
                                    @endphp
                                    @foreach ($data as $row)
                                        <tr>
                                            <td><a href="{{route('secondary.order.report',['product'=>$row->id,'date_from'=>$date_from,'date_to'=>$date_to,'brand'=>$brand])}}" target="_blank">{{ $row->style_no ?? 'N/A' }}</a></td>
                                            <td>{{ $row->total_sales }}</td>
                                        </tr>
                                        @php
                                            $totalSales += $row->total_sales;
                                        @endphp
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>Total</th>
                                        <th>{{ $totalSales }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                    </div>
                </div>
            </div>
        @endif
    @endif
    @if(!empty($reportType) && !empty($data))
        @if($reportType=='ase')
            <div class="row">
                
                <div class="col-md-12">
                    
                    <div class="card card-body">
                        
                        <h5>{{ strtoupper($reportType) }} wise Total Order QTY Report</h5>
                        <div class="col-auto text-end">
                            <a href="javascript:void(0);" id="downloadaseCSV" class="btn btn-sm btn-danger text-end" data-bs-toggle="tooltip" title="Export data in CSV">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                            </a>
                        </div>
                        <div style="">
                            <table id="order-report-ase-table" class="table">
                                <thead>
                            
                                    <tr>
                                        <th>{{ ucfirst($reportType) }}</th>
                                        <th>Total Order Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
				    @php
                                        $totalSales = 0;
                                    @endphp
                                    @foreach ($data as $row)
                                        <tr>
                                            <td><a href="{{route('secondary.order.report',['ase'=>$row->id,'date_from'=>$date_from,'date_to'=>$date_to,'brand'=>$brand])}}" target="_blank">{{ $row->name ?? 'N/A' }}</a>({{$row->status == 1 ? 'Active' : 'Inactive'}} user)  </td>
                                            <td>{{ $row->total_sales }}</td>
                                        </tr>
					@php
                                            $totalSales += $row->total_sales;
                                        @endphp
                                    @endforeach
                                </tbody>
				<tfoot>
                                    <tr>
                                        <th>Total</th>
                                        <th>{{ $totalSales }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                    </div>
                </div>
            </div>
        @endif
    @endif
    
    @if(!empty($reportType) && !empty($data))
        @if($reportType=='asm')
            <div class="row">
                
                <div class="col-md-12">
                    
                    <div class="card card-body">
                        
                        <h5>{{ strtoupper($reportType) }} wise Total Order QTY Report</h5>
                        <div class="col-auto text-end">
                            <a href="javascript:void(0);" id="downloadasmCSV" class="btn btn-sm btn-danger text-end" data-bs-toggle="tooltip" title="Export data in CSV">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                            </a>
                        </div>
                        <div style="">
                            <table id="order-report-asm-table" class="table">
                                <thead>
                            
                                    <tr>
                                        <th>{{ ucfirst($reportType) }}</th>
                                        <th>Total Order Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
				                     @php
                                        $totalSales = 0;
                                    @endphp
                                    @foreach ($data as $row)
                                    
                                   
                                        <tr>
                                            <td><a href="{{route('secondary.order.report',['asm'=>$row['id'],'date_from'=>$date_from,'date_to'=>$date_to,'brand'=>$brand])}}" target="_blank">{{ $row['name'] ?? 'N/A' }}</a></td>
                                            <td>{{ $row['total_sales'] }}</td>
                                        </tr>
					                    @php
                                            $totalSales += $row['total_sales'];
                                        @endphp
                                    @endforeach
                                </tbody>
				<tfoot>
                                    <tr>
                                        <th>Total</th>
                                        <th>{{ $totalSales }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                    </div>
                </div>
            </div>
        @endif
    @endif

    
    @if(!empty($reportType) && !empty($data))
        @if($reportType=='rsm')
            <div class="row">
                
                <div class="col-md-12">
                    
                    <div class="card card-body">
                        
                        <h5>{{ strtoupper($reportType) }} wise Total Order QTY Report</h5>
                        <div class="col-auto text-end">
                            <a href="javascript:void(0);" id="downloadrsmCSV" class="btn btn-sm btn-danger text-end" data-bs-toggle="tooltip" title="Export data in CSV">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                            </a>
                        </div>
                        <div style="">
                            <table id="order-report-rsm-table" class="table">
                                <thead>
                            
                                    <tr>
                                        <th>{{ ucfirst($reportType) }}</th>
                                        <th>Total Order Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
				    @php
                                        $totalSales = 0;
                                    @endphp
                                    @foreach ($data as $row)
                                        <tr>
                                            <td><a href="{{route('secondary.order.report',['rsm'=>$row['id'],'date_from'=>$date_from,'date_to'=>$date_to,'brand'=>$brand])}}" target="_blank">{{ $row['name'] ?? 'N/A' }}</a></td>
                                            <td>{{ $row['total_sales'] }}</td>
                                        </tr>
					@php
                                            $totalSales += $row['total_sales'];
                                        @endphp
                                    @endforeach
                                </tbody>
				<tfoot>
                                    <tr>
                                        <th>Total</th>
                                        <th>{{ $totalSales }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                    </div>
                </div>
            </div>
        @endif
    @endif
    
    @if(!empty($reportType) && !empty($data))
        @if($reportType=='vp')
            <div class="row">
                
                <div class="col-md-12">
                    
                    <div class="card card-body">
                        
                        <h5>{{ strtoupper($reportType) }} wise Total Order QTY Report</h5>
                        <div class="col-auto text-end">
                            <a href="javascript:void(0);" id="downloadvpCSV" class="btn btn-sm btn-danger text-end" data-bs-toggle="tooltip" title="Export data in CSV">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                            </a>
                        </div>
                        <div style="">
                            <table id="order-report-vp-table" class="table">
                                <thead>
                            
                                    <tr>
                                        <th>{{ ucfirst($reportType) }}</th>
                                        <th>Total Order Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
				    @php
                                        $totalSales = 0;
                                    @endphp
                                    @foreach ($data as $row)
                                        <tr>
                                            <td><a href="{{route('secondary.order.report',['vp'=>$row['id'],'date_from'=>$date_from,'date_to'=>$date_to,'brand'=>$brand])}}" target="_blank">{{ $row['name'] ?? 'N/A' }}</a></td>
                                            <td>{{ $row['total_sales'] }}</td>
                                        </tr>
					@php
                                            $totalSales += $row['total_sales'];
                                        @endphp
                                    @endforeach
                                </tbody>
				<tfoot>
                                    <tr>
                                        <th>Total</th>
                                        <th>{{ $totalSales }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                    </div>
                </div>
            </div>
        @endif
    @endif
    
    @if(!empty($reportType) && !empty($data))
        @if($reportType=='distributor')
            <div class="row">
                
                <div class="col-md-12">
                    
                    <div class="card card-body">
                        
                        <h5>{{ strtoupper($reportType) }} wise Total Order QTY Report</h5>
                        <div class="col-auto text-end">
                            <a href="javascript:void(0);" id="downloaddistributorCSV" class="btn btn-sm btn-danger text-end" data-bs-toggle="tooltip" title="Export data in CSV">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                            </a>
                        </div>
                        <div style="">
                            <table id="order-report-distributor-table" class="table">
                                <thead>
                            
                                    <tr>
                                        <th>{{ ucfirst($reportType) }}</th>
                                        <th>Total Order Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
				    @php
                                        $totalSales = 0;
                                    @endphp
                                    @foreach ($data as $row)
                                        <tr>
                                            <td><a href="{{route('secondary.order.report',['distributor'=>$row['id'],'date_from'=>$date_from,'date_to'=>$date_to,'brand'=>$brand])}}" target="_blank">{{ $row['name'] ?? 'N/A' }}</div></td>
                                            <td>{{ $row['total_sales'] }}</td>
                                        </tr>
					@php
                                            $totalSales += $row['total_sales'];
                                        @endphp
                                    @endforeach
                                </tbody>
				<tfoot>
                                    <tr>
                                        <th>Total</th>
                                        <th>{{ $totalSales }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                    </div>
                </div>
            </div>
        @endif
    @endif
    
</section>
@endsection

@section('script')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const monthInput = document.getElementById('month_year');
        const selectedValue = monthInput.value; // e.g., "2025-12"

        if (selectedValue) {
            const months = [
                "January", "February", "March", "April", "May", "June",
                "July", "August", "September", "October", "November", "December"
            ];

            // Split "2025-12" to get "12", convert to Number, and subtract 1 for index
            const monthIndex = parseInt(selectedValue.split('-')[1]) - 1;
            const selectedMonth = months[monthIndex];

            console.log(selectedMonth); // Result: "December"
        }
        const brand = @json($brand);
        //const selectedMonth = monthInput.value;
        const selectedYear = @json($year);
        document.querySelectorAll('.report-btn').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const monthYear = monthInput.value;
                if (!monthYear) {
                    alert('Please select a month first.');
                    return;
                }

                // Build URL with month-year
                const reportType = this.getAttribute('data-report');
                const url = `?month=${selectedMonth}&year=${selectedYear}&month_year=${monthYear}&report_type=${reportType}&brand=${brand}`;
                window.location.href = url;
            });
        });
    });
</script>

<script>
    function download_table_as_csv(tableId, filename = 'report.csv') {
        let csv = [];
        const rows = document.querySelectorAll(`#${tableId} tr`);
        for (let row of rows) {
            let cols = row.querySelectorAll('th, td');
            let rowData = [];
            for (let col of cols) {
                // Escape double quotes and commas
                let text = col.innerText.replace(/"/g, '""');
                rowData.push(`"${text}"`);
            }
            csv.push(rowData.join(','));
        }

        // Create CSV file blob and trigger download
        const csvFile = new Blob([csv.join('\n')], { type: 'text/csv' });
        const downloadLink = document.createElement('a');
        downloadLink.href = URL.createObjectURL(csvFile);
        downloadLink.download = filename;
        downloadLink.click();
    }

    $('#downloadCSV').click(function () {
        download_table_as_csv('order-report-table', 'state_wise_total_order_qty_report.csv');
    });
    
    $('#downloadproductCSV').click(function () {
        download_table_as_csv('order-report-product-table', 'product_wise_total_order_qty_report.csv');
    });
    
    $('#downloadaseCSV').click(function () {
        download_table_as_csv('order-report-ase-table', 'ASE_wise_total_order_qty_report.csv');
    });
    
     $('#downloadasmCSV').click(function () {
        download_table_as_csv('order-report-asm-table', 'ASM_wise_total_order_qty_report.csv');
    });
    
     $('#downloadrsmCSV').click(function () {
        download_table_as_csv('order-report-rsm-table', 'RSM_wise_total_order_qty_report.csv');
    });
    
     $('#downloadvpCSV').click(function () {
        download_table_as_csv('order-report-vp-table', 'VP_wise_total_order_qty_report.csv');
    });
    
     $('#downloaddistributorCSV').click(function () {
        download_table_as_csv('order-report-distributor-table', 'Distributor_wise_total_order_qty_report.csv');
    });
</script>

@endsection