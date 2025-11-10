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

    public function csvExportSlug(Request $request, $slug)
    {
        $data = RetailerBarcode::where('slug', $slug)->get()->toArray();

        if (count($data) > 0) {
            $delimiter = ","; 
            $filename = "luxcozi-reward-barcodes-".date('Y-m-d').".csv";  

            // Create a file pointer 
            $f = fopen('php://memory', 'w'); 

            // Set column headers 
            $fields = array('SR', 'CODE', 'BARCODE DETAILS', 'POINTS', 'START DATE', 'END DATE','MAX TIME USE','STATUS', 'DATETIME'); 
            fputcsv($f, $fields, $delimiter);  

            $count = 1;

            foreach($data as $row) {
                $datetime = date('j F, Y h:i A', strtotime($row['created_at']));

                $lineData = array(
                    $count,
                    $row['code'],
                    $row['name'],
                    $row['amount'],
                    $row['start_date'],
                    $row['end_date'],
                    $row['max_time_of_use'],
                    $row['status'] == 1 ? 'Active' : 'Inactive',
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
	
	public function qrcsvExport(Request $request)
	{
		 $from = $request->date_from ? $request->date_from : date('Y-m-01');
         $to = date('Y-m-d', strtotime(request()->input('date_to')))? date('Y-m-d', strtotime(request()->input('date_to'))) : '';
		if (!empty(isset($request->date_from) || isset($request->date_to) ||$request->keyword)) {
			 $from = $request->date_from ? $request->date_from : date('Y-m-01');
             $to = date('Y-m-d', strtotime(request()->input('date_to'). '+1 day'))? date('Y-m-d', strtotime(request()->input('date_to'). '+1 day')) : '';
			$keyword = $request->keyword;
			$data =  RetailerWalletTxn::select('retailer_barcodes.id','retailer_wallet_txns.user_id','retailer_wallet_txns.barcode_id','retailer_barcodes.name','retailer_barcodes.code','stores.store_name','stores.contact','stores.email','stores.address','stores.area','stores.city','stores.state','stores.pin','retailer_wallet_txns.amount','retailer_wallet_txns.created_at')->join('stores', 'stores.id', 'retailer_wallet_txns.user_id')
            ->join('retailer_barcodes', 'retailer_barcodes.id', 'retailer_wallet_txns.barcode_id')
			->whereBetween('retailer_wallet_txns.created_at', [$from, $to])->latest('retailer_wallet_txns.id')
            ->cursor();
            $users = $data->all();
			
        } else {
        	$data = RetailerWalletTxn::select('retailer_barcodes.id','retailer_wallet_txns.user_id','retailer_wallet_txns.barcode_id','retailer_barcodes.name','retailer_barcodes.code','stores.store_name','stores.contact','stores.email','stores.address','stores.area','stores.city','stores.state','stores.pin','retailer_wallet_txns.amount','retailer_wallet_txns.created_at')->join('stores', 'stores.id', 'retailer_wallet_txns.user_id')
            ->join('retailer_barcodes', 'retailer_barcodes.id', 'retailer_wallet_txns.barcode_id')
			->whereBetween('retailer_wallet_txns.created_at', [$from, $to])->latest('retailer_wallet_txns.id')
            ->cursor();
            $users = $data->all();
		}
        if (count($users) > 0) {
            $delimiter = ","; 
            $filename = "luxcozi-qrcode-scan-details-".$from.' to '.$to.".csv"; 

            // Create a file pointer 
            $f = fopen('php://memory', 'w'); 

            // Set column headers 
            $fields = array('SR', 'QRCODE TITLE','CODE','STORE NAME','STORE MOBILE','STORE EMAIL','STORE ADDRESS','POINTS','DATE'); 
            fputcsv($f, $fields, $delimiter); 

            $count = 1;

            foreach($users as $row) {
               $datetime = date('j F, Y h:i A', strtotime($row['created_at']));

                $lineData = array(
                    $count,
					$row['name'],
                    $row['code'],
					$row['name'],
					$row['contact'],
					$row['email'],
                    $row['address'].' ,'.$row['area'].' ,'.$row['state'].' ,'.$row['pin'],
					$row['amount'],
					$datetime,
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
	
// 	public function qrRedeem(Request $request)
// 	{
		
// 		if (!empty(isset($request->date_from) || isset($request->date_to) ||$request->distributor||$request->ase||$request->keyword)) {
			
// 			 $from = $request->date_from ? $request->date_from : date('Y-m-01');
//              $to = date('Y-m-d', strtotime(request()->input('date_to'). '+1 day'))? date('Y-m-d', strtotime(request()->input('date_to'). '+1 day')) : '';
// 			$keyword = $request->keyword;
// 			$distributor = $request->distributor;
// 			$ase = $request->ase;
// 			$query =  RetailerUserTxnHistory::select('stores.id as store_id','stores.user_id','retailer_user_txn_histories.description','stores.unique_code','stores.name','stores.contact','stores.email','stores.address','stores.area_id','stores.city','stores.state_id','retailer_user_txn_histories.amount','retailer_user_txn_histories.created_at')->join('stores', 'stores.unique_code', 'retailer_user_txn_histories.user_id')
			
//             ;
			
		       
          

			
//               $query->when($keyword, function($query) use ($keyword) {
//                     $query->where('stores.name','like' ,'%'.$keyword.'%')
//                     ->orWhere('stores.business_name', $keyword)
//                     ->orWhere('stores.owner_fname', $keyword)
//                     ->orWhere('stores.contact', $keyword)
//                     ->orWhere('stores.email', $keyword)
//                     ->orWhere('stores.whatsapp', $keyword)
//                     ->orWhere('stores.address', $keyword)
                   
//                     ->orWhere('stores.pin', $keyword)
//                     ->orWhere('stores.contact_person_fname', $keyword)
//                     ->orWhere('stores.contact_person_phone', $keyword)
//                     ->orWhere('stores.contact_person_whatsapp', $keyword)
// 					->orWhere('stores.unique_code', $keyword)
				    
//                     ->orWhere('stores.gst_no', $keyword);
//                 })->whereBetween('retailer_user_txn_histories.created_at', [$from, $to]);
// 			$data = $query->where('stores.user_id','!=','')->latest('retailer_user_txn_histories.id')->paginate(25);
			
//         } else {
			
//         	$data = RetailerUserTxnHistory::select('retailer_user_txn_histories.user_id','retailer_user_txn_histories.description','retailer_user_txn_histories.barcode_id','stores.id as store_id','stores.user_id','stores.name','stores.contact','stores.email','stores.address','stores.area_id','stores.city','stores.state_id','stores.pin','retailer_user_txn_histories.amount','retailer_user_txn_histories.created_at')->join('stores', 'stores.unique_code', 'retailer_user_txn_histories.user_id')
        
			
// 			->where('stores.user_id','!=','')->latest('retailer_user_txn_histories.id')
//             ->paginate(25);
// 		}
// 		//dd($data);
// 		//$allDistributors = User::select('id','name','employee_id','state')->where('name', '!=', null)->groupBy('name')->orderBy('name')->get();
// 		 return view('reward.barcode.redeem', compact('data','request'));
// 	}


public function qrRedeem(Request $request)
{
    // check if filters applied
    if ($request->filled(['date_from', 'date_to']) || $request->distributor || $request->ase || $request->keyword) {
        
        $from = $request->date_from ?? date('Y-m-01');
        $to   = $request->date_to 
                ? date('Y-m-d', strtotime($request->date_to . ' +1 day')) 
                : date('Y-m-d'); // default today

        $query = RetailerUserTxnHistory::select(
                    'stores.id as store_id',
                    'stores.user_id',
                    'retailer_user_txn_histories.description',
                    'stores.unique_code',
                    'stores.name',
                    'stores.contact',
                    'stores.email',
                    'stores.address',
                    'stores.area_id',
                    'stores.city',
                    'stores.state_id',
                    'retailer_user_txn_histories.amount',
                    'retailer_user_txn_histories.created_at'
                )
                ->join('stores', 'stores.unique_code', '=', 'retailer_user_txn_histories.user_id')
                ->whereBetween('retailer_user_txn_histories.created_at', [$from, $to]);

        // keyword search
        if ($request->keyword) {
            $keyword = $request->keyword;
            $query->where(function($q) use ($keyword) {
                $q->where('stores.name','like','%'.$keyword.'%')
                  ->orWhere('stores.business_name','like','%'.$keyword.'%')
                  ->orWhere('stores.owner_fname','like','%'.$keyword.'%')
                  ->orWhere('stores.contact','like','%'.$keyword.'%')
                  ->orWhere('stores.email','like','%'.$keyword.'%')
                  ->orWhere('stores.whatsapp','like','%'.$keyword.'%')
                  ->orWhere('stores.address','like','%'.$keyword.'%')
                  ->orWhere('stores.pin','like','%'.$keyword.'%')
                  ->orWhere('stores.contact_person_fname','like','%'.$keyword.'%')
                  ->orWhere('stores.contact_person_phone','like','%'.$keyword.'%')
                  ->orWhere('stores.contact_person_whatsapp','like','%'.$keyword.'%')
                  ->orWhere('stores.unique_code','like','%'.$keyword.'%')
                  ->orWhere('stores.gst_no','like','%'.$keyword.'%');
            });
        }

        // distributor filter
        if ($request->distributor) {
            $query->where('stores.distributor_id', $request->distributor);
        }

        // ase filter
        if ($request->ase) {
            $query->where('stores.ase_id', $request->ase);
        }

        $data = $query->where('stores.user_id', '!=', '')
                      ->latest('retailer_user_txn_histories.id')
                      ->paginate(25);

    } else {
        // default listing (no filters)
        $data = RetailerUserTxnHistory::select(
                        'retailer_user_txn_histories.user_id',
                        'retailer_user_txn_histories.description',
                        'retailer_user_txn_histories.barcode_id',
                        'stores.id as store_id',
                        'stores.user_id',
                        'stores.name',
                        'stores.contact',
                        'stores.email',
                        'stores.address',
                        'stores.area_id',
                        'stores.city',
                        'stores.state_id',
                        'stores.pin',
                        'retailer_user_txn_histories.amount',
                        'retailer_user_txn_histories.created_at'
                  )
                  ->join('stores', 'stores.unique_code', '=', 'retailer_user_txn_histories.user_id')
                  ->where('stores.user_id','!=','')
                  ->latest('retailer_user_txn_histories.id')
                  ->paginate(25);
    }

    return view('reward.barcode.redeem', compact('data','request'));
}


public function qrRedeemcsvExport(Request $request)
{
    // Date range filters
    $from = $request->date_from ?: '2022-01-01';
    $to   = $request->date_to 
            ? date('Y-m-d', strtotime($request->date_to . ' +1 day')) 
            : date('Y-m-d');

    $query = RetailerUserTxnHistory::select(
                'stores.id as store_id',
                'stores.user_id',
                'retailer_user_txn_histories.description',
                'stores.unique_code',
                'stores.name',
                'stores.contact',
                'stores.email',
                'stores.address',
                'stores.area_id',
                'stores.city',
                'stores.state_id',
                'stores.pin',
                'retailer_user_txn_histories.amount',
                'retailer_user_txn_histories.created_at'
            )
            ->join('stores', 'stores.unique_code', '=', 'retailer_user_txn_histories.user_id')
            ->whereBetween('retailer_user_txn_histories.created_at', [$from, $to]);

    // Apply keyword filter
    if ($request->keyword) {
        $keyword = $request->keyword;
        $query->where(function ($q) use ($keyword) {
            $q->where('stores.name', 'like', "%$keyword%")
              ->orWhere('stores.contact', 'like', "%$keyword%")
              ->orWhere('stores.unique_code', 'like', "%$keyword%");
        });
    }

    // CSV file headers
    $filename = "rupa-qrcode-scan-details-{$from}-to-{$to}.csv";
    header('Content-Type: text/csv');
    header("Content-Disposition: attachment; filename=$filename");

    $f = fopen('php://output', 'w');

    // Write headers
    $headers = [
        'SR',
        'STORE UNIQUE CODE',
        'STORE NAME',
        'STORE MOBILE',
        'STORE EMAIL',
        'STORE STATE',
        'STORE ADDRESS',
        'POINTS',
        'DATE',
        'REMARKS'
    ];
    fputcsv($f, $headers);

    $count = 1;

    $query->chunk(1000, function ($rows) use (&$count, $f) {
        foreach ($rows as $row) {
            $datetime = date('j F, Y h:i A', strtotime($row->created_at));

            // fetch related state and area
            $state = DB::table('states')->where('id', $row->state_id)->first();
            $area  = DB::table('areas')->where('id', $row->area_id)->first();

            // fix: build full address safely
            $fullAddress = trim(($row->address ?? 'NA') . ', ' 
                            . ($area->name ?? 'NA') . ', ' 
                            . ($state->name ?? 'NA') . ', ' 
                            . ($row->pin ?? 'NA'));

            $lineData = [
                $count++,
                $row->unique_code ?? 'NA',
                $row->name ?? 'NA',
                $row->contact ?? 'NA',
                $row->email ?? 'NA',
                $state->name ?? 'NA',
                $fullAddress,
                $row->amount ?? '0',
                $datetime,
                $row->description ?? 'NA',
            ];

            fputcsv($f, $lineData);
        }
    });

    fclose($f);
    exit;
}

	
	 //qrcode redeem history remove
     
      public function qrRedeemRemove(Request $request)
     {
		 //dd($request->all());
         if (!empty($request->file)) {
             $file = $request->file('file');
             $filename = $file->getClientOriginalName();
             $extension = $file->getClientOriginalExtension();
             $tempPath = $file->getRealPath();
             $fileSize = $file->getSize();
             $mimeType = $file->getMimeType();
 
             $valid_extension = array("csv");
             $maxFileSize = 50097152;
             if (in_array(strtolower($extension), $valid_extension)) {
                 if ($fileSize <= $maxFileSize) {
                     $location = 'public/uploads/csv';
                     $file->move($location, $filename);
                     // $filepath = public_path($location . "/" . $filename);
                     $filepath = $location . "/" . $filename;
 
                     // dd($filepath);
 
                     $file = fopen($filepath, "r");
                     $importData_arr = array();
                     $i = 0;
                     while (($filedata = fgetcsv($file, 10000, ",")) !== FALSE) {
                         $num = count($filedata);
                         // Skip first row
                         if ($i == 0) {
                             $i++;
                             continue;
                         }
                         for ($c = 0; $c < $num; $c++) {
                             $importData_arr[$i][] = $filedata[$c];
                         }
                         $i++;
                     }
                     fclose($file);
                     $successCount = 0;
                        $userId='';
                     foreach ($importData_arr as $importData) {
                        $count = $total = 0;
                        $stateData = '';
                        $user=Store::where('contact',$importData[0])->first();
                        if(!empty($user)){
                            $userId =$user->id;
                        $qrTrans=RetailerWalletTxn::where('user_id',$userId)->where('barcode',$importData[1])->first();
                        if(!empty($qrTrans)){
                        $user=Store::findOrFail($userId);
						$user->wallet -= $qrTrans->amount ;
						$user->save();
                        
                            $qrTrans->delete();
                        }
                        $walletHistory = RetailerUserTxnHistory::where('user_id', $userId)->where('barcode',$importData[1])->first();
                        if(!empty($walletHistory)){
                            $walletHistory->delete();
                        }
                        $retailerQr=RetailerBarcode::where('code',$importData[1])->first();
						if(!empty($retailerQr)){
						    $retailerQr->no_of_usage=0;
						    $retailerQr->save();
						}
                        }						
                              
                    
                   
                        
                     }
                     return redirect()->back()->with('success', 'File Uploaded.');
                 } else {
                     return redirect()->back()->with('failure', 'File too large. File must be less than 50MB.');
                 }
             } else {
                 return redirect()->back()->with('failure', 'Invalid File Extension. supported extensions are ' . implode(', ', $valid_extension));
             }
         } else {
             return redirect()->back()->with('failure', 'No file found.');
         }
 
         return redirect()->back();
     }
	
	
	
	public function qrRedeemHistory(Request $request,$id)
	{
		
	
			
        	$data = RetailerBarcode::where('id',$id)
            ->first();
		
		//dd($data);
		$allDistributors = User::select('id','name','employee_id','state')->where('type', '=', 7)->where('name', '!=', null)->groupBy('name')->orderBy('name')->get();
		 return view('reward.barcode.history', compact('data','allDistributors','request'));
	}
	
		
}
