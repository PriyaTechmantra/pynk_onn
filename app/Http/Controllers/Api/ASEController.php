<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\State;
use App\Models\Area;
use App\Models\UserArea;
use App\Models\Store;
use App\Models\Visit;
use App\Models\Team;
use App\Models\Cart;
use App\Models\PrimaryOrder;
use App\Models\SecondaryOrder;
use App\Models\NoOrderReason;
use App\Models\Category;
use App\Models\Color;
use App\Models\Collection;
use App\Models\ProductImage;
use App\Models\Product;
use App\Models\ProductColorSize;
use App\Models\UserNoOrderReason;
use App\Models\Order;
use App\Models\OrderProduct;
use Str;
use Illuminate\Support\Facades\Validator;
use App\Models\UserPermissionCategory;
use DB;
use Illuminate\Support\Facades\Log;
class ASEController extends Controller
{

    public function stateList(Request $request)
    {
         $data = UserArea::where('user_id', $request->ase_id)
                ->where('is_deleted', 0)
                ->groupby('state_id')
                ->with('state')
                ->get();


            

            if ($data->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'State list not found'
                ], 404);
            }

            
            return response()->json([
                'status' => true,
                'message' => 'List of states',
                'data' => $data
            ], 200);

    }
    public function areaList(Request $request)
    {
        $data = UserArea::where('user_id', $request->ase_id)
                ->where('is_deleted', 0)
                ->groupby('area_id')
                ->with('area')
                ->get();

            

            if ($data->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Area list not found'
                ], 404);
            }

            
            return response()->json([
                'status' => true,
                'message' => 'List of areas',
                'data' => $data
            ], 200);

    }
    
    //check visit
    public function checkVisit(Request $request,$id){
		$area=Visit::where('user_id',$id)->where('start_date',date('Y-m-d'))->where('visit_id',NULL)->orderby('id','desc')->with('areas')->first();
        
		$user=Employee::where('id',$id)->first();
        if (empty($area)) {
            return response()->json(['status'=>false, 'message'=>'Start Your Visit']);
        } else {
            return response()->json(['status'=>true, 'message'=>'Visit already started','area'=>$area->areas->name,'visit_id'=>$area->id,'data'=>$user],200);
        } 
		
	}

   	// store visit start
	public function dayStart(Request $request)
{
    $validator = Validator::make($request->all(), [
        "user_id" => "required",
        "area_id" => "required",
        "start_date" => "required",
        "start_time" => "required",
        "start_location" => "nullable",
        "start_lat" => "nullable",
        "start_lon" => "nullable",
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
    }

    $data = [
        
        "user_id" => $request->user_id,
        "area_id" => $request->area_id,
        "start_date" => $request->start_date,
        "start_time" => $request->start_time,
        "start_location" => $request->start_location,
        "start_lat" => $request->start_lat,
        "start_lon" => $request->start_lon,
         "created_at" => now(),
         
    ];

    $visit_id = DB::table('visits')->insertGetId($data);
    $attendance = [
        
        "user_id" => $request->user_id,
        "entry_date" => $request->entry_date,
        "start_time" => $request->start_time,
        "type" => 'P',
         "created_at" => now(),
         
    ];
    $attendance_id = DB::table('user_attendances')->insertGetId($attendance);

    return response()->json(['status' => true, 'message' => 'Visit started', 'visit_id' => $visit_id],200);
}


	// store visit end
	public function dayEnd(Request $request)
	{
		$validator = Validator::make($request->all(), [
            "visit_id" => "required",
            "end_date" => "required",
            "end_time" => "required",
            "end_location" => "nullable",
            "end_lat" => "nullable",
            "end_lon" => "nullable",
        ]);

        if (!$validator->fails()) {
            $data = [
                "visit_id" => $request->visit_id,
                "end_date" => $request->end_date,
                "end_time" => $request->end_time,
                "end_location" => $request->end_location,
                "end_lat" => $request->end_lat,
                "end_lon" => $request->end_lon,
                 "updated_at" => now(),
            ];

            DB::table('visits')->where('id', $request->visit_id)->update($data);

            return response()->json(['status' => true, 'message' => 'Visit ended', 'data' => $data],200);
        } else {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }
	}

	
	//day start activity store
	
	 public function daystartactivityStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "user_id" => "required",
            "area_id" => "required",
            "date" => "required",
            "time" => "required",
            "type" => "required",
            "comment" => "nullable",
            "location" => "nullable",
            "lat" => "nullable",
        ]);


        if (!$validator->fails()) {
            $data = [
                "user_id" => $request->user_id,
                "area_id" => $request->area_id,
                "date" => $request->date,
                "time" => $request->time,
                "type" => 'Visit Started',
                "comment" => $request->comment,
                "location" => $request->location,
                "lat" => $request->lat,
                "lng" => $request->lng,
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ];

            $resp = DB::table('activities')->insertGetId($data);
            if( $resp){
                return response()->json(['status' => true, 'message' => 'Activity stored successfully', 'data' => $resp],200);
            }else{
                return response()->json(['status'=>false, 'message'=>'Something happend'],404);
            }
           
        } else {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }
    }
    //day end activity store
    public function dayendactivityStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "user_id" => "required",
            "area_id" => "required",
            "date" => "required",
            "time" => "required",
            "type" => "required",
            "comment" => "nullable",
            "location" => "nullable",
            "lat" => "nullable",
        ]);

        if (!$validator->fails()) {
            $data = [
                "user_id" => $request->user_id,
                "area_id" => $request->area_id,
                "date" => $request->date,
                "time" => $request->time,
                "type" => 'Visit Ended',
                "comment" => $request->comment,
                "location" => $request->location,
                "lat" => $request->lat,
                "lng" => $request->lng,
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ];

            $resp = DB::table('activities')->insertGetId($data);
            if( $resp){
                return response()->json(['status' => true, 'message' => 'Activity stored successfully', 'data' => $resp],200);
            }else{
                return response()->json(['status'=>false, 'message'=>'Something happend'],404);
            }
           
        } else {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }
    }

    //all activity store
    public function activityStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "user_id" => "required",
            "date" => "required",
            "time" => "required",
            "type" => "required",
            "comment" => "nullable",
            "location" => "nullable",
            "lat" => "nullable",
            "brand" => "required",
        ]);

        if (!$validator->fails()) {
            $brandMap = [
                'ONN'  => 1,
                'PYNK' => 2,
                'Both' => 3,
            ];

            $brandValue = $brandMap[$request->brand] ?? null;

            if (!$brandValue) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid brand value.',
                ]);
            }
            $data = [
                "user_id" => $request->user_id,
                "brand" => $brandValue,
                "store_id" => $request->store_id?? '',
                "order_id" => $request->order_id ?? '',
                "distributor_id" => $request->distributor_id?? '',
                "date" => $request->date,
                "time" => $request->time,
                "type" => $request->type,
                "comment" => $request->comment,
                "location" => $request->location,
                "lat" => $request->lat,
                "lng" => $request->lng,
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ];

            $resp = DB::table('activities')->insertGetId($data);
            if( $resp){
                return response()->json(['status' => true, 'message' => 'Activity stored successfully', 'data' => $resp],200);
            }else{
                return response()->json(['status'=>false, 'message'=>'Something happend'],404);
            }
           
        } else {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }
    }
       

    //ase wise primary and secondary report on dashboard
    
    
/*public function aseSalesreport(Request $request)
{
    $validator = Validator::make($request->all(), [
        "ase_id" => "required",
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
    }

    $ase = $request->ase_id;
    $respArrd = [];
    $respArr = [];

    // Date range
    if ($request->filled('from') || $request->filled('to')) {
        $from = !empty($request->from) ? date('Y-m-d', strtotime($request->from)) : date('Y-m-01');
        $to   = !empty($request->to) ? date('Y-m-d', strtotime($request->to)) : date('Y-m-d');
    } else {
        $from = date('Y-m-01');
        $to   = date('Y-m-d');
    }

    // ✅ Primary (Distributor-wise)
    $distributors = Team::where('ase_id', $ase)
        ->whereNull('store_id')
        ->whereHas('distributor', function ($q) {
            $q->where('status', 1)
              ->where('is_deleted', 0);
        })
        ->with('distributor')
        ->get();

    foreach ($distributors as $item) {
        $qty = PrimaryOrder::where('distributor_id', $item->distributor_id)
            ->whereBetween('order_date', [$from, $to])
            ->sum('qty');

        $respArrd[] = [
            'distributor_id'   => $item->distributor_id ?? 0,
            'distributor_name' => $item->distributor->name ?? '',
            'amount'           => 0,
            'qty'              => $qty ?? 0,
        ];
    }

    // ✅ Secondary (Retailer-wise)
    $stores = Store::where('user_id', $ase)
        ->where('status', 1)
        ->where('is_deleted', 0)
        ->get();

    foreach ($stores as $value) {
        $qty = SecondaryOrder::where('retailer_id', $value->id)
            ->whereBetween('order_date', [$from, $to])
            ->sum('qty');

        $respArr[] = [
            'retailer_id' => $value->id,
            'store_name'  => $value->name,
            'amount'      => 0,
            'qty'         => $qty ?? 0,
        ];
    }

    return response()->json([
        'status' => true,
        'message' => 'ASE wise Primary & Secondary Sales Report',
        'Primary Sales | Distributor wise Daily Report' => $respArrd,
        'Secondary Sales | Retailer wise Daily Report' => $respArr,
    ],200);
}*/


public function aseSalesreport(Request $request)
{
    $validator = Validator::make($request->all(), [
        "ase_id" => "required",
    ]);
    
    if ($validator->fails()) {
        return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
    }

    $ase = $request->ase_id;
    $respArrd = [];
    $respArr = [];
    
    // Date range
    if ($request->filled('from') || $request->filled('to')) {
        $from = !empty($request->from) ? date('Y-m-d', strtotime($request->from)) : date('Y-m-01');
        $to   = !empty($request->to) ? date('Y-m-d', strtotime($request->to)) : date('Y-m-d');
    } else {
        $from = date('Y-m-01');
        $to   = date('Y-m-d');
    }

    /**
     * ✅ Primary (Distributor-wise, Brand-wise)
     */

    $brandMap = [
        1 => 'ONN',
        2 => 'PYNK',
        3 => 'Both',
    ];
    $distributors = Team::where('ase_id', $ase)
        ->whereNull('store_id')
        ->whereHas('distributor', function ($q) {
            $q->where('status', 1)
              ->where('is_deleted', 0);
        })
        ->with('distributor')
        ->get();
     
    foreach ($distributors as $item) {
        // Get brands permitted for this distributor
        // $brandPermissions = DB::table('user_permission_categories')
        //     ->where('distributor_id', $item->distributor_id)
        //     ->value('brand');
        $brandCode = $item->distributor->brand;
        $brandName = $brandMap[$brandCode] ?? '';
        
        // Handle "Both" case
        $brandsToCheck = ($brandCode == 3) ? [1, 2] : [$brandCode];
        
        //foreach ($brandsToCheck  as $brand) {
            $qty = PrimaryOrder::where('distributor_id', $item->distributor_id)
                ->whereIN('brand', $brandsToCheck)
                ->whereBetween('order_date', [$from, $to])
                ->sum('qty');
            
            $respArrd[] = [
                'distributor_id'   => $item->distributor_id ?? 0,
                'distributor_name' => $item->distributor->name ?? '',
                'brand'            => $brandName ?? '',
                'amount'           => 0,
                'qty'              => $qty ?? 0,
            ];
        //}
    }

    /**
     * ✅ Secondary (Retailer-wise, Brand-wise)
     */

    $brandMap = [
        1 => 'ONN',
        2 => 'PYNK',
        3 => 'Both',
    ];
    $stores = Store::where('user_id', $ase)
        ->where('status', 1)
        ->where('is_deleted', 0)
        ->get();

    foreach ($stores as $value) {
        $brandCode = $value->brand;

        // Convert numeric to readable brand
        $brandName = $brandMap[$brandCode] ?? null;

        // Handle "Both" case
         $brandsToCheck = ($brandCode == 3) ? [1, 2] : [$brandCode];
            //foreach ($brandsToCheck as $brand) {
                $qty = SecondaryOrder::where('retailer_id', $value->id)
                    ->whereIN('brand', $brandsToCheck)
                    ->whereBetween('order_date', [$from, $to])
                    ->sum('qty');

                $respArr[] = [
                    'retailer_id' => $value->id,
                    'store_name'  => $value->name,
                    'brand'       => $brandName,
                    'amount'      => 0,
                    'qty'         => $qty ?? 0,
                ];
            //}
    }

    return response()->json([
        'status' => true,
        'message' => 'ASE wise Primary & Secondary Sales Report',
        'PrimarySales' => $respArrd,
        'SecondarySales' => $respArr,
    ], 200);
}




//store list

    public function storeList(Request $request)
    {
		$ase = $_GET['ase_id'];

        $brandMap = [
            1 => 'ONN',
            2 => 'PYNK',
            3 => 'Both',
        ];

		
		$stores = Store::where('user_id',$ase)->where('status',1)->where('is_deleted',0)->with('state','area','user')->get();
		
	
        if ($stores->isNotEmpty()) {
            // Transform brand values
            $stores = $stores->map(function ($store) use ($brandMap) {
                $store->brand_name = $brandMap[$store->brand] ?? null; // readable brand name
                return $store;
            });

            return response()->json([
                'status'  => true,
                'message' => 'Store data fetched successfully',
                'data'    => $stores,
            ], 200);
        } else {
            return response()->json([
                'status'  => false,
                'message' => 'No store data found',
            ], 404);
        }
    }
    

    //inactive store list

    public function inactivestoreList(Request $request)
    {
        $ase = $_GET['ase_id'];
		$brandMap = [
            1 => 'ONN',
            2 => 'PYNK',
            3 => 'Both',
        ];
		$stores = Store::where('user_id',$ase)->where('status',0)->where('is_deleted',0)->with('state','area','user')->get();
		
	
        if ($stores->isNotEmpty()) {
            // Transform brand values
            $stores = $stores->map(function ($store) use ($brandMap) {
                $store->brand_name = $brandMap[$store->brand] ?? null; // readable brand name
                return $store;
            });

            return response()->json([
                'status'  => true,
                'message' => 'Store data fetched successfully',
                'data'    => $stores,
            ], 200);
        } else {
            return response()->json([
                'status'  => false,
                'message' => 'No store data found',
            ], 404);
        }
    }


    public function searchStore(Request $request)
   {
        $search = $request->keyword ?? '';

        // Brand map
        $brandMap = [
            1 => 'ONN',
            2 => 'PYNK',
            3 => 'Both',
        ];

        // Base query
        $query = Store::select('*')
            ->where('status', 1)
            ->where('is_deleted', 0)->with('state','area','user');

        // Search filter
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('contact', '=',$search)
                ->orWhere('name', 'LIKE', "%{$search}%");
            });
        }

        $data = $query->get();

        if ($data->isNotEmpty()) {
            foreach ($data as $item) {
                // Fetch team info
                $item->team = Team::where('store_id', $item->id)->first();

                // Convert brand numeric value to name
                $item->brand_name = $brandMap[$item->brand] ?? null;
            }
        }

        return response()->json([
            'status'  => true,
            'message' => 'Store List',
            'data'    => $data,
        ], 200);
    }

    
    //distributor list area wise


    
    
    public function distributorList(Request $request)
    {
        $ase_id  = $request->ase_id;
        $area_id = $request->area_id;

        // Brand map
        $brandMap = [
            1 => 'ONN',
            2 => 'PYNK',
            3 => 'Both',
        ];

        // Fetch distributors under ASE and Area
        $distributors = Team::where('ase_id', $ase_id)
            ->where('area_id', $area_id)
            ->where('store_id',NULL)
            ->where('is_deleted', 0)
            ->with('distributor')
            ->get();

        if ($distributors->isNotEmpty()) {
           

            $distributors = $distributors->map(function ($distributor) use ($brandMap) {
                $distributor->brand_name = $brandMap[$distributor->brand] ?? null; // readable brand name
                return $distributor;
            });


            return response()->json([
                'status'  => true,
                'message' => 'Distributor data fetched successfully',
                'data'    => $distributors,
            ], 200);
        } else {
            return response()->json([
                'status'  => false,
                'message' => 'No distributor data found',
            ], 404);
        }
    }

    //add store
    public function addStore(Request $request)
    {
         
       $validator = Validator::make($request->all(), [
            "name" => "required|string|unique:stores|max:255",
            "contact" => "required|integer|digits:10|unique:stores,contact",
            "whatsapp"=>"nullable|integer|digits:10",
            "email" => "nullable|string",
            'owner_name' => 'required|regex:/^[\pL\s\-]+$/u',
			'owner_lname' => 'required|regex:/^[\pL\s\-]+$/u',
            'contact_person' => 'required|regex:/^[\pL\s\-]+$/u',
            'contact_person_lname' => 'required|regex:/^[\pL\s\-]+$/u',
            "address" => "nullable|string",
            "state_id" => "required",
            "city" => "nullable|string",
            "pin" => "nullable",
            "area_id" => "required",
            "user_id" => "required",
            "distributor_id" => "required",
             'brand'   => 'required|string|in:ONN,PYNK,Both',
            "image" => "required",
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }
         // 🔁 Map brand name to numeric value
        $brandMap = [
            'ONN'  => 1,
            'PYNK' => 2,
            'Both' => 3,
        ];

        $brandValue = $brandMap[$request->brand] ?? null;

        if (!$brandValue) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid brand value.',
            ]);
        }
        $user = Employee::where('id',$request->user_id)->first();
        $name = $user->name;
        $store = new Store;
        $store->user_id = $request->user_id;
        $store->brand = $brandValue;
        $store->name = $request->name ?? null;
        $slug = Str::slug($request->name, '-');
        $slugExistCount = Store::where('name', $request->name)->count();
        if ($slugExistCount > 0) $slug = $slug.'-'.($slugExistCount);
        $store->slug = $slug;
        $orderData = Store::select('sequence_no')->latest('sequence_no')->first();
        				
        				    if (empty($store->sequence_no)) {
        						if (!empty($orderData->sequence_no)) {
        							$new_sequence_no = (int) $orderData->sequence_no + 1;
        							
        						} else {
        							$new_sequence_no = 1;
        							
        						}
        					}
        			$uniqueNo = sprintf("%'.06d",$new_sequence_no);
        		    $store->sequence_no = $new_sequence_no;
        			$store->unique_code = 'ST'.$uniqueNo;
        // $store->slug = null;
        $store->bussiness_name = $request->bussiness_name ?? null;
        $store->store_OCC_number = $request->store_OCC_number ?? null;
        $store->contact = $request->contact ?? null;
        $store->email = $request->email ?? null;
        $store->whatsapp = $request->whatsapp ?? null;
        $store->address = $request->address ?? null;
        $store->area_id = $request->area_id ?? null;
        $store->state_id = $request->state_id ?? null;
        $store->city = $request->city;
        $store->pin = $request->pin ?? null;
        $store->owner_name	 = $request->owner_name ?? null;
        $store->owner_lname	 = $request->owner_lname ?? null;
        
        $store->gst_no = $request->gst_no ?? null;
        $store->pan_no = $request->pan_no ?? null;
        $store->date_of_birth	 = $request->date_of_birth?? null;
        $store->date_of_anniversary	 = $request->date_of_anniversary?? null;
        $store->contact_person	 = $request->contact_person ?? null;
        $store->contact_person_lname = $request->contact_person_lname ?? null;
        $store->contact_person_phone	= $request->contact_person_phone ?? null;
        $store->contact_person_whatsapp	 = $request->contact_person_whatsapp ?? null;
        $store->contact_person_date_of_birth	 = $request->contact_person_date_of_birth ?? null;
        $store->contact_person_date_of_anniversary	 = $request->contact_person_date_of_anniversary ?? null;
        if (!empty($request['image'])) {
        				$store->image= $request->image;
        }
        if (!empty($request['pan'])) {
        				$store->pan= $request->pan;
        }
        $store->status = 0;
        
        $store->save();
       
        $result1 = Team::where('distributor_id',$request->distributor_id)->where('ase_id',$request->user_id)->where('state_id',$request->state_id)->where('area_id',$request->area_id)->first();

        $retailerListOfOcc = new Team;
        $retailerListOfOcc->vp_id = $result1->vp_id;
        $retailerListOfOcc->state_id = $result1->state_id;
        $retailerListOfOcc->distributor_id = $result1->distributor_id;
        $retailerListOfOcc->area_id = $result1->area_id;
        $retailerListOfOcc->store_id = $store->id ?? null;
        $retailerListOfOcc->rsm_id = $result1->rsm_id;
        $retailerListOfOcc->asm_id = $result1->asm_id;
        $retailerListOfOcc->ase_id = $result1->ase_id;
        $retailerListOfOcc->status = '1';
        $retailerListOfOcc->is_deleted = '0';
        
        $retailerListOfOcc->save();

        	// notification to Admin
        	$loggedInUser = $name;
        	sendNotification($store->user_id, 'admin', 'store-add', 'admin.stores.index', $store->name. '  added by ' .$loggedInUser , '  Store ' .$store->name.' added');
        	// notification to ASM
        	$loggedInUser = $name;
        	$asm = DB::select("SELECT u.id as asm_id FROM `teams` t  INNER JOIN users u ON u.id = t.asm_id where t.ase_id = '$request->user_id' GROUP BY t.asm_id");
                foreach($asm as $value){
                    sendNotification($store->user_id, $value->asm_id, 'store-add', 'front.store.index', $store->name. '  added by ' .$loggedInUser , '  Store ' .$store->name.' added');
                }
                // notification to RSM
                $loggedInUser = $name;
                $rsm = DB::select("SELECT u.id as rsm_id FROM `teams` t  INNER JOIN users u ON u.id = t.rsm_id where t.ase_id = '$request->user_id' GROUP BY t.rsm_id");
                foreach($rsm as $value){
                    sendNotification($store->user_id, $value->rsm_id, 'store-add', '', $store->name. '  added by '  .$loggedInUser ,' Store ' .$store->name. ' added');
                }

               
                
                // notification to VP
                $loggedInUser = $name;
                $vp = DB::select("SELECT u.id as vp_id FROM `teams` t  INNER JOIN users u ON u.id = t.vp_id where t.ase_id = '$request->user_id' GROUP BY t.vp_id");
                foreach($vp as $value){
                    sendNotification($store->user_id, $value->vp_id, 'store-add', '', $store->name. '  added by ' .$loggedInUser ,'Store ' .$store->name.' added  ');
                }
            return response()->json(['status'=>true, 'message'=>'Store data created successfully','data'=>$store]);

        
    }
    

    public function storeimageUpdate(Request $request)
    {

        $validator = Validator::make($request->all(),[
            'image' => ['required', 'image', 'max:1000000']
        ]);

        if(!$validator->fails()){
            $imageName = mt_rand().'.'.$request->image->extension();
			$uploadPath = 'public/uploads/store';
            $filePath='uploads/store';
			$request->image->move($uploadPath, $imageName);
			$total_path = $uploadPath.'/'.$imageName;
            
			return response()->json(['status' => true, 'message' => 'Image added', 'data' => $total_path]);

        }else {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }
        
    }


    public function storepanimageUpdate(Request $request)
    {

        $validator = Validator::make($request->all(),[
            'pan' => ['required', 'image', 'max:1000000']
        ]);

        if(!$validator->fails()){
            $imageName = mt_rand().'.'.$request->pan->extension();
			$uploadPath = 'public/uploads/store';
            $filePath='uploads/store';
			$request->pan->move($uploadPath, $imageName);
			$total_path = $uploadPath.'/'.$imageName;
            
			return response()->json(['status' => true, 'message' => 'Image added', 'data' => $total_path]);

        }else {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }
        
    }

    public function noorderlist(Request $request)
    {
        $brandMap = [
            1 => 'ONN',
            2 => 'PYNK',
            3 => 'Both',
        ];

        $data = NoOrderReason::all();
         if ($data->isNotEmpty()) {
            // Add brand name to response
            $data = $data->map(function ($store) use ($brandMap) {
                $store->brand_name = $brandMap[$store->brand] ?? null;
                return $store;
            });

            return response()->json([
                'status'  => true,
                'message' => 'no order list data fetched successfully',
                'data'    => $data,
            ], 200);
        } else {
            return response()->json([
                'status'  => false,
                'message' => 'No list data found',
            ], 404);
        }
        
    }


    public function noorder(Request $request)
    {
        $validator = Validator::make($request->all(),[
                "no_order_reason_id" => "required",
                "store_id" => "required",
                "user_id" => "required",
                'brand'   => 'required|string|in:ONN,PYNK,Both',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }
        // 🔁 Map brand name to numeric value
        $brandMap = [
            'ONN'  => 1,
            'PYNK' => 2,
            'Both' => 3,
        ];

        $brandValue = $brandMap[$request->brand] ?? null;

        if (!$brandValue) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid brand value.',
            ]);
        }
        $data = new UserNoOrderReason();
        $data->no_order_reason_id= $request->no_order_reason_id;
        $data->store_id= $request->store_id;
        $data->user_id= $request->user_id;
        $data->brand= $brandValue;
        $data->comment= $request->comment;
        $data->description= $request->description;
        $data->location= $request->location;
        $data->lat= $request->lat;
        $data->lng= $request->lng;
        $data->date= $request->date;
        $data->time= $request->time;
        $data->save();
        return response()->json(['status'=>true, 'message'=>'no order reason data updated successfully','data'=>$data]);
    }

    public function noorderhistory(Request $request, $id)
    {
        $noOrder=UserNoOrderReason::where('store_id', $id)->with('user','store','noorder')->orderby('id','desc')->get();
		if ($noOrder->isNotEmpty()) {

            // Brand mapping
            $brandMap = [
                1 => 'ONN',
                2 => 'PYNK',
                3 => 'Both',
            ];

            // Add brand name from table
            $noOrder->transform(function ($item) use ($brandMap) {
                $item->brand_name = $brandMap[$item->brand] ?? 'Unknown';
                return $item;
            });

            return response()->json([
                'status'  => true,
                'message' => 'No order list data fetched successfully',
                'data'    => $noOrder
            ], 200);
		}else{
			  return response()->json(['error' => false, 'message' => 'No data found']);
		}
        
    }

    public function categoryList(Request $request)
    {
		

        $brandMap = [
            1 => 'ONN',
            2 => 'PYNK',
            3 => 'Both',
        ];

		
		$stores = Category::where('status',1)->where('is_deleted',0)->get();
		
	
        if ($stores->isNotEmpty()) {
            // Transform brand values
            $stores = $stores->map(function ($store) use ($brandMap) {
                $store->brand_name = $brandMap[$store->brand] ?? null; // readable brand name
                return $store;
            });

            return response()->json([
                'status'  => true,
                'message' => 'Category data fetched successfully',
                'data'    => $stores,
            ], 200);
        } else {
            return response()->json([
                'status'  => false,
                'message' => 'No category data found',
            ], 404);
        }
    }


    public function collectionList(Request $request)
    {
		

        $brandMap = [
            1 => 'ONN',
            2 => 'PYNK',
            3 => 'Both',
        ];

		
		$stores = Collection::where('status',1)->where('is_deleted',0)->get();
		
	
        if ($stores->isNotEmpty()) {
            // Transform brand values
            $stores = $stores->map(function ($store) use ($brandMap) {
                $store->brand_name = $brandMap[$store->brand] ?? null; // readable brand name
                return $store;
            });

            return response()->json([
                'status'  => true,
                'message' => 'Collection data fetched successfully',
                'data'    => $stores,
            ], 200);
        } else {
            return response()->json([
                'status'  => false,
                'message' => 'No collection data found',
            ], 404);
        }
    }


    // collection wise category & products
	public function collectionWiseCategoryProduct(Request $request,$id = "")
    {
    // If 10000 means all - coming from app
        if ($id != "10000") {
            $collection = Collection::find($id);

            if (!$collection) {
                return response()->json(['error' => true, 'message' => 'Collection not found']);
            }

            $collection_name = $collection->name;

            $brandMap = [
                1 => 'ONN',
                2 => 'PYNK',
                3 => 'Both',
            ];

            // Fetch all categories for the given collection
            $categories = DB::select("
                SELECT DISTINCT 
                    c.id AS category_id, 
                    c.name AS category_name,
                    p.brand AS brand_id
                FROM products AS p
                INNER JOIN categories AS c ON c.id = p.cat_id
                WHERE p.collection_id = ?
                ORDER BY c.position ASC
            ", [$id]);

            // Fetch products for the given collection
            $products = DB::select("
                SELECT 
                    p.id, 
                    p.style_no AS product_style_no, 
                    p.name AS product_name,
                    p.image AS product_image,
                    p.brand AS brand_id
                FROM products AS p
                WHERE p.collection_id = ?
                ORDER BY p.position ASC
            ", [$id]);
        } else {
            $collection_name = 'All';

            $categories = DB::select("
                SELECT DISTINCT 
                    c.id AS category_id, 
                    c.name AS category_name,
                    p.brand AS brand_id
                FROM products AS p
                INNER JOIN categories AS c ON c.id = p.cat_id
                ORDER BY c.position ASC
            ");

            $products = DB::select("
                SELECT 
                    p.id, 
                    p.style_no AS product_style_no, 
                    p.name AS product_name,
                    p.image AS product_image,
                    p.brand AS brand_id
                FROM products AS p
                INNER JOIN collections AS c ON p.collection_id = c.id
                ORDER BY c.position ASC, p.position ASC
            ");
        }

        // Map brand IDs to names
        $brandMap = [
            1 => 'ONN',
            2 => 'PYNK',
            3 => 'Both',
        ];

        $resp = [
            'collection_name' => $collection_name,
            'category' => [],
            'product' => [],
        ];

        foreach ($categories as $category) {
            $resp['category'][] = [
                'cat_id' => $category->category_id,
                'cat_name' => $category->category_name,
                'brand' => $brandMap[$category->brand_id] ?? 'Unknown',
            ];
        }

        foreach ($products as $product) {
            $resp['product'][] = [
                'product_id' => $product->id,
                'product_style_no' => $product->product_style_no,
                'product_name' => $product->product_name,
                'product_image' => $product->product_image,
                'brand' => $brandMap[$product->brand_id] ?? 'Unknown',

            ];
        }

        return response()->json([
            'error' => false,
            'message' => 'Collection wise Category and Product list',
            'data' => $resp
        ]);
}

    public function categorywiseProduct(Request $request,$categoryId)
{
    $brandMap = [
        1 => 'ONN',
        2 => 'PYNK',
        3 => 'Both',
    ];

    $products = Product::where('cat_id', $categoryId)
        ->where('status', 1)
        ->where('is_deleted', 0)
        ->with(['colorSize', 'category'])
        ->orderBy('position_collection', 'asc')
        ->get()
        ->map(function ($product) use ($brandMap) {
            return [
                'product_id' => $product->id,
                'product_style_no' => $product->style_no,
                'product_name' => $product->name,
                'brand' => $brandMap[$product->brand] ?? 'Unknown',
                'category' => $product->category ? $product->category->name : null,
                'color_size' => $product->colorSize,
            ];
        });

    return response()->json([
        'error' => false,
        'resp' => 'Product data fetched successfully',
        'data' => $products,
    ]);
}
    public function productList(Request $request)
    {
        $brandMap = [
            1 => 'ONN',
            2 => 'PYNK',
            3 => 'Both',
        ];

        $data = Product::where('status', 1)
            ->where('is_deleted', 0)
            ->with(['category', 'collection','colorSize']) // optional: if you want category/color-size too
            ->orderBy('position_collection', 'asc')
            ->get()
            ->map(function ($product) use ($brandMap) {
                return [
                    'product_id' => $product->id,
                    'product_style_no' => $product->style_no,
                    'product_name' => $product->name,
                    'brand' => $brandMap[$product->brand] ?? 'Unknown',
                    'category' => $product->category ? $product->category->name : null,
                    'collection' => $product->collection ? $product->collection->name : null,
                    'color_size' => $product->colorSize ?? [],
                ];
            });

        return response()->json([
            'error' => false,
            'resp' => 'Product data fetched successfully',
            'data' => $data,
        ]);
    }


    public function productShow(Request $request, $id)
    {
        $productDetail = Product::findOrFail($id);
        $productColors = ProductColorSize::where('product_id', $id)->with('colorData','size')->groupBy('color_id')->orderBy('position')->get();

        $productColorsResp = [];
        foreach($productColors as $productColor) {
            $productColorsSizes = ProductColorSize::selectRaw('size_id AS size_id, price,offer_price')->where('product_id', $id)->where('color_id', $productColor->color_id)->orderBy('position')->get();

            $productColorsResp[] = [
                "color_id" => $productColor->colorData,
                "color_name" => $productColor->colorData->name,
                "size_details" => $productColorsSizes,
            ];
        }

        $resp = [
            'productDetail' => $productDetail,
            'variationDetail' => $productColorsResp,
            'categoryDetail' => $productDetail->category,
            'collectionDetail' => $productDetail->collection,
        ];

        return response()->json(['error' => false, 'resp' => 'Product detail fetch successfull', 'data' => $resp]);
    }
	


    public function collectionCategoryWiseProducts(Request $request, $collectionId, $categoryId)
    {
        $brandMap = [
            1 => 'ONN',
            2 => 'PYNK',
            3 => 'Both',
        ];

        $data = DB::table('products')
            ->select('id', 'style_no', 'name', 'master_pack', 'master_pack_count', 'position', 'image', 'brand')
            ->where('collection_id', $collectionId)
            ->where('cat_id', $categoryId)
            ->where('status', 1)
             ->where('is_deleted', 0)
            ->orderBy('style_no')
            ->get()
            ->map(function ($product) use ($brandMap) {
                return [
                    'id' => $product->id,
                    'style_no' => $product->style_no,
                    'name' => $product->name,
                    'master_pack' => $product->master_pack,
                    'master_pack_count' => $product->master_pack_count,
                    'position' => $product->position,
                    'image' => $product->image,
                    'brand' => $brandMap[$product->brand] ?? 'Unknown',
                ];
            });

        return response()->json([
            'error' => false,
            'message' => 'Collection and Category wise product data',
            'data' => $data,
        ]);
    }


     


    public function editStore(Request $request)
    {
         $validator = Validator::make($request->all(), [
            'id' => 'required',
            'image' => 'required',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()
            ], 400);
        }
        $updatedEntry = Store::findOrFail($request->id);
        $updatedEntry->image=$request->image;
        $updatedEntry->save();
        if( $updatedEntry){
            return response()->json(['status' => true,'message' => 'updated successfully','store' => $updatedEntry], 200);
        }else{
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }
    }

    //product color wise image
    public function productImages(Request $request,$product_id,$colorId)
    {
        
        $data = ProductImage::where('product_id','=',$product_id)->where('color_id',$colorId)->get();
		
        return response()->json(['error' => false, 'message' => 'Images fetch successfully', 'resp' => $data]);
        
    }

    public function productcolor(Request $request,$id)
    {
        $brandMap = [
            1 => 'ONN',
            2 => 'PYNK',
            3 => 'Both',
        ];
        $color=ProductColorSize::where('product_id',$id)->where('status',1)->distinct('color_id')->with('colorData:id,name,code')->get();
        if ($color) {
            return response()->json(['error'=>false, 'resp'=>'Color List fetched successfully','data'=>$color]);
        } else {
            return response()->json(['error' => true, 'resp' => 'Something happened']);
        }
    }

    public function multicolorsize(Request $request)
    {
        $respArray=[];
        $productId=$_GET['product_id'];
        $colorId=explode('*', $_GET['color_id']);
        
        foreach($colorId as $colorKey => $colorValue)
        {
            $colorDetails=Color::where('id',$colorValue)->first();
            $size=ProductColorSize::select('size_id')->where('product_id',$productId)->where('color_id',$colorValue)->where('status',1)->with('size:id,name,size_details')->get();
            $respArray[] = [
                'color_id' =>$colorDetails->id,
                'color_name' =>$colorDetails->name,
                'primarySizes' => $size,
            ];
        }
        if ($respArray) {
            return response()->json(['error'=>false, 'resp'=>'Size List fetched successfully','data'=>$respArray]);
        } else {
            return response()->json(['error' => true, 'resp' => 'Something happened']);
        }
    }

    //all store search area wise 
    public function searchProduct(Request $request)
    {
         $brandMap = [
            1 => 'ONN',
            2 => 'PYNK',
            3 => 'Both',
        ];

        $brand = $request->input('brand');
        $search = $request->input('keyword');

        // Handle brand logic (1 = ONN, 2 = PYNK, 3 = Both)
        $brands = ($brand == 3) ? [1, 2] : [$brand];

        $query = Product::where('status', 1)
            ->where('is_deleted', 0)
            ->with(['category', 'collection', 'colorSize'])
            ->orderBy('position_collection', 'asc');

        // Filter by brand (if brand is provided)
        if (!empty($brand)) {
            $query->whereIn('brand', $brands);
        }

        // Apply search keyword (if provided)
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('style_no', 'like', "%{$search}%")
                    ->orWhere('short_desc', 'like', "%{$search}%")
                    ->orWhere('desc', 'like', "%{$search}%");
            });
        }

        $data = $query->get()->map(function ($product) use ($brandMap) {
            return [
                'product_id' => $product->id,
                'product_style_no' => $product->style_no,
                'product_name' => $product->name,
                'brand' => $brandMap[$product->brand] ?? 'Unknown',
                'category' => $product->category->name ?? null,
                'collection' => $product->collection->name ?? null,
                'color_size' => $product->colorSize ?? [],
            ];
        });

        return response()->json([
            'error' => false,
            'resp' => 'Product data fetched successfully',
            'data' => $data,
        ]);

    }

    public function bulkAddTocart(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'user_id' => 'required',
            'store_id' => 'required',
            'product_id' => 'required',
            'order_type' => 'required',
            'color' => 'required',
            'brand' => 'required'
        ]);
        if(!$validator->fails()){
            $collectedData = $request->except('_token');
            $multiColorSizeQty = explode("|", $collectedData['color']);
            $colors = array();
            $sizes = array();
            $qtys = array();
            $multiPrice =array();
            // ✅ Convert brand name to code
            $brandMap = [
                'ONN' => 1,
                'PYNK' => 2,
                'Both' => 3,
            ];

            $brandCode = $brandMap[$collectedData['brand']] ?? null;
            foreach($multiColorSizeQty as $m){
                $str_arr = explode("*",$m);
                array_push($colors,$str_arr[0]);
                array_push($sizes,$str_arr[1]);
                array_push($qtys,$str_arr[2]);
                
            }
            $lastEntry = null;
            for($i=0;$i<count($colors);$i++)
            {
                $cartExists = Cart::where('product_id', $collectedData['product_id'])->where('user_id', $collectedData['user_id'])->where('color_id', $colors[$i])->where('size_id', $sizes[$i])->where('brand', $brandCode)->first();
                
    
                if ($cartExists) {
                        $cartExists->qty = $cartExists->qty + $qtys[$i];
                        $cartExists->save();
                } else {
                    if ($collectedData['order_type']) {
                        if ($collectedData['order_type'] == 'store-visit') {
                            $orderType = 'Store visit';
                        } else {
                            $orderType = 'Order on call';
                        }
                    } else {
                        $orderType = null;
                    }
                    
                    $newEntry = new Cart;
                    $newEntry->user_id = $collectedData['user_id'];
                    $newEntry->store_id = $collectedData['store_id'] ?? null;
                    $newEntry->order_type = $orderType;
                    $newEntry->product_id = $collectedData['product_id'];
                    $newEntry->color_id = $colors[$i];
                    $newEntry->size_id = $sizes[$i];
                    $newEntry->qty = $qtys[$i];
                    $newEntry->brand = $brandCode;
                    $newEntry->save();
                }
            }
            return response()->json(['error'=>false, 'resp'=>'Product added to cart successfully','data'=>$newEntry]);
        }else {
            return response()->json(['error' => true, 'resp' => $validator->errors()->first()]);
        }
    }

    public function qtyUpdate(Request $request, $cartId,$q)
    {
        $cart = Cart::findOrFail($cartId);
        dd($cart);
        if ($cart) {
			 $cart->qty = $q;
			 $cart->save();
            return response()->json([
                'error' => false,
                'resp' => 'Quantity updated'
            ]);
        } else {
            return response()->json([
                'error' => true,
                'resp' => 'Something Happened'
            ]);
        }
    }

    public function showByUser(Request $request, $id, $userId)
    {
        // Brand mapping
        $brandMap = [
            'ONN' => 1,
            'PYNK' => 2,
            'Both' => 3,
        ];

        $brandName = $request->brand; // e.g. ONN, PYNK, Both
        $brandId = $brandMap[$brandName] ?? null;

        // Base query
        $query = Cart::where('store_id', $id)
            ->where('user_id', $userId)
            ->with(['product:id,name,style_no,brand', 'color:id,name', 'size:id,name,size_details']);

        // Apply brand filter if provided
        if ($brandId) {
            if ($brandId == 3) {
                // If "Both", show all brands (1, 2, 3)
                $query->whereIn('brand', [1, 2, 3]);
            } else {
                // If ONN or PYNK, include its brand + "Both" (3)
                $query->whereIn('brand', [$brandId, 3]);
            }
        }

        $cart = $query->get();

        // Total quantity
        $total_quantity = $cart->sum('qty');

        // Response
        return response()->json([
            'error' => false,
            'resp' => 'Cart list fetched successfully',
            'data' => $cart,
            'total_quantity' => $total_quantity,
        ]);
    }


    public function cartDelete(Request $request,$id)
    {
        $cart=Cart::destroy($id);
        if ($cart) {
            return response()->json(['error'=>false, 'resp'=>'Product removed from cart']);
        } else {
            return response()->json(['error' => true, 'resp' => 'Something happened']);
        }
    }

    public function cartPreviewPDF_URL(Request $request, $id,$userId)
    {
        return response()->json([
            'error' => false,
            'resp' => 'URL generated',
            'data' => url('/').'/api/cart/pdf/view/'.$id.'/'.$userId,
        ]);
    }

    

    public function cartPreviewPDF_view(Request $request, $id, $userId, $brand)
    {
        // Map brand name to code
        $brandMap = [
            'ONN' => 1,
            'PYNK' => 2,
            'Both' => 3,
        ];

        $brandCode = $brandMap[$brand] ?? null;

        // Base query
        $query = Cart::where('store_id', $id)
            ->where('user_id', $userId)
            ->with(['product', 'stores', 'color', 'size']);

        // Apply brand filter
        if ($brandCode) {
            if ($brandCode == 3) {
                // If "Both", show all (ONN, PYNK, Both)
                $query->whereIn('brand', [1, 2, 3]);
            } else {
                // If ONN or PYNK, show its brand and "Both"
                $query->whereIn('brand', [$brandCode, 3]);
            }
        }

        $cartData = $query->get()->toArray();

        return view('api.cart-pdf', compact('cartData'));
    }


    public function placeOrderUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => ['required'],
            'user_id' => ['required'],
            'brand' => ['required'],
            'order_type' => ['required', 'string', 'min:1'],
            'order_lat' => ['required', 'string', 'min:1'],
            'order_lng' => ['required', 'string', 'min:1'],
            'comment' => ['nullable', 'string', 'min:1'],
            'brand' => ['required']
        ]);

        if (!$validator->fails()) {
            $params = $request->except('_token');
            $collectedData = collect($params);
            $brandMap = [
                'ONN'  => 1,
                'PYNK' => 2,
                'Both' => 3,
            ];

            $brandValue = $brandMap[$request->brand] ?? null;

            if (!$brandValue) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid brand value.',
                ]);
            }
            $cart_count = Cart::where('store_id', $collectedData['store_id'])->where('user_id',$collectedData['user_id'])->where('brand',$brandValue)->get();
            if (!empty($cart_count) ) {
			    $order_no = generateOrderNumber('secondary', $collectedData['store_id'])[0];
                $sequence_no = generateOrderNumber('secondary', $collectedData['store_id'])[1];
                // 1 order
                $newEntry = new Order;
                $newEntry->sequence_no = $sequence_no;
                $newEntry->order_no = $order_no;
                $newEntry->store_id = $collectedData['store_id'];
                $newEntry->brand = $brandValue;
                $newEntry->user_id = $collectedData['user_id'];
                //$newEntry->distributor_id = $collectedData['distributor_id'] ?? '';
                $aseDetails=DB::select("select * from employees where id='".$collectedData['user_id']."'");
                $aseName=$aseDetails[0]->name;
                $user=$newEntry->store_id;
    			$result = DB::select("select * from stores where id='".$user."'");
                $item=$result[0];
                $name = $item->name;
                $newEntry->order_type = $collectedData['order_type'] ?? null;
                $newEntry->order_lat = $collectedData['order_lat'] ?? null;
                $newEntry->order_lng = $collectedData['order_lng'] ?? null;
    
    			$newEntry->email = $item->email;
    			$newEntry->mobile = $item->contact;
                // fetch cart details
                
                $subtotal = $totalOrderQty = 0;
                foreach($cart_count as $cartValue) {
                    $totalOrderQty += $cartValue->qty;
                    $subtotal += $cartValue->product->offer_price * $cartValue->qty;
                    $store_id = $cartValue->store_id;
                    $order_type = $cartValue->order_type;
                }
                $newEntry->amount = $subtotal;
                $newEntry->comment = $collectedData['comment'] ?? null;
                $total = (int) $subtotal;
                $newEntry->final_amount = $total;
                $newEntry->save();
                // 2 insert cart data into order products
                $orderProducts = [];
                foreach($cart_count as $cartValue) {
                    $orderProducts[] = [
                        'order_id' => $newEntry->id,
                        'product_id' => $cartValue->product_id,
                        'color_id' => $cartValue->color_id,
                        'size_id' => $cartValue->size_id,
                        'qty' => $cartValue->qty,
                        "created_at" => date('Y-m-d H:i:s'),
                        "updated_at" => date('Y-m-d H:i:s'),
                    ];
                }
                $orderProductsNewEntry = OrderProduct::insert($orderProducts);
                  Cart::where('store_id', $newEntry->store_id)->where('user_id',$newEntry->user_id)->where('brand',$brandValue)->delete();
    
    			// notification: sender, receiver, type, route, title
                // notification to ASE
                sendNotification($collectedData['user_id'], 'admin', 'secondary-order-place', 'front.user.order', $totalOrderQty.' New order placed',$totalOrderQty.' new order placed  '.$name);
    
    
    			// notification to ASM
    			$loggedInUser = $aseName;
    				$asm = DB::select("SELECT u.id as asm_id FROM `teams` t  INNER JOIN employees u ON u.id = t.asm_id where t.ase_id = '".$collectedData['user_id']."' GROUP BY t.asm_id");
    			foreach($asm as $value){
    				sendNotification($collectedData['user_id'], $value->asm_id, 'secondary-order-place', 'front.user.order', $totalOrderQty.' new order placed by ' .$loggedInUser ,$totalOrderQty.' new order placed from  '.$name);
    			}
    
               
    			// notification to RSM
    			$loggedInUser = $aseName;
    			$rsm = DB::select("SELECT u.id as rsm_id FROM `teams` t  INNER JOIN employees u ON u.id = t.rsm_id where t.ase_id = '".$collectedData['user_id']."' GROUP BY t.rsm_id");
    			foreach($rsm as $value){
    				sendNotification($collectedData['user_id'], $value->rsm_id, 'secondary-order-place', 'front.user.order', $totalOrderQty.' new order placed by ' .$loggedInUser ,$totalOrderQty.' new order placed from  '.$name);
    			}
    			
    			// notification to vp
    			$loggedInUser = $aseName;
    			$zsm = DB::select("SELECT u.id as vp_id FROM `teams` t  INNER JOIN employees u ON u.id = t.vp_id where t.ase_id = '".$collectedData['user_id']."' GROUP BY t.vp_id");
    			foreach($zsm as $value){
    				sendNotification($collectedData['user_id'], $value->vp_id, 'secondary-order-place', 'front.user.order', $totalOrderQty.' new order placed by ' .$loggedInUser ,$totalOrderQty.' new order placed from  '.$name);
    			}
    
    
                return response()->json(['error'=>false, 'resp'=>'Order placed successfully','data'=>$newEntry]);
            }else{
                return response()->json(['error'=>true, 'resp'=>'cart empty']);
            }
        } else {
            return response()->json(['status' => 400, 'resp' => $validator->errors()->first()]);
        }
    }

    public function orderPDF_URL(Request $request, $id)
    {
        return response()->json([
            'error' => false,
            'resp' => 'URL generated',
            'data' => url('/').'/api/order/pdf/view/'.$id,
        ]);
    }

    

    public function orderPDF_view(Request $request, $id)
    {
        $orderData =OrderProduct::where('order_id',$id)->with('product','color','size','orders')->get()->toArray();
		
        return view('api.order-pdf', compact('orderData','id'));
    }

    public function orderList(Request $request,$id,$userId)
    {
        $brandMap = [
                'ONN' => 1,
                'PYNK' => 2,
                'Both' => 3,
            ];

        $brandCode = $brandMap[$request->brand] ?? null;
        $order=Order::where('store_id',$id)->where('user_id',$userId)->where('brand',$brandCode)->orderby('id','desc')->with('stores:id,name')->get();
        if ($order) {
            return response()->json(['error'=>false, 'resp'=>'order List fetched successfully','data'=>$order]);
        } else {
            return response()->json(['error' => true, 'resp' => 'Something happened']);
        }
    }

    public function orderDetails(Request $request,$id)
    {
        $order=OrderProduct::where('order_id',$id)->with('product','color','size','orders')->get();
        if ($order) {
            return response()->json(['error'=>false, 'resp'=>'order details fetched successfully','data'=>$order]);
        } else {
            return response()->json(['error' => true, 'resp' => 'Something happened']);
        }
    }

        public function myOrdersFilter(Request $request){
            $validator = Validator::make($request->all(), [
                'user_id' => ['required'],
                'store_id' => ['nullable'],
                'date_from' => ['nullable'],
                'date_to' => ['nullable'],
                'brand' => ['required'],
            ]);
    
            $user_id = $request->user_id;
            $brandMap = [
                'ONN' => 1,
                'PYNK' => 2,
                'Both' => 3,
            ];

            $brandCode = $brandMap[$request->brand] ?? null;
            if (!$validator->fails()) {
                    // date from
                    if (!empty($request->date_from)) {
                        $from = date('Y-m-d', strtotime($request->date_from));
                    } else {
                        $from = date('Y-m-01');
                    }
    
                    // date to
                    if (!empty($request->date_to)) {
                        //$to = date('Y-m-d', strtotime($request->date_to. '+1 day'));
                        $to = $request->date_to;
                    } else {
                        $to = date('Y-m-d');
                    }
                    
                    $orderByQuery = 'o.id DESC';
    
                    $orders = array();
    
                    if(!empty($request->store_id)){
                        $store_id = $request->store_id;
                        $ordersData = DB::select("SELECT * FROM `orders` AS o
                        WHERE o.user_id = '".$user_id."' AND o.store_id = '".$store_id."' AND o.brand= '".$brandCode."'
                        AND (date(o.created_at) BETWEEN '".$from."' AND '".$to."')
                        ORDER BY ".$orderByQuery);
                    }else{
                        $ordersData = DB::select("SELECT * FROM `orders` AS o
                        WHERE o.user_id = '".$user_id."' AND o.brand= '".$brandCode."'
                        AND (date(o.created_at) BETWEEN '".$from."' AND '".$to."')
                        ORDER BY ".$orderByQuery);
                    }
                    
                    
                    foreach($ordersData as $o){
                        $store_id = $o->store_id;
                        $user_id = $o->user_id;
                        $order_id = $o->id;
    
                        $storesData = Store::where('id',$store_id)->with('states','areas')->first();
                        $usersData = Employee::where('id',$user_id)->first();
                        $orderResult = OrderProduct::select(DB::raw("IFNULL(SUM(qty),0) as product_count"))->where('order_id',$order_id)->get();
                        $o->stores = $storesData;
                        $o->employees = $usersData;
                        $o->product_count = $orderResult[0]->product_count;
                        array_push($orders,$o);
                    }
                
            }else{
                $orders = array();
            }
            
            return response()->json(['error' => false, 'resp' => 'Store orders with filter', 'data' => $orders]);
        }
        
    public function catalogueList(Request $request)
    {
        $brandMap = [
            1 => 'ONN',
            2 => 'PYNK',
            3 => 'Both',
        ];

        $data = ProductCatalogue::where('status', 1)
            ->where('is_deleted', 0)
            ->with(['category', 'collection','colorSize']) // optional: if you want category/color-size too
            ->orderBy('position_collection', 'asc')
            ->get()
            ->map(function ($product) use ($brandMap) {
                return [
                    'product_id' => $product->id,
                    'product_style_no' => $product->style_no,
                    'product_name' => $product->name,
                    'brand' => $brandMap[$product->brand] ?? 'Unknown',
                    'category' => $product->category ? $product->category->name : null,
                    'collection' => $product->collection ? $product->collection->name : null,
                    'color_size' => $product->colorSize ?? [],
                ];
            });

        return response()->json([
            'error' => false,
            'resp' => 'Product data fetched successfully',
            'data' => $data,
        ]);
    }


   





	
















}
 