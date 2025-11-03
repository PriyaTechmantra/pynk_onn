<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDistributor extends Model
{
    use HasFactory;

    public function orderProducts() {
        return $this->hasMany('App\Models\OrderProductDistributor', 'order_id', 'id');
    }

    public function users() {
        return $this->belongsTo('App\Models\Employee', 'user_id', 'id');
    }
    public function distributors() {
        return $this->belongsTo('App\Models\Distributor', 'distributor_id', 'id');
    }
}
