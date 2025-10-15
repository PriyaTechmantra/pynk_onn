<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Area;
use App\Models\UserArea;
use App\Models\Store;
use App\Models\Team;
use App\Models\PrimaryOrder;
use App\Models\SecondaryOrder;
use Str;
use Illuminate\Support\Facades\Validator;
use App\Models\UserPermissionCategory;
use DB;
use Illuminate\Support\Facades\Log;
class ASEController extends Controller
{
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
		$area=DB::table('visits')->where('user_id',$id)->where('start_date',date('Y-m-d'))->where('visit_id',NULL)->orderby('id','desc')->take(1)->get();
		$user=Employee::where('id',$id)->first();
        if (count($area)==0) {
            return response()->json(['status'=>false, 'message'=>'Start Your Visit']);
        } else {
            return response()->json(['status'=>true, 'message'=>'Visit already started','area'=>$area[0]->area,'visit_id'=>$area[0]->id,'data'=>$user],200);
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
        ]);

        if (!$validator->fails()) {
            $data = [
                "user_id" => $request->user_id,
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
}



//store list

    public function storeList(Request $request)
    {
		$ase = $_GET['ase_id'];
		
		$stores = Store::where('user_id',$ase)->where('status',1)->where('is_deleted',0)->get();
		
	
        if ($stores) {

            return response()->json(['status'=>true, 'message'=>'Store data fetched successfully','data'=>$stores],200);
        } else {
            return response()->json(['status' => false, 'message' => 'Something happened'],404);
        }
    }
    

    //inactive store list

    public function inactivestoreList(Request $request)
    {
        $ase = $_GET['ase_id'];
		
		$stores = Store::where('user_id',$ase)->where('status',0)->where('is_deleted',0)->get();
		
	
        if ($stores) {

            return response()->json(['status'=>true, 'message'=>'Store data fetched successfully','data'=>$stores],200);
        } else {
            return response()->json(['status' => false, 'message' => 'Something happened'],404);
        }
    }


    public function searchStore(Request $request)
    {
		$search = !empty($request->keyword)?$request->keyword:'';
        $query = Store::select('*')
        ->where('status', 1)
        ->where('is_deleted', 0);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('contact', $search)
                ->orWhere('name', 'LIKE', "%{$search}%");
            });
        }

        $data = $query->get();
		if(!empty($data)){
			foreach($data as $item){
				$retailer=Team::where('store_id',$item->id)->first();
				$item->team = $retailer;
			}
		}
         return response()->json([
            'status'=>true,
            'message'=>"Store List",
            'data'=> $data
            
        ],200);

    }
    
    //distributor list area wise


    public function distributorList(Request $request)
    {
        $distributors = Team::where('ase_id',$request->ase_id)->where('area_id',$request->area_id)->where('is_deleted',0)->with('distributor')->get();
		
	
        if ($distributors) {

            return response()->json(['status'=>true, 'message'=>'Distributor data fetched successfully','data'=>$distributors],200);
        } else {
            return response()->json(['status' => false, 'message' => 'Something happened'],404);
        }
    }
    

    //add store
    public function addStore(Request $request)
    {
         //dd($request->all());
        $request->validate([
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
            "pin" => "nullable|string",
            "area_id" => "required",
            "user_id" => "required",
            "distributor_id" => "required",
            "image" => "required|mimes:jpg,jpeg,png,svg,gif|max:10000000"
        ]);
        $user = Employee::where('id',$request->user_id)->first();
        $name = $user->name;
        $store = new Store;
        $store->user_id = $request->user_id;
        $store->name = $request->name ?? null;
        $slug = Str::slug($request->name, '-');
        $slugExistCount = Store::where('name', $request->name)->count();
        if ($slugExistCount > 0) $slug = $slug.'-'.($slugExistCount);
        $store->slug = $slug;

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
        $store->store_OCC_number = $request->store_OCC_number ?? null;
        $store->gst_no = $request->gst_no ?? null;
        $store->pan_no = $request->pan_no ?? null;
        $store->date_of_birth	 = $request->date_of_birth?? null;
        $store->date_of_anniversary	 = $request->date_of_anniversary?? null;
        $store->contact_person_name	 = $request->contact_person_name ?? null;
        $store->contact_person_lname = $request->contact_person_lname ?? null;
        $store->contact_person_phone	= $request->contact_person_phone ?? null;
        $store->contact_person_whatsapp	 = $request->contact_person_whatsapp ?? null;
        $store->contact_person_date_of_birth	 = $request->contact_person_date_of_birth ?? null;
        $store->contact_person_date_of_anniversary	 = $request->contact_person_date_of_anniversary ?? null;
        $store->status = 0;
        if($request->hasFile('image')) {
            $imageName = mt_rand().'.'.$request->image->extension();
            $uploadPath = 'uploads/store';
            $request->image->move($uploadPath, $imageName);
            $store->image = $uploadPath.'/'.$imageName;
        }
        $store->save();
       
        $result1 = Team::where('distributor_id',$request->distributor_id)->where('ase_id',$request->user_id)->where('state_id',$request->state_id)->where('area_id',$request->area_id)->first();

        $retailerListOfOcc = new Team;
        $retailerListOfOcc->vp_id = $result1->vp;
        $retailerListOfOcc->state_id = $result1->state_id;
        $retailerListOfOcc->distributor_id = $result1->distributor_id;
        $retailerListOfOcc->area_id = $result1->area_id;
        $retailerListOfOcc->store_id = $store->id ?? null;
        $retailerListOfOcc->rsm_id = $result1->rsm_id;
        $retailerListOfOcc->asm_id = $result1->asm_id;
        $retailerListOfOcc->ase_id = $result1->ase_id;
        $retailerListOfOcc->is_active = '1';
        $retailerListOfOcc->is_deleted = '0';
        $retailerListOfOcc->asm_rsm = $result1->rsm_id;
        $retailerListOfOcc->code = '';
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

        $response = Store::findOrFail($request->store_id);
        $response->image=$request->image;
        $response->save();
		if ($response) {
            return response()->json(['status' => true, 'message' => 'Data updated successfully', 'data' => $response]);
        } else {
            return response()->json(['status' => false, 'message' => 'Something happened']);
        }
        
    }

    public function noorderlist()
    {

        $data = NoOrderReason::all();

        return response()->json(['status'=>true, 'message'=>'no order list data fetched successfully','data'=>$data]);
    }


    public function noorder(Request $request)
    {
       $request->validate([
            "no_order_reason_id" => "required",
            "store_id" => "required",
            "user_id" => "required",
       ]);
        $data = UserNoOrderReason::new();
        $data->no_order_reason_id= $request->no_order_reason_id;
        $data->store_id= $request->store_id;
        $data->user_id= $request->user_id;
        $data->comment= $request->comment;
        $data->description= $request->description;
        $data->location= $request->location;
        $data->lat= $request->lat;
        $data->lng= $request->lng;
        $data->date= $request->date;
        $data->time= $request->time;
        $data->save();
        return response()->json(['status'=>true, 'message'=>'no order list data fetched successfully','data'=>$data]);
    }

    public function noorderhistory(Request $request, $id)
    {
        $noOrder=UserNoOrderReason::where('store_id', $id)->with('user','store')->orderby('id','desc')->get();
		if ($noOrder) {
        return response()->json(['status'=>true, 'message'=>'no order list data fetched successfully','data'=>$noOrder]);
		}else{
			  return response()->json(['error' => false, 'message' => 'No data found']);
		}
        
    }















}
 