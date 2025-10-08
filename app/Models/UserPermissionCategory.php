<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class UserPermissionCategory extends Model
{
    use HasFactory;

    protected $table='user_permission_categories';
    protected $fillable=[
        'user_id',
       
    ];
    
     public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function custodian()
    {
        return $this->belongsTo(User::class,'cus_user_id');
    }
}