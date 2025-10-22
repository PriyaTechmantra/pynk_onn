<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    use HasFactory;
    public function cat() {
        return $this->belongsTo('App\Models\Category', 'cat_id', 'id');
    }
    public function ProductDetails(string $orderBy = 'style_no', string $order = 'asc') {
        return $this->hasMany('App\Models\Product', 'collection_id', 'id')->where('status', 1)->orderBy($orderBy, $order);
    }
}
