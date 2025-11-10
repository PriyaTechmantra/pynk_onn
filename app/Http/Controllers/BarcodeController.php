<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RetailerBarcode;
use App\Models\RetailerWalletTxn;
use App\Models\CouponUsage;
use App\Models\User;
use App\Models\Distributor;
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
        $query = RetailerBarcode::query();

        // Search filter
        if (!empty($request->term)) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->term . '%')
                ->orWhere('code', 'LIKE', '%' . $request->term . '%');
            });
        }

        // Brand filter
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

        // Date filter
        if (!empty($request->date_from) && !empty($request->date_to)) {
            $from = $request->date_from;
            $to = $request->date_to;

            $query->where(function ($q) use ($from, $to) {
                $q->whereBetween('start_date', [$from, $to])
                ->orWhereBetween('end_date', [$from, $to])
                ->orWhere(function ($q2) use ($from, $to) {
                    $q2->where('start_date', '<=', $from)
                        ->where('end_date', '>=', $to);
                });
            });
        } elseif (!empty($request->date_from)) {
            $query->where('end_date', '>=', $request->date_from);
        } elseif (!empty($request->date_to)) {
            $query->where('start_date', '<=', $request->date_to);
        }

        // Group & sort
        $query->groupBy('name')->orderBy('id', 'desc');

        // Paginate
        $data = $query->paginate(25);

        return view('reward.barcode.index', compact('data'));
    }


    public function create(Request $request)
    {
        // $allDistributors = Distributor::where('name', '!=', null)->groupBy('name')->orderBy('name')->with('states')->get();
        return view('reward.barcode.create');
    }

    public function store(Request $request)
    {
        //dd($request->all());
        $request->validate([
            "generate_number" => "required|numeric|min:0|not_in:0",
            "name" => "required|string|max:255",
            "amount" => "required|numeric|min:0|not_in:0",
            "max_time_of_use" => "required|integer",
            "max_time_one_can_use" => "required|integer",
            "start_date" => "required",
            "end_date" => "required",
        ]);

        $params = $request->except('_token');
                                
        function generateUniqueAlphaNumeric($length = 10) {
            $random_string = '';
            for ($i = 0; $i < $length; $i++) {
                $number = random_int(0, 36);
                $character = base_convert($number, 10, 36);
                $random_string .= $character;
            }
            return $random_string;
        }
        $noOfEntries = $request['generate_number'];
         // slug generate
         $slug = \Str::slug($request['name'], '-');
         $slugExistCount = RetailerBarcode::where('slug', $slug)->count();
         if ($slugExistCount > 0) $slug = $slug . '-' . ($slugExistCount + 1);

        for($i = 0; $i < $noOfEntries; $i++) {
            $storeData = new RetailerBarcode;
            $storeData->name = $request['name'];
            $storeData->slug = $slug;
            $storeData->note = 'Please note that this QR code is exclusively intended for authorized retailers through the Onn & Pynk App.';
            $storeData->code = strtoupper(generateUniqueAlphaNumeric(10));
            
            $storeData->amount = $request['amount'];
            $storeData->max_time_of_use = $request['max_time_of_use'];
            $storeData->max_time_one_can_use = $request['max_time_one_can_use'];
            $storeData->start_date = $request['start_date'];
            $storeData->end_date = $request['end_date'];
            $storeData->brand = $request['brand'];
            $storeData->is_print = 0;
            $storeData->save();
        }
        if ($storeData) {
            return redirect()->route('reward.retailer.barcode.index')->with('success', 'New Qrcode created');
        } else {
            return redirect()->route('reward.retailer.barcode.create')->withInput($request->all())->with('success', 'Something happened');
        }
    }

    public function show(Request $request, $slug)
    {
        $data = RetailerBarcode::where('slug', $slug)->first();
		if (!empty($request->keyword)) {
			$coupons = RetailerBarcode::where([['code', 'LIKE', '%' . $request->keyword . '%']])->get();
        } else {
        	$coupons = RetailerBarcode::where('slug', $slug)->get();
		}
        $usage = RetailerWalletTxn::where('barcode_id',$data->id)->with('users')->get();
        return view('reward.barcode.detail', compact('data','coupons','usage','request'));
    }
	
	public function useqrcode(Request $request, $slug)
    {
        $data = RetailerBarcode::where('slug', $slug)->where('no_of_usage','!=',0)->first();
        $coupons = RetailerBarcode::where('slug', $slug)->where('no_of_usage','!=',0)->get();
		if(!empty($data)){
        $usage = RetailerWalletTxn::where('barcode_id',$data->id)->with('users')->get();
		}
		else{
			$usage ='';}
        return view('reward.barcode.useqrcode', compact('data','coupons','usage'));
    }
	public function view(Request $request, $id)
    {
        $data = RetailerBarcode::where('id', $id)->first();
        
        $coupons = RetailerBarcode::where('id', $id)->get();
        $usage = RetailerWalletTxn::where('barcode_id',$data->id)->with('users')->get();
        return view('reward.barcode.view', compact('data','coupons','usage'));
    }
	public function edit(Request $request, $id)
    {
        $data = RetailerBarcode::findOrfail($id);
        $allDistributors = User::select('id','name')->where('type',7)->where('name', '!=', null)->where('status',1)->groupBy('name')->orderBy('name')->get();
        $state = State::where('status',1)->groupBy('name')->orderBy('name')->get();
        return view('reward.barcode.edit', compact('data','allDistributors','state'));
    }
    public function update(Request $request, $id)
    {
        // dd($request->all());

        $request->validate([
            "name" => "required|string|max:255",
            "amount" => "required|numeric|min:0|not_in:0",
            "max_time_of_use" => "required|integer",
            "max_time_one_can_use" => "required|integer",
            "start_date" => "required",
            "end_date" => "required",
        ]);

        $storeData = RetailerBarcode::findOrFail($id);
        // slug generate
        if ($request->name!=$storeData->name) {
            $slug = \Str::slug($request['name'], '-');
            $slugExistCount = RetailerBarcode::where('slug', $slug)->count();
            if ($slugExistCount > 0) $slug = $slug . '-' . ($slugExistCount + 1);
            $storeData->slug = $slug;
        }
        $storeData->name = $request['name'];
        $storeData->state_id = $request['state_id'];
        $storeData->amount = $request['amount'];
        $storeData->max_time_of_use = $request['max_time_of_use'];
        $storeData->max_time_one_can_use = $request['max_time_one_can_use'];
        $storeData->start_date = $request['start_date'];
        $storeData->end_date = $request['end_date'];
        $storeData->brand = $request['brand'];
        $storeData->save();

        if ($storeData) {
            return redirect()->route('reward.retailer.barcode.index')->with('success', 'Qrcode updated');
        } else {
            return redirect()->route('reward.retailer.barcode.view')->withInput($request->all())->with('success', 'Something happened');
        }
    }

    public function status(Request $request, $id)
    {
        $storeData = RetailerBarcode::findOrFail($id);

        $status = ($storeData->status == 1) ? 0 : 1;
        $storeData->status = $status;
        $storeData->save();

        if ($storeData) {
            return redirect()->back()->with('success', 'Qrcode updated');;
        } else {
            return redirect()->back()->withInput($request->all());
        }
    }

    public function csvExport(Request $request)
    {
        $coupon = RetailerBarcode::where('slug', $request->slug)->with('distributor','state')->first();
        
		if (!empty($request->keyword)) {
			$data = RetailerBarcode::where([['code', 'LIKE', '%' . $request->keyword . '%']])->get();
        } else {
        	$data = RetailerBarcode::where('slug', $request->slug)->get();
		}
        if (count($data) > 0) {
            $delimiter = ","; 
            $filename = $coupon->name.".csv"; 

            // Create a file pointer 
            $f = fopen('php://memory', 'w'); 

            // Set column headers 
            $fields = array('SR', 'CODE','NOTE'); 
            fputcsv($f, $fields, $delimiter); 

            $count = 1;

            foreach($data as $row) {
               // $datetime = date('j F, Y h:i A', strtotime($row['created_at']));

                $lineData = array(
                    $count,
                    $row['code'],
                    $row['note'],
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
		
}
