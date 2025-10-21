<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCatalogue extends Model
{
    protected $table = 'product_catelogues';

    protected $fillable = [
        'title',
        'description',
        'price',
        'category_id',
        'image',
    ];

    protected $hidden = [];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function states()
    {
        return $this->belongsTo(\App\Models\State::class, 'state', 'id');
    }
    protected $casts = [
        'brand' => 'array',
        'state' => 'array',
        'vp' => 'array',  
    ];
}
