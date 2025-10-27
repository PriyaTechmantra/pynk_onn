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
                        <h4>No Order Reason
                            <a href="{{route('store.noorderreasonview.csv', request()->only('ase','store_id' ,'comment','brand_selection')) }}" class="btn btn-sm btn-cta float-end" data-bs-toggle="tooltip" title="Export data in CSV">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                CSV
                            </a>
                        </h4>

                        <div class="search__filter mb-0">
                            <div class="row">
                                <div class="col-12">
                                    <p class="text-muted mt-1 mb-0">Showing {{$data->count()}} out of {{$data->total()}} Entries</p>
                                </div>
                            </div>
                            <div class="row">
                                        
                                <div class="col-12">
                                    <form action="{{route('stores.noorderreason')}}">
                                        <div class="row g-2 align-items-center">
                                            <div class="col-10 d-flex align-items-center gap-2">
                                                <label class="text-muted small mb-0">ASE</label>
                                                 <select name="ase" class="form-control form-control-sm">
                                                    <option value="">All</option>
                                                    @foreach($ases as $ase)
                                                        <option value="{{ $ase->id }}" {{ request('ase') == $ase->id ? 'selected' : '' }}>
                                                            {{ $ase->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <label class="text-muted small mb-0">Store</label>
                                               <select name="store_id" class="form-control form-control-sm">
                                                    <option value="">All</option>
                                                    @foreach($stores as $store)
                                                        <option value="{{ $store->id }}" {{ request('store_id') == $store->id ? 'selected' : '' }}>
                                                            {{ $store->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <label class="text-muted small mb-0">Reason</label>
                                                 <select name="comment" class="form-control form-control-sm">
                                                    <option value="">All</option>
                                                    @foreach($reasons as $reason)
                                                        <option value="{{ $reason->id }}" {{ request('comment') == $reason->id ? 'selected' : '' }}>
                                                            {{ $reason->noorderreason }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <label class="text-muted small mb-0">Brand</label>
                                                 <select name="brand_selection" class="form-control form-control-sm">
                                                    <option value="">All</option>
                                                    <option value="1" {{ app('request')->input('brand_selection') == 1 ? 'selected' : '' }}>Onn</option>
                                                    <option value="2" {{ app('request')->input('brand_selection') == 2 ? 'selected' : '' }}>Pynk</option>
                                                    <option value="3" {{ app('request')->input('brand_selection') == 3 ? 'selected' : '' }}>Both</option>
                                                </select>
                                            </div>

                                            <div class="col-2 text-end">
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

                            <table class="table">
                                <thead>
                                    <tr>
                                        <th class="index-col">#</th>
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
                                                {{$item->store ? $item->store->name : ''}}
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
            </div>
        </div>
</div>

@endsection
