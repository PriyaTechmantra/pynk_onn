<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Office;
use App\Models\BookCategory;
use App\Models\Bookshelve;
use App\Models\LostBook;
use App\Models\IssueBook;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Auth;
use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\JsonResponse;
class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
         $this->middleware('permission:book list|book csv upload|book csv export|book status change|book issue list|book issue list csv download|book history', ['only' => ['index']]);
         $this->middleware('permission:create book', ['only' => ['create','store']]);
         $this->middleware('permission:update book', ['only' => ['edit','update']]);
         $this->middleware('permission:delete book', ['only' => ['destroy']]);
         $this->middleware('permission:view book', ['only' => ['show']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request): View
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
    ->latest('id')
    ->paginate(25);

    $office = Office::all();
    $bookshelve = Bookshelve::all();
    $category = BookCategory::all();

    return view('lms.book.index', compact('data', 'office', 'request', 'bookshelve', 'category'))
        ->with('i', (request()->input('page', 1) - 1) * 5);
}
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request): View
    {
        $office=Office::all();
        $bookshelve=Bookshelve::all();
        $category=BookCategory::all();
        return view('lms.book.create',compact('office','request','bookshelve','category'));
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
            'office_id' => 'required',
            'bookshelves_id' => 'required',
           
            'category_id' => 'required',
            'title' => 'required',
        ]);
        function generateUniqueAlphaNumeric($length = 10) {
            $random_string = '';
            for ($i = 0; $i < $length; $i++) {
                $number = random_int(0, 36);
                $character = base_convert($number, 10, 36);
                $random_string .= $character;
            }
            return $random_string;
        }
        for ($i = 0; $i < $request['quantity']; $i++) {
            $data=new Book();
            $data->office_id=$request['office_id'];
            $data->bookshelves_id=$request['bookshelves_id'];
            $data->category_id=$request['category_id'];
            $data->user_id=Auth::user()->id;
            $data->title=$request['title']??'';
            $data->uid=strtoupper(generateUniqueAlphaNumeric(10));
            $data->author=$request['author']??'';
            $data->publisher=$request['publisher']??'';
            $data->edition=$request['edition']??'';
            $data->page=$request['page']??'';
            $data->quantity=$request['quantity']??'';
            $data->book_no=$request['book_no']??'';
            $data->year=$request['year']??'';
            $data->qrcode=strtoupper(generateUniqueAlphaNumeric(10));
            $data->is_deleted=0;
            $data->save();
        }
        return redirect()->route('books.index')
                        ->with('success','Book created successfully.');
    }
    
    /**
     * Display the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show($id): View
    {
        $data = Book::find($id);
        return view('lms.book.view',compact('data'));
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit($id): View
    {
        $data = Book::find($id);
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
     
     
    public function bookshelveOffice($id): JsonResponse
    {
       $data = Bookshelve::where('office_id',$id)->get();
       if (count($data)==0) {
                return response()->json(['error'=>true, 'resp'=>'No data found']);
       } else {
                return response()->json(['error'=>false, 'resp'=>'Bookshelves List','data'=>$data]);
       } 
    }
    
    //book issue list
    public function bookIssueList(Request $request,$id)
    {
       $issueDateFrom = $request->input('issue_date_from');
       $issueDateTo = $request->input('issue_date_to');
       $query = IssueBook::where('book_id',$id)->with('bookmark','user','user2','book');
       if (!empty($issueDateFrom) && !empty($issueDateTo)) {
            $query->whereBetween('issue_books.approve_date', [
                Carbon::parse($issueDateFrom)->startOfDay(),
                Carbon::parse($issueDateTo)->endOfDay()
            ]);
        } elseif (!empty($issueDateFrom)) {
            $query->whereDate('issue_books.approve_date', '>=', Carbon::parse($issueDateFrom)->startOfDay());
        } elseif (!empty($issueDateTo)) {
            $query->whereDate('issue_books.approve_date', '<=', Carbon::parse($issueDateTo)->endOfDay());
        }
        // Get the paginated results
        $data = $query->paginate(25);
       $book=Book::where('id',$id)->first();
       return view('lms.book.book-issue-detail',compact('data','book','request'));
       
       
    }
    
    //book issue list export csv
     public function bookIssuecsvExport(Request $request,$id)
	{
		$bookData=Book::where('id',$id)->first();
        $issueDateFrom = $request->input('issue_date_from');
        $issueDateTo = $request->input('issue_date_to');
        $query = IssueBook::where('book_id',$id);
         // Apply date filter
        if (!empty($issueDateFrom) && !empty($issueDateTo)) {
            $query->whereBetween('issue_books.approve_date', [
                Carbon::parse($issueDateFrom)->startOfDay(),
                Carbon::parse($issueDateTo)->endOfDay()
            ]);
        } elseif (!empty($issueDateFrom)) {
            $query->whereDate('issue_books.approve_date', '>=', Carbon::parse($issueDateFrom)->startOfDay());
        } elseif (!empty($issueDateTo)) {
            $query->whereDate('issue_books.approve_date', '<=', Carbon::parse($issueDateTo)->endOfDay());
        }
        // Get the paginated results
        $data = $query->cursor();
        $book = $data->all();
        if (count($book) > 0) {
            $delimiter = ","; 
            $filename = "issue-lists of-".$bookData->title.".csv"; 

            // Create a file pointer 
            $f = fopen('php://memory', 'w'); 

            // Set column headers 
            // $fields = array('SR', 'QRCODE TITLE','CODE','DISTRIBUTOR','ASE','STORE NAME','STORE MOBILE','STORE EMAIL','STORE STATE','STORE ADDRESS','POINTS','DATE'); 
            $fields = array('SR', 'Member Name','Member Mobile','Member Email','Issue request date','Issued date','Returned date','Remarks'); 
            fputcsv($f, $fields, $delimiter); 

            $count = 1;

            foreach($book as $row) {
                $datetime = date('j F, Y', strtotime($row['approve_date']));
				 //$transfer=BookTransfer::where('book_id',$id)->where('from_user_id',$row->user_id)->with('toUser')->first();      
                if($row->return_date==NUll && !empty($transfer)){
                        $value= 'Transfer to '.$transfer->toUser->name;
                                    
                }
                $lineData = array(
                    $count,
					$row->user->name ?? 'NA',
                    $row->user->mobile  ?? 'NA',
					$row->user->email ?? 'NA',
					$row->request_date ?? 'NA',
					$row->approve_date ?? 'NA',
					$row->return_date ?? 'NA',
				    $value
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
	
	//book history list
    public function bookHistory(Request $request,$id)
    {
       $issueDateFrom = $request->input('issue_date_from');
       $issueDateTo = $request->input('issue_date_to');
       $query = IssueBook::where('book_id',$id)->with('bookmark','user','user2','book');
       if (!empty($issueDateFrom) && !empty($issueDateTo)) {
            $query->whereBetween('issue_books.approve_date', [
                Carbon::parse($issueDateFrom)->startOfDay(),
                Carbon::parse($issueDateTo)->endOfDay()
            ]);
        } elseif (!empty($issueDateFrom)) {
            $query->whereDate('issue_books.approve_date', '>=', Carbon::parse($issueDateFrom)->startOfDay());
        } elseif (!empty($issueDateTo)) {
            $query->whereDate('issue_books.approve_date', '<=', Carbon::parse($issueDateTo)->endOfDay());
        }
        // Get the paginated results
        $data = $query->paginate(25);
       $book=Book::where('id',$id)->first();
       return view('lms.book.history',compact('data','book','request'));
       
       
    }
    
	public function bookHistorycsvExport(Request $request,$id)
	{
		$bookData=Book::where('id',$id)->first();
        $issueDateFrom = $request->input('issue_date_from');
        $issueDateTo = $request->input('issue_date_to');
        $query = IssueBook::where('book_id',$id);
         // Apply date filter
        if (!empty($issueDateFrom) && !empty($issueDateTo)) {
            $query->whereBetween('issue_books.approve_date', [
                Carbon::parse($issueDateFrom)->startOfDay(),
                Carbon::parse($issueDateTo)->endOfDay()
            ]);
        } elseif (!empty($issueDateFrom)) {
            $query->whereDate('issue_books.approve_date', '>=', Carbon::parse($issueDateFrom)->startOfDay());
        } elseif (!empty($issueDateTo)) {
            $query->whereDate('issue_books.approve_date', '<=', Carbon::parse($issueDateTo)->endOfDay());
        }
        // Get the paginated results
        $data = $query->cursor();
        $book = $data->all();
        if (count($book) > 0) {
            $delimiter = ","; 
            $filename = "issue-lists of-".$bookData->title.".csv"; 

            // Create a file pointer 
            $f = fopen('php://memory', 'w'); 

            // Set column headers 
            // $fields = array('SR', 'QRCODE TITLE','CODE','DISTRIBUTOR','ASE','STORE NAME','STORE MOBILE','STORE EMAIL','STORE STATE','STORE ADDRESS','POINTS','DATE'); 
            $fields = array('SR', 'Requested Member','Issue request date','Request send to Authorized Member','Issued date by Authorized Member','Issued date by Requested Member','Returned By','Returned date','Remarks'); 
            fputcsv($f, $fields, $delimiter); 

            $count = 1;

            foreach($book as $row) {
                $datetime = date('j F, Y', strtotime($row['approve_date']));
				 $transfer=\App\Models\BookTransfer::where('book_id',$id)->where('from_user_id',$row->user_id)->with('toUser')->first();  
				 $returnRequest=\App\Models\ReturnRequest::where('book_id',$row->id)->where('from_user_id',$row->bookmark->from_user_id)->with('touser')->first(); 
                if($row->return_date==NUll && !empty($transfer)){
                        $value= 'Transfer to '.$transfer->toUser->name;
                                    
                }
                if($row->status_for_requested_user==1){
                    $statusDate=date('j M Y', strtotime($row->status_change_date));
                }else{
                    $statusDate='';
                }
                if(!empty($returnRequest->touser)){
                            $returnBy=$returnRequest->touser->name ??'';
                }else{
                    $returnBy='';
                }
                                    
                $lineData = array(
                    $count,
					$row->bookmark->fromuser->name ?? 'NA',
                    date('j M Y', strtotime($row->bookmark->created_at))  ?? 'NA',
					$row->bookmark->touser->name ?? 'NA',
					date('j M Y', strtotime($row->request_date)) ?? 'NA',
					
                    $statusDate,
                                   
                    $returnBy, 
					$row->return_date ?? 'NA',
				    $row->issue_type
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
    
    //bookshelf details with id
    public function bookshelveDetail($id): JsonResponse
    {
       $data = Bookshelve::where('id',$id)->first();
       if (!$data) {
                return response()->json(['error'=>true, 'resp'=>'No data found']);
       } else {
                return response()->json(['error'=>false, 'resp'=>'Bookshelves List','data'=>$data]);
       } 
    }
    
    
    //total available books per office
    public function availableBookListOffice(Request $request,$id): View
    {
       $data = Book::select('books.*')->with('bookshelves')->leftjoin('issue_books', 'issue_books.book_id', '=', 'books.id')->join('offices', 'offices.id', '=', 'books.office_id')
                                  ->where(function($query) {
                                        $query->whereNull('issue_books.book_id')
                                              ->orWhere('issue_books.is_return',1)->orWhere('issue_books.status',0);
                                    })
                                  
                                  ->where('books.office_id',$id)
                                   ->distinct()
                                  ->paginate(25);
                                 // dd($data);
       $office=Office::where('id',$id)->first();
       $category=BookCategory::all();
       return view('lms.book.book-office-detail',compact('data','office','category','request'));
       
       
    }
    
    
     //total issue books per office
    public function issueBookListOffice(Request $request,$id): View
    {
       $data =Book::select('books.*')->with('bookshelves')
                                    ->join('issue_books', 'issue_books.book_id', '=', 'books.id')
                                    ->join('offices', 'offices.id', '=', 'books.office_id')
                                    ->where(function($query) {
                                        $query->whereNull('issue_books.is_return')
                                             ;
                                    })
                                    
                                    ->where('books.office_id',$id)
                                    ->distinct()
                                    ->paginate(25);
                                 // dd($data);
       $office=Office::where('id',$id)->first();
       $category=BookCategory::all();
       return view('lms.book.book-issue-office-detail',compact('data','office','category','request'));
       
       
    }
    
     //total issue books 
    public function issueBookList(Request $request): View
    {
        $keyword = $request->input('keyword');
        $issueDateFrom = $request->input('issue_date_from');
        $issueDateTo = $request->input('issue_date_to');
       $query =Book::select('issue_books.*')->with('bookshelves')
                                    ->join('issue_books', 'issue_books.book_id', '=', 'books.id')
                                     ->join('users', 'issue_books.user_id', '=', 'users.id')
                                    ->where(function($query) use ($keyword) {
                                        $query->whereNull('issue_books.is_return')
                                         ->where('books.title', 'LIKE', "%{$keyword}%")
                                          ->orWhere('books.author', 'LIKE', "%{$keyword}%")
                                          ->orWhere('books.uid', 'LIKE', "%{$keyword}%")
                                          ->orWhere('books.publisher', 'LIKE', "%{$keyword}%")
                                          ->orWhere('books.edition', 'LIKE', "%{$keyword}%")
                                          ->orWhere('books.page', 'LIKE', "%{$keyword}%")
                                          ->orWhere('books.quantity', 'LIKE', "%{$keyword}%")
                                          ->orWhere('users.name', 'LIKE', "%{$keyword}%");
                                    });
                                    if (!empty($issueDateFrom) && !empty($issueDateTo)) {
                                        $query->whereBetween('issue_books.approve_date', [
                                            Carbon::parse($issueDateFrom)->startOfDay(),
                                            Carbon::parse($issueDateTo)->endOfDay()
                                        ]);
                                    } elseif (!empty($issueDateFrom)) {
                                        $query->whereDate('issue_books.approve_date', '>=', Carbon::parse($issueDateFrom)->startOfDay());
                                    } elseif (!empty($issueDateTo)) {
                                        $query->whereDate('issue_books.approve_date', '<=', Carbon::parse($issueDateTo)->endOfDay());
                                    }
                                    
                                    
                                    
                       $data=$query->distinct()
                                    ->paginate(25);
                                 // dd($data);
       
       $category=BookCategory::all();
       return view('lms.book.all-book-issue-detail',compact('data','category','request'));
       
       
    }
    
    //unreturned book list
    public function unreturnedBookList(Request $request): View
{
    $keyword = $request->input('keyword');
    $officeId = $request->input('office_id');
    $bookshelveId = $request->input('bookshelves_id');
    $categoryId = $request->input('category_id');
    $issueDateFrom = $request->input('date_from');
    $issueDateTo = $request->input('date_to');

    $data = IssueBook::select(
            'books.*',
            'users.id AS user_id',
            'users.name',
            'users.mobile',
            'issue_books.approve_date',
            'issue_books.request_date',
            'issue_books.is_return',
            'issue_books.issue_type'
        )
        ->join('books', 'issue_books.book_id', '=', 'books.id')
        ->join('users', 'issue_books.user_id', '=', 'users.id')
        ->whereNull('issue_books.is_return')
        ->where(function ($q) {
            $q->whereIn('issue_books.issue_type', ['issue', 're-issue', 'bulk-issue']);
        })
        ->where(function ($q) use ($keyword, $officeId, $bookshelveId, $categoryId, $issueDateFrom, $issueDateTo) {
            if ($keyword) {
                $q->where('books.title', 'LIKE', "%{$keyword}%")
                  ->orWhere('books.author', 'LIKE', "%{$keyword}%")
                  ->orWhere('books.publisher', 'LIKE', "%{$keyword}%")
                  ->orWhere('books.edition', 'LIKE', "%{$keyword}%")
                  ->orWhere('books.page', 'LIKE', "%{$keyword}%")
                  ->orWhere('books.quantity', 'LIKE', "%{$keyword}%")
                  ->orWhere('books.uid', 'LIKE', "%{$keyword}%");
            }
            if ($officeId) {
                $q->where('books.office_id', $officeId);
            }
            if ($bookshelveId) {
                $q->where('books.bookshelves_id', $bookshelveId);
            }
            if ($categoryId) {
                $q->where('books.category_id', $categoryId);
            }
            if (!empty($issueDateFrom) && !empty($issueDateTo)) {
                $q->whereBetween('issue_books.return_date', [
                    Carbon::parse($issueDateFrom)->startOfDay(),
                    Carbon::parse($issueDateTo)->endOfDay(),
                ]);
            } elseif (!empty($issueDateFrom)) {
                $q->whereDate('issue_books.return_date', '>=', Carbon::parse($issueDateFrom)->startOfDay());
            } elseif (!empty($issueDateTo)) {
                $q->whereDate('issue_books.return_date', '<=', Carbon::parse($issueDateTo)->endOfDay());
            }
        })
        ->whereNotNull('issue_books.book_id')
        ->paginate(25);

    $category = BookCategory::all();
    $office = Office::all();

    return view('lms.book.unreturn', compact('data', 'category', 'office', 'request'));
}

    
    
     //unreturned book list csv export
    
    public function unreturnedBookcsvExport(Request $request)
	{
		 $keyword = $request->input('keyword');
    $officeId = $request->input('office_id');
    $bookshelveId = $request->input('bookshelves_id');
    $categoryId = $request->input('category_id');
    $issueDateFrom = $request->input('date_from');
    $issueDateTo = $request->input('date_to');

    $query = IssueBook::select(
            'books.*',
            'users.id AS user_id',
            'users.name',
            'users.mobile',
            'issue_books.approve_date',
            'issue_books.request_date',
            'issue_books.is_return',
            'issue_books.issue_type'
        )
        ->join('books', 'issue_books.book_id', '=', 'books.id')
        ->join('users', 'issue_books.user_id', '=', 'users.id')
        ->whereNull('issue_books.is_return')
        ->where(function ($q) {
            $q->whereIn('issue_books.issue_type', ['issue', 're-issue', 'bulk-issue']);
        })
        ->where(function ($q) use ($keyword, $officeId, $bookshelveId, $categoryId, $issueDateFrom, $issueDateTo) {
            if ($keyword) {
                $q->where('books.title', 'LIKE', "%{$keyword}%")
                  ->orWhere('books.author', 'LIKE', "%{$keyword}%")
                  ->orWhere('books.publisher', 'LIKE', "%{$keyword}%")
                  ->orWhere('books.edition', 'LIKE', "%{$keyword}%")
                  ->orWhere('books.page', 'LIKE', "%{$keyword}%")
                  ->orWhere('books.quantity', 'LIKE', "%{$keyword}%")
                  ->orWhere('books.uid', 'LIKE', "%{$keyword}%");
            }
            if ($officeId) {
                $q->where('books.office_id', $officeId);
            }
            if ($bookshelveId) {
                $q->where('books.bookshelves_id', $bookshelveId);
            }
            if ($categoryId) {
                $q->where('books.category_id', $categoryId);
            }
            if (!empty($issueDateFrom) && !empty($issueDateTo)) {
                $q->whereBetween('issue_books.return_date', [
                    Carbon::parse($issueDateFrom)->startOfDay(),
                    Carbon::parse($issueDateTo)->endOfDay(),
                ]);
            } elseif (!empty($issueDateFrom)) {
                $q->whereDate('issue_books.return_date', '>=', Carbon::parse($issueDateFrom)->startOfDay());
            } elseif (!empty($issueDateTo)) {
                $q->whereDate('issue_books.return_date', '<=', Carbon::parse($issueDateTo)->endOfDay());
            }
        })
        ->whereNotNull('issue_books.book_id');

        // Get the paginated results
        $data = $query->get();
        //$book = $data->all();
        if (count($data) > 0) {
            $delimiter = ","; 
            $filename = "unreturned-books.csv"; 

            // Create a file pointer 
            $f = fopen('php://memory', 'w'); 

            // Set column headers 
            // $fields = array('SR', 'QRCODE TITLE','CODE','DISTRIBUTOR','ASE','STORE NAME','STORE MOBILE','STORE EMAIL','STORE STATE','STORE ADDRESS','POINTS','DATE'); 
            $fields = array('SR', 'Office','Office Location','Bookshelf Number','Category','Title','UID','Author','Member Name','Member Mobile','Issued Date','Type'); 
            fputcsv($f, $fields, $delimiter); 

            $count = 1;

            foreach($data as $row) {
                $datetime = date('j F, Y', strtotime($row['approve_date']));
				$office=Office::where('id',$row->office_id)->first();
                $bookshelve=Bookshelve::where('id',$row->bookshelves_id)->first();
                $category=BookCategory::where('id',$row->category_id)->first();
                if($row->issue_type=='issue'){
                if($row->request_date && date('d-m-Y', strtotime($row->request_date)) !== '1970-01-01'){
                 $approveDate=date('j F, Y', strtotime($row->request_date));
                }else{
                  $approveDate= 'N/A' ;
                }
                }elseif($row->issue_type=='bulk-issue'){
                if($row->request_date && date('d-m-Y', strtotime($row->request_date)) !== '1970-01-01'){
                 $approveDate=date('j F, Y', strtotime($row->request_date));
                }else{
                  $approveDate= 'N/A' ;
                }
               
                }elseif($row->issue_type=='re-issue'){
                if($row->approve_date && date('d-m-Y', strtotime($row->approve_date)) !== '1970-01-01'){
                 $approveDate=date('j F, Y', strtotime($row->approve_date));
                }else{
                 $approveDate= 'N/A' ;
                }
               
                }else{
                 $approveDate='';
                }
             
                 if($row->issue_type=='issue'){
                   $type='Self';
                 }elseif($row->issue_type=='bulk-issue'){
                   $type='Bulk Issue';
                 }elseif($row->issue_type=='re-issue'){
                   $type='Re Issue';
                 }
                $lineData = array(
                    $count,
					$office['name'] ?? 'NA',
                    $office['address'] ?? 'NA',
					$bookshelve->number ?? 'NA',
					$category->name ?? 'NA',
					$row->title ?? 'NA',
					$row->uid ?? 'NA',
					$row->author ?? 'NA',
					$row->name ?? 'NA',
					$row->mobile ?? 'NA',
					$approveDate,
					$type
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
	
	
	public function bookUpdatedCsv(Request $request)
{
    if (!empty($request->file)) {
        $file = $request->file('file');
        $filename = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $fileSize = $file->getSize();

        $valid_extension = array("csv");
        $maxFileSize = 50097152; // 50MB limit

        if (in_array(strtolower($extension), $valid_extension)) {
            if ($fileSize <= $maxFileSize) {
                // Move the file to the upload directory
                $location = 'public/uploads/csv';
                $file->move($location, $filename);
                $filepath = $location . "/" . $filename;

                // Open the CSV file
                $file = fopen($filepath, "r");
                $i = 0; // Row counter
                $successCount = 0; // Count of successful updates
                $errorCount = 0; // Count of failed updates

                while (($filedata = fgetcsv($file, 10000, ",")) !== FALSE) {
                    // Skip header row
                    if ($i == 0) {
                        $i++;
                        continue;
                    }

                    // Use array_map to handle blank values as null
                    $importData = array_map(function($value) {
                        return !empty($value) ? $value : null; // If blank, return null
                    }, $filedata);

                    // Find the user by 'uid' from the CSV
                    $book = Book::where('uid', $importData[0])->first();

                    if (!empty($book)) {
                        // Update fields with new data or leave them as null
                        $book->year = isset($importData[1]) ? $importData[1] : null;
                        $book->book_no = isset($importData[2]) ? $importData[2] : null;

                        // Save the updated book
                        if ($book->save()) {
                            $successCount++;
                        } else {
                            $errorCount++;
                        }
                    } else {
                        $errorCount++; // Count this as a failure if no book found
                    }

                    $i++; // Increment row count
                }

                fclose($file);

                // Return success message
                Session::flash('message', "CSV Import Complete. Successfully updated {$successCount} records, {$errorCount} errors.");

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


//bulk issue
//unreturned book list
     public function bulkissueBookList(Request $request): View
    {
       $keyword = $request->input('keyword');
       $officeId = $request->input('office_id');
       $bookshelveId = $request->input('bookshelves_id');
       $categoryId = $request->input('category_id');
       $issueDateFrom = $request->input('date_from');
       $issueDateTo = $request->input('date_to');
       $data = IssueBook::select('books.*','users.id AS user_id','users.name','users.mobile','issue_books.approve_date','issue_books.request_date','issue_books.is_return','issue_books.issue_type')
       ->join('books', 'issue_books.book_id', '=', 'books.id')
       ->join('users', 'issue_books.user_id', '=', 'users.id')
       ->where('issue_books.is_return',NULL)
       ->where(function ($query) {
            $query->where('issue_books.issue_type', 'bulk-issue')
              ;
            })
       ->where(function($q) use ($keyword, $officeId,$bookshelveId,$categoryId,$issueDateFrom,$issueDateTo) {
                if ($keyword) {
                    $q->where('books.title', 'LIKE', "%{$keyword}%")
                      ->orWhere('books.author', 'LIKE', "%{$keyword}%")
                      ->orWhere('books.publisher', 'LIKE', "%{$keyword}%")
                      ->orWhere('books.edition', 'LIKE', "%{$keyword}%")
                      ->orWhere('books.page', 'LIKE', "%{$keyword}%")
                      ->orWhere('books.quantity', 'LIKE', "%{$keyword}%")->orWhere('uid', 'LIKE', "%{$keyword}%")
                       ->orWhereHas('user', function ($query) use ($keyword) {
                            $query->where('name', 'LIKE', "%$keyword%");
                        });
                }
                if ($officeId) {
                    $q->where('books.office_id', $officeId);
                }
                if ($bookshelveId) {
                    $q->where('books.bookshelves_id', $bookshelveId);
                }
                if ($categoryId) {
                    $q->where('books.category_id', $categoryId);
                }
                if (!empty($issueDateFrom) && !empty($issueDateTo)) {
                $q->whereBetween('issue_books.request_date', [
                    Carbon::parse($issueDateFrom)->startOfDay(),
                    Carbon::parse($issueDateTo)->endOfDay(),
                ]);
                } elseif (!empty($issueDateFrom)) {
                    $q->whereDate('issue_books.request_date', '>=', Carbon::parse($issueDateFrom)->startOfDay());
                } elseif (!empty($issueDateTo)) {
                    $q->whereDate('issue_books.request_date', '<=', Carbon::parse($issueDateTo)->endOfDay());
                }
            })
            ->whereNotNull('issue_books.book_id')->paginate(25);
       //$data = IssueBook::with(['book.office'])->select('books.*','users.id AS user_id','users.name','users.mobile','issue_books.approve_date','issue_books.request_date','issue_books.is_return','issue_books.issue_type')->join('books', 'issue_books.book_id', '=', 'books.id')->join('users', 'issue_books.user_id', '=', 'users.id')->where('issue_books.is_return',NULL)->where('issue_books.issue_type','issue')->orWhere('issue_books.issue_type','re-issue')->orWhere('issue_books.issue_type','bulk-issue')->whereNotNull('issue_books.book_id')->paginate(25);
      // dd($data);
       $category=BookCategory::all();
       $office=Office::all();
       return view('lms.book.bulk-issue',compact('data','category','office','request'));
       
       
    }
    
    
     //bulk issue book list csv export
    
    public function bulkissueBookcsvExport(Request $request)
	{
		$keyword = $request->input('keyword');
       $officeId = $request->input('office_id');
       $bookshelveId = $request->input('bookshelves_id');
       $categoryId = $request->input('category_id');
       $issueDateFrom = $request->input('date_from');
       $issueDateTo = $request->input('date_to');
       $query = IssueBook::select('books.*','users.id AS user_id','users.name','users.mobile','issue_books.approve_date','issue_books.request_date','issue_books.is_return','issue_books.issue_type')
       ->join('books', 'issue_books.book_id', '=', 'books.id')
       ->join('users', 'issue_books.user_id', '=', 'users.id')
       ->where('issue_books.is_return',NULL)
       ->where(function ($query) {
            $query->where('issue_books.issue_type', 'bulk-issue')
              ;
            })
       ->where(function($q) use ($keyword, $officeId,$bookshelveId,$categoryId,$issueDateFrom,$issueDateTo) {
                if ($keyword) {
                    $q->where('books.title', 'LIKE', "%{$keyword}%")
                      ->orWhere('books.author', 'LIKE', "%{$keyword}%")
                      ->orWhere('books.publisher', 'LIKE', "%{$keyword}%")
                      ->orWhere('books.edition', 'LIKE', "%{$keyword}%")
                      ->orWhere('books.page', 'LIKE', "%{$keyword}%")
                      ->orWhere('books.quantity', 'LIKE', "%{$keyword}%")->orWhere('uid', 'LIKE', "%{$keyword}%")
                       ->orWhereHas('user', function ($query) use ($keyword) {
                            $query->where('name', 'LIKE', "%$keyword%");
                        });
                }
                if ($officeId) {
                    $q->where('books.office_id', $officeId);
                }
                if ($bookshelveId) {
                    $q->where('books.bookshelves_id', $bookshelveId);
                }
                if ($categoryId) {
                    $q->where('books.category_id', $categoryId);
                }
                if (!empty($issueDateFrom) && !empty($issueDateTo)) {
                $q->whereBetween('issue_books.request_date', [
                    Carbon::parse($issueDateFrom)->startOfDay(),
                    Carbon::parse($issueDateTo)->endOfDay(),
                ]);
                } elseif (!empty($issueDateFrom)) {
                    $q->whereDate('issue_books.request_date', '>=', Carbon::parse($issueDateFrom)->startOfDay());
                } elseif (!empty($issueDateTo)) {
                    $q->whereDate('issue_books.request_date', '<=', Carbon::parse($issueDateTo)->endOfDay());
                }
            })
            ->whereNotNull('issue_books.book_id');

        // Get the paginated results
        $data = $query->get();
        //$book = $data->all();
        if (count($data) > 0) {
            $delimiter = ","; 
            $filename = "bulk-issue-book.csv"; 

            // Create a file pointer 
            $f = fopen('php://memory', 'w'); 

            // Set column headers 
            // $fields = array('SR', 'QRCODE TITLE','CODE','DISTRIBUTOR','ASE','STORE NAME','STORE MOBILE','STORE EMAIL','STORE STATE','STORE ADDRESS','POINTS','DATE'); 
            $fields = array('SR', 'Office','Office Location','Bookshelf Number','Category','Title','UID','Author','Member Name','Member Mobile','Issued Date','Type'); 
            fputcsv($f, $fields, $delimiter); 

            $count = 1;

            foreach($data as $row) {
                $datetime = date('j F, Y', strtotime($row['approve_date']));
				$office=Office::where('id',$row->office_id)->first();
                $bookshelve=Bookshelve::where('id',$row->bookshelves_id)->first();
                $category=BookCategory::where('id',$row->category_id)->first();
                if($row->issue_type=='issue'){
                if($row->request_date && date('d-m-Y', strtotime($row->request_date)) !== '1970-01-01'){
                 $approveDate=date('j F, Y', strtotime($row->request_date));
                }else{
                  $approveDate= 'N/A' ;
                }
                }elseif($row->issue_type=='bulk-issue'){
                if($row->request_date && date('d-m-Y', strtotime($row->request_date)) !== '1970-01-01'){
                 $approveDate=date('j F, Y', strtotime($row->request_date));
                }else{
                  $approveDate= 'N/A' ;
                }
               
                }elseif($row->issue_type=='re-issue'){
                if($row->approve_date && date('d-m-Y', strtotime($row->approve_date)) !== '1970-01-01'){
                 $approveDate=date('j F, Y', strtotime($row->approve_date));
                }else{
                 $approveDate= 'N/A' ;
                }
               
                }else{
                 $approveDate='';
                }
             
                 if($row->issue_type=='issue'){
                   $type='Self';
                 }elseif($row->issue_type=='bulk-issue'){
                   $type='Bulk Issue';
                 }elseif($row->issue_type=='re-issue'){
                   $type='Re Issue';
                 }
                $lineData = array(
                    $count,
					$office['name'] ?? 'NA',
                    $office['address'] ?? 'NA',
					$bookshelve->number ?? 'NA',
					$category->name ?? 'NA',
					$row->title ?? 'NA',
					$row->uid ?? 'NA',
					$row->author ?? 'NA',
					$row->name ?? 'NA',
					$row->mobile ?? 'NA',
					$approveDate,
					$type
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
	
	
	 public function testBookDelete(Request $request)
     {
		 //dd($request->all());
         if (!empty($request->file)) {
             $file = $request->file('file');
             $filename = $file->getClientOriginalName();
             $extension = $file->getClientOriginalExtension();
             $tempPath = $file->getRealPath();
             $fileSize = $file->getSize();
             $mimeType = $file->getMimeType();
 
             $valid_extension = array("csv");
             $maxFileSize = 50097152;
             if (in_array(strtolower($extension), $valid_extension)) {
                 if ($fileSize <= $maxFileSize) {
                     $location = 'public/uploads/csv';
                     $file->move($location, $filename);
                     // $filepath = public_path($location . "/" . $filename);
                     $filepath = $location . "/" . $filename;
 
                     // dd($filepath);
 
                     $file = fopen($filepath, "r");
                     $importData_arr = array();
                     $i = 0;
                     while (($filedata = fgetcsv($file, 10000, ",")) !== FALSE) {
                         $num = count($filedata);
                         // Skip first row
                         if ($i == 0) {
                             $i++;
                             continue;
                         }
                         for ($c = 0; $c < $num; $c++) {
                             $importData_arr[$i][] = $filedata[$c];
                         }
                         $i++;
                     }
                     fclose($file);
                     $successCount = 0;
                        $userId='';
                     foreach ($importData_arr as $importData) {
                        $count = $total = 0;
                        $stateData = '';
                        $user=Book::where('uid',$importData[0])->where('qrcode',$importData[1])->first();
                        if(!empty($user)){
                            $userId =$user->id;
                        
						$user=Book::findOrFail($userId);
						
						$user->delete();
                        }						
                              
                    
                   
                        
                     }
                 } else {
                     Session::flash('message', 'File too large. File must be less than 50MB.');
                 }
             } else {
                 Session::flash('message', 'Invalid File Extension. supported extensions are ' . implode(', ', $valid_extension));
             }
         } else {
             Session::flash('message', 'No file found.');
         }
 
         return redirect()->back();
     }
     
     
     
     //lost book list
     
     public function lostBookList(Request $request): View
   {
    $query = $request->input('keyword');
    $officeId = $request->input('office_id');
    $bookshelveId = $request->input('bookshelves_id');
    $categoryId = $request->input('category_id');
    $issueDateFrom = $request->input('issue_date_from');
    $issueDateTo = $request->input('issue_date_to');

    $data = LostBook::where(function($q) use ($query, $officeId, $bookshelveId, $categoryId, $issueDateFrom, $issueDateTo) {
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
    ->latest('id')
    ->paginate(25);

    $office = Office::all();
    $bookshelve = Bookshelve::all();
    $category = BookCategory::all();

    return view('lms.book.lost-book', compact('data', 'office', 'request', 'bookshelve', 'category'))
        ->with('i', (request()->input('page', 1) - 1) * 5);
    }
}
