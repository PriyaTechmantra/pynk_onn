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

        $data = $query->groupby('title')->orderByDesc('id')->paginate(25);

        return view('news.index', compact('data', 'request'));
    }

    
    public function create()
    {
        return view('news.create');
        
    }

    public function store(Request $request)
    {
        //dd($request->all());
        $request->validate([
            "title" => "required|string|max:255",
            "start_date" => "required|date",
            "end_date" => "required|date",
            "user_type" => "required|array", // Ensure user_type is an array
            "image" => "required|mimes:jpg,jpeg,png,svg,gif|max:10240",
            "pdf" => "required|mimes:doc,docx,png,svg,jpg,xls,xlsx,csv,pdf|max:10240",
        ]);

        $upload_path = "public/uploads/news/";
        $imagePath = null;
        $pdfPath = null;

        // 1. Handle File Uploads first (do it once, outside the loop)
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . "_" . $image->getClientOriginalName();
            $image->move($upload_path, $imageName);
            $imagePath = $upload_path . $imageName;
        }

        if ($request->hasFile('pdf')) {
            $pdf = $request->file('pdf');
            $pdfName = time() . "_" . $pdf->getClientOriginalName();
            $pdf->move($upload_path, $pdfName);
            $pdfPath = $upload_path . $pdfName;
        }

        // 2. Loop through each user_type and create a NEW record
        foreach ($request->user_type as $type) {
            $storeData = new News;
            $storeData->title = $request->title;
            $storeData->user_type = $type; // Saves just "1", then "2", etc.
            $storeData->start_date = $request->start_date;
            $storeData->end_date = $request->end_date;
            $storeData->brand = $request->brand;
            $storeData->image = $imagePath;
            $storeData->pdf = $pdfPath;
            $storeData->save();
        }

        return redirect('/news')->with('success', 'News items added successfully for all types!');
    }


    public function show($id)
    {
        $data = News::findOrFail($id);
        return view('news.view', compact('data'));
    }

    public function edit($id)
    {
        $data = News::findOrFail($id);

        // Fetch all user_types for records with the same title (or shared group_id)
        $allRelatedUserTypes = News::where('title', $data->title)
                                    ->where('start_date', $data->start_date)
                                    ->pluck('user_type')
                                    ->toArray();
        return view('news.edit', compact('data', 'allRelatedUserTypes'));
    }

   public function update(Request $request, $id)
{
    $request->validate([
        "title" => "required|string|max:255",
        "start_date" => "required|date",
        "end_date" => "required|date",
        "user_type" => "required|array",
        "image" => "nullable|mimes:jpg,jpeg,png,svg,gif|max:10000",
        "pdf" => "nullable|mimes:doc,docx,pdf|max:10000",
    ]);

    // 1. Get the current record to keep track of existing files
    $currentRecord = News::findOrFail($id);
    $upload_path = "public/uploads/news/";

    // 2. Prepare File Paths (Use existing if no new file is uploaded)
    $imagePath = $currentRecord->image;
    $pdfPath = $currentRecord->pdf;

    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $imageName = time() . "_" . $image->getClientOriginalName();
        $image->move($upload_path, $imageName);
        $imagePath = $upload_path . $imageName;
    }

    if ($request->hasFile('pdf')) {
        $pdf = $request->file('pdf');
        $pdfName = time() . "_" . $pdf->getClientOriginalName();
        $pdf->move($upload_path, $pdfName);
        $pdfPath = $upload_path . $pdfName;
    }

    // 3. DELETE old records for this specific news item
    // We target all rows that share the same title and start_date 
    // (Or use a group_id if you added one)
    News::where('title', $currentRecord->title)
        ->where('start_date', $currentRecord->start_date)
        ->delete();

    // 4. CREATE new separate records for only the selected types
    foreach ($request->user_type as $type) {
        $newData = new News;
        $newData->title = $request->title;
        $newData->user_type = $type; 
        $newData->start_date = $request->start_date;
        $newData->end_date = $request->end_date;
        $newData->brand = $request->brand;
        $newData->image = $imagePath;
        $newData->pdf = $pdfPath;
        $newData->status = 1;
        $newData->save();
    }

    return redirect('/news')->with('success', 'News updated and synced successfully!');
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
