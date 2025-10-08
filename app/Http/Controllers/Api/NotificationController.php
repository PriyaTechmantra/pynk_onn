<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\LmsNotification;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
   

   
   
    public function notification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'book_id' => 'required|array',
            'sender_id' => 'required|exists:users,id',
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string|max:255',
            
        ]);
       

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors(),'status' => false], 400);
        }
       
        try {
            foreach($request->book_id as $item){
                $notification = LmsNotification::create([
                    'book_id' => $item,
                    'sender_id' => $request->sender_id,
                    'receiver_id' => $request->receiver_id,
                    'message' => $request->message,
                    'type' => $request->type,
                    'bookmark_id' =>$request->bookmark_id,
                   
                ]);
        
                return response()->json([
                    'status' => true,
                    'message' => 'Notification created successfully',
                    'data' => $notification
                ],201);
            }
    
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create notification',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function notificationListByUser(Request $request)
    {
       
        $userId = $request->input('receiver_id');
        $pageNo =$request->pageNo;
        // Fetch books with related data and where status is active (status = 1)
                if(!$pageNo){
                    $page=1;
                }else{
                    $page=$pageNo;
				 }
                    $limit=10;
                    $offset=($page-1)*$limit;
    $data = LmsNotification::where('receiver_id', $userId)->with('book','sender')->limit($limit)->offset($offset)
            ->get();
    $notificationCount=LmsNotification::where('receiver_id', $userId)->count();
    $count= (int) ceil($notificationCount / $limit);
        

        if (!$data) {
            return response()->json(['status'=>false,'message' => 'Notification not found'], 404);
        }
        return response()->json([
            'status'=>true,
            'message' => 'List of book of user',
            'data' =>$data,
            'count'=>$count
        ], 200);
    }

    public function markAsRead(Request $request)
{
    $validator = Validator::make($request->all(), [
        'id' => 'required|integer|exists:lms_notifications,id',
        
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors(),'status' => false], 400);
    }

    $id = $request->id;

    $notification = LmsNotification::find($id);
    if (!$notification) {
        return response()->json(['status'=>false,'message' => 'Notification not found'], 404);
    }
    else{
        if($notification->message=='request book'){
            $notification->is_read = true;
            $notification->approval = $request->approval;
            $notification->save();
            $newNoti=new LmsNotification();
            $newNoti->sender_id=$notification->receiver_id;
            $newNoti->receiver_id=$notification->sender_id;
            $newNoti->book_id=$notification->book_id;
            $newNoti->message='acknowledgement';
            $newNoti->save();
        }else{
            $notification->is_read = true;
            $notification->save();
        }
    }
    return response()->json(['status'=>true,'message' => 'Notification marked as read','data'=>$notification], 200);
}
}



