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
                        <h4>Notification
                            <a href="{{ route('notifications.exportCSV', request()->only('term','date_from','date_to')) }}" class="btn btn-sm btn-cta float-end" data-bs-toggle="tooltip" title="Export data in CSV">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                CSV
                            </a>
                        </h4>

                        <div class="search__filter mb-0">
                            <div class="row">
                                <div class="col-12">
                                    <p class="text-muted mt-1 ">Showing {{$data->count()}} out of {{$data->total()}} Entries</p>
                                </div>
                            </div>
                            <div class="row">
                                        
                                <div class="col-12">
                                   <form action="{{ route('notifications.index') }}" method="GET">
                                        <div class="row g-2 align-items-center">
                                            <div class="col-3 d-flex align-items-center gap-2">
                                                <input type="search" name="term" id="term"
                                                    class="form-control form-control-sm"
                                                    placeholder="Search by keyword"
                                                    value="{{ request('term') }}"
                                                    autocomplete="off">
                                            </div>

                                            <div class="col-6 d-flex align-items-center gap-2">
                                                <label class="text-muted small ">From</label>
                                                <input type="date" name="date_from"
                                                    class="form-control form-control-sm"
                                                    value="{{ request('date_from') }}">

                                                <label class="text-muted small ">To</label>
                                                <input type="date" name="date_to"
                                                    class="form-control form-control-sm"
                                                    value="{{ request('date_to') }}">
                                            </div>

                                              <div class="col-3 text-end">
                                                <button type="submit" class="btn btn-sm btn-cta">Filter</button>
                                                <a href="{{ url()->current() }}" 
                                                class="btn btn-sm btn-cta" 
                                                data-bs-toggle="tooltip" 
                                                title="Clear Filter">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" 
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" 
                                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" 
                                                        class="feather feather-x">
                                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                                    </svg>
                                                </a>
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
                                        <th class="index-col">#</th>
                                        <th>Type</th>
                                        <th>Sender</th>
                                        <th>Receiver</th>
                                        <th>Title</th>
                                        <th>Body</th>
                                        <th>Created At</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($data as $index => $item)
                                    @php
                                        if (!empty($_GET['read_flag'])) {
                                            if ($_GET['read_flag'] == 'read') {
                                                if ($item->read_flag == 0) continue;
                                            } else {
                                                if ($item->read_flag == 1) continue;
                                            }
                                        }
                                    @endphp
                                        <tr>
                                        <td>{{($data->firstItem()) + $index}}</td>
                                            <td>
                                                @if($item->type == "secondary-order-place")
                                                    <span class="badge bg-success">Secondary Order Place</span>
                                                @elseif($item->type == "primary-order-place")
                                                <span class="badge bg-danger">Primary Order Place</span>
                                                @elseif($item->type == "store-add")
                                                    <span class="badge bg-primary">New Store Create</span>
                                                @endif
                                            </td>
                                            <td>
                                                <p class="small">{{$item->senderDetails->name ?? ''}}</p>
                                            </td>
                                            <td>
                                                <p class="small">{{$item->receiverDetails->name ?? ''}}</p>
                                            </td>
                                            <td>
                                                <p class="small">{{$item->title}}</p>
                                            </td>
                                            <td>
                                                <p class="small">{{$item->body}}</p>
                                            </td>
                                            <td>
                                                <p class="small">{{ date('j F, Y h:i A', strtotime($item->created_at)) }}</p>
                                            </td>
                                            <td class="text-center align-middle">
                                                <span class="badge bg-{{($item->read_flag == 1) ? 'success' : 'danger'}}">{{($item->read_flag == 1) ? 'Read' : 'Unread'}}</span>
                                            </td>
                                        
                                        </tr>
                                    @empty
                                        <tr><td colspan="100%" class="small text-muted text-center">No data found</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end">
                            {{ $data->appends($_GET)->links() }}
                        </div> 
                    </div>
                </div>
            </div>
        </div>
</div>



@endsection