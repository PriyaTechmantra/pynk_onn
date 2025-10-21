<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RetailerOrder extends Model
{
    use HasFactory;
    public function product() {
        return $this->belongsTo('App\Models\RetailerProduct', 'product_id', 'id');
    }
	
	 public function user() {
        return $this->belongsTo('App\Models\Store', 'user_id', 'id');
    }
	
	 public function orderProduct() {
        return $this->hasMany('App\Models\RewardOrderProduct', 'order_id', 'id');
    }
}
