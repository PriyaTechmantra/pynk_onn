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
                        <h4>Members</h4>
                            <div class="search__filter mb-0">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <p class="text-muted mt-1 mb-0">Showing {{$users->count()}} out of {{$users->total()}} Entries</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        
                                        <div class="col-md-12 text-end">
                                            <form class="row align-items-end" action="">
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
                                                        @can('member csv export')
                                                        <a href="{{ url('members/export/csv',['keyword'=>$request->keyword]) }}" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Export data in CSV">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                                            CSV
                                                        </a>
                                                        @endcan
                                                        @can('member csv upload')
                                                        <a href="#csvModal" data-bs-toggle="modal" class="btn btn-sm btn-danger"> Bulk Upload</a>
                                                        @endcan
                                                         @can('create member')
                                                            <a href="{{ url('members/create') }}" class="btn btn-danger btn-sm">Add Member</a>
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
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Mobile</th>
                                    <th>Status</th>
                                    <th class="action_btn">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $index=> $user)
                                
                                
                                <tr>
                                    <td>{{ $index+1 }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        {{ $user->mobile }}
                                    </td>
                                    <td> @can('member status change')<a href="{{ url('members/'.$user->id.'/status/change') }}" ><span class="badge bg-{{($user->status == 1) ? 'success' : 'danger'}}">{{($user->status == 1) ? 'Active' : 'Inactive'}}</span></a>@endcan</td>
                                    <td>
                                        @can('update member')
                                        <a href="{{ url('members/'.$user->id.'/edit') }}" class="btn btn-success">Edit</a>
                                        @endcan
                                        
                                            
                                        @can('delete member')
                                        <a href="{{ url('members/'.$user->id.'/delete') }}" class="btn btn-danger mx-2">Delete</a>
                                        @endcan
                                        @can('member issue list')
                                        <a href="{{ url('members/'.$user->id.'/issue/list') }}" class="btn btn-primary mx-2">Issue List</a>
                                        @endcan
                                        
                                    </td>
                                </tr>
                               
                                @endforeach
                            </tbody>
                        </table>
                        </div>
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
                <form method="post" action="{{ url('members/upload/csv') }}" enctype="multipart/form-data">@csrf
                    <input type="file" name="file" class="form-control" accept=".csv">
                    <br>
                    <a href="{{ asset('backend/csv/sample-member.csv') }}">Download Sample CSV</a>
                    <br>
                    <button type="submit" class="btn btn-danger mt-3" id="csvImportBtn">Import <i class="fas fa-upload"></i></button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection