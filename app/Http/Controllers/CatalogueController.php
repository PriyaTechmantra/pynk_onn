<?php

namespace App\Http\Controllers;

use App\Models\Catalogue;
use Illuminate\Http\Request;
use App\Models\State;
use App\Models\Employee;
use App\Interfaces\CatalogueInterface;
use App\Models\ProductCatalogue;
use DB;
use Auth;
class CatalogueController extends Controller
{

    function __construct()
    {
         $this->middleware('permission:view catalogue', ['only' => ['index','show']]);
         $this->middleware('permission:create catalogue', ['only' => ['create','store']]);
         $this->middleware('permission:update catalogue', ['only' => ['update','edit']]);
         $this->middleware('permission:delete catalogue', ['only' => ['destroy']]);
    }
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
        $query = ProductCatalogue::query();
        
        /**
         * STEP 1: Brand filter (1 = ONN, 2 = PYNK, 3 = BOTH)
         */
        if ($request->filled('brand_selection')) {
            $query->where(function ($q) use ($request) {
                if ($request->brand_selection == 3) {
                    // “Both” selected → show ONN (1), PYNK (2), and Both (3)
                    $q->whereIn('product_catelogues.brand', [1, 2, 3]);
                } else {
                    // single brand selected → include that + both
                    $q->where('product_catelogues.brand', $request->brand_selection)
                    ->orWhere('product_catelogues.brand', 3);
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
                        $q->whereIn('product_catelogues.brand', [1, 2, 3]);
                    } else {
                        // user has limited brand(s)
                        $q->whereIn('product_catelogues.brand', array_merge($userBrandPermissions, [3]));
                    }
                });
            }
        }
        if (!empty($request->term)) {
            $query->where('title', 'LIKE', '%' . $request->term . '%');
        }

        

        $query->latest(); 

        if ($request->has('export_all')) {
            $count = ProductCatalogue::count();
            $data = $query->paginate($count);
        } else {
            $data = $query->paginate(10);
        }

        return view('catalogue.index', compact('data', 'request'));
    }


    public function create()
    {
        $states = State::where('is_deleted', 0)->where('status', 1)->get();
        $vps = Employee::where('type', 1)->where('status', 1)->get(); 
        return view('catalogue.create', compact('states', 'vps'));
    }

    public function store(Request $request)
    {
        $request->validate([
            "title" => "required|string|max:255",
            "start_date" => "nullable|date",
            "end_date" => "nullable|date",
            "state" => "required|array",
            "vp" => "nullable|array",
            "image" => "required|mimes:jpg,jpeg,png,svg,gif|max:10000000",
            "pdf" => "nullable|mimes:doc,docs,png,svg,jpg,excel,csv,pdf|max:10000000",
        ]);

        $upload_path = "public/uploads/catalogue/";

        $storeData = new ProductCatalogue;
        $storeData->title = $request->title;
        $storeData->start_date = $request->start_date;
        $storeData->end_date = $request->end_date;
        if(!empty($request->state)){
            $storeData->state_id = implode(',',$request->state);
        }
        if(!empty($request->vp)){
            $storeData->vp_id = implode(',',$request->vp);
        }
        if(empty($request->brand)){
            $user = auth()->user();
            $userBrands = DB::table('user_permission_categories')
                ->where('user_id', Auth::id())
                ->pluck('brand')
                ->toArray();
        
            $brandsToShow = [];

            if (in_array(3, $userBrands) || (in_array(1, $userBrands) && in_array(2, $userBrands))) {
                // Both brands access
                $brandsToShow = 3;
            } elseif (in_array(1, $userBrands)) {
                $brandsToShow = 1;
            } elseif (in_array(2, $userBrands)) {
                $brandsToShow = 2;
            }
            $storeData->brand = $brandsToShow;
        }else{
           $storeData->brand = $request->brand;
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . "." . mt_rand() . "." . $image->getClientOriginalName();
            $image->move($upload_path, $imageName);
            $storeData->image = $upload_path . $imageName;
        }

        if ($request->hasFile('pdf')) {
            $pdf = $request->file('pdf');
            $pdfName = time() . "." . mt_rand() . "." . $pdf->getClientOriginalName();
            $pdf->move($upload_path, $pdfName);
            $storeData->pdf = $upload_path . $pdfName;
        }

        $storeData->save();

        if ($storeData) {
            return redirect('/catalogues')->with('success', 'Catalogue saved successfully!');
        } else {
            return redirect('/catalogues/create')->withInput($request->all());
        }
    }

    public function show($id)
    {
        $data = ProductCatalogue::findOrFail($id);

        $stateNames = [];
        if (is_array($data->state)) {
            $stateNames = State::whereIn('id', $data->state)->pluck('name')->toArray();
        }

        $vpNames = [];
        if (is_array($data->vp)) {
            $vpNames = Employee::whereIn('id', $data->vp)->pluck('name')->toArray();
        }

        return view('catalogue.view', compact('data', 'stateNames', 'vpNames'));
    }


    public function edit($id)
    {
        $data = ProductCatalogue::findOrFail($id);
        $states = State::where('is_deleted', 0)->where('status', 1)->get();
        $vps = Employee::where('type', 1)->where('status', 1)->get();

        return view('catalogue.edit', compact('data', 'states', 'vps'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            "title" => "required|string|max:255",
            "start_date" => "nullable|date",
            "end_date" => "nullable|date",
            "state" => "required|array",
            "vp" => "required|array",
            "image" => "nullable|mimes:jpg,jpeg,png,svg,gif|max:10000000",
            "pdf" => "nullable|mimes:doc,docs,png,svg,jpg,excel,csv,pdf|max:10000000",
            // "brand" => "nullable|array",
        ]);

        $upload_path = "public/uploads/catalogue/";
        $storeData = ProductCatalogue::findOrFail($id);
        $oldData = $storeData->replicate(); // clone old values before update
        $storeData->title = $request->title;
        $storeData->start_date = $request->start_date;
        $storeData->end_date = $request->end_date;
        $storeData->state_id = $request->state;

        $storeData->vp_id = $request->vp;
        //$storeData->brand =$request->brand;
        if(empty($request->brand)){
            $user = auth()->user();
            $userBrands = DB::table('user_permission_categories')
                ->where('user_id', Auth::id())
                ->pluck('brand')
                ->toArray();
        
            $brandsToShow = [];

            if (in_array(3, $userBrands) || (in_array(1, $userBrands) && in_array(2, $userBrands))) {
                // Both brands access
                $brandsToShow = 3;
            } elseif (in_array(1, $userBrands)) {
                $brandsToShow = 1;
            } elseif (in_array(2, $userBrands)) {
                $brandsToShow = 2;
            }
            $storeData->brand = $brandsToShow;
        }else{
           $storeData->brand = $request->brand;
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . "." . mt_rand() . "." . $image->getClientOriginalName();
            $image->move($upload_path, $imageName);
            $storeData->image = $upload_path . $imageName;
        }

        if ($request->hasFile('pdf')) {
            $pdf = $request->file('pdf');
            $pdfName = time() . "." . mt_rand() . "." . $pdf->getClientOriginalName();
            $pdf->move($upload_path, $pdfName);
            $storeData->pdf = $upload_path . $pdfName;
        }
        $storeData->save();
        // ✅ Compare old vs new and log only changed fields
        $changedFields = $storeData->getChanges(); // only changed attributes

        foreach ($changedFields as $field => $newValue) {
            if (in_array($field, ['updated_at'])) continue; // skip timestamps

            $oldValue = $oldData->$field ?? null;

            DB::table('edit_logs')->insert([
                'table_name' => 'product_catelogues',
                'record_id' => $storeData->id,
                'field' => $field,
                'old_value' => $oldValue,
                'new_value' => $newValue,
                'action' => 'updated',
                'updated_by' => Auth::id(),
                'created_at' => now(),
            ]);
        }
        if ($storeData) {
            return redirect('/catalogues');
        } else {
            return redirect('/catalogue/create')->withInput($request->all());
        }
    }


    public function destroy(Request $request, $id)
    {
        
        $data = ProductCatalogue::findOrfail($id);
        $data->is_deleted=1;
        $data->save();

        // ✅ Log the delete action only (no old/new value)
        DB::table('edit_logs')->insert([
            'table_name' => 'product_catelogues',
            'record_id' => $data->id,
            'action' => 'deleted',
            'updated_by' => Auth::id(),
            'created_at' => now(),
        ]);
        return redirect('/catalogues');
    }

     public function status(Request $request, $id)
    {
        $storeData = ProductCatalogue::findOrFail($id);
        $status = ($storeData->status == 1) ? 0 : 1;
        $storeData->status = $status;
        $storeData->save();

         if ($storeData) {
            return redirect('/catalogues')->with('success', 'Status updated successfully!');
        } else {
            return redirect('/catalogues/create')->withInput($request->all());
        }
    }

    public function exportCSV(Request $request)
    {
        $query = ProductCatalogue::query();

        // Keyword search
        if (!empty($request->keyword)) {
            $query->where('title', 'LIKE', '%' . $request->keyword . '%');
        }

        if (!empty($request->brand_selection)) {
            $brand = $request->brand_selection;

            if ($brand == '1') {
                $query->whereIn('brand', [1, 3]);
            } elseif ($brand == '2') {
                $query->whereIn('brand', [2, 3]);
            } elseif ($brand == '3') {
                $query->where('brand', 3);
            }
        }

        $data = $query->orderBy('id', 'desc')->get();

        if ($data->isEmpty()) {
            return redirect()->back()->with('message', 'No data found for export.');
        }

        $delimiter = ",";
        $filename = "catalogue-report" . date('Y-m-d') . ".csv";

        // Open memory stream
        $f = fopen('php://memory', 'w');

        // CSV headers
        $headers = ['TITLE', 'START DATE', 'END DATE', 'STATUS'];
        fputcsv($f, $headers, $delimiter);

         $stateName = '';
        

        $count = 1;

        foreach ($data as $row) {
            // $stateNames = [];
            // if ($row->state && is_array($row->state)) {
            //     $stateNames = State::whereIn('id', $row->state)->pluck('name')->toArray();
            // }
            // $stateName = implode(', ', $stateNames);

            // $vpNames = [];
            // if ($row->vp && is_array($row->vp)) {
            //     $vpNames = Employee::whereIn('id', $row->vp)->pluck('name')->toArray();
            // }
            // $vpName = implode(', ', $vpNames);

            $lineData = [
                $row->title ?? '',
                $row->start_date ? date('d M Y', strtotime($row->start_date)) : '',
                $row->end_date ? date('d M Y', strtotime($row->end_date)) : '',
                ($row->status == 1) ? 'Active' : 'Inactive',
            ];

            fputcsv($f, $lineData, $delimiter);
            $count++;
        }

        fseek($f, 0);

        // Output headers for download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');

        fpassthru($f);
        exit;
    }


}
