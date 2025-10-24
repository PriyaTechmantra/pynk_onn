<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
    use HasFactory;

    public function product() {
        return $this->belongsTo('App\Models\Product', 'product_id', 'id');
    }

    public function size() {
        return $this->belongsTo('App\Models\Size', 'size_id', 'id');
    }

    public function orders() {
        return $this->belongsTo('App\Models\Order', 'order_id', 'id');
    }

    public function color() {
        return $this->belongsTo('App\Models\Color', 'color_id', 'id');
    }
}
