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
use App\Models\Size;
use App\Models\Collection;
use App\Models\ProductImage;
use App\Models\Product;
use App\Models\ProductColorSize;
use App\Models\UserNoOrderReason;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderProductDistributor;
use App\Models\OrderDistributor;
use App\Models\DistributorMom;
use App\Models\CartDistributor;
use App\Models\Activity;
use App\Models\Scheme;
use App\Models\News;
use App\Models\ProductCatalogue;
use App\Models\RetailerProduct;
use App\Models\RetailerBarcode;
use App\Models\RewardCart;
use App\Models\SecondaryAseOrder;
use App\Models\RetailerOrder;
use App\Models\RewardOrderProduct;
use App\Models\RetailerUserTxnHistory;
use App\Models\RetailerWalletTxn;
use Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Models\UserPermissionCategory;
use DB;
use Auth;
use Hash;
use Illuminate\Support\Facades\Log;
use App\Exports\SecondarySalesExport;
use App\Exports\SecondarySalesProductExport;
use Maatwebsite\Excel\Facades\Excel;
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
                ->with('area','state')
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
            return response()->json(['status'=>true, 'message'=>'Visit already started','area_id'=>$area->areas->id,'area'=>$area->areas->name,'visit_id'=>$area->id,'data'=>$user],200);
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
        "entry_date" => $request->start_date,
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
    
 

public function aseSalesreport(Request $request)
{
    $validator = Validator::make($request->all(), [
        "ase_id" => "required",
        "brand" => "required",
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
    }

    $ase = $request->ase_id;
    $from = $request->filled('from') ? date('Y-m-d', strtotime($request->from)) : date('Y-m-01');
    $to   = $request->filled('to') ? date('Y-m-d', strtotime($request->to)) : date('Y-m-d');
    
    $brandMap = [
        'ONN'  => 1,
        'PYNK' => 2,
        'Both' => 3,
    ];
    $brandCode = $request->brand;
    $brandName = $brandMap[$request->brand] ?? '';
    
    
    
    $respArrd = [];
    $respArr  = [];

    /**
     * ✅ PRIMARY (Distributor-wise)
     */
    $distributors = Team::where('ase_id', $ase)
        ->where('brand', $brandName)
        ->whereNull('store_id')
        ->whereHas('distributor', function ($q) use ($brandName) {
            $q->where('brand', $brandName)
              ->where('status', 1)
              ->where('is_deleted', 0);
        })
        ->with('distributor:id,name')
        ->distinct('distributor_id') // ✅ ensures each distributor_id only once
        ->get(['distributor_id']);

    foreach ($distributors as $item) {
        $qty = PrimaryOrder::where('distributor_id', $item->distributor_id)
            ->where('brand', $brandName)
            ->whereBetween('order_date', [$from, $to])
            ->sum('qty');

        // 🚫 Skip if no quantity (no orders)
        if ($qty <= 0) continue;

        $respArrd[] = [
            'distributor_id'   => $item->distributor_id ?? 0,
            'distributor_name' => $item->distributor->name ?? '',
            'brand'            => $request->brand,
            'amount'           => 0,
            'qty'              => $qty,
        ];
    }

    /**
     * ✅ SECONDARY (Retailer-wise)
     */
    $stores = Store::where('user_id', $ase)
        ->where('brand', $brandName)
        ->where('status', 1)
        ->where('is_deleted', 0)
        ->get();
    
    foreach ($stores as $store) {
        $qty = SecondaryOrder::where('retailer_id', $store->id)
            ->where('brand', $brandName)
            ->whereBetween('order_date', [$from, $to])
            ->sum('qty');
            

        // 🚫 Skip if no quantity (no orders)
        if ($qty <= 0) continue;

        $respArr[] = [
            'retailer_id' => $store->id,
            'store_name'  => $store->name,
            'brand'       => $request->brand,
            'amount'      => 0,
            'qty'         => $qty,
        ];
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
        $area = $_GET['area_id'];
        $brandMap = [
            1 => 'ONN',
            2 => 'PYNK',
            3 => 'Both',
        ];

		
		$stores = Store::where('user_id',$ase)->where('area_id',$area)->where('status',1)->where('is_deleted',0)->with('state','area','user')->get();
		
	
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
        $validator = Validator::make($request->all(),[
            'user_id' => 'required',
            'area_id' => 'required',
            'keyword' => 'required'
        ]);

        if($validator->fails()){
            return response()->json(['error' => true, 'resp' => $validator->errors()->first()]);
        }
        $userId = $_GET['user_id'];
        $areaId = $_GET['area_id'];
        $search = $request->keyword ?? '';

        // Brand map
        $brandMap = [
            1 => 'ONN',
            2 => 'PYNK',
            3 => 'Both',
        ];

        // Base query
        $query = Store::select('*')->where('user_id',$userId)->where('area_id',$areaId)
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
            ->distinct('distributor_id') // ✅ ensures each distributor_id only once
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
        $retailerListOfOcc->brand = $brandValue;
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
        	sendNotification($store->user_id, $brandValue, 'admin', 'store-add', 'admin.stores.index', $store->name. '  added by ' .$loggedInUser , '  Store ' .$store->name.' added');
        	// notification to ASM
        	$loggedInUser = $name;
        	$asm = DB::select("SELECT u.id as asm_id FROM `teams` t  INNER JOIN users u ON u.id = t.asm_id where t.ase_id = '$request->user_id' GROUP BY t.asm_id");
                foreach($asm as $value){
                    sendNotification($store->user_id, $brandValue, $value->asm_id, 'store-add', 'front.store.index', $store->name. '  added by ' .$loggedInUser , '  Store ' .$store->name.' added');
                }
                // notification to RSM
                $loggedInUser = $name;
                $rsm = DB::select("SELECT u.id as rsm_id FROM `teams` t  INNER JOIN users u ON u.id = t.rsm_id where t.ase_id = '$request->user_id' GROUP BY t.rsm_id");
                foreach($rsm as $value){
                    sendNotification($store->user_id,$brandValue, $value->rsm_id, 'store-add', '', $store->name. '  added by '  .$loggedInUser ,' Store ' .$store->name. ' added');
                }

               
                
                // notification to VP
                $loggedInUser = $name;
                $vp = DB::select("SELECT u.id as vp_id FROM `teams` t  INNER JOIN users u ON u.id = t.vp_id where t.ase_id = '$request->user_id' GROUP BY t.vp_id");
                foreach($vp as $value){
                    sendNotification($store->user_id, $brandValue ,$value->vp_id, 'store-add', '', $store->name. '  added by ' .$loggedInUser ,'Store ' .$store->name.' added  ');
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
                WHERE p.collection_id = ? AND c.status=1
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
                WHERE p.collection_id = ? AND p.status=1
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
                WHERE c.status=1
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
                 WHERE p.status=1
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
                $cartExists = Cart::where('product_id', $collectedData['product_id'])->where('user_id', $collectedData['user_id'])->where('store_id', $collectedData['store_id'])->where('color_id', $colors[$i])->where('size_id', $sizes[$i])->where('brand', $brandCode)->first();
                
    
                if ($cartExists) {
                        $cartExists->qty = $cartExists->qty + $qtys[$i];
                        $cartExists->save();
                        return response()->json(['error'=>false, 'resp'=>'Product qty updated','data'=>$cartExists]);
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
            if($newEntry){
                return response()->json(['error'=>false, 'resp'=>'Product added to cart successfully','data'=>$newEntry]);
            }else{
                return response()->json(['error'=>false, 'resp'=>'Something happend']);
            }
        }else {
            return response()->json(['error' => true, 'resp' => $validator->errors()->first()]);
        }
    }

    public function cartqtyUpdate(Request $request)
    {
        $cart = Cart::findOrFail($request->cartId);
        
        if ($cart) {
			 $cart->qty = $request->qty;
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

    public function showByUser(Request $request)
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
        $query = Cart::where('store_id', $request->storeId)
            ->where('user_id', $request->userId)->whereHas('product')
            ->with([
                'size:id,name,size_details',
                'color:id,name',
                'product' => function ($q) {
                    $q->select('id', 'name', 'style_no','brand')
                        ->where('status', 1)
                        ->where('is_deleted', 0);
                }
            ]);
            //->with(['product:id,name,style_no,brand', 'color:id,name', 'size:id,name,size_details']);

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

    public function cartPreviewPDF_URL(Request $request)
    {
        $brandMap = [
            'ONN' => 1,
            'PYNK' => 2,
            'Both' => 3,
        ];

        $brandName = $request->brand; // e.g. ONN, PYNK, Both
        $brandId = $brandMap[$brandName] ?? null;
        return response()->json([
            'error' => false,
            'resp' => 'URL generated',
            'data' => url('/').'/api/cart/pdf/view/?storeId='.$request->storeId.'&userId='.$request->userId.'&brand='.$brandId,
        ]);
    }

    

    public function cartPreviewPDF_view(Request $request)
    {
        // Map brand name to code
        $brandMap = [
            'ONN' => 1,
            'PYNK' => 2,
            'Both' => 3,
        ];

        $brandCode = $brandMap[$request->brand] ?? null;

        // Base query
        $query = Cart::where('store_id', $request->storeId)
            ->where('user_id', $request->userId)->whereHas('product')
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

        $cartData = $query->get();
      
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
            $team=Team::where('store_id',$collectedData['store_id'])->first();

            $cart_count = Cart::where('store_id', $collectedData['store_id'])
            ->where('user_id',$collectedData['user_id'])
            ->where('brand',$brandValue)->whereHas('product')
            ->with(['product' => function ($query) {
                $query->where('status', 1)
                    ->where('is_deleted', 0);
            }])->get();
            //dd($cart_count);
            if ($cart_count->isNotEmpty()) {

                $firstCart = $cart_count->first();

                if ($firstCart->brand == 1) {
                    [$order_no, $sequence_no] = generateONNOrderNumber('secondary', $collectedData['store_id']);
                } else {
                    [$order_no, $sequence_no] = generatePYNKOrderNumber('secondary', $collectedData['store_id']);
                }
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
                foreach ($cart_count as $cartValue) {
                    if ($cartValue->product) {
                        $totalOrderQty += $cartValue->qty;
                        $subtotal += $cartValue->product->offer_price * $cartValue->qty;
                        $store_id = $cartValue->store_id;
                        $order_type = $cartValue->order_type;
                    } else {
                        return response()->json(['error' => true, 'resp' => 'Product not exist or inactive/deleted']);
                    }
                }
                $newEntry->amount = $subtotal;
                $newEntry->comment = $collectedData['comment'] ?? null;
                $total = (int) $subtotal;
                $newEntry->final_amount = $total;

                $matchedDistributorId = null;
                $collectionIds = $cart_count->pluck('product.collection_id')->filter()->unique()->toArray();
                if (!empty($collectionIds)) {
                    $distributorRanges = DB::table('distributor_ranges')
                        ->whereIn('collection_id', $collectionIds)
                        ->pluck('distributor_id')
                        ->toArray();

                    $team = DB::table('teams')->where('store_id', $collectedData['store_id'])->first();
                    if ($team && $team->distributor_id) {
                        $teamDistributorIds = array_map('trim', explode(',', $team->distributor_id));
                        $matched = array_intersect($distributorRanges, $teamDistributorIds);
                        if (!empty($matched)) {
                            $matchedDistributorId = reset($matched);
                        }
                    }
                }

                $newEntry->distributor_id = $matchedDistributorId ?? null;
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
                sendNotification($collectedData['user_id'], $brandValue,'admin', 'secondary-order-place', 'front.user.order', $totalOrderQty.' New order placed',$totalOrderQty.' new order placed  '.$name);
    
    
    			// notification to ASM
    			$loggedInUser = $aseName;
    				$asm = DB::select("SELECT u.id as asm_id FROM `teams` t  INNER JOIN employees u ON u.id = t.asm_id where t.ase_id = '".$collectedData['user_id']."' GROUP BY t.asm_id");
    			foreach($asm as $value){
    				sendNotification($collectedData['user_id'], $brandValue, $value->asm_id, 'secondary-order-place', 'front.user.order', $totalOrderQty.' new order placed by ' .$loggedInUser ,$totalOrderQty.' new order placed from  '.$name);
    			}
    
               
    			// notification to RSM
    			$loggedInUser = $aseName;
    			$rsm = DB::select("SELECT u.id as rsm_id FROM `teams` t  INNER JOIN employees u ON u.id = t.rsm_id where t.ase_id = '".$collectedData['user_id']."' GROUP BY t.rsm_id");
    			foreach($rsm as $value){
    				sendNotification($collectedData['user_id'], $brandValue, $value->rsm_id, 'secondary-order-place', 'front.user.order', $totalOrderQty.' new order placed by ' .$loggedInUser ,$totalOrderQty.' new order placed from  '.$name);
    			}
    			
    			// notification to vp
    			$loggedInUser = $aseName;
    			$zsm = DB::select("SELECT u.id as vp_id FROM `teams` t  INNER JOIN employees u ON u.id = t.vp_id where t.ase_id = '".$collectedData['user_id']."' GROUP BY t.vp_id");
    			foreach($zsm as $value){
    				sendNotification($collectedData['user_id'], $brandValue, $value->vp_id, 'secondary-order-place', 'front.user.order', $totalOrderQty.' new order placed by ' .$loggedInUser ,$totalOrderQty.' new order placed from  '.$name);
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
        $orderData =OrderProduct::where('order_id',$id)->whereHas('product')->with('product','color','size','orders')->get();
		
        return view('api.order-pdf', compact('orderData','id'));
    }

    public function orderList(Request $request)
    {
        $brandMap = [
            'ONN' => 1,
            'PYNK' => 2,
            'Both' => 3,
        ];

        $brandCode = $brandMap[$request->brand] ?? null;

        $orderQuery = Order::where('store_id', $request->storeId)
            ->where('user_id', $request->userId)
            ->where('brand', $brandCode)
            ->with('stores:id,name')
            ->orderBy('id', 'desc');

        // ✅ Apply date filters only if provided
        if ($request->filled('from') && $request->filled('to')) {
            $fromDate = date('Y-m-d 00:00:00', strtotime($request->from));
            $toDate = date('Y-m-d 23:59:59', strtotime($request->to));

            $orderQuery->whereBetween('created_at', [$fromDate, $toDate]);
        }

        $orders = $orderQuery->get();
        // ✅ Add total quantity field to each order
        $orders->map(function ($order) {
            $order->total_qty = $order->orderProducts->sum('qty');
            unset($order->orderProducts); // optional: remove detailed items if not needed
            return $order;
        });
        if ($orders->isNotEmpty()) {
            return response()->json([
                'error' => false,
                'resp' => 'Order list fetched successfully',
                'data' => $orders
            ]);
        } else {
            return response()->json([
                'error' => true,
                'resp' => 'No orders found for the given filters'
            ]);
        }
    }

    public function orderDetails(Request $request,$id)
    {
        $order=OrderProduct::where('order_id',$id)->whereHas('product')->with('product','product.collection','product.category','color','size','orders','orders.stores')->get();
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
            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
            }
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
    
                        $storesData = Store::where('id',$store_id)->with('state','area')->first();
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
        if (!$request->user_id) {
            return response()->json([
                'error' => true,
                'resp'  => 'User not found.',
                'data'  => []
            ]);
        }

        $brandMap = [
            1 => 'ONN',
            2 => 'PYNK',
            3 => 'Both',
        ];

        $user = Employee::find($request->user_id);

        // 🔹 Get VP IDs related to this user
        $vpIds = Team::where(function ($query) use ($request) {
                    $query->where('ase_id', $request->user_id)
                        ->orWhere('asm_id', $request->user_id)
                        ->orWhere('rsm_id', $request->user_id)
                        ->orWhere('distributor_id', $request->user_id);
                    })
                ->pluck('vp_id') // ✅ extract just the IDs
                ->unique()
                ->toArray();

        // 🔹 Get State IDs related to this user
        $stateIds = Team::where(function ($query) use ($request) {
                $query->where('ase_id', $request->user_id)
                    ->orWhere('asm_id', $request->user_id)
                    ->orWhere('rsm_id', $request->user_id)
                    ->orWhere('distributor_id', $request->user_id);
            })
            ->pluck('state_id')
            ->unique()
            ->toArray();
        
        $data = ProductCatalogue::where('status', 1)
            ->where('is_deleted', 0)
            ->where(function ($query) use ($vpIds, $stateIds) {
                    $query->whereRaw("FIND_IN_SET(?, vp_id)", [$vpIds])
                          ->orWhereRaw("FIND_IN_SET(?, state_id)", [$stateIds]);
                })
            ->orderBy('id', 'desc')
            ->get();
            

        if ($data->isNotEmpty()) {
            // Add readable brand names
            $data = $data->map(function ($item) use ($brandMap) {
                $item->brand_name = $brandMap[$item->brand] ?? null;
                return $item;
            });

            return response()->json([
                'status'  => true,
                'message' => 'Catalogue data fetched successfully',
                'data'    => $data,
            ], 200);
        }else{

            return response()->json([
                'status'  => false,
                'message' => 'No catalogue data found',
            ], 404);
        }
    }



    public function schemeList(Request $request)
    {
        $brandMap = [
            1 => 'ONN',
            2 => 'PYNK',
            3 => 'Both',
        ];

        $data = Scheme::where('is_deleted', 0)
            ->orderBy('id', 'desc')
            ->get();
        if ($data->isNotEmpty()) {
            // Transform brand values
            $data = $data->map(function ($store) use ($brandMap) {
                $store->brand_name = $brandMap[$store->brand] ?? null; // readable brand name
                return $store;
            });

            return response()->json([
                'status'  => true,
                'message' => 'Scheme data fetched successfully',
                'data'    => $data,
            ], 200);
        } else {
            return response()->json([
                'status'  => false,
                'message' => 'No scheme data found',
            ], 404);
        }
    }


    public function newsList(Request $request)
    {
        // Define your brand map first
        $brandMap = [
            1 => 'ONN',
            2 => 'PYNK',
            3 => 'Both',
        ];

        $user = Employee::find($request->user_id);

        if (!$user) {
            return response()->json([
                'error' => true,
                'resp'  => 'User not found.',
                'data'  => []
            ], 404);
        }

        $userType = $user->type;
        $today = date('Y-m-d');

        $data = News::where('status', 1)
            ->where('is_deleted', 0)
            ->whereRaw("FIND_IN_SET(?, user_type)", [$userType])
            ->whereDate('end_date', '>=', $today)
            ->get();

        if ($data->isNotEmpty()) {
            $data = $data->map(function ($news) use ($brandMap) {
                // If 'brand' is comma separated (like "1,2"), map multiple names
                if (strpos($news->brand, ',') !== false) {
                    $brands = explode(',', $news->brand);
                    $brandNames = array_map(fn($b) => $brandMap[trim($b)] ?? $b, $brands);
                    $news->brand_name = implode(', ', $brandNames);
                } else {
                    $news->brand_name = $brandMap[$news->brand] ?? $news->brand;
                }

                return $news;
            });

            return response()->json([
                'status'  => true,
                'message' => 'News data fetched successfully',
                'data'    => $data,
            ], 200);
        }else{

            return response()->json([
                'status'  => false,
                'message' => 'No news data found',
                'data'    => [],
            ], 404);
        }
    }


    //primary order
    public function primaryorderList(Request $request)
    {
        $brandMap = [
            'ONN' => 1,
            'PYNK' => 2,
            'Both' => 3,
        ];

        $brandCode = $brandMap[$request->brand] ?? null;

        $orderQuery = OrderDistributor::where('distributor_id', $request->distributorId)
            ->where('user_id', $request->userId)
            ->where('brand', $brandCode)
            ->with('distributors:id,name')
            ->orderBy('id', 'desc');

        // ✅ Apply date filters only if provided
        if ($request->filled('from') && $request->filled('to')) {
            $fromDate = date('Y-m-d 00:00:00', strtotime($request->from));
            $toDate = date('Y-m-d 23:59:59', strtotime($request->to));

            $orderQuery->whereBetween('created_at', [$fromDate, $toDate]);
        }

        $orders = $orderQuery->get();
        // ✅ Add total quantity field to each order
        $orders->map(function ($order) {
            $order->total_qty = $order->orderProducts->sum('qty');
            unset($order->orderProducts); // optional: remove detailed items if not needed
            return $order;
        });
        if ($orders->isNotEmpty()) {
            return response()->json([
                'error' => false,
                'resp' => 'Order list fetched successfully',
                'data' => $orders
            ]);
        } else {
            return response()->json([
                'error' => true,
                'resp' => 'No orders found for the given filters'
            ]);
        }
    }

    public function primaryorderDetails(Request $request,$id)
    {
        $order=OrderProductDistributor::where('order_id',$id)->whereHas('product')->with('product','product.collection','product.category','color','size','orders','orders.distributors')->get();
        if ($order) {
            return response()->json(['error'=>false, 'resp'=>'order details fetched successfully','data'=>$order]);
        } else {
            return response()->json(['error' => true, 'resp' => 'Something happened']);
        }
    }

    public function momStore(Request $request)
    {
        //dd($request->all);
         $validator = Validator::make($request->all(), [
            'distributor_id' => ['required'],
            'ase_id' => ['required'],
            'comment' => ['nullable', 'string', 'min:1'],
            'brand'  => ['required'],
           
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()
            ], 400);
        }
        // ✅ Convert brand name to code
            $brandMap = [
                'ONN' => 1,
                'PYNK' => 2,
                'Both' => 3,
            ];

        $brandCode = $brandMap[$request['brand']] ?? null;
        $data = new DistributorMom;
        $data->user_id = $request->ase_id;
        $data->distributor_id = $request->distributor_id;
        $data->brand = $brandCode;
        $data->comment = $request->comment;
        $data->save();
        if($data){
            return response()->json(['error'=>false, 'resp'=>'MOM added successfully','data'=>$data]);
        } else {
            return response()->json([
                'error' => true,
                'resp' => 'Something Happened'
            ]);
        }
    }


    public function momList(Request $request)
    {
        $brandMap = [
            'ONN' => 1,
            'PYNK' => 2,
            'Both' => 3,
        ];

        $brandCode = $brandMap[$request->brand] ?? null;

        $orderQuery = DistributorMom::where('distributor_id', $request->distributorId)->where('user_id', $request->user_id)
            ->where('brand', $brandCode)
            ->with('distributors:id,name')
            ->with('ase:id,name')
            ->orderBy('id', 'desc');

        // ✅ Apply date filters only if provided
        if ($request->filled('from') && $request->filled('to')) {
            $fromDate = date('Y-m-d 00:00:00', strtotime($request->from));
            $toDate = date('Y-m-d 23:59:59', strtotime($request->to));

            $orderQuery->whereBetween('created_at', [$fromDate, $toDate]);
        }

        $orders = $orderQuery->get();

        if ($orders->isNotEmpty()) {
            return response()->json([
                'error' => false,
                'resp' => 'Mom list fetched successfully',
                'data' => $orders
            ]);
        } else {
            return response()->json([
                'error' => true,
                'resp' => 'No mom found for the given filters'
            ]);
        }
    }

    //place order

    public function distributorbulkAddTocart(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'user_id' => 'required',
            'distributor_id' => 'required',
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
                $cartExists = CartDistributor::where('product_id', $collectedData['product_id'])->where('user_id', $collectedData['user_id'])->where('distributor_id', $collectedData['distributor_id'])->where('color_id', $colors[$i])->where('size_id', $sizes[$i])->where('brand', $brandCode)->first();
                
    
                if ($cartExists) {
                        $cartExists->qty = $cartExists->qty + $qtys[$i];
                        $cartExists->save();
                        return response()->json(['error'=>false, 'resp'=>'Product qty updated','data'=>$cartExists]);
                } else {
                    if ($collectedData['order_type']) {
                        if ($collectedData['order_type'] == 'distributor-visit') {
                            $orderType = 'Distributor visit';
                        } else {
                            $orderType = 'Order on call';
                        }
                    } else {
                        $orderType = null;
                    }
                    
                    $newEntry = new CartDistributor;
                    $newEntry->user_id = $collectedData['user_id'];
                    $newEntry->distributor_id = $collectedData['distributor_id'] ?? null;
                    $newEntry->order_type = $orderType;
                    $newEntry->product_id = $collectedData['product_id'];
                    $newEntry->color_id = $colors[$i];
                    $newEntry->size_id = $sizes[$i];
                    $newEntry->qty = $qtys[$i];
                    $newEntry->brand = $brandCode;
                    $newEntry->save();
                }
            }
            if($newEntry){
                return response()->json(['error'=>false, 'resp'=>'Product added to cart successfully','data'=>$newEntry]);
            }else{
                return response()->json(['error'=>false, 'resp'=>'Something happend']);
            }
        }else {
            return response()->json(['error' => true, 'resp' => $validator->errors()->first()]);
        }
    }

    public function distributorcartqtyUpdate(Request $request)
    {
        $cart = CartDistributor::findOrFail($request->cartId);
        
        if ($cart) {
			 $cart->qty = $request->qty;
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

    public function showBydistributor(Request $request)
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
        $query = CartDistributor::where('distributor_id', $request->distributorId)
            ->where('user_id', $request->userId)->whereHas('product')->with([
                'size:id,name,size_details',
                'color:id,name',
                'product' => function ($q) {
                    $q->select('id', 'name', 'style_no','brand')
                        ->where('status', 1)
                        ->where('is_deleted', 0);
                }
               ]);
            //->with(['product:id,name,style_no,brand', 'color:id,name', 'size:id,name,size_details']);

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


    public function distributorcartDelete(Request $request,$id)
    {
        $cart=CartDistributor::destroy($id);
        if ($cart) {
            return response()->json(['error'=>false, 'resp'=>'Product removed from cart']);
        } else {
            return response()->json(['error' => true, 'resp' => 'Something happened']);
        }
    }

    public function distributorcartPreviewPDF_URL(Request $request)
    {
        $brandMap = [
            'ONN' => 1,
            'PYNK' => 2,
            'Both' => 3,
        ];

        $brandName = $request->brand; // e.g. ONN, PYNK, Both
        $brandId = $brandMap[$brandName] ?? null;
        return response()->json([
            'error' => false,
            'resp' => 'URL generated',
            'data' => url('/').'/api/distributor/cart/pdf/view/?distributorId='.$request->distributorId.'&userId='.$request->userId.'&brand='.$brandId,
        ]);
    }

    

    public function distributorcartPreviewPDF_view(Request $request)
    {
        // Map brand name to code
        $brandMap = [
            'ONN' => 1,
            'PYNK' => 2,
            'Both' => 3,
        ];

        $brandCode = $brandMap[$request->brand] ?? null;

        // Base query
        $query = CartDistributor::where('distributor_id', $request->distributorId)
            ->where('user_id', $request->userId)->whereHas('product')
            ->with(['product', 'distributors', 'color', 'size']);

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

        $cartData = $query->get();

        return view('api.distributor-cart-pdf', compact('cartData'));
    }


    public function distributorplaceOrderUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'distributor_id' => ['required'],
            'user_id' => ['required'],
            'brand' => ['required'],
            'order_type' => ['required', 'string', 'min:1'],
            'order_lat' => ['required', 'string', 'min:1'],
            'order_lng' => ['required', 'string', 'min:1'],
            'comment' => ['nullable', 'string', 'min:1'],
           
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
            $cart_count = CartDistributor::where('distributor_id', $collectedData['distributor_id'])
            ->where('user_id',$collectedData['user_id'])
            ->where('brand',$brandValue)->whereHas('product')
            ->with(['product' => function ($query) {
                $query->where('status', 1)
                    ->where('is_deleted', 0);
            }])->get();
            //dd($cart_count);
            if ($cart_count->isNotEmpty()) {

                $firstCart = $cart_count->first();

                if ($firstCart->brand == 1) {
                    [$order_no, $sequence_no] = generateprimaryONNOrderNumber('primary', $collectedData['distributor_id']);
                } else {
                    [$order_no, $sequence_no] = generateprimaryPYNKOrderNumber('primary', $collectedData['distributor_id']);
                }
                            // 1 order
                $newEntry = new OrderDistributor;
                $newEntry->sequence_no = $sequence_no;
                $newEntry->order_no = $order_no;
                $newEntry->distributor_id = $collectedData['distributor_id'];
                $newEntry->brand = $brandValue;
                $newEntry->user_id = $collectedData['user_id'];
                $newEntry->order_placed_by = $collectedData['order_placed_by'];
                //$newEntry->distributor_id = $collectedData['distributor_id'] ?? '';
                $aseDetails=DB::select("select * from employees where id='".$collectedData['user_id']."'");
                $aseName=$aseDetails[0]->name;
                $user=$newEntry->distributor_id;
    			$result = DB::select("select * from distributors where id='".$user."'");
                $item=$result[0];
                $name = $item->name;
                $newEntry->order_type = $collectedData['order_type'] ?? null;
                $newEntry->order_lat = $collectedData['order_lat'] ?? null;
                $newEntry->order_lng = $collectedData['order_lng'] ?? null;
    
    			$newEntry->email = $item->email;
    			$newEntry->mobile = $item->contact;
                // fetch cart details
                
                $subtotal = $totalOrderQty = 0;
                foreach ($cart_count as $cartValue) {
                    if ($cartValue->product) {
                        $totalOrderQty += $cartValue->qty;
                        $subtotal += $cartValue->product->offer_price * $cartValue->qty;
                        $store_id = $cartValue->store_id;
                        $order_type = $cartValue->order_type;
                    } else {
                        return response()->json(['error' => true, 'resp' => 'Product not exist or inactive/deleted']);
                    }
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
                $orderProductsNewEntry = OrderProductDistributor::insert($orderProducts);
                  CartDistributor::where('distributor_id', $newEntry->distributor_id)->where('user_id',$newEntry->user_id)->where('brand',$brandValue)->delete();
    
    			// notification: sender, receiver, type, route, title
                // notification to ASE
                sendNotification($collectedData['user_id'], $brandValue, 'admin', 'primary-order-place', 'front.user.order', $totalOrderQty.' New order placed',$totalOrderQty.' new order placed  '.$name);
    
    
    			// notification to ASM
    			$loggedInUser = $aseName;
    				$asm = DB::select("SELECT u.id as asm_id FROM `teams` t  INNER JOIN employees u ON u.id = t.asm_id where t.ase_id = '".$collectedData['user_id']."' GROUP BY t.asm_id");
    			foreach($asm as $value){
    				sendNotification($collectedData['user_id'],$brandValue,  $value->asm_id, 'primary-order-place', 'front.user.order', $totalOrderQty.' new order placed by ' .$loggedInUser ,$totalOrderQty.' new order placed from  '.$name);
    			}
    
               
    			// notification to RSM
    			$loggedInUser = $aseName;
    			$rsm = DB::select("SELECT u.id as rsm_id FROM `teams` t  INNER JOIN employees u ON u.id = t.rsm_id where t.ase_id = '".$collectedData['user_id']."' GROUP BY t.rsm_id");
    			foreach($rsm as $value){
    				sendNotification($collectedData['user_id'], $brandValue, $value->rsm_id, 'primary-order-place', 'front.user.order', $totalOrderQty.' new order placed by ' .$loggedInUser ,$totalOrderQty.' new order placed from  '.$name);
    			}
    			
    			// notification to vp
    			$loggedInUser = $aseName;
    			$zsm = DB::select("SELECT u.id as vp_id FROM `teams` t  INNER JOIN employees u ON u.id = t.vp_id where t.ase_id = '".$collectedData['user_id']."' GROUP BY t.vp_id");
    			foreach($zsm as $value){
    				sendNotification($collectedData['user_id'], $brandValue, $value->vp_id, 'primary-order-place', 'front.user.order', $totalOrderQty.' new order placed by ' .$loggedInUser ,$totalOrderQty.' new order placed from  '.$name);
    			}
    
    
                return response()->json(['error'=>false, 'resp'=>'Order placed successfully','data'=>$newEntry]);
            }else{
                return response()->json(['error'=>true, 'resp'=>'cart empty']);
            }
        } else {
            return response()->json(['status' => 400, 'resp' => $validator->errors()->first()]);
        }
    }

    public function distributororderPDF_URL(Request $request, $id)
    {
        return response()->json([
            'error' => false,
            'resp' => 'URL generated',
            'data' => url('/').'/api/distributor/order/pdf/view/'.$id,
        ]);
    }

    

    public function distributororderPDF_view(Request $request, $id)
    {
        $orderData =OrderProductDistributor::where('order_id',$id)->whereHas('product')->with('product','color','size','orders')->get();
		
        return view('api.distributor-order-pdf', compact('orderData','id'));
    }


    public function storeReportASE(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ase_id' => ['required'],
            'from' => ['required'],
            'to' => ['required'],
            'collection' => ['nullable'],
            'category' => ['nullable'],
            'orderBy' => ['nullable'],
            'style_no' => ['nullable'],
            'brand' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }

        $user = Employee::findOrFail($request->ase_id);
        $userName = $user->name;

        $from = $request->filled('from') ? date('Y-m-d', strtotime($request->from)) : date('Y-m-01');
        $to   = $request->filled('to') ? date('Y-m-d', strtotime($request->to)) : date('Y-m-d');

        // 🔹 Filter values
        $collectionQuery = ($request->collection == '10000' || empty($request->collection)) ? null : $request->collection;
        $categoryQuery   = ($request->category == '10000' || empty($request->category)) ? null : $request->category;
        $styleNoQuery    = $request->style_no;

        // 🔹 Handle orderBy
        $orderByQuery = match ($request->orderBy) {
            'date_asc' => 'id ASC',
            'qty_asc'  => 'qty ASC',
            'qty_desc' => 'qty DESC',
            default    => 'id DESC',
        };
        
        $brandMap = [
            'ONN'  => 1,
            'PYNK' => 2,
            'Both' => 3,
        ];
        $brandCode = $request->brand;
        $brandName = $brandMap[$brandCode] ?? null;
        //primary
            $distributors = Team::where('ase_id', $request->ase_id)
            ->where('brand', $brandName)
            ->whereNull('store_id')
            ->where('status', 1)
            ->where('is_deleted', 0)
            ->whereHas('distributor', function ($q) use ($brandName) {
                $q->where('brand', $brandName)
                ->where('status', 1)
                ->where('is_deleted', 0);
            })
            ->with('distributor:id,name')
            ->distinct('distributor_id') // ✅ ensures each distributor_id only once
            ->get(['distributor_id']);
             $respArrd = [];

            foreach ($distributors as $item) {

                // 🔹 If style_no is provided, get product IDs first
                $productIds = [];
                if (!empty($styleNoQuery)) {
                    $productIds = Product::where('style_no', 'LIKE', "%{$styleNoQuery}%")
                        ->where('status', 1)
                        ->where('is_deleted', 0)
                        ->pluck('id')
                        ->toArray();
                }
                // 🔹 Build base query
                $query = PrimaryOrder::where('distributor_id', $item->distributor_id)
                    ->where('brand', $brandName)
                    ->whereBetween('order_date', [$from, $to]);
                
                // 🔹 Handle filters with comma-separated columns
                if (!empty($collectionQuery)) {
                    $query->whereRaw("FIND_IN_SET(?, collection_id)", [$collectionQuery]);
                }

                if (!empty($categoryQuery)) {
                    $query->whereRaw("FIND_IN_SET(?, cat_id)", [$categoryQuery]);
                }

                if (!empty($productIds)) {
                    $query->where(function ($q) use ($productIds) {
                        foreach ($productIds as $pid) {
                            $q->orWhereRaw("FIND_IN_SET(?, product_id)", [$pid]);
                        }
                    });
                }

                $qty = $query->sum('qty');
                // 🚫 Skip if no quantity (no orders)
                if ($qty <= 0) continue;
                $respArrd[] = [
                    'distributor_id' => $item->distributor_id,
                    'distributor_name'  => $item->distributor->name ?? '',
                    'brand'       => $request->brand,
                    'amount'      => 0,
                    'qty'         => $qty ?? 0,
                ];
                
            }
        //secondary
        $stores = Store::where('user_id', $request->ase_id)
            ->where('brand', $brandName)
            ->where('status', 1)
            ->where('is_deleted', 0)
            ->orderBy('name')
            ->get();

        $respArr = [];

        foreach ($stores as $store) {
           // $brandCode = $request->brand;
           // $brandName = $brandMap[$brandCode] ?? null;
           // $brandsToCheck = ($brandCode == 3) ? [1, 2] : [$brandCode];

            // 🔹 If style_no is provided, get product IDs first
            $productIds = [];
            if (!empty($styleNoQuery)) {
                $productIds = Product::where('style_no', 'LIKE', "%{$styleNoQuery}%")
                    ->where('status', 1)
                    ->where('is_deleted', 0)
                    ->pluck('id')
                    ->toArray();
            }

            // 🔹 Build base query
            $query = SecondaryOrder::where('retailer_id', $store->id)
                ->where('brand', $brandName)
                ->whereBetween('order_date', [$from, $to]);
            
            // 🔹 Handle filters with comma-separated columns
            if (!empty($collectionQuery)) {
                $query->whereRaw("FIND_IN_SET(?, collection_id)", [$collectionQuery]);
            }

            if (!empty($categoryQuery)) {
                $query->whereRaw("FIND_IN_SET(?, cat_id)", [$categoryQuery]);
            }

            if (!empty($productIds)) {
                $query->where(function ($q) use ($productIds) {
                    foreach ($productIds as $pid) {
                        $q->orWhereRaw("FIND_IN_SET(?, product_id)", [$pid]);
                    }
                });
            }

            $qty = $query->sum('qty');
            
            $respArr[] = [
                'retailer_id' => $store->id,
                'store_name'  => $store->name,
                'brand'       => $request->brand,
                'amount'      => 0,
                'qty'         => $qty ?? 0,
            ];
        }

        return response()->json([
            'status' => true,
            'message' => 'Store wise Secondary Sales Report',
            'PrimarySales' => $respArrd,
            'SecondarySales' => $respArr,
        ], 200);
    }

    public function productReportASE(Request $request)
    {
        \DB::connection()->enableQueryLog();

        $validator = Validator::make($request->all(), [
            'ase_id' => ['required'],
            'from' => ['required'],
            'to' => ['required'],
            'collection' => ['nullable'],
            'category' => ['nullable'],
            'orderBy' => ['nullable'],
            'style_no' => ['nullable'],
            'brand' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => true, 'resp' => $validator->errors()->first()]);
        }

        $from = date('Y-m-d', strtotime($request->from));
        $to   = date('Y-m-d', strtotime($request->to));

        // 🔹 Filter values
        $collectionQuery = ($request->collection == '10000' || empty($request->collection)) ? null : $request->collection;
        $categoryQuery   = ($request->category == '10000' || empty($request->category)) ? null : $request->category;
        $styleNoQuery    = $request->style_no;

        // 🔹 Handle orderBy
        $orderByQuery = match ($request->orderBy) {
            'date_asc' => 'id ASC',
            'qty_asc'  => 'qty ASC',
            'qty_desc' => 'qty DESC',
            default    => 'id DESC',
        };
        // 🔹 Brand mapping
        $brandMap = [
            'ONN'  => 1,
            'PYNK' => 2,
            'Both' => 3,
        ];

        $brandText = $request->brand;
        $brandCode = $brandMap[$brandText] ?? null;
       
        if (!$brandCode) {
            return response()->json(['error' => true, 'resp' => 'Invalid brand value']);
        }

        // 🔹 Handle "Both"
        $brandsToCheck = ($brandCode == 3) ? [1, 2] : [$brandCode];

        // 🔹 Fetch ASE stores
        $stores = Store::where('user_id', $request->ase_id)
            ->where('brand', $brandCode)
            ->where('status', 1)
            ->where('is_deleted', 0)
            ->get();

        $finalData = [];

        foreach ($stores as $store) {

            $productIds = [];
            if (!empty($styleNoQuery)) {
                $productIds = Product::where('style_no', 'LIKE', "%{$styleNoQuery}%")
                    ->where('status', 1)
                    ->where('is_deleted', 0)
                    ->pluck('id')
                    ->toArray();
            }
            // 🔹 Get all secondary orders for this store
            $query = SecondaryOrder::where('retailer_id', $store->id)
                ->where('brand', $brandCode)
                ->whereBetween('order_date', [$from, $to]);
                
             // 🔹 Handle filters with comma-separated columns
            if (!empty($collectionQuery)) {
                $query->whereRaw("FIND_IN_SET(?, collection_id)", [$collectionQuery]);
            }

            if (!empty($categoryQuery)) {
                $query->whereRaw("FIND_IN_SET(?, cat_id)", [$categoryQuery]);
            }
            if (!empty($productIds)) {
                $query->where(function ($q) use ($productIds) {
                    foreach ($productIds as $pid) {
                        $q->orWhereRaw("FIND_IN_SET(?, product_id)", [$pid]);
                    }
                });
            }
            $orders=$query->get();
            $productQtyMap = [];

            // 🔹 Loop orders to gather product quantities
            foreach ($orders as $order) {
                $productIds = explode(',', $order->product_id);
                $qtys = explode(',', $order->qty);

                foreach ($productIds as $index => $pid) {
                    $pid = trim($pid);
                    $q = isset($qtys[$index]) ? (int)$qtys[$index] : 0;

                    if ($pid && $q > 0) {
                        if (!isset($productQtyMap[$pid])) {
                            $productQtyMap[$pid] = 0;
                        }
                        $productQtyMap[$pid] += $q;
                    }
                }
            }

            // 🔹 Fetch product details
            if (!empty($productQtyMap)) {
                $productDetails = Product::whereIn('id', array_keys($productQtyMap))
                    ->where('status', 1)
                    ->where('is_deleted', 0)
                    ->get(['id', 'name', 'style_no']);

                foreach ($productDetails as $prod) {
                    $finalData[] = [
                        'store_id'   => $store->id,
                        'store_name' => $store->name,
                        'brand'      => $brandText,
                        'product'    => $prod->name,
                        'style_no'   => $prod->style_no,
                        'qty'        => $productQtyMap[$prod->id] ?? 0,
                    ];
                }
            }
        }

        return response()->json([
            'error' => false,
            'resp'  => 'ASE Product-wise report fetched successfully',
            'data'  => array_values($finalData),
        ]);
    }

//activity log
    public function activityList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "user_id" => "required|integer",
            "date" => "required|date",
            "brand" => "required",
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => true, 'resp' => $validator->errors()->first()]);
        }
    
        $user_id = $request->input('user_id');
        $date = date('Y-m-d', strtotime($request->date));
        // 🔹 Brand mapping
        $brandMap = [
            'ONN'  => 1,
            'PYNK' => 2,
            'Both' => 3,
        ];

        $brandText = $request->brand;
        $brandCode = $brandMap[$brandText] ?? null;
        if (!$brandCode) {
            return response()->json(['error' => true, 'resp' => 'Invalid brand value']);
        }

        // 🔹 Handle "Both"
        $brandsToCheck = ($brandCode == 3) ? [1, 2] : [$brandCode];
        // Fetch activities directly without unnecessary object casting
        $activities = Activity::where('user_id', $user_id)
            ->whereDate('date', $date)
            ->where(function ($q) use ($brandCode) {
                $q->where('brand', $brandCode)
                ->orWhereNull('brand')
                ->orWhere('brand', '');
            })
            ->latest('id')
            ->get();
    
        if ($activities->isEmpty()) {
            return response()->json(['error' => true, 'resp' => 'No data found']);
        }
    
        return response()->json(['error' => false, 'resp' => 'Activity List', 'data' => $activities]);
    }

    public function onncurrencyASE(Request $request)
    {
        $keyword = $request->get('keyword', '');
        $distributor_id = $request->get('distributor_id', '');
        $ase_id = $request->get('ase_id', '');
        $brand = $request->get('brand', '');

        $brandMap = [
            'ONN'  => 1,
            'PYNK' => 2,
            'Both' => 3,
        ];

        $brandCode = $brandMap[$brand] ?? null;

        if (!$brandCode) {
            return response()->json(['error' => true, 'resp' => 'Invalid brand value']);
        }

        // Base query
        $query = DB::table('stores')
            ->select('stores.id', 'stores.name', 'stores.wallet', 'stores.user_id')
            ->leftJoin('teams', 'teams.store_id', '=', 'stores.id')
            ->whereRaw('FIND_IN_SET(?, stores.user_id)', [$ase_id])
            ->where('stores.brand', $brandCode)
            ->where('stores.status', 1)
            ->where('stores.is_deleted', 0);

        // Optional filters
        if (!empty($distributor_id)) {
            $query->where('teams.distributor_id', $distributor_id);
        }

        if (!empty($keyword)) {
            $query->where(function ($subQuery) use ($keyword) {
                $subQuery->where('stores.name', 'like', '%' . $keyword . '%')
                        ->orWhere('stores.contact', '=', $keyword);
            });
        }

        $stores = $query->latest('stores.id')->get();

        if ($stores->isNotEmpty()) {
            return response()->json([
                'error' => false,
                'resp' => 'Stores fetched successfully',
                'data' => $stores
            ]);
        }

        return response()->json(['error' => true, 'message' => 'No data found']);
    }


  public function rewardorderaseDetail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ase_id'     => ['required'],
            'brand'      => ['nullable'],
            'date_from'  => ['nullable', 'date'],
            'date_to'    => ['nullable', 'date'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
            ]);
        }

        $aseId = $request->ase_id;
        $dateFrom =   $request->date_from;
        $dateTo   =   $request->date_to;
        $brandText = $request->brand;

        // 🔹 Brand mapping
        $brandMap = [
            'ONN'  => 1,
            'PYNK' => 2,
            'Both' => 3,
        ];

        $brandCode = $brandMap[$brandText] ?? null;

        if (!$brandCode && $brandText !== null) {
            return response()->json(['error' => true, 'resp' => 'Invalid brand value']);
        }

        // 🔹 MAIN QUERY
        $query = RetailerOrder::select('retailer_orders.*')->with([
                'user' => function ($q) {
                    $q->where('status', 1)->where('is_deleted', 0);
                },
                'orderProduct' => function ($q) {
                    $q->whereHas('product', function ($p) {
                            $p->where('status', 1)->where('is_deleted', 0);
                        })
                        ->with([
                            'product' => function ($p) {
                                $p->where('status', 1)->where('is_deleted', 0);
                            }
                        ]);
                }
            ])
            // JOIN STORE
            ->join('stores', 'stores.id', '=', 'retailer_orders.user_id')

            // ASE FILTER
            ->whereRaw('FIND_IN_SET(?, stores.user_id)', [$aseId])

            // Store must be active + not deleted
            ->where('stores.status', 1)
            ->where('stores.is_deleted', 0);

        // 🔹 Brand filter
        if (!empty($brandText) && $brandText !== "Both") {
            $query->where('retailer_orders.brand', $brandCode);
        }

        // 🔹 Date filters
        if (!empty($dateFrom)) {
            $query->whereDate('retailer_orders.created_at', '>=', $dateFrom);
        }

        if (!empty($dateTo)) {
            $query->whereDate('retailer_orders.created_at', '<=', $dateTo);
        }

        // 🔹 Execute
        $orders = $query->orderByDesc('retailer_orders.id')
            ->get();
        
        // 🔹 Filter orders that have:
        //    - Active store
        //    - At least one active product
        $filtered = $orders->filter(function ($order) {
            return $order->user &&
                $order->user->status == 1 &&
                $order->user->is_deleted == 0 &&
                $order->orderProduct->isNotEmpty();
        })->values();

        return response()->json([
            'error' => false,
            'message' => 'Product orders with quantity and brand filter',
            'data' => $filtered,
        ]);
    }


    //ASM
    //notification list
    public function notificationList(Request $request){
		$validator = Validator::make($request->all(), [
			'user_id' => ['required'],
			'pageNo' => ['nullable'],
            'brand'  => ['required'],
		]);
		
		if (!$validator->fails()) {
            // 🔹 Brand mapping
            $brandMap = [
                'ONN'  => 1,
                'PYNK' => 2,
                'Both' => 3,
            ];

            $brandText = $request->brand;
            $brandCode = $brandMap[$brandText] ?? null;
            if (!$brandCode) {
                return response()->json(['error' => true, 'resp' => 'Invalid brand value']);
            }
			$user_id = $request->user_id;
          	$pageNo =$request->pageNo;
			if(!$pageNo){
               $page=1;
             }else{
              $page=$pageNo;
			  }
              $limit=20;
              $offset=($page-1)*$limit;
			  $notifications = DB::select("select * from notifications where receiver_id='$user_id' AND brand='$brandCode' ORDER BY id desc LIMIT ".$limit." OFFSET ".$offset."");
			  $notificationCount=DB::table('notifications')->where('receiver_id','=',$user_id)->where('brand','=',$brandCode)->count();
			  $count= (int) ceil($notificationCount / $limit);
				return response()->json(['error' => false, 'message' => 'User wise notification list', 'data' => $notifications,'count'=>$count]);
			
			
		}else{
			return response()->json(['error' => true, 'resp' => $validator->errors()->first()]);
		}
	}
	//notification update
	public function readNotification(Request $request){
		$id = $request->id;
		$read_time = date("Y-m-d G:i:s");
		
		DB::select("update notifications set read_flag=1, read_at='$read_time' where id='$id'");
		
		return response()->json(['error' => false, 'message' => 'Notification date updated successfully']);
	}


    
    //asm wise ase

    public function aseList(Request $request,$id)
    {
        $brandMap = [
            1 => 'ONN',
            2 => 'PYNK',
            3 => 'Both',
        ];

        $data=Team::where('asm_id',$id)->where('status',1)->where('is_deleted',0)->groupby('ase_id')->with('ase:id,name')->get();
        if ($data->isNotEmpty()) {
            // Add brand name to response
            $data = $data->map(function ($store) use ($brandMap) {
                $store->brand_name = $brandMap[$store->brand] ?? null;
                return $store;
            });

            return response()->json([
                'status'  => true,
                'message' => 'ase list data fetched successfully',
                'data'    => $data,
            ], 200);
        } else {
            return response()->json([
                'status'  => false,
                'message' => 'No list data found',
            ], 404);
        }
        
    }


    public function inactiveAseListASM(Request $request)
{
    $userId = $request->user_id;

    $brandMap = [
        'ONN'  => 1,
        'PYNK' => 2,
        'Both' => 3,
    ];

    $brandText = $request->brand;
    $brandCode = $brandMap[$brandText] ?? null;
    
    if (!$brandCode) {
        return response()->json(['error' => true, 'resp' => 'Invalid brand value']);
    }

    // Handle "Both"
    $brandsToCheck = ($brandCode == 3) ? [1, 2] : [$brandCode];

    // ✅ Get ASE IDs under this ASM
    $aseDetails = Team::join('employees', 'teams.ase_id', '=', 'employees.id')
        ->where('teams.asm_id', $userId)
        ->where('teams.brand', $brandCode)
        ->where('teams.status', 1)
        ->where('teams.is_deleted', 0)
        ->pluck('teams.ase_id')
        ->unique()
        ->toArray();

    // ✅ Get ASEs who are active today (did “Visit Started”)
    $today = now()->format('Y-m-d');

    $activeASEreport = Activity::where('type', 'Visit Started')
        ->whereDate('date', $today)
        ->whereIn('user_id', $aseDetails)
        ->pluck('user_id')
        ->unique()
        ->toArray();

    // ✅ Get ASEs who are NOT in the active list
    $inactiveASE = Employee::select('employees.*')
            ->join('teams', 'teams.ase_id', '=', 'employees.id')
            ->where('teams.asm_id', $userId)
            ->where('teams.brand', $brandCode)
            ->whereNotIn('employees.id', $activeASEreport)
            ->where('teams.status', 1)
            ->where('teams.is_deleted', 0)

            ->where('employees.is_deleted', 0)
            ->groupBy('employees.id')
            ->orderBy('employees.name')
            ->with(['stateDetail', 'area']) // works correctly now
            ->get();

    return response()->json([
        'error' => false,
        'resp' => 'Inactive ASE report - Team wise',
        'data' => $inactiveASE,
    ]);
}


   public function asestoreList(Request $request)
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


    //area list
    public function asmareaList(Request $request,$id)
    {
        $data=Team::where('asm_id',$id)->groupby('area_id')->with('areas:id,name')->get();
        if ($data->isNotEmpty()) {
            return response()->json(['error'=>false, 'resp'=>'Area List','data'=>$data]);
                 
        } else {
            return response()->json(['error'=>true, 'resp'=>'No data found']);   
        } 
    }
    
    //distributor list
    public function asmdistributorList(Request $request)
    {
        $asm = $_GET['user_id'];
        $area = $_GET['area_id'];
        $data= Team::select('distributor_id','area_id')->where('asm_id',$asm)->where('store_id',NULL)->with('distributor')->distinct('distributor_id')->get();
        if ($data->isNotEmpty()) 
        {
            return response()->json(['error' => false, 'resp' => 'Distributor data fetched successfully','data' => $data]);
        } else {
            return response()->json(['error' => true, 'resp' => 'Something happened']);
        }
    }

    
    public function storeReportASM(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'asm_id' => ['required'],
            'date_from' => ['nullable'],
            'date_to' => ['nullable'],
            'collection' => ['nullable'],
            'category' => ['nullable'],
            'orderBy' => ['nullable'],
            'style_no' => ['nullable'],
            'brand' => ['required'],
        ]);
        
        if ($validator->fails()) {
            return response()->json(['error' => true, 'message' => $validator->errors()->first()]);
        }
        
        $user = Employee::findOrFail($request->asm_id);
        $aseIds = Team::where('asm_id', $request->asm_id)->where('status',1)->where('is_deleted',0)
                   ->whereNull('store_id')
                  ->whereNotNull('ase_id')
                  ->pluck('ase_id')->unique()->toArray();
        
        // Fetch ASE Data in One Query with Eager Loading (If applicable)
        $ases = Employee::whereIn('id', $aseIds)->get();

        $from = $request->filled('from') ? date('Y-m-d', strtotime($request->from)) : date('Y-m-01');
        $to   = $request->filled('to') ? date('Y-m-d', strtotime($request->to)) : date('Y-m-d');

        // 🔹 Filter values
        $collectionQuery = ($request->collection == '10000' || empty($request->collection)) ? null : $request->collection;
        $categoryQuery   = ($request->category == '10000' || empty($request->category)) ? null : $request->category;
        $styleNoQuery    = $request->style_no;

        // 🔹 Handle orderBy
        $orderByQuery = match ($request->orderBy) {
            'date_asc' => 'id ASC',
            'qty_asc'  => 'qty ASC',
            'qty_desc' => 'qty DESC',
            default    => 'id DESC',
        };
        
        $brandMap = [
            'ONN'  => 1,
            'PYNK' => 2,
            'Both' => 3,
        ];
        $brandCode = $request->brand;
        $brandName = $brandMap[$brandCode] ?? null;

        //primary
            $distributors = Team::where('asm_id', $request->asm_id)
            ->where('brand', $brandName)
            ->whereNull('store_id')
            ->where('status', 1)
            ->where('is_deleted', 0)
            ->whereHas('distributor', function ($q) use ($brandName) {
                $q->where('brand', $brandName)
                ->where('status', 1)
                ->where('is_deleted', 0);
            })
           
            ->with('distributor:id,name')
            ->distinct('distributor_id') // ✅ ensures each distributor_id only once
            ->get(['distributor_id']);
             $respArrd = [];

            foreach ($distributors as $item) {

                // 🔹 If style_no is provided, get product IDs first
                $productIds = [];
                if (!empty($styleNoQuery)) {
                    $productIds = Product::where('style_no', 'LIKE', "%{$styleNoQuery}%")
                        ->where('status', 1)
                        ->where('is_deleted', 0)
                        ->pluck('id')
                        ->toArray();
                }
                // 🔹 Build base query
                $query = PrimaryOrder::where('distributor_id', $item->distributor_id)
                    ->where('brand', $brandName)
                    ->whereBetween('order_date', [$from, $to]);
                
                // 🔹 Handle filters with comma-separated columns
                if (!empty($collectionQuery)) {
                    $query->whereRaw("FIND_IN_SET(?, collection_id)", [$collectionQuery]);
                }

                if (!empty($categoryQuery)) {
                    $query->whereRaw("FIND_IN_SET(?, cat_id)", [$categoryQuery]);
                }

                if (!empty($productIds)) {
                    $query->where(function ($q) use ($productIds) {
                        foreach ($productIds as $pid) {
                            $q->orWhereRaw("FIND_IN_SET(?, product_id)", [$pid]);
                        }
                    });
                }

                $qty = $query->sum('qty');
                // 🚫 Skip if no quantity (no orders)
                if ($qty <= 0) continue;
                $respArrd[] = [
                    'distributor_id' => $item->distributor_id,
                    'distributor_name'  => $item->distributor->name ?? '',
                    'brand'       => $request->brand,
                    'amount'      => 0,
                    'qty'         => $qty ?? 0,
                ];
                
            }
        // Fetch ASE Sales in Bulk (Optimize Query)
        $aseSales = $this->fetchASESales($aseIds, $brandName,$collectionQuery,$categoryQuery,$styleNoQuery,$from, $to);

        // Map ASE data to response format
        $aseResp = $ases->map(function ($ase) use ($aseSales) {
            return [
                'ase_id' => $ase->id,
                'ase_name' => $ase->name,
                'quantity' => $aseSales[$ase->id] ?? 0,
            ];
        });
           
                $resp[] = [
                    'primary_sales' => $respArrd,
                    'secondary_sales' => $aseResp,
                ];
        return response()->json([
            'error' => false,
            'message' => 'ASM report - Team wise',
            'PrimarySales' => $respArrd,
            'SecondarySales' => $aseResp
            
            
        ]);
    }

    private function fetchASESales($aseIds, $brandName,$collectionQuery,$categoryQuery,$styleNoQuery, $from, $to)
    {
        if (empty($aseIds)) {
            return [];
        }

       

        $respArr = [];

       

            // 🔹 If style_no is provided, get product IDs first
            $productIds = [];
            if (!empty($styleNoQuery)) {
                $productIds = Product::where('style_no', 'LIKE', "%{$styleNoQuery}%")
                    ->where('status', 1)
                    ->where('is_deleted', 0)
                    ->pluck('id')
                    ->toArray();
            }

            // 🔹 Build base query
            $query = SecondaryAseOrder::select('ase_id', DB::raw('SUM(qty) as total_qty'))->whereIN('ase_id', $aseIds)
                ->where('brand', $brandName)
                ->whereBetween('order_date', [$from, $to]);
            
            // 🔹 Handle filters with comma-separated columns
            if (!empty($collectionQuery)) {
                $query->whereRaw("FIND_IN_SET(?, collection_id)", [$collectionQuery]);
            }

            if (!empty($categoryQuery)) {
                $query->whereRaw("FIND_IN_SET(?, cat_id)", [$categoryQuery]);
            }

            if (!empty($productIds)) {
                $query->where(function ($q) use ($productIds) {
                    foreach ($productIds as $pid) {
                        $q->orWhereRaw("FIND_IN_SET(?, product_id)", [$pid]);
                    }
                });
            }

            return $query->pluck('total_qty', 'ase_id')->toArray();
        
        
    }
    public function productReportASM(Request $request)
    {
        \DB::connection()->enableQueryLog();

        $validator = Validator::make($request->all(), [
            'asm_id' => ['required'],
            'from' => ['required'],
            'to' => ['required'],
            'collection' => ['nullable'],
            'category' => ['nullable'],
            'orderBy' => ['nullable'],
            'style_no' => ['nullable'],
            'brand' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => true, 'resp' => $validator->errors()->first()]);
        }
        $user = Employee::findOrFail($request->asm_id);
        $aseIds = Team::where('asm_id', $request->asm_id)->where('status',1)->where('is_deleted',0)
                   ->whereNull('store_id')
                  ->whereNotNull('ase_id')
                  ->pluck('ase_id');
        
        // Fetch ASE Data in One Query with Eager Loading (If applicable)
        $ases = Employee::whereIn('id', $aseIds)->get();

        $from = date('Y-m-d', strtotime($request->from));
        $to   = date('Y-m-d', strtotime($request->to));
         // 🔹 Filter values
        $collectionQuery = ($request->collection == '10000' || empty($request->collection)) ? null : $request->collection;
        $categoryQuery   = ($request->category == '10000' || empty($request->category)) ? null : $request->category;
        $styleNoQuery    = $request->style_no;

        // 🔹 Handle orderBy
        $orderByQuery = match ($request->orderBy) {
            'date_asc' => 'id ASC',
            'qty_asc'  => 'qty ASC',
            'qty_desc' => 'qty DESC',
            default    => 'id DESC',
        };
        // 🔹 Brand mapping
        $brandMap = [
            'ONN'  => 1,
            'PYNK' => 2,
            'Both' => 3,
        ];

        $brandText = $request->brand;
        $brandCode = $brandMap[$brandText] ?? null;
        if (!$brandCode) {
            return response()->json(['error' => true, 'resp' => 'Invalid brand value']);
        }

        // 🔹 Handle "Both"
        $brandsToCheck = ($brandCode == 3) ? [1, 2] : [$brandCode];

        
        $finalData = [];

            $productIds = [];
            if (!empty($styleNoQuery)) {
                $productIds = Product::where('style_no', 'LIKE', "%{$styleNoQuery}%")
                    ->where('status', 1)
                    ->where('is_deleted', 0)
                    ->pluck('id')
                    ->toArray();
            }
            // 🔹 Get all secondary orders for this store
            $query = SecondaryAseOrder::whereIN('ase_id', $aseIds)
                ->where('brand', $brandCode)
                ->whereBetween('order_date', [$from, $to]);
                
             // 🔹 Handle filters with comma-separated columns
            if (!empty($collectionQuery)) {
                $query->whereRaw("FIND_IN_SET(?, collection_id)", [$collectionQuery]);
            }

            if (!empty($categoryQuery)) {
                $query->whereRaw("FIND_IN_SET(?, cat_id)", [$categoryQuery]);
            }
            if (!empty($productIds)) {
                $query->where(function ($q) use ($productIds) {
                    foreach ($productIds as $pid) {
                        $q->orWhereRaw("FIND_IN_SET(?, product_id)", [$pid]);
                    }
                });
            }
            $orders=$query->get();
            // 🔹 Get all secondary orders for this store
           

            $productQtyMap = [];

            // 🔹 Loop orders to gather product quantities
            foreach ($orders as $order) {
                $productIds = explode(',', $order->product_id);
                $qtys = explode(',', $order->qty);

                foreach ($productIds as $index => $pid) {
                    $pid = trim($pid);
                    $q = isset($qtys[$index]) ? (int)$qtys[$index] : 0;

                    if ($pid && $q > 0) {
                        if (!isset($productQtyMap[$pid])) {
                            $productQtyMap[$pid] = 0;
                        }
                        $productQtyMap[$pid] += $q;
                    }
                }
            }

            // 🔹 Fetch product details
            if (!empty($productQtyMap)) {
                $productDetails = Product::whereIn('id', array_keys($productQtyMap))
                    ->where('status', 1)
                    ->where('is_deleted', 0)
                    ->get(['id', 'name', 'style_no']);

                foreach ($productDetails as $prod) {
                    $finalData[] = [
                        
                        'brand'      => $brandText,
                        'product'    => $prod->name,
                        'style_no'   => $prod->style_no,
                        'qty'        => $productQtyMap[$prod->id] ?? 0,
                    ];
                }
            }
        

        return response()->json([
            'error' => false,
            'resp'  => 'ASM Product-wise report fetched successfully',
            'data'  => array_values($finalData),
        ]);
    }



   public function onncurrencyASM(Request $request)
    {
        $keyword = $request->get('keyword', '');
        $distributor_id = $request->get('distributor_id', '');
        $asm_id = $request->get('asm_id', '');
        $brand = $request->get('brand', '');

        $brandMap = [
            'ONN'  => 1,
            'PYNK' => 2,
            'Both' => 3,
        ];

        $brandCode = $brandMap[$brand] ?? null;

        if (!$brandCode) {
            return response()->json(['error' => true, 'resp' => 'Invalid brand value']);
        }

        // Base query
        $query = DB::table('stores')
            ->select('stores.id', 'stores.name', 'stores.wallet')
            ->join('teams', 'teams.store_id', '=', 'stores.id')
            ->whereRaw('FIND_IN_SET(?, teams.asm_id)', [$asm_id])
            ->where('stores.brand', $brandCode)
            ->where('stores.status', 1)
            ->where('stores.is_deleted', 0);

        // Optional filters
        if (!empty($distributor_id)) {
            $query->where('teams.distributor_id', $distributor_id);
        }

        if (!empty($keyword)) {
            $query->where(function ($subQuery) use ($keyword) {
                $subQuery->where('stores.name', 'like', '%' . $keyword . '%')
                        ->orWhere('stores.contact', '=', $keyword);
            });
        }

        // Execute query
        $stores = $query->latest('stores.id')->get();

        // Return response
        if ($stores->isNotEmpty()) {
            return response()->json([
                'error' => false,
                'resp' => 'Stores fetched successfully',
                'data' => $stores
            ]);
        }

        return response()->json(['error' => true, 'message' => 'No data found']);
    }



    public function rewardorderasmDetail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'asm_id'     => ['required'],
            'brand'      => ['nullable'], // brand optional filter
            'date_from'  => ['nullable', 'date'],
            'date_to'    => ['nullable', 'date'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
            ]);
        }

        $asmId = $request->asm_id;
        $brand = $request->brand;
        $perPage = $request->per_page ?? 10;
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        // 🔹 Brand mapping
            $brandMap = [
                'ONN'  => 1,
                'PYNK' => 2,
                'Both' => 3,
            ];

            $brandText = $request->brand;
            $brandCode = $brandMap[$brandText] ?? null;
        
            if (!$brandCode) {
                return response()->json(['error' => true, 'resp' => 'Invalid brand value']);
            }
        // Build query dynamically
        $query = RetailerOrder::select('retailer_orders.*')->with([
                'user' => function ($q) {
                    $q->where('status', 1)->where('is_deleted', 0);
                },
                'orderProduct' => function ($q) {
                    $q->whereHas('product', function ($p) {
                            $p->where('status', 1)->where('is_deleted', 0);
                        })
                        ->with([
                            'product' => function ($p) {
                                $p->where('status', 1)->where('is_deleted', 0);
                            }
                        ]);
                }
            ])
            
            ->join('stores', 'stores.id', '=', 'retailer_orders.user_id')
            ->join('teams', 'teams.store_id', '=', 'stores.id')
            ->whereRaw("FIND_IN_SET(?, teams.asm_id)", [$asmId])
            ->where('stores.status', 1)                         // ✅ active stores only
            ->where('stores.is_deleted', 0)
            ->where('teams.is_deleted', 0);

        // Optional brand filter
        if (!empty($brand)) {
            $query->where('retailer_orders.brand', $brandCode);
        }

        // Optional date filters
        if (!empty($dateFrom)) {
            $query->whereDate('retailer_orders.created_at', '>=', $dateFrom);
        }
        if (!empty($dateTo)) {
            $query->whereDate('retailer_orders.created_at', '<=', $dateTo);
        }

        $query->orderByDesc('retailer_orders.id');

        $data = $query->paginate($perPage);
        $filtered = $data->filter(function ($order) {
        return $order->user &&
               $order->user->status == 1 &&
               $order->user->is_deleted == 0 &&
               $order->orderProduct->isNotEmpty();
            })->values();
        return response()->json([
            'error' => false,
            'message' => 'Product orders with quantity and brand filter',
            'data' => $filtered,
        ]);
    }


    public function rewardorderasmStatus(Request $request) {
        
        $validator = Validator::make($request->all(), [
            'order_id' => ['required'],
			'asm_approval'=>['required'],
            'asm_note' => ['nullable'],
        ]);

        if (!$validator->fails()) {
            
                $order = RetailerOrder::where('id',$request->order_id)->first();
                
                if(empty($order)){
                    return response()->json(['error' => true, 'message' => 'No order found']);
                }else{
                    $order->asm_approval = $request['asm_approval'];
                    $order->asm_note = $request['asm_note'];
                    $order->save();
                }
			//dd($orders);
            

            return response()->json([
                'error' => false,
                'message' => 'Status updated',
                'data' => $order,
            ]);

        } else {
            return response()->json(['error' => true, 'message' => $validator->errors()->first()]);
        }
    }


    //RSM
    public function rsmareaList(Request $request,$id)
    {
        $data=Team::where('rsm_id',$id)->groupby('area_id')->with('areas:id,name')->get();
        if ($data->isNotEmpty()) {
            return response()->json(['error'=>false, 'resp'=>'Area List','data'=>$data]);
                 
        } else {
            return response()->json(['error'=>true, 'resp'=>'No data found']);   
        } 
    }


    public function rsmdistributorList(Request $request)
    {
        $rsm = $_GET['user_id'];
        
        $data= Team::select('distributor_id','area_id')->where('rsm_id',$rsm)->where('store_id',NULL)->with('distributor')->distinct('distributor_id')->get();
        if ($data->isNotEmpty()) 
        {
            return response()->json(['error' => false, 'resp' => 'Distributor data fetched successfully','data' => $data]);
        } else {
            return response()->json(['error' => true, 'resp' => 'Something happened']);
        }
    }



    public function inactiveAseListRSM(Request $request)
    {
        $userId = $request->user_id;

        $brandMap = [
            'ONN'  => 1,
            'PYNK' => 2,
            'Both' => 3,
        ];

        $brandText = $request->brand;
        $brandCode = $brandMap[$brandText] ?? null;

        if (!$brandCode) {
            return response()->json(['error' => true, 'resp' => 'Invalid brand value']);
        }

        // Handle "Both"
        $brandsToCheck = ($brandCode == 3) ? [1, 2] : [$brandCode];

        // ✅ Get ASE IDs under this ASM
        $aseDetails = Team::join('employees', 'teams.ase_id', '=', 'employees.id')
            ->where('teams.rsm_id', $userId)
            ->where('teams.brand', $brandCode)
            ->where('teams.status', 1)
            ->where('teams.is_deleted', 0)
            ->pluck('teams.ase_id')
            ->unique()
            ->toArray();

        // ✅ Get ASEs who are active today (did “Visit Started”)
        $today = now()->format('Y-m-d');

        $activeASEreport = Activity::where('type', 'Visit Started')
            ->whereDate('date', $today)
            ->whereIn('user_id', $aseDetails)
            ->pluck('user_id')
            ->unique()
            ->toArray();

        // ✅ Get ASEs who are NOT in the active list
        $inactiveASE = Employee::select('employees.*')
            ->join('teams', 'teams.ase_id', '=', 'employees.id')
            ->where('teams.rsm_id', $userId)
            ->where('teams.brand', $brandCode)
            ->whereNotIn('employees.id', $activeASEreport)
            ->where('teams.status', 1)
            ->where('teams.is_deleted', 0)

            ->where('employees.is_deleted', 0)
            ->groupBy('employees.id')
            ->orderBy('employees.name')
            ->with(['stateDetail', 'area']) // works correctly now
            ->get();

        return response()->json([
            'error' => false,
            'resp' => 'Inactive ASE report - Team wise',
            'data' => $inactiveASE,
        ]);
    }



    public function storeReportRSM(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rsm_id' => ['required'],
            'date_from' => ['nullable'],
            'date_to' => ['nullable'],
            'region' => ['nullable'],
            'collection' => ['nullable'],
            'category' => ['nullable'],
            'orderBy' => ['nullable'],
            'style_no' => ['nullable'],
            'brand' => ['required'],
        ]);
        
        if ($validator->fails()) {
            return response()->json(['error' => true, 'message' => $validator->errors()->first()]);
        }

        $from = $request->filled('from') ? date('Y-m-d', strtotime($request->from)) : date('Y-m-01');
        $to   = $request->filled('to') ? date('Y-m-d', strtotime($request->to)) : date('Y-m-d');

        // 🔹 Filter values
        $regionQuery = ($request->region == '10000' || empty($request->region)) ? null : $request->region;
        $collectionQuery = ($request->collection == '10000' || empty($request->collection)) ? null : $request->collection;
        $categoryQuery   = ($request->category == '10000' || empty($request->category)) ? null : $request->category;
        $styleNoQuery    = $request->style_no;

        // 🔹 Handle orderBy
        $orderByQuery = match ($request->orderBy) {
            'date_asc' => 'id ASC',
            'qty_asc'  => 'qty ASC',
            'qty_desc' => 'qty DESC',
            default    => 'id DESC',
        };
        
        $user = Employee::findOrFail($request->rsm_id);
        // 🔹 ASM Query
        $asmQuery = Team::where('rsm_id', $request->rsm_id)
            ->where('status', 1)
            ->where('is_deleted', 0)
            ->whereNull('store_id')
            ->whereNotNull('asm_id');

        // Apply region filter if provided
        if (!empty($regionQuery)) {
            $asmQuery->where('area_id', $regionQuery);
        }

        $asmIds = $asmQuery->pluck('asm_id')->unique()->toArray();

        // 🔹 ASE Query
        $aseQuery = Team::where('rsm_id', $request->rsm_id)
            ->where('status', 1)
            ->where('is_deleted', 0)
            ->whereNull('store_id')
            ->whereNotNull('ase_id');

        // Apply region filter if provided
        if (!empty($regionQuery)) {
            $aseQuery->where('area_id', $regionQuery);
        }

        $aseIds = $aseQuery->pluck('ase_id')->unique()->toArray();

        // Fetch ASE Data in One Query with Eager Loading (If applicable)
        $ases = Employee::whereIn('id', $aseIds)->get();
        $asms = Employee::whereIn('id', $asmIds)->get();
        
        
        $brandMap = [
            'ONN'  => 1,
            'PYNK' => 2,
            'Both' => 3,
        ];
        $brandCode = $request->brand;
        $brandName = $brandMap[$brandCode] ?? null;

        //primary
         $distributorQuery = Team::where('rsm_id', $request->rsm_id)
                ->where('brand', $brandName)
                ->whereNull('store_id')
                ->where('status', 1)
                ->where('is_deleted', 0)
                ->whereHas('distributor', function ($q) use ($brandName) {
                    $q->where('brand', $brandName)
                    ->where('status', 1)
                    ->where('is_deleted', 0);
                })
                ->with('distributor:id,name');

            // 🔹 Apply region filter if provided
            if (!empty($regionQuery)) {
                $distributorQuery->where('area_id', $regionQuery);
            }

            // 🔹 Execute query
            $distributors = $distributorQuery->get();
             $respArrd = [];

            foreach ($distributors as $item) {

                // 🔹 If style_no is provided, get product IDs first
                $productIds = [];
                if (!empty($styleNoQuery)) {
                    $productIds = Product::where('style_no', 'LIKE', "%{$styleNoQuery}%")
                        ->where('status', 1)
                        ->where('is_deleted', 0)
                        ->pluck('id')
                        ->toArray();
                }
                // 🔹 Build base query
                $query = PrimaryOrder::where('distributor_id', $item->distributor_id)
                    ->where('brand', $brandName)
                    ->whereBetween('order_date', [$from, $to]);
                
                // 🔹 Handle filters with comma-separated columns
                if (!empty($collectionQuery)) {
                    $query->whereRaw("FIND_IN_SET(?, collection_id)", [$collectionQuery]);
                }

                if (!empty($categoryQuery)) {
                    $query->whereRaw("FIND_IN_SET(?, cat_id)", [$categoryQuery]);
                }

                if (!empty($productIds)) {
                    $query->where(function ($q) use ($productIds) {
                        foreach ($productIds as $pid) {
                            $q->orWhereRaw("FIND_IN_SET(?, product_id)", [$pid]);
                        }
                    });
                }

                $qty = $query->sum('qty');
                // 🚫 Skip if no quantity (no orders)
                if ($qty <= 0) continue;
                $respArrd[] = [
                    'distributor_id' => $item->distributor_id,
                    'distributor_name'  => $item->distributor->name ?? '',
                    'brand'       => $request->brand,
                    'amount'      => 0,
                    'qty'         => $qty ?? 0,
                ];
                
            }
            $productIds = [];
            if (!empty($styleNoQuery)) {
                $productIds = Product::where('style_no', 'LIKE', "%{$styleNoQuery}%")
                    ->where('status', 1)
                    ->where('is_deleted', 0)
                    ->pluck('id')
                    ->toArray();
            }

            // 🔹 Build base query
            $query = SecondaryASEOrder::select('ase_id', DB::raw('SUM(qty) as total_qty'))
                    ->whereIn('ase_id', $aseIds)
                    ->where('brand', $brandName)
                    ->whereBetween('order_date', [$from, $to])
                    ->groupBy('ase_id');
                    
            
            // 🔹 Handle filters with comma-separated columns
            if (!empty($collectionQuery)) {
                $query->whereRaw("FIND_IN_SET(?, collection_id)", [$collectionQuery]);
            }

            if (!empty($categoryQuery)) {
                $query->whereRaw("FIND_IN_SET(?, cat_id)", [$categoryQuery]);
            }

            if (!empty($productIds)) {
                $query->where(function ($q) use ($productIds) {
                    foreach ($productIds as $pid) {
                        $q->orWhereRaw("FIND_IN_SET(?, product_id)", [$pid]);
                    }
                });
            }

            $aseSales =  $query->pluck('total_qty', 'ase_id')->toArray();
            // 🔹 Build ASM → ASE → qty mapping
                $asmReport = [];
                foreach ($asmIds as $asmId) {
                    $aseUnderAsm = Team::where('asm_id', $asmId)
                        ->where('brand', $brandName)->groupby('ase_id')
                        ->pluck('ase_id')
                        ->toArray();

                    $aseList = [];
                    foreach ($aseUnderAsm as $aseId) {
                        $ase = Employee::find($aseId);
                        $aseList[] = [
                            'ase_id'   => $aseId,
                            'ase_name' => $ase->name ?? 'N/A',
                            'qty'      => $aseSales[$aseId] ?? 0,
                        ];
                    }

                    $asm = Employee::find($asmId);
                    $asmReport[] = [
                        'asm_id'   => $asmId,
                        'asm_name' => $asm->name ?? 'N/A',
                        'total_qty' => array_sum(array_column($aseList, 'qty')),
                    ];
                }
                $resp[] = [
                    'primary_sales' => $respArrd,
                    'secondary_sales' => $aseResp,
                ];
        return response()->json([
            'error' => false,
            'message' => 'RSM report - Team wise',
            'PrimarySales' => $respArrd,
            'SecondarySales' => $asmReport
            
            
        ]);
    }


     public function productReportRSM(Request $request)
    {
        \DB::connection()->enableQueryLog();

        $validator = Validator::make($request->all(), [
            'rsm_id' => ['required'],
            'from' => ['required'],
            'to' => ['required'],
             'region' => ['nullable'],
            'collection' => ['nullable'],
            'category' => ['nullable'],
            'orderBy' => ['nullable'],
            'style_no' => ['nullable'],
            'brand' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => true, 'resp' => $validator->errors()->first()]);
        }


        $from = date('Y-m-d', strtotime($request->from));
        $to   = date('Y-m-d', strtotime($request->to));
         // 🔹 Filter values
        $regionQuery = ($request->region == '10000' || empty($request->region)) ? null : $request->region;
        $collectionQuery = ($request->collection == '10000' || empty($request->collection)) ? null : $request->collection;
        $categoryQuery   = ($request->category == '10000' || empty($request->category)) ? null : $request->category;
        $styleNoQuery    = $request->style_no;

        // 🔹 Handle orderBy
        $orderByQuery = match ($request->orderBy) {
            'date_asc' => 'id ASC',
            'qty_asc'  => 'qty ASC',
            'qty_desc' => 'qty DESC',
            default    => 'id DESC',
        };

        $user = Employee::findOrFail($request->rsm_id);
        // 🔹 ASE Query
        $aseQuery = Team::where('rsm_id', $request->rsm_id)
            ->where('status', 1)
            ->where('is_deleted', 0)
            ->whereNull('store_id')
            ->whereNotNull('ase_id');

        // Apply region filter if provided
        if (!empty($regionQuery)) {
            $aseQuery->where('area_id', $regionQuery);
        }

        $aseIds = $aseQuery->pluck('ase_id')->unique()->toArray();

        
        // Fetch ASE Data in One Query with Eager Loading (If applicable)
        $ases = Employee::whereIn('id', $aseIds)->get();

        
        // 🔹 Brand mapping
        $brandMap = [
            'ONN'  => 1,
            'PYNK' => 2,
            'Both' => 3,
        ];

        $brandText = $request->brand;
        $brandCode = $brandMap[$brandText] ?? null;
        if (!$brandCode) {
            return response()->json(['error' => true, 'resp' => 'Invalid brand value']);
        }

        // 🔹 Handle "Both"
        $brandsToCheck = ($brandCode == 3) ? [1, 2] : [$brandCode];

        
        $finalData = [];

            $productIds = [];
            if (!empty($styleNoQuery)) {
                $productIds = Product::where('style_no', 'LIKE', "%{$styleNoQuery}%")
                    ->where('status', 1)
                    ->where('is_deleted', 0)
                    ->pluck('id')
                    ->toArray();
            }
            // 🔹 Get all secondary orders for this store
            $query = SecondaryAseOrder::whereIN('ase_id', $aseIds)
                ->where('brand', $brandCode)
                ->whereBetween('order_date', [$from, $to]);
                
             // 🔹 Handle filters with comma-separated columns
            if (!empty($collectionQuery)) {
                $query->whereRaw("FIND_IN_SET(?, collection_id)", [$collectionQuery]);
            }

            if (!empty($categoryQuery)) {
                $query->whereRaw("FIND_IN_SET(?, cat_id)", [$categoryQuery]);
            }
            if (!empty($productIds)) {
                $query->where(function ($q) use ($productIds) {
                    foreach ($productIds as $pid) {
                        $q->orWhereRaw("FIND_IN_SET(?, product_id)", [$pid]);
                    }
                });
            }
            $orders=$query->get();
            // 🔹 Get all secondary orders for this store
           

            $productQtyMap = [];

            // 🔹 Loop orders to gather product quantities
            foreach ($orders as $order) {
                $productIds = explode(',', $order->product_id);
                $qtys = explode(',', $order->qty);

                foreach ($productIds as $index => $pid) {
                    $pid = trim($pid);
                    $q = isset($qtys[$index]) ? (int)$qtys[$index] : 0;

                    if ($pid && $q > 0) {
                        if (!isset($productQtyMap[$pid])) {
                            $productQtyMap[$pid] = 0;
                        }
                        $productQtyMap[$pid] += $q;
                    }
                }
            }

            // 🔹 Fetch product details
            if (!empty($productQtyMap)) {
                $productDetails = Product::whereIn('id', array_keys($productQtyMap))
                    ->where('status', 1)
                    ->where('is_deleted', 0)
                    ->get(['id', 'name', 'style_no']);

                foreach ($productDetails as $prod) {
                    $finalData[] = [
                        
                        'brand'      => $brandText,
                        'product'    => $prod->name,
                        'style_no'   => $prod->style_no,
                        'qty'        => $productQtyMap[$prod->id] ?? 0,
                    ];
                }
            }
        

        return response()->json([
            'error' => false,
            'resp'  => 'RSM Product-wise report fetched successfully',
            'data'  => array_values($finalData),
        ]);
    }


      public function onncurrencyRSM(Request $request)
    {
        $keyword = $request->get('keyword', '');
        $distributor_id = $request->get('distributor_id', '');
        $rsm_id = $request->get('rsm_id', '');
        $brand = $request->get('brand', '');

        // Brand map
        $brandMap = [
            'ONN'  => 1,
            'PYNK' => 2,
            'Both' => 3,
        ];

        $brandCode = $brandMap[$brand] ?? null;

        if (!$brandCode) {
            return response()->json(['error' => true, 'resp' => 'Invalid brand value']);
        }

        // Base query
        $query = DB::table('stores')
            ->select('stores.id', 'stores.name', 'stores.wallet')
            ->join('teams', 'teams.store_id', '=', 'stores.id')
            ->whereRaw('FIND_IN_SET(?, teams.rsm_id)', [$rsm_id])
            ->where('stores.brand', $brandCode)
            ->where('stores.status', 1)
            ->where('stores.is_deleted', 0);

        // Optional filters
        if (!empty($distributor_id)) {
            $query->where('teams.distributor_id', $distributor_id);
        }

        if (!empty($keyword)) {
            $query->where(function ($subQuery) use ($keyword) {
                $subQuery->where('stores.name', 'like', '%' . $keyword . '%')
                        ->orWhere('stores.contact', '=', $keyword);
            });
        }

        // Execute query
        $stores = $query->latest('stores.id')->get();

        // Return response
        if ($stores->isNotEmpty()) {
            return response()->json([
                'error' => false,
                'resp' => 'Stores fetched successfully',
                'data' => $stores
            ]);
        }

        return response()->json(['error' => true, 'message' => 'No data found']);
    }



    public function rewardorderrsmDetail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rsm_id'     => ['required'],
            'brand'      => ['nullable'], // brand optional filter
            'date_from'  => ['nullable', 'date'],
            'date_to'    => ['nullable', 'date'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
            ]);
        }

        $rsmId = $request->rsm_id;
        $brand = $request->brand;
        $perPage = $request->per_page ?? 10;
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        // 🔹 Brand mapping
            $brandMap = [
                'ONN'  => 1,
                'PYNK' => 2,
                'Both' => 3,
            ];

            $brandText = $request->brand;
            $brandCode = $brandMap[$brandText] ?? null;
        
            if (!$brandCode) {
                return response()->json(['error' => true, 'resp' => 'Invalid brand value']);
            }
        // Build query dynamically
        $query = RetailerOrder::select('retailer_orders.*')->with([
                'user' => function ($q) {
                    $q->where('status', 1)->where('is_deleted', 0);
                },
                'orderProduct' => function ($q) {
                    $q->whereHas('product', function ($p) {
                            $p->where('status', 1)->where('is_deleted', 0);
                        })
                        ->with([
                            'product' => function ($p) {
                                $p->where('status', 1)->where('is_deleted', 0);
                            }
                        ]);
                }
            ])
            
            ->join('stores', 'stores.id', '=', 'retailer_orders.user_id')
            ->join('teams', 'teams.store_id', '=', 'stores.id')
            ->whereRaw("FIND_IN_SET(?, teams.rsm_id)", [$rsmId])
            ->where('stores.status', 1)                         // ✅ active stores only
            ->where('stores.is_deleted', 0)
            ->where('teams.is_deleted', 0);

        // Optional brand filter
        if (!empty($brand)) {
            $query->where('retailer_orders.brand', $brandCode);
        }

        // Optional date filters
        if (!empty($dateFrom)) {
            $query->whereDate('retailer_orders.created_at', '>=', $dateFrom);
        }
        if (!empty($dateTo)) {
            $query->whereDate('retailer_orders.created_at', '<=', $dateTo);
        }

        $query->orderByDesc('retailer_orders.id');

        $data = $query->paginate($perPage);

         $filtered = $data->filter(function ($order) {
            return $order->user &&
                $order->user->status == 1 &&
                $order->user->is_deleted == 0 &&
                $order->orderProduct->isNotEmpty();
                })->values();
        return response()->json([
            'error' => false,
            'message' => 'Product orders with quantity and brand filter',
            'data' => $filtered,
        ]);
    }


    public function rewardorderrsmStatus(Request $request) {
        $validator = Validator::make($request->all(), [
            'order_id' => ['required'],
			'rsm_approval'=>['required'],
            'rsm_note' => ['nullable'],
        ]);

        if (!$validator->fails()) {
            
                $order = RetailerOrder::where('id',$request->order_id)->first();
                if(empty($order)){
                    return response()->json(['error' => true, 'message' => 'No order found']);
                }else{

                $order->rsm_approval = $request['rsm_approval'];
        		$order->rsm_note = $request['rsm_note'];
				$order->save();
                }
			//dd($orders);
            

            return response()->json([
                'error' => false,
                'message' => 'Status updated',
                'data' => $order,
            ]);

        } else {
            return response()->json(['error' => true, 'message' => $validator->errors()->first()]);
        }
    }


    //VP

    public function vpstateList(Request $request,$id)
    {
        $data=Team::where('vp_id',$id)->groupby('state_id')->with('states:id,name')->get();
        if ($data->isNotEmpty()) {
            return response()->json(['error'=>false, 'resp'=>'State List','data'=>$data]);
                 
        } else {
            return response()->json(['error'=>true, 'resp'=>'No data found']);   
        } 
    }


    public function vpstateareaList(Request $request)
    {
        $data=Team::where('vp_id',$request->vp_id)->where('state_id',$request->state_id)->groupby('area_id')->with('areas:id,name')->get();
        if ($data->isNotEmpty()) {
            return response()->json(['error'=>false, 'resp'=>'Area List','data'=>$data]);
                 
        } else {
            return response()->json(['error'=>true, 'resp'=>'No data found']);   
        } 
    }
    
    public function inactiveAseListVP(Request $request)
    {
        $userId = $request->user_id;

        $brandMap = [
            'ONN'  => 1,
            'PYNK' => 2,
            'Both' => 3,
        ];

        $brandText = $request->brand;
        $brandCode = $brandMap[$brandText] ?? null;

        if (!$brandCode) {
            return response()->json(['error' => true, 'resp' => 'Invalid brand value']);
        }

        // Handle "Both"
        $brandsToCheck = ($brandCode == 3) ? [1, 2] : [$brandCode];

        // ✅ Get ASE IDs under this ASM
        $aseDetails = Team::join('employees', 'teams.ase_id', '=', 'employees.id')
            ->where('teams.vp_id', $userId)
            ->where('teams.brand', $brandCode)
            ->where('teams.status', 1)
            ->where('teams.is_deleted', 0)
            ->pluck('teams.ase_id')
            ->unique()
            ->toArray();

        // ✅ Get ASEs who are active today (did “Visit Started”)
        $today = now()->format('Y-m-d');

        $activeASEreport = Activity::where('type', 'Visit Started')
            ->whereDate('date', $today)
            ->whereIn('user_id', $aseDetails)
            ->pluck('user_id')
            ->unique()
            ->toArray();

        // ✅ Get ASEs who are NOT in the active list
        $inactiveASE = Employee::select('employees.*')
            ->join('teams', 'teams.ase_id', '=', 'employees.id')
            ->where('teams.vp_id', $userId)
            ->where('teams.brand', $brandCode)
            ->whereNotIn('employees.id', $activeASEreport)
            ->where('teams.status', 1)
            ->where('teams.is_deleted', 0)

            ->where('employees.is_deleted', 0)
            ->groupBy('employees.id')
            ->orderBy('employees.name')
            ->with(['stateDetail', 'area']) // works correctly now
            ->get();

        return response()->json([
            'error' => false,
            'resp' => 'Inactive ASE report - Team wise',
            'data' => $inactiveASE,
        ]);
    }



    public function storeReportVP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vp_id' => ['required'],
            'date_from' => ['nullable'],
            'date_to' => ['nullable'],
            'state' => ['nullable'],
            'region' => ['nullable'],
            'collection' => ['nullable'],
            'category' => ['nullable'],
            'orderBy' => ['nullable'],
            'style_no' => ['nullable'],
            'brand' => ['required'],
        ]);
        
        if ($validator->fails()) {
            return response()->json(['error' => true, 'message' => $validator->errors()->first()]);
        }

        $from = $request->filled('from') ? date('Y-m-d', strtotime($request->from)) : date('Y-m-01');
        $to   = $request->filled('to') ? date('Y-m-d', strtotime($request->to)) : date('Y-m-d');

        // 🔹 Filter values
        $stateQuery = ($request->state == '10000' || empty($request->state)) ? null : $request->state;
        $regionQuery = ($request->region == '10000' || empty($request->region)) ? null : $request->region;
        $collectionQuery = ($request->collection == '10000' || empty($request->collection)) ? null : $request->collection;
        $categoryQuery   = ($request->category == '10000' || empty($request->category)) ? null : $request->category;
        $styleNoQuery    = $request->style_no;

        // 🔹 Handle orderBy
        $orderByQuery = match ($request->orderBy) {
            'date_asc' => 'id ASC',
            'qty_asc'  => 'qty ASC',
            'qty_desc' => 'qty DESC',
            default    => 'id DESC',
        };
        
        $user = Employee::findOrFail($request->vp_id);
        $rsmQuery = Team::where('vp_id', $request->vp_id)->where('status',1)->where('is_deleted',0)
                   ->whereNull('store_id')
                  ->whereNotNull('rsm_id');
        if (!empty($stateQuery)) {
            $rsmQuery->where('state_id', $stateQuery);
        }
        if (!empty($regionQuery)) {
            $rsmQuery->where('area_id', $regionQuery);
        }     
        $rsmIds = $rsmQuery->pluck('rsm_id')->unique()->toArray();
        
        // 🔹 ASE Query
        $aseQuery = Team::where('vp_id', $request->vp_id)
            ->where('status', 1)
            ->where('is_deleted', 0)
            ->whereNull('store_id')
            ->whereNotNull('ase_id');

        // Apply region filter if provided
        if (!empty($stateQuery)) {
            $aseQuery->where('state_id', $stateQuery);
        }
        if (!empty($regionQuery)) {
            $aseQuery->where('area_id', $regionQuery);
        }

        $aseIds = $aseQuery->pluck('ase_id')->unique()->toArray();
        
        // Fetch ASE Data in One Query with Eager Loading (If applicable)
        $ases = Employee::whereIn('id', $aseIds)->get();
        $rsms = Employee::whereIn('id', $rsmIds)->get();
        
        
        $brandMap = [
            'ONN'  => 1,
            'PYNK' => 2,
            'Both' => 3,
        ];
        $brandCode = $request->brand;
        $brandName = $brandMap[$brandCode] ?? null;

        //primary
            $distributorQuery = Team::where('vp_id', $request->vp_id)
            ->where('brand', $brandName)
            ->whereNull('store_id')
            ->where('status', 1)
            ->where('is_deleted', 0)
            ->whereHas('distributor', function ($q) use ($brandName) {
                $q->where('brand', $brandName)
                ->where('status', 1)
                ->where('is_deleted', 0);
            })
            ->with('distributor:id,name');
            if (!empty($stateQuery)) {
                $distributorQuery->where('state_id', $stateQuery);
            }
            if (!empty($regionQuery)) {
                $distributorQuery->where('area_id', $regionQuery);
            }

            // 🔹 Execute query
            $distributors = $distributorQuery->get();
            
             $respArrd = [];

            foreach ($distributors as $item) {

                // 🔹 If style_no is provided, get product IDs first
                $productIds = [];
                if (!empty($styleNoQuery)) {
                    $productIds = Product::where('style_no', 'LIKE', "%{$styleNoQuery}%")
                        ->where('status', 1)
                        ->where('is_deleted', 0)
                        ->pluck('id')
                        ->toArray();
                }
                // 🔹 Build base query
                $query = PrimaryOrder::where('distributor_id', $item->distributor_id)
                    ->where('brand', $brandName)
                    ->whereBetween('order_date', [$from, $to]);
                
                // 🔹 Handle filters with comma-separated columns
                if (!empty($collectionQuery)) {
                    $query->whereRaw("FIND_IN_SET(?, collection_id)", [$collectionQuery]);
                }

                if (!empty($categoryQuery)) {
                    $query->whereRaw("FIND_IN_SET(?, cat_id)", [$categoryQuery]);
                }

                if (!empty($productIds)) {
                    $query->where(function ($q) use ($productIds) {
                        foreach ($productIds as $pid) {
                            $q->orWhereRaw("FIND_IN_SET(?, product_id)", [$pid]);
                        }
                    });
                }

                $qty = $query->sum('qty');
                // 🚫 Skip if no quantity (no orders)
                if ($qty <= 0) continue;
                $respArrd[] = [
                    'distributor_id' => $item->distributor_id,
                    'distributor_name'  => $item->distributor->name ?? '',
                    'brand'       => $request->brand,
                    'amount'      => 0,
                    'qty'         => $qty ?? 0,
                ];
                
            }
            $productIds = [];
            if (!empty($styleNoQuery)) {
                $productIds = Product::where('style_no', 'LIKE', "%{$styleNoQuery}%")
                    ->where('status', 1)
                    ->where('is_deleted', 0)
                    ->pluck('id')
                    ->toArray();
            }

            // 🔹 Build base query
            $query = SecondaryASEOrder::select('ase_id', DB::raw('SUM(qty) as total_qty'))
                    ->whereIn('ase_id', $aseIds)
                    ->where('brand', $brandName)
                    ->whereBetween('order_date', [$from, $to])
                    ->groupBy('ase_id');
                    
            
            // 🔹 Handle filters with comma-separated columns
            if (!empty($collectionQuery)) {
                $query->whereRaw("FIND_IN_SET(?, collection_id)", [$collectionQuery]);
            }

            if (!empty($categoryQuery)) {
                $query->whereRaw("FIND_IN_SET(?, cat_id)", [$categoryQuery]);
            }

            if (!empty($productIds)) {
                $query->where(function ($q) use ($productIds) {
                    foreach ($productIds as $pid) {
                        $q->orWhereRaw("FIND_IN_SET(?, product_id)", [$pid]);
                    }
                });
            }

            $aseSales =  $query->pluck('total_qty', 'ase_id')->toArray();
            // 🔹 Build ASM → ASE → qty mapping
                $rsmReport = [];
                foreach ($rsmIds as $rsmId) {
                    $aseUnderRsm = Team::where('rsm_id', $rsmId)
                        ->where('brand', $brandName)->groupby('ase_id')
                        ->pluck('ase_id')
                        ->toArray();

                    $aseList = [];
                    foreach ($aseUnderRsm as $aseId) {
                        $ase = Employee::find($aseId);
                        $aseList[] = [
                            'ase_id'   => $aseId,
                            'ase_name' => $ase->name ?? 'N/A',
                            'qty'      => $aseSales[$aseId] ?? 0,
                        ];
                    }

                    $rsm = Employee::find($rsmId);
                    $rsmReport[] = [
                        'rsm_id'   => $rsmId,
                        'rsm_name' => $rsm->name ?? 'N/A',
                        'total_qty' => array_sum(array_column($aseList, 'qty')),
                    ];
                }
                $resp[] = [
                    'primary_sales' => $respArrd,
                    'secondary_sales' => $aseResp,
                ];
        return response()->json([
            'error' => false,
            'message' => 'VP report - Team wise',
            'PrimarySales' => $respArrd,
            'SecondarySales' => $rsmReport
            
            
        ]);
    }


     public function productReportVP(Request $request)
    {
        \DB::connection()->enableQueryLog();

        $validator = Validator::make($request->all(), [
            'vp_id' => ['required'],
            'from' => ['required'],
            'to' => ['required'],
            'state' => ['nullable'],
            'region' => ['nullable'],
            'collection' => ['nullable'],
            'category' => ['nullable'],
            'orderBy' => ['nullable'],
            'style_no' => ['nullable'],
            'brand' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => true, 'resp' => $validator->errors()->first()]);
        }

        $from = date('Y-m-d', strtotime($request->from));
        $to   = date('Y-m-d', strtotime($request->to));
         // 🔹 Filter values
        $stateQuery = ($request->state == '10000' || empty($request->state)) ? null : $request->state;
        $regionQuery = ($request->region == '10000' || empty($request->region)) ? null : $request->region;
        $collectionQuery = ($request->collection == '10000' || empty($request->collection)) ? null : $request->collection;
        $categoryQuery   = ($request->category == '10000' || empty($request->category)) ? null : $request->category;
        $styleNoQuery    = $request->style_no;

        // 🔹 Handle orderBy
        $orderByQuery = match ($request->orderBy) {
            'date_asc' => 'id ASC',
            'qty_asc'  => 'qty ASC',
            'qty_desc' => 'qty DESC',
            default    => 'id DESC',
        };

        $user = Employee::findOrFail($request->vp_id);

        // 🔹 ASE Query
        $aseQuery = Team::where('vp_id', $request->vp_id)
            ->where('status', 1)
            ->where('is_deleted', 0)
            ->whereNull('store_id')
            ->whereNotNull('ase_id');

        // Apply region filter if provided
        if (!empty($stateQuery)) {
            $aseQuery->where('state_id', $stateQuery);
        }
        if (!empty($regionQuery)) {
            $aseQuery->where('area_id', $regionQuery);
        }

        $aseIds = $aseQuery->pluck('ase_id')->unique()->toArray();

        
        
        // Fetch ASE Data in One Query with Eager Loading (If applicable)
        $ases = Employee::whereIn('id', $aseIds)->get();

        
        // 🔹 Brand mapping
        $brandMap = [
            'ONN'  => 1,
            'PYNK' => 2,
            'Both' => 3,
        ];

        $brandText = $request->brand;
        $brandCode = $brandMap[$brandText] ?? null;
        if (!$brandCode) {
            return response()->json(['error' => true, 'resp' => 'Invalid brand value']);
        }

        // 🔹 Handle "Both"
        $brandsToCheck = ($brandCode == 3) ? [1, 2] : [$brandCode];

        
        $finalData = [];

            $productIds = [];
            if (!empty($styleNoQuery)) {
                $productIds = Product::where('style_no', 'LIKE', "%{$styleNoQuery}%")
                    ->where('status', 1)
                    ->where('is_deleted', 0)
                    ->pluck('id')
                    ->toArray();
            }
            // 🔹 Get all secondary orders for this store
            $query = SecondaryAseOrder::whereIN('ase_id', $aseIds)
                ->where('brand', $brandCode)
                ->whereBetween('order_date', [$from, $to]);
                
             // 🔹 Handle filters with comma-separated columns
            if (!empty($collectionQuery)) {
                $query->whereRaw("FIND_IN_SET(?, collection_id)", [$collectionQuery]);
            }

            if (!empty($categoryQuery)) {
                $query->whereRaw("FIND_IN_SET(?, cat_id)", [$categoryQuery]);
            }
            if (!empty($productIds)) {
                $query->where(function ($q) use ($productIds) {
                    foreach ($productIds as $pid) {
                        $q->orWhereRaw("FIND_IN_SET(?, product_id)", [$pid]);
                    }
                });
            }
            $orders=$query->get();
            // 🔹 Get all secondary orders for this store
           

            $productQtyMap = [];

            // 🔹 Loop orders to gather product quantities
            foreach ($orders as $order) {
                $productIds = explode(',', $order->product_id);
                $qtys = explode(',', $order->qty);

                foreach ($productIds as $index => $pid) {
                    $pid = trim($pid);
                    $q = isset($qtys[$index]) ? (int)$qtys[$index] : 0;

                    if ($pid && $q > 0) {
                        if (!isset($productQtyMap[$pid])) {
                            $productQtyMap[$pid] = 0;
                        }
                        $productQtyMap[$pid] += $q;
                    }
                }
            }

            // 🔹 Fetch product details
            if (!empty($productQtyMap)) {
                $productDetails = Product::whereIn('id', array_keys($productQtyMap))
                    ->where('status', 1)
                    ->where('is_deleted', 0)
                    ->get(['id', 'name', 'style_no']);

                foreach ($productDetails as $prod) {
                    $finalData[] = [
                        
                        'brand'      => $brandText,
                        'product'    => $prod->name,
                        'style_no'   => $prod->style_no,
                        'qty'        => $productQtyMap[$prod->id] ?? 0,
                    ];
                }
            }
        

        return response()->json([
            'error' => false,
            'resp'  => 'VP Product-wise report fetched successfully',
            'data'  => array_values($finalData),
        ]);
    }

    public function vpdistributorList(Request $request)
    {
        $vp = $_GET['user_id'];
        
        $data= Team::select('distributor_id','area_id')->where('vp_id',$vp)->where('store_id',NULL)->with('distributor')->distinct('distributor_id')->get();
        if ($data->isNotEmpty()) 
        {
            return response()->json(['error' => false, 'resp' => 'Distributor data fetched successfully','data' => $data]);
        } else {
            return response()->json(['error' => true, 'resp' => 'Something happened']);
        }
    }
   public function onncurrencyVP(Request $request)
    {
        $keyword = $request->get('keyword', '');
        $distributor_id = $request->get('distributor_id', '');
        $vp_id = $request->get('vp_id', '');
        $brand = $request->get('brand', '');

        // Map brand names to numeric codes
        $brandMap = [
            'ONN'  => 1,
            'PYNK' => 2,
            'Both' => 3,
        ];

        $brandCode = $brandMap[$brand] ?? null;

        if (!$brandCode) {
            return response()->json(['error' => true, 'resp' => 'Invalid brand value']);
        }

        if (empty($vp_id)) {
            return response()->json(['error' => true, 'resp' => 'VP ID is required']);
        }

        // Base query
        $query = DB::table('stores')
            ->select('stores.id', 'stores.name', 'stores.wallet')
            ->join('teams', 'teams.store_id', '=', 'stores.id')
            ->whereRaw('FIND_IN_SET(?, teams.vp_id)', [$vp_id])
            ->where('stores.brand', $brandCode)
            ->where('stores.status', 1)
            ->where('stores.is_deleted', 0);

        // Apply filters only if present
        if (!empty($distributor_id)) {
            $query->where('teams.distributor_id', $distributor_id);
        }

        if (!empty($keyword)) {
            $query->where(function($q) use ($keyword) {
                $q->where('stores.name', 'like', '%' . $keyword . '%')
                ->orWhere('stores.contact', '=', $keyword);
            });
        }

        $stores = $query->latest('stores.id')->get();

        if ($stores->isNotEmpty()) {
            return response()->json([
                'error' => false,
                'resp' => 'Stores fetched successfully',
                'data' => $stores
            ]);
        } else {
            return response()->json([
                'error' => true,
                'resp' => 'No data found'
            ]);
        }
    }



    public function rewardordervpDetail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vp_id'     => ['required'],
            'brand'      => ['nullable'], // brand optional filter
            'date_from'  => ['nullable', 'date'],
            'date_to'    => ['nullable', 'date'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
            ]);
        }

        $vpId = $request->vp_id;
        $brand = $request->brand;
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        // 🔹 Brand mapping
            $brandMap = [
                'ONN'  => 1,
                'PYNK' => 2,
                'Both' => 3,
            ];

            $brandText = $request->brand;
            $brandCode = $brandMap[$brandText] ?? null;
        
            if (!$brandCode) {
                return response()->json(['error' => true, 'resp' => 'Invalid brand value']);
            }
        // Build query dynamically
        $query = RetailerOrder::select('retailer_orders.*')->with([
                'user' => function ($q) {
                    $q->where('status', 1)->where('is_deleted', 0);
                },
                'orderProduct' => function ($q) {
                    $q->whereHas('product', function ($p) {
                            $p->where('status', 1)->where('is_deleted', 0);
                        })
                        ->with([
                            'product' => function ($p) {
                                $p->where('status', 1)->where('is_deleted', 0);
                            }
                        ]);
                }
            ])
            ->join('stores', 'stores.id', '=', 'retailer_orders.user_id')
            ->join('teams', 'teams.store_id', '=', 'stores.id')
            ->whereRaw("FIND_IN_SET(?, teams.vp_id)", [$vpId])
            ->where('stores.status', 1)
            ->where('stores.is_deleted', 0)
            ->where('teams.is_deleted', 0);

        // Optional brand filter
        if (!empty($brand)) {
            $query->where('retailer_orders.brand', $brandCode);
        }

        // Optional date filters
        if (!empty($dateFrom)) {
            $query->whereDate('retailer_orders.created_at', '>=', $dateFrom);
        }
        if (!empty($dateTo)) {
            $query->whereDate('retailer_orders.created_at', '<=', $dateTo);
        }

        $query->orderByDesc('retailer_orders.id');

        $data = $query->get();

         $filtered = $data->filter(function ($order) {
            return $order->user &&
               $order->user->status == 1 &&
               $order->user->is_deleted == 0 &&
               $order->orderProduct->isNotEmpty();
                })->values();
        return response()->json([
            'error' => false,
            'message' => 'Product orders with quantity and brand filter',
            'data' => $filtered,
        ]);
    }


    public function rewardordervpStatus(Request $request) {
        $validator = Validator::make($request->all(), [
            'order_id' => ['required'],
			'vp_approval'=>['required'],
            'vp_note' => ['nullable'],
        ]);

        if (!$validator->fails()) {
            
                $order = RetailerOrder::where('id',$request->order_id)->first();
                if(empty($order)){
                    return response()->json(['error' => true, 'message' => 'No order found']);
                }else{
                $order->vp_approval = $request['vp_approval'];
        		$order->vp_note = $request['vp_note'];
				$order->save();
                }
			//dd($orders);
            

            return response()->json([
                'error' => false,
                'message' => 'Status updated',
                'data' => $order,
            ]);

        } else {
            return response()->json(['error' => true, 'message' => $validator->errors()->first()]);
        }
    }


    //distributor


    
    public function distributorAddTocart(Request $request)
    {
        $validator = Validator::make($request->all(),[
           
            'distributor_id' => 'required',
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
                $cartExists = CartDistributor::where('product_id', $collectedData['product_id'])->where('distributor_id', $collectedData['distributor_id'])->where('user_id', 0)->where('color_id', $colors[$i])->where('size_id', $sizes[$i])->where('brand', $brandCode)->first();
                
    
                if ($cartExists) {
                        $cartExists->qty = $cartExists->qty + $qtys[$i];
                        $cartExists->save();
                        return response()->json(['error'=>false, 'resp'=>'Product qty updated','data'=>$cartExists]);
                } else {
                    if ($collectedData['order_type']) {
                        if ($collectedData['order_type'] == 'distributor-visit') {
                            $orderType = 'Distributor visit';
                        } else {
                            $orderType = 'Order on call';
                        }
                    } else {
                        $orderType = null;
                    }
                    
                    $newEntry = new CartDistributor;
                   
                    $newEntry->distributor_id = $collectedData['distributor_id'] ?? null;
                    $newEntry->user_id = 0;
                    $newEntry->order_type = $orderType;
                    $newEntry->product_id = $collectedData['product_id'];
                    $newEntry->color_id = $colors[$i];
                    $newEntry->size_id = $sizes[$i];
                    $newEntry->qty = $qtys[$i];
                    $newEntry->brand = $brandCode;
                    $newEntry->save();
                }
            }
            if($newEntry){
                return response()->json(['error'=>false, 'resp'=>'Product added to cart successfully','data'=>$newEntry]);
            }else{
                return response()->json(['error'=>false, 'resp'=>'Something happend']);
            }
        }else {
            return response()->json(['error' => true, 'resp' => $validator->errors()->first()]);
        }
    }

    public function distributorappcartqtyUpdate(Request $request)
    {
        $cart = CartDistributor::findOrFail($request->cartId);
        
        if ($cart) {
			 $cart->qty = $request->qty;
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

    public function showBydistributorapp(Request $request)
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
        $query = CartDistributor::where('distributor_id', $request->distributorId)->where('user_id', 0)->whereHas('product')->with([
                'size:id,name,size_details',
                'color:id,name',
                'product' => function ($q) {
                    $q->select('id', 'name', 'style_no','brand')
                        ->where('status', 1)
                        ->where('is_deleted', 0);
                }
               ]);
            //->with(['product:id,name,style_no,brand', 'color:id,name', 'size:id,name,size_details']);

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


    public function distributorappcartDelete(Request $request,$id)
    {
        $cart=CartDistributor::destroy($id);
        if ($cart) {
            return response()->json(['error'=>false, 'resp'=>'Product removed from cart']);
        } else {
            return response()->json(['error' => true, 'resp' => 'Something happened']);
        }
    }

    public function distributorappcartPreviewPDF_URL(Request $request)
    {
        $brandMap = [
            'ONN' => 1,
            'PYNK' => 2,
            'Both' => 3,
        ];

        $brandName = $request->brand; // e.g. ONN, PYNK, Both
        $brandId = $brandMap[$brandName] ?? null;
        return response()->json([
            'error' => false,
            'resp' => 'URL generated',
            'data' => url('/').'/api/distributor/cart/pdf/view/?distributorId='.$request->distributorId.'&brand='.$brandId,
        ]);
    }

    

    public function distributorappcartPreviewPDF_view(Request $request)
    {
        // Map brand name to code
        $brandMap = [
            'ONN' => 1,
            'PYNK' => 2,
            'Both' => 3,
        ];

        $brandCode = $brandMap[$request->brand] ?? null;

        // Base query
        $query = CartDistributor::where('distributor_id', $request->distributorId)->where('user_id', 0)->whereHas('product')
            ->with(['product', 'distributors', 'color', 'size']);

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

        $cartData = $query->get();

        return view('api.distributor-cart-pdf', compact('cartData'));
    }


    public function distributorappplaceOrderUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'distributor_id' => ['required'],
            'brand' => ['required'],
            'order_type' => ['required', 'string', 'min:1'],
            'order_lat' => ['required', 'string', 'min:1'],
            'order_lng' => ['required', 'string', 'min:1'],
            'comment' => ['nullable', 'string', 'min:1'],
            'order_placed_by' => ['required'],
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
            $cart_count = CartDistributor::where('distributor_id', $collectedData['distributor_id'])
            ->where('user_id',0)
            ->where('brand',$brandValue)->whereHas('product')
            ->with(['product' => function ($query) {
                $query->where('status', 1)
                    ->where('is_deleted', 0);
            }])->get();
            //dd($cart_count);
            if ($cart_count->isNotEmpty()) {

                $firstCart = $cart_count->first();

                if ($firstCart->brand == 1) {
                    [$order_no, $sequence_no] = generateprimaryONNOrderNumber('primary', $collectedData['distributor_id']);
                } else {
                    [$order_no, $sequence_no] = generateprimaryPYNKOrderNumber('primary', $collectedData['distributor_id']);
                }
                            // 1 order
                $newEntry = new OrderDistributor;
                $newEntry->sequence_no = $sequence_no;
                $newEntry->order_no = $order_no;
                $newEntry->distributor_id = $collectedData['distributor_id'];
                $newEntry->brand = $brandValue;
                $newEntry->order_placed_by = $collectedData['order_placed_by'];
                $newEntry->user_id = 0 ?? '';
                //$aseDetails=DB::select("select * from employees where id='".$collectedData['user_id']."'");
                //$aseName=$aseDetails[0]->name;
                $user=$newEntry->distributor_id;
    			$result = DB::select("select * from distributors where id='".$user."'");
                $item=$result[0];
                $name = $item->name;
                $newEntry->order_type = $collectedData['order_type'] ?? null;
                $newEntry->order_lat = $collectedData['order_lat'] ?? null;
                $newEntry->order_lng = $collectedData['order_lng'] ?? null;
    
    			$newEntry->email = $item->email;
    			$newEntry->mobile = $item->contact;
                // fetch cart details
                
                $subtotal = $totalOrderQty = 0;
                foreach ($cart_count as $cartValue) {
                    if ($cartValue->product) {
                        $totalOrderQty += $cartValue->qty;
                        $subtotal += $cartValue->product->offer_price * $cartValue->qty;
                        $store_id = $cartValue->store_id;
                        $order_type = $cartValue->order_type;
                    } else {
                        return response()->json(['error' => true, 'resp' => 'Product not exist or inactive/deleted']);
                    }
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
                $orderProductsNewEntry = OrderProductDistributor::insert($orderProducts);
                  CartDistributor::where('distributor_id', $newEntry->distributor_id)->where('user_id',0)->where('brand',$brandValue)->delete();
    
    			// notification: sender, receiver, type, route, title
                // notification to ASE
                sendNotification($collectedData['user_id'], $brandValue, 'admin', 'primary-order-place', 'front.user.order', $totalOrderQty.' New order placed',$totalOrderQty.' new order placed  '.$name);
    
    
    
    
                return response()->json(['error'=>false, 'resp'=>'Order placed successfully','data'=>$newEntry]);
            }else{
                return response()->json(['error'=>true, 'resp'=>'cart empty']);
            }
        } else {
            return response()->json(['status' => 400, 'resp' => $validator->errors()->first()]);
        }
    }

    public function distributorapporderPDF_URL(Request $request, $id)
    {
        return response()->json([
            'error' => false,
            'resp' => 'URL generated',
            'data' => url('/').'/api/distributor/order/pdf/view/'.$id,
        ]);
    }

    

    public function distributorapporderPDF_view(Request $request, $id)
    {
        $orderData =OrderProductDistributor::where('order_id',$id)->whereHas('product')->with('product','color','size','orders')->get();
		
        return view('api.distributor-order-pdf', compact('orderData','id'));
    }


    public function distributorprimaryorderList(Request $request)
    {
        $brandMap = [
            'ONN' => 1,
            'PYNK' => 2,
            'Both' => 3,
        ];

        $brandCode = $brandMap[$request->brand] ?? null;

        $orderQuery = OrderDistributor::where('distributor_id', $request->distributorId)
            
            ->where('brand', $brandCode)
            ->with('distributors:id,name')
            ->orderBy('id', 'desc');

        // ✅ Apply date filters only if provided
        if ($request->filled('from') && $request->filled('to')) {
            $fromDate = date('Y-m-d 00:00:00', strtotime($request->from));
            $toDate = date('Y-m-d 23:59:59', strtotime($request->to));

            $orderQuery->whereBetween('created_at', [$fromDate, $toDate]);
        }

        $orders = $orderQuery->get();
        // ✅ Add total quantity field to each order
        $orders->map(function ($order) {
            $order->total_qty = $order->orderProducts->sum('qty');
            unset($order->orderProducts); // optional: remove detailed items if not needed
            return $order;
        });
        if ($orders->isNotEmpty()) {
            return response()->json([
                'error' => false,
                'resp' => 'Order list fetched successfully',
                'data' => $orders
            ]);
        } else {
            return response()->json([
                'error' => true,
                'resp' => 'No orders found for the given filters'
            ]);
        }
    }




    public function storeOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => ['required'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'distributor_id' => ['required'],
            'brand' => ['required'],
            'per_page' => ['nullable', 'integer', 'min:1'], // optional
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first()
            ]);
        }

        $brandMap = ['ONN' => 1, 'PYNK' => 2, 'Both' => 3];
        $brandCode = $brandMap[$request->brand] ?? null;
        $perPage = $request->per_page ?? 10;

        $from = $request->date_from ? date('Y-m-d', strtotime($request->date_from)) : date('Y-m-d');
        $to   = $request->date_to   ? date('Y-m-d', strtotime($request->date_to))   : $from;

        $query = Order::with(['orderProducts.color', 'orderProducts.size', 'orderProducts.product'])
            ->where('store_id', $request->store_id)
            ->where('distributor_id', $request->distributor_id)
            ->where('brand', $brandCode)
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to])
            ->orderBy('id', 'desc');

        $resp = $query->paginate($perPage);

        return response()->json([
            'error' => false,
            'resp'  => 'Order data fetched successfully',
            'data'  => $resp
        ]);
    }

	
	
	
    //product wise order details for distributor dashboard
	
	//  public function productOrder(Request $request)
    //  {
    //    // $params = $request->except('_token');
    //      $validator = Validator::make($request->all(), [
    //          'date_to' => ['nullable'],
    //          'date_from' => ['nullable'],
    //          'distributor_id' => ['required'],
    //         'distributor_name' => ['required'],
    //      ]);
    //       DB::enableQueryLog();
    //      if (!$validator->fails()) {
        
             
	// 	            if (!empty($request->date_from)) {
    //                      $from = date('Y-m-d', strtotime($request->date_from));
    //                  } else {
    //                      $from = date('Y-m-d');
    //                  }
	// 				 // date to
    //                 if (!empty($request->date_to)) {
    //                     $to = date('Y-m-d', strtotime($request->date_to.'+1 day'));
    //                     //dd($to);
    //                 } else {
    //                     $to = date('Y-m-d', strtotime('+1 day'));
    //                 }
    //         $resp = OrderProduct::select(
    //             DB::raw("SUM(order_products.qty) as product_count"),
    //             'orders.order_no',
    //             'products.name AS product_name',
    //             'order_products.product_id',
    //             'products.style_no',
    //             'order_products.size_id',
    //             'colors.name AS color_name',
    //             'sizes.name AS size_name',
    //             'stores.name AS store_name',
    //             'orders.created_at'
    //         )
    //         ->join('products', 'products.id', '=', 'order_products.product_id')
    //         ->join('orders', 'orders.id', '=', 'order_products.order_id')
    //         ->join('colors', 'colors.id', '=', 'order_products.color_id')
    //         ->join('sizes', 'sizes.id', '=', 'order_products.size_id')
    //         ->join('stores', 'stores.id', '=', 'orders.store_id')
    //         ->join('teams', 'teams.store_id', '=', 'stores.id')
            
    //         ->where('orders.distributor_id', $request->distributor_id)
    //         ->whereBetween('orders.created_at', [$from, $to])
    //         ->groupBy('order_products.id')
    //         ->orderBy('order_products.id', 'desc')
    //         ->get()
    //         ->map(function ($item) {
    //             $item->created_at = Carbon::parse($item->created_at)->format('Y-m-d H:i:s');
    //             return $item;
    //         });

        
    //          return response()->json(['error'=>false, 'resp'=>'Order data fetched successfully','data'=>$resp]);
      
    //     } else {
    //          return response()->json(['error' => true, 'message' => $validator->errors()->first()]);
    //     }
    //  }

    public function productOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date_to' => ['nullable'],
            'date_from' => ['nullable'],
            'distributor_id' => ['required'],
            'distributor_name' => ['nullable'],
             'brand' => ['required'],
            'per_page' => ['nullable', 'integer', 'min:1'], // optional, for pagination
            'page' => ['nullable', 'integer', 'min:1'],     // optional
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => true, 'message' => $validator->errors()->first()]);
        }

        $from = $request->date_from ? date('Y-m-d', strtotime($request->date_from)) : date('Y-m-d');
        $to = $request->date_to ? date('Y-m-d', strtotime($request->date_to.' +1 day')) : date('Y-m-d', strtotime('+1 day'));
         $brandMap = [
                'ONN' => 1,
                'PYNK' => 2,
                'Both' => 3,
            ];

         $brandCode = $brandMap[$request->brand] ?? null;
        $perPage = $request->per_page ?? 10; // default 10 items per page
        
        $query = OrderProduct::select(
                DB::raw("SUM(order_products.qty) as product_count"),
                'orders.order_no',
                'products.name AS product_name',
                'order_products.product_id',
                'products.style_no',
                'order_products.size_id',
                'colors.name AS color_name',
                'sizes.name AS size_name',
                'stores.name AS store_name',
                'orders.created_at'
            )
            ->join('products', 'products.id', '=', 'order_products.product_id')
            ->join('orders', 'orders.id', '=', 'order_products.order_id')
            ->join('colors', 'colors.id', '=', 'order_products.color_id')
            ->join('sizes', 'sizes.id', '=', 'order_products.size_id')
            ->join('stores', 'stores.id', '=', 'orders.store_id')
            ->join('teams', 'teams.store_id', '=', 'stores.id')
            ->where('orders.distributor_id', $request->distributor_id)
            ->where('orders.brand',$brandCode)
            ->whereBetween('orders.created_at', [$from, $to])
            ->groupBy('order_products.product_id', 'order_products.size_id', 'order_products.color_id', 'orders.order_no') // better grouping
            ->orderBy('order_products.id', 'desc');

        $resp = $query->paginate($perPage);

        // Format the created_at field for all items
        $resp->getCollection()->transform(function ($item) {
            $item->created_at = Carbon::parse($item->created_at)->format('Y-m-d H:i:s');
            return $item;
        });

        return response()->json([
            'error' => false,
            'resp' => 'Order data fetched successfully',
            'data' => $resp
        ]);
    }
	
	public function csvExport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => 'required',
            'date_from' => 'required',
            'date_to' => 'required',
            'distributor_id' => 'required',
            'brand' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => true, 'message' => $validator->errors()->first()]);
        }

        $brandMap = ['ONN' => 1, 'PYNK' => 2, 'Both' => 3];
        $brandCode = $brandMap[$request->brand] ?? null;

        $from = date('Y-m-d', strtotime($request->date_from));
        $to   = date('Y-m-d', strtotime($request->date_to));

        // Get employee IDs in bulk
        $employeeIds = \App\Models\OrderProduct::query()
            ->join('orders', 'orders.id', '=', 'order_products.order_id')
            ->join('stores', 'stores.id', '=', 'orders.store_id')
            ->join('teams', 'teams.store_id', '=', 'stores.id')
            ->where('orders.store_id', $request->store_id)
            ->where('orders.distributor_id', $request->distributor_id)
            ->where('orders.brand', $brandCode)
            ->whereBetween('orders.created_at', [$from, $to])
            ->get(['stores.user_id', 'teams.asm_id', 'teams.rsm_id', 'teams.vp_id'])
            ->flatMap(function ($row) {
                return array_merge(
                    explode(',', $row->user_id),
                    explode(',', $row->asm_id),
                    explode(',', $row->rsm_id),
                    explode(',', $row->vp_id)
                );
            })
            ->unique()
            ->filter();

        // Fetch all employees in one query
        $employees = DB::table('employees')
            ->whereIn('id', $employeeIds)
            ->pluck('name', 'id')
            ->toArray();

        $filename = "secondary-order-" . date('Y-m-d') . ".xlsx";

        return (new \App\Exports\SecondarySalesExport(
            $request->store_id,
            $request->distributor_id,
            $brandCode,
            $from,
            $to,
            $employees
        ))->download($filename);
    }
	
	
	//product wise order csv download for distributor
	
	public function csvProductExport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'distributor_name' => ['nullable'],
            'distributor_id' => ['required'],
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date'],
             'brand' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => true, 'message' => $validator->errors()->first()]);
        }

        $from = date('Y-m-d', strtotime($request->date_from));
        $to   = date('Y-m-d', strtotime($request->date_to . '+1 day'));
        $brandMap = ['ONN' => 1, 'PYNK' => 2, 'Both' => 3];
        $brandCode = $brandMap[$request->brand] ?? null;
        $filename = "secondary-order-product-" . date('Y-m-d') . ".csv";
         return (new \App\Exports\SecondarySalesProductExport(
           
            $request->distributor_id,
            $from,
            $to,
            $brandCode
        ))->download($filename);
        // Stream CSV with chunking
        //return \Maatwebsite\Excel\Facades\Excel::download(
         //   new \App\Exports\SecondarySalesProductExport($request->distributor_id, $from, $to,$brandCode),
         //   $filename,
         //   \Maatwebsite\Excel\Excel::CSV
            //);
    }



    public function onncurrencyDistributor(Request $request)
    {
        $keyword = $request->get('keyword', '');
        $distributor_id = $request->get('distributor_id', '');
        $brand = $request->get('brand', '');

        // Brand mapping
        $brandMap = [
            'ONN'  => 1,
            'PYNK' => 2,
            'Both' => 3,
        ];

        $brandCode = $brandMap[$brand] ?? null;
        $perPage = $request->per_page ?? 10;
        if (!$brandCode) {
            return response()->json([
                'error' => true,
                'resp' => 'Invalid brand value'
            ]);
        }

        if (empty($distributor_id)) {
            return response()->json([
                'error' => true,
                'resp' => 'Distributor ID is required'
            ]);
        }

        // Base query
        $query = DB::table('stores')
            ->select('stores.id', 'stores.name', 'stores.wallet')
            ->join('teams', 'teams.store_id', '=', 'stores.id')
            ->whereRaw('FIND_IN_SET(?, teams.distributor_id)', [$distributor_id])
            ->where('stores.brand', $brandCode)
            ->where('stores.status', 1)
            ->where('stores.is_deleted', 0);

        // Apply keyword filter if provided
        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('stores.name', 'like', '%' . $keyword . '%')
                ->orWhere('stores.contact', '=', $keyword);
            });
        }

        $stores = $query->latest('stores.id')->paginate($perPage);
        
        if ($stores->isNotEmpty()) {
            return response()->json([
                'error' => false,
                'resp' => 'Stores fetched successfully',
                'data' => $stores
            ]);
        }

        return response()->json([
            'error' => true,
            'resp' => 'No data found'
        ]);
    }



    public function rewardorderdistributorDetail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'distributor_id'     => ['required'],
            'brand'      => ['nullable'], // brand optional filter
            'date_from'  => ['nullable', 'date'],
            'date_to'    => ['nullable', 'date'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
            ]);
        }

        $distributorId = $request->distributor_id;
        $brand = $request->brand;
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $perPage = $request->per_page ?? 10;
        // 🔹 Brand mapping
            $brandMap = [
                'ONN'  => 1,
                'PYNK' => 2,
                'Both' => 3,
            ];

            $brandText = $request->brand;
            $brandCode = $brandMap[$brandText] ?? null;
        
            if (!$brandCode) {
                return response()->json(['error' => true, 'resp' => 'Invalid brand value']);
            }
        // Build query dynamically
        $query = RetailerOrder::with([
                'user' => function ($q) {
                    $q->where('status', 1)->where('is_deleted', 0);
                },
                'orderProduct' => function ($q) {
                    $q->whereHas('product', function ($p) {
                            $p->where('status', 1)->where('is_deleted', 0);
                        })
                        ->with([
                            'product' => function ($p) {
                                $p->where('status', 1)->where('is_deleted', 0);
                            }
                        ]);
                }
            ])
            ->join('stores', 'stores.id', '=', 'retailer_orders.user_id')
            ->join('teams', 'teams.store_id', '=', 'stores.id')
            ->whereRaw("FIND_IN_SET(?, teams.distributor_id)", [$distributorId])
            ->where('stores.status', 1)
            ->where('stores.is_deleted', 0)
            ->where('teams.is_deleted', 0);

        // Optional brand filter
        if (!empty($brand)) {
            $query->where('retailer_orders.brand', $brandCode);
        }

        // Optional date filters
        if (!empty($dateFrom)) {
            $query->whereDate('retailer_orders.created_at', '>=', $dateFrom);
        }
        if (!empty($dateTo)) {
            $query->whereDate('retailer_orders.created_at', '<=', $dateTo);
        }

        $query->orderByDesc('retailer_orders.id');

        $data = $query->paginate($perPage);
       
         $filtered = $data->filter(function ($order) {
            return $order->user &&
                $order->user->status == 1 &&
                $order->user->is_deleted == 0 &&
                $order->orderProduct->isNotEmpty();
            })->values();

        return response()->json([
            'error' => false,
            'message' => 'Retailer orders with quantity and brand filter',
            'data' => $filtered,
        ]);
    }


    public function rewardorderdistributorStatus(Request $request) {
        $validator = Validator::make($request->all(), [
            'order_id' => ['required'],
			'distributor_approval'=>['required'],
            'distributor_note' => ['nullable'],
        ]);

        if (!$validator->fails()) {
            
                $order = RetailerOrder::where('id',$request->order_id)->first();
                if(empty($order)){
                    return response()->json(['error' => true, 'message' => 'No order found']);
                }else{
                $order->distributor_approval = $request['distributor_approval'];
        		$order->distributor_note = $request['distributor_note'];
				$order->save();
                }
			//dd($orders);
            

            return response()->json([
                'error' => false,
                'message' => 'Status updated',
                'data' => $order,
            ]);

        } else {
            return response()->json(['error' => true, 'message' => $validator->errors()->first()]);
        }
    }



    public function distributorstoreList(Request $request)
    {
		$distributor = $_GET['distributor_id'];
        $perPage = $request->per_page ?? 10;
        $brandMap = [
            1 => 'ONN',
            2 => 'PYNK',
            3 => 'Both',
        ];

		
		$stores = Store::join('teams', 'stores.id', '=', 'teams.store_id')->whereRaw("FIND_IN_SET(?, teams.distributor_id)", [$distributor])->where('stores.status',1)->where('stores.is_deleted',0)->with('state','area','user')->paginate($perPage);
		
	
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






    //retailer


    public function allstateList(Request $request)
    {
         $data = State::where('status',1)->where('is_deleted', 0)
                ->orderby('name')
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
    public function allareaList(Request $request,$id)
    {
        $data = Area::where('state_id',$id)->where('status',1)
                ->where('is_deleted', 0)
                ->with('state')
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
     public function retailerLogin(Request $request) {
        $validator = Validator::make($request->all(), [
            'contact' => ['required', 'integer','digits:10'],
			'password' => ['required'],
        ]);
        if (!$validator->fails()) {
            $mobile = $request->contact;
			$password = $request->password;
			$device_id = $request->device_id;
            $userCheck = Store::where('contact', $mobile)->where('is_deleted',0)->with('state','area')->first();
			//dd($userCheck);
            if ($userCheck) {
                 if (Hash::check($password, $userCheck->password)) {
					 $status = $userCheck->status;
					 if ($status == 0) {
						return response()->json(['error' => true, 'resp' =>  'Your account is temporary blocked. Contact Admin']);
					}else{
                        $brandMap = [
                            1 => 'ONN',
                            2 => 'PYNK',
                            3 => 'Both',
                        ];

                        $brands = [$userCheck->brand];

                                    // Check conditions
                                        if (in_array(3, $brands)) {
                                            $brandPermissions = 'Both';
                                        } elseif (in_array(1, $brands) && in_array(2, $brands)) {
                                            $brandPermissions = 'Both';
                                        } else {
                                            $brandPermissions = collect($brands)
                                                ->map(fn($brand) => $brandMap[$brand] ?? $brand)
                                                ->implode(', ');
                                        }
						 $store=Store::findOrfail($userCheck->id);
						 $store->device_id =$device_id;
						 $store->save();
                     return response()->json(['status' => true, 'message' => 'Login successful', 'data' => $userCheck,'brand' => $brandPermissions]);
					 }
                    // return response()->json(['error' => false, 'resp' => 'Login successful', 'data' => $userCheck]);
                 } else {
                     return response()->json(['status' => false, 'message' => 'You have entered wrong login credential. Please try with the correct one.', 'data' => $userCheck->password]);
                 }
                //return response()->json(['error' => false, 'resp' => 'Login successful', 'data' => $userCheck->mobile]);
            } else {
                return response()->json(['status' => false, 'message' => 'You have entered wrong login credential. Please try with the correct one.']);
            }
        }
     else {
        return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }
    }


    public function retailerLoginPin(Request $request) {
        $validator = Validator::make($request->all(), [
            'contact' => ['required'],
			'secret_pin' => ['required'],
        ]);
        if (!$validator->fails()) {
            $uniqueCode = $request->contact;
			$password = $request->secret_pin;
            $userCheck = Store::where('contact', $uniqueCode)->where('is_deleted',0)->with('state','area')->first();
			//dd($userCheck);
            if ($userCheck) {
                 if($password==$userCheck->secret_pin) {
					 $status = $userCheck->status;
					 if ($status == 0) {
						return response()->json(['error' => true, 'resp' =>  'Your account is temporary blocked. Contact Admin']);
					}else{
                        $brandMap = [
                            1 => 'ONN',
                            2 => 'PYNK',
                            3 => 'Both',
                        ];

                        $brands = [$userCheck->brand];

                                    // Check conditions
                                        if (in_array(3, $brands)) {
                                            $brandPermissions = 'Both';
                                        } elseif (in_array(1, $brands) && in_array(2, $brands)) {
                                            $brandPermissions = 'Both';
                                        } else {
                                            $brandPermissions = collect($brands)
                                                ->map(fn($brand) => $brandMap[$brand] ?? $brand)
                                                ->implode(', ');
                                        }
                     return response()->json(['status' => true, 'message' => 'Login successful', 'data' => $userCheck,'brand' => $brandPermissions]);
					 }
                    // return response()->json(['error' => false, 'resp' => 'Login successful', 'data' => $userCheck]);
                 } else {
                     return response()->json(['status' => false, 'message' => 'You have entered wrong login credential. Please try with the correct one.', 'data' => $userCheck->secret_pin]);
                 }
                //return response()->json(['error' => false, 'resp' => 'Login successful', 'data' => $userCheck->mobile]);
            } else {
                return response()->json(['status' => false, 'message' => 'You have entered wrong login credential. Please try with the correct one.']);
            }
        }
     else {
        return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }
    }

    public function retailermyprofile($id)
    {
        $brandMap = [
            1 => 'ONN',
            2 => 'PYNK',
            3 => 'Both',
        ];
        $user = Store::where('id', $id)->with('state','area')->first();

        if ($user) {
            // Transform brand values
            $user->brand_name = $brandMap[$user->brand] ?? null;

            return response()->json([
                'status'  => true,
                'message' => 'Store/Retailer data fetched successfully',
                'data'    => $user,
            ], 200);
        } else {
            return response()->json([
                'status'  => false,
                'message' => 'No Store/Retailer data found',
            ], 404);
        }
        //return response()->json(['error'=>false, 'resp'=>'Retailer data fetched successfully','data'=>$user]);

    }

    public function retailerupdateProfile(Request $request,$id)
    {
        $updatedEntry = Store::findOrFail($id);
        if ($request['owner_name']) {
        $updatedEntry->owner_name = $request->owner_name;
        }
		if ($request['owner_lname']) {
        $updatedEntry->owner_lname = $request->owner_lname;
        }
        if ($request['name']) {
        $updatedEntry->name = $request->name;
        }
        if ($request['address']) {
        $updatedEntry->address = $request->address;
        }
        if ($request['contact']) {
        $updatedEntry->contact = $request->contact;
        }
        if ($request['email']) {
        $updatedEntry->email = $request->email;
        }
        if ($request['whatsapp']) {
        $updatedEntry->whatsapp = $request->whatsapp;
        }
        if ($request['pin']) {
        $updatedEntry->pin = $request->pin;
        }
        if ($request['area_id']) {
        $updatedEntry->area_id = $request->area_id;
        }
        if ($request['state_id']) {
        $updatedEntry->state_id = $request->state_id;
        }
        if ($request['city']) {
        $updatedEntry->city = $request->city;
        }
        
        if ($request['image']) {
            $updatedEntry->image = $request->image;
        }
        if ($request['aadhar']) {
            $updatedEntry->aadhar = $request->aadhar;
        }
        if ($request['pan']) {
            $updatedEntry->pan = $request->pan;
        }
        if ($request['gst']) {
            $updatedEntry->gst = $request->gst;
        }
        $updatedEntry->save();
        if($updatedEntry){
            return response()->json([
                'status'  => true,
                'message' => 'Store/Retailer data Updated successfully',
                'data'    => $updatedEntry,
            ], 200);
            
        } else {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }
    }
	
	//for change password
   
		 
	 public function retailerchangePassword(Request $request)
    {
        //dd($request->all());
        $validator = Validator::make($request->all(), [
             'mobile'  => 'required',
            'new_password' => 'required'
        ]);
        if (!$validator->fails()) {
        $check_old_pass = Store::where('contact',$request->mobile)->first();

        if (!$check_old_pass) {
            return response()->json(['status' => false, 'message' =>'Old Password is not correct']);
        }

        $new_pass = Hash::make($request->new_password);

        $updatedEntry = Store::where('contact', $request->mobile)->update(['password' => $new_pass]);

            return response()->json(['status' => true, 'message' => 'Update Successful','data'=>$updatedEntry]);
        } else {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }
    }
	
	/**
     * This method is to get user wallet balance
    *
    */
  
		 
	 public function retailerwalletBalance(Request $request,$id)
    {
        $data = Store::where('id',$id)->first();
        if($data){
            return response()->json(['status'=>true, 'message'=>'wallet balance data fetched successfully','data'=>$data->wallet]);
        } else {
            return response()->json(['status' => false, 'message' => 'No user found']);
        }
  
    }
	
	/**
     * This method is to get remove profile
    *
    */
    
		 
		 
	public function retailerremoveProfile(Request $request,$id)
    {
        $data = Store::where('id',$id)->delete();
        if($data){
            return response()->json(['status'=>true, 'message'=>'Profile deleted successfully','data'=>$data]);
        } else {
            return response()->json(['status' => false, 'message' => 'Something happend']);
        }
  
    }


    public function retailerRegister(Request $request)
    {
         
        $validator = Validator::make($request->all(), [
            'owner_name' => ['required', 'string', 'min:1'],
			'owner_lname' => ['nullable', 'string', 'min:1'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'min:1'],
            'contact' => ['required', 'integer','digits:10'],
            'pin' => ['required', 'integer','digits:6'],
            'state_id' => ['required', 'max:255'],
            'area_id' => ['required', 'max:255'],
            'city' => ['required', 'string','max:255'],
            'aadhar' => ['nullable'],
      
        ]);
       
        if (!$validator->fails()) {
			
            $upload_path = "uploads/retailer/document";
			$retailer_id = "ONN".mt_rand();
			$storeExist=Store::where('name',$request['name'])->where('area_id',$request['area_id'])->where('contact',$request['contact'])->where('state_id',$request['state_id'])->where('status',1)->where('is_deleted',0)->first();
			//dd($storeExist);
			if(($storeExist)){
				
				  return response()->json(['status' => false, 'message' => 'Store/Retailer already exist']);
			}else{
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
			
			$store = new Store;
			
			$store->name = $request['name'];
            $slug = Str::slug($request->name, '-');
            $slugExistCount = Store::where('name', $request->name)->count();
            if ($slugExistCount > 0) $slug = $slug.'-'.($slugExistCount);
            $store->slug = $slug;
            $store->brand = $brandValue;
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
			
			
			

			
		   
			
			
				 return response()->json(['status' => true, 'message' => 'Registration Successful','data'=>$store]);
			}
        } else {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }
    }


    public function retailerpinGenerate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'secret_pin' => ['required', 'integer', 'min:1'],
			'store_id'   => ['required', 'integer', 'min:1'],
      //  ], [
        //    'aadhar.*' => 'Please enter minimum one document'
        ]);

        if (!$validator->fails()) {
			
				$user= Store::findOrFail($request->store_id);
				$user->secret_pin = $request->secret_pin;
				$user->save();
				 return response()->json(['status' => true, 'message' => 'Pin Generated Successfully','data'=>$user]);
			
        } else {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }
    }
	
	
	public function retailerterms(Request $request)
    {
        /*$brandMap = [
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
        }*/
        //$data=DB::table('reward_terms')->where('brand',$brandValue)->latest('id')->first();
        $data=DB::table('reward_terms')->latest('id')->first();
        return response()->json(['status' => true, 'message' => 'Terms & condition fetched Successfully','data'=>$data]);
    }
    
    
    //monthly scan limit
    public function retailermonthlyScan(Request $request,$id)
    {
        /*$brandMap = [
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
        }*/
        $scanLimit=20;
        
        //$data=RetailerUserTxnHistory::where('user_id',$id)->where('brand',$brandValue)->where('type','qrcode scan')->whereYear('created_at', Carbon::now()->year)->whereMonth('created_at', Carbon::now()->month)->count();
        $data=RetailerUserTxnHistory::where('user_id',$id)->where('type','qrcode scan')->whereYear('created_at', Carbon::now()->year)->whereMonth('created_at', Carbon::now()->month)->count();
        return response()->json(['status' => true, 'message' => 'Monthly Scan Limit History','Monthly Scan Limit'=>$scanLimit,'Scan history by retailer'=>$data,'Monthly_Scan_Limit'=>$scanLimit,'Scan_history_by_retailer'=>$data]);
    }

    // retailer create aadhar document API
	public function retailerCreateAadhar(Request $request) {
		$validator = Validator::make($request->all(), [
            'aadhar' => 'required'
        ]);
        if (!$validator->fails()) {
				$imageName = mt_rand().'.'.$request->aadhar->extension();
				$uploadPath = 'public/uploads/retailer/document';
				$request->aadhar->move($uploadPath, $imageName);
				$total_path = $uploadPath.'/'.$imageName;
			     $resp = [
                       'data' => $total_path,
                       ];
			return response()->json(['error' => false, 'message' => 'Document added', 'data' => $resp]);
		} else {
            return response()->json(['error' => true, 'message' => $validator->errors()->first()]);
        }
		
	}


    public function retailerCreatePan(Request $request) {
		$validator = Validator::make($request->all(), [
            'pan' => 'required'
        ]);
        if (!$validator->fails()) {
				$imageName = mt_rand().'.'.$request->pan->extension();
				$uploadPath = 'public/uploads/retailer/document';
				$request->pan->move($uploadPath, $imageName);
				$total_path = $uploadPath.'/'.$imageName;
			     $resp = [
                       'data' => $total_path,
                       ];
			return response()->json(['error' => false, 'message' => 'Document added', 'data' => $resp]);
		} else {
            return response()->json(['error' => true, 'message' => $validator->errors()->first()]);
        }
		
	}


    public function retailerCreateGst(Request $request) {
		$validator = Validator::make($request->all(), [
            'gst' => 'required'
        ]);
        if (!$validator->fails()) {
				$imageName = mt_rand().'.'.$request->gst->extension();
				$uploadPath = 'public/uploads/retailer/document';
				$request->gst->move($uploadPath, $imageName);
				$total_path = $uploadPath.'/'.$imageName;
			     $resp = [
                       'data' => $total_path,
                       ];
			return response()->json(['status' => true, 'message' => 'Document added', 'data' => $resp]);
		} else {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }
		
	}


    public function retailerBarcode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => ['required'],
            'user_id' =>['required'],
        ]);

        if (!$validator->fails()) {
            $codeExp=explode(',', $request->code);
			$code=$codeExp[0];
            $userId =$request->user_id;
            $barcode=RetailerBarcode::where('code',$code)->first();
            //barcode exist check
            if(!$barcode){
                return response()->json(['status'=>false, 'message'=>'Sorry! Coupon is invalid']);
            }else{
				if ($barcode->start_date > \Carbon\Carbon::now()) {
                    return response()->json(['status'=>false, 'message'=>'Coupon is not valid now']);
                }else{
                // coupon code validity check
					if ($barcode->end_date < \Carbon\Carbon::now() || $barcode->status == 0) {
						return response()->json(['status'=>false, 'message'=>'Sorry! Coupon is expired']);
					}else{
					        $maxtimeusage = RetailerWalletTxn::where('user_id',$userId)->where('type',1)->whereYear('created_at', Carbon::now()->year)->whereMonth('created_at', Carbon::now()->month)->count();
                        					         
                            $limit=20;
                            					    
					    if ($maxtimeusage >= $limit) {
                                 return response()->json(['status'=>false, 'message'=>'Sorry! You have reached your monthly limit']);
                        }else{
						//no of usage check
    						if ($barcode->no_of_usage == $barcode->max_time_of_use || $barcode->no_of_usage >= $barcode->max_time_of_use){
    							return response()->json(['status'=>false, 'message'=>'Sorry! Coupon Already scanned']);
    						}else{
    						     $walletusage = RetailerWalletTxn::where('barcode',$barcode->code)->count();
                                 if ($walletusage >= $barcode->max_time_of_use || $walletusage >= $barcode->max_time_one_can_use) {
                                     return response()->json(['status'=>false, 'message'=>'Sorry! Coupon Already scanned']);
                                }else{
    							    $usage = RetailerWalletTxn::where('barcode_id',$barcode->id)->where('user_id',$userId)->count();
                                     if ($usage >= $barcode->max_time_of_use || $usage >= $barcode->max_time_one_can_use) {
                                         return response()->json(['status'=>false, 'message'=>'Sorry! Coupon Already scanned']);
                                    }else{
                                        
                                         
        							    $userExist=Store::where('id',$userId)->first();
        								if(!$userExist){
        									return response()->json(['status'=>false, 'message'=>'User is invalid']);
        								}else{
        									
        									$userAmount=RetailerWalletTxn::where('user_id',$userId)->orderby('id','desc')->first();
        									$walletTxn=new RetailerWalletTxn();
        									$walletTxn->user_id = $userId;
        									$walletTxn->barcode_id = $barcode->id;
        									$walletTxn->barcode = $barcode->code;
        									$walletTxn->amount = $barcode->amount;
        									$walletTxn->type = 1 ?? '';
        									if(!$userAmount){
        										$walletTxn->final_amount += $barcode->amount ?? '';
        									}else{
        									$walletTxn->final_amount = $userAmount->final_amount+ $barcode->amount ?? '';
        									}
        									$walletTxn->created_at = date('Y-m-d H:i:s');
        									$walletTxn->updated_at = date('Y-m-d H:i:s');
        									$walletTxn->save();
        									$user=Store::findOrFail($userId);
        									$user->wallet += $barcode->amount;
        									$user->save();
        									$userwalletTxn=new RetailerUserTxnHistory();
        									$userwalletTxn->user_id = $userId;
        									$userwalletTxn->barcode_id = $barcode->id;
        									$userwalletTxn->barcode = $barcode->code;
        									$userwalletTxn->amount = $barcode->amount;
        									$userwalletTxn->type = 'Qrcode scan' ?? '';
        									$userwalletTxn->title = $barcode->amount.' points earn';
        									$userwalletTxn->description = 'Using '.$barcode->code.' code';
        									$userwalletTxn->status = 'increment';
        									$userwalletTxn->created_at = date('Y-m-d H:i:s');
        									$userwalletTxn->updated_at = date('Y-m-d H:i:s');
        									$userwalletTxn->save();
        									$barcodeDetails=RetailerBarcode::findOrFail($barcode->id);
        									$barcodeDetails->no_of_usage = $barcode->no_of_usage+1;
        									$barcodeDetails->save();
        									
        								}
        						    }
    					        }
    					    }
    				    }
                    }
                }
               return response()->json(['status'=>true, 'message'=>'Coupon scanned successfully ; ' .$barcode->amount.' ONN currency has been added to your wallet','data'=>$barcode]);
            }
        
		} else {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }

    }
    public function retailerOrder(Request $request)
    {
        /*$brandMap = [
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
        }*/
        //$order = RetailerOrder::where('user_id',$request->userId)->where('brand',$brandValue)->orderby('id','desc')->take(5)->get();
        $order = RetailerUserTxnHistory::where('user_id',$request->userId)->where('type','points redeem')->orderby('id','desc')->with('order')->take(5)->get();
        return response()->json(['status'=>true, 'message'=>'Order history fetched successfully','data'=>$order]);
    }



    public function retailerproductList(Request $request)
     {
 
         $products = RetailerProduct::where('status',1)->where('is_deleted',0)->orderby('amount','ASC')->get();
 
         return response()->json(['status'=>true, 'message'=>'Product data fetched successfully','data'=>$products]);
 
     }
   
     /**
      * This method is for show product details
      * @param  $id
      *
      */
    public function retailerproductView(Request $request,$id)
     {
		 
         $products = RetailerProduct::where('id',$id)->first();
		 $productSpec=DB::table('product_specifications')->where('product_id',$id)->get();
		 $data[] = [
                'product' => $products,
                'productSpecification' => $productSpec,
            ];
         return response()->json(['status'=>true, 'message'=>'Product data fetched successfully','data'=>$products,'productSpecification'=>$productSpec]);
     }
     
	/**
      * This method is for show brochure details
      * 
      *
      */
    public function retailerbrochureindex(Request $request)
	{
        $brochure = Offer::where('is_current',1)->get();
        return response()->json(['status'=>true, 'message'=>'Product data fetched successfully','data'=>$brochure]);
    }
	
	   /**
      * This method is to get 5 order details
      *
      */
    public function retailerOrderDetails(Request $request,$orderId)
    {
		//dd($request->all());
        $resp = $orderDetails = [];
        $order = RetailerOrder::where('id',$orderId)->orderby('created_at','desc')->get();
        foreach ($order as $data) {
            $orderDetails = RetailerOrder::where('created_at', $data->created_at)
            ->orderby('id', 'desc')->with('orderProduct')
            ->get();
            $resp[] = [
                'date' => date('Y-m-d H:i:s', strtotime($data->created_at)),
                'order_details' => $orderDetails,
            ];
        }
        return response()->json([
                'status' => true,
                'message' => 'Order history with quanity',
                'data' => $resp,
            ]);
    }


    public function retailerrewardHistory(Request $request)
      {
        //dd($userId);
        $validator = Validator::make($request->all(), [
            'user_id' =>['required'],
			'pageNo' => ['required'],
            
        ]);
        if (!$validator->fails()) {
          $resp = [];
          $perPage = $request->pageNo ?? 10;
          $userId =$request->user_id;
          $userExist=Store::where('id','=',$userId)->first();
            if(!$userExist){
                return response()->json(['error'=>false, 'resp'=>'Store/Retailer is invalid']);
            }else{
                
                    $limit=20;
                   
                    $resp = RetailerUserTxnHistory::where('user_id',$userId)->where('type','barcode scan')->groupby('barcode_id')->orderby('id','desc')->with('qrcode')->paginate($perPage);
                    
                
            }
            return response()->json([
                'status' => true,
                'message' => 'Reward history with quanity',
                'data' => $resp,
				
            ]);

            } else {
                return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
            }
        
        }

    public function retailerTransaction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' =>['required'],
			'pageNo' => ['required'],
            
        ]);
        if (!$validator->fails()) {
          $resp = [];
          $perPage =$request->pageNo;
          $userId =$request->user_id;
          $userExist=Store::where('id','=',$userId)->first();
            if(!$userExist){
                return response()->json(['error'=>false, 'resp'=>'User is invalid']);
            }else{
                
                   $resp = RetailerUserTxnHistory::where('user_id',$userId)->orderby('id','desc')->with('qrcode')->paginate($perPage);
                    
            }
            return response()->json([
                'status' => true,
                'message' => 'Transaction history fetch successfully',
                'data' => $resp,
				
            ]);

        } else {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }
        
    }



    public function retailerRewardCart(Request $request, $id)
{
    $cartItems = RewardCart::where('store_id', $id)->whereHas('product')
            ->with([
                
                'product' => function ($q) {
                    $q->select('id', 'title','amount')
                        ->where('status', 1)
                        ->where('is_deleted', 0);
                }
            ])->get();

    $grouped = [];
    
    $total_amount = 0;
    foreach ($cartItems as $item) {
        $productId = $item->product_id;

        if (!isset($grouped[$productId])) {
            $grouped[$productId] = [
                'id' => $item->id,
                'store_id' => $item->store_id,
                'device_id' => $item->device_id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->title,
                'product_image' => $item->product->image,
                'status' => $item->status,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,

                // Aggregated values
                'qty' => 0,
                'price' => 0,
                'final_amount' => 0,
            ];
        }
        

        $grouped[$productId]['qty'] += $item->qty;
        $grouped[$productId]['final_amount'] += $item->product->amount * $item->qty;
        $grouped[$productId]['price'] += $item->product->amount;
        
        
        $total_amount += $item->product->amount * $item->qty;
    }

    $data = array_values($grouped);

    // Total cart values
    $cart_count = DB::select("select ifnull(sum(qty),0) as total_qty, ifnull(sum(final_amount),0) as total_amount from reward_carts where store_id = ?", [$id]);

    $total_quantity = $cart_count[0]->total_qty ?? 0;
    //$total_amount = $cart_count[0]->total_amount ?? 0;

    return response()->json([
        'status' => true,
        'message' => 'Cart data fetched successfully',
        'data' => $data,
        'total_quantity' => $total_quantity,
        'total_amount' => (string) $total_amount,
    ]);
}

	
    /**
     * This method is for show reward cart delete
     * @return \Illuminate\Http\JsonResponse
     */
	public function retailerRewardCartclear(Request $request, $id)
    {
        $data = RewardCart::findOrFail($id)->delete();

        if ($data) {
            return response()->json(['status' => true, 'message' => 'Product removed from cart']);
            // return response()->json(null, Response::HTTP_NO_CONTENT);
        } else {
            return response()->json(['status' => false, 'message' => 'Something happened']);
            # code...
        }
        
        
    }
	
	/**
     * This method is for reward product add to cart
     * @return \Illuminate\Http\JsonResponse
     */

    public function retailerrewardbulkAddTocart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => ['required', 'integer', 'min:1'],
            'device_id' => ['nullable'],
            'product_id' => ['required', 'integer'],
            'qty' => ['required'],
           
        ]);

        if (!$validator->fails()) {
            $params = $request->except('_token');
            $collectedData = $params;
            
            $cartExists = RewardCart::where('product_id', $collectedData['product_id'])->where('store_id', $collectedData['store_id'])->first();
        if ($cartExists) {
                $cartExists->qty = $cartExists->qty + $collectedData['qty'];
			    $cartExists->final_amount = $cartExists->price * $cartExists->qty;
                $cartExists->save();
        } else {
            
            $productDetails=RetailerProduct::where('id',$collectedData['product_id'])->first();
            $newEntry = new RewardCart;
            $newEntry->device_id = $collectedData['device_id'] ?? null;
            $newEntry->store_id = $collectedData['store_id'] ?? null;
            $newEntry->product_id = $collectedData['product_id'];
            $newEntry->price = $productDetails->amount;
			$newEntry->final_amount = $productDetails->amount * $collectedData['qty'];
            $newEntry->qty = $collectedData['qty'];
            $newEntry->save();
          }
			
        
        return response()->json(['status' => true, 'message' => 'Product successfully added to cart']);
        
        } else {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }
    }
	
	/**
     * This method is for update quantity for reward cart
     * @param  $id
     * @return \Illuminate\Http\JsonResponse
     */
   

    // $type = "incr"/ "decr"
    public function retailerRewardCartqtyUpdate(Request $request, $cartId,$q)
    {
        $cart = RewardCart::findOrFail($cartId);

        if ($cart) {
			 $cart->qty = $q;
			 $cart->final_amount = $cart->price * $q;
			 $cart->save();
            return response()->json([
                'status' => true,
                'message' => 'Quantity updated'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Something Happened'
            ]);
        }
    }


    public function retailerrewardplaceOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }

        $userExist = Store::where('id', $request['user_id'])->where('status', 1)->where('is_deleted', 0)->first();
        if (!$userExist) {
            return response()->json(['status' => false, 'message' => 'User is invalid']);
        }

        $userBalance = $userExist;

        // Get cart items
        $cartData = RewardCart::where('store_id', $request['user_id'])->whereHas('product')
            ->with(['product' => function ($query) {
                $query->where('status', 1)
                    ->where('is_deleted', 0);
            }])->get();

        // Separate inactive products
        $inactiveProducts = [];
        $activeCartItems = [];

        foreach ($cartData as $item) {
            if ($item->product->status != 1) {
                $inactiveProducts[] = $item->product->title ?? 'Unknown Product';
                $item->delete(); // Remove inactive product from cart
            } else {
                $activeCartItems[] = $item;
            }
        }

        // If no active items left
        if (empty($activeCartItems)) {
            return response()->json([
                'status' => false,
                'message' => 'No active products in cart. Order not placed.',
                'inactive_products' => $inactiveProducts,
            ]);
        }

        // Calculate total amount
        $total_amount = 0;
        foreach ($activeCartItems as $item) {
            $total_amount += $item->product->amount * $item->qty;
        }

        if ((int) $total_amount > (int) $userExist->wallet) {
            return response()->json([
                'status' => false,
                'message' => 'Wallet balance is low',
                'require_points' => (int)$total_amount - (int)$userExist->wallet,
            ]);
        }

        // Generate order number
        $OrderChk = RetailerOrder::select('order_sequence_int')->latest('id')->first();
        $orderSeq = empty($OrderChk->order_sequence_int) ? 1 : (int) $OrderChk->order_sequence_int + 1;
        $ordNo = sprintf("%'.05d", $orderSeq);
        $order_no = "REWARD" . date('y') . '/' . $ordNo;

        // Store user info
        $user = $userExist;
        $newEntry = new RetailerOrder;
        $newEntry->order_sequence_int = $orderSeq;
        $newEntry->order_no = $order_no;
        $newEntry->user_id = $request['user_id'];
        $newEntry->shop_name = $user->store_name ?? null;
        $newEntry->email = $user->email ?? null;
        $newEntry->mobile = $user->contact ?? null;
        $newEntry->billing_address = $user->address ?? null;
        $newEntry->billing_city = $user->area_id ?? null;
        $newEntry->billing_state = $user->state_id ?? null;
        $newEntry->billing_pin = $user->pin ?? null;

        // Calculate subtotal and qty
        $subtotal = $totalOrderQty = 0;
        foreach ($activeCartItems as $cartValue) {
            $totalOrderQty += $cartValue->qty;
            $subtotal += $cartValue->product->amount * $cartValue->qty;
        }

        $newEntry->amount = $subtotal;
        $newEntry->qty = $totalOrderQty;
        $newEntry->final_amount = $subtotal;
        $newEntry->save();

        // Save order products
        $orderProducts = [];
        foreach ($activeCartItems as $cartValue) {
            $orderProducts[] = [
                'order_id' => $newEntry->id,
                'product_id' => $cartValue->product_id,
                'product_name' => $cartValue->product->title,
                'product_image' => $cartValue->product->image,
                'product_slug' => $cartValue->product->slug,
                'price' => $cartValue->product->amount,
              
                'qty' => $cartValue->qty,
            ];
        }
        RewardOrderProduct::insert($orderProducts);

        // Clear remaining cart items
        RewardCart::where('store_id', $request['user_id'])->delete();

        // Deduct wallet
        $user->wallet -= $newEntry->final_amount;
        $user->save();

        // Wallet transaction
        $userAmount = RetailerWalletTxn::where('user_id', $request['user_id'])->orderBy('id', 'desc')->first();
        $walletTxn = new RetailerWalletTxn();
        $walletTxn->user_id = $newEntry->user_id;
        $walletTxn->amount = $newEntry->final_amount;
        $walletTxn->type = 2;
        $walletTxn->final_amount = $userAmount
            ? $userAmount->final_amount - $newEntry->final_amount
            : 0;
        $walletTxn->created_at = now();
        $walletTxn->updated_at = now();
        $walletTxn->save();

        // Transaction history
        $userwalletTxn = new RetailerUserTxnHistory();
        $userwalletTxn->user_id = $request['user_id'];
        $userwalletTxn->order_id = $newEntry->id;
        $userwalletTxn->amount = $newEntry->final_amount;
        $userwalletTxn->type = 'points redeem';
        $userwalletTxn->title = 'Redeem points';
        $userwalletTxn->description = 'You Purchase gift';
        $userwalletTxn->status = 'decrement';
        $userwalletTxn->created_at = now();
        $userwalletTxn->updated_at = now();
        $userwalletTxn->save();

        // Send notification
        sendNotification('admin', '', 'reward-order-place', 'front.user.order', $totalOrderQty . ' New order placed', $totalOrderQty . ' new order placed  ' . $user->name);

        return response()->json([
            'status' => true,
            'message' => 'Order placed successfully',
            'inactive_products' => $inactiveProducts,
            'wallet' => $user->wallet,
            'data' => $newEntry
        ]);
    }

    //b2b 
     public function retailerb2bbulkAddTocart(Request $request)
    {
        $validator = Validator::make($request->all(),[
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
                $cartExists = Cart::where('product_id', $collectedData['product_id'])->where('store_id', $collectedData['store_id'])->where('color_id', $colors[$i])->where('size_id', $sizes[$i])->where('brand', $brandCode)->first();
                
    
                if ($cartExists) {
                        $cartExists->qty = $cartExists->qty + $qtys[$i];
                        $cartExists->save();
                        return response()->json(['status'=>true, 'message'=>'Product qty updated','data'=>$cartExists]);
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
            if($newEntry){
                return response()->json(['status'=>true, 'message'=>'Product added to cart successfully','data'=>$newEntry]);
            }else{
                return response()->json(['status'=>false, 'message'=>'Something happend']);
            }
        }else {
            return response()->json(['status' => true, 'message' => $validator->errors()->first()]);
        }
    }


    public function retailerb2bqtyUpdateLatest(Request $request)
    {
        $cart = Cart::findOrFail($request->cartId);
        
        if ($cart) {
			 $cart->qty = $request->qty;
			 $cart->save();
            return response()->json([
                'status' => true,
                'message' => 'Quantity updated'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Something Happened'
            ]);
        }
    }

    public function retailerb2bshowByUser(Request $request,$storeId)
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
        $query = Cart::where('store_id', $storeId)
           ->whereHas('product')
            ->with([
                'size:id,name,size_details',
                'color:id,name',
                'product' => function ($q) {
                    $q->select('id', 'name', 'style_no','brand')
                        ->where('status', 1)
                        ->where('is_deleted', 0);
                }
            ]);
            //->with(['product:id,name,style_no,brand', 'color:id,name', 'size:id,name,size_details']);

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
            'status' => true,
            'message' => 'Cart list fetched successfully',
            'data' => $cart,
            'total_quantity' => $total_quantity,
        ]);
    }


    public function retailerb2bcartdelete(Request $request,$id)
    {
        $cart=Cart::destroy($id);
        if ($cart) {
            return response()->json(['status'=>true, 'message'=>'Product removed from cart']);
        } else {
            return response()->json(['status' => false, 'message' => 'Something happened']);
        }
    }

    public function retailerb2bcartPlacePDF_URL(Request $request)
    {
        $brandMap = [
            'ONN' => 1,
            'PYNK' => 2,
            'Both' => 3,
        ];

        $brandName = $request->brand; // e.g. ONN, PYNK, Both
        $brandId = $brandMap[$brandName] ?? null;
        return response()->json([
            'error' => false,
            'resp' => 'URL generated',
            'data' => url('/').'/api/retailer/b2b/cart/pdf/view/?storeId='.$request->storeId.'&brand='.$brandId,
        ]);
    }

    

    public function retailerb2bcartPreviewPDF_view(Request $request)
    {
        // Map brand name to code
        $brandMap = [
            'ONN' => 1,
            'PYNK' => 2,
            'Both' => 3,
        ];

        $brandCode = $brandMap[$request->brand] ?? null;

        // Base query
        $query = Cart::where('store_id', $request->storeId)
           ->whereHas('product')
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

        $cartData = $query->get();
       
        return view('api.cart-pdf', compact('cartData'));
    }


    public function retailerb2bplaceOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => ['required'],
            'brand' => ['required'],
            'order_type' => ['required', 'string', 'min:1'],
            'order_lat' => ['required', 'string', 'min:1'],
            'order_lng' => ['required', 'string', 'min:1'],
            'comment' => ['nullable', 'string', 'min:1'],
           
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
            $team=Team::where('store_id',$collectedData['store_id'])->first();

            $cart_count = Cart::where('store_id', $collectedData['store_id'])
            ->where('brand',$brandValue)->whereHas('product')
            ->with(['product' => function ($query) {
                $query->where('status', 1)
                    ->where('is_deleted', 0);
            }])->get();
            //dd($cart_count);
            if ($cart_count->isNotEmpty()) {

                $firstCart = $cart_count->first();

                if ($firstCart->brand == 1) {
                    [$order_no, $sequence_no] = generateONNOrderNumber('secondary', $collectedData['store_id']);
                } else {
                    [$order_no, $sequence_no] = generatePYNKOrderNumber('secondary', $collectedData['store_id']);
                }
                            // 1 order
                $newEntry = new Order;
                $newEntry->sequence_no = $sequence_no;
                $newEntry->order_no = $order_no;
                $newEntry->store_id = $collectedData['store_id'];
                $newEntry->brand = $brandValue;
                //$newEntry->distributor_id = $collectedData['distributor_id'] ?? '';
                
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
                foreach ($cart_count as $cartValue) {
                    if ($cartValue->product) {
                        $totalOrderQty += $cartValue->qty;
                        $subtotal += $cartValue->product->offer_price * $cartValue->qty;
                        $store_id = $cartValue->store_id;
                        $order_type = $cartValue->order_type;
                    } else {
                        return response()->json(['status' => false, 'message' => 'Product not exist or inactive/deleted']);
                    }
                }
                $newEntry->amount = $subtotal;
                $newEntry->comment = $collectedData['comment'] ?? null;
                $total = (int) $subtotal;
                $newEntry->final_amount = $total;

                $matchedDistributorId = null;
                $collectionIds = $cart_count->pluck('product.collection_id')->filter()->unique()->toArray();
                if (!empty($collectionIds)) {
                    $distributorRanges = DB::table('distributor_ranges')
                        ->whereIn('collection_id', $collectionIds)
                        ->pluck('distributor_id')
                        ->toArray();

                    $team = DB::table('teams')->where('store_id', $collectedData['store_id'])->first();
                    if ($team && $team->distributor_id) {
                        $teamDistributorIds = array_map('trim', explode(',', $team->distributor_id));
                        $matched = array_intersect($distributorRanges, $teamDistributorIds);
                        if (!empty($matched)) {
                            $matchedDistributorId = reset($matched);
                        }
                    }
                }

                $newEntry->distributor_id = $matchedDistributorId ?? null;
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
                  Cart::where('store_id', $newEntry->store_id)->where('brand',$brandValue)->delete();
    
    			// notification: sender, receiver, type, route, title
                // notification to ASE
                sendNotification($collectedData['store_id'], $brandValue,'admin', 'secondary-order-place', 'front.user.order', $totalOrderQty.' New order placed',$totalOrderQty.' new order placed  '.$name);
    
    
    			// notification to ASM
    			
    				$asm = DB::select("SELECT u.id as asm_id FROM `teams` t  INNER JOIN employees u ON u.id = t.asm_id where t.store_id = '".$collectedData['store_id']."' GROUP BY t.asm_id");
    			foreach($asm as $value){
    				sendNotification($collectedData['store_id'], $brandValue, $value->asm_id, 'secondary-order-place', 'front.user.order', $totalOrderQty.' new order placed by ' .$name ,$totalOrderQty.' new order placed from  '.$name);
    			}
    
               
    			// notification to RSM
    			
    			$rsm = DB::select("SELECT u.id as rsm_id FROM `teams` t  INNER JOIN employees u ON u.id = t.rsm_id where t.store_id = '".$collectedData['store_id']."' GROUP BY t.rsm_id");
    			foreach($rsm as $value){
    				sendNotification($collectedData['store_id'], $brandValue, $value->rsm_id, 'secondary-order-place', 'front.user.order', $totalOrderQty.' new order placed by ' .$name ,$totalOrderQty.' new order placed from  '.$name);
    			}
    			
    			// notification to vp
    			
    			$zsm = DB::select("SELECT u.id as vp_id FROM `teams` t  INNER JOIN employees u ON u.id = t.vp_id where t.store_id = '".$collectedData['store_id']."' GROUP BY t.vp_id");
    			foreach($zsm as $value){
    				sendNotification($collectedData['store_id'], $brandValue, $value->vp_id, 'secondary-order-place', 'front.user.order', $totalOrderQty.' new order placed by ' .$name ,$totalOrderQty.' new order placed from  '.$name);
    			}
    
    
                return response()->json(['status'=>true, 'message'=>'Order placed successfully','data'=>$newEntry]);
            }else{
                return response()->json(['status'=>false, 'message'=>'cart empty']);
            }
        } else {
            return response()->json(['status' => 400, 'message' => $validator->errors()->first()]);
        }
    }

    public function retailerb2bOrderPlacePDF_URL(Request $request, $id)
    {
        return response()->json([
            'error' => false,
            'resp' => 'URL generated',
            'data' => url('/').'/api/retailer/b2b/order/place/pdf/view/'.$id,
        ]);
    }

    

    public function retailerb2bOrderPlacePDF_view(Request $request, $id)
    {
        $orderData =OrderProduct::where('order_id',$id)->whereHas('product')->with('product','color','size','orders')->get();
		
        return view('api.order-pdf', compact('orderData','id'));
    }

    public function retailerb2bOrderlist(Request $request)
    {
        $brandMap = [
            'ONN' => 1,
            'PYNK' => 2,
            'Both' => 3,
        ];

        $brandCode = $brandMap[$request->brand] ?? null;

        $orderQuery = Order::where('store_id', $request->storeId)
           
            ->where('brand', $brandCode)
            ->with('stores:id,name')
            ->orderBy('id', 'desc');

        // ✅ Apply date filters only if provided
        if ($request->filled('from') && $request->filled('to')) {
            $fromDate = date('Y-m-d 00:00:00', strtotime($request->from));
            $toDate = date('Y-m-d 23:59:59', strtotime($request->to));

            $orderQuery->whereBetween('created_at', [$fromDate, $toDate]);
        }

        $orders = $orderQuery->get();
        // ✅ Add total quantity field to each order
        $orders->map(function ($order) {
            $order->total_qty = $order->orderProducts->sum('qty');
            unset($order->orderProducts); // optional: remove detailed items if not needed
            return $order;
        });
        if ($orders->isNotEmpty()) {
            return response()->json([
                'status' => true,
                'message' => 'Order list fetched successfully',
                'data' => $orders
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'No orders found for the given filters'
            ]);
        }
    }

    public function retailerb2bOrderDetails(Request $request,$id)
    {
        $order=OrderProduct::where('order_id',$id)->whereHas('product')->with('product','product.collection','product.category','color','size','orders','orders.stores')->get();
        if ($order) {
            return response()->json(['status'=>true, 'message'=>'order details fetched successfully','data'=>$order]);
        } else {
            return response()->json(['status' => false, 'message' => 'Something happened']);
        }
    }


    //cron


    public function secondaryOrderCron(Request $request)
    {
        
    } 
















    










   
    
    





    
















     






}
 