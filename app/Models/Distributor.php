<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Distributor extends Model
{
    use HasFactory;
    protected $fillable = [
        'name','code',  'email', 'contact', 'whatsapp',
        'password', 'address', 'state_id', 'city', 'pin','brand','area_id','user_id','date_of_joining','status','is_deleted'
    ];
     public function areas() {
        return $this->belongsTo('App\Models\Area', 'area_id', 'id');
    }
    public function states()
    {
        return $this->belongsTo(State::class, 'state_id', 'id');
    }
    public function createdBy() {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }
}
