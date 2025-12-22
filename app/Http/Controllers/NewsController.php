<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use DB;
use Auth;
class NewsController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $userBrands = DB::table('user_permission_categories')
                ->where('user_id', Auth::id())
                ->pluck('brand')
                ->toArray();
        
            $brandsToShow = [];

            if (in_array(3, $userBrands) || (in_array(1, $userBrands) && in_array(2, $userBrands))) {
                // Both brands access
                $brandsToShow = [1, 2, 3];
            } elseif (in_array(1, $userBrands)) {
                $brandsToShow = [1];
            } elseif (in_array(2, $userBrands)) {
                $brandsToShow = [2];
            }
        $query = News::query();

        if (!empty($request->term)) {
            $query->where('title', 'LIKE', '%' . $request->term . '%');
        }

         if ($request->filled('brand_selection')) {
            $query->where(function ($q) use ($request) {
                if ($request->brand_selection == 3) {
                    // “Both” selected → show ONN (1), PYNK (2), and Both (3)
                    $q->whereIn('news.brand', [1, 2, 3]);
                } else {
                    // single brand selected → include that + both
                    $q->where('news.brand', $request->brand_selection)
                    ->orWhere('news.brand', 3);
                }
            });
        } else {
            // if brand not selected — show according to user permission
            $userBrandPermissions = DB::table('user_permission_categories')
                ->where('user_id', $user->id)
                ->pluck('brand')
                ->toArray();

            if (!empty($userBrandPermissions)) {
                $query->where(function ($q) use ($userBrandPermissions) {
                    if (in_array(3, $userBrandPermissions)) {
                        // user has both brand permission
                        $q->whereIn('news.brand', [1, 2, 3]);
                    } else {
                        // user has limited brand(s)
                        $q->whereIn('news.brand', array_merge($userBrandPermissions, [3]));
                    }
                });
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
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

        $data = $query->orderByDesc('id')->paginate(25);

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
            "image" => "required|mimes:jpg,jpeg,png,svg,gif|max:10000000",
            "pdf" => "required|mimes:doc,docs,png,svg,jpg,excel,csv,pdf|max:10000000",
        ]);

        $upload_path = "public/uploads/news/";

        $storeData = new News;
        $storeData->title = $request->title;
        
        $storeData->user_type = implode(',',$request->user_type);
        $storeData->start_date = $request->start_date;
        $storeData->end_date = $request->end_date;
        $storeData->brand = $request->brand ;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . "." . $image->getClientOriginalName();
            $image->move($upload_path, $imageName);
            $storeData->image = $upload_path . $imageName;
        }

        if ($request->hasFile('pdf')) {
            $pdf = $request->file('pdf');
            $pdfName = time() . "." . $pdf->getClientOriginalName();
            $pdf->move($upload_path, $pdfName);
            $storeData->pdf = $upload_path . $pdfName;
        }

        $storeData->save();


         $record = News::find($storeData->id);

        // Convert "1,4" to [1,4]
        $record->user_type = removeQuotes($record->user_type);
        $record->save();
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

    public function update(Request $request, $id)
    {
        $request->validate([
            "title" => "required|string|max:255",
            "start_date" => "required|date",
            "end_date" => "required|date",
            "user_type" => "nullable|array",
            "image" => "nullable|mimes:jpg,jpeg,png,svg,gif|max:10000000",
            "pdf" => "nullable|mimes:doc,docs,png,svg,jpg,excel,csv,pdf|max:10000000",
        
        ]);
        $storeData = News::findOrFail($id);
        $upload_path = "public/uploads/news/";

        $storeData->title = $request->title;
        $storeData->user_type = implode(',',$request->user_type);
        $storeData->start_date = $request->start_date;
        $storeData->end_date = $request->end_date;
        $storeData->brand = $request->brand;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . "." . $image->getClientOriginalName();
            $image->move($upload_path, $imageName);
            $storeData->image = $upload_path . $imageName;
        }

        if ($request->hasFile('pdf')) {
            $pdf = $request->file('pdf');
            $pdfName = time() . "." . $pdf->getClientOriginalName();
            $pdf->move($upload_path, $pdfName);
            $storeData->pdf = $upload_path . $pdfName;
        }

        $storeData->save();


        $record = News::find($id);
        
        $record->user_type = removeQuotes($record->user_type);
        $record->save();
       
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
