<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\RewardOrderProduct;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class RewardOrderExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $from;
    protected $to;
    protected $product;
    protected $term;
    protected $user_id;

    public function __construct($from, $to, $product, $term, $user_id)
    {
        $this->from = $from;
        $this->to = $to;
        $this->product = $product;
        $this->term = $term;
        $this->user_id = $user_id;
    }

    public function collection()
    {
        $query = RewardOrderProduct::select(
            'retailer_orders.order_no',
            'reward_order_products.product_name',
            'reward_order_products.qty',
            'reward_order_products.price',
            'retailer_orders.shop_name',
            'retailer_orders.mobile',
            'stores.owner_name',
            'stores.owner_lname',
            'retailer_orders.billing_state',
            'retailer_list_of_occ.distributor_name',
            'retailer_orders.asm_approval',
            'retailer_orders.rsm_approval',
            'retailer_orders.vp_approval',
            'retailer_orders.admin_status',
            'retailer_orders.status',
            'retailer_orders.created_at'
        )
            ->join('retailer_products', 'retailer_products.id', 'reward_order_products.product_id')
            ->join('retailer_orders', 'retailer_orders.id', 'reward_order_products.order_id')
            ->join('retailer_list_of_occ', 'retailer_list_of_occ.store_id', 'retailer_orders.user_id')
            ->join('stores', 'stores.id', 'retailer_orders.user_id')
            ->whereBetween('retailer_orders.created_at', [$this->from, $this->to]);

        if ($this->product) {
            $query->where('reward_order_products.product_id', $this->product);
        }

        if ($this->user_id) {
            $query->where('retailer_orders.user_id', $this->user_id);
        }

        if ($this->term) {
            $query->where(function ($q) {
                $q->where('retailer_orders.order_no', 'like', '%' . $this->term . '%')
                  ->orWhere('retailer_orders.shop_name', 'like', '%' . $this->term . '%');
            });
        }

        return $query->latest('retailer_orders.id')->get();
    }

    public function headings(): array
    {
        return [
            'SR',
            'ORDER NUMBER',
            'PRODUCT NAME',
            'QUANTITY',
            'ORDER AMOUNT',
            'STORE',
            'STORE MOBILE',
            'STORE OWNER NAME',
            'STORE STATE',
            'DISTRIBUTOR',
            'ASM APPROVAL',
            'RSM APPROVAL',
            'VP APPROVAL',
            'ADMIN APPROVAL',
            'ORDER STATUS',
            'DATETIME'
        ];
    }

    public function map($row): array
    {
        static $count = 1;

        $asm = $row->asm_approval == 1 ? 'Approved' : ($row->asm_approval == 2 ? 'Wait for approval' : 'Rejected');
        $rsm = $row->rsm_approval == 1 ? 'Approved' : ($row->rsm_approval == 2 ? 'Wait for approval' : 'Rejected');
        $vp  = $row->vp_approval == 1 ? 'Approved' : ($row->vp_approval == 2 ? 'Wait for approval' : 'Rejected');
        $admin = $row->admin_status == 1 ? 'Approved' : ($row->admin_status == 2 ? 'Wait for approval' : 'Rejected');

        $statusTitle = match ($row->status) {
            1 => 'New',
            2 => 'Confirmed',
            3 => 'Shipped',
            4 => 'Delivered',
            5 => 'Cancelled',
            6 => 'Return request',
            7 => 'Return approved',
            8 => 'Return declined',
            9 => 'Products Returned',
            10 => 'Products Received',
            11 => 'Products Shipped',
            12 => 'Products Delivered',
            default => 'New',
        };

        return [
            $count++,
            $row->order_no,
            $row->product_name,
            $row->qty,
            $row->price,
            $row->shop_name,
            $row->mobile,
            $row->owner_name . ' ' . $row->owner_lname,
            $row->billing_state,
            $row->distributor_name,
            $asm,
            $rsm,
            $vp,
            $admin,
            $statusTitle,
            date('j M Y g:i A', strtotime($row->created_at)),
        ];
    }
}
