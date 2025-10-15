<?php
use Illuminate\Support\Facades\Mail;
use App\Models\Team;
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
                        $query=Team::select('vp_id','rsm_id','asm_id','state_id','area_id','distributor_id')->where('ase_id',$userName)->orderby('id','desc')->with('vp','rsm','asm','states','areas','distributors')->first();
                        
                        if ($query) {
                            $namagerDetails['vp'] = $query->vp->name ?? '';
                            $namagerDetails['rsm'] = $query->rsm->name?? '';
                            $namagerDetails['asm'] = $query->asm->name?? '';
                            $namagerDetails['state'] = $query->states->name?? '';
							$namagerDetails['area'] = $query->areas->name?? '';
							$namagerDetails['distributor'] = $query->distributor->name?? '';
                        } else {
                            $namagerDetails[] = "";
                        }
                        break;
				case 7:
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
                        break;
                
            default: 
                $namagerDetails[] = "";
                break;
        }
        array_push($team_wise_attendance, $namagerDetails);
      
        return $team_wise_attendance;
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
}