<?php

namespace App\Http\Controllers;

use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
class StateController extends Controller
{
     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
         $this->middleware('permission:view state', ['only' => ['index']]);
         $this->middleware('permission:create state', ['only' => ['create','store']]);
         $this->middleware('permission:update state', ['only' => ['update','edit']]);
         $this->middleware('permission:delete state', ['only' => ['destroy']]);
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
          $data = State::where('name',$request->term)->orWhere('code',$request->term)->where('is_deleted',0)->latest()->paginate(25);
        }else{
             $data = State::where('is_deleted',0)->latest()->paginate(25);
        }
        return view('state.index',compact('data','request'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(): View
    {
        
        return view('state.create');
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
            'name' => 'required|unique:states',
            'code' => 'nullable',
        ]);
    
        State::create($request->all());
    
        return redirect()->route('states.index')
                        ->with('success','State created successfully.');
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
        $data = State::find($id);
        return view('state.edit',compact('data'));
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
         request()->validate([
            'name' => 'required',
        ]);
    
        $data = State::findOrfail($id);
        $data->name=$request->name;
        $data->code=$request->code;
        $data->save();
        return redirect()->route('states.index')
                        ->with('success','State updated successfully');
    }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy($id): RedirectResponse
    {
        $data = State::findOrfail($id);
        $data->is_deleted=1;
        $data->save();
        return redirect()->route('states.index')
                        ->with('success','State deleted successfully');
    }


    public function status($id): RedirectResponse
    {
        $data = State::find($id);
        $status = ($data->status == 1) ? 0 : 1;
        $data->status = $status;
        $data->save();
    
        return redirect()->route('states.index')
                        ->with('success','State status changed successfully');
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
                        
                            'name' => isset($filedata[0]) ? $filedata[0] : null,
                            'code' => isset($filedata[1]) ? $filedata[1] : null,
                           
                            
                        ];
                            
                        // Step 4: Validate each row's data
                        $validator = Validator::make($rowData, [
                            'name' => 'required|string|max:255|unique:states',
                           
                            'code' => 'required',
                        
                        ]);

                    if ($validator->fails()) {
                        // Accumulate errors with row number context
                        $errors[$i] = $validator->errors()->all();
                    } else {
                            
                        // Step 5: Save data if validation passes
                        $insertData = [
                            
                            "name" => $rowData['name'],
                            "code" => $rowData['code'],
                           
                            "status" => 1,
                            "is_deleted" => 0,
                            "created_at" => date('Y-m-d H:i:s'),
                            "updated_at" => date('Y-m-d H:i:s'),
                        ];
                        
                        State::create($insertData);
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

    public function stateExport(Request $request)
	{
       
        if (!empty($request->term)) 
        {
          $data = State::where('name',$request->term)->orWhere('code',$request->term)->where('is_deleted',0)->latest()->get();
        }else{
             $data = State::where('is_deleted',0)->latest()->get();
        }
        if (count($data) > 0) {
            $delimiter = ","; 
            $filename = "states.csv"; 

            // Create a file pointer 
            $f = fopen('php://memory', 'w'); 

            // Set column headers 
            // $fields = array('SR', 'QRCODE TITLE','CODE','DISTRIBUTOR','ASE','STORE NAME','STORE MOBILE','STORE EMAIL','STORE STATE','STORE ADDRESS','POINTS','DATE'); 
            $fields = array('SR', 'Name','Code','Status','DATE'); 
            fputcsv($f, $fields, $delimiter); 

            $count = 1;

            foreach($data as $row) {
                $datetime = date('j F, Y h:i A', strtotime($row['created_at']));
				

                $lineData = array(
                    $count,
					$row['name'] ?? 'NA',
                    $row['code'] ?? 'NA',
					
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

}




