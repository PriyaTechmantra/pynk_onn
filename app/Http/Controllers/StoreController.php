<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Store;
use App\Models\Employee;
use App\Models\Team;
use App\Models\Distributor;
use App\Models\State;
use App\Models\UserNoOrderReason;
use App\Models\NoOrderReason;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use Auth;
use DB;
use Hash;
class StoreController extends Controller
{
    function __construct()
    {
         $this->middleware('permission:view store', ['only' => ['index','show']]);
         $this->middleware('permission:create store', ['only' => ['create','store']]);
         $this->middleware('permission:update store', ['only' => ['update','edit']]);
         $this->middleware('permission:delete store', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
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
        $query = Store::select('stores.*','teams.distributor_id')->join('teams', 'teams.store_id', '=', 'stores.id');

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
        if ($request->filled('distributor')) {
            $query->whereRaw("find_in_set('".$request->distributor."', teams.distributor_id)");
        }
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
        if ($request->filled('ase')) {
            $query->whereRaw("find_in_set('".$request->ase."',stores.user_id)");
        }

        if ($request->filled('status_id')) {
            $query->where('stores.status', $request->status_id === 'active' ? 1 : 0);
        }

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
        $data = $query->where('stores.is_deleted',0)->orderBy('stores.id', 'desc')->paginate(25);
        $allASEs = Employee::whereIn('brand',$brandsToShow)->where('type',4)->where('name', '!=', null)->groupBy('name')->orderBy('name')->with('stateDetail')->get();
        
        $allDistributors = Distributor::whereIn('brand',$brandsToShow)->where('name', '!=', null)->groupBy('name')->orderBy('name')->with('states')->get();
        $state = State::groupBy('name')->orderBy('name')->get();
        return view('store.index', compact('data','request','allASEs','allDistributors','state'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request,string $id)
    {
        $data=Store::findOrfail($id);
        return view('store.view', compact('data','request'));
    }

    /**
     * Show the form for editing the specified resource.
     */
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
        return view('store.edit', compact('data','request','allASEs','allDistributors','state'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //dd($request->all());
        $request->validate([
            
            'ase' => 'required|array',
            'name' => 'required|string|min:2|max:255',
            'bussiness_name' => 'required|string|min:2|max:255',
            'distributor_id' => 'required|array',
			'owner_name' =>'required|string|max:255',
            'gst_no' => 'nullable',
            'contact' => 'required|integer|digits:10',
            'whatsapp' => 'nullable|integer|digits:10',
            'email' => 'nullable|email',
			'date_of_birth' =>'nullable',
            'date_of_anniversary' =>'nullable',
            'address' => 'required',
            'area_id' => 'nullable',
            'state_id' => 'nullable',
            'city' => 'nullable',
            'pin' => 'required|integer|digits:6',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10000000',
        ]);

         //dd($request->all(), $id);
         $app_name_arr = [];
         $app_area_arr_level = [];
         $vp_arr=[];
         $rsm_arr_level= array();
         $app_id_arr =array();
         $asm_arr_level = array();
         $vp_arr1= array();
         $app_dist =array();
         $store=Store::where('id',$id)->first();
		 $new_ase=Employee::whereIN('id',$request->ase)->get();
        foreach($new_ase as $lang)
        {
            array_push($app_name_arr, $lang->name);
            array_push($app_id_arr, $lang->id);
        }
        //$cat = explode(",", $moreStoreDetail->distributor_name);
        array_push($app_dist, $request->distributor_id);
        
		//$name=$new_ase->name;
        // fetch users & location from distributor
        //$result1 = DB::select("select * from retailer_list_of_occ where distributor_name IN  (".$app_dist.") AND area LIKE '%$request->area%'  AND ase IN (".$app_name_arr.") ORDER BY id ASC");
		$result1 = Team::where(function($query) use ($app_id_arr) {
                foreach ($app_id_arr as $ase_id) {
                    $query->orWhereRaw("FIND_IN_SET(?, ase_id)", [$ase_id]);
                }
            })
            ->where(function($query) use ($app_dist) {
                foreach ($app_dist as $dist_id) {
                    $query->orWhereRaw("FIND_IN_SET(?, distributor_id)", [$dist_id]);
                }
            })
            ->orderBy('id', 'ASC')
            ->groupBy('distributor_id')
            ->get();
        foreach($result1 as $obj)
       {

            $vp_arr1[] = $obj->vp_id;
            $rsm_arr_level[] = $obj->rsm_id;
            $asm_arr_level[]= $obj->asm_id;
          
       }
      
       $vp = implode(",",array_unique(array_filter($vp_arr1)));
       $rsm = implode(",",array_unique(array_filter($rsm_arr_level)));
       $asm = implode(",",array_unique(array_filter($asm_arr_level)));
        $ase_user_detail = Employee::whereIN('id', $app_id_arr)->get();
		$rsmDetails=$request->rsm ?? null;
		$asmDetails= $request->asm ?? null;
		//dd($ase_user_detail);
        if (empty($ase_user_detail)) {
            return redirect()->back()->with('Please change distributor. No ASE found as user');
        }
		
        // update store table
        $store = Store::findOrFail($id);
        $store->user_id = implode(',',$request['ase']);
        $store->gst_no = $request->gst_no ?? null;

        // slug update
        if ($store->name != $request->name) {
            $slug = Str::slug($request->name, '-');
            $slugExistCount = Store::where('name', $request->name)->count();
            if ($slugExistCount > 0) $slug = $slug.'-'.($slugExistCount);
            $store->slug = $slug;
        }

        $store->name = $request->name ?? null;
        $store->bussiness_name = $request->bussiness_name ?? null;
		$store->retailer_list_occ_id = $request->retailer_list_of_occ_id ?? null;
        $store->store_OCC_number = $request->store_OCC_number ?? null;
		$store->owner_name = $request->owner_name ?? null;
		$store->owner_lname = $request->owner_lname ?? null;
        $store->contact = $request->contact ?? null;
        $store->email = $request->email ?? null;
        $store->whatsapp = $request->whatsapp ?? null;
		$store->date_of_birth = $request->date_of_birth ?? null;
		$store->date_of_anniversary = $request->date_of_anniversary ?? null;
        $store->address = $request->address ?? null;
        $store->area_id = $request->area;
         
         $store->pan_no = $request->pan_no ?? null;
        $store->state_id = $request->state;
        // $store->state = $request->state ?? null;
        $store->city = $request->city;
        // $store->city = $request->city ?? $request->area;
        $store->pin = $request->pin ?? null;
		$store->contact_person = $request->contact_person ?? null;
		$store->contact_person_lname = $request->contact_person_lname ?? null;
        $store->contact_person_phone = $request->contact_person_phone ?? null;
        $store->contact_person_whatsapp = $request->contact_person_whatsapp ?? null;
        $store->contact_person_date_of_birth = $request->contact_person_date_of_birth ?? null;
        $store->contact_person_date_of_anniversary = $request->contact_person_date_of_anniversary ?? null;
        //$store->status = 1;

        // image upload
        if($request->hasFile('image')) {
            $imageName = mt_rand().'.'.$request->image->extension();
            $uploadPath = 'public/uploads/store';
            $request->image->move($uploadPath, $imageName);
            $store->image = $uploadPath.'/'.$imageName;
        }
        
         if($request->hasFile('pan')) {
            $imageName = mt_rand().'.'.$request->pan->extension();
            $uploadPath = 'public/uploads/retailer/document';
            $request->pan->move($uploadPath, $imageName);
            $store->pan = $uploadPath.'/'.$imageName;
        }
		$store->updated_at = now();
        $store->save();
		//dd($store);
        // retailer list of occ update
        $retailerListOfOcc = Team::findOrFail($request->retailer_list_of_occ_id);
         $retailerListOfOcc->vp_id = implode(',', $request->vp);
        $retailerListOfOcc->state_id = $request->state;
        $retailerListOfOcc->distributor_id = implode(',', $request->distributor_id);
	    $retailerListOfOcc->ase_id = implode(',',array_filter($app_id_arr));
        $retailerListOfOcc->area_id = $request->area;
        
        $retailerListOfOcc->rsm_id = $rsmDetails;
        $retailerListOfOcc->asm_id = $asmDetails;
        //$retailerListOfOcc->ase = $result1[0]->ase;
        $retailerListOfOcc->status = '1';
        $retailerListOfOcc->is_deleted = '0';
        
		$retailerListOfOcc->created_at = now();
		$retailerListOfOcc->updated_at = now();
        $retailerListOfOcc->save();
        
		//dd($retailerListOfOcc);
        return redirect()->back()->with('success', 'Store information updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        
        $order       = Order::where('store_id', $id)->get();
        
        $noorder     = UserNoorderreason::where('store_id', $id)->get();

        if ($order->isEmpty()  && $noorder->isEmpty()) {
            $data = Store::findOrfail($id);
            $data->is_deleted=1;
            $data->save();
            $team = Team::where('store_id',$id)->first();
            $team->is_deleted=1;
            $team->save();
            return redirect()->route('stores.index')
                            ->with('success','Store deleted successfully');
        } else {
            return redirect()->back()->with('failure', 'Store cannot be deleted because related records exist');
        }
    }


    public function status($id): RedirectResponse
    {
        $data = Store::find($id);
        $status = ($data->status == 1) ? 0 : 1;
        $data->status = $status;
        $data->save();
    
        return redirect()->route('stores.index')
                        ->with('success','Store status changed successfully');
    }

    public function storesExport(Request $request)
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
        $query = Store::select('stores.*','teams.distributor_id')->join('teams', 'teams.store_id', '=', 'stores.id');

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
        if ($request->filled('distributor')) {
            $query->whereRaw("find_in_set('".$request->distributor."', teams.distributor_id)");
        }
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
        if ($request->filled('ase')) {
            $query->whereRaw("find_in_set('".$request->ase."',stores.user_id)");
        }

        if ($request->filled('status_id')) {
            $query->where('stores.status', $request->status_id === 'active' ? 1 : 0);
        }

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
        $data = $query->where('stores.is_deleted',0)->orderBy('stores.id', 'desc')->get();

        if (count($data) > 0) {
            $delimiter = ",";
            $filename = "onn-store-list-".date('Y-m-d').".csv";

            // Create a file pointer
            $f = fopen('php://memory', 'w');

            // Set column headers
            // $fields = array('SR', 'STORE', 'FIRM', 'MOBILE', 'EMAIL', 'WHATSAPP', 'DISTRIBUTOR', 'ASE', 'ASM', 'RSM', 'VP', 'ADDRESS', 'AREA', 'STATE', 'CITY', 'PINCODE', 'OWNER', 'OWNER DATE OF BIRTH', 'OWNER DATE OF ANNIVERSARY', 'CONTACT PERSON', 'CONTACT PERSON PHONE', 'CONTACT PERSON WHATSAPP', 'CONTACT PERSON DATE OF BIRTH', 'CONTACT PERSON DATE OF ANNIVERSARY', 'GST NUMBER', 'STATUS', 'DATETIME');
            $fields = array('SR','UNIQUE CODE', 'STORE', 'FIRM', 'ADDRESS', 'AREA','PINCODE','STATE','OWNER NAME','MOBILE', 'WHATSAPP', 'CONTACT PERSON', 'CONTACT PERSON PHONE', 'OWNER DATE OF BIRTH', 'OWNER DATE OF ANNIVERSARY','EMAIL', 'GST NUMBER','PAN NUMBER','ONN CURRENCY','DISTRIBUTOR', 'ASE', 'ASM', 'RSM', 'VP', 'STATUS', 'DATETIME');
            fputcsv($f, $fields, $delimiter);

            $count = 1;

            foreach($data as $row) {
				//dd($data);
                $datetime = date('j F, Y', strtotime($row['created_at']));
                //$ase = $row->user_id;
               // $username = User::select('name')->where('id', $ase)->first();
				$displayASEName = '';
                foreach(explode(',',$row->user_id) as $aseKey => $aseVal) 
                {
                    //dd($distVal);
                    $catDetails = DB::table('users')->where('id', $aseVal)->first();
                    if(!empty($catDetails)){
                    $displayASEName .= $catDetails->name.',';
                    }
                }
				$store_name = $row->store_name ?? '';
                //$storename = RetailerListOfOcc::select('distributor_name','vp','rsm','asm')->where('retailer', $store_name)->where('ase', $username->name)->where('area', $row->area)->first();
				$storename = RetailerListOfOcc::select('distributor_name','vp','rsm','asm')->where('store_id', $row->id)->first();
				
                // $store = Store::select('store_name')->where('id', $row['store_id'])->first();
                // $ase = User::select('name', 'mobile', 'state', 'city', 'pin')->where('id', $row['user_id'])->first();

                // dd($store->store_name, $ase->name, $ase->mobile);

                $lineData = array(
                    $count,
					$row->unique_code?? '',
                    ucwords($row->store_name)?? '',
                    ucwords($row->bussiness_name)?? '',
					ucwords($row->address)?? '',
                    $row->area?? '',
                    $row->pin?? '',
					$row->state?? '',
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
                    $storename->distributor_name ?? '',
                    substr($displayASEName, 0, -1) ? substr($displayASEName,0, -1) : 'NA',
                    $storename->asm ?? '',
                    $storename->rsm ?? '',
                    $storename->vp ?? '',
                 
                    
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

    public function noOrderreason(Request $request)
    {
        $query = UserNoOrderReason::query();

        if ($request->filled('ase')) {
            $query->where('user_id', $request->ase);
        }

        if ($request->filled('asm')) {
            $query->where('user_id', $request->asm);
        }

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        if ($request->filled('comment')) {
            $query->where('no_order_reason_id', $request->comment);
        }

        if ($request->filled('keyword')) {
            $query->where('comment', 'like', '%' . $request->keyword . '%');
        }
        if (!empty($request->brand_selection)) {
            $brand = $request->brand_selection;

            if ($brand == '1') {
                $query->whereIn('brand', [1, 3]);
            } elseif ($brand == '2') {
                $query->whereIn('brand', [2, 3]);
            } elseif ($brand == '3') {
                $query->where('brand', 3);
            }
        }

        $data = $query->with(['user', 'store', 'noorder'])
                    ->latest('id')
                    ->paginate(25);

        $ases = Employee::where('type', 4)
            ->whereNotNull('name')
            ->orderBy('name')
            ->get();

        $stores = Store::where('is_deleted', 0)
            ->orderBy('name')
            ->get();

        $reasons = NoOrderReason::orderBy('noorderreason')->get();

        return view('store.noorder', compact('data', 'stores', 'reasons', 'request', 'ases'));
    }

    public function noOrderReasonCsv(Request $request)
    {
        $query = UserNoOrderReason::with(['user', 'store']);

        if ($request->ase) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('id', $request->ase);
            });
        }

        if ($request->store_id) {
            $query->where('store_id', $request->store_id);
        }

        if ($request->comment) {
            $query->where('comment', $request->comment);
        }

        if ($request->brand_selection) {
            $query->where('brand', $request->brand_selection);
        }

        $data = $query->get();

        // CSV header
        $filename = 'no_order_reasons_' . date('Ymd') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['#', 'Name', 'Store Name', 'Reason', 'Location', 'Date']);

            foreach ($data as $index => $item) {
                fputcsv($file, [
                    $index + 1,
                    $item->user ? $item->user->name : '',
                    $item->store ? $item->store->name : '',
                    $item->comment .'|'.$item->description,
                    $item->location,
                    date('d M Y', strtotime($item->date)).' '.$item->time,
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

}
