<?php

namespace App\Exports;

use App\Models\OrderProduct;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class SecondarySalesProductExport implements FromCollection, WithHeadings, WithMapping
{
    protected $distributorId;
    protected $from;
    protected $to;
    protected $brand;
    public function __construct($distributorId, $from, $to,$brand)
    {
        $this->distributorId = $distributorId;
        $this->from = $from;
        $this->to = $to;
        $this->brand = $brand;
    }

    // Query to fetch data
    public function query()
    {
        return OrderProduct::query()
            ->select(
                'orders.order_no',
                'products.name as product_name',
                'products.style_no',
                'colors.name as color_name',
                'sizes.name as size_name',
                'order_products.qty',
                'stores.name as store_name',
                'orders.created_at'
            )
            ->join('orders', 'orders.id', '=', 'order_products.order_id')
            ->join('products', 'products.id', '=', 'order_products.product_id')
            ->join('colors', 'colors.id', '=', 'order_products.color_id')
            ->join('sizes', 'sizes.id', '=', 'order_products.size_id')
            ->join('stores', 'stores.id', '=', 'orders.store_id')
            ->where('orders.distributor_id', $this->distributorId)
            ->where('orders.brand',$this->brand)
            ->whereBetween('orders.created_at', [$this->from, $this->to])
            ->orderBy('orders.created_at', 'desc');
    }

    // Map each row to CSV columns
    public function map($row): array
    {
        return [
            $row->order_no,
            $row->product_name,
            $row->style_no,
            $row->color_name,
            $row->size_name,
            $row->qty,
            $row->store_name,
            Carbon::parse($row->created_at)->format('Y-m-d H:i:s'),
        ];
    }

    // Column headers
    public function headings(): array
    {
        return ['ORDER NO', 'PRODUCT', 'STYLE NO', 'COLOR', 'SIZE', 'QTY', 'STORE', 'DATETIME'];
    }

    // Chunk size to reduce memory usage
    public function chunkSize(): int
    {
        return 5000; // adjust based on your server memory
    }
}