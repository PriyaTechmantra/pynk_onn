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

    $data = $query->with('retailer_orders.user')->get();

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
        $updatedEntry->status = $status;
		
        $updatedEntry->save();
		$user_id=$updatedEntry->user_id;
        return redirect()->back()->with('success', 'Order status updated');
    }

     
    public function exportCSV(Request $request)
    {
        $from = $request->date_from ?? date('Y-m-01');
        $to = $request->date_to 
            ? date('Y-m-d', strtotime($request->date_to . ' +1 day')) 
            : date('Y-m-d', strtotime('+1 day'));

        $term = $request->term ?? null;
        $product = $request->product ?? null;
        $user_id = $request->user_id ?? null;

        $query = RewardOrderProduct::join('retailer_products', 'retailer_products.id', '=', 'reward_order_products.product_id')
            ->join('retailer_orders', 'retailer_orders.id', '=', 'reward_order_products.order_id')
            ->when($product, fn($q) => $q->where('reward_order_products.product_id', $product))
            ->when($user_id, fn($q) => $q->where('retailer_orders.user_id', $user_id))
            ->when($term, function($q) use ($term) {
                $q->where(function($inner) use ($term) {
                    $inner->where('retailer_orders.order_no', 'like', '%' . $term . '%')
                        ->orWhere('retailer_orders.shop_name', 'like', '%' . $term . '%');
                });
            })
            ->whereBetween('retailer_orders.created_at', [$from, $to])
            ->select(
                'reward_order_products.qty',
                'retailer_orders.order_no',
                'retailer_orders.shop_name',
                'retailer_orders.email',
                'retailer_orders.mobile',
                'retailer_orders.final_amount',
                'retailer_orders.admin_status',
                'retailer_products.title as product_title',
                'retailer_orders.created_at'
            )
            ->groupBy('reward_order_products.order_id')
            ->latest('retailer_orders.id');

        $data = $query->get();

        if ($data->isEmpty()) {
            return redirect()->back()->with('status', 'No data available to export');
        }

        // Define the CSV filename
        $filename = 'retailer_orders_' . date('Ymd_His') . '.csv';

        // Create CSV headers
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $columns = [
            'S.No',
            'Order No',
            'Shop Name',
            'Email',
            'Mobile',
            'Product',
            'Quantity',
            'Final Amount',
            'Status',
            'Order Date'
        ];

        $callback = function() use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $index => $row) {
                fputcsv($file, [
                    $index + 1,
                    $row->order_no,
                    $row->shop_name,
                    $row->email,
                    $row->mobile,
                    $row->product_title,
                    $row->qty,
                    $row->final_amount,
                    match($row->admin_status) {
                        1 => 'Approved',
                        0 => 'Rejected',
                        default => 'Pending'
                    },
                    date('d M Y', strtotime($row->created_at)),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

  
}
