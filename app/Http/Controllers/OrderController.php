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
class OrderController extends Controller
{
     function __construct()
    {
         $this->middleware('permission:view primary order|view secondary order|view primary order report|view secondary order report', ['only' => ['index','show']]);
         
        
    }
    //primary order report product wise
    public function primaryOrderReport(Request $request)
    {
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







    public function secondaryOrderReport(Request $request)
    { 
		//dd('hi');
        $data = (object) [];
        $from =  date('Y-m-01');
        $to =  date('Y-m-d');
        if(isset($request->date_from) || isset($request->date_to) || isset($request->orderNo)||isset($request->ase)||isset($request->asm)||isset($request->rsm)||isset($request->vp)||isset($request->distributor)||isset($request->state)||isset($request->product)||isset($request->area)) 
		{
            $from = $request->date_from ? $request->date_from : date('Y-m-01');
           $to = date('Y-m-d', strtotime(request()->input('date_to'). '+1 day'))? date('Y-m-d', strtotime(request()->input('date_to'). '+1 day')) : '';
            $orderNo = $request->orderNo ? $request->orderNo : '';
            $product = $request->product ?? '';
            $state = $request->state ?? '';
            $area = $request->area ?? '';
            $ase = $request->ase ?? '';
            $asm = $request->asm ?? '';
            $rsm = $request->rsm ?? '';
            $vp = $request->vp ?? '';
            $distributor = $request->distributor ?? '';
 			$store_id = $request->store_id ? $request->store_id : '';
            // all order products
            $query1 = OrderProduct::join('products', 'products.id', 'order_products.product_id')
            ->join('orders', 'orders.id', 'order_products.order_id')
            ->where('orders.status', 1);
            $query1->when($ase, function($query1) use ($ase) {
                $query1->join('employees', 'employees.id', 'orders.user_id')->where('employees.id', $ase);
            });
            
            $query1->when($asm, function($query1) use ($asm) {
                $query1->join('stores', 'stores.id', 'orders.store_id')->join('teams', 'stores.id', 'teams.store_id')->join('employees', 'employees.id', 'teams.asm_id')->where('employees.id', $asm);
            });
            
            $query1->when($rsm, function($query1) use ($rsm) {
                $query1->join('stores', 'stores.id', 'orders.store_id')->join('teams', 'stores.id', 'teams.store_id')->join('employees', 'employees.id', 'teams.rsm_id')->where('employees.id', $rsm);
            });
            
            $query1->when($vp, function($query1) use ($vp) {
                $query1->join('stores', 'stores.id', 'orders.store_id')->join('teams', 'stores.id', 'teams.store_id')->join('employees', 'employees.id', 'teams.vp_id')->where('employees.id', $vp);
            });
            
            $query1->when($distributor, function($query1) use ($distributor) {
                $query1->join('stores', 'stores.id', 'orders.store_id')->join('teams', 'stores.id', 'teams.store_id')->join('distributors', 'distributors.id', 'teams.distributor_id')->where('distributors.id', $distributor);
            });
            
            $query1->when($product, function($query1) use ($product) {
                $query1->where('products.style_no', $product);
            });
            $query1->when($state, function($query1) use ($state) {
                $query1->join('stores', 'stores.id', 'orders.store_id')->join('teams', 'stores.id', 'teams.store_id')->where('stores.state_id', $state);
            });
            $query1->when($area, function($query1) use ($area) {
                $query1->join('stores', 'stores.id', 'orders.store_id')->join('teams', 'stores.id', 'teams.store_id')->where('stores.area_id', $area);
            });
			 $query1->when($store_id, function($query1) use ($store_id) {
                $query1->where('orders.store_id', $store_id);
            });
            $query1->when($orderNo, function($query1) use ($orderNo) {
                $query1->Where('orders.order_no', 'like', '%' . $orderNo . '%');
            });
            if ($from) {
                $query1->where('orders.created_at', '>=', $from);
            }
            if ($to) {
                $query1->where('orders.created_at', '<=', $to);
            }
            //->whereBetween('order_products.created_at', [$from, $to]);

            $data->all_orders = $query1->latest('orders.id')
            ->paginate(50);
            //dd($data->all_orders);
       }else{
            $data->all_orders = OrderProduct::join('products', 'products.id', 'order_products.product_id')
            ->join('orders', 'orders.id', 'order_products.order_id')->join('stores', 'stores.id', 'orders.store_id')->join('teams', 'stores.id', 'teams.store_id')->where('orders.status', 1)->latest('orders.id')->paginate(50);
            //dd($data->all_orders);
       }
        //$allASEs = RetailerListOfOcc::select('ase')->where('ase', '!=', null)->groupBy('ase')->orderBy('ase')->get();
        $allASEs = Employee::where('type',4)->where('status',1)->where('is_deleted',0)->orderby('name')->get();
        $allASMs = Employee::where('type',3)->where('status',1)->where('is_deleted',0)->orderby('name')->get();
        $allRSMs = Employee::where('type',2)->where('status',1)->where('is_deleted',0)->orderby('name')->get();
        $allVPs = Employee::where('type',1)->where('status',1)->where('is_deleted',0)->orderby('name')->get();
        $allDistributors  = Distributor::where('status',1)->where('is_deleted',0)->orderby('name')->get();
      	$allStores = Store::orderBy('name')->where('status',1)->where('is_deleted',0)->get();
        $state = State::where('status',1)->where('is_deleted',0)->groupBy('name')->orderBy('name')->get();
        $product = Product::where('status', 1)->orderBy('style_no')->get();
        //dd($data->products[1]->style_no);
        return view('order.secondary-order-report', compact('data','product','allASEs','state','request','allStores','allASMs','allRSMs','allVPs','allDistributors'));
    }
}
