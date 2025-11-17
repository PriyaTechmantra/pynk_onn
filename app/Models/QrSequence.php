<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QrSequence extends Model
{
    
    protected $fillable = [
	   'distributor_id', 'from', 'to', 'count', 'actual_date', 'created_at', 'updated_at'
 	];

    public function distributor() {
        return $this->belongsTo('App\Models\Distributor', 'distributor_id', 'id');
    }
}