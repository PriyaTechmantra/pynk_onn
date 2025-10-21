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
                        <h4 class="d-flex">News Detail
                            <a href="{{ url('news') }}" class="btn btn-cta ms-auto">Back</a>
                             <a href="{{ route('news.edit',$data->id) }}" class="btn btn-cta">
                                Edit
                            </a>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-6 col-lg-8 col-12">
                                <div class="card-body">
                                    
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h3 class="text-dark font-weight-bold">{{ $data->title }}</h3>
                                            <p>Status:<span class="btn {{ $data->status == '1' ? 'btn-success' : 'btn-danger'}}">{{ $data->status == '1' ? 'Active' : 'Inactive'}}</span></p>
                                            @php
                                            $roles = [
                                                1 => 'VP',
                                                2 => 'RSM',
                                                3 => 'ASM',
                                                4 => 'ASE',
                                            ];

                                            $userTypes = is_array($data->user_type) ? $data->user_type : json_decode($data->user_type, true);
                                            $roleNames = [];

                                            if (is_array($userTypes)) {
                                                foreach ($userTypes as $type) {
                                                    $roleNames[] = $roles[$type] ?? $type;
                                                }
                                            }
                                        @endphp

                                        <p class="small">
                                            Access For: {{ implode(', ', $roleNames) ?: 'N/A' }}
                                        </p>
                                            <img src="{{ asset($data->image) }}" alt="" style="height: 150px">
                                            <br>
                                            <a class="btn btn-primary" href="{{ asset($data->pdf) }}" target="_blank">View PDF</a>
                                            <p class="small">Validity: {{ $data->start_date }} - {{ $data->end_date }}</p>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
