<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    public function category() {
        return $this->belongsTo('App\Models\Category', 'cat_id', 'id');
    }
    public function collection() {
        return $this->belongsTo('App\Models\Collection', 'collection_id', 'id');
    }
    public function colorSize() {
        return $this->hasMany('App\Models\ProductColorSize', 'product_id', 'id');
    }
    public function color() {
        return $this->hasMany('App\Models\ProductColorSize' ,'color_id','id');
    }
    public function size() {
        return $this->hasMany('App\Models\Size',  'id');
    }
}
