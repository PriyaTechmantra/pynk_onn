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
        $query = Employee::query();

        /**
         * STEP 1: Brand filter (1 = ONN, 2 = PYNK, 3 = BOTH)
         */
        if ($request->filled('brand')) {
            $query->where(function ($q) use ($request) {
                if ($request->brand == 3) {
                    // “Both” selected → show ONN (1), PYNK (2), and Both (3)
                    $q->whereIn('employees.brand', [1, 2, 3]);
                } else {
                    // single brand selected → include that + both
                    $q->where('employees.brand', $request->brand)
                    ->orWhere('employees.brand', 3);
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
                        $q->whereIn('employees.brand', [1, 2, 3]);
                    } else {
                        // user has limited brand(s)
                        $q->whereIn('employees.brand', array_merge($userBrandPermissions, [3]));
                    }
                });
            }
        }


        /**
         * STEP 2: Date range filter (if available)
         */
        
        /**
         * STEP 3: Distributor filter
         */
        if ($request->filled('state')) {
            $query->whereRaw("find_in_set('".$request->state."', state)");
        }
        /**
         * STEP 3: State filter
         */
        if ($request->filled('area')) {
            $query->where('city', $request->area);
        }

        /**
         * STEP 4: Area filter
         */
        if ($request->filled('type')) {
            $query->where('type', $user_type);
        }
        

        /**
         * STEP 5: Keyword search (optional)
         */
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', '%'.$keyword.'%')
                  ->orWhere('fname', 'like', '%'.$keyword.'%')
                  ->orWhere('lname', 'like', '%'.$keyword.'%')
                  ->orWhere('mobile', 'like', '%'.$keyword.'%')
                  ->orWhere('employee_id', 'like', '%'.$keyword.'%')
                  ->orWhere('email', 'like', '%'.$keyword.'%');
            });
        }

        /**
         * STEP 6: Fetch data with pagination
         */
        $data = $query->where('is_deleted',0)->orderBy('id', 'desc')->paginate(25);
    
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
        $data = (object) [];
        $data->employee = Employee::find($id);
        // VP
         // VP
        if ($data->employee->type == 1) {
            $user_type = $request->user_type ? $request->user_type : '';
            $name = $request->name ? $request->name : '';
            $state = $request->state ? $request->state : '';
            $area = $request->area ? $request->area : '';

            $query = Team::where('vp_id', $data->employee->id);

            $query->when($user_type, function($query) use ($user_type, $name) {
                if(!empty($name)) {
                    if ($user_type == 1) $user_type = 'vp';
                    elseif ($user_type == 2) $user_type = 'rsm';
                    elseif ($user_type == 3) $user_type = 'asm';
                    else $user_type = 'ase';
                    
                    

                    $query->where($user_type, $name);
                } else {
                    if ($user_type == 2) {
                        $user_type = 'rsm';
                        $query->where('rsm_id', '!=', null)->groupBy('rsm_id');
                    } elseif ($user_type == 3) {
                        $user_type = 'asm';
                        $query->where('asm_id', '!=', null)->groupBy('asm_id');
                    } else {
                        $user_type = 'ase';
                        $query->where('ase_id', '!=', null)->groupBy('ase_id');
                    
                    }
                }
            });

            $query->when($state, function($query) use ($state) {
                $query->where('state', $state);
            });
            $query->when($area, function($query) use ($area) {
                $query->where('city', $area);
            });

            $data->team = $query->paginate(25);
            
            return view('admin.user.detail.vp', compact('data', 'id', 'request'));
        }
        //ZSM
         elseif ($data->employee->type == 2) {
            $user_type = $request->user_type ? $request->user_type : '';
            $name = $request->name ? $request->name : '';
            $state = $request->state ? $request->state : '';
            $area = $request->area ? $request->area : '';

            $query = Team::where('rsm_id', $data->employee->id);

            $query->when($user_type, function($query) use ($user_type, $name) {
                if(!empty($name)) {
                    if ($user_type == 1) $user_type = 'vp';
                    elseif ($user_type == 2) $user_type = 'rsm';
                    elseif ($user_type == 3) $user_type = 'asm';
                    else $user_type = 'ase';
                    

                    $query->where($user_type, $name);
                } else {
                    if ($user_type == 2) {
                        $user_type = 'rsm';
                        $query->where('rsm_id', '!=', null)->groupBy('rsm_id');
                    } elseif ($user_type == 3) {
                        $user_type = 'asm';
                        $query->where('asm', '!=', null)->groupBy('asm');
                    } elseif ($user_type == 4) {
                        $user_type = 'ase';
                        $query->where('ase', '!=', null)->groupBy('ase');
                    } elseif ($user_type == 5) {
                        $user_type = 'distributor_name';
                        $query->where('distributor_name', '!=', null)->groupBy('distributor_name');
                    } else {
                        $user_type = 'retailer';
                        $query->where('retailer', '!=', null)->groupBy('retailer');
                    }
                }
            });

            $query->when($state, function($query) use ($state) {
                $query->where('state', $state);
            });
            $query->when($area, function($query) use ($area) {
                $query->where('area', $area);
            });

            $data->team = $query->paginate(25);
            
            return view('admin.user.detail.zsm', compact('data', 'id', 'request'));
        }
         // SM
         elseif ($data->user->type == 3) {
            $user_type = $request->user_type ? $request->user_type : '';
            $name = $request->name ? $request->name : '';
            $state = $request->state ? $request->state : '';
            $area = $request->area ? $request->area : '';

            $query = Team::where('rsm_id', $data->user->id);

            $query->when($user_type, function($query) use ($user_type, $name) {
                if(!empty($name)) {
                    if ($user_type == 1) $user_type = 'vp';
                    elseif ($user_type == 2) $user_type = 'rsm';
                    elseif ($user_type == 3) $user_type = 'asm';
                    elseif ($user_type == 4) $user_type = 'ase';
                    elseif ($user_type == 5) $user_type = 'distributor_name';
                    else $user_type = 'retailer';

                    $query->where($user_type, $name);
                } else {
                    if ($user_type == 2) {
                        $user_type = 'rsm';
                        $query->where('rsm', '!=', null)->groupBy('rsm');
                    } elseif ($user_type == 3) {
                        $user_type = 'asm';
                        $query->where('asm', '!=', null)->groupBy('asm');
                    } elseif ($user_type == 4) {
                        $user_type = 'ase';
                        $query->where('ase', '!=', null)->groupBy('ase');
                    } elseif ($user_type == 5) {
                        $user_type = 'distributor_name';
                        $query->where('distributor_name', '!=', null)->groupBy('distributor_name');
                    } else {
                        $user_type = 'retailer';
                        $query->where('retailer', '!=', null)->groupBy('retailer');
                    }
                }
            });

            $query->when($state, function($query) use ($state) {
                $query->where('state', $state);
            });
            $query->when($area, function($query) use ($area) {
                $query->where('area', $area);
            });

            $data->team = $query->paginate(25);
            
            return view('admin.user.detail.rsm', compact('data', 'id', 'request'));
        }
        // SM
        elseif ($data->user->type == 4) {
            $user_type = $request->user_type ? $request->user_type : '';
            $name = $request->name ? $request->name : '';
            $state = $request->state ? $request->state : '';
            $area = $request->area ? $request->area : '';

            $query = Team::where('asm_id', $data->user->id);

            $query->when($user_type, function($query) use ($user_type, $name) {
                if(!empty($name)) {
                    if ($user_type == 1) $user_type = 'vp';
                    elseif ($user_type == 2) $user_type = 'rsm';
                    elseif ($user_type == 3) $user_type = 'asm';
                    elseif ($user_type == 4) $user_type = 'ase';
                    elseif ($user_type == 5) $user_type = 'distributor_name';
                    else $user_type = 'retailer';

                    $query->where($user_type, $name);
                } else {
                    if ($user_type == 2) {
                        $user_type = 'rsm';
                        $query->where('rsm', '!=', null)->groupBy('rsm');
                    } elseif ($user_type == 3) {
                        $user_type = 'asm';
                        $query->where('asm', '!=', null)->groupBy('asm');
                    } elseif ($user_type == 4) {
                        $user_type = 'ase';
                        $query->where('ase', '!=', null)->groupBy('ase');
                    } elseif ($user_type == 5) {
                        $user_type = 'distributor_name';
                        $query->where('distributor_name', '!=', null)->groupBy('distributor_name');
                    } else {
                        $user_type = 'retailer';
                        $query->where('retailer', '!=', null)->groupBy('retailer');
                    }
                }
            });

            $query->when($state, function($query) use ($state) {
                $query->where('state', $state);
            });
            $query->when($area, function($query) use ($area) {
                $query->where('area', $area);
            });

            $data->team = $query->paginate(25);
            
            return view('admin.user.detail.sm', compact('data', 'id', 'request'));
        }
        // ASM
        elseif ($data->user->type == 5) {
            $user_type = $request->user_type ? $request->user_type : '';
            $name = $request->name ? $request->name : '';
            $state = $request->state ? $request->state : '';
            $area = $request->area ? $request->area : '';

            $query = Team::where('asm_id', $data->user->id);

            $query->when($user_type, function($query) use ($user_type, $name) {
                if(!empty($name)) {
                    if ($user_type == 1) $user_type = 'vp';
                    elseif ($user_type == 2) $user_type = 'rsm';
                    elseif ($user_type == 3) $user_type = 'asm';
                    elseif ($user_type == 4) $user_type = 'ase';
                    elseif ($user_type == 5) $user_type = 'distributor_name';
                    else $user_type = 'retailer';

                    $query->where($user_type, $name);
                } else {
                    if ($user_type == 2) {
                        $user_type = 'rsm';
                        $query->where('rsm', '!=', null)->groupBy('rsm');
                    } elseif ($user_type == 3) {
                        $user_type = 'asm';
                        $query->where('asm', '!=', null)->groupBy('asm');
                    } elseif ($user_type == 4) {
                        $user_type = 'ase';
                        $query->where('ase_id', '!=', null)->groupBy('ase_id');
                    } elseif ($user_type == 5) {
                        $user_type = 'distributor_id';
                        $query->where('distributor_id', '!=', null)->groupBy('distributor_id');
                    } else {
                        $user_type = 'retailer';
                        $query->where('store_id', '!=', null)->groupBy('store_id');
                    }
                }
            });

            $query->when($state, function($query) use ($state) {
                $query->where('state_id', $state);
            });
            $query->when($area, function($query) use ($area) {
                $query->where('area_id', $area);
            });

            $data->team = $query->paginate(25);
            
            return view('admin.user.detail.asm', compact('data', 'id', 'request'));
        }
        // ASE
        elseif ($data->user->type == 4) {
            $data->team = Team::where('ase_id', $data->id)->where('store_id', null)->where('is_deleted', 0)->first();
            $data->workAreaList=userArea::where('user_id',$id)->where('is_deleted', 0)->groupby('area_id')->with('area')->get();
            $data->distributorList = Team::where('ase_id', $data->user->id)->where('distributor_id', '!=', null)->where('store_id',NULL)->groupBy('distributor_id')->orderBy('id','desc')->get();
            
			$data->areaDetail= Area::orderby('name')->get();
            $data->distributorList = Team::where('ase_id', $data->id)->where('distributor_id', '!=', null)->where('store_id',NULL)->where('is_deleted', 0)->groupBy('distributor_id')->orderBy('id','desc')->get();
			
            return view('admin.user.detail.ase', compact('data', 'id', 'request'));
        }
        $state = State::where('status', 1)
        ->where('is_deleted', 0)
        ->orderBy('name')
        ->get();
        
        
        $storeList = Store::where('user_id',$data->id)->where('is_deleted', 0)->orderBy('name')->get();
        
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

    public function attendanceReport(Request $request) {
         // === Filter parameters ===
        $date_from = $request->date_from ?? date('Y-m-d');
        $month = $request->month ?? date('Y-m');
        $brand_id = $request->brand_id ?? '';
        $vp_id = $request->vp_id ?? '';
        $rsm_id = $request->rsm_id ?? '';
        $asm_id = $request->asm_id ?? '';
        $ase_id = $request->ase_id ?? '';
        $state_id = $request->state_id ?? '';
        $day = date('D', strtotime($month));
        
        // === Step 1: Get all ASEs (final-level employees) based on filters ===
        $employees = Employee::query()
            ->where('status', 1)
            ->where('is_deleted', 0); // Assuming type=5 means ASE

        if ($ase_id) {
            $employees->where('id', $ase_id);
        } elseif ($asm_id) {
            $employees->where('id', $asm_id);
        } elseif ($rsm_id) {
            $employees->where('id', $rsm_id);
        } elseif ($vp_id) {
            $employees->where('id', $vp_id);
        }

        // Filter by brand via user_permission_categories
        if ($brand_id && $brand_id != 'all') {
            $userIds = \DB::table('user_permission_categories')
                ->where('brand', $brand_id)
                ->pluck('employee_id');
            $employees->whereIn('id', $userIds);
        }

        $employees = $employees->get();
        
       
        $vpDetails=Employee::where('type',1)->where('status',1)->where('is_deleted',0)->get();
         $month = !empty($request->month)?$request->month:date('Y-m');
        return view('attendance.index', compact('request', 'vpDetails', 'month'));
    }

public function attendanceReportExport(Request $request)
{
    // --- STEP 1: Get Filter Inputs ---
    $brand_id = $request->brand_id ?? '';  // 1=ONN, 2=PYNK, 'all'=BOTH
    $vp_id    = $request->vp_id ?? '';
    $state_id = $request->state_id ?? '';
    $rsm_id   = $request->rsm_id ?? '';
    $asm_id   = $request->asm_id ?? '';
    $ase_id   = $request->ase_id ?? '';
    $month    = !empty($request->month) ? $request->month : date('Y-m');

    // --- STEP 2: Prepare ID Arrays ---
    $vpIds  = [];
    $rsmIds = [];
    $asmIds = [];
    $aseIds = [];

    // --- STEP 3: BRAND WISE VP FETCH ---
    if ($brand_id && $brand_id != 'all') {
        $brandIds = \DB::table('user_permission_categories')
                ->where('brand', $brand_id)
                ->pluck('employee_id')->toArray();
        $vpIds = Employee::where('type',1)->whereIN('id',$brandIds)->pluck('id')->toArray();
    } else {
        // BOTH means ONN + PYNK VPs
        $vpIds = Team::groupBy('vp_id')->pluck('vp_id')->toArray();
    }

    // --- STEP 4: VP WISE STATE & RSM ---
    if ($vp_id) {
        $stateIds = Team::where('vp_id', $vp_id)
            ->groupBy('state_id')
            ->pluck('state_id')
            ->toArray();

        if ($state_id) {
            // VP + STATE wise RSM
            $rsmIds = Team::where('vp_id', $vp_id)
                ->where('state_id', $state_id)
                ->groupBy('rsm_id')
                ->pluck('rsm_id')
                ->toArray();
        } else {
            // Only VP wise all RSM
            $rsmIds = Team::where('vp_id', $vp_id)
                ->groupBy('rsm_id')
                ->pluck('rsm_id')
                ->toArray();
        }
    }

    // --- STEP 5: RSM WISE ASM ---
    if ($rsm_id) {
        $asmIds = Team::where('rsm_id', $rsm_id)
            ->groupBy('asm_id')
            ->pluck('asm_id')
            ->toArray();
    } elseif (!empty($rsmIds)) {
        $asmIds = Team::whereIn('rsm_id', $rsmIds)
            ->groupBy('asm_id')
            ->pluck('asm_id')
            ->toArray();
    }

    // --- STEP 6: ASM WISE ASE ---
    if ($asm_id) {
        $aseIds = Team::where('asm_id', $asm_id)
            ->groupBy('ase_id')
            ->pluck('ase_id')
            ->toArray();
    } elseif (!empty($asmIds)) {
        $aseIds = Team::whereIn('asm_id', $asmIds)
            ->groupBy('ase_id')
            ->pluck('ase_id')
            ->toArray();
    }

    // If ASE selected manually, use only that
    if ($ase_id) {
        $aseIds = [$ase_id];
    }

    // --- STEP 7: Merge all Hierarchy IDs ---
    $allIds = array_merge($vpIds, $rsmIds, $asmIds, $aseIds);

    // --- STEP 8: Get Employees matching any of these IDs ---
    $employees = Employee::whereIn('id', $allIds)
        ->where('status', 1)
        ->where('is_deleted', 0)
        ->get();

    // --- STEP 9: Attendance Logic ---
    $my_month = explode('-', $month);
    $year_val = $my_month[0];
    $month_val = $my_month[1];

    $dates_month = dates_month($month_val, $year_val);
    $month_names = $dates_month['month_names'];
    $date_values = $dates_month['date_values'];

    $tableHead = ['TEAM', 'EMPLOYEE', 'EMPLOYEE ID', 'STATUS', 'DESIGNATION', 'DOJ', 'HQ', 'CONTACT'];
    foreach ($month_names as $months) {
        array_push($tableHead, $months);
    }

    $tableBody = [];

    foreach ($employees as $item) {
        $monthlyDates = [];
        foreach ($date_values as $date) {
            $att = dates_attendance($item->id, $date);

            if (empty($att[0][0]['date_wise_attendance'][0]['is_present'])) {
                // --- Sunday = W, Missing date = '-'
                $day = date('w', strtotime($date));
                if ($day == 0) {
                    $status = 'W';
                } else {
                    $status = '-';
                }
            } else {
                $status = $att[0][0]['date_wise_attendance'][0]['is_present'];
            }

            // --- Add Colors ---
            $color = match ($status) {
                'P' => 'background-color: #018634; color:#fff;',
                'A' => 'background-color: red; color:#fff;',
                'L' => 'background-color: #FFA500; color:#fff;',
                'W' => 'background-color: #F1E100; color:#000;',
                default => 'background-color: #294fa1da; color:#fff;',
            };

            $monthlyDates[] = "<td style='{$color} text-align:center; border:1px solid #fff;'>{$status}</td>";
        }

        $tableBody[] = [
            $item->team_name ?? '',
            $item->name ?? '',
            $item->employee_id ?? '',
            $item->status == 1 ? 'Active' : 'Inactive',
            $item->designation ?? '',
            $item->date_of_joining ?? '',
            $item->headquater ?? '',
            $item->mobile ?? '',
            $monthlyDates
        ];
    }

    // --- STEP 10: Build HTML Table ---
    $html = '<table class="table"><thead><tr>';
    foreach ($tableHead as $th) {
        $html .= "<th>{$th}</th>";
    }
    $html .= '</tr></thead><tbody>';

    foreach ($tableBody as $row) {
        $html .= '<tr>';
        foreach (array_slice($row, 0, 8) as $col) {
            $html .= "<td>{$col}</td>";
        }
        foreach ($row[8] as $att) {
            $html .= $att;
        }
        $html .= '</tr>';
    }
    $html .= '</tbody></table>';

    return response()->json([
        'status' => 200,
        'data' => $html
    ]);
}

    public function vpBrandWise(Request $request, $brand_id)
   {
        // Base query for active VPs
        $query = Employee::where('type', 1)
            ->where('status', 1)
            ->where('is_deleted', 0);

        
        if ($brand_id == 'all') {
            // All brands → show all VPs
            $data = $query->orderBy('name')->get();
        } else {
         // Get all user IDs from user_permission_categories table for that brand
            $userIds = \DB::table('user_permission_categories')
                ->where('brand', $brand_id)
                ->pluck('user_id')
                ->toArray();

                if (empty($userIds)) {
                    return response()->json(['error' => true, 'resp' => 'No users found for this brand']);
                }

            // Fetch employees matching those user IDs
            $data = $query->whereIn('id', $userIds)
                ->orderBy('name')
                ->get();
        }

            // Response
            if ($data->isEmpty()) {
                return response()->json(['error' => true, 'resp' => 'No data found']);
            }

        return response()->json([
            'error' => false,
            'resp' => 'VP Brand Wise List',
            'data' => $data,
        ]);
    }


    public function stateVpWise(Request $request,$id)
    {
        
        if($id=='all'){
            $data=Team::select('state_id')->with('states:id,name')->groupby('vp_id')->get();
        }else{
           $data=Team::where('vp_id',$id)->with('states:id,name')->groupby('vp_id')->get();
        }
        
       if (count($data)==0) {
                return response()->json(['error'=>true, 'resp'=>'No data found']);
       } else {
                return response()->json(['error'=>false, 'resp'=>'State List','data'=>$data]);
       } 
        
    }
    //zsm wise state
    public function rsmStateWise(Request $request,$id)
    {
        
        if($id=='all'){
            $data=Team::select('rsm_id')->with('rsm:id,name')->groupby('state_id')->get();
        }else{
           $data=Team::where('state_id',$id)->with('rsm:id,name')->groupby('state_id')->get();
        }
       // dd($data);
       if (count($data)==0) {
                return response()->json(['error'=>true, 'resp'=>'No data found']);
       } else {
                return response()->json(['error'=>false, 'resp'=>'RSM List','data'=>$data]);
       } 
        
    }
    
     //state wise rsm
    public function asmRsmWise(Request $request,$id)
    {
        if($id=='all'){
            $data=Team::select('asm_id')->with('asm:id,name')->groupby('rsm_id')->get();
        }else{
           $data=Team::where('rsm_id',$id)->with('asm:id,name')->groupby('rsm_id')->get();
        }
       // dd($data);
       if (count($data)==0) {
                return response()->json(['error'=>true, 'resp'=>'No data found']);
       } else {
                return response()->json(['error'=>false, 'resp'=>'ASM List','data'=>$data]);
       } 
        
    }
    //rsm wise sm list
    public function aseAsmWise(Request $request,$id)
    {
       if($id=='all'){
            $data=Team::select('ase_id')->with('ase:id,name')->groupby('asm_id')->get();
        }else{
           $data=Team::where('asm_id',$id)->with('ase:id,name')->groupby('asm_id')->get();
        }
       if (count($data)==0) {
                return response()->json(['error'=>true, 'resp'=>'No data found']);
       } else {
                return response()->json(['error'=>false, 'resp'=>'ASE List','data'=>$data]);
       } 
        
    }
    

     

}
