<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

     public function user()
     {
         return $this->belongsTo(Employee::class,'user_id');
     }

      public function state()
     {
         return $this->belongsTo(State::class,'state_id');
     }
     
      public function area()
     {
         return $this->belongsTo(Area::class,'area_id');
     }

      public function ProductDetails() {
        return $this->HasMany('App\Models\Product', 'store_id', 'id')->where('status', 1);
    }
}
