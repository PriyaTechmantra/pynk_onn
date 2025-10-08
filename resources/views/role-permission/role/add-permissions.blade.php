@extends('layouts.app')

@section('content')

<div class="container mt-5">
        <div class="row">
            <div class="col-md-12">

                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif

                <div class="card data-card">
                    <div class="card-header">
                        <h4 class="d-flex align-items-center">
                            Role : {{ucfirst( $role->name) }}
                            <p>&nbsp;&nbsp;&nbsp;(Add/Edit Permission)</p>
                            <a href="{{ url('roles') }}" class="btn btn-cta ms-auto">Back</a>
                        </h4>
                        
                    </div>
                    <div class="card-body" style="max-width: 100%; overflow: auto;">
                        <form action="{{ url('roles/'.$role->id.'/give-permissions') }}" method="POST" class="data-form">
                            @csrf
                            @method('PUT')
                    
                            <div class="mb-3">
                                @error('permission')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                    
                                    <div class="row">
                                        <!-- Loop through each category -->
                                        @foreach ($permissions as $category => $permission)
                                           @php
                                             if($category=='admin'){
                                              $category='Admin Management';
                                              }elseif($category=='lms'){
                                                 $category='Library Management';
                                              }elseif($category=='fms'){
                                                $category='Facility Management';
                                              }elseif($category=='member'){
                                                $category='Member Management';
                                              }else{
                                                 $category='Cave Management';
                                              }
                                              
                                           @endphp
                                            <div class="col-md-6">
                                                <h5 class="category-title">{{ $category }}</h5>
                                                <!-- Loop through each permission within the category -->
                                                <div class="permissions-list">
                                                    @foreach($permission as $item)
                                                        <div class="form-check">
                                                            <input
                                                                type="checkbox"
                                                                name="permission[]"
                                                                class="form-check-input"
                                                                value="{{ $item->name }}"
                                                                {{ in_array($item->id, $rolePermissions) ? 'checked':'' }}
                                                            />
                                                            <label class="form-check-label">
                                                                {{ucfirst( $item->name) }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                    
                            </div>
                            <div class="text-end mb-3">
                                <button type="submit" class="btn btn-submit">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection