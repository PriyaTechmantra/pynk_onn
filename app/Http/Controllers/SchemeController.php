<?php

namespace App\Http\Controllers;

use App\Models\Scheme;
use Illuminate\Http\Request;

class SchemeController extends Controller
{
    
    public function index(Request $request)
    {
        $query = Scheme::query();

        if (!empty($request->type)) {
            $query->where('type', 'LIKE', '%' . $request->type . '%');
        }

        if (!empty($request->brand_selection)) {
            $brands = explode(',', $request->brand_selection);
            $query->where(function ($q) use ($brands) {
                foreach ($brands as $brand) {
                    $q->orWhereJsonContains('brand', (string) trim($brand));
                }
            });
        }

        if (!empty($request->date_from) && !empty($request->date_to)) {
            $from = $request->date_from;
            $to = $request->date_to;

            $query->where(function ($q) use ($from, $to) {
                $q->whereBetween('start_date', [$from, $to])      
                ->orWhereBetween('end_date', [$from, $to])      
                ->orWhere(function ($q2) use ($from, $to) {   
                    $q2->where('start_date', '<=', $from)
                        ->where('end_date', '>=', $to);
                });
            });
        } elseif (!empty($request->date_from)) {
            $query->where('end_date', '>=', $request->date_from);
        } elseif (!empty($request->date_to)) {
            $query->where('start_date', '<=', $request->date_to);
        }

        $data = $query->orderBy('id', 'desc')->paginate(25);

        return view('scheme.index', compact('data', 'request'));
    }

    public function create()
    {
        return view('scheme.create');
        
    }

    public function store(Request $request)
    {
        $request->validate([
            "type" => "required|string|in:Current,Past",
            "title" => "required|string|max:255",
            "start_date" => "required|date",
            "end_date" => "required|date",
            "brand" => "nullable|array",
            "image" => "required|mimes:jpg,jpeg,png,svg,gif|max:10000000",
            "pdf" => "required|mimes:doc,docs,png,svg,jpg,excel,csv,pdf|max:10000000",
        ]);

        $upload_path = "public/uploads/scheme/";

        $storeData = new Scheme;
        $storeData->type = $request->type;
        $storeData->name = $request->title;
        $storeData->start_date = $request->start_date;
        $storeData->end_date = $request->end_date;
        $storeData->brand = $request->brand ?? [];

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . "." . mt_rand() . "." . $image->getClientOriginalName();
            $image->move($upload_path, $imageName);
            $storeData->image = $upload_path . $imageName;
        }

        // Handle PDF upload
        if ($request->hasFile('pdf')) {
            $pdf = $request->file('pdf');
            $pdfName = time() . "." . $pdf->getClientOriginalName();
            $pdf->move($upload_path, $pdfName);
            $storeData->pdf = $upload_path . $pdfName;
        }

        $storeData->save();

        return redirect('/schemes')->with('success', 'Scheme saved successfully!');
    }



   public function show($id)
    {
        $data = Scheme::findOrFail($id);
        return view('scheme.view', compact('data'));
    }

    public function edit($id)
    {
        $data = Scheme::findOrFail($id);
        return view('scheme.edit', compact('data'));
    }

    public function update(Request $request, Scheme $scheme)
    {
        $request->validate([
            "type" => "required|string|in:Current,Past",
            "title" => "required|string|max:255",
            "start_date" => "required|date",
            "end_date" => "required|date",
            "brand" => "nullable|array",
            "image" => "required|mimes:jpg,jpeg,png,svg,gif|max:10000000",
            "pdf" => "required|mimes:doc,docs,png,svg,jpg,excel,csv,pdf|max:10000000",
        ]);

        $params = $request->except('_token');

        $upload_path = "public/uploads/catalogue/";

        $scheme->type = $params['type'];
        $scheme->title = $params['title'];
        $scheme->start_date = $params['start_date'];
        $scheme->end_date = $params['end_date'];
        $scheme->brand = $params['brand'] ?? $scheme->brand;

        // Update image if uploaded
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . "." . mt_rand() . "." . $image->getClientOriginalName();
            $image->move($upload_path, $imageName);
            $scheme->image = $upload_path . $imageName;
        }

        // Update PDF if uploaded
        if ($request->hasFile('pdf')) {
            $pdf = $request->file('pdf');
            $pdfName = time() . "." . $pdf->getClientOriginalName();
            $pdf->move($upload_path, $pdfName);
            $scheme->pdf = $upload_path . $pdfName;
        }

        $scheme->save();

        return redirect('/schemes')->with('success', 'Scheme updated successfully!');
    }

    public function status(Request $request, $id)
    {
        $storeData = Scheme::findOrFail($id);
        $status = ($storeData->status == 1) ? 0 : 1;
        $storeData->status = $status;
        $storeData->save();

         if ($storeData) {
            return redirect('/schemes')->with('success', 'Status updated successfully!');
        } else {
            return redirect('/schemes/create')->withInput($request->all());
        }
    }

    public function destroy($id)
    {
        Scheme::destroy($id);

        return redirect('/schemes')->with('success', 'Scheme deleted successfully!');
    }

}
