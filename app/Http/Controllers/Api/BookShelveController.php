<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bookshelve;
use Illuminate\Support\Facades\Validator;

class BookShelveController extends Controller
{
    public function searchByQrCode(Request $request)
    {
  
        $qrcode = $request->input('qrcode');

        $book = Bookshelve::where('qrcode', $qrcode)->with('office')
            ->get();
        if($book->isEmpty()) {
            return response()->json([
                'message' => 'No data found for this qr-code.', 'status'=>false
            ], 404);
        }
        return response()->json([
            'status' => true,
            'message' => 'Book selves by QR-code',
            'data' =>$book
        ], 200);

      
    }
}
