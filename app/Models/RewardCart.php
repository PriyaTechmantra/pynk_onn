<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RewardCart extends Model
{
    use HasFactory;


     public function product() {
        return $this->belongsTo('App\Models\RetailerProduct', 'product_id', 'id');
    }
    
    public function stores() {
        return $this->belongsTo('App\Models\Store', 'store_id', 'id');
    }
   
}
