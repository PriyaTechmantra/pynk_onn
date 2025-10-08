@extends('layouts.app')

@section('content')


<div class="container mt-2">
        <div class="row">
            <div class="col-md-12">

                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif

                <div class="card mt-3">
                    <div class="card-header">
                        <h4>Bookshelves
                            
                        </h4>
                                <div class="search__filter mb-0">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <p class="text-muted mt-1 mb-0">Showing {{$data->count()}} out of {{$data->total()}} Entries</p>
                                        </div>
                                        <div class="col-md-10 text-end">
                                            <form class="row align-items-end" action="">
                                                <div class="col">
                                                    <select class="form-select form-select-sm" aria-label="Default select example" name="office_id" id="office_id">
                                                        <option value="" selected disabled>Select Office</option>
                                                        @foreach ($office as $cat)
                                                            <option value="{{$cat->id}}" {{request()->input('office_id') == $cat->id ? 'selected' : ''}}> {{$cat->name}}({{$cat->address}})</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col">
                                                    <input type="search" name="keyword" id="term" class="form-control form-control-sm" placeholder="Search by keyword." value="{{app('request')->input('keyword')}}" autocomplete="off">
                                                </div>
                                                <div class="col">
                                                    <div class="btn-group">
                                                        <button type="submit" class="btn btn-danger btn-sm">
                                                            Filter
                                                        </button>
                        
                                                        <a href="{{ url()->current() }}" class="btn btn-sm btn-light" data-bs-toggle="tooltip" title="Clear Filter">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                                        </a>
                                                        @can('bookshelve csv export')
                                                        <a href="{{ url('bookshelves/export/csv',['office_id'=>$request->office_id,'keyword'=>$request->keyword]) }}" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Export data in CSV">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                                            CSV
                                                        </a>
                                                        @endcan
                                                        
                                                        @can('bookshelve csv upload')
                                                        <a href="#csvModal" data-bs-toggle="modal" class="btn btn-sm btn-danger"> Bulk Upload</a>
                                                        @endcan
                                                        @can('create bookshelve')
                                                        <a href="{{ url('bookshelves/create') }}" class="btn btn-sm btn-danger">Add Bookshelves</a>
                                                        @endcan
                                                        
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                            <thead>
                                <tr>
                                    <th class="sl_no">#</th>
                                    <th>Office</th>
                                    <th class="bookshelf">Office Location</th>
                                    <th>Office Area</th>
                                    <th class="bookshelf">Bookshelf No</th>
                                    <th>Manager</th>
                                    <th>Created By</th>
                                    <th>QR</th>
                                    <th class="action_btn">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data as $index=> $item)
                                <tr>
                                    <td>{{ $index+1 }}</td>
                                    <td>{{ $item->office->name ??''}}</td>
                                    <td>{{ $item->office->address ??''}}</td>
                                    <td>{{ $item->area }}</td>
                                    <td>{{ $item->number }}</td>
                                    <td>{{ $item->manager }}</td>
                                    <td>{{ $item->user->name ??'' }}</td>
                                    <td>
                                        <img src="https://bwipjs-api.metafloor.com/?bcid=qrcode&text={{$item->qrcode}}&height=6&textsize=10&scale=6&includetext" 
                                                 alt="QR Code" 
                                                 style="height: 105px;width:105px" 
                                                 id="qr-{{$item->id}}" 
                                                 data-qrcode="{{ $item->qrcode }}">
                                        
                                            <!-- Print Button -->
                                            <a class="btn btn-sm btn-danger print_btn" data-bs-toggle="tooltip" title="Print QR" onclick="printQRCode('{{ $item->id }}', '{{ $item->number }}', '{{ $item->qrcode }}')">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download">
                                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                    <polyline points="7 10 12 15 17 10"></polyline>
                                                    <line x1="12" y1="15" x2="12" y2="3"></line>
                                                </svg>
                                            </a>
                                    </td>
                                    <td>
                                        @can('update bookshelve')
                                        <a href="{{ url('bookshelves/'.$item->id.'/edit') }}" class="btn btn-success">Edit</a>
                                        @endcan
                                        @can('view bookshelve')
                                        <a href="{{ url('bookshelves/'.$item->id) }}" class="btn btn-success">View</a>
                                        @endcan
                                        @can('delete bookshelve')
                                        <a onclick="return confirm('Are you sure ?')" href="{{ url('bookshelves/'.$item->id.'/delete') }}" class="btn btn-danger mx-2">Delete</a>
                                        @endcan
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        </div>
                        {!! $data->render() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>



<div class="modal fade" id="csvModal" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                Bulk Upload
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="{{ url('bookshelves/upload/csv') }}" enctype="multipart/form-data">@csrf
                    <input type="file" name="file" class="form-control" accept=".csv">
                    <br>
                    <a href="{{ asset('backend/csv/sample-bookshelf.csv') }}">Download Sample CSV</a>
                    <br>
                    <button type="submit" class="btn btn-danger mt-3" id="csvImportBtn">Import <i class="fas fa-upload"></i></button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
<script>
    function printQRCode(itemId, bookTitle, qrText) {
        // Open a new window for printing
        const printWindow = window.open('', '', 'width=600,height=400');
        const qrSrc = `https://bwipjs-api.metafloor.com/?bcid=qrcode&text=${qrText}&height=6&textsize=10&scale=6&includetext`;

        printWindow.document.write(`
            <html>
            <head>
                <title>Print QR</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        text-align: center;
                    }
                    .print-container {
                        margin-top: 20px;
                    }
                    .book-title {
                        font-size: 20px;
                        font-weight: bold;
                        margin-bottom: 10px;
                    }
                    .qr-code {
                        margin-top: 10px;
                    }
                </style>
            </head>
            <body>
                <div class="print-container">
                    <!-- Book Name -->
                    <div class="book-title">BookShelf No: ${bookTitle}</div>
                    <!-- QR Code Placeholder -->
                    <div class="qr-code">
                        <img id="qr-code-img" src="${qrSrc}" style="height: 150px; width: 150px;">
                    </div>
                </div>
            </body>
            </html>
        `);

        // Wait for the image to load before printing
        const qrCodeImg = printWindow.document.getElementById('qr-code-img');
        qrCodeImg.onload = function () {
            // Once the image is loaded, trigger the print
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        };

        // If the image fails to load, close the window
        qrCodeImg.onerror = function () {
            alert('QR code could not be loaded.');
            printWindow.close();
        };
    }
</script>
@endsection