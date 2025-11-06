<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\State;
use App\Models\Area;
use App\Models\UserArea;
use App\Models\Team;
use App\Models\Store;
use App\Models\Distributor;
use App\Models\Notification;
use App\Models\Activity;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderDistributor;
use Auth;
use DB;
use Carbon\Carbon;
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
   public function index(Request $request)
{
    $data = (object)[];

    $user = auth()->user();

    // 🔹 Step 1: Detect which brand(s) the user has permission to view
    if ($user->can('onn daily dashboard') && $user->can('pynk dashboard')) {
        $brandPermissions = 'Both';
    } elseif ($user->can('onn daily dashboard')) {
        $brandPermissions = 'ONN';
    } elseif ($user->can('pynk dashboard')) {
        $brandPermissions = 'PYNK';
    } else {
        abort(403, 'Unauthorized access.');
    }

    // 🔹 Step 2: Determine brandCode
    // If both, allow switching from dropdown; else force user's allowed brand
    if ($brandPermissions === 'Both') {
        $brandCode = $request->brand ?? session('selected_brand', 1); // default ONN
        session(['selected_brand' => $brandCode]);
    } elseif ($brandPermissions === 'ONN') {
        $brandCode = 1;
    } elseif ($brandPermissions === 'PYNK') {
        $brandCode = 2;
    }

    // 🔹 Step 3: Proceed with your existing dashboard logic (filtered by $brandCode)
    $data->vp = Employee::where('type', 1)->where('is_deleted', 0)->where('brand', $brandCode)->get();
    $data->rsm = Employee::where('type', 2)->where('is_deleted', 0)->where('brand', $brandCode)->get();
    $data->asm = Employee::where('type', 3)->where('is_deleted', 0)->where('brand', $brandCode)->get();
    $data->ase = Employee::where('type', 4)->where('is_deleted', 0)->where('brand', $brandCode)->get();
    $data->distributor = Distributor::where('is_deleted', 0)->where('brand', $brandCode)->get();

    $data->store = Store::where('status', 1)->where('brand', $brandCode)->count();
    $data->allstore = Store::where('brand', $brandCode)->count();

    $data->primary = OrderDistributor::where('created_at', '>', date('Y-m-d'))
        ->where('brand', $brandCode)
        ->sum('final_amount');

    $data->secondary = OrderProduct::join('orders', 'orders.id', '=', 'order_products.order_id')
        ->where('order_products.created_at', '>=', date('Y-m-d'))
        ->where('orders.brand', $brandCode)
        ->sum('qty');

    // 🔹 Daily & Monthly Reports
    $dayStoreReport = Store::select(DB::raw("(COUNT(*)) as count"), DB::raw("DAYNAME(created_at) as dayname"))
        ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
        ->where('brand', $brandCode)
        ->groupBy('dayname')
        ->orderBy('created_at', 'ASC')
        ->get();

    $monthStoreReport = Store::select(DB::raw("(COUNT(*)) as count"), DB::raw("MONTHNAME(created_at) as monthname"))
        ->whereYear('created_at', date('Y'))
        ->where('brand', $brandCode)
        ->groupBy(DB::raw("MONTHNAME(created_at)"))
        ->orderBy('created_at', 'ASC')
        ->get();

    // 🔹 ASE Wise Report
    if (!empty($request->keyword)) {
        $userName = $request->keyword;
        $aseWiseReport = DB::select("
            SELECT u.id, u.name AS name, st.name AS state_name, COUNT(*) AS count
            FROM stores AS s
            INNER JOIN employees AS u ON FIND_IN_SET(u.id, s.user_id)
            INNER JOIN states AS st ON st.id = u.state
            WHERE u.name = ? AND s.brand = ?
            GROUP BY u.id ORDER BY count DESC
        ", [$userName, $brandCode]);
    } else {
        $aseWiseReport = DB::select("
            SELECT u.id, u.name AS name, st.name AS state_name, COUNT(*) AS count
            FROM stores AS s
            INNER JOIN employees AS u ON FIND_IN_SET(u.id, s.user_id)
            INNER JOIN states AS st ON st.id = u.state
            WHERE s.brand = ?
            GROUP BY u.id ORDER BY count DESC
        ", [$brandCode]);
    }

    // 🔹 State Wise Report
    $stateWiseReport = DB::select("
        SELECT s.name AS name, COUNT(*) AS count
        FROM stores AS st
        INNER JOIN states AS s ON FIND_IN_SET(s.id, st.state_id)
        WHERE st.brand = ?
        GROUP BY st.state_id ORDER BY s.name
    ", [$brandCode]);

    // 🔹 ASE active/inactive
    $userIds = Employee::where('type', 4)->pluck('id')->toArray();
    $activeASEreport = Activity::where('type', 'Visit Started')
        ->where('created_at', '>', date('Y-m-d'))
        ->whereIn('user_id', $userIds)
        ->pluck('user_id')
        ->toArray();

    $inactiveASE = Employee::where('type', 4)
        ->whereNotIn('id', $activeASEreport)
        ->get();

    // 🔹 Monthly Secondary Summary
    $data->monthly_secondary = OrderProduct::join('orders', 'orders.id', '=', 'order_products.order_id')
        ->selectRaw("
            DATE_FORMAT(order_products.created_at, '%Y-%m') as month_key,
            DATE_FORMAT(order_products.created_at, '%M') as month_name,
            DATE_FORMAT(order_products.created_at, '%Y') as year_name,
            SUM(order_products.qty) as total_qty
        ")
        ->where('orders.brand', $brandCode)
        ->where('order_products.created_at', '>=', Carbon::now()->subMonths(5)->startOfMonth())
        ->groupBy('month_key', 'month_name', 'year_name')
        ->orderBy('month_key', 'asc')
        ->get();
   
    return view('home', compact(
        'data',
        'dayStoreReport',
        'monthStoreReport',
        'aseWiseReport',
        'stateWiseReport',
        'inactiveASE',
        'brandPermissions',
        'brandCode',
        'request'
    ));
}




    public function filterReport(Request $request)
    {
        
        $month=$request->month;
        $year=$request->year;
        $monthYear = $request->get('month_year'); // e.g., "2025-04"
        $reportType = $request->get('report_type');
       // dd($reportType);
    $data = [];

    if ($month && $year && $reportType) {
        $startDate = $monthYear . '-01';
        //$endDate = date('Y-m-t', strtotime($startDate));
        $endDate = date('Y-m-t 23:59:59', strtotime($startDate));
       
        switch ($reportType) {
            
            case 'state':
                $data = DB::table('order_products')
                    ->select('states.name', DB::raw('SUM(order_products.qty) as total_sales'))->join('orders', 'orders.id', 'order_products.order_id')->join('stores', 'stores.id', 'orders.store_id')->join('states', 'states.id', 'stores.state_id')
                    ->whereBetween('order_products.created_at', [$startDate, $endDate])
                    ->groupBy('states.id')->orderByDesc('total_sales')
                    ->get();
                break;

            case 'product':
                $data = DB::table('order_products')
                    ->select('products.style_no', DB::raw('SUM(order_products.qty) as total_sales'))->join('products', 'products.id', 'order_products.product_id')->join('orders', 'orders.id', 'order_products.order_id')->join('stores', 'stores.id', 'orders.store_id')->join('states', 'states.id', 'stores.state_id')
                    ->whereBetween('order_products.created_at', [$startDate, $endDate])
                    ->groupBy('products.style_no')->orderByDesc('total_sales')
                    ->get();
                break;
            case 'ase':
                $data = DB::table('order_products')
                    ->select('employees.name','employees.status', DB::raw('SUM(order_products.qty) as total_sales'))->join('orders', 'orders.id', 'order_products.order_id')->join('stores', 'stores.id', 'orders.store_id')->join('employees', 'employees.id', 'orders.user_id')
                    ->whereBetween('order_products.created_at', [$startDate, $endDate])
                    ->groupBy('employees.name')->orderByDesc('total_sales')
                    ->get();
                break;
            case 'asm':
                    $asmUsers = Employee::where('type', 3)->get(); // Get all ASM users

                    $data = []; // Final array for ASM with total sales
                    
                    foreach ($asmUsers as $asm) {
                       
                        $uniqueAseList = [];
                    
                        // Get all ASE names under this ASM from retailer_list_of_occ table
                        $aseDetails = DB::table('teams')
                            ->whereRaw("FIND_IN_SET(?, asm_id)", [$asm->id])
                            ->pluck('ase_id');
                           
                        // Parse all ASEs from comma-separated format
                        foreach ($aseDetails as $aseString) {
                            $ases = explode(',', $aseString);
                            foreach ($ases as $ase) {
                                $trimmed = trim($ase);
                                if (!in_array($trimmed, $uniqueAseList)) {
                                    $uniqueAseList[] = $trimmed;
                                }
                            }
                        }
                        
                        
                    
                        // Get user_ids of those ASEs from users table
                        $aseUsers = Employee::whereIn('id', $uniqueAseList)->pluck('id');
                        
                        if ($aseUsers->isEmpty()) {
                            $totalSales = 0;
                        } else {
                            // Get total sales for these ASE user IDs
                            $totalSales = DB::table('order_products')
                                ->join('orders', 'orders.id', '=', 'order_products.order_id')
                                ->whereIn('orders.user_id', $aseUsers)
                                ->whereBetween('order_products.created_at', [$startDate, $endDate])
                                ->sum('order_products.qty');
                        }
                    
                        $data[] = [
                            'name' => $asm->name,
                            'total_sales' => $totalSales,
                        ];
                        
                        usort($data, function ($a, $b) {
                            return $b['total_sales'] <=> $a['total_sales'];
                        });
                    }

                  
                    
                

                break;
            case 'rsm':
                
		    $rsmUsers = Employee::where('type', 2)->get(); // Get all ASM users

                    $data = []; // Final array for ASM with total sales
                    
                    foreach ($rsmUsers as $rsm) {
                       
                        $uniqueAseList = [];
                    
                        // Get all ASE names under this ASM from retailer_list_of_occ table
                        $aseDetails = DB::table('teams')
                            ->whereRaw("FIND_IN_SET(?, rsm_id)", [$rsm->id])
                            ->pluck('ase_id');
                           
                        // Parse all ASEs from comma-separated format
                        foreach ($aseDetails as $aseString) {
                            $ases = explode(',', $aseString);
                            foreach ($ases as $ase) {
                                $trimmed = trim($ase);
                                if (!in_array($trimmed, $uniqueAseList)) {
                                    $uniqueAseList[] = $trimmed;
                                }
                            }
                        }
                        
                        
                    
                        // Get user_ids of those ASEs from users table
                        $aseUsers = Employee::whereIn('id', $uniqueAseList)->pluck('id');
                        
                        if ($aseUsers->isEmpty()) {
                            $totalSales = 0;
                        } else {
                            // Get total sales for these ASE user IDs
                            $totalSales = DB::table('order_products')
                                ->join('orders', 'orders.id', '=', 'order_products.order_id')
                                ->whereIn('orders.user_id', $aseUsers)
                                ->whereBetween('order_products.created_at', [$startDate, $endDate])
                                ->sum('order_products.qty');
                        }
                    
                        $data[] = [
                            'name' => $rsm->name,
                            'total_sales' => $totalSales,
                        ];
                        
                        usort($data, function ($a, $b) {
                            return $b['total_sales'] <=> $a['total_sales'];
                        });
                    }
                break;
            case 'vp':
               

		    $vpUsers = Employee::where('type', 1)->get(); // Get all ASM users

                    $data = []; // Final array for ASM with total sales
                    
                    foreach ($vpUsers as $vp) {
                       
                        $uniqueAseList = [];
                    
                        // Get all ASE names under this ASM from retailer_list_of_occ table
                        $aseDetails = DB::table('teams')
                            ->whereRaw("FIND_IN_SET(?, vp_id)", [$vp->id])
                            ->pluck('ase_id');
                           
                        // Parse all ASEs from comma-separated format
                        foreach ($aseDetails as $aseString) {
                            $ases = explode(',', $aseString);
                            foreach ($ases as $ase) {
                                $trimmed = trim($ase);
                                if (!in_array($trimmed, $uniqueAseList)) {
                                    $uniqueAseList[] = $trimmed;
                                }
                            }
                        }
                        
                        
                    
                        // Get user_ids of those ASEs from users table
                        $aseUsers = Employee::whereIn('id', $uniqueAseList)->pluck('id');
                        
                        if ($aseUsers->isEmpty()) {
                            $totalSales = 0;
                        } else {
                            // Get total sales for these ASE user IDs
                            $totalSales = DB::table('order_products')
                                ->join('orders', 'orders.id', '=', 'order_products.order_id')
                                ->whereIn('orders.user_id', $aseUsers)
                                ->whereBetween('order_products.created_at', [$startDate, $endDate])
                                ->sum('order_products.qty');
                        }
                    
                        $data[] = [
                            'name' => $vp->name,
                            'total_sales' => $totalSales,
                        ];
                        
                        usort($data, function ($a, $b) {
                            return $b['total_sales'] <=> $a['total_sales'];
                        });
                    }
                break;
                
            
               
            // Add similar cases for ase, asm, etc.
        }
        
        
    }

        return view('order.filter-data', compact('data', 'monthYear', 'reportType','month','year'));
        
    }
  
    
       
        
    
}
