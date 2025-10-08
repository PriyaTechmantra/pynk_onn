@extends('layouts.app')

@section('content')


<div class="container mt-2">
        <div class="row">
            <div class="col-md-12">

                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif

                <div class="card data-card mt-3">
                    <div class="card-header">
                        <h4>Issue List for approval
                            
                        </h4>
                                <div class="search__filter mb-0">
                                    <div class="row">
                                        <div class="col-12">
                                            <p class="text-muted mt-1 mb-0">Showing {{$data->count()}} out of {{$data->total()}} Entries</p>
                                        </div>
                                    </div>
                                        <div class="row">
                                        
                                            <div class="col-md-12 text-end">
                                                <form class="row align-items-end" action="">
                                                    <div class="col">
                                                        <input type="date" name="issue_date_from" id="term" class="form-control form-control-sm"  value="{{app('request')->input('issue_date_from')}}">
                                                    </div>
                                                     <div class="col">
                                                        <input type="date" name="issue_date_to" id="term" class="form-control form-control-sm"  value="{{app('request')->input('issue_date_to')}}">
                                                    </div>
                                                    <div class="col text-end">
                                                        <!--<div class="btn-group">-->
                                                            <button type="submit" class="btn btn-sm btn-cta">
                                                                Filter
                                                            </button>
                        
                                                            <a href="{{ url()->current() }}" class="btn btn-sm btn-cta" data-bs-toggle="tooltip" title="Clear Filter">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                                            </a>
                                                            {{--@can('book csv export')
                                                            <a href="{{ url('books/issue/list/export/csv') }}" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Export data in CSV">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                                                CSV
                                                            </a>
                                                            @endcan--}}
                                                        <!--</div>-->
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
                                    <th class="sl_no index-col">#</th>
                                    <th class="bookshelf">Member Name</th>
                                    <th class="bookshelf">Book Name</th>
                                    <th class="bookshelf">Issue request date</th>
                                    <th class="bookshelf">Type</th>
                                    <th>Approval</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data as $index=> $item)
                                @php
                                $transfer=\App\Models\BookTransfer::where('book_id',$item->id)->where('from_user_id',$item->user_id)->with('toUser')->first();  
                                 if($item->issue_type=='issue'){
                                   $type='Self';
                                 }elseif($item->issue_type=='bulk-issue'){
                                   $type='Bulk Issue';
                                 }elseif($item->issue_type=='re-issue'){
                                   $type='Re Issue';
                                 }
                                @endphp
                                <tr>
                                    <td class="index-col">{{ $index+1 }}</td>
                                    <td><a href="{{ url('members/'.$item->user->id) }}">{{ $item->user->name ??''}}</a></td>
                                    <td><a href="{{ url('books/'.$item->book->id) }}">{{ $item->book->title ??''}}</a></td>
                                    <td><span class="text-dark font-weight-bold mb-2">
                                            {{date('d-m-Y', strtotime($item->request_date))}}
                                        </span></td>
                                        <td>{{$type}}</td>
                                    <td>
                							<div class="btn-group" role="group">
                								<a href="{{ url('issues/books/'.$item->id.'/status/change?status=1') }}" type="button" class="status_1 btn btn-sm btn-cta {{($item->status == 1) ? 'active' : ''}}">Approved</a>
                
                								<a href="{{ url('issues/books/'.$item->id.'/status/change?status=0') }}" type="button" class="status_2 btn btn-sm btn-cta {{($item->status == 0) ? 'active' : ''}}">Rejected</a>
                							</div>
                    						@if($item->status == 1)
                    							 <span class="badge badge-status bg-success">Approved</span>
                    						@elseif($item->status == 2) <span class="badge badge-status bg-success">Waiting for approval</span>
                    						@else
                    						   <span class="badge badge-status bg-danger">Rejected</span>
                    						@endif
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
    $('select[name="office_id"]').on('change', (event) => {
        var value = $('select[name="office_id"]').val();
        OfficeChange(value);
    });
    @if (request()->input('office_id'))
        OfficeChange({{request()->input('office_id')}})
    @endif

    function OfficeChange(value) {
        $.ajax({
            url: '{{url("/")}}/bookshelves/list/officewise/'+value,
            method: 'GET',
            success: function(result) {
                var content = '';
                var slectTag = 'select[name="bookshelves_id"]';
                var displayCollection =  "All";

                content += '<option value="" selected>'+displayCollection+'</option>';
                $.each(result.data, (key, value) => {
                    let selected = ``;
                    @if (request()->input('bookshelves_id'))
                        if({{request()->input('bookshelves_id')}} == value.id) {selected = 'selected';}
                    @endif
                    content += '<option value="'+value.id+'"'; content+=selected; content += '>'+value.number+'</option>';
                });
                $(slectTag).html(content).attr('disabled', false);
            }
        });
    }
    
</script>
@endsection