<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductColorSize extends Model
{
    use HasFactory;
    

    public function colorData() {
        return $this->belongsTo('App\Models\Color' ,'color_id','id');
    }
    public function size() {
        return $this->belongsTo('App\Models\Size', 'size_id', 'id');
    }

}
