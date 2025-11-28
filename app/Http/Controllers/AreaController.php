<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\RedirectResponse;
use DB;
use Auth;
use Illuminate\Validation\Rule;
class AreaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
         $this->middleware('permission:view area', ['only' => ['index']]);
         $this->middleware('permission:create area', ['only' => ['create','store']]);
         $this->middleware('permission:update area', ['only' => ['edit','update']]);
         $this->middleware('permission:delete area', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request): View
    {
        if (!empty($request->term)) 
        {
          $data = Area::where('name',$request->term)->where('is_deleted',0)->latest()->paginate(25);
        }else{
             $data = Area::where('is_deleted',0)->latest()->paginate(25);
        }
        return view('area.index',compact('data','request'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(): View
    {
        $state=State::where('status',1)->where('is_deleted',0)->orderby('name')->get();
        return view('area.create',compact('state'));
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required',
                Rule::unique('areas')
                    ->where('state_id', $request->state_id)
                    ->where('is_deleted', 0)  // ignore deleted rows
            ],
            'state_id' => 'required|integer',
        ]);

        // ✅ Only fill allowed fields (avoids mass-assignment vulnerability)
        $data = Area::create([
            'name' => $request->name,
            'state_id' => $request->state_id,
        ]);

        // ✅ Log the create action (no old/new values)
        DB::table('edit_logs')->insert([
            'table_name' => 'areas',
            'record_id'  => $data->id,
            'action'     => 'created',
            'updated_by' => Auth::id(),
            'created_at' => now(),
        ]);

        return redirect()
            ->route('areas.index')
            ->with('success', 'Area created successfully.');
    }

    
    /**
     * Display the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
   
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit($id): View
    {
        $data = Area::find($id);
        $state=State::where('status',1)->where('is_deleted',0)->orderby('name')->get();
        return view('area.edit',compact('data','state'));
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'name' => 'required',
            'state_id' => 'required'
        ]);

        // ✅ Get existing record before updating
        $data = Area::findOrFail($id);
        $oldData = $data->replicate(); // clone old values before update

        // ✅ Update fields
        $data->name = $request->name;
        $data->state_id = $request->state_id;
        $data->save();

        // ✅ Compare old vs new and log only changed fields
        $changedFields = $data->getChanges(); // only changed attributes

        foreach ($changedFields as $field => $newValue) {
            if (in_array($field, ['updated_at'])) continue; // skip timestamps

            $oldValue = $oldData->$field ?? null;

            DB::table('edit_logs')->insert([
                'table_name' => 'areas',
                'record_id' => $data->id,
                'field' => $field,
                'old_value' => $oldValue,
                'new_value' => $newValue,
                'action' => 'updated',
                'updated_by' => Auth::id(),
                'created_at' => now(),
            ]);
        }

        return redirect()->route('areas.index')
            ->with('success', 'Area updated successfully');
    }

    
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy($id): RedirectResponse
    {
        $isReferenced = DB::table('stores')->where('area_id', $id)->exists();
        $isTReferenced = DB::table('teams')->where('area_id', $id)->exists();
        $isDReferenced = DB::table('distributors')->where('area_id', $id)->exists();
        $isEReferenced = DB::table('employees')->where('city', $id)->exists();
        if ($isReferenced || $isDReferenced || $isEReferenced || $isTReferenced) {
            return redirect()->back()->with('failure', 'Area cannot be deleted because it is referenced in another table.');
        }
        $data = Area::findOrfail($id);
        $data->is_deleted=1;
        $data->save();

         // ✅ Log the delete action only (no old/new value)
        DB::table('edit_logs')->insert([
            'table_name' => 'areas',
            'record_id' => $data->id,
            'action' => 'deleted',
            'updated_by' => Auth::id(),
            'created_at' => now(),
        ]);
        return redirect()->route('areas.index')
                        ->with('success','Area deleted successfully');
    }


    public function status($id): RedirectResponse
    {
        $data = Area::find($id);
        $status = ($data->status == 1) ? 0 : 1;
        $data->status = $status;
        $data->save();
    
        return redirect()->route('areas.index')
                        ->with('success','Area status changed successfully');
    }


    //bulk upload
    public function bulkUpload(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt|mimetypes:text/csv,text/plain,application/csv,application/vnd.ms-excel|max:50000',
        ], [
            'file.mimes' => 'Please upload a valid CSV file.',
            'file.mimetypes' => 'Please upload a valid CSV file with the correct format.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        if (!$request->hasFile('file')) {
            return redirect()->back()->with('error', 'No file found.');
        }

        $file = $request->file('file');
        $filename = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());
        $fileSize = $file->getSize();

        $valid_extension = ["csv"];
        $maxFileSize = 50097152; // 50MB
        $errors = [];
        $successCount = 0;

        if (!in_array($extension, $valid_extension)) {
            return redirect()->back()->with('error', 'Invalid file type. Please upload a CSV file.');
        }

        if ($fileSize > $maxFileSize) {
            return redirect()->back()->with('error', 'File too large. Must be less than 50MB.');
        }

        // ✅ Upload to storage
        $location = public_path('uploads/csv');
        if (!file_exists($location)) {
            mkdir($location, 0755, true);
        }

        $file->move($location, $filename);
        $filepath = $location . '/' . $filename;

        // ✅ Open and process CSV
        if (($handle = fopen($filepath, "r")) !== false) {
            $i = 0;
            while (($filedata = fgetcsv($handle, 10000, ",")) !== false) {
                if ($i == 0) { // Skip header
                    $i++;
                    continue;
                }

                $rowData = [
                    'name' => trim($filedata[0] ?? ''),
                    'state_id' => trim($filedata[1] ?? ''),
                ];

                // Validate row data
                $rowValidator = Validator::make($rowData, [
                    'name' => 'required|string|max:255|unique:areas,name',
                    'state_id' => 'required|string',
                ]);

                if ($rowValidator->fails()) {
                    $errors[$i] = $rowValidator->errors()->all();
                } else {
                    $state = State::where('name', $rowData['state_id'])->first();

                    if ($state) {
                        $data = Area::create([
                            'name' => $rowData['name'],
                            'state_id' => $state->id,
                            'status' => 1,
                            'is_deleted' => 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        // ✅ Log create action
                        DB::table('edit_logs')->insert([
                            'table_name' => 'areas',
                            'record_id'  => $data->id,
                            'action'     => 'created',
                            'updated_by' => Auth::id(),
                            'created_at' => now(),
                        ]);

                        $successCount++;
                    } else {
                        $errors[$i][] = "State '{$rowData['state_id']}' not found.";
                    }
                }

                $i++;
            }
            fclose($handle);
        }

        // ✅ Handle results
        if (!empty($errors)) {
            return redirect()->back()->with(['csv_errors' => $errors]);
        }

        return redirect()->back()->with('success', "CSV import complete. Total entries created: {$successCount}");
    }



    //export

    public function areaExport(Request $request)
	{
       
        if (!empty($request->term)) 
        {
          $data = Area::where('name',$request->term)->where('is_deleted',0)->latest()->get();
        }else{
             $data = Area::where('is_deleted',0)->latest()->get();
        }
        if (count($data) > 0) {
            $delimiter = ","; 
            $filename = "areas.csv"; 

            // Create a file pointer 
            $f = fopen('php://memory', 'w'); 

            // Set column headers 
            // $fields = array('SR', 'QRCODE TITLE','CODE','DISTRIBUTOR','ASE','STORE NAME','STORE MOBILE','STORE EMAIL','STORE STATE','STORE ADDRESS','POINTS','DATE'); 
            $fields = array('SR', 'Name','State','Status','DATE'); 
            fputcsv($f, $fields, $delimiter); 

            $count = 1;

            foreach($data as $row) {
                $datetime = date('j F, Y h:i A', strtotime($row['created_at']));
				

                $lineData = array(
                    $count,
					$row['name'] ?? 'NA',
                    $row['state']['name'] ?? 'NA',
					
					($row->status == 1) ? 'active' : 'inactive',
					$datetime,
                );

                fputcsv($f, $lineData, $delimiter);

                $count++;
            }

            // Move back to beginning of file
            fseek($f, 0);

            // Set headers to download file rather than displayed
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '";');

            //output all remaining data on a file pointer
            fpassthru($f);
        }
	}


    //area list state wise

    public function areaStateWise(Request $request,$id)
    {
         $stateName=State::where('id',$id)->first();
         $region = Area::where('state_id',$stateName->id)->get();
         $resp = [
             'state' => $stateName->name,
             'area' => [],
         ];
 
         foreach($region as $area) {
             $resp['area'][] = [
                 'area_id' => $area->id,
                 'area' => $area->name,
             ];
         }
       
         return response()->json(['error' => false, 'resp' => 'State wise area list', 'data' => $resp]);
    }

}
