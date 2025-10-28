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
                        <h4>Activity
                            <a href="{{ route('activities.exportCSV', request()->all()) }}" class="btn btn-sm btn-cta float-end" data-bs-toggle="tooltip" title="Export data in CSV">
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
                                    <form action="{{ route('activities.index') }}">
                                        <div class="row g-3 align-items-end">
                                            <div class="col-md-2">
                                                <label class="form-label small text-muted mb-1">Brand</label>
                                                <select name="brand_selection" class="form-select form-select-sm">
                                                    <option value="3" {{ request()->input('brand_selection') == 3 ? 'selected' : '' }}>ALL</option>
                                                    <option value="1" {{ request()->input('brand_selection') == 1 ? 'selected' : '' }}>ONN</option>
                                                    <option value="2" {{ request()->input('brand_selection') == 2 ? 'selected' : '' }}>PYNK</option>
                                                </select>
                                            </div>

                                            <div class="col-md-2">
                                                <label class="form-label small text-muted mb-1">User Type</label>
                                                @php
                                                    $typeLabels = [
                                                        1 => 'VP',
                                                        2 => 'RSM',
                                                        3 => 'ASM',
                                                        4 => 'ASE',
                                                    ];
                                                @endphp
                                                <select class="form-select form-select-sm select2" name="type" id="user_type">
                                                    <option value="">All</option>
                                                    @foreach($userTypes as $type)
                                                        <option value="{{ $type }}" {{ request()->input('type') == $type ? 'selected' : '' }}>
                                                            {{ $typeLabels[$type] ?? 'Unknown' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-2">
                                                <label class="form-label small text-muted mb-1">User Name</label>
                                                <select class="form-select form-select-sm select2" name="user_name" id="user_name">
                                                    <option value="">All</option>
                                                    @foreach($employees as $emp)
                                                        <option value="{{ $emp->id }}" {{ request()->input('user_name') == $emp->id ? 'selected' : '' }}>
                                                            {{ $emp->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-2">
                                                <label class="form-label small text-muted mb-1">From</label>
                                                <input type="date" name="date_from" class="form-control form-control-sm"
                                                    value="{{ request()->input('date_from') }}">
                                            </div>

                                            <div class="col-md-2">
                                                <label class="form-label small text-muted mb-1">To</label>
                                                <input type="date" name="date_to" class="form-control form-control-sm"
                                                    value="{{ request()->input('date_to') }}">
                                            </div>

                                            <div class="col-md-2 d-flex justify-content-end align-items-center gap-2">
                                                <button type="submit" class="btn btn-sm btn-cta" title="Apply Filter">
                                                    <i class="fas fa-filter me-1"></i> Filter
                                                </button>
                                                <a href="{{ url()->current() }}" class="btn btn-sm btn-cta" data-bs-toggle="tooltip" title="Clear Filter">
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
                            <table class="table table-sm admin-table no-sticky">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>User</th>
                                        <th>Store</th>
                                        <th>Activity</th>
                                        <th>Datetime</th>
                                        <th>Comment</th>
                                        <th>Location</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($data as $index => $item)
                                        <tr>
                                            <td>{{ $index + $data->firstItem() }}</td>
                                                @php 
                                                $typeColors = [
                                                    1 => 'success', 
                                                    2 => 'danger', 
                                                    3 => 'primary', 
                                                    4 => 'secondary', 
                                                ];
                                                @endphp
                                            <td>
                                                @if($item->user)
                                                    <span class="badge bg-{{ $typeColors[$item->user->type] ?? 'secondary' }}">
                                                        {{ $typeLabels[$item->user->type] ?? 'N/A' }}
                                                    </span>
                                                    <p class="text-dark">{{ $item->user->name }}</p>
                                                @else
                                                    <span class="text-muted small">—</span>
                                                @endif
                                            </td>
                                            <td> {{ $item->store ? $item->store->name : '' }} </td>
                                            <td> {{ $item->type}} </td>
                                            <td> {{ $item->date }} {{ $item->time }} </td>
                                            <td> {{ $item->comment }} </td>
                                            <td> {{ $item->location }} </td>
                                            @if($item->type=='leave')
                                            <td><a href="{{route('users.activity.remove',$item->id)}}" type="button" class="btn btn-danger">Remove leave</a></td>
                                            @endif
                                        </tr>
                                    @empty
                                        <tr><td colspan="100%" class="small text-muted">No data found</td></tr>
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

@section('script')
    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });
    </script>
@endsection
