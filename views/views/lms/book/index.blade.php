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
                        <h4>Books
                            
                        </h4>
                                <div class="search__filter mb-0">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <p class="text-muted mt-1 mb-0">Showing {{$data->count()}} out of {{$data->total()}} Entries</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        
                                        <div class="col-md-12 text-end">
                                            <form class="row" action="">
                                                <div class="col">
                                                    <select class="form-select form-select-sm" aria-label="Default select example" name="office_id" id="office_id">
                                                        <option value="" selected disabled>Select Office</option>
                                                        @foreach ($office as $cat)
                                                            <option value="{{$cat->id}}" {{request()->input('office_id') == $cat->id ? 'selected' : ''}}> {{$cat->name}}({{$cat->address}})</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col">
                                                    <select class="form-select form-select-sm" aria-label="Default select example" name="bookshelves_id" id="bookshelves">
                                                        <option value="" selected disabled>Select Bookshelve</option>
                                                        
                                                            <option value="{{ $request->bookshelves_id }}">Select Office  first</option>
                                                        
                                                    </select>
                                                </div>
                                                <div class="col">
                                                    <select class="form-select form-select-sm" aria-label="Default select example" name="category_id" id="category_id">
                                                        <option value="" selected disabled>Select Category</option>
                                                        @foreach ($category as $cat)
                                                            <option value="{{$cat->id}}" {{request()->input('category_id') == $cat->id ? 'selected' : ''}}> {{$cat->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col">
                                                    <input type="search" name="keyword" id="term" class="form-control form-control-sm" placeholder="Search by keyword." value="{{app('request')->input('keyword')}}" autocomplete="off">
                                                </div>
                                                    <div class="btn-group books_btn_group">
                                                        <button type="submit" class="btn btn-danger btn-sm">
                                                            Filter
                                                        </button>
                        
                                                        <a href="{{ url()->current() }}" class="btn btn-sm btn-light" data-bs-toggle="tooltip" title="Clear Filter">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                                        </a>
                                                        @can('book csv export')
                                                        <a href="{{ url('books/export/csv',['office_id'=>$request->office_id,'bookshelves_id'=>$request->bookshelves_id,'category_id'=>$request->category_id,'keyword'=>$request->keyword]) }}" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Export data in CSV">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                                            CSV
                                                        </a>
                                                        @endcan
                                                        @can('book csv upload')
                                                        <a href="#csvModal" data-bs-toggle="modal" class="btn btn-sm btn-danger"> Bulk Upload</a>
                                                        @endcan
              
                                                        @can('create book')
                                                        <a href="{{ url('books/create') }}" class="btn btn-sm btn-danger">Add Books</a>
                                                        @endcan
                                                        
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
                                        <th>UID</th>
                                        <th>Office</th>
                                        <th class="bookshelf">Office Location</th>
                                        <th class="bookshelf">Bookshelf No</th>
                                        <th>Category</th>
                                        <th>Book Title</th>
                                        <th >Status</th>
                                        <th>QR</th>
                                        <th class="action_btn">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $item->uid ?? '' }}</td>
                                        <td>{{ $item->office->name ?? '' }}</td>
                                        <td>{{ $item->office->address ?? '' }}</td>
                                        <td>{{ $item->bookshelves->number }}</td>
                                        <td>{{ $item->category->name }}</td>
                                        <td>{{ $item->title }}</td>
                                        <td>
                                            @can('book status change')
                                            <a href="{{ url('books/'.$item->id.'/status/change') }}">
                                                <span class="badge bg-{{ $item->status == 1 ? 'success' : 'danger' }}">
                                                    {{ $item->status == 1 ? 'Active' : 'Inactive' }}
                                                </span>
                                            </a>
                                            @endcan
                                        </td>
                                        <td><!-- QR Code Display -->
                                            <img src="https://bwipjs-api.metafloor.com/?bcid=qrcode&text={{$item->qrcode}}&height=6&textsize=10&scale=6&includetext" 
                                                 alt="QR Code" 
                                                 style="height: 105px;width:105px" 
                                                 id="qr-{{$item->id}}" 
                                                 data-qrcode="{{ $item->qrcode }}">
                                        
                                            <!-- Print Button -->
                                            <a class="btn btn-sm btn-danger print_btn" data-bs-toggle="tooltip" title="Print QR" onclick="printQRCode('{{ $item->id }}', '{{ $item->title }}', '{{ $item->uid }}','{{ $item->qrcode }}')">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download">
                                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                    <polyline points="7 10 12 15 17 10"></polyline>
                                                    <line x1="12" y1="15" x2="12" y2="3"></line>
                                                </svg>
                                            </a>
                                        </td>
                                        <td>
                                            @can('update book')
                                            <a href="{{ url('books/'.$item->id.'/edit') }}" class="btn btn-success">Edit</a>
                                            @endcan
                                            @can('view book')
                                            <a href="{{ url('books/'.$item->id) }}" class="btn btn-secondary mx-2">View</a>
                                            @endcan
                                            @can('delete book')
                                            <a onclick="return confirm('Are you sure ?')" href="{{ url('books/'.$item->id.'/delete') }}" class="btn btn-danger mx-2">Delete</a>
                                            @endcan
                                            @can('book issue list')
                                            <a href="{{ url('books/'.$item->id.'/issue/list') }}" class="btn btn-primary mt-2">Issued User</a>
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
                <form method="post" action="{{ url('books/upload/csv') }}" enctype="multipart/form-data">@csrf
                    <input type="file" name="file" class="form-control" accept=".csv">
                    <br>
                    <a href="{{ asset('backend/csv/sample-book.csv') }}">Download Sample CSV</a>
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
    $(document).ready(function() {
        var officeId = '{{ request()->input('office_id') }}';
        var bookshelvesId = '{{ request()->input('bookshelves_id') }}';

        // Check if office_id is present
        if (officeId) {
            OfficeChange(officeId);
        } else if (bookshelvesId) {
            // If office_id is not present but bookshelves_id is, fetch and show the specific bookshelf
            FetchBookshelfById(bookshelvesId);
        }

        $('select[name="office_id"]').on('change', function(event) {
            var value = $(this).val();
            OfficeChange(value);
        });
    });

    function OfficeChange(value) {
        $.ajax({
            url: `{{ url('/bookshelves/list/officewise') }}/${value}`,
            method: 'GET',
            success: function(result) {
                var slectTag = 'select[name="bookshelves_id"]';
                var displayCollection = "All";
                var bookshelvesId = '{{ request()->input('bookshelves_id') }}';

                var content = `<option value="">${displayCollection}</option>`;
                $.each(result.data, function(key, value) {
                    let selected = (bookshelvesId == value.id) ? 'selected' : '';
                    content += `<option value="${value.id}" ${selected}>${value.number}</option>`;
                });

                $(slectTag).html(content).attr('disabled', false);
            }
        });
    }

    function FetchBookshelfById(bookshelvesId) {
        $.ajax({
            url: `{{ url('/bookshelves/get') }}/${bookshelvesId}`,  // Assumes you have a route to fetch a single bookshelf by ID
            method: 'GET',
            success: function(result) {
                var slectTag = 'select[name="bookshelves_id"]';
                var content = '';

                if (result.data) {
                    content += `<option value="${result.data.id}" selected>${result.data.number}</option>`;
                } else {
                    content += `<option value="" selected>No bookshelf found</option>`;
                }

                $(slectTag).html(content).attr('disabled', false);
            }
        });
    }
</script>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- printThis Plugin -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/printThis/1.15.0/printThis.min.js"></script>
<script>

   $(document).ready(function() {
            $('#basic').on('click', function() {
                $('#print-code').show();
                $('#print-code').printThis({
                importCSS: true,        // Import page CSS
                importStyle: true,      // Import style tags
                loadCSS: "",            // Load an additional CSS file
                pageTitle: "Books Info", // Title for the printed document
                removeInline: false,    // Keep the inline styles
                printDelay: 333,        // Delay before printing to allow images to load
                afterPrint: function() {
                    $('#print-code').hide(); // Hide the table again after printing
                }
            });
            });
        });
</script>
<script>
    function printQRCode(itemId, bookTitle,uid, qrText) {
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
                    
                    <div class="book-title">UID: ${uid}</div>
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