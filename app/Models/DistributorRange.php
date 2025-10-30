<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistributorRange extends Model
{
    use HasFactory;

    public function range() {
        return $this->belongsTo('App\Models\Collection', 'collection_id', 'id');
    }

    public function ase() {
        return $this->belongsTo('App\Models\Employee', 'user_id', 'id');
    }

    public function distributor() {
        return $this->belongsTo('App\Models\Distributor', 'distributor_id', 'id');
    }
}
