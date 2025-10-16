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
       $data = (object) [];
        $from =  date('Y-m-01');
        $to =  date('Y-m-d', strtotime('+1 day'));
       if (isset($request->date_from) || isset($request->date_to) ||isset($request->product) ||isset($request->term) || isset($request->user_id) ) {
            $from = $request->date_from ? $request->date_from : date('Y-m-01');
            $to = date('Y-m-d', strtotime(request()->input('date_to'). '+1 day'))? date('Y-m-d', strtotime(request()->input('date_to'). '+1 day')) : '';
            $term = $request->term ? $request->term : '';
            $product = $request->product ?? '';
 			$user_id = $request->user_id ? $request->user_id : '';
            // all order products
            $query1 = RewardOrderProduct::join('retailer_products', 'retailer_products.id', 'reward_order_products.product_id')
            ->join('retailer_orders', 'retailer_orders.id', 'reward_order_products.order_id')
            ;
           
            $query1->when($product, function($query1) use ($product) {
                $query1->where('reward_order_products.product_id', $product);
            });
            
			 $query1->when($user_id, function($query1) use ($user_id) {
                $query1->where('retailer_orders.user_id', $user_id);
            });
            $query1->when($term, function($query1) use ($term) {
                $query1->Where('retailer_orders.order_no', 'like', '%' . $term . '%')->orWhere('retailer_orders.shop_name', 'like', '%' . $term . '%');
            })->whereBetween('retailer_orders.created_at', [$from, $to]);

            $data = $query1->latest('retailer_orders.id')->groupby('reward_order_products.order_id')
            ->get();
			//dd($data);
       }else{
            $data = RewardOrderProduct::join('retailer_products', 'retailer_products.id', 'reward_order_products.product_id')
            ->join('retailer_orders', 'retailer_orders.id', 'reward_order_products.order_id')->whereBetween('reward_order_products.created_at', [$from, $to])->latest('retailer_orders.id')->groupby('reward_order_products.order_id')->get();
            
       }
        $allUser=Store::orderby('name')->get();
        $products=RetailerProduct::orderby('title')->get();
        return view('reward.order.index', compact('data','allUser','products','request'));
    }
	
	// details
	public function show(Request $request, $id)
    {
        $data = RetailerOrder::findOrFail($id);
        return view('reward.order.detail', compact('data'));
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

     
    public function exportCSV(Request $request)
    {
        $data = (object) [];
        $from =  date('Y-m-01');
        $to =  date('Y-m-d', strtotime('+1 day'));
       if (isset($request->date_from) || isset($request->date_to) ||isset($request->product) ||isset($request->term) || isset($request->user_id) ) {
            $from = $request->date_from ? $request->date_from : date('Y-m-01');
            $to = date('Y-m-d', strtotime(request()->input('date_to'). '+1 day'))? date('Y-m-d', strtotime(request()->input('date_to'). '+1 day')) : '';
            $term = $request->term ? $request->term : '';
            $product = $request->product ?? '';
 			$user_id = $request->user_id ? $request->user_id : '';
            // all order products
            $query1 = RewardOrderProduct::select('retailer_orders.order_no','reward_order_products.product_name','reward_order_products.qty','reward_order_products.price','retailer_orders.shop_name','retailer_orders.mobile','stores.owner_name','stores.owner_lname','retailer_orders.billing_state','retailer_list_of_occ.distributor_name','retailer_orders.asm_approval','retailer_orders.rsm_approval','retailer_orders.vp_approval','retailer_orders.admin_status','retailer_orders.status','retailer_orders.created_at')->join('retailer_products', 'retailer_products.id', 'reward_order_products.product_id')
            ->join('retailer_orders', 'retailer_orders.id', 'reward_order_products.order_id')->join('retailer_list_of_occ', 'retailer_list_of_occ.store_id', 'retailer_orders.user_id')->join('stores', 'stores.id', 'retailer_orders.user_id')
            ;
           
            $query1->when($product, function($query1) use ($product) {
                $query1->where('reward_order_products.product_id', $product);
            });
            
			 $query1->when($user_id, function($query1) use ($user_id) {
                $query1->where('retailer_orders.user_id', $user_id);
            });
            $query1->when($term, function($query1) use ($term) {
                $query1->Where('retailer_orders.order_no', 'like', '%' . $term . '%')->orWhere('retailer_orders.shop_name', 'like', '%' . $term . '%');
            })->whereBetween('retailer_orders.created_at', [$from, $to]);

            $data->all_orders = $query1->latest('retailer_orders.id')
            ->get();
			//dd($data->all_orders);
       }else{
            $data->all_orders = RewardOrderProduct::select('retailer_orders.order_no','reward_order_products.product_name','reward_order_products.qty','reward_order_products.price','retailer_orders.shop_name','retailer_orders.mobile','stores.owner_name','stores.owner_lname','retailer_orders.billing_state','retailer_list_of_occ.distributor_name','retailer_orders.asm_approval','retailer_orders.rsm_approval','retailer_orders.vp_approval','retailer_orders.admin_status','retailer_orders.status','retailer_orders.created_at')->join('retailer_products', 'retailer_products.id', 'reward_order_products.product_id')
            ->join('retailer_orders', 'retailer_orders.id', 'reward_order_products.order_id')->join('retailer_list_of_occ', 'retailer_list_of_occ.store_id', 'retailer_orders.user_id')->join('stores', 'stores.id', 'retailer_orders.user_id')->whereBetween('reward_order_products.created_at', [$from, $to])->latest('retailer_orders.id')->get();
           // dd($data->all_orders);
       }

        if (count($data->all_orders) > 0) {
            $delimiter = ",";
            $filename = "onn-reward-order-report-".date('Y-m-d').".csv";

            // Create a file pointer 
            $f = fopen('php://memory', 'w');

            // Set column headers 
            $fields = array('SR', 'ORDER NUMBER', 'PRODUCT NAME', 'QUANTITY','ORDER AMOUNT', 'STORE','STORE MOBILE','STORE OWNER NAME','STORE STATE','DISTRIBUTOR','ASM APPROVAL','RSM APPROVAL','VP APPROVAL','ADMIN APPROVAL','ORDER STATUS','DATETIME');
            fputcsv($f, $fields, $delimiter); 

            $count = 1;

            foreach($data->all_orders as $row) {
               
                $datetime = date('j M Y g:i A', strtotime($row['created_at']));
				    if($row->asm_approval ==2)
                    $asm_status='Wait for approval';
                    elseif($row->asm_approval==1)
                    $asm_status='Approved';
                    else
                    $asm_status='Rejected';
				
				 if($row->rsm_approval ==2)
                    $rsm_status='Wait for approval';
                    elseif($row->rsm_approval==1)
                    $rsm_status='Approved';
                    else
                    $rsm_status='Rejected';
				    if($row->vp_approval ==2)
                    $vp_status='Wait for approval';
                    elseif($row->vp_approval==1)
                    $vp_status='Approved';
                    else
                    $vp_status='Rejected';
                    
                    if($row->admin_status ==2)
                    $admin_status='Wait for approval';
                    elseif($row->admin_status==1)
                    $admin_status='Approved';
                    else
                    $admin_status='Rejected';
				
				      switch ($row->status) {
                    case 1:
                        $statusTitle = 'New';
                        $statusDesc = 'We are currently processing your order';
                        break;
                    case 2:
                        $statusTitle = 'Confirmed';
                        break;
                    case 3:
                        $statusTitle = 'Shipped';
                        $statusDesc = 'Your order is Shipped. It will reach you soon';
                        break;
                    case 4:
                        $statusTitle = 'Delivered';
                        $statusDesc = 'Your order is delivered';
                        break;
                    case 5:
                        $statusTitle = 'Cancelled';
                        $statusDesc = 'Your order is cancelled';
                        break;
                    case 6:
                        $statusTitle = 'Return request';
                        $statusDesc = 'You have requested return for the product';
                        break;
                    case 7:
                        $statusTitle = 'Return approved';
                        $statusDesc = 'You return request is approved';
                        break;
                    case 8:
                        $statusTitle = 'Return declined';
                        $statusDesc = 'You return request is declined';
                        break;
                    case 9:
                        $statusTitle = 'Products Returned';
                        $statusDesc = 'You have returned old products';
                        break;
                    case 10:
                        $statusTitle = 'Products Received';
                        $statusDesc = 'Your returned products are received';
                        break;
                    case 11:
                        $statusTitle = 'Products Shipped';
                        $statusDesc = 'Your new products are shipped';
                        break;
                    case 12:
                        $statusTitle = 'Products Delivered';
                        $statusDesc = 'Your new products are delivered';
                        break;
                    default:
                        $statusTitle = 'New';
                        $statusDesc = 'We are currently processing your order';
                        break;
                }
                $lineData = array(
                    $count,
                    $row['order_no'] ?? '',
                    $row['product_name'] ?? '',
                    $row['qty'] ?? '',
                    $row['price'] ?? '',
                    $row['shop_name'] ?? '',
                    $row['mobile'] ?? '',
                    $row['owner_name'].' '.$row['owner_lname'] ?? '',
                    $row['billing_state'] ?? '',
                    $row['distributor_name'] ?? '',
					$asm_status,
					$rsm_status,
					$vp_status,
					$admin_status,
					$statusTitle,
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
  
}
