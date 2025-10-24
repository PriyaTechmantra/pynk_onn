<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    public function product() {
        return $this->belongsTo('App\Models\Product', 'product_id', 'id');
    }
    public function users() {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }
    public function stores() {
        return $this->belongsTo('App\Models\Store', 'store_id', 'id');
    }
    public function color() {
        return $this->belongsTo('App\Models\Color', 'color_id', 'id');
    }
    public function size() {
        return $this->belongsTo('App\Models\Size', 'size_id', 'id');
    }
}
