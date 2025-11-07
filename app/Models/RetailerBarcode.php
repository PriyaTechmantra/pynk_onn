<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RetailerBarcode extends Model
{
    use HasFactory;
    public function distributor() {
        return $this->belongsTo('App\Models\User', 'distributor_id', 'id');
    }
    public function state() {
        return $this->belongsTo('App\Models\State', 'state_id', 'id');
    }
    
     public function user() {
        return $this->belongsTo('App\Models\User', 'member_id', 'id');
        
    }
     public function stock() {
        return $this->hasMany('App\Models\WarehouseStock', 'qrcode_id', 'id');
    }
}
