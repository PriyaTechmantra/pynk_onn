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
			$data->vp = Employee::where('type',1)->where('is_deleted',0)->get();
			$data->rsm = Employee::where('type',2)->where('is_deleted',0)->get();
			$data->asm =  Employee::where('type',3)->where('is_deleted',0)->get();
			$data->ase =  Employee::where('type',4)->where('is_deleted',0)->get();
			$data->distributor = Distributor::where('is_deleted',0)->get();
			$data->store = Store::where('status', 1)->count();
            $data->allstore = Store::count();
			 $data->primary = OrderDistributor::where('created_at', '>', date('Y-m-d'))->latest('id', 'desc')->sum('final_amount');
			//$data->primary = DB::select("SELECT SUM(final_amount) AS final_amount FROM `orders_distributors` WHERE date(created_at) = '".date('Y-m-d')."'");
			 $data->secondary = OrderProduct::where('created_at', '>=', date('Y-m-d'))->sum('qty');
			//$data->secondary = DB::select("SELECT SUM(qty) AS qty FROM `order_products` WHERE date(created_at) = '".date('Y-m-d')."'");
			 $dayStoreReport= Store::select(DB::raw("(COUNT(*)) as count"),DB::raw("DAYNAME(created_at) as dayname"))
				->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
				->whereYear('created_at', date('Y'))
				->groupBy('dayname')
				->orderBy('created_at', 'ASC')
				->get();
			 $monthStoreReport= Store::select(DB::raw("(COUNT(*)) as count"),DB::raw("MONTHNAME(created_at) as monthname"))
			   // ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
				->whereYear('created_at', date('Y'))
				->groupBy('MONTHNAME')
				->orderBy('created_at', 'ASC')
				->get();
			if(!empty($request->keyword)){
				$userName=$request->keyword;
				$user_id=Employee::select('id')->where('name',$userName)->get();
				$app_id_arr=array();

					foreach($user_id as $lang)
					{
						array_push($app_id_arr, $lang->id);

					}
				
			 $aseWiseReport = \DB::select('SELECT u.id,u.name AS name,st.name AS state_name,COUNT(*) AS count FROM `stores` AS s
					INNER JOIN employees AS u ON FIND_IN_SET(u.id,s.user_id)
                    INNER JOIN states AS st ON st.id=u.state
					WHERE u.name = "'.$userName.'"
					 GROUP BY u.id ORDER BY count desc');
			
				
			}else{
				
					
				  $aseWiseReport = \DB::select('SELECT u.id,u.name AS name,st.name AS state_name,COUNT(*) AS count FROM `stores` AS s
					INNER JOIN employees AS u ON FIND_IN_SET(u.id,s.user_id)
                    INNER JOIN states AS st ON st.id=u.state
					GROUP BY u.id ORDER BY count desc');
					
			}
			//dd($aseWiseReport);
			$stateWiseReport = DB::select('SELECT s.name AS name , COUNT(*) AS count FROM `stores` AS st INNER JOIN states AS s ON FIND_IN_SET(s.id,st.state_id)GROUP BY st.state_id ORDER BY s.name' );
			//$stateWiseReport = DB::select("SELECT state AS name,COUNT(*) AS total_count,SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) AS active_count FROM `stores` GROUP BY state ORDER BY state");
			$user=Employee::where('type',4)->get()->pluck('id')->toArray();

					$activeASEreport=Activity::where('type','Visit Started')->where('created_at', '>', date('Y-m-d'))->whereIn('user_id',$user)->pluck('user_id')->toArray();
						//dd($inactiveASEreport);
					$inactiveASE=Employee::where('type',4)->whereNotIn('id',$activeASEreport)
			->get();
			
			$data->monthly_secondary = OrderProduct::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month_key, DATE_FORMAT(created_at, '%M') as month_name,DATE_FORMAT(created_at, '%Y') as year_name, SUM(qty) as total_qty")
                ->where('created_at', '>=', Carbon::now()->subMonths(5)->startOfMonth())
                ->groupBy('month_key', 'month_name','year_name')
                ->orderBy('month_key', 'asc')
                ->get();
		
		//dd($inactiveASE);
           return view('home', compact('data','dayStoreReport','monthStoreReport','aseWiseReport','stateWiseReport','inactiveASE','request'));
    }


/*public function index(Request $request)
{
    $user = auth()->user();
    $userBrands = DB::table('user_permission_categories')
                ->where('user_id', Auth::id())
                ->pluck('brand')
                ->toArray();
        
            $brandsToShow = [];

            if (in_array(3, $userBrands) || (in_array(1, $userBrands) && in_array(2, $userBrands))) {
                // Both brands access
                $brandsToShow = [1, 2, 3];
            } elseif (in_array(1, $userBrands)) {
                $brandsToShow = [1];
            } elseif (in_array(2, $userBrands)) {
                $brandsToShow = [2];
            }
            /// ✅ Brand map
        $brandMap = [
            1 => 'ONN',
            2 => 'PYNK',
            3 => 'Both',
        ];

        // ✅ Handle multiple brands dynamically
        $brandNames = array_map(function ($id) use ($brandMap) {
            return $brandMap[$id] ?? 'Unknown';
        }, $brandsToShow);

        // Optional: Convert to a readable string (e.g. "ONN, PYNK")
        $brandNameString = implode(', ', $brandNames);
    // 🔹 Employee hierarchy counts (brand-wise)
    $dashboardData = [
        'brand' => $brandNameString,
        'vp' => Employee::where('type', 1)->where('is_deleted', 0)->where('brand', $brandsToShow)->count(),
        'rsm' => Employee::where('type', 2)->where('is_deleted', 0)->where('brand', $brandsToShow)->count(),
        'asm' => Employee::where('type', 3)->where('is_deleted', 0)->where('brand', $brandsToShow)->count(),
        'ase' => Employee::where('type', 4)->where('is_deleted', 0)->where('brand', $brandsToShow)->count(),
        'distributors' => Distributor::where('is_deleted', 0)->where('brand', $brandsToShow)->count(),
        'stores' => Store::where('status', 1)->where('brand', $brandsToShow)->count(),
        'primaryOrders' => OrderDistributor::where('brand',$brandsToShow)
            ->whereDate('created_at', today())->sum('final_amount'),
        'secondaryOrders' => OrderProduct::join('orders','orders.id', '=', 'order_products.order_id')->where('orders.brand',$brandsToShow)
            ->whereDate('order_products.created_at', today())->sum('qty'),
    ];

    // 🔹 Weekly store report (daywise)
    $dayStoreReport = Store::selectRaw('COUNT(*) as count, DAYNAME(created_at) as dayname')
        ->where('brand', $brandsToShow)
        ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
        ->groupBy('dayname')
        ->orderBy('created_at')
        ->get();

    // 🔹 Monthly store report
    $monthStoreReport = Store::selectRaw('COUNT(*) as count, MONTHNAME(created_at) as monthname')
        ->where('brand', $brandsToShow)
        ->whereYear('created_at', now()->year)
        ->groupBy('monthname')
        ->orderByRaw('MIN(created_at)')
        ->get();

    // 🔹 ASE-wise report (brand-wise + optional keyword)
    if ($request->filled('keyword')) {
    $aseWiseReport = DB::select("
        SELECT 
            u.id, 
            u.name, 
            st.name AS state_name, 
            COUNT(*) AS count
        FROM stores s
        JOIN employees u ON FIND_IN_SET(u.id, s.user_id)
        JOIN states st ON st.id = u.state
        WHERE u.name = ? 
          AND s.brand IN (" . implode(',', $brandsToShow) . ")
        GROUP BY u.id 
        ORDER BY count DESC
    ", [$request->keyword]);
} else {
    $aseWiseReport = DB::select("
        SELECT 
            u.id, 
            u.name, 
            st.name AS state_name, 
            COUNT(*) AS count
        FROM stores s
        JOIN employees u ON FIND_IN_SET(u.id, s.user_id)
        JOIN states st ON st.id = u.state
        WHERE s.brand IN (" . implode(',', $brandsToShow) . ")
        GROUP BY u.id 
        ORDER BY count DESC
    ");
}


    // 🔹 State-wise report
   $stateWiseReport = DB::table('stores as st')
    ->join('states as s', DB::raw('FIND_IN_SET(s.id, st.state_id)'), '>', DB::raw('0'))
    ->select('s.name', DB::raw('COUNT(*) as count'))
    ->whereIn('st.brand', $brandsToShow)
    ->groupBy('st.state_id', 's.name')
    ->orderBy('s.name')
    ->get();

    // 🔹 Active / inactive ASEs
    $aseIds = Employee::where('type', 4)->where('brand', $brandsToShow)->pluck('id');
    $activeASEIds = Activity::where('type', 'Visit Started')
        ->whereDate('created_at', today())
        ->whereIn('user_id', $aseIds)
        ->pluck('user_id');
    $inactiveASE = Employee::where('type', 4)
        ->where('brand', $brandsToShow)
        ->whereNotIn('id', $activeASEIds)
        ->get();

    // 🔹 Monthly secondary (for last 6 months)
    $monthlySecondary = OrderProduct::join('orders','orders.id', '=', 'order_products.order_id')->selectRaw("
            DATE_FORMAT(orders.created_at, '%M') as month_name, SUM(order_products.qty) as total_qty
        ")
        ->where('orders.brand', $brandsToShow)
        ->where('orders.created_at', '>=', now()->subMonths(5)->startOfMonth())
        ->groupBy('month_name')
        ->orderByRaw('MIN(orders.created_at)')
        ->get();

    // 🔹 Chart data arrays
    $dashboardData['charts'] = [
        'daily' => [
            'labels' => $dayStoreReport->pluck('dayname'),
            'data' => $dayStoreReport->pluck('count'),
        ],
        'monthly' => [
            'labels' => $monthStoreReport->pluck('monthname'),
            'data' => $monthStoreReport->pluck('count'),
        ],
        'stateWise' => [
            'labels' => collect($stateWiseReport)->pluck('name'),
            'data' => collect($stateWiseReport)->pluck('count'),
        ],
    ];

    // 🔹 Return to Blade
    return view('home', compact(
        'dashboardData',
        'dayStoreReport',
        'monthStoreReport',
        'aseWiseReport',
        'stateWiseReport',
        'inactiveASE',
        'brandNameString',
        'request'
       
    ));
}*/
  
    
       
        
    
}
