<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RewardOrderProduct extends Model
{
    use HasFactory;
     public function order()
    {
        return $this->belongsTo(RetailerOrder::class, 'order_id', 'id');
    }
	
	 public function product()
    {
        return $this->belongsTo(RetailerProduct::class, 'product_id', 'id');
    }
}
