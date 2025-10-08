<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\IssueBookController;
use App\Http\Controllers\Api\BookShelveController;
use App\Http\Controllers\Api\BookmarkController;
use App\Http\Controllers\Api\BookTransferController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\Fms\CabBookingController;
use App\Http\Controllers\Api\Fms\FlightBookingController;
use App\Http\Controllers\Api\Fms\TrainBookingController;
use App\Http\Controllers\Api\Fms\HotelBookingController;
use App\Http\Controllers\Api\Fms\BookingHistoryController;
use App\Http\Controllers\Api\ASEController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CaveController;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


//ase
Route::post('login', [AuthController::class, 'sendOtp']);
Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
Route::get('area/list', [ASEController::class, 'areaList']);
Route::post('activity/store', [ASEController::class, 'activityStore']);
Route::post('day/start', [ASEController::class, 'dayStart']);
Route::post('day/end', [ASEController::class, 'dayEnd']);
Route::get('check/visit/{id}', [ASEController::class, 'checkVisit']);



Route::post('day/start/activity/create', [ASEController::class, 'daystartactivityStore']);
Route::post('day/end/activity/create', [ASEController::class, 'dayendactivityStore']);
Route::get('stores/list', [ASEController::class, 'storeList']);
Route::get('inactive/stores/list', [ASEController::class, 'inactivestoreList']);



Route::get('/books/search/{id}', [BookController::class, 'search']);
Route::get('/books/list', [BookController::class, 'index']);

Route::get('/active-books/list/{id}', [BookController::class, 'activeBookList']);
Route::get('/books/list/with-issuedbook', [BookController::class, 'bookWithIssuedBook']);
Route::get('/books/detail', [BookController::class, 'bookDetails']);
Route::get('/books/details-by-qrcode', [BookController::class, 'searchDetailsByQrCode']);
Route::get('/books/category-wise-list', [BookController::class, 'CategoryWiseBookList']);
Route::get('/books/history/{id}', [IssueBookController::class, 'history']);

Route::post('/issue-books', [IssueBookController::class, 'store']);
Route::post('/issue-bulk-books-with-qr', [IssueBookController::class, 'bulkBookIssueWithQR']);
Route::post('/issue-books-with-qr', [IssueBookController::class, 'singleBookIssueWithQR']);
Route::post('/issue-books-for-other', [IssueBookController::class, 'issueBookForAnotherUser']);



Route::get('/issue-books/list-by-user', [IssueBookController::class, 'listByUser']);
Route::get('/issue-books/issued-list-by-user', [IssueBookController::class, 'issuedBookListByUser']);
Route::get('/issue-books/request-list-by-user', [IssueBookController::class, 'requestedBookListByUser']);

Route::patch('/return-book', [IssueBookController::class, 'returnBook']);
Route::post('/transfer-book', [BookTransferController::class, 'transferBook']);

Route::get('/books-shelve/search-by-qrcode', [BookShelveController::class, 'searchByQrCode']);
Route::post('/bookmark', [BookmarkController::class, 'store']);
Route::get('/bookmark/list', [BookmarkController::class, 'index']);
Route::get('/bookmark/remove', [BookmarkController::class, 'destroy']);

Route::post('/share', [BookmarkController::class, 'share']);
Route::get('/books/detail-by-book-shelves-qrcode', [BookController::class, 'showBooksByBookShelveQRCode']);
Route::get('/books/detail-by-book-shelves', [BookController::class, 'showBooksByBookShelve']);
Route::get('/my/request/books/{id}', [BookController::class, 'myrequestedbookList']);
Route::get('/requested/books/by/user/{id}', [BookController::class, 'requestedbookList']);

Route::get('/completed/history/{id}', [BookController::class, 'completedbookList']);
Route::get('/scanned-books/list-by-authorized-user', [BookController::class, 'scannedlistByUser']);
Route::post('/status/change/for/requested/books', [BookController::class, 'statuschangeforRequestedbooks']);
//return book
Route::post('/return-request-send', [BookController::class, 'returnRequestSend']);

Route::get('/return-request-list/{id}', [BookController::class, 'returnRequestList']);

Route::post('/return-bulk-book', [BookController::class, 'returnBook']);

Route::post('/return-bulk-book-for-captain', [BookController::class, 'returnBookForCaptain']);

Route::post('/save-fcm-token', [NotificationController::class, 'saveToken']);


Route::post('/save-notification', [NotificationController::class, 'Notification']);
Route::get('/notification-list-by-user', [NotificationController::class, 'notificationListByUser']);
Route::post('/notification-read', [NotificationController::class, 'markAsRead']);


Route::prefix('cab_bookings')->group(function () {
    // Route::get('/', [BookingController::class, 'index']);            
    // Route::get('/{id}', [BookingController::class, 'show']);         
    Route::post('/store', [CabBookingController::class, 'store']);  
    Route::post('/edit', [CabBookingController::class, 'edit']);  
    // Route::put('/{id}', [CabBookingController::class, 'update']);      
    // Route::delete('/{id}', [CabBookingController::class, 'destroy']);  
});

Route::prefix('flight_bookings')->group(function () {
    Route::post('/store', [FlightBookingController::class, 'store']); 
    Route::post('/edit', [FlightBookingController::class, 'edit']); 
});

Route::prefix('train_bookings')->group(function () {
    Route::post('/store', [TrainBookingController::class, 'store']);    
    Route::post('/edit', [TrainBookingController::class, 'edit']);    
});


Route::prefix('hotel_bookings')->group(function () {
    Route::post('/store', [HotelBookingController::class, 'store']);
    Route::post('/edit', [HotelBookingController::class, 'edit']);
});

Route::get('/room_list', [HotelBookingController::class, 'roomList']);
Route::get('/property_list', [HotelBookingController::class, 'propertyList']);
Route::get('/booked_hotel_list', [HotelBookingController::class, 'userRoomBookings']);



Route::get('bookings_history', [BookingHistoryController::class, 'getBookingHistory']);


Route::prefix('cancel_bookings')->group(function () {
    Route::post('/train', [TrainBookingController::class, 'cancelTrainBooking']);
    Route::post('/flight', [FlightBookingController::class, 'cancelFlightBooking']);
    Route::post('/cab', [CabBookingController::class, 'cancelCabBooking']);
    Route::post('/hotel', [HotelBookingController::class, 'cancelHotelBooking']);
});

//cave
Route::get('cave-search', [CaveController::class, 'search']);

Route::get('cave-list/{id}', [CaveController::class, 'index']);

Route::get('cave-detail/{id}', [CaveController::class, 'detail']);

Route::post('take-in', [CaveController::class, 'store']);

Route::post('received', [CaveController::class, 'received']);


Route::post('/take-out-request-send', [CaveController::class, 'takeOutRequest']);
Route::get('/my/request/vault/{id}', [CaveController::class, 'myrequestedvaultList']);
Route::get('/requested/vaults/by/user/{id}', [CaveController::class, 'requestedvaultList']);

Route::get('/issued-vaults/list-by-user/{id}', [CaveController::class, 'listByUser']);
Route::get('/scanned-vaults/list-by-authorized-user', [CaveController::class, 'scannedlistByUser']);
Route::post('/scan/to/accept/requested/vaults', [CaveController::class, 'statuschangeforRequestedvaults']);

Route::get('/vault/history/{id}', [CaveController::class, 'vaultHistory']);