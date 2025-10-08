<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Bookmark;
use App\Models\BookmarkItem;
use App\Models\Wishlist;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;


class BookmarkController extends Controller
{
    public function index(Request $request)
    {
        $wishlists = Wishlist::where('user_id', $request->user_id)->with('book','book.issueBooks')->get();
        if ($wishlists->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No bookmarks found for this user'
                
            ], 404);
        }
        return response()->json(['status'=>true,'message' => 'Bookmark list', 'data' => $wishlists ], 200);
    }

  
    public function share(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_user_id' => 'required',
            'book_id' => 'required|array',
            'to_user_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false,'error' => $validator->errors()], 400);
        }
        
        $orderData = Bookmark::select('sequence_no')->latest('sequence_no')->first();
            
            //if (empty($orderData->sequence_no)) {
                if (!empty($orderData->sequence_no)) {
                    $new_sequence_no = (int) $orderData->sequence_no + 1;
    
                } else {
                    $new_sequence_no = 1;
    
                }
            //}
            $user=User::where('id',$request->from_user_id)->first();
            $firstletter=$initials = getInitials($user->name);
           
            $uniqueNo = 'WL'.$new_sequence_no.'-'.$firstletter.'-'.Carbon::now()->format('d/m/Y').'-'.Carbon::now()->format('h:i A');
            
    		
        $wishlist = Bookmark::create([
            'sequence_no' => $new_sequence_no,
            'order_no' => $uniqueNo,
            'from_user_id' => $request->from_user_id,
            'to_user_id' => $request->to_user_id,
        ]);
        foreach($request->book_id as $cartValue) {
                    $wishlistItem = BookmarkItem::create([
                        'bookmark_id' => $wishlist->id,
                        'book_id' => $cartValue,
                    ]);
                    $wishL=Wishlist::where('user_id',$request->from_user_id)->where('book_id',$cartValue)->delete();
        }
        
        return response()->json([ 'status'=>true,'message' => 'Books have been shared successfully', 'data' => $wishlist], 201);

        if (!$wishlist) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to books added to list',
                
            ], 500); 
        }
    }
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'book_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false,'error' => $validator->errors()], 400);
        }

        $wishlist = Wishlist::create([
            'user_id' => $request->user_id,
            'book_id' => $request->book_id,
        ]);
        return response()->json([ 'status'=>true,'message' => 'Book added to wishlist', 'wishlist' => $wishlist], 201);

        if (!$wishlist) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to book added to wishlist',
                
            ], 500); 
        }
    }
    
    
    public function destroy(Request $request)
    {
        $id = $request->book_id;  
        $userId = $request->user_id;  

        if (empty($userId)) {
            return response()->json([ 'status' => false,'message' => 'Unauthorized'], 403);
        }

         $bookmark = Wishlist::where('user_id',$userId)->where('book_id',$id)->delete();

        return response()->json(['status'=>true,'message' => 'Bookmark removed successfully'], 200);
    }
}
