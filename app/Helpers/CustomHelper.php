<?php
use Illuminate\Support\Facades\Mail;
use App\Models\Team;
use App\Models\UserAttendance;
use App\Models\Employee;
use App\Models\Order;
use App\Models\Notification;
if (!function_exists('generateUniqueAlphaNumericValue')) {
    function generateUniqueAlphaNumericValue($length = 10) {
        $random_string = '';
        for ($i = 0; $i < $length; $i++) {
            $number = random_int(0, 36);
            $character = base_convert($number, 10, 36);
            $random_string .= $character;
        }
        return strtoupper($random_string);
    }
}

if (!function_exists('getInitials')) {
     function getInitials($fullName) {
            return collect(explode(' ', $fullName))
                ->map(fn($name) => Str::upper(Str::substr($name, 0, 1)))
                ->join('');
        }
}



function SendMail($data)
{
	if(isset($data['from']) || !empty($data['from'])) {
		$mail_from = $data['from'];
	} else {
		$mail_from = 'admin@foxandmandal.co.in';
	}
	// $mail_from = $data['from'] ? $data['from'] : 'support@onninternational.com';



    // send mail
    Mail::send($data['blade_file'], $data, function ($message) use ($data) {
		if(isset($data['from']) || !empty($data['from'])) {
			$mail_from = $data['from'];
		} else {
			$mail_from = 'admin@foxandmandal.co.in';
		}

		// $mail_from = $data['from'] ? $data['from'] : 'support@onninternational.com';
        $message->to($data['email'], $data['name'])->subject($data['subject'])->from($mail_from, env('APP_NAME'));
    });
}
if (!function_exists('findManagerDetails')) {
    function findManagerDetails($userName, $userType ) {
        $namagerDetails = array();
        $team_wise_attendance =array();
        switch ($userType) {
            case 1:
                $namagerDetails[] = "";
                break;
            case 2:
                $query=Team::select('vp_id','state_id','area_id')->where('rsm_id',$userName)->groupby('rsm_id')->with('vp','states','areas')->first();
               
                if ($query) {
                    $namagerDetails['vp'] = $query->vp->name?? '';
                    $namagerDetails['state'] = $query->states->name?? '';
					$namagerDetails['area'] = $query->areas->name?? '';
                    $namagerDetails['rsm'] = "";
                    $namagerDetails['asm'] = "";
                } else {
                    $namagerDetails[] = "";
                }
                break;
            case 3:
                $query=Team::select('vp_id','rsm_id','state_id','area_id')->where('asm_id',$userName)->groupby('asm_id')->with('vp','rsm','states','areas')->first();
                
                if ($query) {
                    $namagerDetails['vp'] = $query->vp->name?? '';
                    $namagerDetails['rsm'] = $query->rsm->name?? '';
                    $namagerDetails['state'] = $query->states->name?? '';
					$namagerDetails['area'] = $query->areas->name?? '';
                    
                    $namagerDetails['asm'] = "";
                } else {
                    $namagerDetails[] = "";
                }
                break;
            
                case 4:
                        $query=Team::select('vp_id','rsm_id','asm_id','state_id','area_id','distributor_id')->where('ase_id',$userName)->orderby('id','desc')->with('vp','rsm','asm','states','areas','distributor')->first();
                        
                        if ($query) {
                            $namagerDetails['vp'] = $query->vp->name ?? '';
                            $namagerDetails['rsm'] = $query->rsm->name?? '';
                            $namagerDetails['asm'] = $query->asm->name?? '';
                            $namagerDetails['state'] = $query->states->name?? '';
					        $namagerDetails['area'] = $query->areas->name?? '';
                            
                        } else {
                            $namagerDetails[] = "";
                        }
                        break;
				case 5:
                        $query=Team::select('vp_id','rsm_id','asm_id','ase_id','state_id','area_id')->where('distributor_id',$userName)->orderby('id','desc')->with('vp','rsm','asm','ase','states','areas')->first();
                        
                        if ($query) {
                            $namagerDetails['VP'] = $query->vp->name ?? '';
                            $namagerDetails['RSM'] = $query->rsm->name?? '';
                            $namagerDetails['ASM'] = $query->asm->name?? '';
							$namagerDetails['ASE'] = $query->ase->name?? '';
							$namagerDetails['STATE'] = $query->states->name?? '';
							$namagerDetails['AREA'] = $query->areas->name?? '';
                        } else {
                            $namagerDetails[] = "";
                        }
                        break;
                
            default: 
                $namagerDetails[] = "";
                break;
        }
        //array_push($team_wise_attendance, $namagerDetails);
        
        //return $team_wise_attendance;
          // Convert array to HTML string with line breaks
        $output = '';
        foreach ($namagerDetails as $role => $name) {
            if(!empty($name)){
                $output .= '<span style="color: #343a40; font-weight:600;">' .$role . ' : </span> ' . $name . '<br>';
            }
        }

        return $output;
    }
}



if (!function_exists('userTypeName')) {
    function userTypeName($userType ) {
        switch ($userType) {
            case 1: $userTypeDetail = "VP";break;
            case 2: $userTypeDetail = "RSM";break;
            case 3: $userTypeDetail = "ASM";break;
            case 4: $userTypeDetail = "ASE";break;
            default: $userTypeDetail = "";break;
        }
        return $userTypeDetail;
    }
}

if(!function_exists('sendNotification')) {
    function sendNotification($sender, $receiver, $type, $route, $title, $body='')
    {
        $noti = new Notification();
        $noti->sender_id = $sender;
        $noti->receiver_id = $receiver;
        $noti->type = $type;
        $noti->route = $route;
        $noti->title = $title;
        $noti->body = $body;
        $noti->read_flag = 0;
        $noti->save();
    }


    function vpStates($id) {
        return \App\Models\Team::select('state_id')->where('vp_id', 'LIKE', '%'.$id.'%')->groupBy('state_id')->orderBy('state_id')->get();
    }

    function vpAreaCount($id) {
        return \App\Models\Team::select('area_id')->where('vp_id', 'LIKE', '%'.$id.'%')->groupBy('area_id')->get();
    }

    function vpRSMCount($id) {
        return \App\Models\Team::select('rsm_id')->where('vp_id', 'LIKE', '%'.$id.'%')->where('rsm_id', '!=', null)->groupBy('rsm_id')->get();
    }

    function vpASMCount($id) {
        return \App\Models\Team::select('asm_id')->where('vp_id', 'LIKE', '%'.$id.'%')->where('asm_id', '!=', null)->groupBy('asm_id')->get();
    }

    function vpASECount($id) {
        return \App\Models\Team::select('ase_id')->where('vp_id', 'LIKE', '%'.$id.'%')->where('ase_id', '!=', null)->groupBy('ase_id')->get();
    }


    function rsmVp($id) {
        if ($id == null) {
            return \App\Models\Team::select('vp_id')->whereRaw('rsm_id IS null')->groupBy('vp_id')->get();
        } else {
            return \App\Models\Team::select('vp_id')->where('rsm_id', 'LIKE', '%'.$id.'%')->groupBy('vp_id')->get();
        }
    }

    function rsmStates($id) {
        if ($id == null) {
            return \App\Models\Team::select('state_id')->whereRaw('rsm_id IS null')->groupBy('state_id')->orderBy('state_id')->get();
        } else {
            return \App\Models\Team::select('state_id')->where('rsm_id', 'LIKE', '%'.$id.'%')->groupBy('state_id')->orderBy('state_id')->get();
        }
    }

    function rsmAreaCount($id) {
        if ($id == null) {
            return \App\Models\Team::select('area_id')->whereRaw('rsm_id IS null')->groupBy('area_id')->get();
        } else {
            return \App\Models\Team::select('area_id')->where('rsm_id', 'LIKE', '%'.$id.'%')->groupBy('area_id')->get();
        }
    }

    function rsmASMCount($id) {
        if ($id == null) {
        return \App\Models\Team::select('asm_id')->whereRaw('rsm_id IS null')->groupBy('asm_id')->get();
        } else {
        return \App\Models\Team::select('asm_id')->where('rsm_id', 'LIKE', '%'.$id.'%')->groupBy('asm_id')->get();
        }
    }

    function rsmASECount($id) {
        if ($id == null) {
            return \App\Models\Team::select('ase_id')->whereRaw('rsm_id IS null')->groupBy('ase_id')->get();
        } else {
            return \App\Models\Team::select('ase_id')->where('rsm_id', 'LIKE', '%'.$id.'%')->groupBy('ase_id')->get();
        }
    }


    function asmVp($id) {
        if ($id == null) {
            return \App\Models\Team::select('vp_id')->whereRaw('asm_id IS null')->groupBy('vp_id')->get();
        } else {
            return \App\Models\Team::select('vp_id')->where('asm_id', 'LIKE', '%'.$id.'%')->groupBy('vp_id')->get();
        }
    }

    function asmStates($id) {
        if ($id == null) {
            return \App\Models\Team::select('state_id')->whereRaw('asm_id IS null')->groupBy('state_id')->orderBy('state_id')->get();
        } else {
            return \App\Models\Team::select('state_id')->where('asm_id', 'LIKE', '%'.$id.'%')->groupBy('state_id')->orderBy('state_id')->get();
        }
    }

    function asmAreaCount($id) {
        if ($id == null) {
            return \App\Models\Team::select('area_id')->whereRaw('asm_id IS null')->groupBy('area_id')->get();
        } else {
            return \App\Models\Team::select('area_id')->where('asm_id', 'LIKE', '%'.$id.'%')->groupBy('area_id')->get();
        }
    }

    function asmRSMCount($id) {
        if ($id == null) {
        return \App\Models\Team::select('asm_id')->whereRaw('asm_id IS null')->groupBy('asm_id')->get();
        } else {
        return \App\Models\Team::select('asm_id')->where('asm_id', 'LIKE', '%'.$id.'%')->groupBy('asm_id')->get();
        }
    }

    function asmASECount($id) {
        if ($id == null) {
            return \App\Models\Team::select('ase_id')->whereRaw('asm_id IS null')->groupBy('ase_id')->get();
        } else {
            return \App\Models\Team::select('ase_id')->where('asm_id', 'LIKE', '%'.$id.'%')->groupBy('ase_id')->get();
        }
    }


    if (!function_exists('findTeamDetails')) {
    function findTeamDetails($userName, $userType ) {
        $namagerDetails = array();
        $team_wise_attendance =array();
        switch ($userType) {
            case 1:
                $namagerDetails[] = "";
                break;
            case 2:
                $query=Team::select('vp_id','state_id','area_id')->where('rsm_id',$userName)->groupby('rsm_id')->with('vp','states','areas')->first();
               
                if ($query) {
                    $namagerDetails['vp'] = $query->vp->name?? '';
                    $namagerDetails['state'] = $query->states->name?? '';
					$namagerDetails['area'] = $query->areas->name?? '';
                    $namagerDetails['asm'] = "";
                } else {
                    $namagerDetails[] = "";
                }
                break;
            
                case 3:
                    $query=Team::select('vp_id','rsm_id','state_id','area_id')->where('asm_id',$userName)->orderby('id','desc')->with('vp','rsm','states','areas')->first();
                    
                    if ($query) {
                        $namagerDetails['vp'] = $query->vp->name?? '';
                        $namagerDetails['rsm'] = $query->rsm->name?? '';
                        $namagerDetails['state'] = $query->states->name?? '';
						$namagerDetails['area'] = $query->areas->name?? '';
                        $namagerDetails['asm'] = "";
                    } else {
                        $namagerDetails[]= "";
                    }
                    break;
                case 4:
                        $query=Team::select('vp_id','rsm_id','asm_id','state_id','area_id')->where('ase_id',$userName)->orderby('id','desc')->with('vp','rsm','asm','states','areas')->first();
                        
                        if ($query) {
                            $namagerDetails['vp'] = $query->vp->name ?? '';
                            $namagerDetails['rsm'] = $query->rsm->name?? '';
                            $namagerDetails['asm'] = $query->asm->name?? '';
                            $namagerDetails['state'] = $query->states->name?? '';
							$namagerDetails['area'] = $query->areas->name?? '';
                        } else {
                            $namagerDetails[] = "";
                        }
                        break;
				
                
            default: 
                $namagerDetails[] = "";
                break;
        }
        array_push($team_wise_attendance, $namagerDetails);
      
        return $team_wise_attendance;
    }
}

}


    if (!function_exists('findDistributorTeamDetails')) {
    function findDistributorTeamDetails($userName) {
        $namagerDetails = array();
        $team_wise_attendance =array();
        
                $query=Team::select('vp_id','rsm_id','asm_id','ase_id','state_id','area_id')->where('distributor_id',$userName)->orderby('id','desc')->with('vp','rsm','asm','ase','states','areas')->first();
                        
                        if ($query) {
                            $namagerDetails['vp'] = $query->vp->name ?? '';
                            $namagerDetails['rsm'] = $query->rsm->name?? '';
                            $namagerDetails['asm'] = $query->asm->name?? '';
							$namagerDetails['ase'] = $query->ase->name?? '';
							$namagerDetails['state'] = $query->states->name?? '';
							$namagerDetails['area'] = $query->areas->name?? '';
                        } else {
                            $namagerDetails[] = "";
                        }
                
            
                
				
                
            
        
        array_push($team_wise_attendance, $namagerDetails);
      
        return $team_wise_attendance;
    }
}

function dates_month($month, $year) {
    $num = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $month_names = array();
    $date_values = array();

    for ($i = 1; $i <= $num; $i++) {
        $mktime = mktime(0, 0, 0, $month, $i, $year);
        $date = date("d (D)", $mktime);
        $month_names[$i] = $date;
        $date_values[$i] = date("Y-m-d", $mktime);
    }
    
    return ['month_names'=>$month_names,'date_values'=>$date_values];
}

function dates_attendance($id, $date) {
    $day = date('D', strtotime($date));
    
    $date_wise_attendance = array();
    $d=array();
    $users = array();
    $user = Employee::where('id', $id)->first();

    
            $res= UserAttendance::where('user_id',$id)->whereDate('entry_date', $date)->groupby('entry_date')->orderby('id','asc')->first();
            if(!empty($res)){
                if ($res->type=='P') {
                    $d['is_present'] = 'P';
                }
                else if($day=='Sun' && empty($res))
                {
                    $d['is_present'] = 'W';
                }else if($date > date('Y-m-d')){
                    $d['is_present'] = '-';
                }else if(!empty($res) && $res->type=='leave') {
                    
                        $d['is_present'] = 'L';
                    
                }
                else{
                    $d['is_present'] = 'A';
                }
            }else{
                $d['is_present'] = 'A';
            }

            array_push($date_wise_attendance, $d);
        
    

    $data['date_wise_attendance'] = $date_wise_attendance;

    array_push($users, $data);
    
    return [$users];
}


if (!function_exists('slugGenerate')) {
    function slugGenerate($title, $table) {
        $slug = Str::slug($title, '-');
        $slugExistCount = DB::table($table)->where('name', $title)->count();
        if ($slugExistCount > 0) $slug = $slug . '-' . ($slugExistCount + 1);
        return $slug;
    }
}



if (!function_exists('generateONNOrderNumber')) {
    function generateONNOrderNumber(string $type, int $id) {
        if ($type == "secondary") {
            $shortOrderCode = "SC";
            $orderData = Order::select('sequence_no')->latest('id')->first();
             
            if (!empty($orderData)) {
                if (!empty($orderData->sequence_no)) {
                    $new_sequence_no = (int) $orderData->sequence_no + 1;
                } else {
                    $new_sequence_no = 1;
                }

                $ordNo = sprintf("%'.07d", $new_sequence_no);

                $store_id = $id;
                $storeData = Store::where('id', $store_id)->with('states:id,name','areas:id,name')->first();
               
                if (!empty($storeData)) {
                    $state = $storeData->states->name;
                    
                    if ($state != "UP CENTRAL" || $state != "UP East" || $state != "UP WEST") {
                        $stateCodeData = State::where('name', $state)->first();
                        $stateCode = $stateCodeData->code;
                    } else {
                        if ($state == "UP CENTRAL") $stateCode = "UPC";
                        elseif ($state == "UP East") $stateCode = "UPE";
                        elseif ($state == "UP WEST") $stateCode = "UPW";
                    }

                    $order_no = "ONN-".date('Y').'-'.$shortOrderCode.'-'.$stateCode.'-'.$ordNo;
                   
                    return [$order_no, $new_sequence_no];
                } else {
                    return false;
                }
            }
        } else {
            $shortOrderCode = "PR";
            
        }
    }
}


if (!function_exists('generatePYNKOrderNumber')) {
    function generatePYNKOrderNumber(string $type, int $id) {
        if ($type == "secondary") {
            $shortOrderCode = "SC";
            $orderData = Order::select('sequence_no')->latest('id')->first();
             
            if (!empty($orderData)) {
                if (!empty($orderData->sequence_no)) {
                    $new_sequence_no = (int) $orderData->sequence_no + 1;
                } else {
                    $new_sequence_no = 1;
                }

                $ordNo = sprintf("%'.07d", $new_sequence_no);

                $store_id = $id;
                $storeData = Store::where('id', $store_id)->with('states:id,name','areas:id,name')->first();
               
                if (!empty($storeData)) {
                    $state = $storeData->states->name;
                    
                    if ($state != "UP CENTRAL" || $state != "UP East" || $state != "UP WEST") {
                        $stateCodeData = State::where('name', $state)->first();
                        $stateCode = $stateCodeData->code;
                    } else {
                        if ($state == "UP CENTRAL") $stateCode = "UPC";
                        elseif ($state == "UP East") $stateCode = "UPE";
                        elseif ($state == "UP WEST") $stateCode = "UPW";
                    }

                    $order_no = "PYNK-".date('Y').'-'.$shortOrderCode.'-'.$stateCode.'-'.$ordNo;
                   
                    return [$order_no, $new_sequence_no];
                } else {
                    return false;
                }
            }
        } else {
            $shortOrderCode = "PR";
            
        }
    }
}

