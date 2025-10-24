<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductColorSize extends Model
{
    use HasFactory;
    

    public function color() {
        return $this->hasMany('App\Models\Color' ,'color_id','id');
    }
    public function size() {
        return $this->hasMany('App\Models\Size', 'size_id', 'id');
    }

}
