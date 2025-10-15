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

     public function areas() {
        return $this->belongsTo('App\Models\Area', 'area_id', 'id');
    }
    public function states() {
        return $this->belongsTo('App\Models\State', 'state_id', 'id');
    }
    
    public function vp() {
        return $this->belongsTo('App\Models\Employee', 'vp_id', 'id');
    }
    public function rsm() {
        return $this->belongsTo('App\Models\Employee', 'rsm_id', 'id');
    }
    
    public function asm() {
        return $this->belongsTo('App\Models\Employee', 'asm_id', 'id');
    }
    
    public function ase() {
        return $this->belongsTo('App\Models\Employee', 'ase_id', 'id');
    }
     public function store() {
        return $this->belongsTo('App\Models\Store', 'store_id', 'id');
    }

}
