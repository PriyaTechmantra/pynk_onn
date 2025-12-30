<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Store;
use App\Models\Distributor;
use App\Models\User;
use App\Models\Employee;
use App\Models\State;
use App\Models\Area;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Auth;
class RetailerUserController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        /**
         * STEP 1: Determine which brands this user can see
         */
        $userBrands = DB::table('user_permission_categories')
            ->where('user_id', $user->id)
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

        /**
         * STEP 2: Base query — join teams table for distributor relation
         */
        $query = Store::select('stores.*');

        /**
         * STEP 3: Brand filter
         */
        if ($request->filled('brand')) {
            $query->where(function ($q) use ($request) {
                if ($request->brand == 3) {
                    $q->whereIn('stores.brand', [1, 2, 3]);
                } else {
                    $q->where('stores.brand', $request->brand)
                        ->orWhere('stores.brand', 3);
                }
            });
        } else {
            // Apply user's brand permission if not manually filtered
            if (!empty($brandsToShow)) {
                $query->where(function ($q) use ($brandsToShow) {
                    if (in_array(3, $brandsToShow)) {
                        $q->whereIn('stores.brand', [1, 2, 3]);
                    } else {
                        $q->whereIn('stores.brand', array_merge($brandsToShow, [3]));
                    }
                });
            }
        }

        /**
         * STEP 4: Date range filter
         */
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $from = date('Y-m-d 00:00:00', strtotime($request->date_from));
            $to   = date('Y-m-d 23:59:59', strtotime($request->date_to));
            $query->whereBetween('stores.created_at', [$from, $to]);
        }

        if ($request->filled('status_id')) {
            $query->where('stores.status', $request->status_id === 'active' ? 1 : 0);
        }

        /**
         * STEP 5: Distributor, ASE, State, Area filters
         */
        if ($request->filled('distributor')) {
            $query->whereRaw("find_in_set(?, teams.distributor_id)", [$request->distributor]);
        }

        if ($request->filled('ase')) {
            $query->whereRaw("find_in_set(?, stores.user_id)", [$request->ase]);
        }

        if ($request->filled('state')) {
            $query->where('stores.state_id', $request->state);
        }

        if ($request->filled('area')) {
            $query->where('stores.area_id', $request->area);
        }

        /**
         * STEP 6: Status filter
         */
        if ($request->filled('status_id')) {
            $query->where('stores.status', $request->status_id === 'active' ? 1 : 0);
        }

        /**
         * STEP 7: Keyword search
         */
       if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('stores.name', 'like', "%$keyword%")
                ->orWhere('stores.unique_code', 'like', "%$keyword%")
                ->orWhere('stores.contact', 'like', "%$keyword%");
            });
        }

        /**
         * STEP 8: Execute query
         */
        $data = $query->where('stores.user_id', NULL)->where('stores.is_deleted', 0)
            ->orderBy('stores.id', 'desc')
            ->paginate(25);

        /**
         * STEP 9: Dropdown data
         */
        $allASEs = Employee::whereIn('brand', $brandsToShow)
            ->where('type', 4)
            ->whereNotNull('name')
            ->groupBy('name')
            ->orderBy('name')
            ->with('stateDetail')
            ->get();

        $allDistributors = Distributor::whereIn('brand', $brandsToShow)
            ->whereNotNull('name')
            ->groupBy('name')
            ->orderBy('name')
            ->with('states')
            ->get();

        $state = State::groupBy('name')->orderBy('name')->get();
        $inactiveStore = Store::where('status', 0)->groupBy('name')->get();

        /**
         * STEP 10: Return view
         */
        return view('reward.user.index', compact('data', 'allASEs', 'allDistributors', 'state', 'request', 'inactiveStore'));
    }

    public function status(Request $request, $id)
    {
        $storeData = State::findOrFail($id);

        $status = ($storeData->status == 1) ? 0 : 1;
        $storeData->status = $status;
        $storeData->save();

        if ($storeData) {
            return redirect()->back()->with('success','Status Updated');
            // return redirect()->route('user.list');
        } else {
            return redirect()->route('reward.retailer.user.index')->withInput($request->all());
        }
    }
    public function show(Request $request,string $id)
    {
        $data=Store::findOrfail($id);
        return view('reward.user.view', compact('data','request'));
    }

    public function edit(Request $request,string $id)
    {
        $data=Store::findOrfail($id);
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
        $allASEs = Employee::whereIn('brand',$brandsToShow)->where('type',4)->where('name', '!=', null)->groupBy('name')->orderBy('name')->with('stateDetail')->get();
        
        $allDistributors = Distributor::whereIn('brand',$brandsToShow)->where('name', '!=', null)->groupBy('name')->orderBy('name')->with('states')->get();
        $state = State::groupBy('name')->orderBy('name')->get();
        return view('reward.user.edit', compact('data','request','allASEs','allDistributors','state'));
    }

	public function update(Request $request, string $id)
    {
        $request->validate([
            'owner_name'        => 'required|string|max:255',
             'owner_lname'        => 'required|string|max:255',
            'name'         => 'required|string|min:2|max:255',
            'address'      => 'required|string|max:500',
            'contact'           => 'required|digits:10',
            'whatsapp'  => 'nullable|digits:10',
            'state_id'             => 'required|integer',
            'area_id'              => 'nullable|integer',
            'brand'             => 'nullable|integer|in:1,2,3',
            'aadhar'            => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10000',
            'pan'               => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10000',
            'gst'               => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10000',
        ]);

        $store = Store::findOrFail($id);

        if ($store->name !== $request->name) {
            $slug = Str::slug($request->name, '-');
            $exists = Store::where('slug', $slug)->where('id', '!=', $id)->exists();
            if ($exists) {
                $slug .= '-' . time();
            }
            $store->slug = $slug;
        }

        $store->owner_name   = $request->owner_name;
        $store->owner_lname   = $request->owner_lname;
        $store->name         = $request->name;
        $store->address      = $request->address;
        $store->contact      = $request->contact;
        $store->whatsapp     = $request->whatsapp;
        $store->state_id     = $request->state_id;
        $store->area_id      = $request->area_id;
        $store->brand        = $request->brand;


          $upload_path = "public/uploads/store/";

        if (isset($request['aadhar'])) {
            $image = $request['aadhar'];
            $imageName = time() . "." . $image->getClientOriginalName();
            $image->move($upload_path, $imageName);
            $uploadedImage = $imageName;
            $store->aadhar = $upload_path . $uploadedImage;
        }
        if (isset($request['pan'])) {
            $image = $request['pan'];
            $imageName = time() . "." . $image->getClientOriginalName();
            $image->move($upload_path, $imageName);
            $uploadedImage = $imageName;
            $store->pan = $upload_path . $uploadedImage;
        }
        if (isset($request['gst'])) {
            $image = $request['gst'];
            $imageName = time() . "." . $image->getClientOriginalName();
            $image->move($upload_path, $imageName);
            $uploadedImage = $imageName;
            $store->gst = $upload_path . $uploadedImage;
        }

        $store->updated_at = now();
        $store->save();
        $changedFields = $store->getChanges();

        foreach ($changedFields as $field => $newValue) {
            if (in_array($field, ['updated_at'])) continue; // skip timestamps

            $oldValue = $oldData->$field ?? null;

            DB::table('edit_logs')->insert([
                'table_name' => 'stores',
                'record_id' => $store->id,
                'field' => $field,
                'old_value' => $oldValue,
                'new_value' => $newValue,
                'action' => 'updated',
                'updated_by' => Auth::id(),
                'created_at' => now(),
            ]);
        }
        return redirect()
            ->route('reward.retailer.user.index')
            ->with('success', 'Store information updated successfully.');
    }
   

    public function destroy($id)
    {
        $data = Store::find($id);

        if (!$data) {
            return redirect()->route('reward.retailer.user.index')
                ->with('error', 'Store not found.');
        }

        // Soft delete (mark as deleted)
        $data->is_deleted = 1;
        $data->save();

        DB::table('edit_logs')->insert([
                'table_name' => 'stores',
                'record_id' => $data->id,
                'action' => 'deleted',
                'updated_by' => Auth::id(),
                'created_at' => now(),
            ]);
        return redirect()->route('reward.retailer.user.index')
            ->with('success', 'Store deleted successfully.');
    }

     public function exportCSV(Request $request)
    {
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
        // Base query
        $query = Store::select('stores.*');

        /**
         * STEP 1: Brand filter (1 = ONN, 2 = PYNK, 3 = BOTH)
         */
        if ($request->filled('brand')) {
            $query->where(function ($q) use ($request) {
                if ($request->brand == 3) {
                    // “Both” selected → show ONN (1), PYNK (2), and Both (3)
                    $q->whereIn('stores.brand', [1, 2, 3]);
                } else {
                    // single brand selected → include that + both
                    $q->where('stores.brand', $request->brand)
                    ->orWhere('stores.brand', 3);
                }
            });
        } else {
            // if brand not selected — show according to user permission
            $userBrandPermissions = DB::table('user_permission_categories')
                ->where('user_id', $user->id)
                ->pluck('brand')
                ->toArray();

            if (!empty($userBrandPermissions)) {
                $query->where(function ($q) use ($userBrandPermissions) {
                    if (in_array(3, $userBrandPermissions)) {
                        // user has both brand permission
                        $q->whereIn('stores.brand', [1, 2, 3]);
                    } else {
                        // user has limited brand(s)
                        $q->whereIn('stores.brand', array_merge($userBrandPermissions, [3]));
                    }
                });
            }
        }


        /**
         * STEP 2: Date range filter (if available)
         */
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $from = date('Y-m-d 00:00:00', strtotime($request->date_from));
            $to   = date('Y-m-d 23:59:59', strtotime($request->date_to));
            $query->whereBetween('stores.created_at', [$from, $to]);
        }
        /**
         * STEP 3: Distributor filter
         */
        
        /**
         * STEP 3: State filter
         */
        if ($request->filled('state')) {
            $query->where('stores.state_id', $request->state);
        }

        /**
         * STEP 4: Area filter
         */
        if ($request->filled('area')) {
            $query->where('stores.area_id', $request->area);
        }
        /**
         * STEP 4: ASE filter
         */
        

        /**
         * STEP 5: Keyword search (optional)
         */
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('stores.name', 'like', "%$keyword%")
                ->orWhere('stores.unique_code', 'like', "%$keyword%")
                ->orWhere('stores.contact', 'like', "%$keyword%");
            });
        }

        /**
         * STEP 6: Fetch data with pagination
         */
        $data = $query->where('user_id',NULL)->where('stores.is_deleted',0)->orderBy('stores.id', 'desc')->get();

        if (count($data) > 0) {
            $delimiter = ",";
            $filename = "new-store-registration-list-".date('Y-m-d').".csv";

            // Create a file pointer
            $f = fopen('php://memory', 'w');

            // Set column headers
            // $fields = array('SR', 'STORE', 'FIRM', 'MOBILE', 'EMAIL', 'WHATSAPP', 'DISTRIBUTOR', 'ASE', 'ASM', 'RSM', 'VP', 'ADDRESS', 'AREA', 'STATE', 'CITY', 'PINCODE', 'OWNER', 'OWNER DATE OF BIRTH', 'OWNER DATE OF ANNIVERSARY', 'CONTACT PERSON', 'CONTACT PERSON PHONE', 'CONTACT PERSON WHATSAPP', 'CONTACT PERSON DATE OF BIRTH', 'CONTACT PERSON DATE OF ANNIVERSARY', 'GST NUMBER', 'STATUS', 'DATETIME');
            $fields = array('SR','BRAND','UNIQUE CODE', 'STORE', 'FIRM', 'ADDRESS', 'AREA','PINCODE','STATE','OWNER NAME','MOBILE', 'WHATSAPP', 'CONTACT PERSON', 'CONTACT PERSON PHONE', 'OWNER DATE OF BIRTH', 'OWNER DATE OF ANNIVERSARY','EMAIL', 'GST NUMBER','PAN NUMBER','ONN CURRENCY','DISTRIBUTOR', 'ASE', 'ASM', 'RSM', 'VP', 'STATUS', 'DATETIME');
            fputcsv($f, $fields, $delimiter);

            $count = 1;

            foreach($data as $row) {

                $assignedPermissions = [$row->brand];

                    $brandMap = [
                        1 => 'ONN',
                        2 => 'PYNK',
                        3 => 'Both',
                    ];

                    if (in_array(3, $assignedPermissions)) {
                        $brandPermissions = 'Both';
                    } elseif (in_array(1, $assignedPermissions) && in_array(2, $assignedPermissions)) {
                        $brandPermissions = 'Both';
                    } else {
                        $brandPermissions = collect($assignedPermissions)
                        ->map(fn($brand) => $brandMap[$brand] ?? $brand)
                        ->implode(', ');
                    }
				//dd($data);
                $datetime = date('j F, Y', strtotime($row['created_at']));
                //$ase = $row->user_id;
               // $username = User::select('name')->where('id', $ase)->first();
				
				$store_name = $row->name ?? '';
                //$storename = RetailerListOfOcc::select('distributor_name','vp','rsm','asm')->where('retailer', $store_name)->where('ase', $username->name)->where('area', $row->area)->first();
				
                // dd($store->store_name, $ase->name, $ase->mobile);

                $lineData = array(
                    $count,
                    $brandPermissions,
					$row->unique_code?? '',
                    ucwords($row->name)?? '',
                    ucwords($row->bussiness_name)?? '',
					ucwords($row->address)?? '',
                    $row->area->name?? '',
                    $row->pin?? '',
					$row->state->name?? '',
					ucwords($row->owner_name.' '.$row->owner_lname),
                    $row->contact?? '',
					$row->whatsapp?? '',
					$row->contact_person.' '.$row->contact_person_lname,
                    $row->contact_person_phone?? '',
					$row->date_of_birth?? '',
                    $row->date_of_anniversary?? '',
                    $row->email?? '',
                    $row->gst_no?? '',
                    $row->pan_no?? '',
					$row->wallet?? '',
                    'NA',
                    'NA',
                    'NA',
                    'NA',
                     'NA',
                 
                    
                   // $row->city,
                   
                   
                    
                   // $row->contact_person_whatsapp,
                   // $row->contact_person_date_of_birth,
                   // $row->contact_person_date_of_anniversary,
                   
                    ($row->status == 1) ? 'Active' : 'Inactive',
                    $datetime
                );

                fputcsv($f, $lineData, $delimiter);

                $count++;
            }

            // Move back to beginning of file
            fseek($f, 0);

            // Set headers to download file rather than displayed
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '";');

            //output all remaining data on a file pointer
            fpassthru($f);
        }

    }
    public function loginCount(Request $request)
    {
        $user = auth()->user();
        $stateId = $request->input('state_id');

        $query = \DB::table('stores as s')
            ->select('s.state_id','s.brand', \DB::raw('COUNT(s.secret_pin) AS count'))
            
            ->orderByDesc('count');

        if ($stateId) {
            $query->where('s.state_id', $stateId);
        }

        if ($request->filled('brand_selection')) {
            $query->where(function ($q) use ($request) {
                if ($request->brand_selection == 3) {
                    // “Both” selected → show ONN (1), PYNK (2), and Both (3)
                    $q->whereIn('s.brand', [1, 2, 3]);
                } else {
                    // single brand selected → include that + both
                    $q->where('s.brand', $request->brand_selection)
                    ->orWhere('s.brand', 3);
                }
            });
        } else {
            // if brand not selected — show according to user permission
            $userBrandPermissions = DB::table('user_permission_categories')
                ->where('user_id', $user->id)
                ->pluck('brand')
                ->toArray();

            if (!empty($userBrandPermissions)) {
                $query->where(function ($q) use ($userBrandPermissions) {
                    if (in_array(3, $userBrandPermissions)) {
                        // user has both brand permission
                        $q->whereIn('s.brand', [1, 2, 3]);
                    } else {
                        // user has limited brand(s)
                        $q->whereIn('s.brand', array_merge($userBrandPermissions, [3]));
                    }
                });
            }
        }

        $loginCountWiseReport = $query->get();

        $states = State::orderBy('name')->get();

        return view('reward.user.login-count', compact('loginCountWiseReport', 'states', 'request'));
    }


    public function loginCountexportCSV(Request $request)
    {
        $user = auth()->user();
        $stateId = $request->input('state_id');

        // Base query
        $query = DB::table('stores as s')
            ->select('s.state_id','s.brand', DB::raw('COUNT(s.secret_pin) AS count'))
            
            ->orderByDesc('count');

        // Apply filters
        if ($stateId) {
            $query->where('s.state_id', $stateId);
        }

        if ($request->filled('brand_selection')) {
            $query->where(function ($q) use ($request) {
                if ($request->brand_selection == 3) {
                    // “Both” selected → show ONN (1), PYNK (2), and Both (3)
                    $q->whereIn('s.brand', [1, 2, 3]);
                } else {
                    // single brand selected → include that + both
                    $q->where('s.brand', $request->brand_selection)
                    ->orWhere('s.brand', 3);
                }
            });
        } else {
            // if brand not selected — show according to user permission
            $userBrandPermissions = DB::table('user_permission_categories')
                ->where('user_id', $user->id)
                ->pluck('brand')
                ->toArray();

            if (!empty($userBrandPermissions)) {
                $query->where(function ($q) use ($userBrandPermissions) {
                    if (in_array(3, $userBrandPermissions)) {
                        // user has both brand permission
                        $q->whereIn('s.brand', [1, 2, 3]);
                    } else {
                        // user has limited brand(s)
                        $q->whereIn('s.brand', array_merge($userBrandPermissions, [3]));
                    }
                });
            }
        }

        $data = $query->get();

        // Prepare CSV content
        $filename = 'login_count_state_wise_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $columns = ['Brand','State Name', 'Login Count'];

        $callback = function() use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $row) {
                $assignedPermissions = [$row->brand];

                    $brandMap = [
                        1 => 'ONN',
                        2 => 'PYNK',
                        3 => 'Both',
                    ];

                    if (in_array(3, $assignedPermissions)) {
                        $brandPermissions = 'Both';
                    } elseif (in_array(1, $assignedPermissions) && in_array(2, $assignedPermissions)) {
                        $brandPermissions = 'Both';
                    } else {
                        $brandPermissions = collect($assignedPermissions)
                        ->map(fn($brand) => $brandMap[$brand] ?? $brand)
                        ->implode(', ');
                    }
                $stateName = DB::table('states')->where('id', $row->state_id)->value('name');
                fputcsv($file, [
                    $brandPermissions,
                    $stateName ?? 'Unknown',
                    $row->count
                ]);
            }
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

   
    public function loginStoreCount(Request $request,$state)
	{
		$allASEs = Employee::select('id','name')->where('type',4)->where('name', '!=', null)->where('status',1)->groupBy('name')->orderBy('name')->get();
        $allASMs = Employee::select('id','name')->where('type',3)->where('name', '!=', null)->where('status',1)->groupBy('name')->orderBy('name')->get();
        $allDistributors = Distributor::where('name', '!=', null)->groupBy('name')->orderBy('name')->with('states')->get();
        $stateData = State::where('status',1)->groupBy('name')->orderBy('name')->get();
		$areaData=Area::where('state_id',$state)->orderby('name')->get();
		if( isset($request->distributor_id)||isset($request->ase_id)||isset($request->asm_id)||isset($request->state_id)||isset($request->keyword)||isset($request->area_id)) 
        {
           

            $distributor = $request->distributor_id ? $request->distributor_id : '';
            $ase = $request->ase_id ? $request->ase_id : '';
            $asm = $request->asm_id ? $request->asm_id : '';
            $stateDetails = $request->state_id ? $request->state_id : '';
            $area = $request->area_id ? $request->area_id : '';
            $keyword = $request->keyword ? $request->keyword : '';
			
            $query = Store::selectRaw('stores.*')->with('states','areas','users')->join('teams', 'teams.store_id', 'stores.id');
            $query->when($distributor, function($query) use ($distributor) {
                $query->whereRaw("find_in_set('".$distributor."',teams.distributor_id)");
            });
            $query->when($ase, function($query) use ($ase) {
                $query->whereRaw("find_in_set('".$ase."',stores.user_id)");
            });
            $query->when($asm, function($query) use ($asm) {
                $query->whereRaw("find_in_set('".$asm."',stores.user_id)");
            });
            
            $query->when($area, function($query) use ($area) {
                $query->where('stores.area_id', $area);
            });
		
            if (!empty($request->brand_selection)) {
                $brand = $request->brand_selection;

                if ($brand == '1') {
                    $query->whereIn('stores.brand', [1, 3]);
                } elseif ($brand == '2') {
                    $query->whereIn('stores.brand', [2, 3]);
                } elseif ($brand == '3') {
                    $query->where('stores.brand', 3);
                }
            }
		
            $query->when($keyword, function($query) use ($keyword) {
                $query->where('stores.name','=',$keyword)
                ->orWhere('stores.bussiness_name', $keyword)
                ->orWhere('stores.owner_name', $keyword)
                ->orWhere('stores.contact','=', $keyword);
            });

            $loginCountWiseReport = $query->where('stores.state_id',$state)->where('secret_pin','!=',NULL)->where('stores.user_id','!=','')->latest('stores.id')->paginate(25);
        }
        else{		
		
		$loginCountWiseReport=Store::where('state_id',$state)->where('secret_pin','!=',NULL)->orderby('name')->paginate(25);
        }
		return view('reward.user.login-store', compact('loginCountWiseReport',  'request','allDistributors','allASEs','allASMs','stateData','state','areaData'));
	}
	
    public function loginStoreCountCsv(Request $request)
    {
        $state = $request->state;
        
        $query = \App\Models\Store::select('stores.*')
            ->with(['state', 'area', 'user'])
            ->join('teams', 'teams.store_id', '=', 'stores.id')
            ->where('stores.secret_pin', '!=', null)
            ->where('stores.user_id', '!=', '')
            ->orderBy('stores.id', 'desc');

        if ($request->state) {
            $query->where('stores.state_id', $request->state);
        }

        if ($request->distributor_id) {
            $query->whereRaw("find_in_set('".$request->distributor_id."',teams.distributor_id)");
        }

        if ($request->ase_id) {
            $query->whereRaw("find_in_set('".$request->ase_id."',stores.user_id)");
        }

        if ($request->asm_id) {
            $query->whereRaw("find_in_set('".$request->asm_id."',stores.user_id)");
        }

        if ($request->area_id) {
            $query->where('stores.area_id', $request->area_id);
        }

        if ($request->keyword) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('stores.name', 'like', "%{$keyword}%")
                    ->orWhere('stores.bussiness_name', 'like', "%{$keyword}%")
                    ->orWhere('stores.owner_name', 'like', "%{$keyword}%")
                    ->orWhere('stores.contact', 'like', "%{$keyword}%");
            });
        }

        if (!empty($request->brand_selection)) {
                $brand = $request->brand_selection;

                if ($brand == '1') {
                    $query->whereIn('stores.brand', [1, 3]);
                } elseif ($brand == '2') {
                    $query->whereIn('stores.brand', [2, 3]);
                } elseif ($brand == '3') {
                    $query->where('stores.brand', 3);
                }
        }
		
        $stores = $query->get();

        $filename = 'store_login_count_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $columns = [
            'Unique Code', 'Store Name', 'Business Name', 'Contact', 'Distributor',
            'State', 'Area', 'Address', 'Created Date', 'Status'
        ];

        $callback = function() use ($stores, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($stores as $store) {
                $distributor = \App\Models\Team::select('users.name')
                    ->join('users', 'users.id', 'teams.distributor_id')
                    ->where('store_id', $store->id)
                    ->value('users.name');

                $user = $store->users ? $store->users->name . 
                    (($store->users->type == 3) ? ' (ASM)' : (($store->users->type == 4) ? ' (ASE)' : '')) : '';

                $brandName = match($store->brand_id) {
                    1 => 'ONN',
                    2 => 'PYNK',
                    default => '',
                };

                fputcsv($file, [
                    $store->unique_code,
                    $store->name,
                    $store->bussiness_name,
                    $store->contact,
                    $distributor ?? '',
                    $store->state->name ?? '',
                    $store->area->name ?? '',
                    $store->address,
                   
                    \Carbon\Carbon::parse($store->created_at)->format('d/m/Y'),
                    $store->status == 1 ? 'Active' : 'Inactive',
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
