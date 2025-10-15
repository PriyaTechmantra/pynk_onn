<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\RewardTerms;
class TermsController extends Controller
{
    public function index(Request $request)
    {
        $data = RewardTerms::latest('id')->first();
        return view('terms.index', compact('data'));
    }

    public function store(Request $request)
    {
         $request->validate([
            "terms" => "required",
        ]);
        $storeData=new RewardTerms();
        $storeData->id = 1;
        $storeData->terms = $request->terms;

        $storeData->save();
        
        if ($storeData) {
            return redirect()->back()->with('success', 'Terms and condition added successfully');
        } else {
            return redirect()->back()->withInput($request->all())->with('failure', 'Something happened');
        }
    }
	
	public function update(Request $request)
    {
        $request->validate([
            "terms" => "required",
        ]);
        $storeData =  RewardTerms::findOrFail($request->id);
        $storeData->terms = $request->terms;
        $storeData->save();
        
        if ($storeData) {
            return redirect()->back()->with('success', 'Terms and condition updated successfully');
        } else {
            return redirect()->back()->withInput($request->all())->with('failure', 'Something happened');
        }
    }
}

