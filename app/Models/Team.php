<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;
     
      public function distributor()
     {
         return $this->belongsTo(Distributor::class);
     }

}
