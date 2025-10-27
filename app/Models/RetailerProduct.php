<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RetailerProduct extends Model
{
    use HasFactory;
    protected $table='retailer_products';
  
    protected $fillable = [
        'title',
        'desc',
        'amount',
        'status',
    ];

}
