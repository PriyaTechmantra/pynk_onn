@extends('layouts.app')

@section('content')
<style>
    .permission-title {
    font-size: 15px;
    font-weight: 700;
    margin-bottom: 10px;
    padding-bottom: 5px;
    color: #333;
    border-bottom: 1px solid #eee;
}

.custom-checkbox {
    display: flex;
    gap: 8px;
    align-items: center;
    padding: 6px 0;
    font-size: 14px;
    cursor: pointer;
}

.custom-checkbox input {
    width: 16px;
    height: 16px;
    cursor: pointer;
}

.permission-block {
    background: #fafafa;
    padding: 15px;
    border-radius: 8px;
    border: 1px solid #eee;
}

.card {
    border-radius: 12px !important;
}

.card-header h4 {
    font-size: 18px;
    
}

.card-header .btn-cta {
    color: #fff;
    border-radius: 6px;
    background: #dc3545;
    padding: 0.25rem 1rem;
}
</style>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-12">

            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            <div class="card shadow-sm rounded-3">
                <div class="card-header bg-white border-0 pt-4 pb-2">
                    <h4 class="d-flex align-items-center mb-0">
                        <span class="fw-bold">Designation Permission</span>
                        <span class="text-muted ms-2">→ {{ ucfirst($role->name) }}</span>

                        <a href="{{ url('roles') }}" class="btn btn-cta ms-auto">
                           Back
                        </a>
                    </h4>
                </div>

                <div class="card-body">

                    <form action="{{ url('roles/'.$role->id.'/give-permissions') }}" 
                          method="POST">
                        @csrf
                        @method('PUT')

                        <div class="permission-wrapper">

                            @foreach ($permissions as $category => $permission)
                                
                                <div class="permission-block mb-4">

                                    {{-- Category Title --}}
                                    <h5 class="permission-title">
                                        {{ strtoupper($category) }} MANAGEMENT
                                    </h5>

                                    <div class="row">

                                        @foreach ($permission as $item)
                                            <div class="col-md-3 col-6 mb-2">
                                                <label class="custom-checkbox">
                                                    <input 
                                                        type="checkbox"
                                                        name="permission[]"
                                                        value="{{ $item->name }}"
                                                        {{ in_array($item->id, $rolePermissions) ? 'checked' : '' }}
                                                    >
                                                    <span>{{ ucfirst($item->name) }}</span>
                                                </label>
                                            </div>
                                        @endforeach
                                        
                                    </div>
                                </div>
                            @endforeach

                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-submit">
                                Update
                            </button>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>
</div>


@endsection