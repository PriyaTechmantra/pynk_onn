<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
class CategoryController extends Controller
{
    public function index(Request $request)
    {
        if (!empty($request->term)) 
        {
            $data=Category::where('name',$request->term)->orderBy('position')->paginate(30);
        }else{
            $data=Category::orderBy('position')->paginate(30);
        }
        return view('category.index', compact('data','request'));
    }

    public function create(Request $request)
    {
        return view('category.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            "name" => "required|string|max:255"
        ]);
        $upload_path = "uploads/category/";
        $data = new Category;
        $data->name = $request->name;
        $data->description = $request->description ?? '';
        $data->slug = slugGenerate($request->name,'categories');
       
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
            return redirect()->route('categories.index');
        } else {
            return redirect()->route('categories.create')->withInput($request->all());
        }

    }

    public function show($id)
    {
        $data=Category::where('id',$id)->first();
        return view('category.details',compact('data'));
    }

    public function edit($id)
    {
        $data=Category::findOrfail($id);
        return view('category.edit',compact('data'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            "name" => "required|string|max:255",
            "description" => "nullable|string",
            "icon_path" => "nullable|mimes:jpg,jpeg,png,svg,gif|max:10000000"
        ]);
        $upload_path = "uploads/category/";
        $data = Category::findOrfail($id);
        $data->name = $request->name;
        $data->description = $request->description;
        if($data->name != $request->name){
            $data->slug = slugGenerate($request->name,'categories');
        }
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
            return redirect()->route('categories.index');
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
            return redirect()->route('categories.index');
        } else {
            return redirect()->route('categories.create')->withInput($request->all());
        }
    }
    
    public function csvExport(Request $request)
    {
        if (!empty($request->term)) 
        {
            $data=Category::where('name',$request->term)->orderBy('position')->paginate(30);
        }else{
            $data=Category::orderBy('position')->paginate(30);
        }
        if (count($data) > 0) {
            $delimiter = ",";
            $filename = "Lux-Product-Category-".date('Y-m-d').".csv";

            $f = fopen('php://memory', 'w');

            $fields = array('SR', 'Name', 'Description',  'STATUS', 'DATETIME');
            fputcsv($f, $fields, $delimiter);

            $count = 1;

            foreach($data as $row) {
                $datetime = date('j F, Y', strtotime($row['created_at']));
                $lineData = array(
                    $count,
                    ucwords($row->name),
                    $row->description,
                    ($row->status == 1) ? 'Active' : 'Inactive',
                    $datetime
                );

                fputcsv($f, $lineData, $delimiter);

                $count++;
            }

            fseek($f, 0);

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '";');

            fpassthru($f);
        }
    }


}

