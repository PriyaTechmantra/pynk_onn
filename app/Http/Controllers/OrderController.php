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
    $distributor = $request->distributor ?? '';
    
    // ✅ Base query
    $query = OrderProductDistributor::select(
            'order_product_distributors.id as order_product_id',
            'order_product_distributors.qty',
            'order_product_distributors.color_id',
            'order_product_distributors.size_id',
            'products.id as product_id',
            'products.name as product_name',
            'products.style_no',
            'order_distributors.brand', // ✅ ensure brand comes from orders table
            'order_distributors.id as order_id',
            'order_distributors.order_no',
            'order_distributors.status',
            'order_distributors.created_at',
            'distributors.state_id',
            'distributors.area_id',
            'employees.name as user_name',
            'colors.name as color_name',
            'sizes.name as size_name',
            'distributors.name as distributor_name',
            'states.name as state_name',
            'areas.name as area_name',
             // ✅ team hierarchy names
            'ase_emp.name as ase_name',
        )
        ->join('products', 'products.id', '=', 'order_product_distributors.product_id')
        ->join('colors', 'colors.id', '=', 'order_product_distributors.color_id')
        ->join('sizes', 'sizes.id', '=', 'order_product_distributors.size_id')
        ->join('order_distributors', 'order_distributors.id', '=', 'order_product_distributors.order_id')
        
        ->join('employees', 'employees.id', '=', 'order_distributors.user_id')
        ->leftJoin('distributors', 'distributors.id', '=', 'order_distributors.distributor_id')
        ->join('teams', 'distributors.id', '=', 'teams.distributor_id')
        ->leftJoin('states', 'states.id', '=', 'distributors.state_id')
        ->leftJoin('areas', 'areas.id', '=', 'distributors.area_id')
        // ✅ join employees table again for hierarchy
        ->leftJoin('employees as ase_emp', 'ase_emp.id', '=', 'teams.ase_id')
        ->whereBetween('order_distributors.created_at', [$from, $to]);

    // ✅ Brand filter logic
    if ($request->filled('brand')) {
        $query->where(function ($q) use ($request) {
            if ($request->brand == 3) {
                // “Both” selected → show ONN, PYNK, and Both
                $q->whereIn('order_distributors.brand', [1, 2, 3]);
            } else {
                // Single brand selected → include that + both
                $q->where('order_distributors.brand', $request->brand)
                    ->orWhere('order_distributors.brand', 3);
            }
        });
    } else {
        // If brand not selected — use user permissions
        if (!empty($userBrands)) {
            $query->where(function ($q) use ($userBrands) {
                if (in_array(3, $userBrands)) {
                    $q->whereIn('order_distributors.brand', [1, 2, 3]);
                } else {
                    $q->whereIn('order_distributors.brand', array_merge($userBrands, [3]));
                }
            });
        }
    }

    // ✅ Dynamic filters (kept as-is but cleaned)
    $query->when($ase, fn($q) => $q->where('teams.ase_id', $ase));
    $query->when($distributor, fn($q) => $q->where('order_distributors.distributor_id', $distributor));
    $query->when($state, fn($q) => $q->where('distributors.state_id', $state));
    $query->when($area, fn($q) => $q->where('distributors.area_id', $area));
    $query->when($orderNo, fn($q) => $q->where('order_distributors.order_no', 'like', "%$orderNo%"));
    $query->when($product, fn($q) => $q->where('products.id', $product));

    // ✅ Fetch paginated results
    //$data->all_orders = $query->latest('orders.id')->get();
    //if ($request->has('download_csv')) {
        $data->all_orders = $query->groupby('order_product_distributors.id')->latest('order_distributors.id')->paginate(25);
        
        $allASEs = Employee::where('type',4)->where('name', '!=', null)->where('status',1)->where('is_deleted',0)->orderBy('name')->get();
        $allDistributors = Distributor::where('name', '!=', null)->where('status',1)->where('is_deleted',0)->orderBy('name')->get();
        $state = State::where('status',1)->where('is_deleted',0)->orderBy('name')->get();
        $product = Product::where('status', 1)->where('is_deleted',0)->orderBy('style_no')->get();
        //dd($data->products[1]->style_no);
        return view('order.primary-order-report', compact('data','allASEs','state','request','allDistributors','product'));
    }



    public function primaryOrderReportExport(Request $request)
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
    $distributor = $request->distributor ?? '';
    
    // ✅ Base query
    $query = OrderProductDistributor::select(
            'order_product_distributors.id as order_product_id',
            'order_product_distributors.qty',
            'order_product_distributors.color_id',
            'order_product_distributors.size_id',
            'products.id as product_id',
            'products.name as product_name',
            'products.style_no',
            'order_distributors.brand', // ✅ ensure brand comes from orders table
            'order_distributors.id as order_id',
            'order_distributors.order_no',
            'order_distributors.status',
            'order_distributors.created_at',
            'distributors.state_id',
            'distributors.area_id',
            'employees.name as user_name',
            'colors.name as color_name',
            'sizes.name as size_name',
            'distributors.name as distributor_name',
            'states.name as state_name',
            'areas.name as area_name',
             // ✅ team hierarchy names
            'ase_emp.name as ase_name',
        )
        ->join('products', 'products.id', '=', 'order_product_distributors.product_id')
        ->join('colors', 'colors.id', '=', 'order_product_distributors.color_id')
        ->join('sizes', 'sizes.id', '=', 'order_product_distributors.size_id')
        ->join('order_distributors', 'order_distributors.id', '=', 'order_product_distributors.order_id')
        
        ->join('employees', 'employees.id', '=', 'order_distributors.user_id')
        ->leftJoin('distributors', 'distributors.id', '=', 'order_distributors.distributor_id')
        ->join('teams', 'distributors.id', '=', 'teams.distributor_id')
        ->leftJoin('states', 'states.id', '=', 'distributors.state_id')
        ->leftJoin('areas', 'areas.id', '=', 'distributors.area_id')
        // ✅ join employees table again for hierarchy
        ->leftJoin('employees as ase_emp', 'ase_emp.id', '=', 'teams.ase_id')
        ->whereBetween('order_distributors.created_at', [$from, $to]);

    // ✅ Brand filter logic
    if ($request->filled('brand')) {
        $query->where(function ($q) use ($request) {
            if ($request->brand == 3) {
                // “Both” selected → show ONN, PYNK, and Both
                $q->whereIn('order_distributors.brand', [1, 2, 3]);
            } else {
                // Single brand selected → include that + both
                $q->where('order_distributors.brand', $request->brand)
                    ->orWhere('order_distributors.brand', 3);
            }
        });
    } else {
        // If brand not selected — use user permissions
        if (!empty($userBrands)) {
            $query->where(function ($q) use ($userBrands) {
                if (in_array(3, $userBrands)) {
                    $q->whereIn('order_distributors.brand', [1, 2, 3]);
                } else {
                    $q->whereIn('order_distributors.brand', array_merge($userBrands, [3]));
                }
            });
        }
    }

    // ✅ Dynamic filters (kept as-is but cleaned)
    $query->when($ase, fn($q) => $q->where('teams.ase_id', $ase));
    $query->when($distributor, fn($q) => $q->where('order_distributors.distributor_id', $distributor));
    $query->when($state, fn($q) => $q->where('distributors.state_id', $state));
    $query->when($area, fn($q) => $q->where('distributors.area_id', $area));
    $query->when($orderNo, fn($q) => $q->where('order_distributors.order_no', 'like', "%$orderNo%"));
    $query->when($product, fn($q) => $q->where('products.id', $product));

    // ✅ Fetch paginated results
    //$data->all_orders = $query->latest('orders.id')->get();
    //if ($request->has('download_csv')) {
        $orders = $query->groupby('order_product_distributors.id')->latest('order_distributors.id')->get();

        // ✅ Define CSV headers
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="primary_order_report.csv"',
        ];

        // ✅ Define CSV columns
        $columns = [
             'SR',
            'Order No',
            'Order Date',
            'Brand',
            'State',
            'Area',
            'ASE',
            'Distributor',
            'Product Name',
            'Style No',
            'Color',
            'Size',
            'Quantity',
            
            'Created By',
        ];

        // ✅ Stream CSV response
        $callback = function () use ($orders, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            $count = 1;
            foreach ($orders as $row) {
                $brandName = match($row->brand) {
                    1 => 'ONN',
                    2 => 'PYNK',
                    3 => 'Both',
                    default => '-',
                };
                fputcsv($file, [
                    $count,
                    $row->order_no,
                    date('d-m-Y', strtotime($row->created_at)),
                    $brandName,
                    $row->state_name,
                    $row->area_name,
                    $row->ase_name,
                    $row->distributor_name,
                    $row->product_name,
                    $row->style_no,
                    $row->color_name,
                    $row->size_name,
                    $row->qty,
                    
                    $row->user_name,
                ]);
                $count++;
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    //}
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
            'employees.name as user_name',
             'collections.name as collection_name',
            'categories.name as category_name',
            'colors.name as color_name',
            'sizes.name as size_name',
            'distributors.name as distributor_name',
            'states.name as state_name',
            'areas.name as area_name',
             // ✅ team hierarchy names
            'ase_emp.name as ase_name',
            'asm_emp.name as asm_name',
            'rsm_emp.name as rsm_name',
            'vp_emp.name as vp_name'
        )
        ->join('products', 'products.id', '=', 'order_products.product_id')
        ->join('collections', 'collections.id', '=', 'products.collection_id')
        ->join('categories', 'categories.id', '=', 'products.cat_id')
        ->join('colors', 'colors.id', '=', 'order_products.color_id')
        ->join('sizes', 'sizes.id', '=', 'order_products.size_id')
        ->join('orders', 'orders.id', '=', 'order_products.order_id')
        ->join('stores', 'stores.id', '=', 'orders.store_id')
        ->join('teams', 'stores.id', '=', 'teams.store_id')
        ->join('employees', 'employees.id', '=', 'orders.user_id')
        ->leftJoin('distributors', 'distributors.id', '=', 'teams.distributor_id')
        
        ->leftJoin('states', 'states.id', '=', 'stores.state_id')
        ->leftJoin('areas', 'areas.id', '=', 'stores.area_id')
        // ✅ join employees table again for hierarchy
        ->leftJoin('employees as ase_emp', 'ase_emp.id', '=', 'teams.ase_id')
        ->leftJoin('employees as asm_emp', 'asm_emp.id', '=', 'teams.asm_id')
        ->leftJoin('employees as rsm_emp', 'rsm_emp.id', '=', 'teams.rsm_id')
        ->leftJoin('employees as vp_emp', 'vp_emp.id', '=', 'teams.vp_id')
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
    $query->when($orderNo, fn($q) => $q->where('orders.order_no', '=', $orderNo));
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


public function secondaryOrderReportExport(Request $request)
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
             'stores.contact as store_contact',
            'stores.state_id',
            'stores.area_id',
            'employees.name as user_name',
            'colors.name as color_name',
            'sizes.name as size_name',
            'distributors.name as distributor_name',
            'states.name as state_name',
            'areas.name as area_name',
             // ✅ team hierarchy names
            'ase_emp.name as ase_name',
            'asm_emp.name as asm_name',
            'rsm_emp.name as rsm_name',
            'vp_emp.name as vp_name'
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
        // ✅ join employees table again for hierarchy
        ->leftJoin('employees as ase_emp', 'ase_emp.id', '=', 'teams.ase_id')
        ->leftJoin('employees as asm_emp', 'asm_emp.id', '=', 'teams.asm_id')
        ->leftJoin('employees as rsm_emp', 'rsm_emp.id', '=', 'teams.rsm_id')
        ->leftJoin('employees as vp_emp', 'vp_emp.id', '=', 'teams.vp_id')
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
    $query->when($orderNo, fn($q) => $q->where('orders.order_no', '=', $orderNo));
    $query->when($product, fn($q) => $q->where('products.id', $product));
    $query->when($store_id, fn($q) => $q->where('orders.store_id', $store_id));

    // ✅ Fetch paginated results
    //$data->all_orders = $query->latest('orders.id')->get();
    //if ($request->has('download_csv')) {
        $orders = $query->latest('orders.id')->get();

        // ✅ Define CSV headers
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="secondary_order_report.csv"',
        ];

        // ✅ Define CSV columns
        $columns = [
             'SR',
            'Order No',
            'Order Date',
            'Brand',
            'Store Name',
            'Store Mobile',
            'State',
            'Area',
            'ASE',
            'ASM',
            'RSM',
            'VP',
            'Distributor',
            'Product Name',
            'Style No',
            'Color',
            'Size',
            'Quantity',
            
            'Created By',
        ];

        // ✅ Stream CSV response
        $callback = function () use ($orders, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            $count = 1;
            foreach ($orders as $row) {
                $brandName = match($row->brand) {
                    1 => 'ONN',
                    2 => 'PYNK',
                    3 => 'Both',
                    default => '-',
                };
                fputcsv($file, [
                    $count,
                    $row->order_no,
                    date('d-m-Y', strtotime($row->created_at)),
                    $brandName,
                    $row->store_name,
                    $row->store_contact,
                    $row->state_name,
                    $row->area_name,
                    $row->ase_name,
                    $row->asm_name,
                    $row->rsm_name,
                    $row->vp_name,
                    $row->distributor_name,
                    $row->product_name,
                    $row->style_no,
                    $row->color_name,
                    $row->size_name,
                    $row->qty,
                    
                    $row->user_name,
                ]);
                $count++;
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    //}
}




    public function pdfExport(Request $request, $id)
    {
        $data = Order::findOrfail($id);
        return view('order.secondary-order-pdf', compact('data'));
    }

    //csv download for individual order
    public function individualcsvExport(Request $request, $id)
    {
        $orderDetails = Order::findOrfail($id);
        $data = orderProductsUpdatedMatrix($orderDetails->orderProducts);
        $childData = orderProductsUpdatedMatrixChild($orderDetails->orderProducts);

        if (count($data) > 0 || count($childData) > 0) {
            $delimiter = ",";
            $filename = "secondary-order-detail-".$orderDetails->order_no."-".date('Y-m-d').".csv";

            // Create a file pointer
            $f = fopen('php://memory', 'w');

            // Set column headers
            $fields = array('Name of Quality Shape & Unit', '75', '80', '85', '90', '95', '100', '105', '110', '115','120', 'Total');
            $childFields = array('Name of Quality Shape & Unit', '35', '40', '45', '50', '55', '60', '65', '70', '73','75','','Total');

            $count = 1;

            if (count($data) > 0) {
                fputcsv($f, $fields, $delimiter);
                foreach($data as $row) {
					 
                     $row1 = $row['product_name']."\n".$row['product_style_no']."\n".$row['color'];

                    $lineData = array(
                        $row1,
                        $row['75'] ? $row['75'] : '',
                        $row['80'] ? $row['80'] : '',
                        $row['85'] ? $row['85'] : '',
                        $row['90'] ? $row['90'] : '',
                        $row['95'] ? $row['95'] : '',
                        $row['100'] ? $row['100'] : '',
                        $row['105'] ? $row['105'] : '',
                        $row['110'] ? $row['110'] : '',
                        $row['115'] ? $row['115'] : '',
                        $row['120'] ? $row['120'] : '',
                        $row['total']
                    );
                    fputcsv($f, $lineData, $delimiter);
                    $count++;
                }
            }

            if (count($childData) > 0) {
                fputcsv($f, $childFields, $delimiter);
                foreach($childData as $row) {
					 
                    $row2 = $row['product_name']."\n".$row['product_style_no']."\n".$row['color'];

                    $lineData = array(
                        $row2,
                        $row['35'] ? $row['35'] : '',
                        $row['40'] ? $row['40'] : '',
                        $row['45'] ? $row['45'] : '',
                        $row['50'] ? $row['50'] : '',
                        $row['55'] ? $row['55'] : '',
                        $row['60'] ? $row['60'] : '',
                        $row['65'] ? $row['65'] : '',
                        $row['70'] ? $row['70'] : '',
						$row['73'] ? $row['73'] : '',
                        $row['75'] ? $row['75'] : '',
						'',
                        $row['total']
                    );

                    fputcsv($f, $lineData, $delimiter);
                    $count++;
                }
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
