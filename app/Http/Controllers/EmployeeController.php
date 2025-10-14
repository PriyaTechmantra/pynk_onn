<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\State;
use App\Models\Area;
use App\Models\UserArea;
use App\Models\Team;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use Auth;
use DB;
use Hash;
class EmployeeController extends Controller
{
     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
         $this->middleware('permission:view employee', ['only' => ['index','show']]);
         $this->middleware('permission:create employee', ['only' => ['create','store']]);
         $this->middleware('permission:update employee', ['only' => ['update','edit']]);
         $this->middleware('permission:delete employee', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request): View
{
    $user_type   = $request->type ?? '';
    $state       = $request->state ?? '';
    $area        = $request->area ?? '';
    $keyword     = $request->keyword ?? '';
    $brandFilter = $request->brand ?? '';

    $query = Employee::query();

    // Filters for type, state, area, keyword
    $query->when($user_type, fn($q) => $q->where('type', $user_type));
    $query->when($state, fn($q) => $q->where('state', $state));
    $query->when($area, fn($q) => $q->where('city', $area));

    $query->when($keyword, function($q) use ($keyword) {
        $q->where(function($inner) use ($keyword) {
            $inner->where('name', 'like', '%'.$keyword.'%')
                  ->orWhere('fname', 'like', '%'.$keyword.'%')
                  ->orWhere('lname', 'like', '%'.$keyword.'%')
                  ->orWhere('mobile', 'like', '%'.$keyword.'%')
                  ->orWhere('employee_id', 'like', '%'.$keyword.'%')
                  ->orWhere('email', 'like', '%'.$keyword.'%');
        });
    });

    // Get logged-in user's accessible brands
    $userId = auth()->id();
    $accessibleBrands = DB::table('user_permission_categories')
        ->where('user_id', $userId)
        ->pluck('brand')
        ->unique()
        ->toArray(); // e.g. [1], [2], [1,2]
    if (in_array(3, $accessibleBrands)) {
        $accessibleBrands = [1, 2];
    }
    
    // Brand filtering logic
    if ($brandFilter) {
        if ($brandFilter === 'All') {
            // "All" → show employees only from brands user has access to
            $query->whereHas('permissions', function($q) use ($accessibleBrands) {
                $q->where(function($inner) use ($accessibleBrands) {
                    $inner->whereIn('brand', $accessibleBrands)
                        ->orWhere('brand', 3); // include Both
                });
            });
        } else {
            // Specific brand → show only if user has access
            if (in_array($brandFilter, $accessibleBrands)|| in_array(3, $accessibleBrands)) {
                $query->whereHas('permissions', fn($q) => $q->where('brand', $brandFilter));
            } else {
                // If user tries to access brand they don’t have → return empty
                $query->whereRaw('1=0');
            }
        }
    } else {
        // First page load → default to logged-in user's permitted brands
        $query->whereHas('permissions', function($q) use ($accessibleBrands) {
            $q->where(function($inner) use ($accessibleBrands) {
                $inner->whereIn('brand', $accessibleBrands)
                    ->orWhere('brand', 3); // include Both
            });
        });
        
    }
    
    $data = $query->where('is_deleted', 0)->with('stateDetail', 'area')
                  ->latest('id')
                  ->paginate(25);
    
    $state = State::where('status', 1)
        ->where('is_deleted', 0)
        ->orderBy('name')
        ->get();

    return view('employee.index', compact('data', 'request', 'state'))
        ->with('i', (request()->input('page', 1) - 1) * 5);
}

    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request): View
    {
        $state = State::where('status',1)->where('is_deleted',0)->orderBy('name')->get();
        return view('employee.create',compact('request','state'));
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): RedirectResponse
    {
        request()->validate([
            'name' => 'required',
            'employee_id' => 'required|unique:employees',
        ]);
    
        $data = Employee::create([
            'name'        => $request->name,
            'employee_id' => $request->employee_id,
            'email'       => $request->email,
            'mobile'      => $request->mobile,
            'whatsapp_no'      => $request->whatsapp_no,
            'type'        => $request->type,
            'state'       => $request->state,
            'city'        => $request->area,
            'date_of_joining'  => $request->date_of_joining,
            'created_by'  => auth()->id(),
            'password'    => Hash::make($request->password), // hash here ✅
        ]);
        DB::table('user_permission_categories')->updateOrInsert(
                    ['employee_id' => $data->id, 'brand' => $request->brand],
                    
                    ['created_at' => now(), 'updated_at' => now()]
                    );
        return redirect()->route('employees.index')
                        ->with('success','employee created successfully.');
    }
    
    /**
     * Display the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show($id): View
    {
        $data = Employee::find($id);
        $state = State::where('status', 1)
        ->where('is_deleted', 0)
        ->orderBy('name')
        ->get();
        $workAreaList=userArea::where('user_id',$id)->where('is_deleted', 0)->groupby('area_id')->with('area')->get();
        $team = Team::where('ase_id', $data->id)->where('store_id', null)->where('is_deleted', 0)->first();
        $storeList = Store::where('user_id',$data->id)->where('is_deleted', 0)->orderBy('name')->get();
        $distributorList = Team::where('ase_id', $data->id)->where('distributor_id', '!=', null)->where('store_id',NULL)->where('is_deleted', 0)->groupBy('distributor_id')->orderBy('id','desc')->get();
        return view('employee.view',compact('data','state','workAreaList','team','distributorList','storeList'));
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit($id): View
    {
        $data = Employee::find($id);
        $state = State::where('status',1)->where('is_deleted',0)->orderBy('name')->get();
        return view('employee.edit',compact('data','state'));
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
        //dd($request->all());
         request()->validate([
            'name' => 'required',
            'employee_id' => 'required',
        ]);
    
        $data = Employee::findOrfail($id);
        $updateData = $request->except('password'); // take everything except password

        // If password is present, hash it
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $data->update($updateData);
                // Normalize brand value
        if (is_array($request->brand)) {
            // If multiple brands selected
            if (in_array(1, $request->brand) && in_array(2, $request->brand)) {
                $brand = 3; // both
            } elseif (in_array(1, $request->brand)) {
                $brand = 1; // onn
            } elseif (in_array(2, $request->brand)) {
                $brand = 2; // pynk
            }
        } else {
            // Single brand value
            $brand = $request->brand;
        }

        // Update or insert (this will overwrite old record for same employee)
        DB::table('user_permission_categories')->updateOrInsert(
            ['employee_id' => $data->id],  // condition (only employee_id, so old brand replaced)
            [
                'brand' => $brand,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        return redirect()->route('employees.index')
                        ->with('success','Employee updated successfully');
    }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy($id): RedirectResponse
    {
        $data = Employee::findOrfail($id);
        $data->is_deleted=1;
        $data->save();
        return redirect()->route('employees.index')
                        ->with('success','Employee deleted successfully');
    }


    public function status($id): RedirectResponse
    {
        $data = Employee::find($id);
        $status = ($data->status == 1) ? 0 : 1;
        $data->status = $status;
        $data->save();
    
        return redirect()->route('employees.index')
                        ->with('success','Employee status changed successfully');
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
        if (!empty($request->file)) {
            $file = $request->file('file');
            $filename = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $fileSize = $file->getSize();

            // Validate CSV extension and file size
            $valid_extension = ["csv"];
            $maxFileSize = 50097152; // Max 50MB

            if (in_array(strtolower($extension), $valid_extension)) {
                if ($fileSize <= $maxFileSize) {
                    // Upload the file to the storage location
                    $location = 'public/uploads/csv';
                    $file->move($location, $filename);
                    $filepath = $location . "/" . $filename;

                    // Open the CSV file and read it
                    $file = fopen($filepath, "r");
                    $importData_arr = [];
                    $i = 0;
                    $successCount=0;
                    // Read the CSV file row by row
                    while (($filedata = fgetcsv($file, 10000, ",")) !== false) {
                        // Skip the header row
                        if ($i == 0) {
                            $i++;
                            continue;
                        }

                         // Step 3: Extract the data from each row
                        $rowData = [
                            'type' => isset($filedata[0]) ? $filedata[0] : null,
                            'designation' => isset($filedata[1]) ? $filedata[1] : null,
                            'employee_id' => isset($filedata[2]) ? $filedata[2] : null,
                            'name' => isset($filedata[3]) ? $filedata[3] : null,
                            'email' => isset($filedata[4]) ? $filedata[4] : null,
                            'mobile' => isset($filedata[5]) ? $filedata[5] : null,
                            
                            'whatsapp_no' => isset($filedata[6]) ? $filedata[6] : null,
                            'state' => isset($filedata[7]) ? $filedata[7] : null,
                            'area' => isset($filedata[8]) ? $filedata[8] : null,
                            'date_of_joining' => isset($filedata[9]) ? $filedata[9] : null,
                            'password' => isset($filedata[10]) ? $filedata[10] : null,
                            'brand' => isset($filedata[11]) ? $filedata[11] : null,
                            
                        ];
                            
                        // Step 4: Validate each row's data
                        $validator = Validator::make($rowData, [
                            'name' => 'required|string|max:255',
                            'employee_id' => 'required|string|max:255|unique:employees',
                            'type' => 'required',
                        
                        ]);

                    if ($validator->fails()) {
                        // Accumulate errors with row number context
                        $errors[$i] = $validator->errors()->all();
                    } else {
                            // Map brand text to numeric value
                                $brandValue = null;
                                if (!empty($rowData['brand'])) {
                                    $brandText = strtolower(trim($rowData['brand']));
                                    if ($brandText === 'onn') {
                                        $brandValue = 1;
                                    } elseif ($brandText === 'pynk') {
                                        $brandValue = 2;
                                    } elseif (in_array($brandText, ['both', 'onn,pynk', 'pynk,onn'])) {
                                        $brandValue = 3;
                                    }
                                }
                        // Step 5: Save data if validation passes
                        $insertData = [
                            "type" => $rowData['type'],
                            "designation" => $rowData['designation'],
                            "employee_id" => $rowData['employee_id'],
                            "name" => $rowData['name'],
                            "email" => $rowData['email'],
                            "mobile" => $rowData['mobile'],
                            "whatsapp_no" => $rowData['whatsapp_no'],
                            "state" => $rowData['state'],
                            "area" => $rowData['area'],
                            "date_of_joining" => $rowData['date_of_joining'],
                            "password" => $rowData['password'],
                            
                            "status" => 1,
                            "is_deleted" => 0,
                            "created_at" => date('Y-m-d H:i:s'),
                            "updated_at" => date('Y-m-d H:i:s'),
                        ];
                        
                        Employee::create($insertData);
                        
                        // Save brand permission if valid
                        if ($brandValue) {
                            DB::table('user_permission_categories')->updateOrInsert(
                                [
                                    'employee_id' => $employee->id,
                                    'brand'       => $brandValue,
                                ],
                                [
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]
                            );
                        }
                        $successCount++;

                        
                    }

                    $i++;
                }

                fclose($file);
                
                if (!empty($errors)) {
                    // Redirect back to upload page if there are row-level validation errors
                    return redirect()->back()->with([
                        'csv_errors' => $errors, // pass errors to display
                    ]);
                }else{

                    Session::flash('message', 'CSV Import Complete. Total number of entries: ' . $successCount);
                }
                } else {
                    Session::flash('message', 'File too large. File must be less than 50MB.');
                }
            } else {
                Session::flash('message', 'Invalid File Extension. Supported extensions are ' . implode(', ', $valid_extension));
            }
        } else {
            Session::flash('message', 'No file found.');
        }

        // return redirect()->back();
    }


    //export

    public function employeeExport(Request $request)
	{
       
        $user_type   = $request->type ?? '';
    $state       = $request->state ?? '';
    $area        = $request->area ?? '';
    $keyword     = $request->keyword ?? '';
    $brandFilter = $request->brand ?? '';

    $query = Employee::query();

    // Filters for type, state, area, keyword
    $query->when($user_type, fn($q) => $q->where('type', $user_type));
    $query->when($state, fn($q) => $q->where('state', $state));
    $query->when($area, fn($q) => $q->where('city', $area));

    $query->when($keyword, function($q) use ($keyword) {
        $q->where(function($inner) use ($keyword) {
            $inner->where('name', 'like', '%'.$keyword.'%')
                  ->orWhere('fname', 'like', '%'.$keyword.'%')
                  ->orWhere('lname', 'like', '%'.$keyword.'%')
                  ->orWhere('mobile', 'like', '%'.$keyword.'%')
                  ->orWhere('employee_id', 'like', '%'.$keyword.'%')
                  ->orWhere('email', 'like', '%'.$keyword.'%');
        });
    });

    // Get logged-in user's accessible brands
    $userId = auth()->id();
    $accessibleBrands = DB::table('user_permission_categories')
        ->where('user_id', $userId)
        ->pluck('brand')
        ->unique()
        ->toArray(); // e.g. [1], [2], [1,2]
    if (in_array(3, $accessibleBrands)) {
        $accessibleBrands = [1, 2];
    }
    // Brand filtering logic
    if ($brandFilter) {
        if ($brandFilter === 'All') {
            // "All" → show employees only from brands user has access to
            $query->whereHas('permissions', fn($q) => $q->whereIn('brand', $accessibleBrands));
        } else {
            // Specific brand → show only if user has access
            if (in_array($brandFilter, $accessibleBrands)) {
                $query->whereHas('permissions', fn($q) => $q->where('brand', $brandFilter));
            } else {
                // If user tries to access brand they don’t have → return empty
                $query->whereRaw('1=0');
            }
        }
    } else {
        // First page load → default to logged-in user's permitted brands
        $query->whereHas('permissions', fn($q) => $q->whereIn('brand', $accessibleBrands));
    }

    $data = $query->where('is_deleted', 0)->with('stateDetail', 'area')
                  ->latest('id')
                  ->get();
        if (count($data) > 0) {
            $delimiter = ","; 
            $filename = "employee.csv"; 

            // Create a file pointer 
            $f = fopen('php://memory', 'w'); 

            // Set column headers 
            
            $fields = array('SR','Brand Permission','User Type','Name','Designation','Employee ID','Mobile','WhatsApp Number','Email','Date of Joining','State','Area','Working Area','Status','DATE'); 
            fputcsv($f, $fields, $delimiter); 

            $count = 1;

            foreach($data as $row) {
                $datetime = date('j F, Y h:i A', strtotime($row['created_at']));
                $area ='';
                $areaDetail = DB::table('user_areas')->where('user_id','=',$row->id)->get();
                                        
                if(!empty($areaDetail)) {
                    foreach($areaDetail as $key => $obj) {
                        $areaList=DB::table('areas')->where('id','=',$obj->area_id)->first();
                        $area .= $areaList->name ??'';
                        if((count($areaDetail) - 1) != $key) $area .= ', ';
                    }
                }
				$assignedPermissions = DB::table('user_permission_categories')
                    ->select('user_permission_categories.*')
                    ->join('employees','employees.id','=','user_permission_categories.employee_id')
                    ->where('user_permission_categories.employee_id', $row->id)
                    ->get();

                    $brandMap = [
                        1 => 'ONN',
                        2 => 'PYNK',
                        3 => 'Both',
                    ];

                    $brandPermissions = $assignedPermissions->pluck('brand')
                        ->map(function ($brand) use ($brandMap) {
                            return $brandMap[$brand] ?? $brand; // fallback if unknown
                        })
                        ->unique() // avoid duplicates
                        ->implode(', '); // comma separated string


                $lineData = array(
                    $count,
                    $brandPermissions,
                    $row->designation ? $row->designation : userTypeName($row->type) ,
					$row['name'] ?? 'NA',
                    $row['designation']?? 'NA',
                    $row['employee_id']?? 'NA',
                    $row['mobile'] ?? 'NA',
                    $row['whatsapp_no'] ?? 'NA',
                    $row['email'] ?? 'NA',
					$row['date_of_joining'] ?? 'NA',
                    $row['state']['name'] ?? 'NA',
                    $row['area']['name'] ?? 'NA',
                    $area,
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



     //state wise area
     public function state(Request $request, $state)
     {
         $stateName=State::where('id',$state)->first();
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


    public function filterByBrand(Request $request)
{
    $brand = $request->brand;

    // Fetch employees with optional brand filter
    $employees = Employee::all();

    // Prepare assigned permissions for all employees
    $employeePermissions = [];

    foreach ($employees as $employee) {
        $assignedPermissions = DB::table('user_permission_categories')
            ->select('user_permission_categories.*')
            ->join('employees','employees.id','=','user_permission_categories.employee_id')
            ->where('user_permission_categories.employee_id', $employee->id)->where('brand',$brand)
            ->get();

        $employeePermissions[$employee->id] = $assignedPermissions;
    }

    return view('employees.index', compact('employees', 'employeePermissions'));
}

//add area

     public function addArea(Request $request)
    {
          $area=UserArea::where('user_id',$request->user_id)->where('state_id',$request->state_id)->where('area_id',$request->area_id)->first();
		  if(empty($area)){
           $areaSave=New UserArea();
		   $areaSave->user_id = $request->user_id ?? '';
		   $areaSave->state_id = $request->state ?? '';
           $areaSave->area_id = $request->city ?? '';
		   $areaSave->save();
       
            return redirect()->back()->with('success','Area created successfully');
          }else{
            return redirect()->back()->with('success','Already added');
          }

    }

	
	//area delete for ASE
     public function deleteArea(Request $request,$id)
     {
        $data=UserArea::findOrfail($id);
        $data->is_deleted=1;
        $data->save();
        if ($data) {
            return redirect()->back()->with('success', 'Area Deleted successfully');
        } else {
            return redirect()->back()->with('success', 'Area Deleted successfully')->withInput($request->all());
        }
 
         
     }

     //store transfer
    public function bulkASEDistributorransfer(Request $request)
    {
        //dd($request->all());
        // Get the selected checkboxes
        $statusChecks = $request->input('status_check', []);
        $aseUsers = $request->input('aseUser', []);
        $ids = [];
        $names = [];
        
        // Loop through the original array
        foreach ($aseUsers as $item) {
           
            // Add to respective arrays
            $ids[] = $item;
            
        }

        
        
        $distributorUsers = $request->input('distributorUser', []);
        // Convert arrays to comma-separated strings
        $aseUsersString = implode(',', $ids);
        
        
        //
        $distributorUsersString = implode(',', $distributorUsers);
        
        foreach ($statusChecks as $storeId) {
            // Perform transfer logic for each selected store
            // e.g., update the store's ASE and Distributor
            
            $store = Store::find($storeId);

            if ($store) {
                // Append new ASE User values to the existing values
                $existingAseUsers = $store->user_id ?? '';
                //$aseUsersArray = array_filter(array_unique(array_merge(explode(',', $existingAseUsers), $ids)));
                $aseUsersArray = array_filter(array_unique($ids));
                $newAseUsers = implode(',', $aseUsersArray);
                
                $store->user_id = $newAseUsers;
                $store->save();
                // Append new Distributor User values to the existing values
                $team = Team::where('store_id',$storeId)->first();
                $existingDistributors = $team->distributor_id ?? '';
                $existingASE = $team->ase_id ?? '';
                
                //$asesArray = array_filter(array_unique(array_merge(explode(',', $existingASE), $names)));
                $asesArray = array_filter(array_unique($ids));
                $newases = implode(',', $asesArray);
                
                $distributorArray = array_filter(array_unique(array_merge(explode(',', $existingDistributors), $distributorUsers)));
                //$distributorArray = array_filter(array_unique($distributorUsers));
                $newDistributors = implode(',', $distributorArray);
                //dd($newDistributors);
                // Update the store with the new ASE User and Distributor values
                
                $team->distributor_id = $newDistributors ??'';
                $team->ase_id = $newases;
                $team->save();
            }
        }

        // Redirect or return response
        return redirect()->back()->with('success', 'Stores transferred successfully.');
    }

     

}
