@extends('admin.layouts.app')
@section('page', 'No Order Reason')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@section('content')
<section>
    <div class="card card-body">
        <div class="search__filter mb-0">
            <div class="row">
                <div class="col-md-3">
                    <p class="small text-muted mt-1 mb-0">Showing {{$data->firstItem()}} - {{$data->lastItem()}} out of {{$data->total()}} Entries</p>
                </div>

                <div class="col-md-9 text-end">
                    <form class="row align-items-end" action="" method="GET">
                        <div class="col-auto">
                            <label for="user_id" class="text-muted small">ASE</label>
                            <select name="user_id" id="user_id" class="form-control form-control-sm select2">
                                <option value="" disabled>Select</option>
                                <option value="" selected>All</option>
                                @foreach ($ases as $ase)
                                    <option value="{{$ase->id}}" {{ request()->input('user_id') == $ase->id ? 'selected' : '' }}>{{$ase->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <label for="store_id" class="text-muted small">Store</label>
                            <select name="store_id" id="store_id" class="form-control form-control-sm select2">
                                <option value="" disabled>Select</option>
                                <option value="" selected>All</option>
                                @foreach ($stores as $store)
                                    <option value="{{$store->id}}" {{ request()->input('store_id') == $store->id ? 'selected' : '' }}>{{$store->store_name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <label for="comment" class="text-muted small">Reason</label>
                            <select name="comment" id="comment" class="form-control form-control-sm select2">
                                <option value="" disabled>Select</option>
                                <option value="" selected>All</option>
                                @foreach ($reasons as $reason)
                                    <option value="{{$reason->noorderreason}}" {{ request()->input('comment') == $reason->noorderreason ? 'selected' : '' }}>{{$reason->noorderreason}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <label for="term" class="text-muted small">Search</label>
                            <input type="search" name="keyword" id="term" class="form-control form-control-sm" placeholder="Search comment..." value="{{app('request')->input('keyword')}}" autocomplete="off">
                        </div>
                        <div class="col-auto">
                            <div class="btn-group">
                                <button type="submit" class="btn btn-danger btn-sm">
                                    Filter
                                </button>

                                <a href="{{ url()->current() }}" class="btn btn-sm btn-light" data-bs-toggle="tooltip" title="Clear Filter">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                </a>

                                <a href="{{ route('admin.store.noorderreasonview.csv') }}" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Export data in CSV">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>#SR</th>
                <th>Name</th>
                <th>Store Name</th>
                <th>Reason</th>
                <th>Location</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $index => $item)
                <tr>
                    <td>
                        {{ $data->firstItem() + $index }}
                    </td>
                    <td>
                        {{$item->user ? $item->user->name : ''}}
                    </td>
                    <td>
                        {{$item->store ? $item->store->store_name : ''}}
                    </td>
                    <td>
                        {{$item->comment}}
                        @if($item->description)
                        <p class="small text-muted mb-0">{{$item->description}}</p>
                        @endif
                    </td>
                    <td>
                        {{$item->location}}
                    </td>
                    <td>{{date('d M Y', strtotime($item->date))}} {{ $item->time}}</td>
                </tr>
            @empty
                <tr><td colspan="100%" class="small text-muted">No data found</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="d-flex justify-content-end">
        {{$data->appends($_GET)->links()}}
    </div>



    {{-- <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <div class="search__filter">
                        <div class="row align-items-center justify-content-between">
                            <div class="col">
                            </div>
                            <div class="col-auto">
                                <form action="">
                                    <div class="row g-3 align-items-center">
                                        <div class="col-auto">
                                            <input type="search" name="term" id="term" class="form-control" placeholder="Search here.." value="{{app('request')->input('term')}}" autocomplete="off">
                                        </div>
                                        <div class="col-auto">
                                            <button type="submit" class="btn btn-outline-danger btn-sm">Search</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#SR</th>
                                <th>Name</th>
                                <th>Store Name</th>
                                <th>Comment</th>
                                <th>Location</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data as $index => $item)
                                <tr>
                                    <td>
                                        {{ $data->firstItem() + $index }}
                                    </td>
                                    <td>
                                        {{$item->user ? $item->user->name : ''}}
                                    </td>
                                    <td>
                                        {{$item->store ? $item->store->store_name : ''}}
                                    </td>
                                    <td>
                                        {{$item->comment}}
										@if($item->description)
										<p class="small text-muted mb-0">{{$item->description}}</p>
										@endif
                                    </td>
                                    <td>
                                        {{$item->location}}
                                    </td>
                                    <td>{{date('d M Y', strtotime($item->date))}} {{ $item->time}}</td>
                                </tr>
                            @empty
                                <tr><td colspan="100%" class="small text-muted">No data found</td></tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-end">
                        {{$data->appends($_GET)->links()}}
                    </div>
                </div>
            </div>
        </div>
    </div> --}}
</section>
@endsection
@section('script')


<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });
    </script>

@endsection