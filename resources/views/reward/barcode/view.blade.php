@extends('layouts.app')


@section('content')
<div class="container mt-5">
        <div class="row">
            <div class="col-md-12">

                <div class="card data-card">
                    <div class="card-header">
                        <h4 class="d-flex">Qrcode detail
                            <a href="{{ route('reward.retailer.barcode.index') }}" class="btn btn-cta ms-auto">Back</a>
                        
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h3 class="text-muted">{{ $data->code ?? ''}}</h3>
                                {{-- <h6>{{ $data->name }}</h6> --}}
                            </div>
                            <div class="col-md-4 text-end">
                                @if ($data->end_date < \Carbon\Carbon::now() )
                                <h3 class="text-danger mt-3 fw-bold">EXPIRED</h3>
                                @endif
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <p class="small text-muted mt-4 mb-2">Details</p>
                                <table class="">
                                    <tr>
                                                    <td class="text-muted">Brand: </td>
                                                    <td>
                                                    @php
                                                

                                                            $brandMap = [
                                                                1 => 'ONN',
                                                                2 => 'PYNK',
                                                                3 => 'Both',
                                                            ];

                                                        // Collect brand IDs from items (avoid duplicates)
                                                        $brands = [$data->brand];
                                                        
                                                        // Determine brand permissions
                                                        if (in_array(3, $brands)) {
                                                            // If any brand is "Both"
                                                            $brandPermissions = 'Both';
                                                        } elseif (in_array(1, $brands) && in_array(2, $brands)) {
                                                            // If both ONN and PYNK exist
                                                            $brandPermissions = 'Both';
                                                        } elseif (in_array(1, $brands)) {
                                                            $brandPermissions = 'ONN';
                                                        } elseif (in_array(2, $brands)) {
                                                            $brandPermissions = 'PYNK';
                                                        } else {
                                                            // Fallback for unexpected values
                                                            $brandPermissions = collect($brands)
                                                                ->map(fn($b) => $brandMap[$b] ?? 'Unknown')
                                                                ->implode(', ');
                                                        }

                                                    @endphp

                                                        {{ $brandPermissions ?? '' }}
                                                    </td>
                                                </tr>
                                    <tr>
                                        <td class="text-muted">Points: </td>
                                        <td>{{$data->type == 1 ? $data->amount.' ' : ' '. $data->amount}}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Max time usage : </td>
                                        <td>{{$data->max_time_of_use ?? ''}}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Max time usage for single user :  </td>
                                        <td>{{$data->max_time_one_can_use ?? ''}}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">No of usage : </td>
                                        <td>{{$data->no_of_usage ?? ''}}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Start date: </td>
                                        <td>{{ date('j M Y h:m A', strtotime($data->start_date)) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">End date: </td>
                                        <td>{{ date('j M Y h:m A', strtotime($data->end_date)) }}</td>
                                    </tr>
                                </table>

                                <hr>

                                <p class="small text-muted mt-4 mb-2">QRcode Usage</p>

                                <table class="table table-sm">
                                    <tr>
                                        <thead>
                                            <th>#SR</th>
                                            <th> Points</th>
                                            <th>Scanned points</th>
                                            <th>User details</th>
                                            <th>Time</th>
                                        </thead>
                                    </tr>
                                    @forelse ($usage as $usageKey => $usageValue)
                                    <tr>
                                        <td>{{$usageKey+1}}</td>
                                        <td>{{$usageValue->amount ?? ''}}</td>
                                        <td>{{$usageValue->amount ?? ''}}</td>
                                        <td>
                                            @if($usageValue->user_id != 0)
                                                <p colspan="100%" class="small text-dark">{{$usageValue->users->owner_name?? ''}}</p>
                                                <p colspan="100%" class="small text-muted">{{$usageValue->users->store_name ?? ''}}</p>
                                            @endif
                                            <p class="small mb-0">{{$usageValue->users->email ?? ''}} </p>
                                            <p class="small mb-0">{{$usageValue->users->contact ?? ''}} </p>
                                            <p class="small mb-0">{{$usageValue->users->unique_code ?? ''}} </p>
                                        </td>
                                        <td>{{ date('j M Y H:i a', strtotime($usageValue->created_at)) }}</td>

                                    </tr>
                                    @empty
                                    <tr> <td colspan="9" class="text-center">No record found</td></tr>
                                    @endforelse
                                </table>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>

@endsection

@section('script')
<script src="{{ asset('admin/js/printThis.js') }}"></script>
<script>
 $('#basic').on("click", function () {
      $('.print-code').printThis();
    });
</script>
@endsection
