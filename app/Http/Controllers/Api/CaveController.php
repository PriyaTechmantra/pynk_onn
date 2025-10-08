<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CaveForm;
use App\Models\CaveLocation;
use App\Models\CaveDoc;
use App\Models\VaultRequest;
use App\Models\VaultReturnRequest;
use App\Models\UserPermissionCategory;
use DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
class CaveController extends Controller
{
    public function index(Request $request,$id)
    {
        $data = CaveForm::join('cave_docs', 'cave_docs.cave_form_id', '=', 'cave_forms.id')
        ->where('cave_docs.user_id', $id)
        ->where('cave_docs.scan_status', 1) // Only consider records with status = 1
        ->whereNotIn('cave_docs.cave_form_id', function ($query) use ($id) {
            $query->select('cave_form_id')
                ->from('cave_docs')
                ->where('user_id', $id)
                ->where('status', 0); // Exclude records that have status = 0 for the same cave_form_id
        })
        ->with(['location', 'category','takeOut'])
        ->get();
        if ($data) {
             return response()->json(['status'=>true,'message' => 'List of items','data' => $data ], 200);
        }else {
            return response()->json([
                'status' => false,
                'message' => 'Not found'
            ], 404);
        }
    }
    
    /*public function search(Request $request)
    {
         $request->validate([
            'keyword' => 'required|string|min:1',
        ]);

        $keyword = $request->keyword;

        // Search query with joins
        $results = DB::table('cave_forms')
            ->join('cave_locations', 'cave_locations.id', '=', 'cave_forms.location_id')
            ->join('cave_categories', 'cave_categories.id', '=', 'cave_forms.category_id')
            ->where(function ($query) use ($keyword) {
                $query->where('cave_forms.client_name', 'LIKE', "%$keyword%")
                      ->orWhere('cave_forms.remarks', 'LIKE', "%$keyword%")
                      ->orWhere('cave_locations.location', 'LIKE', "%$keyword%")
                      ->orWhere('cave_categories.name', 'LIKE', "%$keyword%");
            })
            ->select('cave_forms.id','cave_forms.client_name as client_name', 'cave_forms.remarks', 'cave_locations.location', 'cave_categories.name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    
    }*/
    
    // public function search(Request $request)
    // {
    //     // Validate the request
    //     $request->validate([
    //         'keyword' => 'required|string|min:1',
    //     ]);
    
    //     $keyword = $request->keyword;
    
    //     // Eloquent query with relationships and search conditions
    //     $results = CaveForm::with(['takeOut.user', 'location', 'category'])
    //         ->where(function ($query) use ($keyword) {
    //             $query->where('client_name', 'LIKE', "%$keyword%")
    //                   ->orWhere('remarks', 'LIKE', "%$keyword%");
    //         })
    //         ->orWhereHas('location', function ($query) use ($keyword) {
    //             $query->where('location', 'LIKE', "%$keyword%");
    //         })
    //         ->orWhereHas('category', function ($query) use ($keyword) {
    //             $query->where('name', 'LIKE', "%$keyword%");
    //         })
    //         ->get();
    
    //     // Return the search results as JSON
    //     return response()->json([
    //         'success' => true,
    //         'data' => $results,
    //     ]);
    // }
    
    
public function search(Request $request)
{
    // Validate the request
    $request->validate([
        'keyword' => 'required|string|min:1',
        'user_id' => 'required|integer', // Ensure user_id is provided
    ]);

    $keyword = $request->keyword;
    $userId = $request->user_id;

    // Eloquent query with relationships and search conditions
    $results = CaveForm::with([ 'location', 'custodian'])
        ->where(function ($query) use ($keyword) {
            $query->where('client_name', 'LIKE', "%$keyword%")
                  ->orWhere('remarks', 'LIKE', "%$keyword%")->orWhere('description', 'LIKE', "%$keyword%")->orWhere('room', 'LIKE', "%$keyword%")->orWhere('unique_code', 'LIKE', "%$keyword%");
        })
        ->orWhereHas('location', function ($query) use ($keyword) {
            $query->where('location', 'LIKE', "%$keyword%");
        })
        ->orWhereHas('custodian', function ($query) use ($keyword) {
            $query->where('name', 'LIKE', "%$keyword%");
        })
        ->get();
        foreach ($results as $item) {
            $requestList = VaultRequest::where('vault_id', $item->id)->where('is_return',NULL)->with('user')
                
                ->first();
            $takeOut = CaveDoc::where('cave_form_id', $item->id)->where('is_return',NULL)->with('user','user2')
                
                ->first();
            //dd($takeOut);
           
            $item->requestList = $requestList;
             if ($takeOut) {
                // Calculate overdue days for takeOut
                $expectedReturnDate = $takeOut->expected_return_date 
                    ? \Carbon\Carbon::createFromFormat('d-m-Y', $takeOut->expected_return_date) 
                    : null;
    
                $today = \Carbon\Carbon::today();
                $takeOut->overdue_days = $expectedReturnDate && $today->gt($expectedReturnDate) 
                    ? $today->diffInDays($expectedReturnDate) 
                    : 0;
    
                // Attach the takeOut to the item
                $item->takeOut = $takeOut;
            }
        }
    // Add a status message for 'takeOut' to indicate document status
    

    // Return the search results as JSON
    return response()->json([
        'success' => true,
        'data' => $results,
    ]);
}
    
    
    public function detail(Request $request,$id)
    {
        $data=CaveForm::where('id',$id)->with('location','category','custodian')->first();
        $requestList = VaultRequest::where('vault_id', $data->id)->where('is_return',NULL)->with('user')
                
                ->first();
            $takeOut = CaveDoc::where('cave_form_id', $data->id)->where('is_return',NULL)->with('user','user2')
                
                ->first();
            
           
            $data->requestList = $requestList;
             if ($takeOut) {
                // Calculate overdue days for takeOut
                $expectedReturnDate = $takeOut->expected_return_date 
                    ? \Carbon\Carbon::createFromFormat('d-m-Y', $takeOut->expected_return_date) 
                    : null;
    
                $today = \Carbon\Carbon::today();
                $takeOut->overdue_days = $expectedReturnDate && $today->gt($expectedReturnDate) 
                    ? $today->diffInDays($expectedReturnDate) 
                    : 0;
    
                // Attach the takeOut to the item
                $data->takeOut = $takeOut;
            }
        if ($data) {
             return response()->json(['status'=>true,'message' => 'List of items','data' => $data ], 200);
        }else {
            return response()->json([
                'status' => false,
                'message' => 'Not found'
            ], 404);
        }
    }
    //take out request send 
    
    
     public function takeOutRequest(Request $request)
    {
         $validator = Validator::make($request->all(), [
            'user_id' => 'required',
           // 'cave_form_id' => 'required',
             'vault_id' => 'required', 
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'error' => $validator->errors()], 400);
        }else{
            $vault=CaveForm::where('id',$request->vault_id)->first();
            //barcode exist check
            if(!$vault){
                return response()->json(['error'=>false, 'resp'=>'vault document is invalid']);
            }else{
                $data=CaveDoc::where('user_id',$request->user_id)->orWhere('user_id2',$request->user_id)->where('cave_form_id',$vault->id)->where('status_for_requested_user',1)->where('is_return',NULL)->first();
               
                $protem=UserPermissionCategory::where('cus_user_id',$vault->custodian_id)->pluck('user_id')->toArray();
                if (!$data) {
                    
                    
                
                      $data = new VaultRequest();
                      $data->user_id = $request->user_id;
                      $data->vault_id = $vault->id;
                      $data->custodian_id = $vault->custodian_id;
                      $data->protem_id = implode(',',$protem);
                     
                      $data->save();
                     return response()->json(['status'=>true,'message' => 'data submitted successfully','data' => $data ], 200);
                }else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Already received please return first'
                    ], 404);
                }
            }
        }
    }
    
    
    public function requestedvaultList(Request $request, $id)
    {
        // Step 1: Retrieve all bookmarks for the given user
        $bookmarks = VaultRequest::with(['vault','vault.location', 'vault.takeOut', 'custodian','user'])
            ->where('custodian_id', $id)->orWhereRaw("FIND_IN_SET(?, protem_id)", [$id])
            ->get();
        
        // Step 2: Loop through each bookmark to fetch associated issue_books
         

    if ($bookmarks->isNotEmpty()) {
        return response()->json([
            'status' => true,
            'message' => 'List of vaults',
            'data' => $bookmarks
        ], 200);
    } else {
        return response()->json([
            'status' => false,
            'message' => 'vaults list not found'
        ], 404);
    }
}
    
     //my requesting book list
    public function myrequestedvaultList(Request $request,$id)
    {
        $bookmarks = VaultRequest::with(['vault','vault.location', 'vault.takeOut', 'custodian'])
            ->where('user_id', $id)->latest('id')
            ->get();
        if ($bookmarks) {
             return response()->json(['status'=>true,'message' => 'List of vaults','data' => $bookmarks ], 200);
        }else {
            return response()->json([
                'status' => false,
                'message' => 'Vault list not found'
            ], 404);
        }
    }
    
    public function store(Request $request)
    {
         $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'return_date' => 'required',
             'qrcode' => 'required|string', 
             'request_id' =>'nullable'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'error' => $validator->errors()], 400);
        }else{
            $qrcode=CaveForm::where('qrcode',$request->qrcode)->first();
            //barcode exist check
            if(!$qrcode){
                return response()->json(['error'=>false, 'resp'=>'QR code is invalid']);
            }else{
                $data=CaveDoc::where('user_id',$request->user_id)->where('cave_form_id',$qrcode->id)->where('is_return',NULL)->first();
                
                
                if (!$data) {
                    $data = new CaveDoc();
                       $data->user_id = $request->user_id;
                        $data->cave_form_id = $qrcode->id;
                        $data->request_id=$request->request_id;
                        $data->request_date = now();
                        $data->expected_return_date = $request->return_date;
                        $data->scan_status=1;
                         $data->save();
                    //$vault=VaultRequest::where('custodian_id',$request->user_id)->orWhereRaw("FIND_IN_SET(?, protem_id)", [$request->user_id])->where('vault_id',$qrcode->id)->delete();
                     return response()->json(['status'=>true,'message' => 'data submitted successfully','data' => $data ], 200);
                }else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Already added'
                    ], 404);
                }
            }
        }
    }
    
    //member accept vault
    public function statuschangeforRequestedvaults(Request $request)
    {
       // dd($request->all());
        $validator = Validator::make($request->all(), [
            'qrcode' =>'required',
            'user_id2'=> 'required',
            'vault_id' => 'required',
            'status_for_requested_user'=> 'required',
            'request_id' =>'required'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['status' => false, 'error' => $validator->errors()], 400);
        }

        try {
              $qrcode=CaveForm::where('qrcode',$request->qrcode)->first();
              //barcode exist check
            if(!$qrcode){
                return response()->json(['error'=>false, 'resp'=>'QR code is invalid']);
            }else{
                $vault = CaveDoc::where('request_id', $request->request_id)->where('cave_form_id',$request->vault_id)->where('scan_status',1)->first();
               
                if (!$vault) {
                    return response()->json([
                        'status' => false,
                        'message' => "issue list not found."
                    ], 404);
                }
                
                $vault->status_for_requested_user=$request->status_for_requested_user;
                $vault->user_id2=$request->user_id2;
                $vault->status_change_date= now();
                $vault->save();
                
    
               
            
             return response()->json([
                    'status' => true,
                    'message' => 'Vault issued status changed successfully.',
                    'data' => $vault
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while issuing the book.',
                'error' => $e->getMessage()
            ], 500);
        }
        
    }
    
    //issued list
    
    public function listByUser(Request $request,$id)
    {
        $returnRequestList = false;
        $userId = $id;
        
    
        // Subquery to get the latest record per book_id for the user
        $subQuery = CaveDoc::selectRaw('MAX(id) as id')
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                      ->orWhere('user_id2', $userId);
            })
            ->whereNull('is_return')
            ->groupBy('cave_form_id');
    
        // Main query using the subquery as a filter
        $query = CaveDoc::select('cave_docs.*')
            ->joinSub($subQuery, 'latest_issues', function ($join) {
                $join->on('cave_docs.id', '=', 'latest_issues.id');
            })
            ->join('cave_forms', 'cave_docs.cave_form_id', '=', 'cave_forms.id')
            ->where(function ($query) use ($userId) {
                $query->where('cave_docs.user_id', $userId)
                      ->orWhere('cave_docs.user_id2', $userId);
            })
            ->whereNull('cave_docs.is_return')
            ->orderBy('cave_docs.id', 'desc')
            ->with('vault','vault.location');
    
        
    
        // Execute the query to get the issued books
        $vaults = $query->get();
        //dd($vaults);
        foreach ($vaults as $item) {
            $returnRequestList = VaultReturnRequest::where('from_user_id', $userId)
                ->where('vault_id', $item->vault->id)
                ->first();
    
            $item->returnRequest = $returnRequestList;
        }
        $vaults->each(function ($vault) use ($returnRequestList) {
        

        // Calculate overdue days
        $expectedReturnDate = $vault->expected_return_date 
            ? \Carbon\Carbon::createFromFormat('d-m-Y', $vault->expected_return_date) 
            : null;

        $today = \Carbon\Carbon::today();
        $vault->overdue_days = $expectedReturnDate && $today->gt($expectedReturnDate) 
            ? $today->diffInDays($expectedReturnDate) 
            : 0;
        });
        // If no books are found for the user
        if ($vaults->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No issued vaults found for this filter.'
            ], 404);
        }
    
        // Return books if found
        return response()->json([
            'status' => true,
            'message' => 'List of vaults issued to the user.',
            'data' => $vaults
        ], 200);
    }
    
    public function received(Request $request)
    {
         $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'qrcode' => 'required|string', 
            'cave_form_id' => 'nullable',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'error' => $validator->errors()], 400);
        }else{
            $caveQr = CaveForm::where('qrcode', $request->qrcode)
            ->first();
           // dd();
            if (!$caveQr) {
                return response()->json(['message' => 'No vaults found for the provided QR code.','status' => false], 404);
            }
            
                $data=CaveDoc::where('user_id',$request->user_id)->where('cave_form_id',$caveQr->id)->where('scan_status',1)->where('is_return',NULL)->first();
                //dd($data);
                
                if ($data) {
                    
                        $data->is_return = 1;
                        $data->return_user_id =$request->user_id;
                        $data->return_date = Carbon::now()->toDateString();
                        $data->save();
                        if(!empty($data->request_id)){
                            $request=VaultRequest::where('id',$data->request_id)->first();
                            $request->is_return=1;
                            $request->save();
                        }
                     return response()->json(['status'=>true,'message' => 'data submitted successfully','data' => $data ], 200);
                }else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Already returned'
                    ], 404);
                }
            }
        
    }
    
    
    //history
    
     public function vaultHistory(Request $request,$id)
    {
        $returnRequestList = false;
        $userId = $id;
        
    
        // Subquery to get the latest record per book_id for the user
        $subQuery = CaveDoc::selectRaw('MAX(id) as id')
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                     ;
            })
            
           ;
    
        // Main query using the subquery as a filter
        $query = CaveDoc::select('cave_docs.*')
            ->joinSub($subQuery, 'latest_issues', function ($join) {
                $join->on('cave_docs.id', '=', 'latest_issues.id');
            })
            ->join('cave_forms', 'cave_docs.cave_form_id', '=', 'cave_forms.id')
            ->where(function ($query) use ($userId) {
                $query->where('cave_docs.user_id', $userId)
                     ;
            })
           
            ->orderBy('cave_docs.id', 'desc')
            ->with('vault','vault.location','user','user2');
    
        
    
        // Execute the query to get the issued books
        $vaults = $query->get();
        //dd($vaults);
        
        
        // If no books are found for the user
        if ($vaults->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No issued vaults found'
            ], 404);
        }
    
        // Return books if found
        return response()->json([
            'status' => true,
            'message' => 'List of vaults issued to the user.',
            'data' => $vaults
        ], 200);
    }
}
