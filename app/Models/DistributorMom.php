<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistributorMom extends Model
{
    use HasFactory;
    protected $table='directory_mom';

     public function distributors() {
        return $this->belongsTo('App\Models\Distributor', 'distributor_id', 'id');
    }
}
