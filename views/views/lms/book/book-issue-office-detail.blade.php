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
                        <h4>Issued book Details of {{$office->name}} ({{$office->address}})
                            
                        </h4>
                                <div class="search__filter mb-0">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <p class="text-muted mt-1 mb-0">Showing {{$data->count()}} out of {{$data->total()}} Entries</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        
                                        <div class="col-md-12 text-end">
                                            <form class="row align-items-end" action="">
                                               
                                                <div class="col">
                                                    <select class="form-select form-select-sm" aria-label="Default select example" name="bookshelves_id" id="bookshelves">
                                                        <option value="" selected disabled>Select Bookshelve</option>
                                                        
                                                            <option value="">Select Office  first</option>
                                                        
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
                                                <div class="col">
                                                    <div class="btn-group">
                                                        <button type="submit" class="btn btn-danger btn-sm">
                                                            Filter
                                                        </button>
                        
                                                        <a href="{{ url()->current() }}" class="btn btn-sm btn-light" data-bs-toggle="tooltip" title="Clear Filter">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                                        </a>
                                                        @can('book csv export')
                                                        <a href="{{ url('books/export/csv'). '?office_id=' . $office->id.'&bookshelves_id='. $request->bookshelves_id.'&category_id'.$request->category_id.'&keyword'.$request->keyword }}" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Export data in CSV">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                                            CSV
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
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="sl_no">#</th>
                                     <th>UID</th>
                                    <th>Office</th>
                                    <th class="bookshelf">Office Location</th>
                                    <th class="bookshelf">Bookshelf Number</th>
                                    <th>Category</th>
                                    <th>Title</th>
                                   
                                    <th>Author</th>
                                    <th>Status</th>
                                    <th>Qrcode</th>
                                    <th class="action_btn">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data as $index=> $item)
                                <tr>
                                    <td>{{ $index+1 }}</td>
                                    <td>{{ $item->uid ??'' }}</td>
                                    <td>{{ $item->office->name ??''}}</td>
                                    <td>{{ $item->office->address ??''}}</td>
                                    <td>{{ $item->bookshelves->number }}</td>
                                    <td>{{ $item->category->name }}</td>
                                    <td>{{ $item->title }}</td>
                                    
                                    <td>{{ $item->author ??'' }}</td>
                                    <td> @can('book status change')<a href="{{ url('books/'.$item->id.'/status/change') }}" ><span class="badge bg-{{($item->status == 1) ? 'success' : 'danger'}}">{{($item->status == 1) ? 'Active' : 'Inactive'}}</span></a>@endcan</td>
                                    <td><img src="https://bwipjs-api.metafloor.com/?bcid=qrcode&text={{$item->qrcode}}&height=6&textsize=10&scale=6&includetext" alt="" style="height: 105px;width:105px" id="{{$item->qrcode}}"></td>
                                    <td>
                                        @can('update book')
                                        <a href="{{ url('books/'.$item->id.'/edit') }}" class="btn btn-success ">Edit</a>
                                        @endcan
                                        @can('view book')
                                        <a href="{{ url('books/'.$item->id) }}" class="btn btn-secondary mx-2">View</a>
                                        @endcan
                                        @can('delete book')
                                        <a onclick="return confirm('Are you sure ?')" href="{{ url('books/'.$item->id.'/delete') }}" class="btn btn-danger mx-2">Delete</a><br>
                                        @endcan
                                        @can('book issue list')
                                        <a href="{{ url('books/'.$item->id.'/issue/list') }}" class="btn btn-primary mt-2">Number of Issues</a>
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
        var officeId = '{{ $office->id }}';
        var bookshelvesId = '{{ request()->input('bookshelves_id') }}';

        // Check if office_id is present
        if (officeId) {
            OfficeChange(officeId);
        } else if (bookshelvesId) {
            // If office_id is not present but bookshelves_id is, fetch and show the specific bookshelf
            FetchBookshelfById(bookshelvesId);
        }

        
            var value = '{{ $office->id }}';
            OfficeChange(value);
        
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
@endsection