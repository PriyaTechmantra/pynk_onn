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
                        <h4>Edit Logs
                             
                            
                        </h4>
                        <div class="search__filter mb-0">
                                    <div class="row">
                                        
                                    </div>
                                    <div class="row">
                                        
                                        <div class="col-12">
                                            <form action="">
                                                <div class="row">
                                                    <div class="col">
                                                        <input type="date" name="date_from" id="term" class="form-control form-control-sm"  value="{{app('request')->input('date_from')}}">
                                                    </div>
                                                     <div class="col">
                                                        <input type="date" name="date_to" id="term" class="form-control form-control-sm"  value="{{app('request')->input('date_to')}}">
                                                    </div>
                                                    
                                                </div>
                                                <div class="row mt-2">
                                                    <div class="col-12 text-end">
                                                        <!--<div class="btn-group books_btn_group">-->
                                                            <button type="submit" class="btn btn-sm btn-cta">
                                                                Filter
                                                            </button>
                            
                                                            <a href="{{ url()->current() }}" class="btn btn-sm btn-cta" data-bs-toggle="tooltip" title="Clear Filter">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                                            </a>
                                                            @can('edit log csv export')
                                                            <a href="{{ route('log.report.export',['date_from'=>$request->date_from,'date_to'=>$request->date_to]) }}" class="btn btn-sm btn-cta ms-auto" data-bs-toggle="tooltip" title="Export data in CSV">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                                                CSV
                                                            </a>
                                                            @endcan
                                                        <!--</div>-->
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
                                     <th>Module</th>
                                    <th>Record Details</th>
                                    
                                    <th>Field</th>
                                    <th>Previous Value</th>
                                   
                                    <th>Updated Value</th>
                                    <th>Updated Date</th>
                                    <th>Updated By</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($data as $index=> $item)
                                  @php
                                    if ($item->table_name && $item->record_id) {
                                        $modelClass = 'App\\Models\\' . ucfirst(Str::camel(Str::singular($item->table_name)));
                                
                                        if (class_exists($modelClass)) {
                                            $record = $modelClass::find($item->record_id);
                                            $item->record_details = $record;
                                           
                                        } else {
                                            $item->record_details = null;
                                        }
                                    }
                                    
                                    if ($item->updated_by && $item->updated_by) {
                                        $modelClass = 'App\\Models\User';
                                
                                        if (class_exists($modelClass)) {
                                            $record = $modelClass::find($item->updated_by);
                                            $item->user_details = $record;
                                           
                                        } else {
                                            $item->user_details = null;
                                        }
                                    }
                                  @endphp
                                <tr>
                                    <td class="index-col">{{ $index+1 }}</td>
                                     
                                    
                                    <td>{{ ucwords(str_replace('_', ' ',$item->table_name)) ??'' }}</td> 
                                    <td>@if($item->record_details)
                                           {{ $item->record_details->name ?? $item->record_details->name ?? 'Details available' }}
                                        @else
                                            Record not found
                                        @endif
                                    </td>
                                     <td>{{ $item->field ??'' }}</td>
                                     <td>{{ $item->old_value ??'' }}</td>
                                     <td>{{ $item->new_value ??'' }}</td>
                                     <td>{{ date('d-m-Y', strtotime($item->created_at)) }}</td>
                                     <td>{{ $item->user_details->name ??'' }}</td>
                                     <td>{{ ucwords($item->action) ??'' }}</td>          
                                </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No record found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        </div>
                         {{ $data->appends($_GET)->render() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection