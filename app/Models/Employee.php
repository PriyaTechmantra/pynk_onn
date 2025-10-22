<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;
class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name','designation', 'type', 'email', 'mobile', 'whatsapp_no',
        'password', 'employee_id', 'address', 'state', 'city', 'pin',
    ];
     
    public function stateDetail()
    {
        return $this->belongsTo(State::class,'state');
    }
    
    public function area()
    {
        return $this->belongsTo(Area::class,'city');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class,'created_by');
    }

   public function permissions()
   {
    return $this->hasMany(UserPermissionCategory::class, 'employee_id');
   }

    public function attendance()
    {
        return $this->hasMany(UserAttendance::class, 'user_id');
    }
	
     
     
     
     
     
   
}
