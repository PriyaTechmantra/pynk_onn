<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Size;
class SizeController extends Controller
{
    
    public function index(Request $request)
    {
        $query = Size::query();

        if(!empty($request->term)) {
            $query->where('name', 'LIKE', '%' . $request->term . '%');
        }
        if (!empty($request->brand_selection)) {
            $brands = explode(',', $request->brand_selection);

            $query->where(function ($q) use ($brands) {
                foreach ($brands as $brand) {
                    $q->orWhereJsonContains('brand', (string) trim($brand));
                }
            });
        }
        $query->latest(); 
        $data = $query->paginate(25);

        return view('size.index', compact('data','request'));
    }
    public function create()
    {
        return view('size.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            "title" => "required|string|max:255",
        ]);
        $data = new Size;
        $data->name = $request->title;
        $data->brand = $request->brand;
        $data->save();
        
        if ($data) {
            return redirect('/sizes')->with('success', 'Size added successfully!');
        } else {
            return redirect('/sizes/create')->withInput($request->all());
        }
    }

    public function show($id)
    {
        $data=Size::where('id',$id)->first();
        return view('size.view',compact('data'));
    }

    public function edit($id)
    {
        $data=Size::findOrfail($id);
        return view('size.edit',compact('data'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            "title" => "required|string|max:255",
            
        ]);
        $data =  Size::findOrfail($id);
        $data->name = $request->title;
        $data->brand = $request->brand;
        $data->save();
        
        if ($data) {
            return redirect('/sizes')->with('success', 'Size updated successfully!');
        } else {
            return redirect('sizes/create')->withInput($request->all());
        }
    }

    public function destroy($id)
    {
        
        // $isReferenced = DB::table('order_products')->where('size_id', $id)->exists();
    
        // if ($isReferenced) {
        //     return redirect()->route('sizes.index')->with('error', 'Size cannot be deleted because it is referenced in another table.');
        // }
        $data=Size::destroy($id);
        if ($data) {
            return redirect('/sizes')->with('success', 'Size deleted successfully.');
        } else {
            return redirect('/sizes')->with('error', 'Failed to delete size.');
        }
    }

    public function status(Request $request, $id)
    {
        $data = Size::findOrFail($id);
        $status = ( $data->status == 1 ) ? 0 : 1;
        $data->status = $status;
        $data->save();
        if ($data) {
            return redirect('/sizes')->with('success', 'Status changed successfully.');
        } else {
            return redirect('sizes/create')->withInput($request->all());
        }
    }
}
