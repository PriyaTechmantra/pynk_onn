<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RetailerOrder;
use App\Models\Store;
use App\Models\RetailerProduct;
use App\Models\RewardOrderProduct;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use DB;
use App\Exports\RewardOrderExport;
use Maatwebsite\Excel\Facades\Excel;
class RetailerOrderController extends Controller
{
    public function index(Request $request)
{
    $from = $request->date_from ?? date('Y-m-01');
    $to = $request->date_to 
        ? date('Y-m-d', strtotime($request->date_to . ' +1 day')) 
        : date('Y-m-d', strtotime('+1 day'));

    $term = $request->term ?? null;
    $product = $request->product ?? null;
    $user_id = $request->user_id ?? null;

    $query = RewardOrderProduct::join('retailer_orders', 'retailer_orders.id', '=', 'reward_order_products.order_id')
        ->leftJoin('retailer_products', 'retailer_products.id', '=', 'reward_order_products.product_id')
        
        // filters
        ->when($product, fn($q) => $q->where('reward_order_products.product_id', $product))
        ->when($user_id, fn($q) => $q->where('retailer_orders.user_id', $user_id))
        ->when($term, function($q) use ($term) {
            $q->where(function($inner) use ($term) {
                $inner->where('retailer_orders.order_no', 'like', '%' . $term . '%')
                    ->orWhere('retailer_orders.shop_name', 'like', '%' . $term . '%');
            });
        })
        ->whereBetween('retailer_orders.created_at', [$from, $to])

        // safe selects
        ->select(
            'reward_order_products.order_id',
            'retailer_orders.*',
            DB::raw('MIN(retailer_products.title) as product_title')
        )

        // group only by the safe column(s)
        ->groupBy('reward_order_products.order_id')

        ->latest('retailer_orders.id');

    $data = $query->with('order.user')->get();
    
    $allUser = Store::orderBy('name')->get();
    $products = RetailerProduct::orderBy('title')->get();

    return view('reward.order.index', compact('data','allUser','products','request'));
}


	
	// details
	public function show(Request $request, $id)
    {
        $data = RetailerOrder::findOrFail($id);
        return view('reward.order.view', compact('data'));
    }
	
	public function approval(Request $request,$id,$status)
    {
		//dd($request->status);
        $updatedEntry = RetailerOrder::findOrFail($id);
        $updatedEntry->admin_status = $status;
		if($status == 0)
		{
			$updatedEntry->status=5;
		}
        $updatedEntry->save();
		$user_id=$updatedEntry->user_id;
		if($updatedEntry->admin_status == 0)
		{
		  $store=Store::findOrFail($user_id);
		  $store->wallet += $updatedEntry->final_amount;
		  $store->save();
		}
       return redirect()->back()->with('success', 'Order status updated');
    }
    public function status(Request $request,$id,$status)
    {
		//dd($request->status);
        $updatedEntry = RetailerOrder::findOrFail($id);
        if ($updatedEntry->status == 5) {
            return redirect()->back()->with('failure', 'Order has been cancelled');
        }
        if ($updatedEntry->status == 4 && in_array($status, [1,2, 3, 5])) {
            return redirect()->back()->with('failure', 'Order has been delivered');
        }

        if ($updatedEntry->status == 3 && in_array($status, [1,2])) {
            return redirect()->back()->with('failure', 'Order has been shipped');
        }

        if ($updatedEntry->status == 2 && in_array($status, [1])) {
            return redirect()->back()->with('failure', 'Order has been shipped');
        }
        $updatedEntry->status = $status;
		
        $updatedEntry->save();
		$user_id=$updatedEntry->user_id;
        return redirect()->back()->with('success', 'Order status updated');
    }

     
    public function exportCSV(Request $request)
    {
        $from = $request->date_from ?? date('Y-m-01');
        $to   = $request->date_to ? date('Y-m-d', strtotime($request->date_to . ' +1 day')) : date('Y-m-d', strtotime('+1 day'));

        $export = new RewardOrderExport(
            $from,
            $to,
            $request->product ?? '',
            $request->term ?? '',
            $request->user_id ?? ''
        );

        return Excel::download($export, 'reward-order-report-' . date('Y-m-d') . '.csv');
    }

  
}
