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
                        <h4>Book Category
                            @can('create book category')
                            <a href="{{ url('bookcategories/create') }}" class="btn btn-sm btn-danger float-end">Add Book Category</a>
                            @endcan
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Details</th>
                                    <th>Status</th>
                                    <th class="action_btn">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data as $index=> $item)
                                <tr>
                                    <td>{{ $index+1 }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->details }}</td>
                                    <td> @can('book category status change')<a href="{{ url('bookcategories/'.$item->id.'/status/change') }}" ><span class="badge bg-{{($item->status == 1) ? 'success' : 'danger'}}">{{($item->status == 1) ? 'Active' : 'Inactive'}}</span></a>@endcan</td>
                                    <td>
                                        @can('update office')
                                        <a href="{{ url('bookcategories/'.$item->id.'/edit') }}" class="btn btn-success">Edit</a>
                                        @endcan

                                        @can('delete office')
                                        <a href="{{ url('bookcategories/'.$item->id.'/delete') }}" class="btn btn-danger mx-2">Delete</a>
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

@endsection