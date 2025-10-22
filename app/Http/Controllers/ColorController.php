<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Color;
class ColorController extends Controller
{
    
    public function index(Request $request)
    {
        $query = Color::query();

        if (!empty($request->term)) {
            $query->where('name', 'LIKE', '%' . $request->term . '%');
        }

        if (!empty($request->brand_selection)) {
            $brands = explode(',', $request->brand_selection);

            $query->where(function ($q) use ($brands) {
                foreach ($brands as $brand) {
                    switch ($brand) {
                        case '1': 
                            $q->orWhereJsonContains('brand', '1')
                            ->orWhereJsonContains('brand', '3');
                            break;

                        case '2':
                            $q->orWhereJsonContains('brand', '2')
                            ->orWhereJsonContains('brand', '3');
                            break;

                        case '3': 
                            $q->orWhere(function ($q2) {
                            $q2->whereJsonContains('brand', '1')
                               ->whereJsonContains('brand', '2');
                        })->orWhereJsonContains('brand', '3');
                        break;
                    }
                }
            });
        }

        $data = $query->latest()->paginate(25);

        return view('color.index', compact('data','request'));
    }

    public function create()
    {
        return view('color.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            "title" => "required|string|max:255",
            "code" => "required",
            "brand" => "nullable|array"
        ]);

        $data = new Color;
        $data->name = $request->title;
        $data->code = $request->code;
        $data->brand = $request->brand;

        $data->save();
        
        if ($data) {
            return redirect()->route('colors.index')->with('success', 'Color added successfully!');
        } else {
            return redirect()->route('colors.create')->withInput($request->all());
        }
    }

    public function show($id)
    {
        $data=Color::where('id',$id)->first();
        return view('color.view',compact('data'));
    }

    public function edit($id)
    {
        $data=Color::findOrfail($id);
        return view('color.edit',compact('data'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            "title" => "required|string|max:255",
            "code" => "required",
            "brand" => "nullable|array"

        ]);
        $data =  Color::findOrfail($id);
        $data->name = $request->title;
        $data->code = $request->code;
        $data->brand = $request->brand;
        $data->save();
        
        if ($data) {
            return redirect()->route('colors.index')->with('success', 'Color updated successfully!');
        } else {
            return redirect()->route('colors.create')->withInput($request->all());
        }
    }

    public function destroy($id)
    {
        
        // $isReferenced = DB::table('order_products')->where('color_id', $id)->exists();
    
        // if ($isReferenced) {
        //     return redirect()->route('colors.index')->with('error', 'Color cannot be deleted because it is referenced in another table.');
        // }
        $data=Color::destroy($id);
        if ($data) {
            return redirect()->route('colors.index')->with('success', 'Color deleted successfully.');
        } else {
            return redirect()->route('colors.index')->with('error', 'Failed to delete color.');
        }
    }

    public function status(Request $request, $id)
    {
        $data = Color::findOrFail($id);
        $status = ( $data->status == 1 ) ? 0 : 1;
        $data->status = $status;
        $data->save();
        if ($data) {
            return redirect()->route('colors.index')->with('success', 'Status changed successfully!');
        } else {
            return redirect()->route('colors.create')->withInput($request->all());
        }
    }
}
