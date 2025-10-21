<?php

namespace App\Http\Controllers;

use App\Models\Catalogue;
use Illuminate\Http\Request;
use App\Models\State;
use App\Models\Employee;
use App\Interfaces\CatalogueInterface;
use App\Models\ProductCatalogue;
use DB;

class CatalogueController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductCatalogue::query();

        if (!empty($request->term)) {
            $query->where('title', 'LIKE', '%' . $request->term . '%');
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
            "state" => 'nullable|exists:states,id',
            "image" => "required|mimes:jpg,jpeg,png,svg,gif|max:10000000",
            "pdf" => "required|mimes:doc,docs,png,svg,jpg,excel,csv,pdf|max:10000000",
            "brand" => "nullable|array",
        ]);

        $params = $request->except('_token');
         
        $upload_path = "public/uploads/catalogue/";
        $collection = collect($params);

        $storeData = new ProductCatalogue;
        $storeData->title = $collection['title'];
        $storeData->start_date = $collection['start_date'];
        $storeData->end_date = $collection['end_date'];
        $storeData->state = $collection['state'];
        
        $storeData->vp = $collection['vp'];
        $storeData->brand = $request->brand;


        // image image
        $image = $collection['image'];
        $imageName = time() . "." . mt_rand() . "." . $image->getClientOriginalName();
        $image->move($upload_path, $imageName);
        $uploadedImage = $imageName;
        $storeData->image = $upload_path . $uploadedImage;

        // pdf icon
        $pdf = $collection['pdf'];
        $pdfName = time().".".$pdf->getClientOriginalName();
        $pdf->move($upload_path, $pdfName);
        $uploadedPdf = $pdfName;
        $storeData->pdf= $upload_path.$uploadedPdf;

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
        return view('catalogue.view', compact('data'));
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
            "state" => 'nullable|exists:states,id',
            "image" => "nullable|mimes:jpg,jpeg,png,svg,gif|max:10000000",
            "pdf" => "nullable|mimes:doc,docs,png,svg,jpg,excel,csv,pdf|max:10000000",
            "brand" => "nullable|array",
        ]);

        $params = $request->except('_token');

        $upload_path = "public/uploads/catalogue/";
        $storeData = ProductCatalogue::findOrFail($id);
        $collection = collect($params);

        $storeData->title = $collection['title'];
        $storeData->start_date = $collection['start_date'];
        $storeData->end_date = $collection['end_date'];
        $storeData->state = $collection['state'];

        $storeData->vp = $collection['vp'];
        $storeData->brand =$request->brand;


        if (isset($params['image'])) {
            $image = $collection['image'];
            $imageName = time() . "." . mt_rand() . "." . $image->getClientOriginalName();
            $image->move($upload_path, $imageName);
            $uploadedImage = $imageName;
            $storeData->image = $upload_path . $uploadedImage;
        }

        if (isset($params['pdf'])) {
            $image = $collection['pdf'];
            $imageName = time() . "." . mt_rand() . "." . $image->getClientOriginalName();
            $image->move($upload_path, $imageName);
            $uploadedImage = $imageName;
            $storeData->pdf = $upload_path . $uploadedImage;
        }
        $storeData->save();

        if ($storeData) {
            return redirect('/catalogues');
        } else {
            return redirect('/catalogue/create')->withInput($request->all());
        }
    }


    public function destroy(Request $request, $id)
    {
        ProductCatalogue::destroy($id);

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
            $brands = explode(',', $request->brand_selection);
            $query->where(function ($q) use ($brands) {
                foreach ($brands as $brand) {
                    $q->orWhereJsonContains('brand', trim($brand));
                }
            });
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
        $headers = ['TITLE', 'START DATE', 'END DATE', 'STATE', 'VP','STATUS'];
        fputcsv($f, $headers, $delimiter);

         $stateName = '';
        

        $count = 1;

        foreach ($data as $row) {
            $stateNames = [];
            if ($row->state && is_array($row->state)) {
                $stateNames = State::whereIn('id', $row->state)->pluck('name')->toArray();
            }
            $stateName = implode(', ', $stateNames);

            $lineData = [
                $row->title ?? '',
                $row->start_date ? date('d M Y', strtotime($row->start_date)) : '',
                $row->end_date ? date('d M Y', strtotime($row->end_date)) : '',
                $stateName,
                $row->vp ?? '',
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
