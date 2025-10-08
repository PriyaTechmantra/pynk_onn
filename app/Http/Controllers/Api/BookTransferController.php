<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BookTransfer;
use App\Models\Book;
use App\Models\IssueBook;
use App\Models\User;
use App\Models\LmcNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;


class BookTransferController extends Controller
{
    // public function transferBook(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'book_id' => 'required', 
    //         'from_user_id' => 'required',
    //         'to_user_id' => 'required', 
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['error' => $validator->errors()], 400);
    //     }

    //     DB::beginTransaction(); 

    //     try {
    //         $bookTransfer = BookTransfer::create([
    //             'book_id' => $request->book_id,
    //             'is_transfer' => 1,
    //             'from_user_id' => $request->from_user_id,
    //             'to_user_id' => $request->to_user_id,
    //             'transfer_date' => now()->toDateString(),
    //         ]);

    //         $notificationDataFrom = [
    //             'title' => 'Book Transfer Notification',
    //             'body' => 'A book has been transferred from your account.',
    //             'data' => [
    //                 'book_id' => $request->book_id,
    //                 'from_user_id' => $request->from_user_id,
    //                 'to_user_id' => $request->to_user_id,
    //             ],
    //         ];

           
    //         DB::commit(); 

    //         return response()->json([
    //             'status'=>true,
    //             'message' => 'Book transfer status updated successfully.',
    //             'data' => $bookTransfer,
                
    //         ], 201); 
    //         if (!$bookTransfer) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Failed to book transfer status updated ',
                    
    //             ], 500); 
    //         }

    //     } catch (\Exception $e) {
    //         DB::rollBack(); 
    //         Log::error('Book transfer error: ' . $e->getMessage());
    //         return response()->json([
    //             'message' => 'An error occurred during the book transfer.',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }
    public function transferBook(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'book_id' => 'required',  
            'from_user_id' => 'required',
            'to_user_id' => 'required',  
            'qrcode' => 'required',  
        ]);
    
        if ($validator->fails()) {
            return response()->json(['status' => false, 'error' => $validator->errors()], 400);
        }
    
        $book = Book::where('id', $request->book_id)
                    ->where('qrcode', $request->qrcode)
                    ->first();

        
        if (!$book) {
            return response()->json([
                'status' => false,
                'message' => 'Book not found.',
            ], 404);
        }

        
        $issuebook = IssueBook::where('user_id', $request->from_user_id)
        ->where('book_id', $request->book_id)
        ->first();

        if ( ! $issuebook) {
            return response()->json([
                'status' => false,
                'message' => 'The specified user does not currently hold this book.',
            ], 403);
        }
        $transferHistory=BookTransfer::where('book_id',$request->book_id)->where('from_user_id',$request->from_user_id)->where('to_user_id',$request->to_user_id)->first();
        if(empty($transferHistory)){
            $data = BookTransfer::create([
                'book_id' => $book->id,
                'from_user_id' => $request->from_user_id,
                'to_user_id' => $request->to_user_id,
                'is_transfer' => 1,
                'transfer_date' => now()->toDateString(),
            ]);
            
            if (!$data) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to update book transfer status.',
                ], 500);
            }
        
            
        
            return response()->json([
                'status' => true,
                'message' => 'Book transfer status updated successfully.',
                'data' => $data,
            ], 200);
        }else{
           return response()->json([
                    'status' => false,
                    'message' => 'Failed to update book transfer status.Already transfered',
                ], 500); 
        }
    }
    

    
    
}
 