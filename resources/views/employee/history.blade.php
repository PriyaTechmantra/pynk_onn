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
                        <h4 class="d-flex">
                            Transaction History of {{$book->title}}
                            @can('book csv export')
                            <a href="{{ url('books/history/export/csv/'.$book->id) }}" class="btn btn-sm btn-cta ms-auto" data-bs-toggle="tooltip" title="Export data in CSV">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                CSV
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
                                        
                                            <div class="col-md-12 text-end">
                                                <form class="row align-items-end" action="">
                                                    <div class="col">
                                                        <input type="date" name="issue_date_from" id="term" class="form-control form-control-sm"  value="{{app('request')->input('issue_date_from')}}">
                                                    </div>
                                                     <div class="col">
                                                        <input type="date" name="issue_date_to" id="term" class="form-control form-control-sm"  value="{{app('request')->input('issue_date_to')}}">
                                                    </div>
                                                    <div class="col">
                                                        <!--<div class="btn-group">-->
                                                            <button type="submit" class="btn btn-cta btn-sm">
                                                                Filter
                                                            </button>
                        
                                                            <a href="{{ url()->current() }}" class="btn btn-sm btn-cta" data-bs-toggle="tooltip" title="Clear Filter">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                                            </a>
                                                            
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
                                    <th class="bookshelf">Requested By</th>
                                    <th class="bookshelf">Request Date</th>
                                    <th class="bookshelf">Request Sent To</th>
                                    <th class="bookshelf">Authorized Member Issued On</th>
                                    <th class="bookshelf">Request Accept On</th>
                                    <th class="bookshelf">Returned By</th>
                                    <th class="bookshelf">Returned Date</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($data as $index=> $item)
                                @php
                                $transfer=\App\Models\BookTransfer::where('book_id',$book->id)->where('from_user_id',$item->user_id)->with('toUser')->first(); 
                                if(!empty($item->bookmark->from_user_id)){
                                $returnRequest=\App\Models\ReturnRequest::where('book_id',$book->id)->where('from_user_id',$item->bookmark->from_user_id)->with('touser')->first(); 
                                }
                                @endphp
                                <tr>
                                    <td class="index-col">{{ $index+1 }}</td>
                                    @if(!empty($item->bookmark->fromuser))
                                    <td><a href="{{url('members/'.$item->bookmark->fromuser->id)}}">{{ $item->bookmark->fromuser->name ??''}}</a></td>
                                    @else
                                    <td></td>
                                    @endif
                                    @if($item->bookmark)
                                    <td>{{date('d-m-Y', strtotime($item->bookmark->created_at))}}</td>
                                    @else
                                    <td></td>
                                    @endif
                                    @if(!empty($item->bookmark->touser))
                                    <td><a href="{{url('members/'.$item->bookmark->touser->id)}}">{{ $item->bookmark->touser->name }}</a></td>
                                    @else
                                    <td></td>
                                    @endif
                                    <td>{{ date('d-m-Y', strtotime($item->request_date)) }}</td>
                                    @if($item->status_for_requested_user==1)
                                    <td>{{ date('d-m-Y', strtotime($item->status_change_date)) }}</td>
                                    @else
                                    <td></td>
                                    @endif
                                    @if(!empty($returnRequest->touser))
                                    <td><a href="{{url('members/'.$returnRequest->touser->id)}}">{{ $returnRequest->touser->name ??'' }}</a></td>
                                    @else
                                    <td></td>
                                    @endif
                                    @if($item->is_return==1)
                                    <td>{{date('d-m-Y', strtotime( $item->return_date)) }}</td>
                                    @else
                                    <td></td>
                                    @endif
                                    <td>{{$item->issue_type ??''}}</td>
                                    
                                </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No record found</td>
                                    </tr>
                                @endforelse
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