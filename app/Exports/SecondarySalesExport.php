<?php

namespace App\Exports;

use App\Models\OrderProduct;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class SecondarySalesExport implements FromQuery, WithHeadings, WithMapping, WithChunkReading, ShouldQueue
{
    use Exportable;

    protected $store_id, $distributor_id, $brandCode, $from, $to, $employees;

    public function __construct($store_id, $distributor_id, $brandCode, $from, $to, $employees)
    {
        $this->store_id       = $store_id;
        $this->distributor_id = $distributor_id;
        $this->brandCode      = $brandCode;
        $this->from           = $from;
        $this->to             = $to;
        $this->employees      = $employees;
    }

    public function query()
    {
        return OrderProduct::query()
            ->select(
                'order_products.id',
                'order_products.qty',
                'orders.order_no',
                'products.name AS product_name',
                'products.style_no',
                'colors.name AS color_name',
                'sizes.name AS size_name',
                'stores.name AS store_name',
                'stores.user_id',
                'teams.asm_id',
                'teams.rsm_id',
                'teams.vp_id',
                'stores.state_id',
                'stores.area_id',
                'stores.pin',
                'orders.created_at'
            )
            ->join('colors', 'colors.id', '=', 'order_products.color_id')
            ->join('sizes', 'sizes.id', '=', 'order_products.size_id')
            ->join('products', 'products.id', '=', 'order_products.product_id')
            ->join('orders', 'orders.id', '=', 'order_products.order_id')
            ->join('stores', 'stores.id', '=', 'orders.store_id')
            ->join('teams', 'teams.store_id', '=', 'stores.id')
            ->where('orders.store_id', $this->store_id)
            ->where('orders.distributor_id', $this->distributor_id)
            ->where('orders.brand', $this->brandCode)
            ->whereBetween('orders.created_at', [$this->from, $this->to])
            ->orderBy('orders.id', 'DESC');
    }

    public function headings(): array
    {
        return [
            'SR', 'ORDER NO', 'PRODUCT', 'STYLE NO', 'COLOR', 'SIZE', 'QTY',
            'STORE', 'ASE', 'ASM', 'RSM', 'VP', 'STATE', 'AREA', 'PINCODE', 'DATETIME'
        ];
    }

    public function map($row): array
    {
        static $count = 1;

        $datetime = date('j F, Y h:i A', strtotime($row->created_at));

        return [
            $count++,
            $row->order_no,
            $row->product_name,
            $row->style_no,
            $row->color_name,
            $row->size_name,
            $row->qty,
            $row->store_name,
            $this->resolve($row->user_id),
            $this->resolve($row->asm_id),
            $this->resolve($row->rsm_id),
            $this->resolve($row->vp_id),
            optional(\App\Models\State::find($row->state_id))->name,
            optional(\App\Models\Area::find($row->area_id))->name,
            $row->pin,
            $datetime
        ];
    }

    private function resolve($csvIds)
    {
        $names = [];
        foreach (explode(',', $csvIds) as $id) {
            if (isset($this->employees[$id])) {
                $names[] = $this->employees[$id];
            }
        }
        return $names ? implode(', ', $names) : 'NA';
    }

    // Chunk size for big data
    public function chunkSize(): int
    {
        return 5000; // loads 5000 rows at a time
    }
}
