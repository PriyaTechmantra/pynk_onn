<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IssueBook;
use App\Models\Bookshelve;
use App\Models\Bookmark;
use App\Models\BookmarkItem;
use App\Models\Book;
use App\Models\BookTransfer;
use App\Models\ReturnRequest;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use App\Models\User;
use App\Notifications\BookReturnedNotification;
use Illuminate\Support\Facades\Validator;

class IssueBookController extends Controller
{
    public function store(Request $request)
    {
        //dd($request->all());
        $validator = Validator::make($request->all(), [
            'book_id' => 'required|array',
            'user_id' => 'required', 
            // 'request_date' => 'required',
        ]);


        if ($validator->fails()) {
            return response()->json(['status' => false,'error' => $validator->errors()], 400);
        }

        $insertedData = [];

        try {
            
            foreach ($request->book_id as $bookId) {
                   $data = new IssueBook();
                
                   $data ->user_id = $request->user_id;
                   $data ->book_id = $bookId;
                   $data ->issue_type = $request->issue_type;
                   $data ->request_date = now()->toDateString();
                   $data->bookmark_id= $request->bookmark_id;
                   $data->save();
                if($data->issue_type =='bulk-issue'){
                    $bookM=BookmarkItem::where('bookmark_id',$request->bookmark_id)->first();
                    $bookM->status=1;
                    $bookM->save();
                }
                
                $insertedData[] = $data;
                $bookmark=Wishlist::where('user_id',$request->user_id)->where('book_id',$bookId)->delete();
            }
            
            
            return response()->json([
                'status' => true,
                'message' => 'Books issue request submitted successfully.',
                'data' => $insertedData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while issuing books.',
                'error' => $e->getMessage()
            ], 500);
        }

    }
    public function bulkBookIssueWithQR(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'qrcode' => 'required|array', 
            'user_id' => 'required',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['status' => false, 'error' => $validator->errors()], 400);
        }
    
        $insertedData = [];
    
        try {
            foreach ($request->qrcode as $qrcode) {
                $book = Book::where('qrcode', $qrcode)->first();
    
                if (!$book) {
                    return response()->json([
                        'status' => false,
                        'message' => "Book with QR code $qrcode not found."
                    ], 404);
                }
    
                $data = IssueBook::create([
                    'user_id' => $request->user_id,
                    'book_id' => $book->id,
                    'request_date' => now()->toDateString(),
                ]);
    
                $insertedData[] = $data;
            }
    
            return response()->json([
                'status' => true,
                'message' => 'Books issued successfully.',
                'data' => $insertedData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while issuing books.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    

   

    public function singleBookIssueWithQR(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'qrcode' => 'required|string', 
            'user_id' => 'required',
            'book_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'error' => $validator->errors()], 400);
        }

        try {
            $book = Book::where('qrcode', $request->qrcode)->first();

            if (!$book) {
                return response()->json([
                    'status' => false,
                    'message' => "Book with QR code {$request->qrcode} not found."
                ], 404);
            }

            if ($book->id != $request->book_id) {
                return response()->json([
                    'status' => false,
                    'message' => "The QR code does not match the provided book ID."
                ], 400);
            }

                $data = new IssueBook();
                $data->user_id = $request->user_id;
                $data->book_id = $book->id;
                $data->request_date = now()->toDateString();
                $data->issue_type = $request->issue_type;
                $data->bookmark_id = $request->bookmark_id;
                $data->save();
            if($data->issue_type =='bulk-issue'){
                    $bookM=BookmarkItem::where('bookmark_id',$request->bookmark_id)->first();
                    $bookM->status=1;
                    $bookM->save();
            }
            return response()->json([
                'status' => true,
                'message' => 'Book issued successfully.',
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while issuing the book.',
                'error' => $e->getMessage()
            ], 500);
        }
    }




    public function issueBookForAnotherUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'qrcode' => 'required|string',  
            'user_id' => 'required|exists:users,id',  
           // 'book_holder_user_id' => 'required|exists:users,id', 
        ]);
    
        if ($validator->fails()) {
            return response()->json(['status' => false, 'error' => $validator->errors()], 400);
        }
    
        try {
            $book = Book::where('qrcode', $request->qrcode)->first();
    
            if (!$book) {
                return response()->json([
                    'status' => false,
                    'message' => "Book with QR code {$request->qrcode} not found."
                ], 404);
            }
    
            $bookHolder = User::find($request->user_id2);
    
            $data = IssueBook::create([
                'user_id' => $request->user_id,  
                'book_id' => $book->id,
                'request_date' => now()->toDateString(),
                'issue_type' => $request->issue_type,
                'user_id2' => $bookHolder->id,  
                'name_of_issue_person' => $request->name_of_issue_person,  
            ]);
    
            return response()->json([
                'status' => true,
                'message' => 'Book issued successfully to the user.',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while issuing the book.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    


  public function listByUser(Request $request)
{
    $returnRequestList = false;
    $userId = $request->input('user_id');
    $categoryId = $request->input('category_id');

    // Subquery to get the latest record per book_id for the user
    $subQuery = IssueBook::selectRaw('MAX(id) as id')
        ->where(function ($query) use ($userId) {
            $query->where('user_id', $userId)
                  ->orWhere('user_id2', $userId);
        })
        ->whereNull('is_return')
        ->groupBy('book_id');

    // Main query using the subquery as a filter
    $query = IssueBook::select('issue_books.*')
        ->joinSub($subQuery, 'latest_issues', function ($join) {
            $join->on('issue_books.id', '=', 'latest_issues.id');
        })
        ->join('books', 'issue_books.book_id', '=', 'books.id')
        ->where(function ($query) use ($userId) {
            $query->where('issue_books.user_id', $userId)
                  ->orWhere('issue_books.user_id2', $userId);
        })
        ->whereNull('issue_books.is_return')
        ->orderBy('issue_books.id', 'desc')
        ->with('book','book.office','book.bookshelves');

    // Apply category filter if provided
    if (!is_null($categoryId)) {
        $query->where('books.category_id', $categoryId);
    }

    // Execute the query to get the issued books
    $books = $query->get();

    foreach ($books as $item) {
        $returnRequestList = ReturnRequest::where('from_user_id', $userId)
            ->where('book_id', $item->book->id)
            ->first();

        $item->returnRequest = $returnRequestList;
    }

    // If no books are found for the user
    if ($books->isEmpty()) {
        return response()->json([
            'status' => false,
            'message' => 'No issued books found for this filter.'
        ], 404);
    }

    // Return books if found
    return response()->json([
        'status' => true,
        'message' => 'List of books issued to the user.',
        'data' => $books
    ], 200);
}

    public function issuedBookListByUser(Request $request)
    {
      
        $issuedBooks = IssueBook::where('user_id', $request['user_id'])
            
            ->whereNull('is_return')
            ->with('book') 
            ->orderBy('approve_date', 'desc')
            ->get();
            
        if ($issuedBooks->isEmpty()) {
            return response()->json([
                'status'=>false,
                'message' => 'No issued books found for this user.'
            ], 404);
        }

        return response()->json([
            'status'=>true,
            'message' => 'Issued books list.',
            'issued_books' => $issuedBooks
        ], 200);
    }

    public function requestedBookListByUser(Request $request)
    {
        $issuedBooks = IssueBook::where('user_id', $request['user_id'])
            ->whereNull('status')
            ->with('book') 
            ->orderBy('request_date', 'desc')
            ->get();

        if ($issuedBooks->isEmpty()) {
            return response()->json([
                'message' => 'No issued books found for this user.',
            ], 404);
        }

        return response()->json([
            'status'=>true,
            'message' => 'Issued books retrieved successfully.',
            'issued_books' => $issuedBooks
        ], 200);
    }

    public function returnBook(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'qrcode' => 'required|string',          
            'user_id' => 'required',          
            'book_id' => 'required|integer|exists:books,id', 
            
        ]);


        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors(),'status' => false], 400);
        }
        $bookshelf = User::where('qrcode', $request->qrcode)
        ->first();

        if (!$bookshelf) {
            return response()->json(['message' => 'No user found for the provided QR code.','status' => false], 404);
        }

        //$book = Book::where('bookshelves_id', $bookshelf->id)
                    //->where('id', $request->book_id) 
                    //->first();

       // if (!$book) {
           // return response()->json(['message' => 'No book found on the provided bookshelf.','status' => false], 404);
       // }

        $issueBook = IssueBook::where([
            'book_id' => $book->id, 
            'user_id' => $request->user_id,
        ])->first();

        if (!$issueBook) {
            return response()->json(['message' => 'No active issue record found for this book or the book has already been returned.','status' => false], 404);
        }

        $issueBook->update([
            'is_return' => 1,                 
            'return_date' => Carbon::now()->toDateString(), 
        ]);

      
        return response()->json([
            'status'=>true,
            'message' => 'Book return status updated successfully.',
            'data' => $issueBook,
            'shelve_data'=>$bookshelf,
             
        ],200);
    }


    public function transferBook(Request $request)
    {
       
        $validator = Validator::make($request->all(), [
            'book_id' => 'required',
            'from_user_id' => 'required', 
            'to_user_id' => 'required',   
        ]);


        if ($validator->fails()) {
            return response()->json(['status' => false,'error' => $validator->errors()], 400);
        }
   
        $data = BookTransfer::create([
            'is_transfer' => 1,
            'from_user_id' => $request->from_user_id,
            'to_user_id' => $request->to_user_id,
            'transfer_date' => now()->toDateString(),
        ]);
        
        return response()->json([
            'status'=>true,
            'message' => 'Book transfer status updated successfully.',
            'data' => $issueBook
            
        ],200);
        if (!$data) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to book transfer status updated '
                
            ], 500); 
        }
    }
    
    public function transferBookByQr(Request $request)
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

        
        $issuebook = IssueBook::where('id', $request->book_id)
        ->where('user_id', $request->from_user_id)
        ->first();

        if ( ! $issuebook) {
            return response()->json([
                'status' => false,
                'message' => 'The specified user does not currently hold this book.',
            ], 403);
        }
    
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
    }
    
    
    //book history user wise
    public function history(Request $request,$id)
    {
      
        $issuedBooks = IssueBook::where('user_id', $id)->orWhere('user_id2',$id)
            ->with('book','book.category','book.office') 
            ->orderBy('request_date', 'desc')
            ->get();
            
        if ($issuedBooks->isEmpty()) {
            return response()->json([
                'status'=>false,
                'message' => 'No issued books found for this user.'
            ], 404);
        }

        return response()->json([
            'status'=>true,
            'message' => 'book history fetch successfully.',
            'issued_books' => $issuedBooks
        ], 200);
    }
    
    
}
