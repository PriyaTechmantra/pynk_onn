<?php

namespace App\Http\Controllers;

use App\Models\Distributor;
use App\Models\State;
use App\Models\Area;
use App\Models\Team;
use App\Models\Store;
use App\Models\Employee;
use App\Models\DistributorRange;
use App\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Auth;
use DB;
use Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\JsonResponse;
class DistributorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
         $this->middleware('permission:view distributor|distributor bulk upload|distributor export|distributor status change', ['only' => ['index','show']]);
         $this->middleware('permission:create distributor', ['only' => ['create','store']]);
         $this->middleware('permission:update distributor', ['only' => ['edit','update']]);
         $this->middleware('permission:delete distributor', ['only' => ['destroy']]);
        
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request): View
{
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
        $query = Distributor::query();

        /**
         * STEP 1: Brand filter (1 = ONN, 2 = PYNK, 3 = BOTH)
         */
        if ($request->filled('brand')) {
            $query->where(function ($q) use ($request) {
                if ($request->brand == 3) {
                    // “Both” selected → show ONN (1), PYNK (2), and Both (3)
                    $q->whereIn('distributors.brand', [1, 2, 3]);
                } else {
                    // single brand selected → include that + both
                    $q->where('distributors.brand', $request->brand)
                    ->orWhere('distributors.brand', 3);
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
                        $q->whereIn('distributors.brand', [1, 2, 3]);
                    } else {
                        // user has limited brand(s)
                        $q->whereIn('distributors.brand', array_merge($userBrandPermissions, [3]));
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
            $query->whereRaw("find_in_set('".$request->state."', distributors.state_id)");
        }
        /**
         * STEP 3: State filter
         */
        if ($request->filled('area')) {
            $query->where('distributors.area_id', $request->area);
        }

        
        

        /**
         * STEP 4: Keyword search (optional)
         */
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', '%'.$keyword.'%')
                  ->orWhere('contact', 'like', '%'.$keyword.'%')
                  ->orWhere('code', 'like', '%'.$keyword.'%')
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

    return view('distributor.index', compact('data', 'request', 'state'))
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
        return view('distributor.create',compact('request','state'));
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
            'code' => 'required',
            'name' => 'required',
           
            'contact' => 'required',
            'brand' => 'required',
        ]);
        $data = Employee::create([
            'name'        => $request->name,
            'code' =>   $request->code,
            'email'       => $request->email,
            'contact'      => $request->contact,
            'whatsapp_no'      => $request->whatsapp_no,
            'state_id'       => $request->state,
            'area_id'        => $request->area,
            'date_of_joining'  => $request->date_of_joining,
            'brand'  => $request->brand,
            'user_id'  => auth()->id(),
            'password'    => Hash::make($request->password), // hash here ✅
        ]);
        
        return redirect()->route('distributors.index')
                        ->with('success','distributor created successfully.');
    }
    
    /**
     * Display the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request,$id): View
    { 
        $storebrandFilter = $request->get('storebrand');
        $data = (object) [];
        $data->distributor = Distributor::find($id);
        $area=Area::where('id', $data->distributor->area_id)->first();
		$user = Auth::user();
            $userBrands = DB::table('user_permission_categories')
                ->where('user_id', $user->id)
                ->pluck('brand')
                ->toArray();
            // Employee's brand permission (1=ONN, 2=PYNK, 3=Both)
            $employeeBrand = $data->distributor->brand ?? null;
            $data->team = Team::where('distributor_id', $data->distributor->id)->where('store_id','=',NULL)->first();
		// ==================== STORE LIST ====================
                $storeQuery = Store::where('teams.distributor_id', $data->distributor->id)
                    ->where('stores.is_deleted', 0)
                    ->join('teams', function($join) use ($data) {
                        $join->on('stores.id', '=', 'teams.store_id')
                            ->whereNOTNull('teams.store_id')
                            ->where('teams.is_deleted', 0);
                    })
                    ->select('stores.*')
                    ->orderBy('stores.name');

                $storeQuery->where(function ($q) use ($data, $storebrandFilter, $userBrands) {
                         $employeeBrand = $data->distributor->brand ?? null;
                    

                    if ($storebrandFilter) {
                        if ($storebrandFilter == 3) {
                            $q->whereIn('stores.brand', [1, 2, 3]);
                        } else {
                            $q->where(function ($q2) use ($storebrandFilter) {
                                $q2->where('stores.brand', $storebrandFilter)
                                ->orWhere('stores.brand', 3);
                            });
                        }
                    } else {
                        if ($employeeBrand == 1 && in_array(3, $userBrands)) {
                            $q->whereIn('stores.brand', [1, 3]);
                        } elseif ($employeeBrand == 2 && in_array(3, $userBrands)) {
                            $q->whereIn('stores.brand', [2, 3]);
                        } elseif ($employeeBrand == 3 && in_array(3, $userBrands)) {
                            $q->whereIn('stores.brand', [1, 2, 3]);
                        } elseif ($employeeBrand == 3 && in_array(1, $userBrands)) {
                            $q->whereIn('stores.brand', [1, 3]);
                        } elseif ($employeeBrand == 3 && in_array(2, $userBrands)) {
                            $q->whereIn('stores.brand', [2, 3]);
                        } elseif (in_array($employeeBrand, $userBrands)) {
                            $q->whereIn('stores.brand', [$employeeBrand, 3]);
                        } else {
                            $q->where('stores.brand', -1);
                        }
                    }
                });

                $data->storeList = $storeQuery->get();
       
		//$data->storeList = Team::where('distributor_id', $data->distributor->id)->where('store_id','!=',null)->groupBy('store_id')->with('store')->get();
        return view('distributor.view',compact('data','id','request'));
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request,$id): View
    {
        $user = auth()->user();

            // Logged-in user brand permissions
            $userBrands = DB::table('user_permission_categories')
                ->where('user_id', $user->id)
                ->pluck('brand')
                ->toArray();
        $data = Distributor::find($id);
        $employeeBrand=$data->brand;
        $state = State::where('status',1)->where('is_deleted',0)->orderBy('name')->get();
        $query1=Employee::where('type',1) ->where('is_deleted', 0)
                ->where('status', 1);
        $query1->where(function ($q) use ($employeeBrand, $userBrands) {

                // If dropdown filter selected
                
                    // No dropdown → apply employee + user permission logic
                    if ($employeeBrand == 3 && in_array(3, $userBrands)) {
                        $q->whereIn('brand', [1, 2, 3]); // Both + Both
                    } elseif ($employeeBrand == 3) {
                        $q->whereIn('brand', array_merge($userBrands, [3])); // Both employee, limited user
                    } elseif (in_array(3, $userBrands)) {
                        $q->whereIn('brand', [$employeeBrand, 3]); // Limited employee, both user
                    } else {
                        $q->whereIn('brand', [$employeeBrand]); // Limited + limited
                    }
                
            });
            $data->allZSM = $query1->groupBy('name')
                ->orderBy('id', 'desc')
                ->paginate(25);
        
                $query2=Employee::where('type',2) ->where('is_deleted', 0)
                ->where('status', 1);
        $query2->where(function ($q) use ($employeeBrand, $userBrands) {

                // If dropdown filter selected
               
                
                    // No dropdown → apply employee + user permission logic
                    if ($employeeBrand == 3 && in_array(3, $userBrands)) {
                        $q->whereIn('brand', [1, 2, 3]); // Both + Both
                    } elseif ($employeeBrand == 3) {
                        $q->whereIn('brand', array_merge($userBrands, [3])); // Both employee, limited user
                    } elseif (in_array(3, $userBrands)) {
                        $q->whereIn('brand', [$employeeBrand, 3]); // Limited employee, both user
                    } else {
                        $q->whereIn('brand', [$employeeBrand]); // Limited + limited
                    }
                
            });
            $data->allRSM = $query2->groupBy('name')
                ->orderBy('id', 'desc')
                ->paginate(25);
       $query3=Employee::where('type',3) ->where('is_deleted', 0)
                ->where('status', 1);
        $query3->where(function ($q) use ($employeeBrand, $userBrands) {

                // If dropdown filter selected
                
                    // No dropdown → apply employee + user permission logic
                    if ($employeeBrand == 3 && in_array(3, $userBrands)) {
                        $q->whereIn('brand', [1, 2, 3]); // Both + Both
                    } elseif ($employeeBrand == 3) {
                        $q->whereIn('brand', array_merge($userBrands, [3])); // Both employee, limited user
                    } elseif (in_array(3, $userBrands)) {
                        $q->whereIn('brand', [$employeeBrand, 3]); // Limited employee, both user
                    } else {
                        $q->whereIn('brand', [$employeeBrand]); // Limited + limited
                    }
                
            });
            $data->allASM = $query3->groupBy('name')
                ->orderBy('id', 'desc')
                ->paginate(25);
        $query4=Employee::where('type',4) ->where('is_deleted', 0)
                ->where('status', 1);
        $query4->where(function ($q) use ($employeeBrand, $userBrands) {

                
                
                    // No dropdown → apply employee + user permission logic
                    if ($employeeBrand == 3 && in_array(3, $userBrands)) {
                        $q->whereIn('brand', [1, 2, 3]); // Both + Both
                    } elseif ($employeeBrand == 3) {
                        $q->whereIn('brand', array_merge($userBrands, [3])); // Both employee, limited user
                    } elseif (in_array(3, $userBrands)) {
                        $q->whereIn('brand', [$employeeBrand, 3]); // Limited employee, both user
                    } else {
                        $q->whereIn('brand', [$employeeBrand]); // Limited + limited
                    }
                
            });
            $data->allASE = $query3->groupBy('name')
                ->orderBy('id', 'desc')
                ->paginate(25);
       
        return view('distributor.edit',compact('data','state','request'));
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,  $id): RedirectResponse
    {
         request()->validate([
            'code' => 'required',
            'name' => 'required',
           
            'contact' => 'required',
            'brand' => 'required',
        ]);
        $data = Distributor::findOrfail($id);
       
        $updateData = $request->except('password','_token','_method'); // take everything except password
        //dd($updateData);
        // If password is present, hash it
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }
       
        

        $data->update($updateData);
        
        return redirect()->route('distributors.index')
                        ->with('success','distributor created successfully.');
    }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy($id): RedirectResponse
    {
        $data = Distributor::find($id);
        $data->is_deleted=1;
        $data->save();
    
        return redirect()->route('distributors.index')
                        ->with('success','Distributor deleted successfully');
    }
    
    public function status($id): RedirectResponse
    {
        $data = Distributor::find($id);
        $status = ($data->status == 1) ? 0 : 1;
        $data->status = $status;
        $data->save();
    
        return redirect()->route('distributors.index')
                        ->with('success','Distributor status changed successfully');
    }
    
    
    //csv export
    
     public function employeeExport(Request $request)
	{
       
        
        $state       = $request->state ?? '';
        $area        = $request->area ?? '';
        $keyword     = $request->keyword ?? '';
        $brandFilter = $request->brand ?? '';
        
    $query = Distributor::query();
     if ($request->filled('brand')) {
            $query->where(function ($q) use ($request) {
                if ($request->brand == 3) {
                    // “Both” selected → show ONN (1), PYNK (2), and Both (3)
                    $q->whereIn('distributors.brand', [1, 2, 3]);
                } else {
                    // single brand selected → include that + both
                    $q->where('distributors.brand', $request->brand)
                    ->orWhere('distributors.brand', 3);
                }
            });
        } else {
            $user = auth()->user();
            // if brand not selected — show according to user permission
            $userBrandPermissions = DB::table('user_permission_categories')
                ->where('user_id', $user->id)
                ->pluck('brand')
                ->toArray();

            if (!empty($userBrandPermissions)) {
                $query->where(function ($q) use ($userBrandPermissions) {
                    if (in_array(3, $userBrandPermissions)) {
                        // user has both brand permission
                        $q->whereIn('distributors.brand', [1, 2, 3]);
                    } else {
                        // user has limited brand(s)
                        $q->whereIn('distributors.brand', array_merge($userBrandPermissions, [3]));
                    }
                });
            }
        }
    // Filters for type, state, area, keyword
    
    $query->when($state, fn($q) => $q->where('state_id', $state));
    $query->when($area, fn($q) => $q->where('area_id', $area));

    $query->when($keyword, function($q) use ($keyword) {
        $q->where(function($inner) use ($keyword) {
            $inner->where('name', 'like', '%'.$keyword.'%')
                  ->orWhere('contact', 'like', '%'.$keyword.'%')
                  ->orWhere('code', 'like', '%'.$keyword.'%')
                  ->orWhere('email', 'like', '%'.$keyword.'%');
        });
    });

    

    $data = $query->where('is_deleted', 0)->with('states', 'areas')
                  ->latest('id')
                  ->get();
        if (count($data) > 0) {
            $delimiter = ","; 
            $filename = "distributors.csv"; 

            // Create a file pointer 
            $f = fopen('php://memory', 'w'); 

            // Set column headers 
            
            $fields = array('SR','Brand Permission','Created By','Name','Employee ID','Mobile','WhatsApp Number','Official Email','Date of Joining','State','Area','Status','DATE'); 
            fputcsv($f, $fields, $delimiter); 

            $count = 1;

            foreach($data as $row) {
                $datetime = date('j F, Y h:i A', strtotime($row['created_at']));
                
				$assignedPermissions = [$row->brand];

                    $brandMap = [
                        1 => 'ONN',
                        2 => 'PYNK',
                        3 => 'Both',
                    ];

                    if (in_array(3, $assignedPermissions)) {
                        $brandPermissions = 'Both';
                    } elseif (in_array(1, $assignedPermissions) && in_array(2, $assignedPermissions)) {
                        $brandPermissions = 'Both';
                    } else {
                        $brandPermissions = collect($assignedPermissions)
                        ->map(fn($brand) => $brandMap[$brand] ?? $brand)
                        ->implode(', ');
                    }


                $lineData = array(
                    $count,
                    $brandPermissions,
                    $row['createdBy']['name'],
					$row['name'] ?? 'NA',
                    $row['employee_id']?? 'NA',
                    $row['contact'] ?? 'NA',
                    $row['whatsapp'] ?? 'NA',
                    $row['email'] ?? 'NA',
					$row['date_of_joining'] ?? 'NA',
                    $row['states']['name'] ?? 'NA',
                    $row['areas']['name'] ?? 'NA',
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
	
	//csv upload
	
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
                            'brand' => isset($filedata[0]) ? $filedata[0] : null,
                            'code' => isset($filedata[3]) ? $filedata[3] : null,
                            'name' => isset($filedata[4]) ? $filedata[4] : null,
                            'email' => isset($filedata[5]) ? $filedata[5] : null,
                            'contact' => isset($filedata[6]) ? $filedata[6] : null,
                            
                            'whatsapp' => isset($filedata[7]) ? $filedata[7] : null,
                            'statea_id' => isset($filedata[8]) ? $filedata[8] : null,
                            'area_id' => isset($filedata[9]) ? $filedata[9] : null,
                            'date_of_joining' => isset($filedata[10]) ? $filedata[10] : null,
                            'password' => isset($filedata[11]) ? $filedata[11] : null,
                            
                            
                        ];
                            
                        // Step 4: Validate each row's data
                        $validator = Validator::make($rowData, [
                            'name' => 'required|string|max:255',
                            'code' => 'required',
                            'contact' => 'required',
                        
                        ]);

                    if ($validator->fails()) {
                        // Accumulate errors with row number context
                        $errors[$i] = $validator->errors()->all();
                    } else {
                        $stateName=State::where('name',$rowData['state_id'])->first();
                        $areaName=Area::where('name',$rowData['area_id'])->first();
                            // Map brand text to numeric value
                                $brandValue = null;
                                if (!empty($rowData['brand'])) {
                                    $brandText = strtolower(trim($rowData['brand']));
                                    if ($brandText === 'ONN') {
                                        $brandValue = 1;
                                    } elseif ($brandText === 'PYNK') {
                                        $brandValue = 2;
                                    } elseif (in_array($brandText, ['Both', 'ONN,PYNK', 'PYNK,ONN'])) {
                                        $brandValue = 3;
                                    }
                                }
                        // Step 5: Save data if validation passes
                        $insertData = [
                            "code" => $rowData['code'],
                            "name" => $rowData['name'],
                            "email" => $rowData['email'],
                            "contact" => $rowData['contact'],
                            "whatsapp" => $rowData['whatsapp'],
                            "state_id" => $stateName->id,
                            "area_id" => $areaName->id,
                            "date_of_joining" => $rowData['date_of_joining'],
                             "brand" => $brandValue,
                            "password" => $rowData['password'],
                            
                            "status" => 1,
                            "is_deleted" => 0,
                            "created_at" => date('Y-m-d H:i:s'),
                            "updated_at" => date('Y-m-d H:i:s'),
                        ];
                        
                        Distributor::create($insertData);
                        
                       
                        
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

                    return redirect()->back()->with('success', 'CSV Import Complete. Total number of entries: ' . $successCount);
                }
                } else {
                    return redirect()->back()->with('failure', 'File too large. File must be less than 50MB.');
                }
            } else {
                return redirect()->back()->with('failure', 'Invalid File Extension. Supported extensions are ' . implode(', ', $valid_extension));
            }
        } else {
            return redirect()->back()->with('failure', 'No file found.');
        }

        // return redirect()->back();
    }
     
     
   public function distributorHierarchy(Request $request)
     {
        
        
        $keyword     = $request->keyword ?? '';
        $brandFilter = $request->brand ?? '';

        $user = auth()->user();
    
        // Base query
        $query = Distributor::select('distributors.*')->join('teams', 'teams.distributor_id', '=', 'distributors.id')->distinct();

        /**
         * STEP 1: Brand filter (1 = ONN, 2 = PYNK, 3 = BOTH)
         */
        if ($request->filled('brand')) {
            $query->where(function ($q) use ($request) {
                if ($request->brand == 3) {
                    // “Both” selected → show ONN (1), PYNK (2), and Both (3)
                    $q->whereIn('distributors.brand', [1, 2, 3]);
                } else {
                    // single brand selected → include that + both
                    $q->where('distributors.brand', $request->brand)
                    ->orWhere('distributors.brand', 3);
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
                        $q->whereIn('distributors.brand', [1, 2, 3]);
                    } else {
                        // user has limited brand(s)
                        $q->whereIn('distributors.brand', array_merge($userBrandPermissions, [3]));
                    }
                });
            }
        }

        /**
         * STEP 2: Keyword search (optional)
         */
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('distributors.name', 'like', '%'.$keyword.'%')
                 
                  ->orWhere('distributors.contact', 'like', '%'.$keyword.'%')
                  ->orWhere('distributors.code', 'like', '%'.$keyword.'%')
                  ->orWhere('distributors.email', 'like', '%'.$keyword.'%');
            });
        }

        /**
         * STEP 6: Fetch data with pagination
         */
        $data = $query->where('distributors.is_deleted',0)->where('teams.store_id',NULL)->orderBy('distributors.id', 'desc')->paginate(25);
        
        
        return view('distributor.hierarchy', compact( 'request','data'));
     }


      public function distributorhierarchyExportCSV(Request $request)
    {

       $keyword     = $request->keyword ?? '';
        $brandFilter = $request->brand ?? '';

        $user = auth()->user();
    
        // Base query
        $query = Distributor::select('distributors.*')->join('teams', 'teams.distributor_id', '=', 'distributors.id')->distinct();

        /**
         * STEP 1: Brand filter (1 = ONN, 2 = PYNK, 3 = BOTH)
         */
        if ($request->filled('brand')) {
            $query->where(function ($q) use ($request) {
                if ($request->brand == 3) {
                    // “Both” selected → show ONN (1), PYNK (2), and Both (3)
                    $q->whereIn('distributors.brand', [1, 2, 3]);
                } else {
                    // single brand selected → include that + both
                    $q->where('distributors.brand', $request->brand)
                    ->orWhere('distributors.brand', 3);
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
                        $q->whereIn('distributors.brand', [1, 2, 3]);
                    } else {
                        // user has limited brand(s)
                        $q->whereIn('distributors.brand', array_merge($userBrandPermissions, [3]));
                    }
                });
            }
        }

        /**
         * STEP 2: Keyword search (optional)
         */
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('distributors.name', 'like', '%'.$keyword.'%')
                 
                  ->orWhere('distributors.contact', 'like', '%'.$keyword.'%')
                  ->orWhere('distributors.code', 'like', '%'.$keyword.'%')
                  ->orWhere('distributors.email', 'like', '%'.$keyword.'%');
            });
        }

        /**
         * STEP 6: Fetch data with pagination
         */
        $data = $query->where('distributors.is_deleted',0)->orderBy('distributors.id', 'desc')->get();



        if (count($data) > 0) {
            $delimiter = ",";
            $filename = "distributor-hiererchy-".date('Y-m-d').".csv";

            // Create a file pointer
            $f = fopen('php://memory', 'w');

            // Set column headers
            $fields = array('SR','BRAND','DISTRIBUTOR','STATE','AREA','VP','RSM','ASM','ASE');
            fputcsv($f, $fields, $delimiter);

            $count = 1;

            foreach($data as $row) {
               $assignedPermissions = [$row->brand];

                    $brandMap = [
                        1 => 'ONN',
                        2 => 'PYNK',
                        3 => 'Both',
                    ];

                    if (in_array(3, $assignedPermissions)) {
                        $brandPermissions = 'Both';
                    } elseif (in_array(1, $assignedPermissions) && in_array(2, $assignedPermissions)) {
                        $brandPermissions = 'Both';
                    } else {
                        $brandPermissions = collect($assignedPermissions)
                        ->map(fn($brand) => $brandMap[$brand] ?? $brand)
                        ->implode(', ');
                    }
                $findDistributorTeamDetails= findDistributorTeamDetails($row->id);
                $lineData = array(
                    $count,
                    $brandPermissions,
                    $row ? $row->name : '',
                    $findDistributorTeamDetails[0]['state']?? '',
                    $findDistributorTeamDetails[0]['area']?? '',
                   
                    
                    $findDistributorTeamDetails[0]['vp']?? '',
                    $findDistributorTeamDetails[0]['rsm']?? '',
                    
                    $findDistributorTeamDetails[0]['asm']?? '',
                    $findDistributorTeamDetails[0]['ase']?? '', ''
                   
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


    public function range(Request $request, $id)
    {
        $user = auth()->user();
        $userBrands = DB::table('user_permission_categories')
                ->where('user_id', Auth::id())
                ->pluck('brand')
                ->toArray();
        
           
		$data = DistributorRange::where('distributor_id', $id)->where('is_deleted',0)->with('range','ase')->paginate(25);
		$collections = Collection::where('status', 1)->where('is_deleted',0)->orderBy('position')->get();
        $distributor = Distributor::findOrFail($id);
        $aseList = Team::where('distributor_id',$distributor->id)->with('ase')->orderBy('ase_id')->groupby('ase_id')->get();
		
        return view('distributor.range', compact('request','data', 'collections', 'id', 'distributor', 'aseList'));
    }

	public function rangeSave(Request $request, $id)
    {
		$request->validate([
			"collection_id" => "required|integer|min:1",
			"distributor_id" => "required|integer|min:1",
			"user_id" => "required|integer|min:1",
		]);

		$check = DB::table('distributor_ranges')->where('distributor_id', $request->distributor_id)->where('collection_id', $request->collection_id)->where('is_deleted',0)->first();

		if($check) {
			return redirect()->back()->with('failure', 'This Range already exists to this Distributor');
		} else {
			DB::table('distributor_ranges')->insert([
                'distributor_id' => $request->distributor_id, 
                'collection_id' => $request->collection_id,
                'user_id' => $request->user_id,
                'brand' => $request->brand,
            ]);
		}

		return redirect()->back()->with('success', 'Range Added to this Distributor');
    }

	public function rangedestroy(Request $request, $id)
    {
		$data = DistributorRange::where('id', $id)->first();
        $data->is_deleted=1;
        $data->save();
        return redirect()->back()->with('success', 'Range Deleted for this Distributor');
    }


    public function userTeamAdd(Request $request)
    {
        //dd($request->all());
		$request->validate([
			"distributor_id" => "required|integer",
			"ase_id" => "required|integer",
            "stateId" => "required",
            "areaId" => "required",
		]);
        $state_id=State::where('id',$request->stateId)->first();
        $area_id=Area::where('id',$request->areaId)->first();
		$newEntry = new Team;
        $newEntry->brand = $request->brand;
        $newEntry->state_id = $request->stateId;
        $newEntry->area_id = $request->areaId;
		$newEntry->distributor_id = $request['distributor_id'];
        $newEntry->vp_id = $request['vp_id'];
        $newEntry->rsm_id = $request['rsm_id'];
        $newEntry->asm_id = $request['asm_id'];
        $newEntry->ase_id = $request['ase_id'];
		$newEntry->save();
		//dd($newEntry);
        if($newEntry){
		    return redirect()->back()->with('success', 'Team Added to this Distributor');
        }
    }

     //team update
     public function userTeamEdit(Request $request,$id)
     {
         //dd($request->all());
         $request->validate([
             "distributor_id" => "required|integer",
             "ase_id" => "required|integer",
             "stateId" => "required",
             "areaId" => "required",
         ]);
         $state_id=State::where('id',$request->stateId)->first();
         $area_id=Area::where('id',$request->areaId)->first();
         $newEntry = Team::findOrfail($id);
         $newEntry->state_id = $state_id->id ?? '';
		 if(!empty($request->areaId)){
        	 $newEntry->area_id = $area_id->id ?? '';
		 } 
         $newEntry->distributor_id = $request['distributor_id'] ?? '';
         $newEntry->vp_id = $request['vp_id'] ?? '';
         $newEntry->rsm_id = $request['rsm_id'] ?? '';
         $newEntry->asm_id = $request['asm_id'] ?? '';
         $newEntry->ase_id = $request['ase_id'] ?? '';
         $newEntry->save();
         if($newEntry){
             return redirect()->back()->with('success', 'Team Updated to this Distributor');
         }
     }

    //team delete
    public function userTeamDestroy(Request $request,$id)
    {
		$data = Team::where('id', $id)->first();
        $data->is_deleted=1;
        $data->save();
        return redirect()->back()->with('success', 'Team data Deleted for this Distributor');
    }


}
