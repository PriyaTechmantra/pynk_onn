<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RetailerBarcode;
use App\Models\RetailerWalletTxn;
use App\Models\CouponUsage;
use App\Models\User;
use App\Models\Distributor;
use App\Models\RetailerOrder;
use App\Models\RewardOrderProduct;
use App\Models\State;
use App\Models\RetailerUserTxnHistory;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
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

        $data = $query->groupBy('name')->orderBy('id', 'desc')->paginate(25);

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
          // Get the last serial number and increment for each new entry
            $lastSerial = RetailerBarcode::max('serial_number');
        
            if (!$lastSerial) {
                $lastSerial = 111; // Start from 111 if no serial numbers exist
            } else {
                $lastSerial = (int)$lastSerial;
            }

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
            $storeData->is_print = 1;
            // if(Auth::guard('admin')->user()->email=='testprinter@gmail.com')
			// {
			// 	 $storeData->is_print = 1;
			// }else{
			// 	$storeData->is_print = 0;
			// }

            $serialNumber = $lastSerial++; // Increment serial number for each entry
            if ($serialNumber <= 999999) {
                $formattedSerialNumber = str_pad($serialNumber, 6, '0', STR_PAD_LEFT);
            } else {
                $formattedSerialNumber = str_pad($serialNumber, 7, '0', STR_PAD_LEFT);
            }
            $storeData->serial_number = $formattedSerialNumber;
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
        return view('reward.barcode.edit', compact('data'));
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
        $coupon = RetailerBarcode::where('slug', $request->slug)->first();
        
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

    public function retailerwiseReport(Request $request)
    {
        
    $from = $request->date_from ? $request->date_from : date('Y-m-01');
    $to = $request->date_to ? $request->date_to : date('Y-m-d');
    $storeId = $request->store;
    $store = Store::find($storeId);

    // Calculate opening balance (total credit - total debit before the date range)
    $openingBalance = RetailerWalletTxn::where('user_id', $storeId)
                        ->where('type', 1)
                        ->whereDate('created_at', '<', $from)
                        ->sum('amount')
                     - RetailerOrder::where('user_id', $storeId)
                        ->where('admin_status', '!=', 0)
                        ->whereDate('created_at', '<', $from)
                        ->sum('final_amount');

    $ledgerData = [];
    $currentDate = $from;

    // Loop through each day in the range
    while (strtotime($currentDate) <= strtotime($to)) {
        // Fetch daily credit and debit
        $dailyCredit = RetailerWalletTxn::where('user_id', $storeId)
            ->where('type', 1)
            ->whereDate('created_at', $currentDate)
            ->sum('amount');

        $dailyDebit = RetailerOrder::where('user_id', $storeId)->where('admin_status', '!=', 0)
           
            ->whereDate('created_at', $currentDate)
            ->sum('final_amount');
            $dailyDebitOrders = RetailerOrder::where('user_id', $storeId)
            ->where('admin_status', '!=', 0)
            
            ->whereDate('created_at', $currentDate)
            ->pluck('id');
             $productNames = RewardOrderProduct::whereIn('order_id', $dailyDebitOrders)
                ->pluck('product_name')
                ->toArray();

        // Calculate available balance
        $availableBalance = $openingBalance + $dailyCredit - $dailyDebit;
        if ($dailyDebit > 0 && $dailyCredit > 0) {
            // Both debit and credit are present
            $remarks = 'Qr Scan, Gift Redeem (' . implode(', ', $productNames) . ')';
        } elseif ($dailyDebit > 0) {
            // Only debit is present
            $remarks = 'Gift Redeem (' . implode(', ', $productNames) . ')';
        } elseif ($dailyCredit > 0) {
            // Only credit is present
            $remarks = 'Qr Scan';
        }
        
        if ($dailyDebit > 0 || $dailyCredit > 0) {
        // Add to ledger data
        $ledgerData[] = [
            'date' => date('d-m-Y', strtotime($currentDate)),
            'unique_code' =>$store->unique_code,
            'remarks'=>$remarks,
            'opening_balance' => $openingBalance,
            'debit' => $dailyDebit,
            'credit' => $dailyCredit,
            'available_balance' => $availableBalance,
        ];
        }
        // Update opening balance for the next day
        $openingBalance = $availableBalance;

        // Move to the next day
        $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
    }
        $store = Cache::remember('active_stores', 3600, function () {
            return Store::where('status', 1)
                ->orderBy('name')
                
                ->get();
        });
        
    
        return view('reward.barcode.retailer-report',compact('request','store','ledgerData'));
    }

    public function fetchStores(Request $request)
    {
        $search = $request->input('search');

        // Query to fetch stores matching the search term
        $stores = Store::where('status', 1)
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('unique_code', 'like', "%{$search}%")
                    ->orWhere('contact', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->select('id', 'name', 'unique_code')
            ->limit(20) // Limit the results to avoid heavy queries
            ->get();

        return response()->json($stores);
    }
    
    public function retailerReportcsvExport(Request $request)
    {
        // Set default date range if not provided
        $from = $request->date_from ? $request->date_from : date('Y-m-01');
        $to = $request->date_to ? date('Y-m-d', strtotime($request->date_to . ' +1 day')) : date('Y-m-d', strtotime('+1 day'));
        
        // Fetch all stores
        $stores = Store::all();

        // Prepare store-wise data
        $storeData = [];
        
        foreach ($stores as $store) {
            // Fetch transactions grouped by store and calculate points
            
                
            $pointsEarned = RetailerWalletTxn::select( DB::raw('SUM(retailer_wallet_txns.amount) as points_earned'))->where('user_id', $store->id)->where('type',1)->whereBetween('retailer_wallet_txns.created_at', [$from, $to])->get();
            //dd($pointsEarned);
            $pointsRedeemed = RetailerOrder::select(DB::raw('SUM(retailer_orders.final_amount) as points_redeemed'))->where('user_id', $store->id)->where('admin_status', '!=', 0)->whereBetween('retailer_orders.created_at', [$from, $to])->get();
            $availablePoints = Store::where('id', $store->id)
                        
                        ->first();
            // Prepare data row for CSV
            $storeData[] = [
                'unique_code' => $store->unique_code,
                'name' => $store->name,
                'contact' => $store->contact,
                'email' => $store->email,
                'state' => $store->state,
                'address' => $store->address,
                'points_earned' => $pointsEarned ? $pointsEarned[0]->points_earned : 0,
                'points_redeemed' => $pointsRedeemed ? $pointsRedeemed[0]->points_redeemed : 0,
                'available_points' => ($pointsEarned[0]->points_earned - $pointsRedeemed[0]->points_redeemed ) ?? 0,
            ];
        }

        // Check if there is data to export
        if (!empty($storeData)) {
            $delimiter = ",";
            $filename = "store-wise-report-" . date('Y-m-d') . ".csv";

            // Create a file pointer
            $f = fopen('php://memory', 'w');

            // Set column headers
            $fields = ['SR', 'STORE UNIQUE CODE', 'STORE NAME', 'STORE MOBILE', 'STORE EMAIL', 'STORE STATE', 'STORE ADDRESS', 'POINTS EARNED', 'POINTS REDEMPTION', 'AVAILABLE POINTS'];
            fputcsv($f, $fields, $delimiter);

            // Add data to CSV
            $count = 1;
            foreach ($storeData as $row) {
                $lineData = array_merge([$count], array_values($row));
                fputcsv($f, $lineData, $delimiter);
                $count++;
            }

            // Move back to the beginning of the file
            fseek($f, 0);

            // Set headers for download
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '";');

            // Output all data on the file pointer
            fpassthru($f);
            exit;
        } else {
            return redirect()->back()->with('error', 'No data found for the selected criteria.');
        }
    }
    
    public function retailerProductReportcsvExport(Request $request)
    {
        // Validate inputs
        $from = $request->date_from ? $request->date_from : date('Y-m-01');
        $to = $request->date_to ? $request->date_to : date('Y-m-d');
        $storeId = $request->store;
        $store = Store::find($storeId);

        

        // Calculate opening balance (total credit - total debit before the date range)
        $openingBalance = RetailerWalletTxn::where('user_id', $storeId)
                            ->where('type', 1)
                            ->whereDate('created_at', '<', $from)
                            ->sum('amount')
                        - RetailerOrder::where('user_id', $storeId)
                            ->where('admin_status', '!=', 0)
                            ->whereDate('created_at', '<', $from)
                            ->sum('final_amount');

        $ledgerData = [];
        $currentDate = $from;

        // Loop through each day in the range
        while (strtotime($currentDate) <= strtotime($to)) {
            // Fetch daily credit and debit
            $dailyCredit = RetailerWalletTxn::where('user_id', $storeId)
                ->where('type', 1)
                ->whereDate('created_at', $currentDate)
                ->sum('amount');

            $dailyDebit = RetailerOrder::where('user_id', $storeId)->where('admin_status', '!=', 0)
            
                ->whereDate('created_at', $currentDate)
                ->sum('final_amount');
                $dailyDebitOrders = RetailerOrder::where('user_id', $storeId)
                ->where('admin_status', '!=', 0)
                
                ->whereDate('created_at', $currentDate)
                ->pluck('id');
                $productNames = RewardOrderProduct::whereIn('order_id', $dailyDebitOrders)
                    ->pluck('product_name')
                    ->toArray();

            // Calculate available balance
            $availableBalance = $openingBalance + $dailyCredit - $dailyDebit;
            if ($dailyDebit > 0 && $dailyCredit > 0) {
                // Both debit and credit are present
                $remarks = 'Qr Scan, Gift Redeem (' . implode(', ', $productNames) . ')';
            } elseif ($dailyDebit > 0) {
                // Only debit is present
                $remarks = 'Gift Redeem (' . implode(', ', $productNames) . ')';
            } elseif ($dailyCredit > 0) {
                // Only credit is present
                $remarks = 'Qr Scan';
            }
            
            if ($dailyDebit > 0 || $dailyCredit > 0) {
                // Add to ledger data
                $ledgerData[] = [
                    'date' => date('d-m-Y', strtotime($currentDate)),
                    'unique_code' => $store->unique_code,
                    'remarks' => $remarks,
                    'opening_balance' => $openingBalance,
                
                    'credit' => $dailyCredit,
                    'debit' => $dailyDebit,
                    'available_balance' => $availableBalance,
                ];
            }
            // Update opening balance for the next day
            $openingBalance = $availableBalance;

            // Move to the next day
            $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
        }

        // Check if there is data to export
        if (!empty($ledgerData)) {
            $delimiter = ",";
            $filename = $store->name . "_" . $store->contact . "-ledger-report-" . date('Y-m-d') . ".csv";

            // Create a file pointer
            $f = fopen('php://memory', 'w');

            // Set column headers
            $headers = [
                ['STORE NAME', $store->name],
                ['CONTACT NO', $store->contact],
                ['STORE STATE',$store->state],
                ['LEDGER DATE RANGE', $from, $to],
                ['DATE', 'RETAILER UNIQUE CODE', 'PARTICULARS', 'OPENING BALANCE', 'CREDIT', 'DEBIT', 'AVAILABLE BALANCE']
            ];

            // Write headers to the file
            foreach ($headers as $header) {
                fputcsv($f, $header, $delimiter);
            }

            // Add data to CSV
            foreach ($ledgerData as $row) {
                fputcsv($f, $row, $delimiter);
            }

            // Move back to the beginning of the file
            fseek($f, 0);

            // Set headers for CSV download
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '";');
            header('Pragma: no-cache');
            header('Expires: 0');

            // Output the CSV file
            fpassthru($f);
            exit;
        } else {
            return redirect()->back()->with('error', 'No transactions found for the selected criteria.');
        }
    }

    public function retailerscanReport(Request $request)
    {
        $month = $request->month ?? date('Y-m');
        $keyword = $request->keyword ?? null;

        // Base query: stores joined with teams
        $query = Store::select(
                'stores.id',
                'stores.unique_code',
                'stores.created_at',
                'stores.name',
                'stores.user_id',
                'stores.state_id',
                'stores.area_id',
                'stores.city',
                'stores.pin',
                'stores.address',
                'stores.email',
                'stores.contact',
                'stores.bussiness_name',
                'stores.status',
                'stores.wallet',
                'teams.distributor_id'
            )->leftJoin('teams', 'teams.store_id', '=', 'stores.id')
            ->where('stores.is_deleted', 0);

        //  Filter by month (based on store created_at)
        if ($request->filled('month')) {
            $startOfMonth = date('Y-m-01 00:00:00', strtotime($month));
            $endOfMonth   = date('Y-m-t 23:59:59', strtotime($month));
            $query->whereBetween('stores.created_at', [$startOfMonth, $endOfMonth]);
        }

        //  Keyword filter
        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('stores.name', 'like', "%$keyword%")
                ->orWhere('stores.contact', 'like', "%$keyword%")
                ->orWhere('stores.unique_code', 'like', "%$keyword%")
                ->orWhere('stores.bussiness_name', 'like', "%$keyword%");
            });
        }

        // Fetch paginated data
        $data = $query->orderByDesc('stores.id')->paginate(25);

        // Pass to view
        return view('reward.barcode.scan-report', compact('data', 'request', 'month'));
    }
		
}
