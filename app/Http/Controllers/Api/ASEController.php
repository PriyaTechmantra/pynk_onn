<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Area;
use App\Models\UserArea;
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
    
    
    public function aseSalesreport(Request $request) {

    $validator = Validator::make($request->all(), [
        "ase_id" => "required",
    ]);
    if (!$validator->fails()) {
        $ase = $request->ase_id;
		if ( request()->input('from') || request()->input('to') ) {
            // date from
            if (!empty(request()->input('from'))) {
                $from = date('Y-m-d', strtotime(request()->input('from')));
            } else {
                $from = date('Y-m-01');
            }

            // date to
            if (!empty(request()->input('to'))) {
                
                $to = date('Y-m-d', strtotime(request()->input('to').' +1 day'));
            } else {
                $to = date('Y-m-d', strtotime('+1 day'));
            }

          
            

            $distributor=Team::where('ase_id',$request->ase_id)->where('store_id',NULL)->with('distributor')->get();
            
            $respArrd = [];
            foreach ($distributor as $key => $item) {
                $report1 = \DB::select("SELECT od.user_id AS id,SUM(od.final_amount) AS amount, SUM(opd.qty) AS qty FROM `orders_distributors` AS od
                INNER JOIN order_products_distributors AS opd
                ON od.id = opd.order_id

                WHERE od.distributor_id = '".$item->distributor_id."'  AND (od.created_at BETWEEN '".$from."' AND '".$to."') ");
                  
                $respArrd[] = [
                    'distributor_id'=> $report1[0]->id ?? 0,
                    'distributor_name' => $item->distributor->name,
					'amount' => 0,
                    'qty' => $report1[0]->qty ?? 0,
                ];
            }

            $ase_id = DB::select("select id from users where name = '".$aseData."'");

            $secondaryreport = DB::select("SELECT s.store_name AS name, s.id FROM `stores` AS s
            WHERE s.user_id = '".$ase_id[0]->id."' and s.status=1");
            //dd($secondaryreport);
            $respArr = [];

            foreach ($secondaryreport as $key => $value) {
                $report = \DB::select("SELECT SUM(o.final_amount) AS amount, SUM(op.qty) AS qty FROM `orders` AS o
                                                    INNER JOIN order_products AS op
                                                    ON o.id = op.order_id
                WHERE o.store_id = '".$value->id."' AND (o.created_at BETWEEN '".$from."' AND '".$to."') ");
               //dd($value);
                $respArr[] = [
                    'retailer_id' => $value->id,
                    'store_name' => $value->name,
                    'amount' => $report[0]->amount ?? 0,
                    'qty' => $report[0]->qty ?? 0,
                ];

            }
        } else {
            $ase_name = DB::select("select name from users where name = '".$aseData."'");
            $primaryreport = DB::select("SELECT u.id AS user_id,distributor_name FROM `retailer_list_of_occ` AS ro
            INNER JOIN users AS u
                ON u.name = ro.distributor_name
            WHERE ase = '".$ase_name[0]->name."'
            GROUP BY distributor_name
            ORDER BY distributor_name ");
            $respArrd = [];
            foreach ($primaryreport as $key => $item) {
                $report1 = \DB::select("SELECT u.id AS user_id, SUM(od.final_amount) AS amount, SUM(opd.qty) AS qty FROM `orders_distributors` AS od
                INNER JOIN order_products_distributors AS opd
                ON od.id = opd.order_id
                INNER JOIN users AS u ON od.user_id = u.id
                WHERE od.distributor_name = '".$item->distributor_name."' AND DATE(od.created_at) = CURDATE() ");

                $respArrd[] = [
                    'distributor_id'=> $item->user_id,
                    'distributor_name' => $item->distributor_name,
                    'amount' => $report1[0]->amount ?? 0,
                    'qty' => $report1[0]->qty ?? 0,
                ];
            }
            $ase_id = DB::select("select id from users where name = '".$aseData."'");

            $secondaryreport = DB::select("SELECT s.store_name AS name, s.id FROM `stores` AS s
            WHERE s.user_id = '".$ase_id[0]->id."' ");
            $respArr = [];

            foreach ($secondaryreport as $key => $value) {
                $report = \DB::select("SELECT SUM(o.final_amount) AS amount, SUM(op.qty) AS qty FROM `orders` AS o
                INNER JOIN order_products AS op
                ON o.id = op.order_id
                WHERE o.store_id = '".$value->id."' AND DATE(o.created_at) = CURDATE() ");

                $respArr[] = [
                    'retailer_id' => $value->id,
                    'store_name' => $value->name,
                    'amount' => $report[0]->amount ?? 0,
                    'qty' => $report[0]->qty ?? 0,
                ];

            }
        }
        return response()->json(['error' => false, 'message' => 'ASE wise Primary Sales report', 'Primary Sales|Distributor wise Daily Report' => $respArrd,'Secondary Sales|Retailer wise Daily Report' => $respArr]);
    }else {
        return response()->json(['error' => true, 'message' => $validator->errors()->first()]);
    }
}
    

}
 