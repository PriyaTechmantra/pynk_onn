<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserNoOrderReason extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(Employee::class, 'user_id');
    }

      public function store()
     {
         return $this->belongsTo(Store::class);
     }

     public function noorder(){
        return $this->belongsTo(NoOrderReason::class,'no_order_reason_id');
     }
}
