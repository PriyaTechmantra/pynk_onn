<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LogController extends Controller
{

     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
         $this->middleware('permission:view edit log|activity log csv export', ['only' => ['index']]);
         
    }
    public function index(Request $request): View
	{ 
	    $issueDateFrom = $request->input('date_from');
        $issueDateTo = $request->input('date_to');
	    
	    $query=DB::table('edit_logs');
	    if (!empty($issueDateFrom) && !empty($issueDateTo)) {
            $query->whereBetween('created_at', [
                Carbon::parse($issueDateFrom)->startOfDay(),
                Carbon::parse($issueDateTo)->endOfDay()
            ]);
        } 
        // Apply date filter if only 'date_from' is provided
        elseif (!empty($issueDateFrom)) {
            $query->whereDate('created_at', '>=', Carbon::parse($issueDateFrom)->startOfDay());
        } 
        // Apply date filter if only 'date_to' is provided
        elseif (!empty($issueDateTo)) {
            $query->whereDate('created_at', '<=', Carbon::parse($issueDateTo)->endOfDay());
        }
	    $data=$query->paginate(25);
	    return view('logs.index', compact('data', 'request'));
	}
	
	
	
	public function logReportExport(Request $request)
    {
            // Capture inputs
            $issueDateFrom = $request->input('date_from');
            $issueDateTo = $request->input('date_to');
            
            $query=DB::table('edit_logs');
            if (!empty($issueDateFrom) && !empty($issueDateTo)) {
                $query->whereBetween('created_at', [
                    Carbon::parse($issueDateFrom)->startOfDay(),
                    Carbon::parse($issueDateTo)->endOfDay()
                ]);
            } 
            // Apply date filter if only 'date_from' is provided
            elseif (!empty($issueDateFrom)) {
                $query->whereDate('created_at', '>=', Carbon::parse($issueDateFrom)->startOfDay());
            } 
            // Apply date filter if only 'date_to' is provided
            elseif (!empty($issueDateTo)) {
                $query->whereDate('created_at', '<=', Carbon::parse($issueDateTo)->endOfDay());
            }
            $data=$query->cursor();
            $log = $data->all();
            if (count($log) > 0) {
                $delimiter = ","; 
                $filename = "edit-logs.csv"; 

                // Create a file pointer 
                $f = fopen('php://memory', 'w'); 

                // Set column headers 
                // $fields = array('SR', 'QRCODE TITLE','CODE','DISTRIBUTOR','ASE','STORE NAME','STORE MOBILE','STORE EMAIL','STORE STATE','STORE ADDRESS','POINTS','DATE'); 
                $fields = array('SR', 'Module','Record Details','Field','Previous Value','Updated Value','Updated Date','Updated By','Action'); 
                fputcsv($f, $fields, $delimiter); 

                $count = 1;

                foreach($log as $row) {
                    if ($row->table_name && $row->record_id) {
                                            $modelClass = 'App\\Models\\' . ucfirst(Str::camel(Str::singular($row->table_name)));
                                    
                                            if (class_exists($modelClass)) {
                                                $record = $modelClass::find($row->record_id);
                                                $row->record_details = $record;
                                            
                                            } else {
                                                $row->record_details = null;
                                            }
                                        }
                                        
                                        if ($row->updated_by && $row->updated_by) {
                                            $modelClass = 'App\\Models\User';
                                    
                                            if (class_exists($modelClass)) {
                                                $record = $modelClass::find($row->updated_by);
                                                $row->user_details = $record;
                                            
                                            } else {
                                                $row->user_details = null;
                                            }
                                        }
                                        
                                    

                    $lineData = array(
                        $count,
                        ucwords(str_replace('_', ' ',$row->table_name)) ??'',
                        $row->record_details->order_no ?? $row->record_details->order_no ?? 'Details available',
                        $row->field  ?? 'NA',
                        $row->old_value ?? 'NA',
                        
                        $row->new_value ?? 'NA',
                        date('d-m-Y', strtotime($row->created_at)),
                        $row->user_details->name ?? 'NA',
                        ucwords($row->action) ?? 'NA',
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
