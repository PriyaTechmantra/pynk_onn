<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\State;
use App\Models\Area;
use App\Models\UserArea;
use App\Models\Team;
use App\Models\Store;
use App\Models\Distributor;
use App\Models\Notification;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
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
            'type' => 'required',
            'employee_id' => 'required|unique:employees',
            'designation' => 'required',
            'mobile' => 'required|unique:employees',
            'email' =>  'nullable|email',
            'personal_mail' =>  'nullable|email',
            'state' => 'required',
            'password' => 'required',
            'brand' => 'required',
        ]);
        
        $data = Employee::create([
            'name'        => $request->name,
            'employee_id' => $request->employee_id,
            'designation' => $request->designation,
            'email'       => $request->email,
            'personal_mail'       => $request->personal_mail,
            'mobile'      => $request->mobile,
            'alt_number1'      => $request->alt_number1,
            'alt_number2'      => $request->alt_number2,
            'alt_number3'      => $request->alt_number3,
            'whatsapp_no'      => $request->whatsapp_no,
            'type'        => $request->type,
            'state'       => $request->state,
            'city'        => $request->area,
            'date_of_joining'  => $request->date_of_joining,
            'brand'  => $request->brand,
            'created_by'  => auth()->id(),
            'password'    => Hash::make($request->password), // hash here ✅
        ]);
        
        return redirect()->route('employees.index')
                        ->with('success','employee created successfully.');
    }
    
    /**
     * Display the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request,$id): View
    {
        $data = (object) [];
        $data->employee = Employee::find($id);
        // VP
         // VP
        if ($data->employee->type == 1) {
            $employeeBrand = $data->employee->brand;
            $user = auth()->user();

            // Logged-in user brand permissions
            $userBrands = DB::table('user_permission_categories')
                ->where('user_id', $user->id)
                ->pluck('brand')
                ->toArray();

            // Request filters
            $user_type = $request->user_type ?? '';
            $keyword = $request->keyword ?? '';
            $state = $request->state ?? '';
            $area = $request->area ?? '';
            $brandFilter = $request->brand ?? '';
            // Determine reporting column based on employee type
            $column = match ($data->employee->type) {
                1 => 'vp_id', // VP
                2 => 'rsm_id', // RSM
                3 => 'asm_id', // ASM
                4 => 'ase_id', // ASE
                default => null,
            };

            if (!$column) {
                return response()->json(['error' => true, 'message' => 'Invalid employee type.']);
            }

            // 🔹 Base Query
            $query = Team::where($column, $data->employee->id)
                ->where('is_deleted', 0)
                ->where('status', 1);

            // 🔹 Apply filters
            if ($user_type) {
                // Example: user_type = 2 means show RSM level under this VP
                $typeMap = [1 => 'vp_id', 2 => 'rsm_id', 3 => 'asm_id', 4 => 'ase_id'];
                $query->whereNotNull($typeMap[$user_type]);
            }

            if ($state && $state !== 'all') {
                $query->where('state_id', $state);
            }

            if ($area && $area !== 'all') {
                $query->where('area_id', $area);
            }

            if (!empty($keyword)) {
                $query->where(function ($query) use ($keyword) {
                    $query->whereHas('rsm', fn($q) => $q->where('name', 'like', "%{$keyword}%"))
                        ->orWhereHas('asm', fn($q) => $q->where('name', 'like', "%{$keyword}%"))
                        ->orWhereHas('ase', fn($q) => $q->where('name', 'like', "%{$keyword}%"));
                });
            }

            // 🔹 Brand Logic — unified and fixed
            $query->where(function ($q) use ($employeeBrand, $userBrands, $brandFilter) {

                // If dropdown filter selected
                if ($brandFilter && $brandFilter !== 'All') {
                    if ($brandFilter == 3) {
                        // "Both" selected
                        $q->whereIn('brand', [1, 2, 3]);
                    } else {
                        // ONN or PYNK selected
                        $q->whereIn('brand', [$brandFilter, 3]);
                    }
                } else {
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
                }
            });

            // 🔹 Get results
            $data->team = $query->with(['vp', 'rsm', 'asm', 'ase'])
                ->orderBy('id', 'desc')
                ->paginate(25);
            $state = State::where('status', 1)
            ->where('is_deleted', 0)
            ->orderBy('name')
            ->get();
            return view('employee.detail.vp', compact('data', 'id', 'request','state'));
        }
        //RSM
         elseif ($data->employee->type == 2) {
             $employeeBrand = $data->employee->brand;
             $user = auth()->user();

            // Logged-in user brand permissions
            $userBrands = DB::table('user_permission_categories')
                ->where('user_id', $user->id)
                ->pluck('brand')
                ->toArray();

            // Request filters
            $user_type = $request->user_type ?? '';
            $keyword = $request->keyword ?? '';
            $state = $request->state ?? '';
            $area = $request->area ?? '';
            $brandFilter = $request->brand ?? '';
            // Determine reporting column based on employee type
            $column = match ($data->employee->type) {
                1 => 'vp_id', // VP
                2 => 'rsm_id', // RSM
                3 => 'asm_id', // ASM
                4 => 'ase_id', // ASE
                default => null,
            };

            if (!$column) {
                return response()->json(['error' => true, 'message' => 'Invalid employee type.']);
            }

            // 🔹 Base Query
            $query = Team::where($column, $data->employee->id)
                ->where('is_deleted', 0)
                ->where('status', 1);

            // 🔹 Apply filters
            if ($user_type) {
                // Example: user_type = 2 means show RSM level under this VP
                $typeMap = [1 => 'vp_id', 2 => 'rsm_id', 3 => 'asm_id', 4 => 'ase_id'];
                $query->whereNotNull($typeMap[$user_type]);
            }

            if ($state && $state !== 'all') {
                $query->where('state_id', $state);
            }

            if ($area && $area !== 'all') {
                $query->where('area_id', $area);
            }

            if (!empty($keyword)) {
                $query->where(function ($query) use ($keyword) {
                    $query->whereHas('vp', fn($q) => $q->where('name', 'like', "%{$keyword}%"))
                         ->orWhereHas('rsm', fn($q) => $q->where('name', 'like', "%{$keyword}%"))
                        ->orWhereHas('asm', fn($q) => $q->where('name', 'like', "%{$keyword}%"))
                        ->orWhereHas('ase', fn($q) => $q->where('name', 'like', "%{$keyword}%"));
                });
            }

            // 🔹 Brand Logic — unified and fixed
            $query->where(function ($q) use ($employeeBrand, $userBrands, $brandFilter) {

                // If dropdown filter selected
                if ($brandFilter && $brandFilter !== 'All') {
                    if ($brandFilter == 3) {
                        // "Both" selected
                        $q->whereIn('brand', [1, 2, 3]);
                    } else {
                        // ONN or PYNK selected
                        $q->whereIn('brand', [$brandFilter, 3]);
                    }
                } else {
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
                }
            });

            // 🔹 Get results
            $data->team = $query->with(['vp', 'rsm', 'asm', 'ase'])
                ->orderBy('id', 'desc')
                ->paginate(25);
            $state = State::where('status', 1)
            ->where('is_deleted', 0)
            ->orderBy('name')
            ->get();
            return view('employee.detail.rsm', compact('data', 'id', 'request','state'));
        }
         // ASM
         elseif ($data->employee->type == 3) {
            $employeeBrand = $data->employee->brand;
             $user = auth()->user();

            // Logged-in user brand permissions
            $userBrands = DB::table('user_permission_categories')
                ->where('user_id', $user->id)
                ->pluck('brand')
                ->toArray();

            // Request filters
            $user_type = $request->user_type ?? '';
            $keyword = $request->keyword ?? '';
            $state = $request->state ?? '';
            $area = $request->area ?? '';
            $brandFilter = $request->brand ?? '';
            // Determine reporting column based on employee type
            $column = match ($data->employee->type) {
                1 => 'vp_id', // VP
                2 => 'rsm_id', // RSM
                3 => 'asm_id', // ASM
                4 => 'ase_id', // ASE
                default => null,
            };

            if (!$column) {
                return response()->json(['error' => true, 'message' => 'Invalid employee type.']);
            }

            // 🔹 Base Query
            $query = Team::where($column, $data->employee->id)
                ->where('is_deleted', 0)
                ->where('status', 1);

            // 🔹 Apply filters
            if ($user_type) {
                // Example: user_type = 2 means show RSM level under this VP
                $typeMap = [1 => 'vp_id', 2 => 'rsm_id', 3 => 'asm_id', 4 => 'ase_id'];
                $query->whereNotNull($typeMap[$user_type]);
            }

            if ($state && $state !== 'all') {
                $query->where('state_id', $state);
            }

            if ($area && $area !== 'all') {
                $query->where('area_id', $area);
            }

            if (!empty($keyword)) {
                $query->where(function ($query) use ($keyword) {
                    $query->whereHas('vp', fn($q) => $q->where('name', 'like', "%{$keyword}%"))
                         ->orWhereHas('rsm', fn($q) => $q->where('name', 'like', "%{$keyword}%"))
                        ->orWhereHas('asm', fn($q) => $q->where('name', 'like', "%{$keyword}%"))
                        ->orWhereHas('ase', fn($q) => $q->where('name', 'like', "%{$keyword}%"));
                });
            }

            // 🔹 Brand Logic — unified and fixed
            $query->where(function ($q) use ($employeeBrand, $userBrands, $brandFilter) {

                // If dropdown filter selected
                if ($brandFilter && $brandFilter !== 'All') {
                    if ($brandFilter == 3) {
                        // "Both" selected
                        $q->whereIn('brand', [1, 2, 3]);
                    } else {
                        // ONN or PYNK selected
                        $q->whereIn('brand', [$brandFilter, 3]);
                    }
                } else {
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
                }
            });

            // 🔹 Get results
            $data->team = $query->with(['vp', 'rsm', 'asm', 'ase'])
                ->orderBy('id', 'desc')
                ->paginate(25);
            $state = State::where('status', 1)
            ->where('is_deleted', 0)
            ->orderBy('name')
            ->get();
            
            return view('employee.detail.asm', compact('data', 'id', 'request','state'));
        }
        // ASE
        
        
        elseif ($data->employee->type == 4) {
            $brandFilter = $request->get('brand');
            $storebrandFilter = $request->get('storebrand'); 
            $user = Auth::user();
            $userBrands = DB::table('user_permission_categories')
                ->where('user_id', $user->id)
                ->pluck('brand')
                ->toArray();
            // Employee's brand permission (1=ONN, 2=PYNK, 3=Both)
            $employeeBrand = $data->employee->brand ?? null;
            $data->team = Team::where('ase_id', $data->employee->id)->where('store_id', null)->where('is_deleted', 0)->with('vp','rsm','asm','ase')->first();
            $data->workAreaList=userArea::where('user_id',$data->employee->id)->where('is_deleted', 0)->groupby('area_id')->with('area')->get();
            //$data->distributorList = Team::where('ase_id', $data->employee->id)->where('distributor_id', '!=', null)->where('store_id',NULL)->where('is_deleted', 0)->groupBy('distributor_id')->orderBy('id','desc')->get();
            
			$data->areaDetail= Area::orderby('name')->get();
            $data->state = State::where('status', 1)
                ->where('is_deleted', 0)
                ->orderBy('name')
                ->get();
        
        
            // ==================== STORE LIST ====================
                $storeQuery = Store::where('stores.user_id', $data->employee->id)
                    ->where('stores.is_deleted', 0)
                    ->join('teams', function($join) use ($data) {
                        $join->on('stores.id', '=', 'teams.store_id')
                            ->whereNOTNull('teams.store_id')
                            ->where('teams.is_deleted', 0);
                    })
                    ->select('stores.*')
                    ->orderBy('stores.name');

                $storeQuery->where(function ($q) use ($data, $storebrandFilter, $userBrands) {
                         $employeeBrand = $data->employee->brand ?? null;
                    

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
			 // 🔹 Distributor Query
            $query = Team::where('ase_id', $data->employee->id)
                ->whereNotNull('distributor_id')
                ->whereNull('store_id')
                ->where('status', 1)
                ->where('is_deleted', 0)
                ->orderBy('id', 'desc');

                
            // 🔹 If brand dropdown is applied
            if ($brandFilter) {
                $query->whereHas('distributor', function ($q) use ($brandFilter) {
                    if ($brandFilter == 3) {
                        $q->whereIn('brand', [1, 2, 3]);
                    } else {
                        $q->whereIn('brand', [$brandFilter]);
                    }
                });
            } 
            else {
                // 🔹 No filter → Apply Employee + User Brand Logic
                $query->whereHas('distributor', function ($q) use ($employeeBrand, $userBrands) {

                    $allowedBrands = [];

                    // CASE 1: Both have Both → Show All
                    if (($employeeBrand == 3) && in_array(3, $userBrands)) {
                        $allowedBrands = [1, 2, 3];
                    }
                    // CASE 2: Employee Both, User ONN → Only ONN
                    elseif ($employeeBrand == 3 && in_array(1, $userBrands)) {
                        $allowedBrands = [1];
                    }
                    // CASE 3: Employee ONN, User Both → Only ONN
                    elseif ($employeeBrand == 1 && in_array(3, $userBrands)) {
                        $allowedBrands = [1];
                    }
                    // CASE 4: Both ONN → Only ONN
                    elseif ($employeeBrand == 1 && in_array(1, $userBrands)) {
                        $allowedBrands = [1];
                    }
                    // CASE 5: Other (like ONN+PYNK mismatch) → no distributors
                    else {
                        $allowedBrands = [];
                    }

                    if (!empty($allowedBrands)) {
                        $q->whereIn('brand', $allowedBrands);
                    } else {
                        // Ensures no results if no matching permission
                        $q->whereRaw('1=0');
                    }
                });
            }

            $data->distributorList = $query->groupBy('distributor_id')->get();

            //all distributor 

            $query1 = Distributor::where('status', 1)
                ->where('is_deleted', 0)
                ->orderBy('name', 'asc');

                
            
                // 🔹 No filter → Apply Employee + User Brand Logic
                $query1->where(function ($q) use ($employeeBrand, $userBrands) {

                    $allowedBrands = [];

                    // CASE 1: Both have Both → Show All
                    if (($employeeBrand == 3) && in_array(3, $userBrands)) {
                        $allowedBrands = [1, 2, 3];
                    }
                    // CASE 2: Employee Both, User ONN → Only ONN
                    elseif ($employeeBrand == 3 && in_array(1, $userBrands)) {
                        $allowedBrands = [1];
                    }
                    // CASE 3: Employee ONN, User Both → Only ONN
                    elseif ($employeeBrand == 1 && in_array(3, $userBrands)) {
                        $allowedBrands = [1];
                    }
                    // CASE 4: Both ONN → Only ONN
                    elseif ($employeeBrand == 1 && in_array(1, $userBrands)) {
                        $allowedBrands = [1];
                    }
                    // CASE 5: Other (like ONN+PYNK mismatch) → no distributors
                    else {
                        $allowedBrands = [];
                    }

                    if (!empty($allowedBrands)) {
                        $q->whereIn('brand', $allowedBrands);
                    } else {
                        // Ensures no results if no matching permission
                        $q->whereRaw('1=0');
                    }
                });
            

            $distributorList = $query1->get();

            //all ase

            $query2 = Employee::where('type',4)->where('status', 1)
                ->where('is_deleted', 0)
                ->orderBy('name', 'asc');

                
            
                // 🔹 No filter → Apply Employee + User Brand Logic
                $query2->where(function ($q) use ($employeeBrand, $userBrands) {

                    $allowedBrands = [];

                    // CASE 1: Both have Both → Show All
                    if (($employeeBrand == 3) && in_array(3, $userBrands)) {
                        $allowedBrands = [1, 2, 3];
                    }
                    // CASE 2: Employee Both, User ONN → Only ONN
                    elseif ($employeeBrand == 3 && in_array(1, $userBrands)) {
                        $allowedBrands = [1];
                    }
                    // CASE 3: Employee ONN, User Both → Only ONN
                    elseif ($employeeBrand == 1 && in_array(3, $userBrands)) {
                        $allowedBrands = [1];
                    }
                    // CASE 4: Both ONN → Only ONN
                    elseif ($employeeBrand == 1 && in_array(1, $userBrands)) {
                        $allowedBrands = [1];
                    }
                    // CASE 5: Other (like ONN+PYNK mismatch) → no distributors
                    else {
                        $allowedBrands = [];
                    }

                    if (!empty($allowedBrands)) {
                        $q->whereIn('brand', $allowedBrands);
                    } else {
                        // Ensures no results if no matching permission
                        $q->whereRaw('1=0');
                    }
                });
            //}

            $aseList = $query2->with('stateDetail')->get();
            return view('employee.detail.ase', compact('data', 'id', 'request','distributorList','aseList'));
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
       
        $updateData = $request->except('password','_token','_method'); // take everything except password
        //dd($updateData);
        // If password is present, hash it
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }
       
        

        $data->update($updateData);
                // Normalize brand value
        
        
        
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
                            'brand' => isset($filedata[0]) ? $filedata[0] : null,
                            'type' => isset($filedata[1]) ? $filedata[1] : null,
                            'designation' => isset($filedata[2]) ? $filedata[2] : null,
                            'employee_id' => isset($filedata[3]) ? $filedata[3] : null,
                            'name' => isset($filedata[4]) ? $filedata[4] : null,
                            'email' => isset($filedata[5]) ? $filedata[5] : null,
                            'mobile' => isset($filedata[6]) ? $filedata[6] : null,
                            
                            'whatsapp_no' => isset($filedata[7]) ? $filedata[7] : null,
                            'state' => isset($filedata[8]) ? $filedata[8] : null,
                            'city' => isset($filedata[9]) ? $filedata[9] : null,
                            'date_of_joining' => isset($filedata[10]) ? $filedata[10] : null,
                            'password' => isset($filedata[11]) ? $filedata[11] : null,
                            
                            
                        ];
                            
                        // Step 4: Validate each row's data
                        $validator = Validator::make($rowData, [
                            'name' => 'required|string|max:255',
                            'employee_id' => 'required|string|max:255|unique:employees',
                            'type' => 'required',
                            'designation' => 'required',
                            'mobile' => 'required',
                        
                        ]);

                    if ($validator->fails()) {
                        // Accumulate errors with row number context
                        $errors[$i] = $validator->errors()->all();
                    } else {
                        $stateName=State::where('name',$rowData['state'])->first();
                        $areaName=Area::where('name',$rowData['city'])->first();
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
                            "type" => $rowData['type'],
                            "designation" => $rowData['designation'],
                            "employee_id" => $rowData['employee_id'],
                            "name" => $rowData['name'],
                            "email" => $rowData['email'],
                            "mobile" => $rowData['mobile'],
                            "whatsapp_no" => $rowData['whatsapp_no'],
                            "state" => $stateName->id,
                            "city" => $areaName->id,
                            "date_of_joining" => $rowData['date_of_joining'],
                             "brand" => $brandValue,
                            "password" => $rowData['password'],
                            
                            "status" => 1,
                            "is_deleted" => 0,
                            "created_at" => date('Y-m-d H:i:s'),
                            "updated_at" => date('Y-m-d H:i:s'),
                        ];
                        
                        Employee::create($insertData);
                        
                       
                        
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


    //export

    public function employeeExport(Request $request)
	{
       
        $user_type   = $request->type ?? '';
        $state       = $request->state ?? '';
        $area        = $request->area ?? '';
        $keyword     = $request->keyword ?? '';
        $brandFilter = $request->brand ?? '';
        
    $query = Employee::query();
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
                        $q->whereIn('employees.brand', [1, 2, 3]);
                    } else {
                        // user has limited brand(s)
                        $q->whereIn('employees.brand', array_merge($userBrandPermissions, [3]));
                    }
                });
            }
        }
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

    

    $data = $query->where('is_deleted', 0)->with('stateDetail', 'area')
                  ->latest('id')
                  ->get();
        if (count($data) > 0) {
            $delimiter = ","; 
            $filename = "employee.csv"; 

            // Create a file pointer 
            $f = fopen('php://memory', 'w'); 

            // Set column headers 
            
            $fields = array('SR','Brand Permission','User Type','Name','Designation','Employee ID','Mobile','WhatsApp Number','Alt. Mobile 1','Alt. Mobile 2','Alt. Mobile 3','Official Email','Personal Email','Date of Joining','State','Area','Working Area','Status','DATE'); 
            fputcsv($f, $fields, $delimiter); 

            $count = 1;

            foreach($data as $row) {
                $datetime = date('j F, Y h:i A', strtotime($row['created_at']));
                $area ='';
                $areaDetail = DB::table('user_areas')->where('user_id','=',$row->id)->where('is_deleted', 0)->groupby('area_id')->get();
                                        
                if(!empty($areaDetail)) {
                    foreach($areaDetail as $key => $obj) {
                        $areaList=DB::table('areas')->where('id','=',$obj->area_id)->first();
                        $area .= $areaList->name ??'';
                        if((count($areaDetail) - 1) != $key) $area .= ', ';
                    }
                }
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
                    $row->designation ? $row->designation : userTypeName($row->type) ,
					$row['name'] ?? 'NA',
                    $row['designation']?? 'NA',
                    $row['employee_id']?? 'NA',
                    $row['mobile'] ?? 'NA',
                    $row['whatsapp_no'] ?? 'NA',
                    $row['alt_number1'] ?? 'NA',
                    $row['alt_number2'] ?? 'NA',
                    $row['alt_number3'] ?? 'NA',
                    $row['email'] ?? 'NA',
                    $row['personal_mail'] ?? 'NA',
					$row['date_of_joining'] ?? 'NA',
                    $row['stateDetail']['name'] ?? 'NA',
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
        //dd($request->all());
          $area=UserArea::where('user_id',$request->employee_id)->where('state_id',$request->state_id)->whereIN('area_id',$request->city)->first();
		  if(empty($area)){
            foreach($request->city as $row){
                $areaSave=New UserArea();
                $areaSave->user_id = $request->employee_id ?? '';
                $areaSave->state_id = $request->state ?? '';
                $areaSave->area_id = $row ?? '';
                $areaSave->save();
            }
       
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


    public function typeWiseName(Request $request,$type)
    {
		if($type=='all'){
			$typeQuery = '';
		} else {
			$typeQuery = "where type='$type'";
		}

		$name = DB::select("SELECT DISTINCT name ,id from employees ".$typeQuery." order by name");

        $resp = [
            'type' => $type,
            'name' => [],
        ];

        foreach($name as $item) {
            $resp['name'][] = [
				'id' =>  $item->id,
                'name' => $item->name
            ];
        }

		return response()->json(['error' => false, 'message' => 'Type wise name list', 'data' => $resp]);
    }

    public function notificationList(Request $request)
    {
        $date_from = $request->date_from ?? '';
        $date_to = $request->date_to ?? '';
        $keyword = $request->term ?? '';

        $query = Notification::query();

        $query->when($date_from, function ($query) use ($date_from) {
            $query->whereDate('created_at', '>=', $date_from);
        });

        $query->when($date_to, function ($query) use ($date_to) {
            $query->whereDate('created_at', '<=', $date_to);
        });

        $query->when($keyword, function ($query) use ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                ->orWhere('body', 'like', "%{$keyword}%");
            });
        });

        $data = $query->latest('id')->with('senderDetails','receiverDetails')->paginate(25);

        return view('notification.index', compact('data', 'request'));
    }

    public function notificationExportCSV(Request $request)
    {
        $date_from = $request->date_from ?? '';
        $date_to = $request->date_to ?? '';
        $keyword = $request->term ?? '';

        $query = Notification::query();

        $query->when($date_from, function ($query) use ($date_from) {
            $query->whereDate('created_at', '>=', $date_from);
        });

        $query->when($date_to, function ($query) use ($date_to) {
            $query->whereDate('created_at', '<=', $date_to);
        });

        $query->when($keyword, function ($query) use ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                ->orWhere('body', 'like', "%{$keyword}%");
            });
        });

        $data = $query->latest('id')->with('senderDetails', 'receiverDetails')->get();

        if ($data->isEmpty()) {
            return back()->with('status', 'No data found for export.');
        }

        $filename = 'notifications_' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($data) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'S.No', 'Type', 'Sender', 'Receiver', 'Title', 'Body', 'Created At', 'Status'
            ]);

            $serial = 1;

            foreach ($data as $item) {
                fputcsv($handle, [
                    $serial++,
                    $item->type,
                    $item->senderDetails->name ?? '',
                    $item->receiverDetails->name ?? '',
                    $item->title,
                    $item->body,
                    $item->created_at->format('Y-m-d H:i:s'),
                    $item->read_flag ? 'Read' : 'Unread',
                ]);
            }

            fclose($handle);
        };

        return Response::stream($callback, 200, $headers);
    }


    public function employeeHierarchy(Request $request)
     {
        
        
        $keyword     = $request->keyword ?? '';
        $brandFilter = $request->brand ?? '';

        $user = auth()->user();
    
        // Base query
        $query = Employee::select('employees.*')->join('teams', 'teams.ase_id', '=', 'employees.id')->distinct();

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
         * STEP 2: Keyword search (optional)
         */
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('employees.name', 'like', '%'.$keyword.'%')
                 
                  ->orWhere('employees.mobile', 'like', '%'.$keyword.'%')
                  ->orWhere('employees.employee_id', 'like', '%'.$keyword.'%')
                  ->orWhere('employees.email', 'like', '%'.$keyword.'%');
            });
        }

        /**
         * STEP 6: Fetch data with pagination
         */
        $data = $query->where('employees.type',4)->where('employees.is_deleted',0)->where('teams.store_id',NULL)->orderBy('employees.id', 'desc')->paginate(25);
        
        
        return view('employee.hierarchy', compact( 'request','data'));
     }


      public function hierarchyExportCSV(Request $request)
    {

       $keyword     = $request->keyword ?? '';
        $brandFilter = $request->brand ?? '';

        $user = auth()->user();
    
        // Base query
        $query = Employee::select('employees.*')->join('teams', 'teams.ase_id', '=', 'employees.id')->distinct();

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
         * STEP 2: Keyword search (optional)
         */
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('employees.name', 'like', '%'.$keyword.'%')
                 
                  ->orWhere('employees.mobile', 'like', '%'.$keyword.'%')
                  ->orWhere('employees.employee_id', 'like', '%'.$keyword.'%')
                  ->orWhere('employees.email', 'like', '%'.$keyword.'%');
            });
        }

        /**
         * STEP 6: Fetch data with pagination
         */
        $data = $query->where('employees.type',4)->where('employees.is_deleted',0)->orderBy('employees.id', 'desc')->get();



        if (count($data) > 0) {
            $delimiter = ",";
            $filename = "employee-hiererchy-".date('Y-m-d').".csv";

            // Create a file pointer
            $f = fopen('php://memory', 'w');

            // Set column headers
            $fields = array('SR','BRAND','STATE','AREA','VP','RSM','ASM','ASE');
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
                $findTeamDetails= findTeamDetails($row->id, $row->type);
                $lineData = array(
                    $count,
                    $brandPermissions,
                    $findTeamDetails[0]['state']?? '',
                    $findTeamDetails[0]['area']?? '',
                   
                    
                    $findTeamDetails[0]['vp']?? '',
                    $findTeamDetails[0]['rsm']?? '',
                    
                    $findTeamDetails[0]['asm']?? '',
                    $row ? $row->name : ''
                   
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

    public function activityList(Request $request)
    {
        $date_from = $request->date_from ?? '';
        $date_to = $request->date_to ?? '';

        $query = Activity::query();

        $query->when($date_from, fn($q) => $q->whereDate('date', '>=', $date_from));
        $query->when($date_to, fn($q) => $q->whereDate('date', '<=', $date_to));

        $query->when($request->user_name, fn($q) => $q->where('user_id', $request->user_name));

        $query->when($request->type, function ($q) use ($request) {
            $q->whereHas('user', fn($uq) => $uq->where('type', $request->type));
        });

        $query->when($request->brand_selection, function ($q) use ($request) {
            $brand = (int)$request->brand_selection;

            $q->whereHas('user', function ($uq) use ($brand) {
                if ($brand === 1) {
                    $uq->whereIn('brand', [1, 3]);
                } elseif ($brand === 2) {
                    $uq->whereIn('brand', [2, 3]);
                } elseif ($brand === 3) {
                    $uq->where('brand', 3);
                }
            });
        });

        $employees = Employee::select('id', 'name')->orderBy('name')->get();

        $userTypes = Employee::select('type')
            ->whereNotNull('type')
            ->distinct()
            ->pluck('type')
            ->toArray();

        $data = $query->latest('id')->paginate(25);

        return view('activity.index', compact('data', 'employees', 'userTypes', 'request'));
    }


    public function activityExportCSV(Request $request)
    {
        $date_from = $request->date_from ?? '';
        $date_to = $request->date_to ?? '';

        $query = Activity::query();

        $query->when($date_from, fn($q) => $q->whereDate('date', '>=', $date_from));
        $query->when($date_to, fn($q) => $q->whereDate('date', '<=', $date_to));

        $query->when($request->user_name, fn($q) => $q->where('user_id', $request->user_name));
        $query->when($request->type, fn($q) => $q->whereHas('user', fn($uq) => $uq->where('type', $request->type)));

        $query->when($request->brand_selection, function ($q) use ($request) {
            $brand = (int)$request->brand_selection;

            $q->whereHas('user', function ($uq) use ($brand) {
                if ($brand === 1) {
                    $uq->whereIn('brand', [1, 3]);
                } elseif ($brand === 2) {
                    $uq->whereIn('brand', [2, 3]);
                } elseif ($brand === 3) {
                    $uq->where('brand', 3);
                }
            });
        });

        $activities = $query->with(['user', 'store'])->latest('id')->get();

        $typeLabels = [
            1 => 'VP',
            2 => 'RSM',
            3 => 'ASM',
            4 => 'ASE',
        ];

        $response = new StreamedResponse(function () use ($activities, $typeLabels) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'S.No',
                'User',
                'Store',
                'Activity',
                'DateTime',
                'Comment',
                'Location',
            ]);

            $serial = 1;
            foreach ($activities as $activity) {
                fputcsv($handle, [
                    $serial++,
                    ($typeLabels[$activity->user->type ?? ''] ?? 'N/A') . ' - ' . ($activity->user->name ?? ''),
                    $activity->store->name ?? '',
                    $activity->type,
                    $activity->date . ' ' . $activity->time,
                    $activity->comment,
                    $activity->location,
                ]);
            }

            fclose($handle);
        });

        $filename = 'activities_' . now()->format('Y-m-d') . '.csv';
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', "attachment; filename=\"$filename\"");

        return $response;
    }


}