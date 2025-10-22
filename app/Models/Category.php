<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    public function ProductDetails(string $orderBy = 'style_no', string $order = 'asc') {
        return $this->hasMany('App\Models\Product', 'cat_id', 'id')->where('status', 1)->orderBy($orderBy, $order);
    }
}
