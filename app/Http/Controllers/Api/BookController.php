<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\Bookshelve;
use App\Models\Bookmark;
use App\Models\BookmarkItem;
use App\Models\LmsNotification;
use App\Models\IssueBook;
use App\Models\User;
use App\Models\BookTransfer;
use App\Models\ReturnRequest;
use App\Models\Wishlist;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
class BookController extends Controller
{
    public function index(Request $request)
    {
        $books = Book::with(['office','bookshelve','category'])->where('is_deleted',0)->get();
        if ($books) {
             return response()->json(['status'=>true,'message' => 'List of book','data' => $books ], 200);
        }else {
            return response()->json([
                'status' => false,
                'message' => 'Book list not found'
            ], 404);
        }
    }

   public function activeBookList(Request $request, $id)
    {
        $pageNo =$request->pageNo;
    // Fetch books with related data and where status is active (status = 1)
                if(!$pageNo){
                    $page=1;
                }else{
                    $page=$pageNo;
				 }
                    $limit=10;
                    $offset=($page-1)*$limit;
    $books = Book::where('status', 1)->where('is_deleted',0)->with(['office', 'bookshelve', 'category','issueBooks.user'])->limit($limit)->offset($offset)->get();
    $bookCount=Book::where('status', 1)->count();
    $count= (int) ceil($bookCount / $limit);

    // Check if books are found
    if ($books->count() > 0) {
        foreach ($books as $item) {
            $isWishlist = false;

            // Check if user ID is provided
            if (!empty($id)) {
                $check_user_wishlist = Wishlist::where('user_id', $id)->where('book_id', $item->id)->first();

                // If the book is in the user's wishlist, set $isWishlist to true
                if (!empty($check_user_wishlist)) {
                    $isWishlist = true;
                }
            }

            // Add the wishlist status to the book object
            $item->isWishlist = $isWishlist;
        }

        // Return the list of books with a success message
        return response()->json(['status' => true, 'message' => 'List of books', 'data' => $books,
				'count'=>$count], 200);
    } else {
        // Return a not found message if no books are available
        return response()->json([
            'status' => false,
            'message' => 'Book list not found'
        ], 404);
    }
}

    public function bookWithIssuedBook(Request $request)
    {
        $books = Book::with(['office','bookshelve','category','issuebook.user'])->where('is_deleted',0)->get();
        if ($books) {
            return response()->json(['status'=>true,'message' => 'List of book with issue details','data' => $books, ], 200);
        }else {
            return response()->json([
                'status' => false,
                'message' => 'Book list not found'
            ], 404);
        }
    }
    public function bookDetails(Request $request)
    {
  
        try {
            $book = Book::with(['office','bookshelve','category'])->findOrFail($request->id);
            if ($book) {
                $isReturn = 0;
                 //$returnRequestList=ReturnRequest::where('from_user_id',$request->user_id)->where('book_id', $book->id)->first();
                 $returnRequestList=IssueBook::where('user_id',$request->user_id)->orWhere('user_id2',$request->id)->where('book_id', $book->id)->where('is_return',1)->first();
                // dd($returnRequestList);
                 if (!empty($returnRequestList)) {
                    $isReturn = 1;
                }
            return response()->json([
                'status'=>true,
                'message' => 'Detail of book',
                'data' => $book,
                'returnRequest' => $isReturn
            ], 200);
            }else {
                return response()->json([
                    'status' => false,
                    'message' => 'Details not found'
                ], 404);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching the book details.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
   

  
    public function search(Request $request,$id)
    {
        try {
            $keyword = $request->input('keyword'); 
            $cat = $request->input('category_id'); 
             $pageNo =$request->pageNo;
                // Fetch books with related data and where status is active (status = 1)
                if(!$pageNo){
                    $page=1;
                }else{
                    $page=$pageNo;
				 }
                    $limit=10;
                    $offset=($page-1)*$limit;
            $books = Book::where('status', 1)->where('is_deleted',0);

            if ($keyword) {
                $books->where(function ($query) use ($keyword) {
                    $query->where('title', 'LIKE', "%{$keyword}%")
                        ->orWhere('publisher', 'LIKE', "%{$keyword}%")
                        ->orWhere('author', 'LIKE', "%{$keyword}%")
                        ->orWhere('year', 'LIKE', "%{$keyword}%")
                        ->orWhere('edition', 'LIKE', "%{$keyword}%")
                        ->orWhere('uid', 'LIKE', "%{$keyword}%")
                        ->orWhereHas('office', function ($query) use ($keyword) {
                            $query->where('address', 'LIKE', "%{$keyword}%");
                        });
                });

                
            }
            if ($cat) {
                $books->where(function ($query) use ($cat) {
                    $query->where('category_id', '=', $cat);
                        
                });

                
            }

            //$books = $books->with('category', 'office', 'bookshelve', 'issueBooks.user')->limit($limit)->offset($offset)->get();
            $books = $books->with('category', 'office', 'bookshelve', 'issueBooks.user','issueBooks.user2')->latest('id')->get();
            $bookCount=Book::where('status', 1);

            if ($keyword) {
                $bookCount->where(function ($query) use ($keyword) {
                    $query->where('title', 'LIKE', "%{$keyword}%")
                        ->orWhere('publisher', 'LIKE', "%{$keyword}%")
                        ->orWhere('author', 'LIKE', "%{$keyword}%")
                        ->orWhere('year', 'LIKE', "%{$keyword}%")
                        ->orWhere('edition', 'LIKE', "%{$keyword}%")
                        ->orWhere('uid', 'LIKE', "%{$keyword}%")
                        ->orWhereHas('office', function ($query) use ($keyword) {
                            $query->where('address', 'LIKE', "%{$keyword}%");
                        });
                });

                
            };
            if ($cat) {
                $bookCount->where(function ($query) use ($cat) {
                    $query->where('category_id', '=', $cat);
                        
                });

                
            };
            $bookCount=$bookCount->count();
            $count= (int) ceil($bookCount / $limit);
            if ($books->count() > 0) {
                foreach ($books as $item) {
                    $isWishlist = false;
                    $isRequestlist= false;
                    $returnRequestList= false;
                    // Check if user ID is provided
                    if (!empty($id)) {
                        $check_user_wishlist = Wishlist::where('user_id', $id)->where('book_id',$item->id)->first();
                        
                        // If the book is in the user's wishlist, set $isWishlist to true
                        if (!empty($check_user_wishlist)) {
                            $isWishlist = true;
                        }
                        $check_request_user=LmsNotification::where('message','request book')->where('sender_id', $id)->where('book_id', $item->id)->first();
                        $issueList=IssueBook::where('user_id',$id)->where('book_id', $item->id)->first();
                        $transferList=BookTransfer::where('to_user_id',$id)->where('book_id', $item->id)->first();
                        $returnRequestList=ReturnRequest::where('from_user_id',$id)->where('book_id', $item->id)->first();
                        if (!empty($check_request_user) && empty($issueList) && empty($transferList)) {
                            $isRequestlist = true;
                        }
                        
                    }
        
                    // Add the wishlist status to the book object
                    $item->isWishlist = $isWishlist;
                    $item->isRequestlist = $isRequestlist;
                    $item->returnRequest = $returnRequestList;
                }

                    // Return the list of books with a success message
                    return response()->json(['status' => true, 'message' => 'List of books', 'data' => $books,
				'count'=>$count], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'No data found'
                ], 404);
            }
        } catch (\Exception $e) {
           
            return response()->json([
                'message' => 'An error occurred during the search.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    


    public function searchDetailsByQrCode(Request $request)
    {
        try{
            $qrcode = $request->input('qrcode');

            $book = Book::where('qrcode', $qrcode)->with(['category','office','bookshelve'])
                ->first();
            if ($book) {
                return response()->json([
                    'status'=>true,
                    'message' => 'Details of book by Qr-Code',
                    'data' =>$book
                ], 200);
            }else {
                return response()->json([
                    'status' => false,
                    'message' => 'Details not found'
                ], 404);
            }
        } catch (\Exception $e) {
            DB::rollBack(); 
            Log::error('Book transfer error: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred during the book transfer.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function CategoryWiseBookList(Request $request)
    {
                $pageNo =$request->pageNo;
        // Fetch books with related data and where status is active (status = 1)
                if(!$pageNo){
                    $page=1;
                }else{
                    $page=$pageNo;
				 }
                    $limit=10;
                    $offset=($page-1)*$limit;
        $books = Book::where('category_id', $request->category_id)->where('status', 1)->where('is_deleted',0)->with(['office', 'bookshelve','issueBooks.user'])->limit($limit)->offset($offset)->get();
        $bookCount=Book::where('category_id', $request->category_id)->where('status', 1)->where('is_deleted',0)->count();
        $count= (int) ceil($bookCount / $limit);
        if ($books->count() > 0) {
                foreach ($books as $item) {
                    $isWishlist = false;
        
                    // Check if user ID is provided
                    if (!empty($request->user_id)) {
                        $check_user_wishlist = Bookmark::where('user_id', $request->user_id)->where('book_id', $item->id)->first();
        
                        // If the book is in the user's wishlist, set $isWishlist to true
                        if (!empty($check_user_wishlist)) {
                            $isWishlist = true;
                        }
                    }
        
                    // Add the wishlist status to the book object
                    $item->isWishlist = $isWishlist;
                }

                    // Return the list of books with a success message
                    return response()->json(['status' => true, 'message' => 'List of books', 'data' => $books,
				'count'=>$count], 200);
        }else {
            return response()->json([
                'status' => false,
                'message' => 'Book not found'
            ], 404);
        }
    }

    public function showBooksByBookShelveQRCode(Request $request)
    {
        $bookshelve = Bookshelve::where('qrcode', $request->qrcode)->first();

        if (!$bookshelve) {
            return response()->json(['message' => 'Bookshelf not found','status'=>false], 404);
        }

        $books = $bookshelve->books()->with(['office', 'category'])->get();
        if ($books) {
            return response()->json([
                'books' => $books->map(function ($book) {
                    return [
                        'status'=>true,
                        'message' => 'Book list by shelve QR-code wise',
                        'data'=> $book
                    ];
                })
            ],200);
        }else {
            return response()->json([
                'status' => false,
                'message' => 'Book not found'
                
            ], 404);
        }
    }

   
    public function showBooksByBookShelve(Request $request)
    {
        $bookshelve = Bookshelve::where('number', $request->number)->first();

        if (!$bookshelve) {
            return response()->json(['message' => 'Bookshelf not found','status'=>false], 404);
        }

        $books = $bookshelve->books()->get();
        if ($books) {
        return response()->json([
            'status'=>true,
            'message' => 'Book list by shelve number wise', 
            
            'books' => $books->map(function ($book) {
                return [
                    'data'=> $book,
                ];
            })
        ],200);
        }else {
            return response()->json([
                'status' => false,
                'message' => 'Book not found'
               
            ], 404);
        }

    }
    
    
    //request book list by user
    //  public function requestedbookList(Request $request,$id)
    // {
    //     $books = Bookmark::with(['item', 'item.book', 'fromuser'])
    //     ->rightJoin('issue_books', 'issue_books.bookmark_id', '=', 'bookmarks.id')
    //     ->where('bookmarks.to_user_id', $id)
    //     ->where('issue_books.status_for_requested_user', '!=', 1)
    //     ->get();
    //     if ($books) {
    //          return response()->json(['status'=>true,'message' => 'List of books','data' => $books ], 200);
    //     }else {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Book list not found'
    //         ], 404);
    //     }
    // }
    public function requestedbookList(Request $request, $id)
    {
        // Step 1: Retrieve all bookmarks for the given user
        $bookmarks = Bookmark::with(['item', 'item.book','item.book.office','item.book.bookshelve', 'item.book.issueBooks', 'fromuser','issue'])
            ->where('to_user_id', $id)
            ->get();
    
        // Step 2: Loop through each bookmark to fetch associated issue_books
         $bookmarks = $bookmarks->map(function ($bookmark) {
        $bookmark->item = $bookmark->item->map(function ($item) {
            $book = $item->book;

            if ($book) {
                // Fetch issue_books related to this book
                $issueBooks = IssueBook::where('book_id', $book->id)
                    ->where('status_for_requested_user', '=', 2)
                    ->get();

                // Embed issue_books in the book object
                $book->issue_books = $issueBooks;
            }

            return $item;
        });

        return $bookmark;
    });

    if ($bookmarks->isNotEmpty()) {
        return response()->json([
            'status' => true,
            'message' => 'List of books',
            'data' => $bookmarks
        ], 200);
    } else {
        return response()->json([
            'status' => false,
            'message' => 'Book list not found'
        ], 404);
    }
}
    
     //my requesting book list
     public function myrequestedbookList(Request $request,$id)
    {
        $books = Bookmark::with(['item','item.book','item.book.office','item.book.bookshelve','touser','issue'])->where('from_user_id',$id)->get();
        if ($books) {
             return response()->json(['status'=>true,'message' => 'List of books','data' => $books ], 200);
        }else {
            return response()->json([
                'status' => false,
                'message' => 'Book list not found'
            ], 404);
        }
    }
    
    //history of completed books
   
     public function completedbookList(Request $request,$id)
    {
        // $books = Bookmark::with(['item', 'item.book','item.book.office','item.book.bookshelve', 'fromuser'])
       
        // ->where('bookmarks.to_user_id', $id)
        
        // ->get();
        $books = Bookmark::select('bookmarks.*')->with(['item', 'item.book','item.book.office','item.book.bookshelve', 'fromuser','issue'])
        ->join('issue_books', 'issue_books.bookmark_id', '=', 'bookmarks.id')
         ->where('bookmarks.to_user_id', $id)
        ->where('issue_books.status_for_requested_user', '=', 1)
         ->get();
        
        if ($books->isNotEmpty()) {
        return response()->json([
            'status' => true,
            'message' => 'List of books',
            'data' => $books
        ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Book list not found'
            ], 404);
        }
    }
    //issue book by authorized user for bulk issue
    public function scannedlistByUser(Request $request)
    {
        $issuedBooks = IssueBook::where('user_id', $request['user_id'])->where('book_id',$request['user_id'])->where('bookmark_id',$request['bookmark_id'])
            ->whereNull('is_return')
            ->with('book') 
            ->orderBy('request_date', 'desc')
            ->first();

        if (!$issuedBooks) {
            return response()->json([
                'status'=>false,
                'message' => 'No issued books found for this user.',
            ], 404);
        }

        return response()->json([
            'status'=>true,
            'message' => 'Issued books retrieved successfully.',
            'issued_books' => $issuedBooks
        ], 200);
    }
    
    //status change for requesting book by user
    public function statuschangeforRequestedbooks(Request $request)
    {
       // dd($request->all());
        $validator = Validator::make($request->all(), [
            'bookmark_id' => 'required', 
            'user_id2'=> 'required',
            'book_id' => 'required|array',
            'status_for_requested_user'=> 'required',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['status' => false, 'error' => $validator->errors()], 400);
        }

        try {
            foreach($request->book_id as $item){
                $book = IssueBook::where('bookmark_id', $request->bookmark_id)->where('book_id',$item)->where('issue_type','bulk-issue')->first();
               
                if (!$book) {
                    return response()->json([
                        'status' => false,
                        'message' => "issue list not found."
                    ], 404);
                }
                
                $book->status_for_requested_user=$request->status_for_requested_user;
                $book->user_id2=$request->user_id2;
                $book->status_change_date= now();
                $book->save();
                
    
               
            }
             return response()->json([
                    'status' => true,
                    'message' => 'Book issued status changed successfully.',
                    'data' => $book
                ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while issuing the book.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    //return request send
     public function returnRequestSend(Request $request)
     {
         $validator = Validator::make($request->all(), [
                      
            'from_user_id' => 'required',     
            'to_user_id' => 'required',     
            'book_id' => 'required|array|exists:books,id', 
            
        ]);


        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors(),'status' => false], 400);
        }
        foreach ($request->book_id as $item) {
            $issueBook = IssueBook::where([
                'book_id' => $item, 
                'user_id2' => $request->from_user_id,
            ])->where('is_return',NULL)->first();
        
    
            
                    $returnData = new ReturnRequest();
                    $returnData->from_user_id = $request->from_user_id;
                    $returnData->to_user_id = $request->to_user_id;
                    $returnData->book_id = $item;
                    $returnData->save();
            
        }
        
        return response()->json([
                    'status'=>true,
                    'message' => 'Book return request saved successfully.',
                    'data' => $returnData,
                 
                ],200);
        
     }

      public function returnRequestList(Request $request,$id)
    {
        $books = ReturnRequest::with(['book','book.office','book.bookshelve','touser','fromuser'])->where('to_user_id',$id)->get();
        $books = $books->map(function ($bookmark) {
        
            $book = $bookmark->book_id;

            if ($book) {
                // Fetch issue_books related to this book
                $issueBooks = IssueBook::where('book_id', $book)->where('user_id',$bookmark->from_user_id)->orWhere('user_id2',$bookmark->from_user_id)
                    ->where('is_return', '=', NULL)
                    ->get();
                
                // Embed issue_books in the book object
                $bookmark->issue_books = $issueBooks;
            }

           
        

        return $bookmark;
    });

        if ($books) {
             return response()->json(['status'=>true,'message' => 'List of return requests','data' => $books ], 200);
        }else {
            return response()->json([
                'status' => false,
                'message' => 'Book list not found'
            ], 404);
        }
    }
//return bulk book
   public function returnBook(Request $request)
    {
    $validator = Validator::make($request->all(), [
        'qrcode' => 'required|string',
        'book_id' => 'required|array|exists:books,id',
        'return_request_id' => 'required|exists:return_requests,id',
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors(), 'status' => false], 400);
    }

    $bookshelf = User::where('qrcode', $request->qrcode)->first();

    if (!$bookshelf) {
        return response()->json(['message' => 'No Member found for the provided QR code.', 'status' => false], 404);
    }

    $returnData = ReturnRequest::where('id', $request->return_request_id)->first();

    if (!$returnData) {
        return response()->json(['message' => 'Invalid return request ID.', 'status' => false], 404);
    }

    $updatedBooks = [];
    foreach ($request->book_id as $item) {
       
        $issueBook = IssueBook::where([
            'book_id' => $item,
            'user_id2' => $returnData->from_user_id,
            'user_id' => $returnData->to_user_id,
            'is_return' => NULL, // Ensure it's not already returned
        ])->get();
        
       
       foreach($issueBook as $row){
        $row->update([
            'is_return' => 1,
            'return_date' => Carbon::now()->toDateString(),
        ]);
       
        $updatedBooks[] = $row;
       }
    }

    return response()->json([
        'status' => true,
        'message' => 'Book return status updated successfully.',
        'data' => $updatedBooks,
    ], 200);
}



public function returnBookForCaptain(Request $request)
{
    $validator = Validator::make($request->all(), [
        'qrcode' => 'required|string',
        'book_id' => 'required|array',
        'user_id' => 'required|array',
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors(), 'status' => false], 400);
    }

    // Ensure both arrays have the same number of elements
    if (count($request->book_id) !== count($request->user_id)) {
        return response()->json(['message' => 'Mismatch between book_id and user_id count.', 'status' => false], 400);
    }

    // Find user based on QR code
    $bookshelf = User::where('qrcode', $request->qrcode)->first();

    if (!$bookshelf) {
        return response()->json(['message' => 'No Member found for the provided QR code.', 'status' => false], 404);
    }

    $updatedBooks = [];

    // Loop through user-book pairs
    foreach ($request->book_id as $index => $bookId) {
        $userId = $request->user_id[$index]; // Get the corresponding user_id

        // Find the exact user-book pair in IssueBook
        $issueBook = IssueBook::where('book_id', $bookId)
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                      ->orWhere('user_id2', $userId);
            })
            ->whereNull('is_return')
            ->first(); // Get single matching record

        if ($issueBook) {
            $issueBook->update([
                'is_return' => 1,
                'return_date' => Carbon::now()->toDateString(),
            ]);
            $updatedBooks[] = $issueBook; // Collect updated books

            // Remove return request for this book-user pair
            ReturnRequest::where('book_id', $bookId)
                ->where('from_user_id', $userId)
                ->delete();
        }
    }

    return response()->json([
        'status' => true,
        'message' => 'Book return status updated successfully.',
        'data' => $updatedBooks,
    ], 200);
}


}
 