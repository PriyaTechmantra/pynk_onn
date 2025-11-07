<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RetailerBarcode;
use App\Models\RetailerWalletTxn;
use App\Models\CouponUsage;
use App\Models\User;
use App\Models\State;
use App\Models\RetailerUserTxnHistory;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Auth;

class BarcodeController extends Controller
{
    public function index(Request $request)
    {
		
		if (!empty($request->term)) {
			$data = RetailerBarcode::where([['name', 'LIKE', '%' . $request->term . '%']])->orWhere([['code', 'LIKE', '%' . $request->term . '%']])->where('is_print',1)->groupby('name')->orderby('id','desc')->paginate(25);
		} else {
			$data = RetailerBarcode::latest('id')->where('is_print',1)->groupBy('name')->paginate(25);
		}
		
        return view('reward.barcode.index', compact('data'));
    }

    public function create(Request $request)
    {
        $allDistributors = User::select('id','name','state','employee_id')->where('type',7)->where('name', '!=', null)->where('status',1)->orderBy('name')->get();
        $state = State::where('status',1)->groupBy('name')->orderBy('name')->get();
        return view('reward.barcode.create',compact('allDistributors','state'));
    }

    
	
		
}
