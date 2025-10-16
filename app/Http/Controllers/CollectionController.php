<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    function __construct()
    {
         $this->middleware('permission:view collection', ['only' => ['index']]);
         $this->middleware('permission:create collection', ['only' => ['create','store']]);
         $this->middleware('permission:update collection', ['only' => ['edit','update']]);
         $this->middleware('permission:delete collection', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Collection::query();

        if (!empty($request->term)) {
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

        $data = $query->where('is_deleted',0)->orderBy('position')->paginate(25);

        return view('collection.index', compact('data', 'request'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        return view('collection.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            "name" => "required|string|max:255",
            "description" => "nullable|string",
            "icon_path" => "required|mimes:jpg,jpeg,png,svg,gif|max:10000000",
            "sketch_icon" => "required|mimes:jpg,jpeg,png,svg,gif|max:10000000",
            "image_path" => "required|mimes:jpg,jpeg,png,svg,gif|max:10000000",
            "banner_image" => "required|mimes:jpg,jpeg,png,svg,gif|max:10000000"
        ]);
        $storeData=new Collection();
        $storeData->name=$request->name;
        $storeData->description=$request->description;
        $colData = Collection::select('position')->latest('id')->first();
        
            if (!empty($colData->position)) {
                $new_position = (int) $colData->position + 1;
            } else {
                $new_position = 1;
            }
        $storeData->position =$new_position;
        $storeData->brand =$request->brand;
        
        // slug generate
        $slug = \Str::slug($request['name'], '-');
        $slugExistCount = RetailerProduct::where('slug', $slug)->count();
        if ($slugExistCount > 0) $slug = $slug . '-' . ($slugExistCount + 1);
        $storeData->slug = $slug;
        if (isset($request['icon_path'])) {
            $upload_path = "public/uploads/collection/";
            $image = $request['icon_path'];
            $imageName = time() . "." . $image->getClientOriginalName();
            $image->move($upload_path, $imageName);
            $uploadedImage = $imageName;
            $storeData->icon_path = $upload_path . $uploadedImage;
        }
        if (isset($request['sketch_icon'])) {
            $upload_path = "public/uploads/collection/";
            $image = $request['sketch_icon'];
            $imageName = time() . "." . $image->getClientOriginalName();
            $image->move($upload_path, $imageName);
            $uploadedImage = $imageName;
            $storeData->sketch_icon = $upload_path . $uploadedImage;
        }
        if (isset($request['image_path'])) {
            $upload_path = "public/uploads/collection/";
            $image = $request['image_path'];
            $imageName = time() . "." . $image->getClientOriginalName();
            $image->move($upload_path, $imageName);
            $uploadedImage = $imageName;
            $storeData->image_path = $upload_path . $uploadedImage;
        }
        if (isset($request['banner_image'])) {
            $upload_path = "public/uploads/collection/";
            $image = $request['banner_image'];
            $imageName = time() . "." . $image->getClientOriginalName();
            $image->move($upload_path, $imageName);
            $uploadedImage = $imageName;
            $storeData->banner_image = $upload_path . $uploadedImage;
        }
        $storeData->save();
		
        if ($storeData) {
            return redirect()->route('collection.index');
        } else {
            return redirect()->route('collection.create')->withInput($request->all());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Collection $collection)
    {
        $data = Collection::where('id',$id)->first();
        return view('collection.view', compact('data'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Collection $collection)
    {
        $data = Collection::findOrfail($id);
        return view('collection.edit', compact('data'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Collection $collection)
    {
        $request->validate([
            "name" => "required|string|max:255",
            "description" => "nullable|string",
            "icon_path" => "required|mimes:jpg,jpeg,png,svg,gif|max:10000000",
            "sketch_icon" => "required|mimes:jpg,jpeg,png,svg,gif|max:10000000",
            "image_path" => "required|mimes:jpg,jpeg,png,svg,gif|max:10000000",
            "banner_image" => "required|mimes:jpg,jpeg,png,svg,gif|max:10000000"
        ]);
        $storeData=Collection::findOrfail($id);
        $storeData->name=$request->name;
        $storeData->description=$request->description;
        
        $storeData->brand =$request->brand;
        
        // slug generate
        if($storeData->name != $request['name']){
            $slug = \Str::slug($request['name'], '-');
            $slugExistCount = RetailerProduct::where('slug', $slug)->count();
            if ($slugExistCount > 0) $slug = $slug . '-' . ($slugExistCount + 1);
            $storeData->slug = $slug;
        }
        if (isset($request['icon_path'])) {
            $upload_path = "public/uploads/collection/";
            $image = $request['icon_path'];
            $imageName = time() . "." . $image->getClientOriginalName();
            $image->move($upload_path, $imageName);
            $uploadedImage = $imageName;
            $storeData->icon_path = $upload_path . $uploadedImage;
        }
        if (isset($request['sketch_icon'])) {
            $upload_path = "public/uploads/collection/";
            $image = $request['sketch_icon'];
            $imageName = time() . "." . $image->getClientOriginalName();
            $image->move($upload_path, $imageName);
            $uploadedImage = $imageName;
            $storeData->sketch_icon = $upload_path . $uploadedImage;
        }
        if (isset($request['image_path'])) {
            $upload_path = "public/uploads/collection/";
            $image = $request['image_path'];
            $imageName = time() . "." . $image->getClientOriginalName();
            $image->move($upload_path, $imageName);
            $uploadedImage = $imageName;
            $storeData->image_path = $upload_path . $uploadedImage;
        }
        if (isset($request['banner_image'])) {
            $upload_path = "public/uploads/collection/";
            $image = $request['banner_image'];
            $imageName = time() . "." . $image->getClientOriginalName();
            $image->move($upload_path, $imageName);
            $uploadedImage = $imageName;
            $storeData->banner_image = $upload_path . $uploadedImage;
        }
        $storeData->save();
		
        if ($storeData) {
            return redirect()->route('collection.index');
        } else {
            return redirect()->route('collection.create')->withInput($request->all());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Collection $collection)
    {
        $isReferenced = DB::table('products')->where('collection_id', $id)->exists();
    
        if ($isReferenced) {
            return redirect()->route('collections.index')->with('error', 'Collection cannot be deleted because it is referenced in another table.');
        }
        $data=Collection::findOrfail($id);
        $data->is_deleted=1;
        $data->save();
        if ($data) {
            return redirect()->route('collections.index')->with('success', 'Collection deleted successfully.');
        } else {
            return redirect()->route('collections.index')->with('error', 'Failed to delete collection.');
        }
    }
}
