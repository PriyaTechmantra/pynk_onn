@php
       
   
         $displayASEName = '';
        foreach(explode(',',$data->user_id) as $aseKey => $aseVal) 
        {
            $catDetails = DB::table('employees')->where('id', $aseVal)->get();
            //dd($distVal);
			if(count($catDetails)>0){
				$displayASEName .= $catDetails[0]->name.' ';
			}else{
				$displayASEName .= '';
			}
            
        }
   
        $moreinformation = \App\Models\Team::where('store_id', $data->id)->with('vp','rsm','asm','distributor')->first();
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
            $user = auth()->user();
            $userBrands = DB::table('user_permission_categories')
                ->where('user_id', Auth::id())
                ->pluck('brand')
                ->toArray();
        
            $brandsToShow = [];

            if (in_array(3, $userBrands) || (in_array(1, $userBrands) && in_array(2, $userBrands))) {
                // Both brands access
                $brandsToShow = [1, 2, 3];
            } elseif (in_array(1, $userBrands)) {
                $brandsToShow = [1];
            } elseif (in_array(2, $userBrands)) {
                $brandsToShow = [2];
            }
  
   $allVps=DB::table('employees')->whereIn('brand',$brandsToShow)->where('type',1)->get();
   $allRSM=DB::table('employees')->whereIn('brand',$brandsToShow)->where('type',2)->get();
   $allASM=DB::table('employees')->whereIn('brand',$brandsToShow)->where('type',3)->get();
@endphp

@extends('layouts.app')

@section('page', '')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@section('content')
<style>
    input::file-selector-button {
        display: none;
    }
</style>

<section>
    <div class="row">
        <div class="col-sm-12">
            
            <div class="card">
               <div class="card-header">
                        <h4 class="d-flex">Edit Store
                            <a href="{{ url('stores') }}" class="btn btn-danger ms-auto">Back</a>
                        </h4>
                    </div>
                <div class="card-body">
                     
                    <form method="POST" action="{{ url('stores/'.$data->id) }}" enctype="multipart/form-data">@csrf
                                    @method('PUT')
                        <div class="row mb-2">
                            <div class="col-12">
                                <p class="small text-muted mb-2">Manager details</p>
                            </div>
                            <div class="col-md-4">
								
                                <div class="form-group">
                                     <label for="distributor_id">Distributor *</label>
                                    <div class="form-floating mb-3">
                                        <p class="small text-danger">({{substr($displayDistName,0,-1)}})</p>
                                        <select class="form-select select2" id="distributor_id"   multiple="multiple" name="distributor_id[]" aria-label="Floating label select example">
                                            <option value="" selected disabled>Select</option>
                                            @foreach ($allDistributors as $item)
                                            @php
                                                $cat = explode(",", $moreinformation->distributor->id);
                                                $isSelected = in_array($item->name,$cat) ? "selected='selected'" : "";
                                            @endphp
                                                <option value="{{$item->id}}" {{is_array($cat) && in_array($item->id, $cat) ? 'selected' : '' }}>{{$item->name}}({{$item->states->name}})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('distributor_id') <p class="small text-danger">{{$message}}</p> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
									<label for="ase">ASE *</label>
                                    <div class="form-floating mb-3">
										<p class="small text-danger">({{substr($displayASEName,0,-1)}})</p>
                                        <select class="form-select select2" id="ase" name="ase[]" multiple="multiple" aria-label="Floating label select example">
                                            <option value="" selected disabled>Select</option>
                                            @foreach ($allASEs as $item)
											@php
                                                $userD = explode(",", $data->user_id);
                                                $isSelected = in_array($item->id,$userD) ? "selected='selected'" : "";
                                            @endphp
                                                <option value="{{$item->id}}" {{is_array($userD) && in_array($item->id, $userD) ? 'selected' : '' }}>{{$item->name}}({{$item->stateDetail->name}})</option>
                                            @endforeach
                                        </select>
                                        
                                    </div>
                                    
                                </div>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-12">
                                <p class="small text-muted mb-2">Store information</p>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" id="name" name="name" placeholder="store name" value="{{ old('name') ? old('name') : $data->name }}">
                                        <label for="name">Store name *</label>
                                    </div>
                                    @error('name') <p class="small text-danger">{{$message}}</p> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" id="bussiness_name" name="bussiness_name" placeholder="Distributor name" value="{{ old('bussiness_name') ? old('bussiness_name') : $data->bussiness_name }}">
                                        <label for="bussiness_name">Firm name *</label>
                                    </div>
                                    @error('bussiness_name') <p class="small text-danger">{{$message}}</p> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" id="gst_no" name="gst_no" placeholder="Distributor name" value="{{ old('gst_no') ? old('gst_no') : $data->gst_no }}">
                                        <label for="gst_no">GST number</label>
                                    </div>
                                    @error('gst_no') <p class="small text-danger">{{$message}}</p> @enderror
                                </div>
                            </div>
                             <div class="col-md-4">
                                <div class="form-group">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" id="pan_no" name="pan_no" placeholder="Pan no" value="{{ old('pan_no') ? old('pan_no') : $data->pan_no }}">
                                        <label for="gst_no">PAN number</label>
                                    </div>
                                    @error('pan_no') <p class="small text-danger">{{$message}}</p> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="d-flex">
                                        @if (!empty($data->pan) || file_exists($data->pan))
                                            <img src="{{ asset($data->pan) }}" alt="" class="img-thumbnail" style="height: 52px;margin-right: 10px;">
                                        @endif
                                        <div class="form-floating mb-3">
                                            <input type="file" class="form-control" id="pan" name="pan" placeholder="pan" value="">
                                            <label for="image">Pan</label>
                                        </div>
                                    </div>
                                    @error('pan') <p class="small text-danger">{{$message}}</p> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="d-flex">
                                        @if (!empty($data->image) || file_exists($data->image))
                                            <img src="{{ asset($data->image) }}" alt="" class="img-thumbnail" style="height: 52px;margin-right: 10px;">
                                        @endif
                                        <div class="form-floating mb-3">
                                            <input type="file" class="form-control" id="image" name="image" placeholder="Distributor name" value="">
                                            <label for="image">Image</label>
                                        </div>
                                    </div>
                                    @error('image') <p class="small text-danger">{{$message}}</p> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-12">
                                <p class="small text-muted mb-2">Owner information</p>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" id="owner_name" name="owner_name" placeholder="name" value="{{ old('owner_name') ? old('owner_name') : $data->owner_name }}">
                                        <label for="owner_name">Owner first name *</label>
                                    </div>
                                    @error('owner_name') <p class="small text-danger">{{$message}}</p> @enderror
                                </div>
                            </div>
							<div class="col-md-4">
                                <div class="form-group">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" id="owner_lname" name="owner_lname" placeholder="name" value="{{ old('owner_lname') ? old('owner_lname') : $data->owner_lname }}">
                                        <label for="owner_lname">Owner last name *</label>
                                    </div>
                                    @error('owner_lname') <p class="small text-danger">{{$message}}</p> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="form-floating mb-3">
                                        <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" placeholder="name@example.com" value="{{ old('date_of_birth') ? old('date_of_birth') : $data->date_of_birth }}">
                                        <label for="date_of_birth">Date of Birth</label>
                                    </div>
                                    @error('date_of_birth') <p class="small text-danger">{{$message}}</p> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="form-floating mb-3">
                                        <input type="date" class="form-control" id="date_of_anniversary" name="date_of_anniversary" placeholder="name@example.com" value="{{ old('date_of_anniversary') ? old('date_of_anniversary') : $data->date_of_anniversary }}">
                                        <label for="date_of_anniversary">Date of Anniversary</label>
                                    </div>
                                    @error('date_of_anniversary') <p class="small text-danger">{{$message}}</p> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-12">
                                <p class="small text-muted mb-2">Contact information</p>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="form-floating mb-3">
                                        <input type="number" class="form-control" id="contact" name="contact" placeholder="name@example.com" value="{{ old('contact') ? old('contact') : $data->contact }}">
                                        <label for="contact">Contact *</label>
                                    </div>
                                    @error('contact') <p class="small text-danger">{{$message}}</p> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="form-floating mb-3">
                                        <input type="number" class="form-control" id="whatsapp" name="whatsapp" placeholder="name@example.com" value="{{ old('whatsapp') ? old('whatsapp') : $data->whatsapp }}">
                                        <label for="whatsapp">Whatsapp</label>
                                    </div>
                                    @error('whatsapp') <p class="small text-danger">{{$message}}</p> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="form-floating mb-3">
                                        <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" value="{{ old('email') ? old('email') : $data->email }}">
                                        <label for="email">Email</label>
                                    </div>
                                    @error('email') <p class="small text-danger">{{$message}}</p> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-12">
                                <p class="small text-muted mb-2">Location details</p>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" id="address" name="address" placeholder="name@example.com" value="{{ old('address') ? old('address') : $data->address }}">
                                        <label for="address">Address *</label>
                                    </div>
                                    @error('address') <p class="small text-danger">{{$message}}</p> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" id="city" name="city" placeholder="name@example.com" value="{{ old('city') ? old('city') : $data->city }}">
                                        <label for="city">City *</label>
                                    </div>
                                    @error('city') <p class="small text-danger">{{$message}}</p> @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="form-floating mb-3">
                                        <select class="form-select" id="state" name="state" aria-label="Floating label select example">
                                            <option value="" selected disabled>Select</option>
                                            @foreach ($state as $index => $item)
                                                <option value="{{ $item->id }}" {{ ($data->state_id == $item->id) ? 'selected' : '' }}>{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                        <label for="state">State *</label>
                                    </div>
                                   
                                    @error('state') <p class="small text-danger">{{$message}}</p> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="form-floating mb-3">
                                        <select class="form-select" id="area" name="area" aria-label="Floating label select example" readonly>
                                            <option value="{{$data->area->id}}" selected>{{$data->area->name}}</option>
                                        </select>
                                        <label for="area">Area *</label>
                                    </div>
                                    
                                    @error('area') <p class="small text-danger">{{$message}}</p> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="form-floating mb-3">
                                        <input type="number" class="form-control" id="pin" name="pin" placeholder="name@example.com" value="{{ old('pin') ? old('pin') : $data->pin }}">
                                        <label for="pin">Pincode *</label>
                                    </div>
                                    @error('pin') <p class="small text-danger">{{$message}}</p> @enderror
                                </div>
                            </div>
                            
                        </div>

                        <div class="row mb-2">
                            <div class="col-12">
                                <p class="small text-muted mb-2">Contact person information</p>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" id="contact_person" name="contact_person" placeholder="name@example.com" value="{{ old('contact_person') ? old('contact_person') : $data->contact_person }}">
                                        <label for="contact_person">First name *</label>
                                    </div>
                                    @error('contact_person') <p class="small text-danger">{{$message}}</p> @enderror
                                </div>
                            </div>
							<div class="col-md-4">
                                <div class="form-group">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" id="contact_person_lname" name="contact_person_lname" placeholder="name" value="{{ old('contact_person_lname') ? old('contact_person_lname') : $data->contact_person_lname }}">
                                        <label for="contact_person_lname">Last name *</label>
                                    </div>
                                    @error('contact_person_lname') <p class="small text-danger">{{$message}}</p> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="form-floating mb-3">
                                        <input type="number" class="form-control" id="contact_person_phone" name="contact_person_phone" placeholder="name@example.com" value="{{ old('contact_person_phone') ? old('contact_person_phone') : $data->contact_person_phone }}">
                                        <label for="contact_person_phone">Contact *</label>
                                    </div>
                                    @error('contact_person_phone') <p class="small text-danger">{{$message}}</p> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="form-floating mb-3">
                                        <input type="number" class="form-control" id="contact_person_whatsapp" name="contact_person_whatsapp" placeholder="name@example.com" value="{{ old('contact_person_whatsapp') ? old('contact_person_whatsapp') : $data->contact_person_whatsapp }}">
                                        <label for="contact_person_whatsapp">Whatsapp</label>
                                    </div>
                                    @error('contact_person_whatsapp') <p class="small text-danger">{{$message}}</p> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="form-floating mb-3">
                                        <input type="date" class="form-control" id="contact_person_date_of_birth" name="contact_person_date_of_birth" placeholder="name@example.com" value="{{ old('contact_person_date_of_birth') ? old('contact_person_date_of_birth') : $data->contact_person_date_of_birth }}">
                                        <label for="contact_person_date_of_birth">Date of Birth</label>
                                    </div>
                                    @error('contact_person_date_of_birth') <p class="small text-danger">{{$message}}</p> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="form-floating mb-3">
                                        <input type="date" class="form-control" id="contact_person_date_of_anniversary" name="contact_person_date_of_anniversary" placeholder="name@example.com" value="{{ old('contact_person_date_of_anniversary') ? old('contact_person_date_of_anniversary') : $data->contact_person_date_of_anniversary }}">
                                        <label for="contact_person_date_of_anniversary">Date of Anniversary</label>
                                    </div>
                                    @error('contact_person_date_of_anniversary') <p class="small text-danger">{{$message}}</p> @enderror
                                </div>
                            </div>
                        </div>

						                         <div class="row mb-2">
                            <div class="col-12">
                                <p class="small text-muted mb-2">Team information</p>
                            </div>
						    <div class="col-md-4">
								
                                <div class="form-group">
                                     <label for="distributor_name">VP *</label>
                                    <div class="form-floating mb-3">
                                        <p class="small text-danger"></p>
                                        <select class="form-select select2" id="vp"   multiple="multiple" name="vp[]" aria-label="Floating label select example">
                                            <option value="" selected disabled>Select</option>
                                            @foreach ($allVps as $vp)
                                            @php
                                                $cat = explode(",", strtoupper($moreinformation->vp_id));
                                                $isSelected = in_array($vp->id,$cat) ? "selected='selected'" : "";
                                            @endphp
                                                <option value="{{$vp->id}}" {{is_array($cat) && in_array(strtoupper($vp->id), $cat) ? 'selected' : '' }}>{{$vp->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('vp_id') <p class="small text-danger">{{$message}}</p> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
									<label for="ase">RSM *</label>
                                    <div class="form-floating mb-3">
										<p class="small text-danger"></p>
                                        <select class="form-select select2" id="rsm" name="rsm" multiple="multiple" aria-label="Floating label select example">
                                            <option value="" selected disabled>Select</option>
                                            @foreach ($allRSM as $rsm)
                                            
											@php
                                                $cat = explode(",", strtoupper($moreinformation->rsm_id));
                                                $isSelected = in_array(strtoupper($rsm->id),$cat) ? "selected='selected'" : "";
                                            @endphp
                                                <option value="{{strtoupper($rsm->id)}}" {{is_array($cat) && in_array(strtoupper($rsm->id), $cat) ? 'selected' : '' }}>{{strtoupper($rsm->name)}}</option>
                                            @endforeach
                                        </select>
                                        
                                    </div>
                                    
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
									<label for="ase">ASM *</label>
                                    <div class="form-floating mb-3">
										<p class="small text-danger"></p>
                                        <select class="form-select select2" id="asm" name="asm" multiple="multiple" aria-label="Floating label select example">
                                            <option value="" selected disabled>Select</option>
                                            @foreach ($allASM as $asm)
											@php
                                                $cat = explode(",", strtoupper($moreinformation->asm_id));
                                                $isSelected = in_array($asm->id,$cat) ? "selected='selected'" : "";
                                            @endphp
                                                <option value="{{$asm->id}}" {{is_array($cat) && in_array($asm->id, $cat) ? 'selected' : '' }}>{{$asm->name}}</option>
                                            @endforeach
                                        </select>
                                        
                                    </div>
                                    
                                </div>
                            </div>
                            
                        </div>

                        <div class="row">
                            <div class="col-12">
                                
                                <input type="hidden" name="retailer_list_of_occ_id" value="{{ $moreinformation->id }}">
                                <button type="submit" class="btn btn-danger text-end">Update changes</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>


{{-- <section>
    <div class="row">
        <div class="col-sm-12">
       
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.store.update', $data->id) }}" enctype="multipart/form-data">
                    @csrf
                        <h4 class="page__subtitle">Edit Store</h4>
                        <div class="form-group mb-3">
                            <label class="label-control">Store Name <span class="text-danger">*</span> </label>
                            <input type="text" name="store_name" placeholder="" class="form-control" value="{{ $data->store_name }}">
                            <input type="hidden" name="id" placeholder="" class="form-control" value="{{ $data->id }}">
                            
                            @error('store_name') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>

                        <div class="card">
                            <div class="card-header p-0 mb-3">Image <span class="text-danger">*</span></div>
                            <div class="card-body p-0">
                                <div class="w-100 product__thumb">
                                    <label for="thumbnail"><img id="output" src="{{ asset($data->image) }}" /></label>
                                </div>
                                <input type="file" name="image" id="thumbnail" accept="image/*" onchange="loadFile(event)" class="d-none">
                                <script>
                                    var loadFile = function(event) {
                                        var output = document.getElementById('output');
                                        output.src = URL.createObjectURL(event.target.files[0]);
                                        output.onload = function() {
                                            URL.revokeObjectURL(output.src) // free memory
                                        }
                                    };
                                </script>
                            </div>
                            @error('image') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-danger ms-auto">Update Store</button>
                        </div>
                    </form>
                </div>
            </div>
        </div> 
    </div>
</section> --}}
@endsection

@section('script')
    <script>
    $('select[name="state"]').on('change', (event) => {
        var value = $('select[name="state"]').val();
      
        $.ajax({
            url: '{{url("/")}}/employees/state/'+value,
            method: 'GET',
            success: function(result) {
                var content = '';
                var slectTag = 'select[name="area"]';
                var displayCollection =  "All";

                content += '<option value="" selected>'+displayCollection+'</option>';
                $.each(result.data.area, (key, value) => {
                    content += '<option value="'+value.area_id+'">'+value.area+'</option>';
                });
                $(slectTag).html(content).attr('disabled', false);
            }
        });
    });
</script>
    	<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        <script>
            $(document).ready(function() {
                $('.select2').select2();
            });
        </script>
@endsection