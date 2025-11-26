<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RetailerUserTxnHistory extends Model
{
    use HasFactory;

    public function qrcode() {
        return $this->belongsTo('App\Models\RetailerBarcode', 'barcode_id', 'id');
    }
}
