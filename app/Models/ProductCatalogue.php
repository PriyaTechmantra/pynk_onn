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
}
