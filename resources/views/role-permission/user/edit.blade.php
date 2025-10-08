@extends('layouts.app')

@section('content')


<div class="container mt-5">
        <div class="row">
            <div class="col-md-12">

                @if ($errors->any())
                <ul class="alert alert-warning">
                    @foreach ($errors->all() as $error)
                        <li>{{$error}}</li>
                    @endforeach
                </ul>
                @endif

                <div class="card data-card">
                    <div class="card-header">
                        <h4 class="d-flex">Edit User
                            <a href="{{ url('users') }}" class="btn btn-cta ms-auto">Back</a>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-3 col-lg-2 col-12"></div>
                            <div class="col-xl-6 col-lg-8 col-12">
                                <form action="{{ url('users/'.$user->id) }}" method="POST" class="data-form">
                                    @csrf
                                    @method('PUT')
        
                                    <div class="mb-3">
                                        <label for="">Name</label>
                                        <input type="text" name="name" value="{{ $user->name }}" class="form-control" />
                                        @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="">Email</label>
                                        <input type="text" name="email" readonly value="{{ $user->email }}" class="form-control" />
                                    </div>
                                    <div class="mb-3">
                                        <label for="">Password</label>
                                        <input type="text" name="password" class="form-control" />
                                        @error('password') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="">Roles</label>
                                        <select name="roles[]" readonly class="form-control" multiple>
                                            <option value="">Select Role</option>
                                            @foreach ($roles as $role)
                                            @php
                                                if($role=='super-admin'){
                                                    continue;
                                                }
                                            @endphp
                                            <option
                                                value="{{ $role }}"
                                                {{ in_array($role, $userRoles) ? 'selected':'' }}
                                            >
                                                {{ $role }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('roles') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                    @php
                                         $assignedPermissions = DB::table('user_permission_categories')->select('user_permission_categories.*')->join('users','users.id','=','user_permission_categories.user_id')->where('user_permission_categories.user_id', $user->id)->get()->toArray();
                                         $brand = collect($assignedPermissions)->pluck('brand')->toArray();
                                    @endphp
                                        <div class="mb-3">
                                            <!-- Communication Medium -->
                                            <h6>Brand Permission:</h6>
                                            
                                            <div class="form-check">
                                                <input 
                                                    class="form-check-input medium-checkbox" 
                                                    type="checkbox" 
                                                    name="brand" 
                                                    value="1" 
                                                    id="mediumOnn"
                                                    {{ isset($assignedPermissions) && in_array('1', $brand) ? 'checked' : '' }}
                                                    onchange="toggleSelectBox()"
                                                >
                                                <label class="form-check-label" for="mediumOnn">Onn</label>
                                            </div>
                                            <div class="form-check">
                                                <input 
                                                    class="form-check-input medium-checkbox" 
                                                    type="checkbox" 
                                                    name="brand" 
                                                    value="2" 
                                                    id="mediumPynk"
                                                    {{ isset($assignedPermissions) && in_array('2', $brand) ? 'checked' : '' }}
                                                    onchange="toggleSelectBox()"
                                                >
                                                <label class="form-check-label" for="mediumPynk">Pynk</label>
                                            </div>
                                            
                                            <div class="form-check">
                                                <input 
                                                    class="form-check-input medium-checkbox" 
                                                    type="checkbox" 
                                                    name="brand" 
                                                    value="3" 
                                                    id="mediumBoth"
                                                    {{ isset($assignedPermissions) && in_array('3', $brand) ? 'checked' : '' }}
                                                    onchange="toggleSelectBox()"
                                                >
                                                <label class="form-check-label" for="mediumBoth">Both</label>
                                            </div>
                                        </div>
                                    <div class="text-end mb-3">
                                        <button type="submit" class="btn btn-submit">Update</button>
                                    </div>
                                </form>
                            </div>
                            <div class="col-xl-3 col-lg-2 col-12"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection