<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserArea extends Model
{
    use HasFactory;
    protected $table='user_areas';

    public function area()
     {
         return $this->belongsTo(Area::class);
     }
      public function state()
     {
         return $this->belongsTo(State::class);
     }
}
