<?php

namespace App\Http\Controllers;

use App\Models\Distributor;
use App\Models\State;
use App\Models\Area;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Auth;
use DB;
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
    public function show($id): View
    {
        $data = (object) [];
        $data->distributor = Distributor::find($id);
        $area=Area::where('id', $data->distributor->area_id)->first();
			
        $data->team = Team::where('distributor_id', $data->distributor->id)->where('store_id','=',NULL)->first();
			
       
		$data->storeList = Team::where('distributor_id', $data->distributor->id)->where('store_id','!=',null)->groupBy('store_id')->with('store')->get();
        return view('distributor.view',compact('data'));
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit($id): View
    {
        $data = Distributor::find($id);
        $office=Office::all();
        $bookshelve=Bookshelve::all();
        $category=BookCategory::all();
        return view('lms.book.edit',compact('data','office','bookshelve','category'));
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
         $request->validate([
            'office_id' => [
                'required',
                'string'
            ]
        ]);
    
        $data = Book::find($id);
        $data->office_id=$request['office_id'];
        $data->bookshelves_id=$request['bookshelves_id'];
        $data->category_id=$request['category_id'];
        $data->user_id=Auth::user()->id;
        $data->title=$request['title']??'';
        $data->book_no=$request['book_no']??'';
        $data->author=$request['author']??'';
        $data->publisher=$request['publisher']??'';
        $data->edition=$request['edition']??'';
        $data->page=$request['page']??'';
        $data->year=$request['year']??'';
        $data->quantity=$request['quantity']??'';
        $data->save();
    
        return redirect()->back()
                        ->with('success','Bookshelve updated successfully');
    }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy($id): RedirectResponse
    {
        $data = Book::find($id);
        $data->is_deleted=1;
        $data->deleted_at=now();
        $data->save();
    
        return redirect()->route('books.index')
                        ->with('success','Book deleted successfully');
    }
    
    public function status($id): RedirectResponse
    {
        $data = Book::find($id);
        $status = ($data->status == 1) ? 0 : 1;
        $data->status = $status;
        $data->save();
    
        return redirect()->route('books.index')
                        ->with('success','Book status changed successfully');
    }
    
    
    //csv export
    
    public function csvExport(Request $request)
	{
		 $query = $request->input('keyword');
    $officeId = $request->input('office_id');
    $bookshelveId = $request->input('bookshelves_id');
    $categoryId = $request->input('category_id');
    $issueDateFrom = $request->input('issue_date_from');
    $issueDateTo = $request->input('issue_date_to');

    $data = Book::where(function($q) use ($query, $officeId, $bookshelveId, $categoryId, $issueDateFrom, $issueDateTo) {
        if ($query) {
            $q->where('title', 'LIKE', "%{$query}%")
              ->orWhere('author', 'LIKE', "%{$query}%")
              ->orWhere('publisher', 'LIKE', "%{$query}%")
              ->orWhere('edition', 'LIKE', "%{$query}%")
              ->orWhere('page', 'LIKE', "%{$query}%")
              ->orWhere('quantity', 'LIKE', "%{$query}%")
              ->orWhere('uid', 'LIKE', "%{$query}%");
        }
        if ($officeId) {
            $q->where('office_id', $officeId);
        }
        if ($bookshelveId) {
            $q->where('bookshelves_id', $bookshelveId);
        }
        if ($categoryId) {
            $q->where('category_id', $categoryId);
        }
        if (!empty($issueDateFrom) && !empty($issueDateTo)) {
            $q->whereBetween('created_at', [
                Carbon::parse($issueDateFrom)->startOfDay(),
                Carbon::parse($issueDateTo)->endOfDay()
            ]);
        } elseif (!empty($issueDateFrom)) {
            $q->whereDate('created_at', '>=', Carbon::parse($issueDateFrom)->startOfDay());
        } elseif (!empty($issueDateTo)) {
            $q->whereDate('created_at', '<=', Carbon::parse($issueDateTo)->endOfDay());
        }
    })
    ->where('is_deleted', 0)
    ->latest('id')->cursor();
        $book = $data->all();
        if (count($book) > 0) {
            $delimiter = ","; 
            $filename = "books.csv"; 

            // Create a file pointer 
            $f = fopen('php://memory', 'w'); 

            // Set column headers 
            // $fields = array('SR', 'QRCODE TITLE','CODE','DISTRIBUTOR','ASE','STORE NAME','STORE MOBILE','STORE EMAIL','STORE STATE','STORE ADDRESS','POINTS','DATE'); 
            $fields = array('SR', 'Office','Office Location','Bookshelf Number','Category','Title','Uid','Author','Publisher','Edition','Pages','Quantity','Book No','Created By','Status','DATE'); 
            fputcsv($f, $fields, $delimiter); 

            $count = 1;

            foreach($book as $row) {
                $datetime = date('j F, Y h:i A', strtotime($row['created_at']));
				

                $lineData = array(
                    $count,
					$row['office']['name'] ?? 'NA',
                    $row['office']['address'] ?? 'NA',
					$row->bookshelves->number ?? 'NA',
					$row->category->name ?? 'NA',
					$row->title ?? 'NA',
					$row->uid ?? 'NA',
					$row->author ?? 'NA',
					$row->publisher ?? 'NA',
					$row->edition ?? 'NA',
					$row->page ?? 'NA',
					$row->quantity ?? 'NA',
					$row->book_no ?? 'NA',
					$row->user->name ?? 'NA',
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
	
	public function csvImport(Request $request)
{
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
                
                // Read the CSV file row by row
                while (($filedata = fgetcsv($file, 10000, ",")) !== false) {
                    // Skip the header row
                    if ($i == 0) {
                        $i++;
                        continue;
                    }

                    // Store each row in $importData_arr
                    $importData_arr[] = $filedata;
                    $i++;
                }
                fclose($file);

                $successCount = 0;
                foreach ($importData_arr as $importData) {
                    //dd($importData_arr);
                    // Handling Office Data
                    $office = Office::firstOrCreate(
                        ['name' => $importData[0], 'address' => $importData[1]],
                        ['created_at' => now(), 'updated_at' => now()]
                    );

                    // Handling Bookshelve Data
                    $bookshelve = Bookshelve::firstOrCreate(
                        ['number' => $importData[2]],
                        ['office_id' => $office->id, 'user_id' => Auth::user()->id]
                    );

                    // Handling Book Category Data
                    $bookCategory = BookCategory::firstOrCreate(
                        ['name' => $importData[3]],
                        ['created_at' => now(), 'updated_at' => now()]
                    );

                    // Insert the book data based on quantity
                    $quantity = isset($importData[9]) ? $importData[9] : 1; // Default quantity 1 if not provided
                    for ($i = 0; $i < $quantity; $i++) {
                        $bookData = [
                            "office_id" => $office->id,
                            "user_id" => Auth::user()->id,
                            "category_id" => $bookCategory->id,
                            "bookshelves_id" => $bookshelve->id,
                            "title" => isset($importData[4]) ? $importData[4] : null,
                            "uid" => strtoupper(generateUniqueAlphaNumericValue(10)),
                            "author" => isset($importData[5]) ? $importData[5] : null,
                            "publisher" => isset($importData[6]) ? $importData[6] : null,
                            "edition" => isset($importData[7]) ? $importData[7] : null,
                            "year" => isset($importData[8]) ? $importData[8] : null,
                            "quantity" => 1, // Inserting single entry per loop iteration
                            "page" => isset($importData[10]) ? $importData[10] : null,
                            "book_no" => isset($importData[11]) ? $importData[11] : null,
                            "status" => 1,
                            "is_deleted" => 0,
                            "qrcode" => strtoupper(generateUniqueAlphaNumericValue(10)),
                            "created_at" => now(),
                            "updated_at" => now(),
                        ];

                        // Insert the book data
                        Book::create($bookData);
                        $successCount++;
                    }
                }

                Session::flash('message', 'CSV Import Complete. Total number of entries: ' . $successCount);
            } else {
                Session::flash('message', 'File too large. File must be less than 50MB.');
            }
        } else {
            Session::flash('message', 'Invalid File Extension. Supported extensions are ' . implode(', ', $valid_extension));
        }
    } else {
        Session::flash('message', 'No file found.');
    }

    return redirect()->back();
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


}
