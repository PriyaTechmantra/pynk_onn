<?php

namespace App\Http\Controllers;

use App\Models\Catalogue;
use Illuminate\Http\Request;
use App\Models\State;
use App\Interfaces\CatalogueInterface;
use App\Models\ProductCatalogue;
use DB;

class CatalogueController extends Controller
{
    public function index(Request $request)
    {
        $term = $request->term;

        $query = ProductCatalogue::query();

        if (!empty($term)) {
            $query->where('title', 'LIKE', '%' . $term . '%');
        }

        $query->latest();

        if ($request->has('export_all')) {
            $count = ProductCatalogue::count();
            $data = $query->paginate($count);
        } else {
            $data = $query->paginate(10);
        }

        return view('catalogue.index', compact('data'));
    }

    public function create()
    {
        $states = State::where('is_deleted', 0)->where('status', 1)->get();
        return view('catalogue.create', compact('states'));
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
        return view('catalogue.edit', compact('data', 'states'));
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

        $state = State::find($request->state);
        $storeData->state = $state ? $state->id : $storeData->state;
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

    public function pdf(Request $request, $id)
    {
        $data = ProductCatalogue::findOrfail($id);
		$image = DB::table('product_catalogues_images')->where('catalogue_id',$data->id)->get();
        return view('catalogue.pdf', compact('data','image'));
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


}
