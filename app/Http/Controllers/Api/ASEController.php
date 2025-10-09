<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Area;
use App\Models\UserArea;
use App\Models\Store;
use Str;
use Illuminate\Support\Facades\Validator;
use App\Models\UserPermissionCategory;
use DB;
class ASEController extends Controller
{
    public function areaList(Request $request)
    {
        $data = UserArea::where('user_id',$request->ase_id)->where('is_deleted',0)->with('area')->get();
        if ($data) {
             return response()->json(['status'=>true,'message' => 'List of areas','data' => $data ], 200);
        }else {
            return response()->json([
                'status' => false,
                'message' => 'Area list not found'
            ], 404);
        }
    }
    
    //check visit
    public function checkVisit(Request $request,$id){
		$area=DB::table('visits')->where('user_id',$id)->where('start_date',date('Y-m-d'))->where('visit_id',NULL)->orderby('id','desc')->take(1)->get();
		$user=Employee::where('id',$id)->first();
        if (count($area)==0) {
            return response()->json(['error'=>true, 'resp'=>'Start Your Visit']);
        } else {
            return response()->json(['error'=>false, 'resp'=>'Visit already started','area'=>$area[0]->area,'visit_id'=>$area[0]->id,'data'=>$user]);
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
        return response()->json(['error' => true, 'message' => $validator->errors()->first()]);
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

    return response()->json(['error' => false, 'message' => 'Visit started', 'visit_id' => $visit_id]);
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

            return response()->json(['error' => false, 'message' => 'Visit ended', 'data' => $data]);
        } else {
            return response()->json(['error' => true, 'message' => $validator->errors()->first()]);
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
                return response()->json(['error' => false, 'resp' => 'Activity stored successfully', 'data' => $resp]);
            }else{
                return response()->json(['error'=>true, 'resp'=>'Something happend']);
            }
           
        } else {
            return response()->json(['error' => true, 'resp' => $validator->errors()->first()]);
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
                return response()->json(['error' => false, 'resp' => 'Activity stored successfully', 'data' => $resp]);
            }else{
                return response()->json(['error'=>true, 'resp'=>'Something happend']);
            }
           
        } else {
            return response()->json(['error' => true, 'resp' => $validator->errors()->first()]);
        }
    }


       

    //ase wise primary and secondary report on dashboard
    
    
public function aseSalesreport(Request $request)
{
    $validator = Validator::make($request->all(), [
        "ase_id" => "required",
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => true, 'message' => $validator->errors()->first()]);
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
        'error' => false,
        'message' => 'ASE wise Primary & Secondary Sales Report',
        'Primary Sales | Distributor wise Daily Report' => $respArrd,
        'Secondary Sales | Retailer wise Daily Report' => $respArr,
    ]);
}



//store list

    public function storeList(Request $request)
    {
		$ase = $_GET['ase_id'];
		
		$stores = Store::where('user_id',$ase)->where('status',1)->where('is_deleted',0)->get();
		
	
        if ($stores) {

            return response()->json(['error'=>false, 'resp'=>'Store data fetched successfully','data'=>$stores]);
        } else {
            return response()->json(['error' => true, 'resp' => 'Something happened']);
        }
    }
    

    //inactive store list

    public function inactivestoreList(Request $request)
    {
        $ase = $_GET['ase_id'];
		
		$stores = Store::where('user_id',$ase)->where('status',0)->where('is_deleted',0)->get();
		
	
        if ($stores) {

            return response()->json(['error'=>false, 'resp'=>'Store data fetched successfully','data'=>$stores]);
        } else {
            return response()->json(['error' => true, 'resp' => 'Something happened']);
        }
    }

}
 