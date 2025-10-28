<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Auth;
use Hash;
class CategoryController extends Controller
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
        // Base query
        $query = Category::query();

        /**
         * STEP 1: Brand filter (1 = ONN, 2 = PYNK, 3 = BOTH)
         */
        if ($request->filled('brand_selection')) {
            $query->where(function ($q) use ($request) {
                if ($request->brand_selection == 3) {
                    // “Both” selected → show ONN (1), PYNK (2), and Both (3)
                    $q->whereIn('categories.brand', [1, 2, 3]);
                } else {
                    // single brand selected → include that + both
                    $q->where('categories.brand', $request->brand_selection)
                    ->orWhere('categories.brand', 3);
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
                        $q->whereIn('categories.brand', [1, 2, 3]);
                    } else {
                        // user has limited brand(s)
                        $q->whereIn('categories.brand', array_merge($userBrandPermissions, [3]));
                    }
                });
            }
        }
        

        if (!empty($request->term)) {
            $query->where('name', 'LIKE', '%' . $request->term . '%');
        }

        

        $data = $query->orderBy('position','desc')->paginate(25);

        return view('category.index', compact('data','request'));
    }

    public function create(Request $request)
    {
        return view('category.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            "title" => "required|string|max:255",
            "description" => "nullable|string",
            "icon_path" => "required|mimes:jpg,jpeg,png,svg,gif|max:10000000",
            "sketch_icon" => "nullable|mimes:jpg,jpeg,png,svg,gif|max:10000000",
            "image_path" => "nullable|mimes:jpg,jpeg,png,svg,gif|max:10000000",
            "banner_image" => "nullable|mimes:jpg,jpeg,png,svg,gif|max:10000000",
        ]);
        $upload_path = "public/uploads/category/";
        $data = new Category;
        $data->name = $request->title;
        $data->description = $request->description;
        $data->brand = $request->brand;

        $colData = Category::select('position')->latest('id')->first();
            if (!empty($colData->position)) {
                $new_position = (int) $colData->position + 1;
            } else {
                $new_position = 1;
            }
        $data->position =$new_position;

        $slug = \Str::slug($request->title, '-');
        $slugExistCount = Category::where('slug', $slug)->count();
        if ($slugExistCount > 0) $slug = $slug . '-' . ($slugExistCount + 1);
        $data->slug = $slug;
       
            if ($request->hasFile('icon_path')) {
                $image = $request->file('icon_path');
                $imageName = time().".".mt_rand().".".$image->getClientOriginalName();
                $image->move($upload_path, $imageName);
                $uploadedImage = $imageName;
                $data->icon_path = $upload_path.$uploadedImage;
            }
            // thumb image
            if($request->hasFile('image_path')){
                $image = $request->file('image_path');
                $imageName = time().".".mt_rand().".".$image->getClientOriginalName();
                $image->move($upload_path, $imageName);
                $uploadedImage = $imageName;
                $data->image_path = $upload_path.$uploadedImage;
            }
            // banner image
            if($request->hasFile('banner_image')){
                $image = $request->file('banner_image');
                $imageName = time().".".mt_rand().".".$image->getClientOriginalName();
                $image->move($upload_path, $imageName);
                $uploadedImage = $imageName;
                $data->banner_image = $upload_path.$uploadedImage;
            }
            // sketch icon
            if($request->hasFile('sketch_icon')){
                $image = $request->file('sketch_icon');
                $imageName = time().".".mt_rand().".".$image->getClientOriginalName();
                $image->move($upload_path, $imageName);
                $uploadedImage = $imageName;
                $data->sketch_icon = $upload_path.$uploadedImage;
            }
            $data->save();
        if ($data) {
            return redirect()->route('categories.index')->with('success', 'Category added successfully.');
        } else {
            return redirect()->route('categories.create')->withInput($request->all());
        }

    }

    public function show($id)
    {
        $data=Category::where('id',$id)->first();
        return view('category.view',compact('data'));
    }

    public function edit($id)
    {
        $data=Category::findOrfail($id);
        return view('category.edit',compact('data'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            "title" => "required|string|max:255",
            "description" => "nullable|string",
            "icon_path" => "nullable|mimes:jpg,jpeg,png,svg,gif|max:10000000",
            "sketch_icon" => "nullable|mimes:jpg,jpeg,png,svg,gif|max:10000000",
            "image_path" => "nullable|mimes:jpg,jpeg,png,svg,gif|max:10000000",
            "banner_image" => "nullable|mimes:jpg,jpeg,png,svg,gif|max:10000000",
        ]);

        $data = Category::findOrfail($id);
        $data->name = $request->title;
        $data->description = $request->description;
        $data->brand = $request->brand;

        if ($data->name != $request->title) {
            $slug = \Str::slug($request['title'], '-');
            $slugExistCount = Category::where('slug', $slug)->count();
            if ($slugExistCount > 0) $slug = $slug . '-' . ($slugExistCount + 1);
            $data->slug = $slug;
        }

        $upload_path = "public/uploads/category/";

        // icon image
        if($request->hasFile('icon_path')){
            $image = $request->file('icon_path');
            $imageName = time().".".mt_rand().".".$image->getClientOriginalName();
            $image->move($upload_path, $imageName);
            $uploadedImage = $imageName;
            $data->icon_path = $upload_path.$uploadedImage;
        }
        // thumb image
        if($request->hasFile('image_path')){
            $image = $request->file('image_path');
            $imageName = time().".".mt_rand().".".$image->getClientOriginalName();
            $image->move($upload_path, $imageName);
            $uploadedImage = $imageName;
            $data->image_path = $upload_path.$uploadedImage;
        }
        // banner image
        if($request->hasFile('banner_image')){
            $image = $request->file('banner_image');
            $imageName = time().".".mt_rand().".".$image->getClientOriginalName();
            $image->move($upload_path, $imageName);
            $uploadedImage = $imageName;
            $data->banner_image = $upload_path.$uploadedImage;
        }
        // sketch icon
        if($request->hasFile('sketch_icon')){
            $image = $request->file('sketch_icon');
            $imageName = time().".".mt_rand().".".$image->getClientOriginalName();
            $image->move($upload_path, $imageName);
            $uploadedImage = $imageName;
            $data->sketch_icon = $upload_path.$uploadedImage;
        }
        $data->save();
        
        if ($data) {
            return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
        } else {
            return redirect()->route('categories.create')->withInput($request->all());
        }
    }

    public function destroy($id)
    {
        $isReferenced = DB::table('products')->where('cat_id', $id)->exists();
    
        if ($isReferenced) {
            return redirect()->route('categories.index')->with('error', 'Category cannot be deleted because it is referenced in another table.');
        }
        $data=Category::destroy($id);
        $data->is_deleted=1;
        $data->save();
        if ($data) {
            return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
        } else {
            return redirect()->route('categories.index')->with('error', 'Failed to delete category.');
        }
    }
   
    public function status(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $status = ( $category->status == 1 ) ? 0 : 1;
        $category->status = $status;
        $category->save();
        if ($category) {
            return redirect()->route('categories.index')->with('success', 'Status changed successfully.');
        } else {
            return redirect()->route('categories.create')->withInput($request->all());
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
        $query = Category::query();

        /**
         * STEP 1: Brand filter (1 = ONN, 2 = PYNK, 3 = BOTH)
         */
        if ($request->filled('brand_selection')) {
            $query->where(function ($q) use ($request) {
                if ($request->brand_selection == 3) {
                    // “Both” selected → show ONN (1), PYNK (2), and Both (3)
                    $q->whereIn('categories.brand', [1, 2, 3]);
                } else {
                    // single brand selected → include that + both
                    $q->where('categories.brand', $request->brand_selection)
                    ->orWhere('categories.brand', 3);
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
                        $q->whereIn('categories.brand', [1, 2, 3]);
                    } else {
                        // user has limited brand(s)
                        $q->whereIn('categories.brand', array_merge($userBrandPermissions, [3]));
                    }
                });
            }
        }
        

        if (!empty($request->term)) {
            $query->where('name', 'LIKE', '%' . $request->term . '%');
        }

        

        $data = $query->orderBy('position','desc')->get();

        $filename = "Product-Category-" . date('Y-m-d') . ".csv";

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

