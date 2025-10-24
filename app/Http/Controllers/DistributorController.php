<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\UserNoOrderReason;
class DistributorController extends Controller
{
    public function note(Request $request)
    {
       
        $data = UserNoOrderReason::latest('id')
                ->with(['user' => function ($query) {
                $query->where('status', 1);
            }])
            ->paginate(25);
            return view('distributor.note',compact('data'));

    }
    
}