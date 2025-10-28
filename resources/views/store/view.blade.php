@extends('layouts.app')

@section('page', '')

@section('content')

@php
     $brandMap = [1 => 'ONN', 2 => 'PYNK', 3 => 'Both'];
    $brandPermissions = $brandMap[$data->brand] ?? 'Unknown';
     // Logged-in user permission (fetched from user_permission_categories table)
    $userPermission = \App\Models\UserPermissionCategory::where('user_id', auth()->id())
        ->value('brand'); // assuming column name is 'brand' in user_permission_categories

    $userBrandPermission = $brandMap[$userPermission] ?? 'Unknown';
       $store_name = $data->store_name;
       $area = $data->area;
	//$name=$data->user->name;
        $displayASEName = '';
        foreach(explode(',',$data->user_id) as $aseKey => $aseVal) 
        {
            //dd($distVal);
            $catDetails = DB::table('employees')->where('id', $aseVal)->get();
			
			if(count($catDetails)>0){
				$displayASEName .= $catDetails[0]->name.',';
			}else{
				$displayASEName .= '';
			}
            
        }

        $moreinformation = \App\Models\Team::where('store_id', $data->id)->first();
            $distributor = $moreinformation->distributor_id;
            
            $displayDistName = '';
            foreach(explode(',',$moreinformation->distributor_id) as $distKey => $distVal) 
            {
                //dd($distVal);
                $catDetails = \App\Models\Distributor::where('id', $distVal)->get();
        
                if(count($catDetails)>0){
                    $displayDistName .=  $catDetails[0]->name.',';
                }else{
                    $displayDistName .= '';
                }
            
            
            }

            $vp = $moreinformation->vp_id;
            
            $displayVPName = '';
            foreach(explode(',',$moreinformation->vp_id) as $distKey => $distVal) 
            {
                //dd($distVal);
                $catDetails = \App\Models\Employee::where('id', $distVal)->get();
        
                if(count($catDetails)>0){
                    $displayVPName .=  $catDetails[0]->name.',';
                }else{
                    $displayVPName .= '';
                }
            
            
            }

            $rsm = $moreinformation->rsm_id;
            
            $displayRSMName = '';
            foreach(explode(',',$moreinformation->rsm_id) as $distKey => $distVal) 
            {
                //dd($distVal);
                $catDetails = \App\Models\Employee::where('id', $distVal)->get();
        
                if(count($catDetails)>0){
                    $displayRSMName .=  $catDetails[0]->name.',';
                }else{
                    $displayRSMName .= '';
                }
            
            
            }

            $asm = $moreinformation->asm_id;
            
            $displayASMName = '';
            foreach(explode(',',$moreinformation->asm_id) as $distKey => $distVal) 
            {
                //dd($distVal);
                $catDetails = \App\Models\Employee::where('id', $distVal)->get();
        
                if(count($catDetails)>0){
                    $displayASMName .=  $catDetails[0]->name.',';
                }else{
                    $displayASMName .= '';
                }
            
            
            }
     $duplicateStore=App\Models\Store::where('owner_name',$data->owner_name)->where('owner_lname',$data->owner_lname)->where('gst_no',$data->gst_no)->count();
  
@endphp

<div class="container mt-5">
    <div class="row">
        <div class="col-md-12">

            {{-- Error Messages --}}
            @if ($errors->any())
                <div class="alert alert-warning">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card data-card">
                    <div class="card-header">
                    
                     <h4 class="d-flex">Store Detail
                        <a href="{{ url('stores') }}" class="btn btn-cta ms-auto">Back</a>
                        @can('update employee')
                            <a href="{{ url('stores/'.$data->id.'/edit') }}" class="btn btn-cta">Edit</a>
                        @endcan
                    </h4>
                </div>

                <div class="card-body">
                    
                    {{-- Image Section --}}
                    <div class="row mb-4">
                        <div class="col-md-6 text-center mb-3">
                            <p class=" text-muted mb-1">Store Image</p>
                            @if(!empty($data->image))
                            <img src="{{ asset($data->image) }}" class="img-fluid rounded border" alt="">
                            @endif
                        </div>
                        <div class="col-md-6 text-center mb-3">
                            <p class=" text-muted mb-1">PAN Image</p>
                            <img src="{{ asset($data->pan) }}" class="img-fluid rounded border" alt="">
                        </div>
                    </div>

                    {{-- Store Info --}}
                    <h5 class="text-primary border-bottom pb-2">Store Information</h5>
                    <div class="row">
                        <div class="col-md-3"><strong>Brand:</strong> {{ $brandPermissions }}</div>
                        <div class="col-md-3 mb-3"><strong>Store Name:</strong> {{ $data->name ?? 'NA' }}</div>
                        <div class="col-md-3 mb-3"><strong>Firm Name:</strong> {{ $data->bussiness_name ?? 'NA' }}</div>
                        <div class="col-md-3 mb-3"><strong>GST Number:</strong> {{ $data->gst_no ?? 'NA' }}</div>
                        <div class="col-md-3 mb-3"><strong>PAN Number:</strong> {{ $data->pan_no ?? 'NA' }}</div>
                        <div class="col-md-3 mb-3"><strong>Store OCC Number:</strong> {{ $data->store_OCC_number ?? 'NA' }}</div>
                    </div>

                    {{-- Manager Info --}}
                    <h5 class="text-primary border-bottom pb-2 mt-4">Manager Information</h5>
                    <div class="row">
                        <div class="col-md-4 mb-3"><strong>Distributor:</strong> {{ substr($displayDistName,0,-1) ?: 'NA' }}</div>
                        <div class="col-md-4 mb-3"><strong>VP:</strong> {{ substr($displayVPName,0,-1) ?: 'NA' }}</div>
                        <div class="col-md-4 mb-3"><strong>RSM:</strong> {{ substr($displayRSMName,0,-1) ?: 'NA' }}</div>
                        <div class="col-md-4 mb-3"><strong>ASM:</strong> {{ substr($displayASMName,0,-1) ?: 'NA' }}</div>
                        <div class="col-md-4 mb-3"><strong>ASE / Created By:</strong> {{ substr($displayASEName,0,-1) ?: 'NA' }}</div>
                    </div>

                    {{-- Owner Info --}}
                    <h5 class="text-primary border-bottom pb-2 mt-4">Owner Information</h5>
                    <div class="row">
                        <div class="col-md-4 mb-3"><strong>Owner First Name:</strong> {{ $data->owner_name ?? 'NA' }}</div>
                        <div class="col-md-4 mb-3"><strong>Owner Last Name:</strong> {{ $data->owner_lname ?? 'NA' }}</div>
                        <div class="col-md-4 mb-3"><strong>Date of Birth:</strong> {{ $data->date_of_birth ?? 'NA' }}</div>
                        <div class="col-md-4 mb-3"><strong>Date of Anniversary:</strong> {{ $data->date_of_anniversary ?? 'NA' }}</div>
                    </div>

                    {{-- Contact Info --}}
                    <h5 class="text-primary border-bottom pb-2 mt-4">Contact Information</h5>
                    <div class="row">
                        <div class="col-md-4 mb-3"><strong>Contact:</strong> {{ $data->contact ?? 'NA' }}</div>
                        <div class="col-md-4 mb-3"><strong>Whatsapp:</strong> {{ $data->whatsapp ?? 'NA' }}</div>
                        <div class="col-md-4 mb-3"><strong>Email:</strong> {{ $data->email ?? 'NA' }}</div>
                    </div>

                    {{-- Address Info --}}
                    <h5 class="text-primary border-bottom pb-2 mt-4">Address Information</h5>
                    <div class="row">
                        <div class="col-md-3 mb-3"><strong>Address:</strong> {{ $data->address ?? 'NA' }}</div>
                        <div class="col-md-3 mb-3"><strong>City:</strong> {{ $data->city ?? 'NA' }}</div>
                        
                        <div class="col-md-3 mb-3"><strong>State:</strong> {{ $data->state->name ?? 'NA' }}</div>
                        <div class="col-md-3 mb-3"><strong>Area:</strong> {{ $data->area->name ?? 'NA' }}</div>
                        <div class="col-md-3 mb-3"><strong>Pincode:</strong> {{ $data->pin ?? 'NA' }}</div>
                    </div>

                    {{-- Contact Person Info --}}
                    <h5 class="text-primary border-bottom pb-2 mt-4">Contact Person Information</h5>
                    <div class="row">
                        <div class="col-md-4 mb-3"><strong>Full Name:</strong> {{ trim(($data->contact_person ?? '').' '.($data->contact_person_lname ?? '')) ?: 'NA' }}</div>
                        <div class="col-md-4 mb-3"><strong>Contact:</strong> {{ $data->contact_person_phone ?? 'NA' }}</div>
                        <div class="col-md-4 mb-3"><strong>Whatsapp:</strong> {{ $data->contact_person_whatsapp ?? 'NA' }}</div>
                        <div class="col-md-4 mb-3"><strong>Date of Birth:</strong> {{ $data->contact_person_date_of_birth ?? 'NA' }}</div>
                        <div class="col-md-4 mb-3"><strong>Date of Anniversary:</strong> {{ $data->contact_person_date_of_anniversary ?? 'NA' }}</div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>


@endsection
