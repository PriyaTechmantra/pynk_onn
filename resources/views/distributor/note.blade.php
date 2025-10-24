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
                        <h4>Distributor Note

                            <a href="{{ route('distributors.note.exportCSV', request()->all()) }}" class="btn btn-sm btn-cta float-end" data-bs-toggle="tooltip" title="Export data in CSV">
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
                                    <form action="{{url('distributors/note')}}">
                                        <div class="row">
                                            <div class="col-2">
                                                    <select name="user_type" class="form-control form-control-sm">
                                                        <option value="">User Type</option>
                                                        <option value="1" {{ app('request')->input('user_type') == 1 ? 'selected' : '' }}>VP</option>
                                                        <option value="2" {{ app('request')->input('user_type') == 2 ? 'selected' : '' }}>RSM</option>
                                                        <option value="3" {{ app('request')->input('user_type') == 3 ? 'selected' : '' }}>ASM</option>
                                                        <option value="4" {{ app('request')->input('user_type') == 4 ? 'selected' : '' }}>ASE</option>
                                                    </select>
                                            </div>
                                            <div class="col-2">
                                                <select name="user_name" class="form-control form-control-sm">
                                                    <option value="">User Name</option>
                                                    @foreach($users as $user)
                                                        <option value="{{ $user->id }}" {{ request('user_name') == $user->id ? 'selected' : '' }}>
                                                            {{ $user->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-2">
                                                <select name="distributor_name" class="form-control form-control-sm">
                                                    <option value="">Distributor</option>
                                                    @foreach($distributors as $distributor)
                                                        <option value="{{ $distributor->id }}" {{ request('distributor_name') == $distributor->id ? 'selected' : '' }}>
                                                            {{ $distributor->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            
                                            <div class="col-2">
                                                 
                                                <select name="brand_selection" class="form-control form-control-sm">
                                                    <option value="">Select Brand</option>
                                                    <option value="1" {{ app('request')->input('brand_selection') == 1 ? 'selected' : '' }}>Onn</option>
                                                    <option value="2" {{ app('request')->input('brand_selection') == 2 ? 'selected' : '' }}>Pynk</option>
                                                    <option value="3" {{ app('request')->input('brand_selection') == 3 ? 'selected' : '' }}>Both</option>
                                                </select>
                                            </div>
                                            <div class="col-2">
                                                <input type="search" name="term" id="term" class="form-control form-control-sm" placeholder="Comment" value="{{app('request')->input('term')}}" autocomplete="off">
                                            </div>

                                            <div class="col-2 text-end">
                                                    <button type="submit" class="btn btn-sm btn-cta">
                                                        Filter
                                                    </button>
                    
                                                    <a href="{{ url()->current() }}" class="btn btn-sm btn-cta" data-bs-toggle="tooltip" title="Clear Filter">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
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
                            <table class="table no-sticky">
                                 <thead>
                                    <tr>
                                        <th class="index-col">#</th>
                                        <th>User</th>
                                        <th>Distributor</th>
                                        <th>Comment</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($data as $index => $item)
                                    @php
                                    if (!empty($_GET['is_current'])) {
                                        if ($_GET['is_current'] == 'active') {
                                            if ($item->is_current == 0) continue;
                                        } else {
                                            if ($item->is_current == 1) continue;
                                        }
                                    }
                                    @endphp
                                    <tr>
                                        <td>{{ $index+1 }}</td>
                                        <td> {{ optional($item->user)->name ?? '' }}</td>
                                        <td>{{ optional($item->distributor)->name ?? '' }}</td>
                                        <td>{{$item->comment}}</td>
                                        <td><div class="text-muted">{{ date('d M Y', strtotime($item->date)) }}&nbsp;{{$item->time}}</div></td>
                                    
                                    </tr>
                                    @empty  
                                    <tr><td colspan="100%" class="small text-muted">No data found</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-end">
                            {{$data->appends($_GET)->links()}}
                        </div>
                    </div>
                </div>
            </div>
        </div>  
</div>
@endsection
@section('script')


<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>


    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });
    </script>
<script>
    $('select[name="zsm"]').on('change', (event) => {
        var value = $('select[name="zsm"]').val();

        $.ajax({
            url: '{{url("/")}}/admin/rsm/list/zsmwise/'+value,
            method: 'GET',
            success: function(result) {
                var content = '';
                var slectTag = 'select[name="rsm"]';
                var displayCollection =  "All";

                content += '<option value="" selected>'+displayCollection+'</option>';
                $.each(result.data, (key, value) => {
                    content += '<option value="'+value.rsm.id+'">'+value.rsm.name+'</option>';
                });
                $(slectTag).html(content).attr('disabled', false);
            }
        });
    });
</script>

<script>
    $('select[name="rsm"]').on('change', (event) => {
        var value = $('select[name="rsm"]').val();

        $.ajax({
            url: '{{url("/")}}/admin/sm/list/rsmwise/'+value,
            method: 'GET',
            success: function(result) {
                var content = '';
                var slectTag = 'select[name="sm"]';
                var displayCollection =  "All";

                content += '<option value="" selected>'+displayCollection+'</option>';
                $.each(result.data, (key, value) => {
                    content += '<option value="'+value.sm.id+'">'+value.sm.name+'</option>';
                });
                $(slectTag).html(content).attr('disabled', false);
            }
        });
    });
</script>
<script>
    $('select[name="sm"]').on('change', (event) => {
        var value = $('select[name="sm"]').val();

        $.ajax({
            url: '{{url("/")}}/admin/asm/list/smwise/'+value,
            method: 'GET',
            success: function(result) {
                var content = '';
                var slectTag = 'select[name="asm"]';
                var displayCollection =  "All";

                content += '<option value="" selected>'+displayCollection+'</option>';
                $.each(result.data, (key, value) => {
                    content += '<option value="'+value.asm.id+'">'+value.asm.name+'</option>';
                });
                $(slectTag).html(content).attr('disabled', false);
            }
        });
    });
</script>
<script>
    $('select[name="asm"]').on('change', (event) => {
        var value = $('select[name="asm"]').val();

        $.ajax({
            url: '{{url("/")}}/admin/ase/list/asmwise/'+value,
            method: 'GET',
            success: function(result) {
                var content = '';
                var slectTag = 'select[name="ase"]';
                var displayCollection =  "All";

                content += '<option value="" selected>'+displayCollection+'</option>';
                $.each(result.data, (key, value) => {
                    content += '<option value="'+value.ase.id+'">'+value.ase.name+'</option>';
                });
                $(slectTag).html(content).attr('disabled', false);
            }
        });
    });
</script>
@endsection