<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookCategory;
use Illuminate\Http\Request;


class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $data = BookCategory::get();
        if ($data->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No category found for this user'
                
            ], 404);
        }
        return response()->json([
            'status'=>true,
            'message' => 'Category list', 
            'data' => $data 
        ], 200);
    }
  
}
