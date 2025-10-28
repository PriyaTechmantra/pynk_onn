<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Auth;
use Hash;
use Illuminate\Support\Facades\DB;

class CollectionController extends Controller
{
    function __construct()
    {
         $this->middleware('permission:view collection', ['only' => ['index']]);
         $this->middleware('permission:create collection', ['only' => ['create','store']]);
         $this->middleware('permission:update collection', ['only' => ['edit','update']]);
         $this->middleware('permission:delete collection', ['only' => ['destroy']]);
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
        // Base query
        $query = Collection::query();

        /**
         * STEP 1: Brand filter (1 = ONN, 2 = PYNK, 3 = BOTH)
         */
        if ($request->filled('brand_selection')) {
            $query->where(function ($q) use ($request) {
                if ($request->brand_selection == 3) {
                    // “Both” selected → show ONN (1), PYNK (2), and Both (3)
                    $q->whereIn('collections.brand', [1, 2, 3]);
                } else {
                    // single brand selected → include that + both
                    $q->where('collections.brand', $request->brand_selection)
                    ->orWhere('collections.brand', 3);
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
                        $q->whereIn('collections.brand', [1, 2, 3]);
                    } else {
                        // user has limited brand(s)
                        $q->whereIn('collections.brand', array_merge($userBrandPermissions, [3]));
                    }
                });
            }
        }
        

        if (!empty($request->term)) {
            $query->where('name', 'LIKE', '%' . $request->term . '%');
        }

        

        $data = $query->orderBy('position','desc')->paginate(25);

        return view('collection.index', compact('data', 'request'));
    }

    public function create(Request $request)
    {
        return view('collection.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            "title" => "required|string|max:255",
            "description" => "nullable|string",
            "icon_path" => "required|mimes:jpg,jpeg,png,svg,gif|max:10000000",
            "sketch_icon" => "required|mimes:jpg,jpeg,png,svg,gif|max:10000000",
            "image_path" => "required|mimes:jpg,jpeg,png,svg,gif|max:10000000",
            "banner_image" => "required|mimes:jpg,jpeg,png,svg,gif|max:10000000",
        ]);

        $storeData=new Collection();
        $storeData->name=$request->title;
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
        $slug = \Str::slug($request['title'], '-');
        $slugExistCount = Collection::where('slug', $slug)->count();
        if ($slugExistCount > 0) $slug = $slug . '-' . ($slugExistCount + 1);
        $storeData->slug = $slug;

        $upload_path = "public/uploads/collection/";

        if (isset($request['icon_path'])) {
            $image = $request['icon_path'];
            $imageName = time() . "." . $image->getClientOriginalName();
            $image->move($upload_path, $imageName);
            $uploadedImage = $imageName;
            $storeData->icon_path = $upload_path . $uploadedImage;
        }
        if (isset($request['sketch_icon'])) {
            $image = $request['sketch_icon'];
            $imageName = time() . "." . $image->getClientOriginalName();
            $image->move($upload_path, $imageName);
            $uploadedImage = $imageName;
            $storeData->sketch_icon = $upload_path . $uploadedImage;
        }
        if (isset($request['image_path'])) {
            $image = $request['image_path'];
            $imageName = time() . "." . $image->getClientOriginalName();
            $image->move($upload_path, $imageName);
            $uploadedImage = $imageName;
            $storeData->image_path = $upload_path . $uploadedImage;
        }
        if (isset($request['banner_image'])) {
            $image = $request['banner_image'];
            $imageName = time() . "." . $image->getClientOriginalName();
            $image->move($upload_path, $imageName);
            $uploadedImage = $imageName;
            $storeData->banner_image = $upload_path . $uploadedImage;
        }
        $storeData->save();
		
        if ($storeData) {
            return redirect()->route('collections.index')->with('success', 'Collection added successfully.');
        } else {
            return redirect()->route('collections.create')->withInput($request->all());
        }
    }

    public function show($id)
    {
        $data = Collection::where('id',$id)->first();
        return view('collection.view', compact('data'));
    }

   
    public function edit($id)
    {
        $data = Collection::findOrfail($id);
        return view('collection.edit', compact('data'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            "title" => "required|string|max:255",
            "description" => "nullable|string",
            "icon_path" => "nullable|mimes:jpg,jpeg,png,svg,gif|max:10000000",
            "sketch_icon" => "nullable|mimes:jpg,jpeg,png,svg,gif|max:10000000",
            "image_path" => "nullable|mimes:jpg,jpeg,png,svg,gif|max:10000000",
            "banner_image" => "nullable|mimes:jpg,jpeg,png,svg,gif|max:10000000"
        ]);
        $storeData=Collection::findOrfail($id);
        $storeData->name=$request->title;
        $storeData->description=$request->description;
        $storeData->brand =$request->brand;
        
        // slug generate
        if($storeData->name != $request['title']){
            $slug = \Str::slug($request['title'], '-');
            $slugExistCount = RetailerProduct::where('slug', $slug)->count();
            if ($slugExistCount > 0) $slug = $slug . '-' . ($slugExistCount + 1);
            $storeData->slug = $slug;
        }

        $upload_path = "public/uploads/collection/";

        if (isset($request['icon_path'])) {
            $image = $request['icon_path'];
            $imageName = time() . "." . $image->getClientOriginalName();
            $image->move($upload_path, $imageName);
            $uploadedImage = $imageName;
            $storeData->icon_path = $upload_path . $uploadedImage;
        }
        if (isset($request['sketch_icon'])) {
            $image = $request['sketch_icon'];
            $imageName = time() . "." . $image->getClientOriginalName();
            $image->move($upload_path, $imageName);
            $uploadedImage = $imageName;
            $storeData->sketch_icon = $upload_path . $uploadedImage;
        }
        if (isset($request['image_path'])) {
            $image = $request['image_path'];
            $imageName = time() . "." . $image->getClientOriginalName();
            $image->move($upload_path, $imageName);
            $uploadedImage = $imageName;
            $storeData->image_path = $upload_path . $uploadedImage;
        }
        if (isset($request['banner_image'])) {
            $image = $request['banner_image'];
            $imageName = time() . "." . $image->getClientOriginalName();
            $image->move($upload_path, $imageName);
            $uploadedImage = $imageName;
            $storeData->banner_image = $upload_path . $uploadedImage;
        }
        $storeData->save();
		
        if ($storeData) {
            return redirect()->route('collections.index')->with('success', 'Collection updated successfully.');
        } else {
            return redirect()->route('collections.create')->withInput($request->all());
        }
    }

    public function status(Request $request, $id)
    {
        $category = Collection::findOrFail($id);
        $status = ( $category->status == 1 ) ? 0 : 1;
        $category->status = $status;
        $category->save();
        if ($category) {
            return redirect()->route('collections.index')->with('success', 'Status changed successfully.');
        } else {
            return redirect()->route('collections.create')->withInput($request->all());
        }
    }

    public function destroy($id)
    {
        $isReferenced = DB::table('products')->where('collection_id', $id)->exists();
    
        if ($isReferenced) {
            return redirect()->route('collections.index')->with('error', 'Collection cannot be deleted because it is referenced in another table.');
        }

        $data=Collection::destroy($id);
        $data->is_deleted=1;
        $data->save();
        if ($data) {
            return redirect()->route('collections.index')->with('success', 'Collection deleted successfully.');
        } else {
            return redirect()->route('collections.index')->with('error', 'Failed to delete collection.');
        }
    }
    public function csvExport(Request $request)
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
        // Base query
        $query = Collection::query();

        /**
         * STEP 1: Brand filter (1 = ONN, 2 = PYNK, 3 = BOTH)
         */
        if ($request->filled('brand_selection')) {
            $query->where(function ($q) use ($request) {
                if ($request->brand_selection == 3) {
                    // “Both” selected → show ONN (1), PYNK (2), and Both (3)
                    $q->whereIn('collections.brand', [1, 2, 3]);
                } else {
                    // single brand selected → include that + both
                    $q->where('collections.brand', $request->brand_selection)
                    ->orWhere('collections.brand', 3);
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
                        $q->whereIn('collections.brand', [1, 2, 3]);
                    } else {
                        // user has limited brand(s)
                        $q->whereIn('collections.brand', array_merge($userBrandPermissions, [3]));
                    }
                });
            }
        }
        

        if (!empty($request->term)) {
            $query->where('name', 'LIKE', '%' . $request->term . '%');
        }

        

        $data = $query->orderBy('position','desc')->get();

        $filename = "Product-Collection-" . date('Y-m-d') . ".csv";

        return response()->stream(function() use ($data) {
            $f = fopen('php://output', 'w');

            // CSV headers
            fputcsv($f, ['SR', 'Title', 'DATE', 'STATUS']);

            $count = 1;
            foreach ($data as $row) {
                fputcsv($f, [
                    $count++,
                    ucwords($row->name), 
                    'Published: ' . $row->created_at->format('j F, Y'),
                    $row->status == 1 ? 'Active' : 'Inactive',
                ]);
            }

            fclose($f);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
