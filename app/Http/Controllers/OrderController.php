<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrderProduct;
use App\Models\Order;
use App\Models\OrderDistributor;
use App\Models\OrderProductDistributor;
use App\Models\Employee;
use App\Models\Distributor;
use App\Models\State;
use App\Models\Store;
use App\Models\Product;
use DB;
use Auth;
class OrderController extends Controller
{
     function __construct()
    {
         $this->middleware('permission:view primary order|view secondary order|view primary order report|view secondary order report', ['only' => ['index','show']]);
         
        
    }
    //primary order report product wise
    public function primaryOrderReport(Request $request)
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
        $data = (object) [];
        $from =  date('Y-m-01');
        $to =  date('Y-m-d', strtotime('+1 day'));
        if(isset($request->date_from) || isset($request->date_to) || isset($request->orderNo)||isset($request->ase)||isset($request->distributor)||isset($request->state)||isset($request->product)||isset($request->area)) 
        {
            $from = $request->date_from ? $request->date_from : date('Y-m-01');
            $to = date('Y-m-d', strtotime(request()->input('date_to'). '+1 day'))? date('Y-m-d', strtotime(request()->input('date_to'). '+1 day')) : '';
            $orderNo = $request->orderNo ? $request->orderNo : '';
            $product = $request->product ?? '';
            $state = $request->state ?? '';
            $area = $request->area ?? '';
            $ase = $request->ase ?? '';
			$distributor = $request->distributor ?? '';
            // all order products
            $query1 = OrderProductDistributor::select('order_distributors.brand','order_product_distributors.id AS id','products.style_no AS product_style_no','products.name AS product_name','order_product_distributors.color_id AS color_id','order_product_distributors.size_id AS size_id','order_product_distributors.qty AS qty','order_distributors.order_no AS order_no','retailer_list_of_occ.state AS state','retailer_list_of_occ.area AS area','order_distributors.fname AS fname','order_distributors.lname AS lname','distributors.name AS distributor_name','order_distributors.created_at AS created_at','order_product_distributors.status AS status')->join('products', 'products.id', 'order_product_distributors.product_id')
            ->join('order_distributors', 'order_distributors.id', 'order_product_distributors.order_id')->join('teams', 'teams.distributor_id', 'order_distributors.distributor_id')->join('distributors', 'distributors.id', 'order_distributors.distributor_id')->whereBetween('order_distributors.created_at', [$from, $to])->where('order_distributors.status', 1);
            $query1->when($ase, function($query1) use ($ase) {
                $query1->join('employees', 'employees.id', 'order_distributors.user_id')->where('employees.id', $ase);
            });
            $query1->when($product, function($query1) use ($product) {
                $query1->where('order_product_distributors.product_id', $product);
            });
            $query1->when($state, function($query1) use ($state) {
                $query1->where('teams.state_id', $state);
            });
            $query1->when($area, function($query1) use ($area) {
                $query1->where('teams.area_id', $area);
            });
			$query1->when($distributor, function($query1) use ($distributor) {
                $query1->where('order_distributors.distributor_id', $distributor);
            });
            $query1->when($orderNo, function($query1) use ($orderNo) {
                $query1->Where('order_distributors.order_no', 'like', '%' . $orderNo . '%');
            })->whereBetween('order_distributors.created_at', [$from, $to]);

            $data->all_orders = $query1->groupby('order_product_distributors.id')->latest('order_distributors.id')
            ->paginate(25);
            // dd($data->all_orders);
        }else{
            $data->all_orders = OrderProductDistributor::select('order_distributors.brand','order_product_distributors.id AS id','products.style_no AS product_style_no','products.name AS product_name','order_product_distributors.color_id AS color_id','order_product_distributors.size_id AS size_id','order_product_distributors.qty AS qty','order_distributors.order_no AS order_no','teams.state_id AS state_id','teams.area_id AS area_id','order_distributors.fname AS fname','order_distributors.lname AS lname','distributors.name AS distributor_name','order_distributors.created_at AS created_at','order_product_distributors.status AS status')->join('products', 'products.id', 'order_product_distributors.product_id')
            ->join('order_distributors', 'order_distributors.id', 'order_product_distributors.order_id')->join('teams', 'teams.distributor_id', 'order_distributors.distributor_id')->join('distributors', 'distributors.id', 'order_distributors.distributor_id')->whereBetween('order_distributors.created_at', [$from, $to])->where('order_distributors.status', 1)->groupby('order_product_distributors.id')->latest('order_distributors.id')->paginate(25);
            //dd($data->all_orders[1]);
        }
        $allASEs = Employee::where('type',4)->where('name', '!=', null)->where('status',1)->where('is_deleted',0)->orderBy('name')->get();
        $allDistributors = Distributor::where('name', '!=', null)->where('status',1)->where('is_deleted',0)->orderBy('name')->get();
        $state = State::where('status',1)->where('is_deleted',0)->orderBy('name')->get();
        $product = Product::where('status', 1)->where('is_deleted',0)->orderBy('style_no')->get();
        //dd($data->products[1]->style_no);
        return view('order.primary-order-report', compact('data','allASEs','state','request','allDistributors','product'));
    }







//   public function secondaryOrderReport(Request $request)
// {
//         $user = auth()->user();
//         $userBrands = DB::table('user_permission_categories')
//                 ->where('user_id', Auth::id())
//                 ->pluck('brand')
//                 ->toArray();
        
//             $brandsToShow = [];

//             if (in_array(3, $userBrands) || (in_array(1, $userBrands) && in_array(2, $userBrands))) {
//                 // Both brands access
//                 $brandsToShow = [1, 2, 3];
//             } elseif (in_array(1, $userBrands)) {
//                 $brandsToShow = [1];
//             } elseif (in_array(2, $userBrands)) {
//                 $brandsToShow = [2];
//             }
//     $data = (object) [];
//     $from = $request->date_from ?? date('Y-m-01');
//     $to = $request->date_to 
//         ? date('Y-m-d', strtotime($request->date_to . ' +1 day'))
//         : date('Y-m-d');
//     $brandFilter = $request->brand ?? '';
//     $orderNo = $request->orderNo ? $request->orderNo : ''; 
//     $product = $request->product ?? ''; 
//     $state = $request->state ?? ''; 
//     $area = $request->area ?? ''; 
//     $ase = $request->ase ?? ''; 
//     $asm = $request->asm ?? ''; 
//     $rsm = $request->rsm ?? ''; 
//     $vp = $request->vp ?? ''; 
//     $distributor = $request->distributor ?? ''; 
//     $store_id = $request->store_id ? $request->store_id : '';
//     // Base query (joins done once)
//     $query = OrderProduct::select(
//             'order_products.id as order_product_id',
//             'order_products.qty',
//             'order_products.color_id',
//             'order_products.size_id',
//             'products.id as product_id',
//             'products.name as product_name',
//             'products.style_no',
//             'products.brand',
//             'orders.id as order_id',
//             'orders.order_no',
//             'orders.status',
//             'orders.created_at',
//             'stores.name as store_name',
//             'stores.state_id',
//             'stores.area_id',
//             'colors.name as color_name',
//             'sizes.name as size_name',
//             'distributors.name as distributor_name',
//             'states.name as state_name',
//             'areas.name as area_name'
//         )
//         ->join('products', 'products.id', '=', 'order_products.product_id')
//         ->join('colors', 'colors.id', '=', 'order_products.color_id')
//         ->join('sizes', 'sizes.id', '=', 'order_products.size_id')
//         ->join('orders', 'orders.id', '=', 'order_products.order_id')
//         ->join('stores', 'stores.id', '=', 'orders.store_id')
//         ->join('teams', 'stores.id', '=', 'teams.store_id')
//         ->leftJoin('distributors', 'distributors.id', '=', 'teams.distributor_id')
//         ->leftJoin('states', 'states.id', '=', 'stores.state_id')
//         ->leftJoin('areas', 'areas.id', '=', 'stores.area_id')
       
//         ->whereBetween('orders.created_at', [$from, $to]);
//         if ($request->filled('brand')) {
//             $query->where(function ($q) use ($request) {
//                 if ($request->brand == 3) {
//                     // “Both” selected → show ONN (1), PYNK (2), and Both (3)
//                     $q->whereIn('orders.brand', [1, 2, 3]);
//                 } else {
//                     // single brand selected → include that + both
//                     $q->where('orders.brand', $request->brand)
//                     ->orWhere('orders.brand', 3);
//                 }
//             });
//         } else {
//             // if brand not selected — show according to user permission
//             $userBrandPermissions = DB::table('user_permission_categories')
//                 ->where('user_id', $user->id)
//                 ->pluck('brand')
//                 ->toArray();

//             if (!empty($userBrandPermissions)) {
//                 $query->where(function ($q) use ($userBrandPermissions) {
//                     if (in_array(3, $userBrandPermissions)) {
//                         // user has both brand permission
//                         $q->whereIn('orders.brand', [1, 2, 3]);
//                     } else {
//                         // user has limited brand(s)
//                         $q->whereIn('orders.brand', array_merge($userBrandPermissions, [3]));
//                     }
//                 });
//             }
//         }
//     // Apply filters dynamically
//     $query->when($request->ase, fn($q, $ase) =>
//         $q->where('teams.ase_id', $ase)
//     );

//     $query->when($request->asm, fn($q, $asm) =>
//         $q->where('teams.asm_id', $asm)
//     );

//     $query->when($request->rsm, fn($q, $rsm) =>
//         $q->where('teams.rsm_id', $rsm)
//     );

//     $query->when($request->vp, fn($q, $vp) =>
//         $q->where('teams.vp_id', $vp)
//     );

//     $query->when($request->distributor, fn($q, $distributor) =>
//         $q->where('teams.distributor_id', $distributor)
//     );

//     $query->when($request->state, fn($q, $state) =>
//         $q->where('stores.state_id', $state)
//     );

//     $query->when($request->area, fn($q, $area) =>
//         $q->where('stores.area_id', $area)
//     );

//     $query->when($request->orderNo, fn($q, $orderNo) =>
//         $q->where('orders.order_no', 'like', "%$orderNo%")
//     );

//     $query->when($request->product, fn($q, $product) =>
//         $q->where('products.id', $product)
//     );

//     $query->when($request->store_id, fn($q, $store_id) =>
//         $q->where('orders.store_id', $store_id)
//     );

//     // Final result with pagination
//     $data->all_orders = $query->latest('orders.id')->paginate(50);
//     dd($data->all_orders);
//     // Supporting data
//     $allASEs = Employee::where('type', 4)->whereIn('brand',$brandsToShow)->where('status', 1)->where('is_deleted', 0)->orderBy('name')->get();
//     $allASMs = Employee::where('type', 3)->whereIn('brand',$brandsToShow)->where('status', 1)->where('is_deleted', 0)->orderBy('name')->get();
//     $allRSMs = Employee::where('type', 2)->whereIn('brand',$brandsToShow)->where('status', 1)->where('is_deleted', 0)->orderBy('name')->get();
//     $allVPs  = Employee::where('type', 1)->whereIn('brand',$brandsToShow)->where('status', 1)->where('is_deleted', 0)->orderBy('name')->get();
//     $allDistributors = Distributor::where('status', 1)->whereIn('brand',$brandsToShow)->where('is_deleted', 0)->orderBy('name')->get();
//     $allStores = Store::where('status', 1)->whereIn('brand',$brandsToShow)->where('is_deleted', 0)->orderBy('name')->get();
//     $state = State::where('status', 1)->where('is_deleted', 0)->orderBy('name')->get();
//     $product = Product::where('status', 1)->whereIn('brand',$brandsToShow)->orderBy('style_no')->get();

//     return view('order.secondary-order-report', compact(
//         'data', 'product', 'allASEs', 'state', 'request',
//         'allStores', 'allASMs', 'allRSMs', 'allVPs', 'allDistributors'
//     ));
// }


public function secondaryOrderReport(Request $request)
{
    $user = auth()->user();

    // ✅ Fetch user brand permissions
    $userBrands = DB::table('user_permission_categories')
        ->where('user_id', $user->id)
        ->pluck('brand')
        ->toArray();

    // ✅ Determine which brands user can access
    $brandsToShow = [];
    if (in_array(3, $userBrands) || (in_array(1, $userBrands) && in_array(2, $userBrands))) {
        $brandsToShow = [1, 2, 3]; // Both access
    } elseif (in_array(1, $userBrands)) {
        $brandsToShow = [1];
    } elseif (in_array(2, $userBrands)) {
        $brandsToShow = [2];
    }

    $data = (object) [];

    // ✅ Date range (make sure “to” includes the entire day)
    $from = $request->date_from ?? date('Y-m-01');
    $to = $request->date_to
        ? date('Y-m-d', strtotime($request->date_to . ' +1 day'))
        : date('Y-m-d', strtotime('+1 day'));

    // ✅ Filters
    $brandFilter = $request->brand ?? '';
    $orderNo = $request->orderNo ?? '';
    $product = $request->product ?? '';
    $state = $request->state ?? '';
    $area = $request->area ?? '';
    $ase = $request->ase ?? '';
    $asm = $request->asm ?? '';
    $rsm = $request->rsm ?? '';
    $vp = $request->vp ?? '';
    $distributor = $request->distributor ?? '';
    $store_id = $request->store_id ?? '';

    // ✅ Base query
    $query = OrderProduct::select(
            'order_products.id as order_product_id',
            'order_products.qty',
            'order_products.color_id',
            'order_products.size_id',
            'products.id as product_id',
            'products.name as product_name',
            'products.style_no',
            'orders.brand', // ✅ ensure brand comes from orders table
            'orders.id as order_id',
            'orders.order_no',
            'orders.status',
            'orders.created_at',
            'stores.name as store_name',
            'stores.state_id',
            'stores.area_id',
            'employees.name as ase_name',
            'colors.name as color_name',
            'sizes.name as size_name',
            'distributors.name as distributor_name',
            'states.name as state_name',
            'areas.name as area_name'
        )
        ->join('products', 'products.id', '=', 'order_products.product_id')
        ->join('colors', 'colors.id', '=', 'order_products.color_id')
        ->join('sizes', 'sizes.id', '=', 'order_products.size_id')
        ->join('orders', 'orders.id', '=', 'order_products.order_id')
        ->join('stores', 'stores.id', '=', 'orders.store_id')
        ->join('teams', 'stores.id', '=', 'teams.store_id')
        ->join('employees', 'employees.id', '=', 'orders.user_id')
        ->leftJoin('distributors', 'distributors.id', '=', 'teams.distributor_id')
        ->leftJoin('states', 'states.id', '=', 'stores.state_id')
        ->leftJoin('areas', 'areas.id', '=', 'stores.area_id')
        ->whereBetween('orders.created_at', [$from, $to]);

    // ✅ Brand filter logic
    if ($request->filled('brand')) {
        $query->where(function ($q) use ($request) {
            if ($request->brand == 3) {
                // “Both” selected → show ONN, PYNK, and Both
                $q->whereIn('orders.brand', [1, 2, 3]);
            } else {
                // Single brand selected → include that + both
                $q->where('orders.brand', $request->brand)
                    ->orWhere('orders.brand', 3);
            }
        });
    } else {
        // If brand not selected — use user permissions
        if (!empty($userBrands)) {
            $query->where(function ($q) use ($userBrands) {
                if (in_array(3, $userBrands)) {
                    $q->whereIn('orders.brand', [1, 2, 3]);
                } else {
                    $q->whereIn('orders.brand', array_merge($userBrands, [3]));
                }
            });
        }
    }

    // ✅ Dynamic filters (kept as-is but cleaned)
    $query->when($ase, fn($q) => $q->where('teams.ase_id', $ase));
    $query->when($asm, fn($q) => $q->where('teams.asm_id', $asm));
    $query->when($rsm, fn($q) => $q->where('teams.rsm_id', $rsm));
    $query->when($vp, fn($q) => $q->where('teams.vp_id', $vp));
    $query->when($distributor, fn($q) => $q->where('teams.distributor_id', $distributor));
    $query->when($state, fn($q) => $q->where('stores.state_id', $state));
    $query->when($area, fn($q) => $q->where('stores.area_id', $area));
    $query->when($orderNo, fn($q) => $q->where('orders.order_no', 'like', "%$orderNo%"));
    $query->when($product, fn($q) => $q->where('products.id', $product));
    $query->when($store_id, fn($q) => $q->where('orders.store_id', $store_id));

    // ✅ Fetch paginated results
    $data->all_orders = $query->latest('orders.id')->paginate(50);
   //dd($query->toRawSql());
    // ✅ Debug check (remove later)
    // dd($data->all_orders);

    // ✅ Supporting data
    $allASEs = Employee::where('type', 4)
        ->whereIn('brand', $brandsToShow)
        ->where('status', 1)
        ->where('is_deleted', 0)
        ->orderBy('name')
        ->get();

    $allASMs = Employee::where('type', 3)
        ->whereIn('brand', $brandsToShow)
        ->where('status', 1)
        ->where('is_deleted', 0)
        ->orderBy('name')
        ->get();

    $allRSMs = Employee::where('type', 2)
        ->whereIn('brand', $brandsToShow)
        ->where('status', 1)
        ->where('is_deleted', 0)
        ->orderBy('name')
        ->get();

    $allVPs = Employee::where('type', 1)
        ->whereIn('brand', $brandsToShow)
        ->where('status', 1)
        ->where('is_deleted', 0)
        ->orderBy('name')
        ->get();

    $allDistributors = Distributor::where('status', 1)
        ->whereIn('brand', $brandsToShow)
        ->where('is_deleted', 0)
        ->orderBy('name')
        ->get();

    $allStores = Store::where('status', 1)
        ->whereIn('brand', $brandsToShow)
        ->where('is_deleted', 0)
        ->orderBy('name')
        ->get();

    $state = State::where('status', 1)
        ->where('is_deleted', 0)
        ->orderBy('name')
        ->get();

    $product = Product::where('status', 1)->where('is_deleted', 0)
        ->whereIn('brand', $brandsToShow)
        ->orderBy('style_no')
        ->get();

    return view('order.secondary-order-report', compact(
        'data',
        'product',
        'allASEs',
        'state',
        'request',
        'allStores',
        'allASMs',
        'allRSMs',
        'allVPs',
        'allDistributors'
    ));
}


}
