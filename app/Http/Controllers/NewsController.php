<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $query = News::query();

        if (!empty($request->term)) {
            $query->where('name', 'LIKE', '%' . $request->term . '%');
        }

        // if (!empty($request->brand_selection)) {
        //     $brands = explode(',', $request->brand_selection);

        //     $query->where(function ($q) use ($brands) {
        //         foreach ($brands as $brand) {
        //             $q->orWhereJsonContains('brand', (string) trim($brand));
        //         }
        //     });  
        // }

        $data = $query->orderBy('id')->paginate(25);
       return view('news.index', compact('data', 'request'));
    }

    public function create()
    {
        return view('news.create');
        
    }

    public function store(Request $request)
    {
        $request->validate([
            "title" => "required|string|max:255",
            "start_date" => "required|date",
            "end_date" => "required|date",
            "brand" => "nullable|array",
            "image" => "required|mimes:jpg,jpeg,png,svg,gif|max:10000000",
            "pdf" => "required|mimes:doc,docs,png,svg,jpg,excel,csv,pdf|max:10000000",
        ]);

        $upload_path = "public/uploads/news/";

        $storeData = new News;
        $storeData->title = $request->title;
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

        return redirect('/news')->with('success', 'New added successfully!');
    }


    public function show($id)
    {
        $data = News::findOrFail($id);
        return view('news.view', compact('data'));
    }

    public function edit($id)
    {
        $data = News::findOrFail($id);
        return view('news.edit', compact('data'));
    }

    public function update(Request $request, News $news)
    {
        $request->validate([
            "title" => "required|string|max:255",
            "start_date" => "required|date",
            "end_date" => "required|date",
            "brand" => "nullable|array",
            "image" => "required|mimes:jpg,jpeg,png,svg,gif|max:10000000",
            "pdf" => "required|mimes:doc,docs,png,svg,jpg,excel,csv,pdf|max:10000000",
        ]);

        $params = $request->except('_token');

        $upload_path = "public/uploads/catalogue/";

        $news->title = $params['title'];
        $news->start_date = $params['start_date'];
        $news->end_date = $params['end_date'];
        $news->brand = $params['brand'] ?? $news->brand;

        // Update image if uploaded
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . "." . mt_rand() . "." . $image->getClientOriginalName();
            $image->move($upload_path, $imageName);
            $news->image = $upload_path . $imageName;
        }

        // Update PDF if uploaded
        if ($request->hasFile('pdf')) {
            $pdf = $request->file('pdf');
            $pdfName = time() . "." . $pdf->getClientOriginalName();
            $pdf->move($upload_path, $pdfName);
            $news->pdf = $upload_path . $pdfName;
        }

        $news->save();

        return redirect('/news')->with('success', 'News updated successfully!');
    }

    public function status(Request $request, $id)
    {
        $storeData = News::findOrFail($id);
        $status = ($storeData->status == 1) ? 0 : 1;
        $storeData->status = $status;
        $storeData->save();

         if ($storeData) {
            return redirect('/news')->with('success', 'Status updated successfully!');
        } else {
            return redirect('/news/create')->withInput($request->all());
        }
    }

    public function destroy($id)
    {
        News::destroy($id);
        return redirect('/news')->with('success', 'News deleted successfully!');
    }
}
